<?php
/**
 * Thư Viện AI - PHP Backend
 * Main entry point cho API
 */

// Cấu hình CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Xử lý preflight request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Autoload classes
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/services/AIService.php';
require_once __DIR__ . '/services/DocumentService.php';
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Log.php';
require_once __DIR__ . '/models/AIQueryHistory.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

// Khởi tạo database
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $db = null;
}

// Lấy method và path
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Debug logging
error_log("Request URI: " . $requestUri);
error_log("Parsed path: " . $path);
error_log("Method: " . $method);

// Remove /index.php from path if present
$path = str_replace('/index.php', '', $path);

// Simple health check for root path
if ($path === '/' || $path === '') {
    sendSuccess([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'message' => 'Thư Viện AI API is running'
    ]);
}

// Router đơn giản
try {
    switch ($path) {
        case '/api/chat':
            if ($method === 'POST') {
                handleChat($db);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/upload':
            if ($method === 'POST') {
                handleUpload($db);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/login':
            if ($method === 'POST') {
                handleLogin($db);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/register':
            if ($method === 'POST') {
                handleRegister($db);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/auth/register':
            if ($method === 'POST') {
                // Redirect to auth.php with register action
                $_GET['action'] = 'register';
                require_once __DIR__ . '/api/auth.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/auth/login':
            if ($method === 'POST') {
                // Redirect to auth.php with login action
                $_GET['action'] = 'login';
                require_once __DIR__ . '/api/auth.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/logout':
            if ($method === 'POST') {
                handleLogout();
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/user/profile':
            if ($method === 'GET') {
                handleGetProfile($db);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/history':
        case '/api/user/history':
            if ($method === 'GET') {
                handleGetHistory($db);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/models':
            if ($method === 'GET') {
                handleGetModels();
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case '/api/health':
            if ($method === 'GET') {
                handleHealthCheck();
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        default:
            sendError('Endpoint not found', 404);
            break;
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendError('Internal server error', 500);
}

/**
 * Xử lý chat request
 */
function handleChat($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['message']) || empty($input['message'])) {
        sendError('Message is required', 400);
        return;
    }
    
    $message = $input['message'];
    $model = $input['model'] ?? 'gpt-4-turbo';
    $mode = $input['mode'] ?? 'single';
    $document = $input['document'] ?? null;
    
    $aiService = new AIService();
    
    try {
        if ($mode === 'ensemble') {
            $response = $aiService->processEnsemble($message, $document);
        } elseif ($mode === 'distributed') {
            $response = $aiService->processDistributed($message, $document);
        } else {
            $response = $aiService->processSingle($message, $model, $document);
        }
        
        // Lưu lịch sử (tạm thời bỏ qua để tránh lỗi database)
        // $userId = getCurrentUserId();
        // if ($userId) {
        //     $history = new AIQueryHistory($db);
        //     $history->create($userId, $message, $response['content']);
        // }
        
        sendSuccess($response);
    } catch (Exception $e) {
        error_log("Chat error: " . $e->getMessage());
        sendError('Failed to process chat request', 500);
    }
}

/**
 * Xử lý upload file
 */
function handleUpload($db) {
    if (!isset($_FILES['file'])) {
        sendError('No file uploaded', 400);
        return;
    }
    
    $file = $_FILES['file'];
    $documentService = new DocumentService();
    
    try {
        $result = $documentService->processUpload($file);
        sendSuccess($result);
    } catch (Exception $e) {
        error_log("Upload error: " . $e->getMessage());
        sendError('Failed to process file', 500);
    }
}

/**
 * Xử lý đăng nhập
 */
function handleLogin($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password'])) {
        sendError('Username and password are required', 400);
        return;
    }
    
    $userService = new UserService($db);
    
    try {
        $user = $userService->login($input['username'], $input['password']);
        if ($user) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            sendSuccess([
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            sendError('Invalid credentials', 401);
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        sendError('Login failed', 500);
    }
}

/**
 * Xử lý đăng ký
 */
function handleRegister($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password'])) {
        sendError('Username and password are required', 400);
        return;
    }
    
    $userService = new UserService($db);
    $auth = new AuthMiddleware();
    
    try {
        $user = $userService->register($input['username'], $input['password']);
        if ($user) {
            // Tạo session
            session_start();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            
            // Generate JWT token for automatic login
            $token = $auth->generateToken(
                $user->id,
                $user->username,
                $user->role ?? 'user'
            );
            
            sendSuccess([
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role ?? 'user'
                ],
                'expires_in' => 24 * 60 * 60 // 24 hours in seconds
            ]);
        } else {
            sendError('Registration failed', 400);
        }
    } catch (Exception $e) {
        error_log("Register error: " . $e->getMessage());
        sendError('Registration failed', 500);
    }
}

/**
 * Xử lý đăng xuất
 */
function handleLogout() {
    session_start();
    session_destroy();
    sendSuccess(['message' => 'Logged out successfully']);
}

/**
 * Lấy thông tin profile
 */
function handleGetProfile($db) {
    // Sử dụng JWT authentication thay vì session
    $auth = new AuthMiddleware();
    
    $token = $auth->getTokenFromRequest();
    if (!$token) {
        sendError('No token provided', 401);
        return;
    }
    
    $user_data = $auth->getCurrentUser($token);
    if (!$user_data) {
        sendError('Invalid token', 401);
        return;
    }
    
    $userService = new UserService($db);
    $user = $userService->getById($user_data['user_id']);
    
    if ($user) {
        unset($user['password']); // Không trả về password
        sendSuccess(['user' => $user]);
    } else {
        sendError('User not found', 404);
    }
}

/**
 * Lấy lịch sử chat
 */
function handleGetHistory($db) {
    try {
        // Kiểm tra database connection
        if (!$db) {
            error_log("Database connection is null in handleGetHistory");
            sendError('Database connection failed', 500);
            return;
        }
        
        // Sử dụng JWT authentication thay vì session
        $auth = new AuthMiddleware();
        
        $token = $auth->getTokenFromRequest();
        if (!$token) {
            sendError('No token provided', 401);
            return;
        }
        
        $user_data = $auth->getCurrentUser($token);
        if (!$user_data) {
            sendError('Invalid token', 401);
            return;
        }
        
        $userId = $user_data['user_id'];
        
        if (!$userId) {
            error_log("User ID is null in handleGetHistory");
            sendError('Invalid user ID', 400);
            return;
        }
        
        $history = new AIQueryHistory($db);
        
        try {
            $records = $history->getByUserId($userId);
            
            // Đảm bảo records là array
            if (!is_array($records)) {
                error_log("getByUserId returned non-array, converting to empty array");
                $records = [];
            }
            
            sendSuccess(['history' => $records]);
        } catch (Exception $e) {
            error_log("Exception when calling getByUserId: " . $e->getMessage());
            // Trả về empty array thay vì error để không làm gián đoạn UI
            sendSuccess(['history' => []]);
        }
    } catch (Exception $e) {
        error_log("Error in handleGetHistory: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        // Trả về empty array thay vì error để không làm gián đoạn UI
        // Frontend sẽ fallback về local history nếu không có data
        sendSuccess(['history' => []]);
    }
}

/**
 * Lấy danh sách models
 */
function handleGetModels() {
    $config = new Config();
    $models = $config->getAvailableModels();
    sendSuccess(['models' => $models]);
}

/**
 * Health check
 */
function handleHealthCheck() {
    sendSuccess([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0'
    ]);
}

/**
 * Lấy user ID hiện tại
 */
function getCurrentUserId() {
    session_start();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Gửi response thành công
 */
function sendSuccess($data) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit();
}

/**
 * Gửi response lỗi
 */
function sendError($message, $code = 400) {
    if (!headers_sent()) {
        http_response_code($code);
    }
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit();
}
?>

