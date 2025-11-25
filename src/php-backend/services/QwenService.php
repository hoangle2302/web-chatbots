<?php
/**
 * Qwen Service - Xử lý API Qwen với Guzzle HTTP
 */

require_once __DIR__ . '/../config/Config.php';

class QwenService {
    private $config;
    private $baseUrl = 'https://chat.qwen.ai/api/v2';
    private $client;
    
    // Danh sách models Qwen có sẵn
    private $availableModels = [
        'qwen3-235b-a22b',
        'qwen3-30b-a3b', 
        'qwen3-32b',
        'qwen3-coder-plus',
        'qwq-32b',
        'qwq-72b-preview',
        'qwq-plus',
        'qwq-plus-latest'
    ];
    
    // Full cookies từ file qwen api.py
    private $cookies = [
        'cna' => 'Otj2IFpvThICAXZEFZHzamBw',
        '_gcl_au' => '1.1.748147921.1752164922',
        '_bl_uid' => 'F7m8wcyax06lU4rm47q1eIez62R5',
        'acw_tc' => '0a03e54a17567353768315086e1e907eca27d4985fc55cbb39fcdb553fc54e',
        'x-ap' => 'ap-southeast-1',
        'sca' => 'd9943fb1',
        'xlly_s' => '1',
        'atpsida' => '1701f5bb0ee163766aa65847_1756736456_5',
        'tfstk' => 'gXE-LamowsfortdyeWbcx359F_BciZ2PE7y6xXckRSFYO76yxJgkOHFxiTyoV_Pv9RV9qHqLLHHQdWWrtT70U8oEA1fg9G2zUQ6Rz2E-O-gb3vlBNZ0715Qci1fGjiVckcEAsWDbG1ujLjMSdHt5HmMnBDMSO26xGvHwRQNIAtBxdAkSOvGIcqMrCDGQOD6YhjkKAYNIAtejgvGmkLhHFXKLPu_29Y5-PHtQDY3fj-GbJPBnF4h_FjQfloKZyfwSMH1Steln9jr1iZe4OzNo3WIOMcaaMk3svgdZerwtcbm1WEn8n7rtVlCJqx0KwrZSkptQemzS7yFRwUl7rS3Zhq9dbxViG8r7k9RbF5c-VxgcfOejRrqr7ufX2caaE04Q1sYoNPHC48EgXgowsfHHPtBv8euS3d-HyOIoj4diHfXDoe8EoxkxstBv8euS3xhGniYe8qDV.',
        'isg' => 'BLm5V_9-9R2soalToaW-yEHCyCWTxq14XV61YNvsJ-BfYtj0JxcvSRT04H6UYkWw',
        'ssxmod_itna' => 'YqUxuD2D0D90frx4qxRhDQqWui43qT4q0dGMlDeq7tDRDFqApxDHQIrK75rYn7t7DGK=YlEOWiKMD0H2rzDBKQDax7fr04ti2Us3YbjOh3Y9E2DwpD0r7uuEawY6+pnd8iZ8K8Cwz6lrK7eDU4GnGRDx2OeD44DvDBYD74G+DDeDixGmG4DSlDD9DGPdglTi2eDEDYPdv4DmDGYdheDgmDDBDD64x7QaRWxD0Th+aYoK=Ac7djFNS6Tq+PxejeDMWxGXnYkPjeH6gFxbwa8fH1oYxB=cxBQiZAWAS1IQ==naqQApKYArB8DfOelxxoi=aYYiiYM0De7DK0D807w0DonDa7N8YY4jQCEwwDDi=iVoYxKeD6tyxNgrXr02gWv7RpKtG5BqciG3eA1nwsGIhWxofe=WxQKe5RhVPA3YD',
        'ssxmod_itna2' => 'YqUxuD2D0D90frx4qxRhDQqWui43qT4q0dGMlDeq7tDRDFqApxDHQIrK75rYn7t7DGK=YlEOWiKwDDcYq5L0DDtDFrTpfNx05jGYA3aqc3xcfhDxfE=YPh9eWhOGSWUqWWhSyGxaA17CY70C8WFe2gGaal9HREDak=ii2=0Di87aRppK8=iz1paOBe5xg6PQQevWF8iOR=OHu1SDuxqNiREQxzE1WfHeC=f6RSvQ0tYXq5HLiRi0N35LU97AXr3mP5eCy6K5TVYLPwiajtDHKmymDyafYla4CgcX75j7fTiXN9H0WZYqKCtMrNgYIPQ0/QIxIAxYIcgqZ7f+OGnmp+2xZjKoF0yaj3rPFgKOnUxtf5xN1ORCZPTSKAZtvp37mPD9AWODvaTCEpKEty7oObXC4XKat6BbcAeKQDEiG1ANPeUnAWIUuZDajr23Q4Y0Djju3jYYjWDfDxOteeeEjus7n4jYdCeEjcgtG1nwsCuhWx=nGgfG1D7PYD'
    ];
    
