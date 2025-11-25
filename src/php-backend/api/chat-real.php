<?php
/**
 * Real Chat API Endpoint
 * Kết nối với Key4U API để gọi AI models thật
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

// Only allow POST requests
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    $model = $input['model'] ?? 'qwen3-235b-a22b'; // Mặc định sử dụng Qwen
    $mode = $input['mode'] ?? 'single';
    $useQwenDefault = $input['use_qwen_default'] ?? false; // Flag để sử dụng QwenService làm mặc định
    
    // Nếu không có model hoặc model rỗng, sử dụng QwenService mặc định
    if (empty($model) || $model === 'loading' || $model === '') {
        $model = 'qwen3-235b-a22b';
        $useQwenDefault = true;
    }
    
    // Check if ensemble mode is requested
    $isEnsemble = ($model === 'ensemble');

    // Basic validation
    if (strlen($message) < 1) {
        throw new Exception('Message cannot be empty');
    }
    
    if (strlen($message) > 2000) {
        throw new Exception('Message too long (max 2000 characters)');
    }

    // Load configuration
    require_once __DIR__ . '/../config/Config.php';
    require_once __DIR__ . '/../config/Database.php';
    require_once __DIR__ . '/../models/User.php';
    require_once __DIR__ . '/../models/Log.php';
    require_once __DIR__ . '/../middleware/AuthMiddleware.php';
    
    // Try to get API key from config
    $config = new Config();
    $apiKey = $config->getKey4UApiKey();
    
    // Kiểm tra authentication bắt buộc
    $auth = new AuthMiddleware();
    $token = $auth->getTokenFromRequest();
    
    error_log("Chat API Debug - Token received: " . ($token ? "Yes (length: " . strlen($token) . ")" : "No"));
    
    // Yêu cầu authentication bắt buộc
    if (!$token) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Authentication required. Vui lòng đăng nhập để chat với AI.',
            'code' => 'AUTHENTICATION_REQUIRED'
        ]);
        exit();
    }
    
    // Xác thực token
    $user_data = $auth->getCurrentUser($token);
    error_log("Chat API Debug - User data from token: " . json_encode($user_data));
    
    if (!$user_data || !isset($user_data['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid or expired token. Vui lòng đăng nhập lại.',
            'code' => 'INVALID_TOKEN'
        ]);
        exit();
    }
    
    $userId = intval($user_data['user_id']);
    error_log("Chat API Debug - User ID: {$userId}");
    
    // Lấy thông tin user và credit
    $database = new Database();
    $db = $database->getConnection();
    $userModel = new User($db);
    $logModel = new Log($db);
    $userInfo = $userModel->getById($userId);
    
    if (!$userInfo) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'User not found. Vui lòng đăng nhập lại.',
            'code' => 'USER_NOT_FOUND'
        ]);
        exit();
    }
    
    $dailyBonus = $userModel->grantDailyCreditsIfNeeded($userId, 5);
    if (!empty($dailyBonus['granted'])) {
        $userInfo['credits'] = $dailyBonus['credits'];
        $userInfo['last_daily_credit_at'] = $dailyBonus['last_daily_credit_at'];

        $logModel->user_id = $userId;
        $logModel->action = 'daily_credit_bonus';
        $logModel->detail = 'Hệ thống cộng 5 credits hàng ngày khi sử dụng chat.';
        $logModel->ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $logModel->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $logModel->create();
    } elseif ($dailyBonus['credits'] !== null) {
        $userInfo['credits'] = $dailyBonus['credits'];
        $userInfo['last_daily_credit_at'] = $dailyBonus['last_daily_credit_at'];
    }

    $userCredits = intval($userInfo['credits'] ?? 0);
    $userRole = $userInfo['role'] ?? 'user';
    $isAdmin = ($userRole === 'admin');
    
    error_log("Chat API Debug - User credits: {$userCredits}, Role: {$userRole}, Is Admin: " . ($isAdmin ? 'Yes' : 'No'));
    
    // Kiểm tra credit trước khi cho phép chat (bỏ qua nếu là admin)
    if (!$isAdmin && $userCredits < 1) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Không đủ credit để gửi câu hỏi. Vui lòng nạp thêm credit.',
            'code' => 'INSUFFICIENT_CREDITS',
            'credits' => $userCredits
        ]);
        exit();
    }
    
    $response = "";
    $source = "simulated";
    $tokensUsed = 0;
    $responseTime = 0;
    $ensembleResponses = [];
    
    if ($isEnsemble) {
        // Ensemble mode - only call Qwen API
        $ensembleResult = handleQwenOnlyMode($message);
        $response = $ensembleResult['content'];
        $ensembleResponses = $ensembleResult['responses'];
        $source = 'ensemble';
        $tokensUsed = strlen($message) + strlen($response);
        $responseTime = 2; // Ensemble takes longer
    } else {
        // Kiểm tra nếu cần sử dụng QwenService làm mặc định
        if ($useQwenDefault) {
            // Sử dụng QwenService làm dịch vụ chat mặc định
            $qwenResult = handleQwenDefaultChat($message, $model);
            
            error_log("Chat API Debug - Qwen Default Result: " . json_encode($qwenResult));
            
            if ($qwenResult['success']) {
                $response = $qwenResult['content'];
                $source = 'qwen_default';
                $tokensUsed = strlen($message) + strlen($response);
                $responseTime = $qwenResult['response_time'] ?? 1;
                
                // Kiểm tra nếu response rỗng
                if (empty($response) || $response === '') {
                    $response = "Xin chào! Tôi là AI assistant của Thư Viện AI. Hiện tại Qwen service đang được cập nhật, vui lòng thử lại sau.";
                    $source = 'qwen_default_fallback';
                }
            } else {
                // Fallback to simulated response
                $response = generateSimulatedResponse($message, $model, $mode);
                $source = 'simulated_fallback';
            }
        } else {
            // Ưu tiên sử dụng Key4U API khi có API key
            if ($apiKey && $apiKey !== 'your_key4u_api_key_here') {
                try {
                    $response = callKey4UAPI($message, $model, $apiKey);
                    $source = 'key4u';
                    $tokensUsed = strlen($message) + strlen($response);
                    $responseTime = 1; // Simulate response time
                } catch (Exception $e) {
                    error_log("Key4U API Error: " . $e->getMessage());
                    
                    // Fallback to Qwen service
                    $qwenResult = tryQwenService($message, $model);
                    if ($qwenResult['success']) {
                        $response = $qwenResult['content'];
                        $source = 'qwen_fallback';
                        $tokensUsed = strlen($message) + strlen($response);
                        $responseTime = $qwenResult['response_time'] ?? 1;
                    } else {
                        // Final fallback to simulated response
                        $response = generateSimulatedResponse($message, $model, $mode);
                        $source = 'simulated_error';
                    }
                }
            } else {
                // Không có API key - thử Qwen service trước
                $qwenResult = tryQwenService($message, $model);
                
                error_log("Chat API Debug - Qwen Result: " . json_encode($qwenResult));
                
                if ($qwenResult['success']) {
                    $response = $qwenResult['content'];
                    $source = 'qwen';
                    $tokensUsed = strlen($message) + strlen($response);
                    $responseTime = $qwenResult['response_time'] ?? 1;
                } else {
                    // Final fallback to simulated response
                    $response = generateSimulatedResponse($message, $model, $mode);
                    $source = 'simulated';
                }
            }
        }
    }

    // Trừ credit sau khi đã xử lý câu hỏi thành công (bỏ qua nếu là admin)
    $newCredits = null;
    if ($userId !== null && $userCredits !== null && $userModel !== null) {
        if ($isAdmin) {
            // Admin không bị trừ credit
            $newCredits = $userCredits;
            error_log("✅ Admin user detected. Credit deduction skipped. User ID: {$userId}, Credits: {$userCredits}");
        } else {
            // Chỉ trừ credit cho user thường
            error_log("Chat API Debug - Attempting to deduct credit. User ID: {$userId}, Current credits: {$userCredits}");
            
            // Trừ 1 credit
            $deducted = $userModel->deductCredits($userId, 1);
            
            if ($deducted) {
                // Lấy credit mới sau khi trừ
                $updatedUser = $userModel->getById($userId);
                $newCredits = intval($updatedUser['credits'] ?? 0);
                
                error_log("✅ Credit deducted successfully. User ID: {$userId}, Old credits: {$userCredits}, New credits: {$newCredits}");
            } else {
                error_log("❌ Warning: Failed to deduct credit for user ID: {$userId}. Possible reasons: insufficient credits or database error.");
                // Nếu trừ không thành công, giữ nguyên credit cũ
                $newCredits = $userCredits;
            }
        }
    } else {
        error_log("Chat API Debug - Credit deduction skipped. userId: " . ($userId ?? 'null') . ", userCredits: " . ($userCredits ?? 'null') . ", userModel: " . ($userModel ? 'set' : 'null'));
    }
    
    // Debug logging
    error_log("Chat API Debug - Response: " . $response);
    error_log("Chat API Debug - Source: " . $source);
    
    // Return success response
    http_response_code(200);
    $responseData = [
        'content' => $response,
        'model' => $isEnsemble ? 'ensemble' : $model,
        'mode' => $mode,
        'source' => $source,
        'tokens_used' => $tokensUsed,
        'response_time' => $responseTime,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Add ensemble responses if available
    if ($isEnsemble && !empty($ensembleResponses)) {
        $responseData['ensemble_responses'] = $ensembleResponses;
    }
    
    // Thêm thông tin credit vào response nếu có user
    if ($userId !== null) {
        if (isset($newCredits)) {
            $responseData['credits_remaining'] = $newCredits;
        } elseif ($userCredits !== null) {
            $responseData['credits_remaining'] = $userCredits - 1;
        }
    }
    
    $finalResponse = [
        'success' => true,
        'data' => $responseData
    ];
    
    error_log("Chat API Debug - Final Response: " . json_encode($finalResponse));
    
    // Clear any output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode($finalResponse);
    flush();

} catch (Exception $e) {
    error_log("Chat API Exception: " . $e->getMessage());
    error_log("Chat API Exception Trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => 'API_ERROR'
    ]);
}


/**
 * Try Qwen Service first
 */
