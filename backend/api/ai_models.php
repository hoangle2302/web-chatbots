<?php
/**
 * AI Models API
 * Handles AI model management and selection
 */

require_once '../config/database.php';
require_once '../includes/auth_functions.php';

session_start();
corsHeaders();
handlePreflight();

// Define constants if not defined
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        getAIModels();
        break;
    case 'select':
        selectAIModel();
        break;
    case 'add':
        addCustomModel();
        break;
    case 'remove':
        removeCustomModel();
        break;
    case 'current':
        getCurrentModel();
        break;
    default:
        sendResponse(false, 'Invalid action', 400);
}

function getAIModels() {
    try {
        // Read models from file
        $modelsFile = '../frontend/list_AI.txt';
        $models = [];
        
        if (file_exists($modelsFile)) {
            $content = file_get_contents($modelsFile);
            $lines = explode("\n", trim($content));
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $models[] = [
                        'id' => $line,
                        'name' => formatModelName($line),
                        'category' => categorizeModel($line),
                        'description' => getModelDescription($line)
                    ];
                }
            }
        }
        
        // Get user's custom models if logged in
        if (isUserLoggedIn()) {
            $customModels = getUserCustomModels();
            $models = array_merge($models, $customModels);
        }
        
        // Group models by category
        $groupedModels = [];
        foreach ($models as $model) {
            $category = $model['category'];
            if (!isset($groupedModels[$category])) {
                $groupedModels[$category] = [];
            }
            $groupedModels[$category][] = $model;
        }
        
        sendResponse(true, 'AI models loaded successfully', 200, [
            'models' => $models,
            'grouped' => $groupedModels,
            'total' => count($models)
        ]);
        
    } catch (Exception $e) {
        error_log("Get AI models error: " . $e->getMessage());
        sendResponse(false, 'Lỗi khi tải danh sách AI models', 500);
    }
}

function selectAIModel() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', 405);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $modelId = sanitizeInput($input['model_id'] ?? '');
    
    if (empty($modelId)) {
        sendResponse(false, 'Model ID không được để trống', 400);
        return;
    }
    
    try {
        // Store in session for guest users
        $_SESSION['selected_ai_model'] = $modelId;
        
        // Store in database for logged in users
        if (isUserLoggedIn()) {
            $db = new Database();
            $conn = $db->getConnection();
            $userId = getCurrentUserId();
            
            // Update user's preferred model
            $stmt = $conn->prepare("UPDATE users SET preferred_ai_model = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$modelId, $userId]);
            
            // Log the selection
            logActivity('select_ai_model', "Model: $modelId");
        }
        
        sendResponse(true, 'AI model đã được chọn', 200, [
            'selected_model' => $modelId,
            'model_name' => formatModelName($modelId)
        ]);
        
    } catch (Exception $e) {
        error_log("Select AI model error: " . $e->getMessage());
        sendResponse(false, 'Lỗi khi chọn AI model', 500);
    }
}

function getCurrentModel() {
    $currentModel = 'claude-3-5-sonnet-latest'; // default
    
    if (isUserLoggedIn()) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $userId = getCurrentUserId();
            
            $stmt = $conn->prepare("SELECT preferred_ai_model FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if ($user && !empty($user['preferred_ai_model'])) {
                $currentModel = $user['preferred_ai_model'];
            }
        } catch (Exception $e) {
            error_log("Get current model error: " . $e->getMessage());
        }
    } else {
        // Get from session for guest users
        $currentModel = $_SESSION['selected_ai_model'] ?? $currentModel;
    }
    
    sendResponse(true, 'Current model retrieved', 200, [
        'current_model' => $currentModel,
        'model_name' => formatModelName($currentModel)
    ]);
}