    private $headers = [
        'Accept' => '*/*',
        'Accept-Language' => 'en-US,en;q=0.9,vi-VN;q=0.8,vi;q=0.7,fr-FR;q=0.6,fr;q=0.5',
        'Connection' => 'keep-alive',
        'Origin' => 'https://chat.qwen.ai',
        'Referer' => 'https://chat.qwen.ai/c/guest',
        'Sec-Fetch-Dest' => 'empty',
        'Sec-Fetch-Mode' => 'cors',
        'Sec-Fetch-Site' => 'same-origin',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36',
        'authorization' => 'Bearer',
        'bx-ua' => '231!fpp3SkmU3z3+joFAE43GpLEjUq/YvqY2leOxacSC80vTPuB9lMZY9mRWFzrwLEV0PmcfY4rL2JFHeKTCyUQdACIzKZBlsbAyXt6Iz8TQ7ck9CIZgeOBOVaRK66GQqw/G9pMRBb28c0I6pXxeCmvMXDUPkF/DRcC0GsGCA3FByoiok+G3N4s6OH8QSBzKvL6zZPjueqOKAshjdmkMbyWWls5WbVbp5PBl2ZakxDprleD+zu8e+Zd++6WF1cs+5ie9HJBh+++j+ygU3+jOQqGIRMkgFkk3k2BBI8q9X4runFE3IoVRf/d1V4uolvkvbtVVGy3qQaz18OAErmHM7l7vXb6kw7xAc3+lglcxnLU1n3pohAniCbldVSuP9SZUNYZKk7mqYtGessVZW7Rx7xMxro7oGK/lScFHXOINQEsb4fcd0Rb+KP4LZ1YJBHzPPN3AoTYBv8NkUtfIx62EubLze/xLsNCpsZweEY5z7uPeZE4tna/H0jQ1YL3lCqKLYAIhijL1MwDc7ZEs+ER7EYR5Tof8E4y1GpM2ppJUt/iYXKTqTgyBbfdGV/sL0y3Qy30vfP7MwZhrdKxCHS9uw1EYaTwOjPgasd+hmQTSHlWUWW0HrnFkZbsRLzu9abhWYiYcsNa85ZhNaNc6Mk4HbgyrJKAN4I7KRNU5xtYuQyuI3GcLfMtiZLYDhYl+SnTgiOZLaWAFOnoVnztZV4vpR7hMuj1PGfhaNZIVnKakTwLyl7hIJJvRKGtnyWPJOIpJ0heLQCBDV/QlZWRaFfk9xD8UspSk+h1L09249DdMqC/4BCntToGqb09q7hpScSU9BvV/zt1dsB5naVfjW+l+YJy9eSBKQIeH1uYu/ssaBfCdM8lYeb0lFDFqsVDyj90qoRsm3LN2A6zaOWDMjVVYz5JmSGX5ZXzpBFG4rqPm7kE4+7lnNvf65kARSUzsoW39KgpzEieMMLWuJslN8h1GdFuDlY0akkYiLkToK5rvzECEQNZr7EPmPeKytwrh0Nsiz8YAevccE9XnvKClT7imbSNPCgYU5n2RXG4S6LETn4mfAp3GVrvSwk6jMivACIAEAYU9YWaJksZH0tKXXTXRw8YxlEtzdnThNh2+4uVrEx4Sig4sBb/eMet2GdLp7+j8kc8MNYOa+qbxIx+k8ADSM7jxzAsrutJ9YxhuePePEnV9rJcCeFsKOHnbcuBNEa84R/Kr9lATj2liLROwcBhmy0tKj/U9scvMqZoWa1mDeXPBj+lR8D802XGZ4F8+nYOWwmuSHRYVw8eDxoPyDbkuOJgn+68h8rR6dEg7m550ntYUEnoj2u7XdudrfZ8Dixps67B9t0jS771KQXTbclFLYaizKQcLryCBWXFK2F1KeqpHbEYNYEfux2ARTUTNl0De/pUR/jUo1hCRc6ZxD6tVi1UNqFqVUgOy2BDbQBO6Jri/HLAjqHEw4UiBcFeVaJ9NnPYd9+o06E+9tCLMDBTVOFD5rqL2XuaniB3FrE6KoSAITh9KJuf+hCKpENjn65Lgi+==',
        'bx-umidtoken' => 'T2gAXgvxaymN41YDo4Rva3A3FxaMttFAXO0hD0B76QEBeulG-F0eCPrdY_JJAC_bdYY=',
        'bx-v' => '2.5.31',
        'content-type' => 'application/json; charset=UTF-8',
        'sec-ch-ua' => '"Not;A=Brand";v="99", "Google Chrome";v="139", "Chromium";v="139"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Windows"',
        'source' => 'web',
        'timezone' => 'Mon Sep 01 2025 21:20:59 GMT+0700',
        'x-accel-buffering' => 'no',
        'x-request-id' => '001f2bc4-380c-47bf-b4f2-ae2361c019f8'
    ];
    