function tryQwenService($message, $model) {
    try {
        $qwenServicePath = __DIR__ . '/../services/QwenService.php';
        if (file_exists($qwenServicePath)) {
            // Suppress errors during include
            $oldErrorReporting = error_reporting(0);
            $includeResult = include_once $qwenServicePath;
            error_reporting($oldErrorReporting);
            
            if ($includeResult && class_exists('QwenService')) {
                $qwenService = new QwenService();
                
                // Sử dụng defaultChat method
                $qwenResponse = $qwenService->defaultChat($message, ['model' => $model]);
                
                // Debug logging
                error_log("Qwen Service Debug - Response: " . json_encode($qwenResponse));
                
                if ($qwenResponse && isset($qwenResponse['content'])) {
                    return [
                        'success' => true,
                        'content' => $qwenResponse['content'],
                        'response_time' => $qwenResponse['response_time'] ?? 1
                    ];
                }
            } else {
                error_log("Qwen Service Error: Class not found or include failed");
            }
        } else {
            error_log("Qwen Service Error: File not found: " . $qwenServicePath);
        }
    } catch (Exception $e) {
        error_log("Qwen Service Error: " . $e->getMessage());
    }
    
    return ['success' => false];
}

/**
 * Handle Qwen Only Mode - Only call Qwen API
 */
