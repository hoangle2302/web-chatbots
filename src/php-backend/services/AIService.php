<?php
/**
 * AI Service - Xử lý các request đến AI models
 */

require_once __DIR__ . '/../config/Config.php';

class AIService {
    private $config;
    private $key4uConfig;
    
    public function __construct() {
        $this->config = new Config();
        $this->key4uConfig = $this->config->getKey4UConfig();
    }
    
    /**
     * Xử lý single AI request
     */
    public function processSingle($message, $model, $document = null) {
        $context = $this->buildContext($message, $document);
        
        $response = $this->callKey4UAPI($model, $context);
        
        return [
            'content' => $response['content'],
            'model' => $model,
            'tokens_used' => $response['usage']['total_tokens'] ?? 0,
            'mode' => 'single'
        ];
    }
    
    /**
     * Xử lý ensemble request (4 AI hàng đầu)
     */
    public function processEnsemble($message, $document = null) {
        $ensembleConfig = $this->config->getEnsembleConfig();
        $topModels = $ensembleConfig['top_models'];
        
        $context = $this->buildContext($message, $document);
        $responses = [];
        
        // Gọi song song 4 AI
        $promises = [];
        foreach ($topModels as $model) {
            $promises[] = $this->callKey4UAPIAsync($model, $context);
        }
        
        // Chờ tất cả responses
        foreach ($promises as $index => $promise) {
            try {
                $response = $promise;
                $responses[] = [
                    'model' => $topModels[$index],
                    'content' => $response['content'],
                    'tokens' => $response['usage']['total_tokens'] ?? 0
                ];
            } catch (Exception $e) {
                error_log("Ensemble error for model {$topModels[$index]}: " . $e->getMessage());
            }
        }
        
        // Kết hợp responses
        $combinedContent = $this->combineEnsembleResponses($responses);
        
        return [
            'content' => $combinedContent,
            'models' => $topModels,
            'responses' => $responses,
            'mode' => 'ensemble'
        ];
    }
    
    /**
     * Xử lý distributed request (28 AI phân công)
     */
    public function processDistributed($message, $document = null) {
        $context = $this->buildContext($message, $document);
        
        // Tạo kế hoạch phân công
        $taskPlan = $this->createDistributedPlan($message, $document);
        
        $results = [];
        $totalTokens = 0;
        
        foreach ($taskPlan as $task) {
            try {
                $response = $this->callKey4UAPI($task['model'], $task['prompt']);
                $results[] = [
                    'task' => $task['task'],
                    'model' => $task['model'],
                    'content' => $response['content'],
                    'tokens' => $response['usage']['total_tokens'] ?? 0
                ];
                $totalTokens += $response['usage']['total_tokens'] ?? 0;
            } catch (Exception $e) {
                error_log("Distributed task error: " . $e->getMessage());
            }
        }
        
        // Tổng hợp kết quả
        $finalContent = $this->combineDistributedResults($results);
        
        return [
            'content' => $finalContent,
            'tasks' => $results,
            'total_tokens' => $totalTokens,
            'mode' => 'distributed'
        ];
    }
    
    /**
     * Gọi Key4U API
     */
    private function callKey4UAPI($model, $messages) {
        $url = $this->key4uConfig['api_url'];
        $apiKey = $this->key4uConfig['api_key'];
        
        if (empty($apiKey)) {
            throw new Exception('Key4U API key not configured');
        }
        
        // Use default model if specified model is not available
        $defaultModel = $this->key4uConfig['default_model'] ?? 'gpt-4-turbo';
        $finalModel = $model;
        
        // Check if model is available, fallback to default
        $availableModels = ['gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo'];
        if (!in_array($model, $availableModels)) {
            $finalModel = $defaultModel;
            error_log("Model $model not available, using $finalModel instead");
        }
        
        $data = [
            'model' => $finalModel,
            'messages' => $messages,
            'temperature' => $this->key4uConfig['default_temperature'],
            'max_tokens' => $this->key4uConfig['default_max_tokens']
        ];
        
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        if ($response === false) {
            $curlError = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: " . $curlError);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("API request failed with code: $httpCode. Response: $response");
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            throw new Exception("API error: " . $result['error']['message']);
        }
        
        return $result['choices'][0]['message'];
    }
    