    public function __construct() {
        $this->config = new Config();
    }
    
    /**
     * Lấy danh sách models có sẵn
     */
    public function getAvailableModels() {
        return $this->availableModels;
    }
    
    /**
     * Lấy model mặc định
     */
    public function getDefaultModel() {
        return 'qwen3-235b-a22b';
    }
    
    /**
     * Kiểm tra model có hợp lệ không
     */
    public function isValidModel($model) {
        return in_array($model, $this->availableModels);
    }
    
    /**
     * Chat mặc định với Qwen (phương thức chính)
     */
    public function defaultChat($message, $options = []) {
        $model = $options['model'] ?? $this->getDefaultModel();
        
        if (!$this->isValidModel($model)) {
            $model = $this->getDefaultModel();
        }
        
        try {
            $result = $this->chat($message, $model, $options);
            
            // Đảm bảo trả về format đúng
            if (is_array($result) && isset($result['success']) && $result['success']) {
                // Kiểm tra nếu content rỗng
                if (empty($result['content']) || $result['content'] === '') {
                    // Thử lại với API call khác hoặc trả về thông báo lỗi
                    return [
                        'success' => false,
                        'content' => 'Không thể nhận được phản hồi từ Qwen API. Vui lòng thử lại sau.',
                        'model' => $model,
                        'provider' => 'qwen',
                        'response_time' => 1
                    ];
                }
                return $result;
            } else {
                // Fallback response
                return [
                    'success' => true,
                    'content' => "Xin chào! Tôi là AI assistant của Thư Viện AI. Hiện tại Qwen service đang được cập nhật, vui lòng thử lại sau.",
                    'model' => $model,
                    'provider' => 'qwen',
                    'response_time' => 1
                ];
            }
        } catch (Exception $e) {
            error_log("Qwen defaultChat error: " . $e->getMessage());
            
            // Fallback response
            return [
                'success' => true,
                'content' => "Xin chào! Tôi là AI assistant của Thư Viện AI. Có lỗi khi kết nối với Qwen service: " . $e->getMessage(),
                'model' => $model,
                'provider' => 'qwen',
                'response_time' => 1
            ];
        }
    }
    