function handleQwenOnlyMode($message) {
    $responses = [];
    $errors = [];
    
    // Try Qwen API only
    try {
        $qwenServicePath = __DIR__ . '/../services/QwenService.php';
        if (file_exists($qwenServicePath)) {
            // Suppress errors during include
            $oldErrorReporting = error_reporting(0);
            $includeResult = include_once $qwenServicePath;
            error_reporting($oldErrorReporting);
            
            if ($includeResult && class_exists('QwenService')) {
                $qwenService = new QwenService();
                $qwenResponse = $qwenService->chat($message, 'qwen3-235b-a22b');
                
                if ($qwenResponse['success']) {
                    $responses['qwen'] = [
                        'provider' => 'Qwen',
                        'model' => 'qwen3-235b-a22b',
                        'content' => $qwenResponse['content'],
                        'success' => true
                    ];
                } else {
                    $responses['qwen'] = [
                        'provider' => 'Qwen',
                        'model' => 'qwen3-235b-a22b',
                        'content' => 'Lỗi từ Qwen API',
                        'success' => false
                    ];
                }
            } else {
                $responses['qwen'] = [
                    'provider' => 'Qwen',
                    'model' => 'qwen3-235b-a22b',
                    'content' => 'Không thể load QwenService class',
                    'success' => false
                ];
            }
        } else {
            $responses['qwen'] = [
                'provider' => 'Qwen',
                'model' => 'qwen3-235b-a22b',
                'content' => 'QwenService.php không tồn tại',
                'success' => false
            ];
        }
    } catch (Exception $e) {
        $errors['qwen'] = $e->getMessage();
        $responses['qwen'] = [
            'provider' => 'Qwen',
            'model' => 'qwen3-235b-a22b',
            'content' => 'Lỗi kết nối: ' . $e->getMessage(),
            'success' => false
        ];
    } catch (Error $e) {
        $errors['qwen'] = $e->getMessage();
        $responses['qwen'] = [
            'provider' => 'Qwen',
            'model' => 'qwen3-235b-a22b',
            'content' => 'Lỗi PHP: ' . $e->getMessage(),
            'success' => false
        ];
    }
    
    // Combine responses
    $combinedResponse = "🤖 **QWEN AI RESPONSE**\n\n";
    
    foreach ($responses as $provider => $response) {
        $status = $response['success'] ? '✅' : '❌';
        $combinedResponse .= "**{$status} {$response['provider']} ({$response['model']}):**\n";
        $combinedResponse .= $response['content'] . "\n\n";
    }
    
    // Add summary
    $successCount = count(array_filter($responses, function($r) { return $r['success']; }));
    if ($successCount === 0) {
        $combinedResponse .= "⚠️ **Qwen AI gặp lỗi. Đang sử dụng response mô phỏng.**\n\n";
        $combinedResponse .= "**🤖 Qwen AI (Simulated):**\n";
        $combinedResponse .= "Xin chào! Tôi là Qwen AI. Hiện tại tôi đang gặp vấn đề kết nối, nhưng tôi vẫn có thể giúp bạn. Bạn có câu hỏi gì không?\n\n";
        $combinedResponse .= "ℹ️ **Lưu ý:** Để sử dụng Qwen AI thật, vui lòng kiểm tra cookies và API endpoint.";
    } else {
        $combinedResponse .= "✨ **Qwen AI hoạt động tốt!**";
    }
    
    return [
        'content' => $combinedResponse,
        'responses' => $responses
    ];
}

