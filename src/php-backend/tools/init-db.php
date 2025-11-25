<?php
/**
 * Init DB script - creates SQLite folder/file and tables using Database.php
 * Usage (Windows PowerShell):
 *   php ./src/php-backend/tools/init-db.php
 */

header('Content-Type: application/json; charset=utf-8');

try {
	$root = realpath(__DIR__ . '/../../');
	if ($root === false) {
		throw new Exception('Cannot resolve project root');
	}

	// Load config.env to get DATABASE_PATH
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

	$dbRelPath = $_ENV['DATABASE_PATH'] ?? 'data/database/thuvien_ai.db';
	$dbAbsPath = $root . '/' . $dbRelPath;
	$dbDir = dirname($dbAbsPath);

	// Ensure database directory exists
	if (!is_dir($dbDir)) {
		if (!mkdir($dbDir, 0777, true) && !is_dir($dbDir)) {
			throw new Exception('Failed to create database directory: ' . $dbDir);
		}
	}

	require_once $root . '/src/php-backend/config/Database.php';

	$database = new Database();
	$conn = $database->getConnection();

	// Touch tables with a trivial query
	$stmt = $conn->query("SELECT COUNT(*) AS c FROM users");
	$row = $stmt ? $stmt->fetch() : null;

	echo json_encode([
		'success' => true,
		'message' => 'Database initialized successfully',
		'database_path' => $dbAbsPath,
		'users_count' => $row ? intval($row['c']) : 0
	]);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'error' => $e->getMessage()
	]);
}