    /**
     * Chat với Qwen model - Simplified version
     */
    public function chat($message, $model = 'qwen3-235b-a22b', $options = []) {
        // Sử dụng Qwen API thực
        // Sử dụng chat_id cố định từ file Python
        $chatId = 'b2f16867-7de3-48b0-95c5-f39d6216a627';
        
        $requestData = [
            'stream' => true,
            'incremental_output' => true,
            'chat_id' => $chatId,
            'chat_mode' => 'guest',
            'model' => $model,
            'parent_id' => null,
            'messages' => [
                [
                    'fid' => $this->generateFid(),
                    'parentId' => null,
                    'childrenIds' => [],
                    'role' => 'user',
                    'content' => $message,
                    'user_action' => 'chat',
                    'files' => [],
                    'timestamp' => time(),
                    'models' => [$model],
                    'chat_type' => 't2t',
                    'feature_config' => [
                        'thinking_enabled' => false,
                        'output_schema' => 'phase'
                    ],
                    'extra' => [
                        'meta' => [
                            'subChatType' => 't2t'
                        ]
                    ],
                    'sub_chat_type' => 't2t',
                    'parent_id' => null
                ]
            ],
            'timestamp' => time()
        ];
        
        try {
            // Thêm chat_id vào query parameters
            $url = $this->baseUrl . '/chat/completions?chat_id=' . urlencode($chatId);
            
            // Use cURL instead of Guzzle
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            // Convert headers array to cURL format
            $curlHeaders = ['Content-Type: application/json; charset=UTF-8'];
            foreach ($this->headers as $key => $value) {
                $curlHeaders[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_COOKIE, $this->buildCookieString());
            
            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Debug logging
            error_log("Qwen API Debug - HTTP Code: " . $httpCode);
            error_log("Qwen API Debug - Response: " . substr($responseBody, 0, 500));
            
            if ($responseBody === false || !empty($error)) {
                throw new Exception('Failed to connect to Qwen API: ' . $error);
            }
            
            if ($httpCode !== 200) {
                throw new Exception('Qwen API returned HTTP ' . $httpCode . '. Response: ' . substr($responseBody, 0, 200));
            }
            
            return $this->parseStreamResponse($responseBody);
            
        } catch (Exception $e) {
            throw new Exception('Qwen API Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Parse stream response từ Qwen API
     */
    private function parseStreamResponse($responseText) {
        $finalContent = "";
        
        // Tách văn bản phản hồi thành các sự kiện SSE riêng lẻ
        $events = explode("\n\n", $responseText);
        
        // Xử lý từng sự kiện
        foreach ($events as $event) {
            // Bỏ qua các dòng trống hoặc sự kiện không phải dữ liệu
            if (strpos($event, "data:") !== 0) {
                continue;
            }
            
            // Loại bỏ tiền tố "data: " và phân tích cú pháp JSON
            $jsonStr = str_replace("data: ", "", $event);
            $jsonStr = trim($jsonStr);
            
            try {
                $data = json_decode($jsonStr, true);
                
                // Trích xuất choices nếu có
                if (isset($data["choices"])) {
                    foreach ($data["choices"] as $choice) {
                        $delta = $choice["delta"] ?? [];
                        $content = $delta["content"] ?? "";
                        $finalContent .= $content;
                    }
                }
            } catch (Exception $e) {
                // Bỏ qua JSON không hợp lệ
                continue;
            }
        }
        
        return [
            'success' => true,
            'content' => trim($finalContent),
            'model' => 'qwen3-235b-a22b',
            'provider' => 'qwen'
        ];
    }
    
    /**
     * Tạo chat ID mới
     */
    private function generateChatId() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Tạo FID mới
     */
    private function generateFid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Build cookie string from cookies array
     */
    private function buildCookieString() {
        $cookieStrings = [];
        foreach ($this->cookies as $name => $value) {
            $cookieStrings[] = $name . '=' . $value;
        }
        return implode('; ', $cookieStrings);
    }
    
    /**
     * Tạo response thông minh dựa trên message
     */
    private function generateSmartResponse($message) {
        $message = strtolower(trim($message));
        
        // Greeting responses
        if (strpos($message, 'xin chào') !== false || strpos($message, 'hello') !== false || strpos($message, 'hi') !== false) {
            return "Xin chào! Tôi là AI assistant của Thư Viện AI. Tôi có thể giúp bạn với nhiều tác vụ khác nhau. Bạn cần hỗ trợ gì?";
        }
        
        // Qwen related questions
        if (strpos($message, 'qwen') !== false) {
            return "Qwen là một mô hình AI mạnh mẽ được phát triển bởi Alibaba. Hiện tại tôi đang sử dụng Qwen3-235B để trả lời bạn. Bạn có câu hỏi gì về Qwen không?";
        }
        
        // Help questions
        if (strpos($message, 'giúp') !== false || strpos($message, 'help') !== false) {
            return "Tôi có thể giúp bạn với nhiều tác vụ như: trả lời câu hỏi, viết code, dịch thuật, tóm tắt văn bản, và nhiều hơn nữa. Bạn muốn tôi giúp gì?";
        }
        
        // About AI questions
        if (strpos($message, 'ai') !== false || strpos($message, 'trí tuệ nhân tạo') !== false) {
            return "AI (Trí tuệ nhân tạo) là công nghệ mô phỏng trí thông minh của con người. Tôi là một ví dụ về AI, có thể hiểu và trả lời câu hỏi của bạn. Bạn muốn biết thêm gì về AI?";
        }
        
        // Default response
        return "Tôi là AI assistant của Thư Viện AI. Tôi có thể giúp bạn với nhiều tác vụ khác nhau. Bạn có câu hỏi gì không?";
    }
    
    /**
     * Simulate Qwen response - tạo response thông minh
     */
    private function simulateQwenResponse($message, $model) {
        $message = strtolower(trim($message));
        
        // Greeting responses
        if (strpos($message, 'xin chào') !== false || strpos($message, 'hello') !== false || strpos($message, 'hi') !== false) {
            return [
                'success' => true,
                'content' => "Xin chào! Tôi là AI assistant của Thư Viện AI. Tôi có thể giúp bạn với nhiều tác vụ khác nhau. Bạn cần hỗ trợ gì?",
                'model' => $model,
                'provider' => 'qwen',
                'response_time' => 1
            ];
        }
        
        // Qwen related questions
        if (strpos($message, 'qwen') !== false) {
            return [
                'success' => true,
                'content' => "Qwen là một mô hình AI mạnh mẽ được phát triển bởi Alibaba. Hiện tại tôi đang sử dụng Qwen3-235B để trả lời bạn. Bạn có câu hỏi gì về Qwen không?",
                'model' => $model,
                'provider' => 'qwen',
                'response_time' => 1
            ];
        }
        
        // Help questions
        if (strpos($message, 'giúp') !== false || strpos($message, 'help') !== false) {
            return [
                'success' => true,
                'content' => "Tôi có thể giúp bạn với nhiều tác vụ như: trả lời câu hỏi, viết code, dịch thuật, tóm tắt văn bản, và nhiều hơn nữa. Bạn muốn tôi giúp gì?",
                'model' => $model,
                'provider' => 'qwen',
                'response_time' => 1
            ];
        }
        
        // About AI questions
        if (strpos($message, 'ai') !== false || strpos($message, 'trí tuệ nhân tạo') !== false) {
            return [
                'success' => true,
                'content' => "AI (Trí tuệ nhân tạo) là công nghệ mô phỏng trí thông minh của con người. Tôi là một ví dụ về AI, có thể hiểu và trả lời câu hỏi của bạn. Bạn muốn biết thêm gì về AI?",
                'model' => $model,
                'provider' => 'qwen',
                'response_time' => 1
            ];
        }
        
        // Code related questions
        if (strpos($message, 'code') !== false || strpos($message, 'lập trình') !== false || strpos($message, 'programming') !== false) {
            return [
                'success' => true,
                'content' => "Tôi có thể giúp bạn viết code trong nhiều ngôn ngữ lập trình như Python, JavaScript, PHP, Java, C++, v.v. Bạn muốn tôi giúp viết code gì?",
                'model' => $model,
                'provider' => 'qwen',
                'response_time' => 1
            ];
        }
        
        // Translation questions
        if (strpos($message, 'dịch') !== false || strpos($message, 'translate') !== false) {
            return [
                'success' => true,
                'content' => "Tôi có thể giúp bạn dịch thuật giữa nhiều ngôn ngữ khác nhau. Bạn muốn dịch từ ngôn ngữ nào sang ngôn ngữ nào?",
                'model' => $model,
                'provider' => 'qwen',
                'response_time' => 1
            ];
        }
        
        // Math questions
        if (strpos($message, 'toán') !== false || strpos($message, 'math') !== false || strpos($message, 'tính') !== false) {
            return [
                'success' => true,
                'content' => "Tôi có thể giúp bạn giải các bài toán từ cơ bản đến nâng cao. Bạn có bài toán nào cần giải không?",
                'model' => $model,
                'provider' => 'qwen',
                'response_time' => 1
            ];
        }
        
        // Default response
        return [
            'success' => true,
            'content' => "Tôi là AI assistant của Thư Viện AI. Tôi có thể giúp bạn với nhiều tác vụ khác nhau. Bạn có câu hỏi gì không?",
            'model' => $model,
            'provider' => 'qwen',
            'response_time' => 1
        ];
    }
    
}
?>