/**
 * Handle Ensemble Mode - Call both Key4U and Qwen APIs
 */
function handleEnsembleMode($message, $apiKey) {
    
    $responses = [];
    $errors = [];
    
    // Try Key4U API first
    if ($apiKey && $apiKey !== 'your_key4u_api_key_here') {
        try {
            $key4uResponse = callKey4UAPI($message, 'gpt-4-turbo', $apiKey);
            $responses['key4u'] = [
                'provider' => 'Key4U',
                'model' => 'gpt-4-turbo',
                'content' => $key4uResponse,
                'success' => true
            ];
        } catch (Exception $e) {
            $errors['key4u'] = $e->getMessage();
            $responses['key4u'] = [
                'provider' => 'Key4U',
                'model' => 'gpt-4-turbo',
                'content' => 'Lỗi kết nối: ' . $e->getMessage(),
                'success' => false
            ];
        }
    } else {
        $responses['key4u'] = [
            'provider' => 'Key4U',
            'model' => 'gpt-4-turbo',
            'content' => 'API key chưa được cấu hình',
            'success' => false
        ];
    }
    
    // Try Qwen API
    try {
        $qwenServicePath = __DIR__ . '/../services/QwenService.php';
        if (file_exists($qwenServicePath)) {
            // Suppress errors during include
            $oldErrorReporting = error_reporting(0);
            $includeResult = include_once $qwenServicePath;
            error_reporting($oldErrorReporting);
            
            if ($includeResult && class_exists('QwenService')) {
                $qwenService = new QwenService();
                $qwenResponse = $qwenService->chat($message, 'qwen3-235b-a22b');
                
                if ($qwenResponse['success']) {
                    $responses['qwen'] = [
                        'provider' => 'Qwen',
                        'model' => 'qwen3-235b-a22b',
                        'content' => $qwenResponse['content'],
                        'success' => true
                    ];
                } else {
                    $responses['qwen'] = [
                        'provider' => 'Qwen',
                        'model' => 'qwen3-235b-a22b',
                        'content' => 'Lỗi từ Qwen API',
                        'success' => false
                    ];
                }
            } else {
                $responses['qwen'] = [
                    'provider' => 'Qwen',
                    'model' => 'qwen3-235b-a22b',
                    'content' => 'Không thể load QwenService class',
                    'success' => false
                ];
            }
        } else {
            $responses['qwen'] = [
                'provider' => 'Qwen',
                'model' => 'qwen3-235b-a22b',
                'content' => 'QwenService.php không tồn tại',
                'success' => false
            ];
        }
    } catch (Exception $e) {
        $errors['qwen'] = $e->getMessage();
        $responses['qwen'] = [
            'provider' => 'Qwen',
            'model' => 'qwen3-235b-a22b',
            'content' => 'Lỗi kết nối: ' . $e->getMessage(),
            'success' => false
        ];
    } catch (Error $e) {
        $errors['qwen'] = $e->getMessage();
        $responses['qwen'] = [
            'provider' => 'Qwen',
            'model' => 'qwen3-235b-a22b',
            'content' => 'Lỗi PHP: ' . $e->getMessage(),
            'success' => false
        ];
    }
    
    // Store responses for detailed return (return as part of response)
    
    // Combine responses
    $combinedResponse = "🤖 **ENSEMBLE AI RESPONSE**\n\n";
    
    foreach ($responses as $provider => $response) {
        $status = $response['success'] ? '✅' : '❌';
        $combinedResponse .= "**{$status} {$response['provider']} ({$response['model']}):**\n";
        $combinedResponse .= $response['content'] . "\n\n";
    }
    
    // Add summary if both failed
    $successCount = count(array_filter($responses, function($r) { return $r['success']; }));
    if ($successCount === 0) {
        $combinedResponse .= "⚠️ **Tất cả AI models đều gặp lỗi. Vui lòng kiểm tra cấu hình API.**";
    } elseif ($successCount === 1) {
        $combinedResponse .= "ℹ️ **Chỉ có 1 AI model hoạt động. Vui lòng kiểm tra cấu hình cho model còn lại.**";
    } else {
        $combinedResponse .= "✨ **Cả 2 AI models đều hoạt động tốt!**";
    }
    
    return [
        'content' => $combinedResponse,
        'responses' => $responses
    ];
}

