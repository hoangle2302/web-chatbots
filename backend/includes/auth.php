<?php
require_once 'config.php';
require_once 'database.php';

class Auth {
    private static $db;
    
    public static function init() {
        $database = new Database();
        self::$db = $database->getConnection();
    }
    
    public static function login($email, $password, $remember = false) {
        self::init();
        
        try {
            // Check login attempts
            $stmt = self::$db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác'];
            }
            
            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                $lockTime = date('H:i d/m/Y', strtotime($user['locked_until']));
                return ['success' => false, 'message' => "Tài khoản bị khóa đến $lockTime"];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                // Increment login attempts
                self::incrementLoginAttempts($user['id']);
                return ['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác'];
            }
            
            // Reset login attempts on successful login
            self::resetLoginAttempts($user['id']);
            
            // Update last login
            $stmt = self::$db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Create session
            self::createSession($user, $remember);
            
            // Log login
            self::logAction($user['id'], 'login', 'User logged in successfully');
            
            return ['success' => true, 'message' => 'Đăng nhập thành công'];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
        }
    }
    
    public static function register($data) {
        self::init();
        
        try {
            // Validate data
            $validation = self::validateRegistration($data);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // Check if email exists
            $stmt = self::$db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email đã được sử dụng'];
            }
            
            // Check if username exists
            $stmt = self::$db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$data['username']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Tên đăng nhập đã được sử dụng'];
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = self::$db->prepare("
                INSERT INTO users (username, email, password, full_name) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['full_name']
            ]);
            
            $userId = self::$db->lastInsertId();
            
            // Log registration
            self::logAction($userId, 'register', 'User registered successfully');
            
            return ['success' => true, 'message' => 'Đăng ký thành công'];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
        }
    }
    
    public static function logout() {
        if (isset($_SESSION['user_id'])) {
            self::logAction($_SESSION['user_id'], 'logout', 'User logged out');
            
            // Remove session from database
            if (isset($_SESSION['session_token'])) {
                self::init();
                $stmt = self::$db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
                $stmt->execute([$_SESSION['session_token']]);
            }
        }
        
        // Clear session
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
    }
    
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        self::init();
        $stmt = self::$db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public static function requireAdmin() {
        $user = self::getCurrentUser();
        if (!$user || !$user['is_admin']) {
            header('Location: dashboard.php');
            exit();
        }
    }
    
    private static function createSession($user, $remember = false) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['preferred_ai_model'] = $user['preferred_ai_model'];
        
        // Generate session token
        $sessionToken = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $sessionToken;
        
        // Store session in database
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
        $stmt = self::$db->prepare("
            INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user['id'],
            $sessionToken,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $expiresAt
        ]);
        
        // Set remember me cookie
        if ($remember) {
            $rememberToken = bin2hex(random_bytes(32));
            setcookie('remember_token', $rememberToken, time() + (30 * 24 * 3600), '/'); // 30 days
            
            $stmt = self::$db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$rememberToken, $user['id']]);
        }
    }
    
    private static function incrementLoginAttempts($userId) {
        $stmt = self::$db->prepare("
            UPDATE users 
            SET login_attempts = login_attempts + 1,
                locked_until = CASE 
                    WHEN login_attempts >= 4 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                    ELSE locked_until
                END
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }
    
    private static function resetLoginAttempts($userId) {
        $stmt = self::$db->prepare("
            UPDATE users 
            SET login_attempts = 0, locked_until = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }
    
    private static function validateRegistration($data) {
        if (empty($data['username']) || strlen($data['username']) < 3) {
            return ['valid' => false, 'message' => 'Tên đăng nhập phải có ít nhất 3 ký tự'];
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Email không hợp lệ'];
        }
        
        if (empty($data['password']) || strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự'];
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            return ['valid' => false, 'message' => 'Mật khẩu xác nhận không khớp'];
        }
        
        if (empty($data['full_name']) || strlen($data['full_name']) < 2) {
            return ['valid' => false, 'message' => 'Họ tên phải có ít nhất 2 ký tự'];
        }
        
        return ['valid' => true];
    }
    
    private static function logAction($userId, $action, $details) {
        try {
            $stmt = self::$db->prepare("
                INSERT INTO system_logs (user_id, action, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Log action error: " . $e->getMessage());
        }
    }
}
?>