    /**
     * Gọi Key4U API async (simulation)
     */
    private function callKey4UAPIAsync($model, $messages) {
        // Trong thực tế, đây sẽ là async call
        // Hiện tại chỉ là wrapper cho sync call
        return $this->callKey4UAPI($model, $messages);
    }
    
    /**
     * Xây dựng context cho AI
     */
    private function buildContext($message, $document = null) {
        $context = [
            [
                'role' => 'system',
                'content' => 'Bạn là trợ lý AI thông minh của Thư Viện AI. Hãy trả lời câu hỏi một cách chính xác và hữu ích.'
            ]
        ];
        
        if ($document) {
            $context[] = [
                'role' => 'system',
                'content' => "Tài liệu tham khảo:\n" . $document['content']
            ];
        }
        
        $context[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        return $context;
    }
    
    /**
     * Kết hợp responses từ ensemble
     */
    private function combineEnsembleResponses($responses) {
        if (empty($responses)) {
            return "Xin lỗi, không thể xử lý yêu cầu của bạn.";
        }
        
        $content = "## Kết quả từ " . count($responses) . " AI:\n\n";
        
        foreach ($responses as $index => $response) {
            $content .= "### AI " . ($index + 1) . " ({$response['model']}):\n";
            $content .= $response['content'] . "\n\n";
        }
        
        $content .= "---\n";
        $content .= "*Kết quả được tổng hợp từ " . count($responses) . " mô hình AI hàng đầu.*";
        
        return $content;
    }
    
    /**
     * Tạo kế hoạch phân công cho distributed mode
     */
    private function createDistributedPlan($message, $document) {
        $models = $this->config->getAvailableModels();
        $plan = [];
        
        // Phân tích câu hỏi
        $analysisPrompt = "Phân tích câu hỏi này và chia thành các nhiệm vụ con: " . $message;
        $plan[] = [
            'task' => 'analyze',
            'model' => 'gpt-4-turbo',
            'prompt' => $this->buildContext($analysisPrompt, $document)
        ];
        
        // Tìm kiếm thông tin
        $searchPrompt = "Tìm kiếm thông tin liên quan đến: " . $message;
        $plan[] = [
            'task' => 'search',
            'model' => 'claude-3-5-sonnet',
            'prompt' => $this->buildContext($searchPrompt, $document)
        ];
        
        // Xử lý chuyên môn
        $expertPrompt = "Đưa ra phân tích chuyên sâu về: " . $message;
        $plan[] = [
            'task' => 'expert_analysis',
            'model' => 'gemini-2-5-pro',
            'prompt' => $this->buildContext($expertPrompt, $document)
        ];
        
        // Kiểm duyệt và tổng hợp
        $reviewPrompt = "Kiểm duyệt và tổng hợp các kết quả về: " . $message;
        $plan[] = [
            'task' => 'review',
            'model' => 'deepseek-v3',
            'prompt' => $this->buildContext($reviewPrompt, $document)
        ];
        
        return $plan;
    }
    
    /**
     * Kết hợp kết quả từ distributed mode
     */
    private function combineDistributedResults($results) {
        if (empty($results)) {
            return "Xin lỗi, không thể xử lý yêu cầu của bạn.";
        }
        
        $content = "## Kết quả xử lý phân tán:\n\n";
        
        foreach ($results as $result) {
            $content .= "### {$result['task']} ({$result['model']}):\n";
            $content .= $result['content'] . "\n\n";
        }
        
        $content .= "---\n";
        $content .= "*Kết quả được xử lý bởi " . count($results) . " AI chuyên biệt.*";
        
        return $content;
    }
}
?>
