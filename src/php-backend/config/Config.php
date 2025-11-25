<?php
/**
 * Configuration management
 */
class Config {
    private $config;
    
    public function __construct() {
        $this->loadConfig();
    }
    
    private function loadConfig() {
        // Load từ config.env
        $envFile = __DIR__ . '/../../../config.env';  // Từ src/php-backend/config/ lên 3 cấp đến thư mục gốc
        if (!file_exists($envFile)) {
            // Try alternative path
            $envFile = __DIR__ . '/../../config.env';
        }
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
        
        $this->config = [
            'key4u' => [
                'api_url' => 'https://api.key4u.shop/v1/chat/completions',
                'api_key' => $_ENV['KEY4U_API_KEY'] ?? '',
                'default_temperature' => 0.7,
                'default_max_tokens' => 2000,
                'default_model' => 'gpt-4-turbo'
            ],
            'ai_tool' => [
                'base_url' => $_ENV['AI_TOOL_BASE_URL'] ?? 'http://127.0.0.1:8001',
                'api_key' => $_ENV['AI_TOOL_INTERNAL_KEY'] ?? $_ENV['KEY4U_API_KEY'] ?? '',
                'timeout' => isset($_ENV['AI_TOOL_TIMEOUT']) ? (int)$_ENV['AI_TOOL_TIMEOUT'] : 120
            ],
            'models' => [
                'o3-mini' => ['name' => 'OpenAI o3-mini', 'tier' => 1],
                'o3' => ['name' => 'OpenAI o3', 'tier' => 1],
                'GPT-4O' => ['name' => 'GPT-4O', 'tier' => 1],
                'gpt-4-turbo' => ['name' => 'GPT-4 Turbo', 'tier' => 2],
                'gemini-2-5-pro' => ['name' => 'Gemini 2.5 Pro', 'tier' => 1],
                'gemini-pro' => ['name' => 'Gemini Pro 1.5', 'tier' => 2],
                'gemini-ultra' => ['name' => 'Gemini Ultra', 'tier' => 1],
                'claude-3-5-sonnet' => ['name' => 'Claude 3.5 Sonnet', 'tier' => 1],
                'claude-3-5-haiku' => ['name' => 'Claude 3.5 Haiku', 'tier' => 2],
                'claude-3-opus' => ['name' => 'Claude 3 Opus', 'tier' => 3],
                'grok-2' => ['name' => 'Grok-2', 'tier' => 4],
                'grok-2-mini' => ['name' => 'Grok-2 Mini', 'tier' => 5],
                'llama-3-3-70b' => ['name' => 'Llama 3.3 70B', 'tier' => 3],
                'llama-3-1-405b' => ['name' => 'Llama 3.1 405B', 'tier' => 4],
                'qwen-2-5-72b' => ['name' => 'Qwen 2.5 72B', 'tier' => 3],
                'qwen-2-5-coder' => ['name' => 'Qwen 2.5 Coder', 'tier' => 4],
                'deepseek-v3' => ['name' => 'DeepSeek-V3', 'tier' => 2],
                'deepseek-coder' => ['name' => 'DeepSeek Coder V2', 'tier' => 4],
                'mistral-large' => ['name' => 'Mistral Large 2', 'tier' => 3],
                'mistral-nemo' => ['name' => 'Mistral Nemo', 'tier' => 5],
                'yi-large' => ['name' => 'Yi-Large', 'tier' => 5],
                'command-r-plus' => ['name' => 'Command R+', 'tier' => 5],
                'phi-3-5' => ['name' => 'Phi-3.5', 'tier' => 5],
                'nemotron-70b' => ['name' => 'Nemotron 70B', 'tier' => 5],
                'wizardlm-2' => ['name' => 'WizardLM-2', 'tier' => 5],
                'solar-pro' => ['name' => 'Solar Pro', 'tier' => 5],
                'mixtral-8x22b' => ['name' => 'Mixtral 8x22B', 'tier' => 5]
            ],
            'ensemble' => [
                'top_models' => ['gpt-4-turbo', 'claude-3-5-sonnet', 'gemini-2-5-pro', 'deepseek-v3'],
                'max_tokens_per_model' => 1500
            ],
            'ui' => [
                'auto_scroll' => true,
                'show_model_name' => true,
                'typing_animation' => false
            ]
        ];
    }
    
    public function get($key = null) {
        if ($key === null) {
            return $this->config;
        }
        
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return null;
            }
        }
        
        return $value;
    }
    
    public function getAvailableModels() {
        return array_keys($this->config['models']);
    }
    
    public function getModelInfo($model) {
        return $this->config['models'][$model] ?? null;
    }
    
    public function getKey4UConfig() {
        return $this->config['key4u'];
    }
    
    public function getKey4UApiKey() {
        return $this->config['key4u']['api_key'] ?? '';
    }

    public function getAiToolConfig() {
        return $this->config['ai_tool'];
    }
    
    public function getYescaleConfig() {
        return $this->config['key4u']; // Backward compatibility
    }
    
    public function getEnsembleConfig() {
        return $this->config['ensemble'];
    }
}
?>
