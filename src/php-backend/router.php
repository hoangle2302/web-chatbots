<?php
/**
 * Simple Router for PHP Development Server
 */

// Log that router.php is being executed
error_log("=== ROUTER.PHP EXECUTED ===");
error_log("Router - Script: " . __FILE__);
error_log("Router - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET'));
error_log("Router - SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET'));
error_log("Router - SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET'));

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request URI and method
// Try multiple sources for REQUEST_URI to handle different server configurations
$requestUri = $_SERVER['REQUEST_URI'] ?? $_SERVER['REDIRECT_URL'] ?? '/';
$uri = parse_url($requestUri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove .php extension from URI for routing (backward compatibility)
$uri = preg_replace('/\.php$/', '', $uri);

// Normalize URI (remove trailing slash except for root)
$uri = rtrim($uri, '/') ?: '/';

// Log request with full debug info
error_log("Router - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET'));
error_log("Router - Parsed URI: $uri");
error_log("Router - Method: $method");

// Route to appropriate file with error handling
try {
    // Test endpoint to verify router.php is being called
    if ($uri === '/api/test-router') {
        error_log("Router - Test endpoint called!");
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Router.php is working!',
            'uri' => $uri,
            'request_uri' => $requestUri,
            'method' => $method,
            'server_vars' => [
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'NOT SET',
                'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'NOT SET',
                'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET'
            ]
        ]);
        exit;
    }
    
    if ($uri === '/api/health' || $uri === '/health') {
        if (file_exists(__DIR__ . '/api/health.php')) {
            require __DIR__ . '/api/health.php';
            exit;
        } else {
            error_log("File not found: /api/health.php");
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Health endpoint not found']);
            exit;
        }
    }

// Route /api/auth.php?action=... or /api/auth/...
if (preg_match('/^\/api\/auth\/(\w+)$/', $uri, $matches)) {
    // Format: /api/auth/login
    $_GET['action'] = $matches[1];
    require __DIR__ . '/api/auth.php';
    exit;
}

if ($uri === '/api/auth') {
    // Format: /api/auth.php?action=login
    // Action will be in $_GET['action']
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

if ($uri === '/api/models') {
    require __DIR__ . '/api/models.php';
    exit;
}

if ($uri === '/api/ai-tool') {
    require __DIR__ . '/api/ai-tool.php';
    exit;
}

// Route /api/user/* endpoints to index.php
if (strpos($uri, '/api/user/') === 0) {
    error_log("Router - Routing to index.php for: $uri");
    // Ensure REQUEST_URI is preserved for index.php
    $_SERVER['REQUEST_URI'] = $requestUri;
    require __DIR__ . '/index.php';
    exit;
}

// Route /api/history to index.php
if ($uri === '/api/history') {
    error_log("Router - Routing to index.php for: $uri");
    // Ensure REQUEST_URI is preserved for index.php
    $_SERVER['REQUEST_URI'] = $requestUri;
    require __DIR__ . '/index.php';
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

// Fallback: if no route matched, return 404
error_log("Router - No route matched for: $uri");
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Endpoint not found',
    'path' => $uri,
    'method' => $method
]);
exit;
} catch (Exception $e) {
    error_log("Router error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>
