<?php
/**
 * Simple Authentication API for debugging
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to catch any unwanted output
ob_start();

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Include required files
    require_once '../config/database.php';
    
    session_start();
    
    // Get action
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    if (empty($action)) {
        throw new Exception('No action specified');
    }
    
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
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    // Clear any output
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => 'API Error',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

function handleLogin() {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Method not allowed');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            throw new Exception('Email và mật khẩu không được để trống');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ');
        }
        
        // Connect to database
        $db = new Database();
        $conn = $db->getConnection();
        
        if (!$conn) {
            throw new Exception('Không thể kết nối database');
        }
        
        // Find user
        $stmt = $conn->prepare("SELECT id, name, email, password, status FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception('Email hoặc mật khẩu không chính xác');
        }
        
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Update last login
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Return success
        unset($user['password']);
        
        ob_clean(); // Clear any output
        echo json_encode([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => $user,
                'session_id' => session_id()
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        ob_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    exit();
}

function handleRegister() {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Method not allowed');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception('Tất cả các trường đều bắt buộc');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Mật khẩu phải có ít nhất 6 ký tự');
        }
        
        // Connect to database
        $db = new Database();
        $conn = $db->getConnection();
        
        if (!$conn) {
            throw new Exception('Không thể kết nối database');
        }
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email đã được sử dụng');
        }
        
        // Hash password and insert user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, status, created_at) VALUES (?, ?, ?, 'active', NOW())");
        $result = $stmt->execute([$name, $email, $hashedPassword]);
        
        if (!$result) {
            throw new Exception('Không thể tạo tài khoản');
        }
        
        $userId = $conn->lastInsertId();
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data' => [
                'user_id' => $userId
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        ob_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    exit();
}

function handleLogout() {
    session_unset();
    session_destroy();
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Đăng xuất thành công',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

function checkAuthStatus() {
    $isLoggedIn = isset($_SESSION['user_id']) && 
                  isset($_SESSION['last_activity']) && 
                  (time() - $_SESSION['last_activity']) < 3600;
    
    if ($isLoggedIn) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT id, name, email, status FROM users WHERE id = ? AND status = 'active'");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['last_activity'] = time();
                
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'message' => 'Authenticated',
                    'data' => [
                        'authenticated' => true,
                        'user' => $user
                    ],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                session_destroy();
                throw new Exception('User not found');
            }
        } catch (Exception $e) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Session không hợp lệ',
                'data' => ['authenticated' => false],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    } else {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Not authenticated',
            'data' => ['authenticated' => false],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    exit();
}
?>