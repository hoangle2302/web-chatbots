<?php
/**
 * Authentication Helper Functions
 * Common functions for authentication and session management
 */

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['last_activity']) && 
           (time() - $_SESSION['last_activity']) < SESSION_TIMEOUT;
}

function requireLogin() {
    if (!isUserLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Phiên đăng nhập đã hết hạn',
            'code' => 'SESSION_EXPIRED'
        ]);
        exit();
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

function getCurrentUserEmail() {
    return $_SESSION['user_email'] ?? '';
}

function checkLoginAttempts($email, $conn) {
    try {
        // Clean old attempts (older than 15 minutes)
        $stmt = $conn->prepare("DELETE FROM login_attempts WHERE email = ? AND attempt_time < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->execute([$email]);
        
        // Count recent attempts
        $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE email = ? AND success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        
        return ($result['attempts'] ?? 0) >= MAX_LOGIN_ATTEMPTS;
    } catch (Exception $e) {
        error_log("Check login attempts error: " . $e->getMessage());
        return false;
    }
}

function logLoginAttempt($email, $success, $conn) {
    try {
        $stmt = $conn->prepare("INSERT INTO login_attempts (email, success, ip_address, user_agent, attempt_time) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $email,
            $success ? 1 : 0,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Log login attempt error: " . $e->getMessage());
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput($input, $type = 'string') {
    if (is_array($input)) {
        return array_map(function($item) use ($type) {
            return sanitizeInput($item, $type);
        }, $input);
    }
    
    $input = trim($input);
    
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'html':
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        default:
            return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password) {
    return strlen($password) >= 6;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function logActivity($action, $details = '', $userId = null) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $userId = $userId ?? getCurrentUserId();
        
        $stmt = $conn->prepare("INSERT INTO user_activities (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $userId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Log activity error: " . $e->getMessage());
    }
}

function sendResponse($success, $message, $code = 200, $data = []) {
    http_response_code($code);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (!empty($data)) {
        $response['data'] = $data;
    }
    
    // Add CSRF token for authenticated users
    if (isUserLoggedIn()) {
        $response['csrf_token'] = generateCSRFToken();
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

function corsHeaders() {
    // Get the origin of the request
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Check if the origin is allowed
    $allowedOrigins = [
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:3000',
        'http://localhost:8000'
    ];
    
    if (in_array($origin, $allowedOrigins) || strpos($origin, 'file://') === 0) {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json; charset=utf-8');
}

function handlePreflight() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        corsHeaders();
        http_response_code(204);
        exit();
    }
}

function getUserById($userId, $conn) {
    try {
        $stmt = $conn->prepare("SELECT id, name, email, status, created_at, last_login FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get user by ID error: " . $e->getMessage());
        return false;
    }
}

function updateUserLastActivity($userId, $conn) {
    try {
        $stmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    } catch (Exception $e) {
        error_log("Update user activity error: " . $e->getMessage());
    }
}
?>