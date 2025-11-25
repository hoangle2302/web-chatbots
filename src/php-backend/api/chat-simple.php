<?php
/**
 * Simple Chat API Endpoint
 * Xử lý chat với AI - phiên bản đơn giản
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Only POST is supported.',
        'code' => 'METHOD_NOT_ALLOWED'
    ]);
    exit();
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (empty($input['message'])) {
        throw new Exception('Message is required');
    }

    $message = trim($input['message']);
    $model = $input['model'] ?? 'gpt-4-turbo';
    $mode = $input['mode'] ?? 'single';

    // Basic validation
    if (strlen($message) < 1) {
        throw new Exception('Message cannot be empty');
    }
    
    if (strlen($message) > 1000) {
        throw new Exception('Message too long (max 1000 characters)');
    }

    // Simulate AI response based on message content
    $response = generateAIResponse($message, $model, $mode);

    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'content' => $response,
            'model' => $model,
            'mode' => $mode,
            'tokens_used' => rand(50, 200),
            'response_time' => rand(1, 3),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => 'VALIDATION_ERROR'
    ]);
}

/**
 * Generate AI response based on message content
 */
function generateAIResponse($message, $model, $mode) {
    $message = strtolower($message);
    
    // Greeting responses
    if (strpos($message, 'xin chào') !== false || strpos($message, 'hello') !== false) {
        return "Xin chào! Tôi là AI assistant của Thư Viện AI. Tôi có thể giúp bạn trả lời câu hỏi, giải thích khái niệm, hoặc trò chuyện về bất kỳ chủ đề nào. Bạn muốn hỏi gì?";
    }
    
    // Question about AI
    if (strpos($message, 'ai') !== false || strpos($message, 'artificial intelligence') !== false) {
        return "AI (Artificial Intelligence) là công nghệ cho phép máy tính thực hiện các tác vụ thường đòi hỏi trí thông minh của con người, như nhận dạng hình ảnh, xử lý ngôn ngữ tự nhiên, và đưa ra quyết định. AI đang phát triển rất nhanh và có nhiều ứng dụng trong cuộc sống hàng ngày.";
    }
    
    // Programming questions
    if (strpos($message, 'code') !== false || strpos($message, 'programming') !== false || strpos($message, 'lập trình') !== false) {
        return "Tôi có thể giúp bạn với các câu hỏi về lập trình! Bạn muốn hỏi về ngôn ngữ nào? PHP, JavaScript, Python, hay ngôn ngữ khác? Tôi có thể giải thích concepts, syntax, best practices, và giúp debug code.";
    }
    
    // Math questions
    if (strpos($message, 'toán') !== false || strpos($message, 'math') !== false || strpos($message, 'tính') !== false) {
        return "Tôi có thể giúp bạn với các bài toán! Bạn có thể đưa ra bài toán cụ thể, tôi sẽ giải thích từng bước và đưa ra đáp án. Tôi có thể xử lý đại số, hình học, giải tích, và nhiều lĩnh vực toán học khác.";
    }
    
    // Technology questions
    if (strpos($message, 'công nghệ') !== false || strpos($message, 'technology') !== false || strpos($message, 'tech') !== false) {
        return "Công nghệ đang phát triển rất nhanh! Tôi có thể thảo luận về các xu hướng công nghệ mới như AI, Machine Learning, Blockchain, IoT, Cloud Computing, và nhiều lĩnh vực khác. Bạn quan tâm đến chủ đề nào?";
    }
    
    // Weather questions
    if (strpos($message, 'thời tiết') !== false || strpos($message, 'weather') !== false) {
        return "Tôi không có khả năng truy cập dữ liệu thời tiết thời gian thực, nhưng tôi có thể giải thích về khí hậu, các hiện tượng thời tiết, và cách dự báo thời tiết hoạt động. Bạn muốn biết gì về thời tiết?";
    }
    
    // Default responses based on model
    $responses = [
        'gpt-4-turbo' => "Đây là một câu hỏi thú vị! Tôi là GPT-4 Turbo và tôi có thể giúp bạn phân tích vấn đề này một cách chi tiết. Bạn có thể cung cấp thêm thông tin để tôi có thể đưa ra câu trả lời chính xác hơn không?",
        'claude-3-5-sonnet' => "Cảm ơn bạn đã chia sẻ câu hỏi này. Tôi là Claude 3.5 Sonnet và tôi sẽ cố gắng đưa ra câu trả lời hữu ích. Bạn có thể cho tôi biết thêm context hoặc chi tiết cụ thể không?",
        'gemini-pro' => "Tôi là Gemini Pro và tôi thấy câu hỏi của bạn rất hay. Để tôi có thể trả lời tốt nhất, bạn có thể cung cấp thêm một số thông tin bổ sung không?",
        'ensemble' => "🤖 Đã hỏi ý kiến 4 AI hàng đầu và đây là câu trả lời tổng hợp: Câu hỏi của bạn rất thú vị và đáng suy nghĩ. Dựa trên phân tích từ nhiều góc độ khác nhau, tôi khuyên bạn nên xem xét kỹ hơn về context và mục tiêu cụ thể.",
        'distributed' => "🚀 28 AI đã phân công nhiệm vụ và đây là kết quả: Sau khi phân tích từ nhiều chuyên gia AI khác nhau, tôi có thể đưa ra một câu trả lời toàn diện. Bạn có muốn tôi giải thích chi tiết từng khía cạnh không?"
    ];
    
    return $responses[$model] ?? $responses['gpt-4-turbo'];
}
?>
