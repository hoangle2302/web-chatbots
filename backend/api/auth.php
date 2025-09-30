<?php
/**
 * Authentication API
 * Handles login, register, logout, and session management
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';
require_once '../includes/auth_functions.php';

session_start();

// Route based on the action parameter
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'status':
        checkAuthStatus();
        break;
    case 'refresh':
        refreshSession();
        break;
    default:
        sendResponse(false, 'Invalid action', 400);
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', 405);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $email = sanitizeInput($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        sendResponse(false, 'Email và mật khẩu không được để trống', 400);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Email không hợp lệ', 400);
        return;
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check login attempts
        if (checkLoginAttempts($email, $conn)) {
            sendResponse(false, 'Tài khoản tạm thời bị khóa do đăng nhập sai quá nhiều lần', 429);
            return;
        }
        
        // Verify user credentials
        $stmt = $conn->prepare("SELECT id, name, email, password, status, created_at FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            // Log failed attempt
            logLoginAttempt($email, false, $conn);
            sendResponse(false, 'Email hoặc mật khẩu không chính xác', 401);
            return;
        }
        
        // Successful login
        logLoginAttempt($email, true, $conn);
        
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Update last login
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Return user data (without password)
        unset($user['password']);
        sendResponse(true, 'Đăng nhập thành công', 200, [
            'user' => $user,
            'session_id' => session_id()
        ]);
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        sendResponse(false, 'Lỗi hệ thống, vui lòng thử lại sau', 500);
    }
}

function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', 405);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $name = sanitizeInput($input['name'] ?? '');
    $email = sanitizeInput($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        sendResponse(false, 'Tất cả các trường đều bắt buộc', 400);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Email không hợp lệ', 400);
        return;
    }
    
    if (strlen($password) < 6) {
        sendResponse(false, 'Mật khẩu phải có ít nhất 6 ký tự', 400);
        return;
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendResponse(false, 'Email đã được sử dụng', 409);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, status, created_at) VALUES (?, ?, ?, 'active', NOW())");
        $result = $stmt->execute([$name, $email, $hashedPassword]);
        
        if ($result) {
            $userId = $conn->lastInsertId();
            sendResponse(true, 'Đăng ký thành công', 201, [
                'user_id' => $userId,
                'redirect' => 'login'
            ]);
        } else {
            sendResponse(false, 'Không thể tạo tài khoản', 500);
        }
        
    } catch (Exception $e) {
        error_log("Register error: " . $e->getMessage());
        sendResponse(false, 'Lỗi hệ thống, vui lòng thử lại sau', 500);
    }
}

function handleLogout() {
    if (!isUserLoggedIn()) {
        sendResponse(false, 'Người dùng chưa đăng nhập', 401);
        return;
    }
    
    // Clear session
    session_unset();
    session_destroy();
    
    sendResponse(true, 'Đăng xuất thành công', 200);
}

function checkAuthStatus() {
    if (isUserLoggedIn()) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT id, name, email, status, created_at, last_login FROM users WHERE id = ? AND status = 'active'");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['last_activity'] = time();
                sendResponse(true, 'Authenticated', 200, [
                    'authenticated' => true,
                    'user' => $user
                ]);
            } else {
                // User not found or inactive
                session_destroy();
                sendResponse(false, 'Session không hợp lệ', 401, [
                    'authenticated' => false
                ]);
            }
        } catch (Exception $e) {
            error_log("Auth status error: " . $e->getMessage());
            sendResponse(false, 'Lỗi kiểm tra xác thực', 500, [
                'authenticated' => false
            ]);
        }
    } else {
        sendResponse(false, 'Not authenticated', 401, [
            'authenticated' => false
        ]);
    }
}

function refreshSession() {
    if (isUserLoggedIn()) {
        $_SESSION['last_activity'] = time();
        sendResponse(true, 'Session refreshed', 200);
    } else {
        sendResponse(false, 'Not authenticated', 401);
    }
}

// Utility functions
function sendResponse($success, $message, $code = 200, $data = []) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>