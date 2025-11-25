<?php
/**
 * Init MySQL DB script - creates database (if missing) and tables using Database.php
 * Usage (Windows PowerShell):
 *   php ./src/php-backend/tools/init-mysql.php
 */

header('Content-Type: application/json; charset=utf-8');

try {
	$root = realpath(__DIR__ . '/../../../');
	if ($root === false) {
		throw new Exception('Cannot resolve project root');
	}

	// Load config.env to fill $_ENV
	$envFile = $root . '/config.env';
	if (file_exists($envFile)) {
		$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
				list($key, $value) = explode('=', $line, 2);
				$_ENV[trim($key)] = trim($value);
			}
		}
	}

	// Guard: ensure MySQL is selected
	if (!isset($_ENV['DB_HOST'])) {
		throw new Exception('MySQL is not enabled in config.env. Set DB_HOST, DB_PORT, DB_NAME, DB_USERNAME, DB_PASSWORD');
	}

	require_once $root . '/src/php-backend/config/Database.php';

	$database = new Database();
	$conn = $database->getConnection();

	// Verify users table works
	$stmt = $conn->query("SELECT COUNT(*) AS c FROM users");
	$row = $stmt ? $stmt->fetch() : null;

	echo json_encode([
		'success' => true,
		'message' => 'MySQL database initialized successfully',
		'host' => $_ENV['DB_HOST'] ?? null,
		'database' => $_ENV['DB_NAME'] ?? null,
		'users_count' => $row ? intval($row['c']) : 0
	]);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'error' => $e->getMessage()
	]);
}


