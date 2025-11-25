<?php
/**
 * Models API Endpoint
 * Trả về danh sách tất cả models có sẵn
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Debug logging
    error_log("Models API: Starting to load services...");
    
    // Load services
    require_once __DIR__ . '/../services/Key4UService.php';
    require_once __DIR__ . '/../services/QwenService.php';
    
    error_log("Models API: Services loaded, creating instances...");
    
    $key4uService = new Key4UService();
    $qwenService = new QwenService();
    
    error_log("Models API: Getting all models...");
    
    // Get all models
    $key4uModels = $key4uService->getAllModels();
    $qwenModels = $qwenService->getAvailableModels();
    $topModels = $key4uService->getTopModels();
    
    error_log("Models API: Models retrieved successfully");
    
    // Combine models
    $allModels = [
        'key4u' => $key4uModels,
        'qwen' => $qwenModels,
        'top_models' => $topModels,
        'default_chat_model' => 'qwen3-235b-a22b',
        'default_image_model' => 'flux-kontext-max',
        'default_audio_model' => 'whisper-1',
        'default_video_model' => 'veo2'
    ];
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $allModels,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log("Models API Error: " . $e->getMessage());
    error_log("Models API Error Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => 'MODELS_API_ERROR',
        'trace' => $e->getTraceAsString()
    ]);
}
?>

