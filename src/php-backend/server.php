<?php
/**
 * Router for PHP Development Server
 * Sử dụng: php -S localhost:8001 server.php
 */

// Get request URI and method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Log request
error_log("Request: $method $uri");

// Route to appropriate file
if ($uri === '/api/health' || $uri === '/health') {
    require __DIR__ . '/api/health.php';
    exit;
}

if (preg_match('/^\/api\/auth\/(\w+)$/', $uri, $matches)) {
    $_GET['action'] = $matches[1];
    require __DIR__ . '/api/auth.php';
    exit;
}

if ($uri === '/api/admin' || strpos($uri, '/api/admin/') === 0) {
    require __DIR__ . '/api/admin.php';
    exit;
}

if ($uri === '/api/chat') {
    require __DIR__ . '/api/chat-simple.php';
    exit;
}

if ($uri === '/api/chat-real') {
    require __DIR__ . '/api/chat-real.php';
    exit;
}

if ($uri === '/api/documents' || strpos($uri, '/api/documents/') === 0) {
    require __DIR__ . '/api/documents.php';
    exit;
}

if ($uri === '/api/models' || $uri === '/api/models.php') {
    require __DIR__ . '/api/models.php';
    exit;
}

// Check if file exists
$file = __DIR__ . $uri;
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    return false; // Serve the file directly
}

// Default to index.php
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    exit;
}

// Fallback to index.php for other routes
require __DIR__ . '/index.php';
?>






