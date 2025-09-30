<?php
session_start();

// Include required files
require_once '../includes/Database.php';
require_once '../includes/Auth.php';

// Set JSON headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $auth = new Auth();
    
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Bạn cần đăng nhập để sử dụng tính năng này'
        ]);
        exit();
    }
    
    $user = $auth->getCurrentUser();
    $userId = $user['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get chat history
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT id, title, created_at, updated_at
            FROM chat_sessions 
            WHERE user_id = ? 
            ORDER BY updated_at DESC 
            LIMIT 20
        ");
        $stmt->execute([$userId]);
        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'chats' => $chats
        ]);
        
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>