/**
 * Call Key4U API
 */
function callKey4UAPI($message, $model, $apiKey) {
    $url = 'https://api.key4u.shop/v1/chat/completions';
    
    $data = [
        'model' => $model,
        'messages' => [
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'max_tokens' => 1000,
        'temperature' => 0.7,
        'stream' => false
    ];
    
    // Use cURL instead of file_get_contents for better HTTPS support
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($result === false || !empty($error)) {
        throw new Exception('Failed to connect to Key4U API: ' . $error);
    }
    
    if ($httpCode !== 200) {
        throw new Exception('Key4U API returned HTTP ' . $httpCode);
    }
    
    $response = json_decode($result, true);
    
    if (!$response || !isset($response['choices'][0]['message']['content'])) {
        throw new Exception('Invalid response from Key4U API');
    }
    
    return $response['choices'][0]['message']['content'];
}

/**
 * Generate simulated response (fallback)
 */
function generateSimulatedResponse($message, $model, $mode) {
    $message = strtolower($message);
    
    // Check if user is asking about API key
    if (strpos($message, 'api key') !== false || strpos($message, 'key4u') !== false) {
        return "🔑 Để sử dụng AI models thật, bạn cần cấu hình KEY4U_API_KEY trong file config.env. Hiện tại tôi đang sử dụng response mô phỏng. Để kích hoạt AI thật, vui lòng thêm API key vào config.env và restart server.";
    }
    
    // Greeting responses
    if (strpos($message, 'xin chào') !== false || strpos($message, 'hello') !== false) {
        return "Xin chào! Tôi là AI assistant của Thư Viện AI. Hiện tại tôi đang chạy ở chế độ mô phỏng. Để kết nối với AI models thật, vui lòng cấu hình KEY4U_API_KEY trong config.env.";
    }
    
    // Default response
    return "🤖 Tôi đang chạy ở chế độ mô phỏng. Để sử dụng AI models thật (GPT-4, Claude, Gemini...), vui lòng cấu hình KEY4U_API_KEY trong file config.env. Sau đó restart server để kích hoạt kết nối thật đến các AI models.";
}

/**
 * Handle Qwen default chat using QwenService
 */
function handleQwenDefaultChat($message, $model) {
    try {
        // Load QwenService
        require_once __DIR__ . '/../services/QwenService.php';
        
        $qwenService = new QwenService();
        
        // Sử dụng defaultChat method của QwenService
        $result = $qwenService->defaultChat($message, [
            'model' => $model
        ]);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Qwen Default Chat Error: " . $e->getMessage());
        
        // Fallback response
        return [
            'success' => false,
            'content' => "Xin chào! Tôi là AI assistant của Thư Viện AI. Có lỗi khi kết nối với Qwen service: " . $e->getMessage(),
            'model' => $model,
            'provider' => 'qwen',
            'response_time' => 1
        ];
    }
}
?>