function addCustomModel() {
    requireLogin();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', 405);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $modelId = sanitizeInput($input['model_id'] ?? '');
    $modelName = sanitizeInput($input['model_name'] ?? '');
    $description = sanitizeInput($input['description'] ?? '');
    
    if (empty($modelId) || empty($modelName)) {
        sendResponse(false, 'Model ID và tên không được để trống', 400);
        return;
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $userId = getCurrentUserId();
        
        // Check if model already exists
        $stmt = $conn->prepare("SELECT id FROM user_ai_models WHERE user_id = ? AND model_id = ?");
        $stmt->execute([$userId, $modelId]);
        if ($stmt->fetch()) {
            sendResponse(false, 'Model đã tồn tại', 409);
            return;
        }
        
        // Insert custom model
        $stmt = $conn->prepare("INSERT INTO user_ai_models (user_id, model_id, model_name, description, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $modelId, $modelName, $description]);
        
        logActivity('add_custom_model', "Model: $modelId");
        
        sendResponse(true, 'Custom AI model đã được thêm', 201, [
            'model_id' => $modelId,
            'model_name' => $modelName
        ]);
        
    } catch (Exception $e) {
        error_log("Add custom model error: " . $e->getMessage());
        sendResponse(false, 'Lỗi khi thêm custom model', 500);
    }
}

function removeCustomModel() {
    requireLogin();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', 405);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $modelId = sanitizeInput($input['model_id'] ?? '');
    
    if (empty($modelId)) {
        sendResponse(false, 'Model ID không được để trống', 400);
        return;
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $userId = getCurrentUserId();
        
        $stmt = $conn->prepare("DELETE FROM user_ai_models WHERE user_id = ? AND model_id = ?");
        $result = $stmt->execute([$userId, $modelId]);
        
        if ($stmt->rowCount() > 0) {
            logActivity('remove_custom_model', "Model: $modelId");
            sendResponse(true, 'Custom AI model đã được xóa', 200);
        } else {
            sendResponse(false, 'Không tìm thấy model', 404);
        }
        
    } catch (Exception $e) {
        error_log("Remove custom model error: " . $e->getMessage());
        sendResponse(false, 'Lỗi khi xóa custom model', 500);
    }
}

// Helper functions
function getUserCustomModels() {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $userId = getCurrentUserId();
        
        $stmt = $conn->prepare("SELECT model_id, model_name, description FROM user_ai_models WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $customModels = $stmt->fetchAll();
        
        return array_map(function($model) {
            return [
                'id' => $model['model_id'],
                'name' => $model['model_name'],
                'category' => 'Custom',
                'description' => $model['description'] ?: 'Custom AI model',
                'is_custom' => true
            ];
        }, $customModels);
        
    } catch (Exception $e) {
        error_log("Get user custom models error: " . $e->getMessage());
        return [];
    }
}

function categorizeModel($modelId) {
    if (strpos($modelId, 'claude') !== false) {
        return 'Claude';
    } elseif (strpos($modelId, 'gpt') !== false) {
        return 'GPT';
    } elseif (strpos($modelId, 'mj_') !== false || strpos($modelId, 'midjourney') !== false) {
        return 'Midjourney';
    } elseif (strpos($modelId, 'gemini') !== false) {
        return 'Gemini';
    } elseif (strpos($modelId, 'llama') !== false) {
        return 'Llama';
    } else {
        return 'Other';
    }
}

function formatModelName($modelId) {
    // Special cases
    $specialNames = [
        'claude-3-5-sonnet-latest' => 'Claude 3.5 Sonnet (Latest)',
        'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet (Oct 2024)',
        'claude-3-haiku-20240307' => 'Claude 3 Haiku',
        'gpt-4' => 'GPT-4',
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        'gemini-pro' => 'Gemini Pro'
    ];
    
    if (isset($specialNames[$modelId])) {
        return $specialNames[$modelId];
    }
    
    // Generic formatting
    return ucwords(str_replace(['-', '_'], ' ', $modelId));
}

function getModelDescription($modelId) {
    $descriptions = [
        'claude-3-5-sonnet-latest' => 'Most capable Claude model with excellent reasoning and creativity',
        'claude-3-haiku-20240307' => 'Fast and efficient Claude model for quick responses',
        'gpt-4' => 'OpenAI\'s most advanced language model',
        'gpt-3.5-turbo' => 'Fast and cost-effective GPT model',
        'gemini-pro' => 'Google\'s advanced multimodal AI model'
    ];
    
    return $descriptions[$modelId] ?? 'Advanced AI language model';
}
?>