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
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get specific chat
        $chatId = $_GET['id'] ?? null;
        
        if (!$chatId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Chat ID is required'
            ]);
            exit();
        }
        
        // Verify chat belongs to user
        $stmt = $conn->prepare("SELECT id, title FROM chat_sessions WHERE id = ? AND user_id = ?");
        $stmt->execute([$chatId, $userId]);
        $chat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$chat) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Chat not found'
            ]);
            exit();
        }
        
        // Get messages
        $stmt = $conn->prepare("
            SELECT role, content, created_at 
            FROM messages 
            WHERE chat_session_id = ? 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$chatId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'chat' => $chat,
            'messages' => $messages
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Send message
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['message'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Message is required'
            ]);
            exit();
        }
        
        $message = trim($input['message']);
        $chatId = $input['chatId'] ?? null;
        $aiModel = $input['aiModel'] ?? 'gpt-3.5-turbo';
        
        // Create new chat if none exists
        if (!$chatId) {
            $chatTitle = mb_substr($message, 0, 50) . (mb_strlen($message) > 50 ? '...' : '');
            
            $stmt = $conn->prepare("
                INSERT INTO chat_sessions (user_id, title, ai_model) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$userId, $chatTitle, $aiModel]);
            $chatId = $conn->lastInsertId();
        }
        
        // Save user message
        $stmt = $conn->prepare("
            INSERT INTO messages (chat_session_id, role, content) 
            VALUES (?, 'user', ?)
        ");
        $stmt->execute([$chatId, $message]);
        
        // Generate AI response (simple simulation for now)
        $responses = [
            "Cảm ơn bạn đã chia sẻ! Về \"{$message}\", tôi nghĩ rằng đây là một chủ đề thú vị. Bạn có muốn tôi giải thích thêm không? 🤔",
            "Thật tuyệt! \"{$message}\" là một ý tưởng hay. Tôi có thể giúp bạn phát triển nó thêm. Bạn muốn bắt đầu từ đâu? 💡",
            "Tôi hiểu bạn đang quan tâm đến \"{$message}\". Đây là một lĩnh vực rất thú vị! Tôi có thể chia sẻ một số thông tin hữu ích về điều này. 📚",
            "\"{$message}\" - đây là một câu hỏi tốt! Hãy để tôi suy nghĩ và đưa ra câu trả lời chi tiết nhất cho bạn. ✨",
            "Wow! \"{$message}\" nghe có vẻ thú vị đấy. Tôi có một số ý tưởng về điều này. Bạn có muốn tôi liệt kê ra không? 🚀"
        ];
        
        $aiResponse = $responses[array_rand($responses)];
        
        // Save AI response
        $stmt = $conn->prepare("
            INSERT INTO messages (chat_session_id, role, content) 
            VALUES (?, 'assistant', ?)
        ");
        $stmt->execute([$chatId, $aiResponse]);
        
        // Update chat session timestamp
        $stmt = $conn->prepare("UPDATE chat_sessions SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$chatId]);
        
        echo json_encode([
            'success' => true,
            'response' => $aiResponse,
            'chatId' => $chatId
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