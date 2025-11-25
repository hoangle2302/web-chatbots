<?php
/**
 * Key4U Service - Xử lý tất cả các loại API Key4U với Guzzle HTTP
 */

require_once __DIR__ . '/../config/Config.php';

class Key4UService {
    private $config;
    private $apiKey;
    private $baseUrl = 'https://api.key4u.shop/v1';
    private $client;
    
    // Danh sách tất cả models có sẵn - được phân loại theo chức năng
    
    // === CHAT/TEXT MODELS ===
    private $chatModels = [
        // OpenAI GPT Models
        'gpt-3.5-turbo', 'gpt-3.5-turbo-0125', 'gpt-3.5-turbo-0613', 'gpt-3.5-turbo-1106',
        'gpt-3.5-turbo-16k', 'gpt-3.5-turbo-16k-0613', 'gpt-3.5-turbo-instruct', 'gpt-3.5-turbo-instruct-0914',
        'gpt-4', 'gpt-4-0125-preview', 'gpt-4-0613', 'gpt-4-1106-preview', 'gpt-4-32k', 'gpt-4-32k-0613',
        'gpt-4-turbo', 'gpt-4-turbo-2024-04-09', 'gpt-4-turbo-preview', 'gpt-4-vision-preview',
        'gpt-4-all', 'gpt-4-gizmo-*',
        'gpt-4.1-2025-04-14', 'gpt-4.1-mini-2025-04-14', 'gpt-4.1-nano-2025-04-14',
        'gpt-4o', 'gpt-4o-2024-05-13', 'gpt-4o-2024-08-06', 'gpt-4o-2024-11-20', 'gpt-4o-all',
        'gpt-4o-image-vip', 'gpt-4o-audio-preview', 'gpt-4o-audio-preview-2024-10-01', 'gpt-4o-audio-preview-2024-12-17',
        'gpt-4o-mini', 'gpt-4o-mini-2024-07-18', 'gpt-4o-mini-transcribe', 'gpt-4o-mini-tts',
        'gpt-4o-realtime-preview', 'gpt-4o-realtime-preview-2024-10-01', 'gpt-4o-search-preview-2025-03-11',
        'gpt-4o-transcribe',
        'gpt-5-2025-08-07', 'gpt-5-chat-latest', 'gpt-5-mini-2025-08-07', 'gpt-5-nano-2025-08-07',
        'o1', 'o1-2024-12-17', 'o1-all', 'o1-mini', 'o1-mini-2024-09-12', 'o1-mini-all',
        'o1-preview', 'o1-preview-2024-09-12', 'o1-preview-all', 'o1-pro-all',
        'o3', 'o3-2025-04-16', 'o3-all', 'o3-deep-research', 'o3-deep-research-2025-06-26',
        'o3-mini', 'o3-mini-2025-01-31', 'o3-mini-all', 'o3-mini-high-all',
        'o3-pro', 'o3-pro-2025-06-10', 'o3-pro-all',
        'o4-mini', 'o4-mini-2025-04-16', 'o4-mini-deep-research', 'o4-mini-deep-research-2025-06-26', 'o4-mini-all',
        
        // Claude Models
        'claude-3-haiku-20240307', 'claude-3-sonnet-20240229', 'claude-3-opus-20240229',
        'claude-3-5-haiku-20241022', 'claude-3-5-sonnet-20240620', 'claude-3-5-sonnet-20241022',
        'claude-3-5-sonnet-latest', 'claude-3-5-sonnet-all',
        'claude-3-7-sonnet-20250219', 'claude-3-7-sonnet-20250219-thinking',
        'claude-opus-4-20250514', 'claude-opus-4-20250514-thinking', 'claude-opus-4-1-20250805', 'claude-opus-4-1-20250805-thinking',
        'claude-sonnet-4-20250514', 'claude-sonnet-4-20250514-thinking',
        
        // Google Gemini Models
        'gemini-1.5-flash-002', 'gemini-1.5-flash-latest', 'gemini-1.5-pro-002', 'gemini-1.5-pro-latest',
        'gemini-2.0-flash', 'gemini-2.0-flash-001', 'gemini-2.0-flash-lite', 'gemini-2.0-flash-lite-001',
        'gemini-2.5-flash', 'gemini-2.5-flash-all', 'gemini-2.5-flash-deepsearch',
        'gemini-2.5-pro-all', 'gemini-2.5-pro-deepsearch',
        
        // Chinese Models
        'qwen3-235b-a22b', 'qwen3-30b-a3b', 'qwen3-32b', 'qwen3-coder-plus',
        'qwq-32b', 'qwq-72b-preview', 'qwq-plus', 'qwq-plus-latest',
        'yi-large', 'yi-lightning', 'yi-vision', 'yi-large-rag', 'yi-large-turbo', 'yi-medium-200k',
        'glm-4', 'glm-4-air', 'glm-4-flash', 'glm-4.5', 'glm-4.5-air', 'glm-4.5-flash',
        'glm-3-turbo', 'glm-4-airx', 'glm-4-long', 'glm-4.5-airx', 'glm-4.5-x',
        'hunyuan-large', 'hunyuan-standard', 'hunyuan-t1', 'hunyuan-t1-latest',
        'hunyuan-a13b', 'hunyuan-code', 'hunyuan-functioncall', 'hunyuan-role',
        'hunyuan-standard-256K', 'hunyuan-t1-20250711', 'hunyuan-t1-vision', 'hunyuan-t1-vision-20250619',
        'hunyuan-turbos-20250716', 'hunyuan-turbos-latest', 'hunyuan-turbos-longtext-128k-20250325',
        'hunyuan-turbos-vision', 'hunyuan-turbos-vision-20250619',
        'kimi-k2-0711-preview', 'kimi-k2-instruct', 'kimi-k2-0711-preview-search', 'kimi-k2-250711',
        'ernie-4.5-turbo-128k', 'ernie-3.5-128k', 'ernie-3.5-128k-preview', 'ernie-3.5-8k', 'ernie-3.5-8k-0613',
        'ernie-3.5-8k-0701', 'ernie-3.5-8k-preview', 'ernie-4.0-8k', 'ernie-4.0-8k-latest', 'ernie-4.0-8k-preview',
        'ernie-4.0-turbo-128k', 'ernie-4.0-turbo-8k', 'ernie-4.0-turbo-8k-0628', 'ernie-4.0-turbo-8k-latest',
        'ernie-4.0-turbo-8k-preview', 'ernie-4.5-0.3b', 'ernie-4.5-21b-a3b', 'ernie-4.5-8k-preview',
        'ernie-4.5-turbo-128k', 'ernie-4.5-turbo-128k-preview', 'ernie-4.5-turbo-32k',
        'ernie-4.5-turbo-vl-32k', 'ernie-4.5-turbo-vl-32k-preview', 'ernie-4.5-turbo-vl-preview',
        'ernie-4.5-vl-28b-a3b', 'ernie-x1-32k', 'ernie-x1-32k-preview', 'ernie-x1-turbo-32k', 'ernie-x1-turbo-32k-preview',
        'doubao-1-5-lite-32k', 'doubao-1-5-lite-32k-250115', 'doubao-1-5-pro-256k-250115', 'doubao-1-5-pro-32k-250115',
        'doubao-1-5-thinking-pro-250415', 'doubao-1-5-thinking-pro-m-250415', 'doubao-1-5-thinking-vision-pro-250428',
        'doubao-1-5-vision-pro-32k', 'doubao-1-5-vision-pro-32k-250115', 'doubao-1.5-pro-256k', 'doubao-1.5-pro-32k',
        'doubao-1.5-vision-pro-32k', 'doubao-pro-32k-241215', 'doubao-seed-1-6-250615', 'doubao-seed-1-6-flash-250615',
        'doubao-seed-1-6-thinking-250615', 'doubao-seed-1-6-thinking-250715', 'doubao-seededit-3-0-i2i-250628',
        'doubao-seedream-3-0-t2i-250415', 'doubao-lite-128k', 'doubao-lite-32k', 'doubao-lite-4k',
        'doubao-pro-128k', 'doubao-pro-32k-character', 'doubao-pro-4k',
        
        // Other Models
        'llama-2-70b', 'llama-2-13b', 'llama-3-sonar-large-32k-chat', 'llama-3-sonar-small-32k-chat',
        'llama-3.1-405b-instruct', 'llama-3.1-405b', 'llama-3.1-405b', 'meta-llama/llama-4-maverick', 'meta-llama/llama-4-scout',
        'phi-4', 'Phi-4', 'mistral-large-latest', 'mistral-small-latest',
        'grok-2-1212', 'grok-3-deepsearch', 'grok-3-image', 'grok-3-reasoner', 'grok-3-reasoning',
        'grok-4', 'grok-beta', 'grok-3', 'grok-3-mini',
        'moonshot-v1-128k', 'moonshot-v1-32k', 'moonshot-v1-8k',
        'deepseek-r1', 'deepseek-r1-250528', 'deepseek-reasoner', 'deepseek-r1-2025-01-20', 'deepseek-r1-searching',
        'qwen-omni-turbo', 'qwen-turbo-1101', 'qwen-turbo-2024-11-01', 'qwen-turbo', 'qwen-turbo-latest',
        'qwen-plus', 'qwen-plus-latest', 'qwen-vl-max', 'qwen-vl-plus',
        'qwen2.5-32b-instruct', 'qwen2.5-vl-32b-instruct', 'qwen2.5-vl-72b-instruct', 'qwen2.5-72b-instruct', 'qwen2.5-7b-instruct',
        'qwen3-0.6b', 'qwen3-1.7b', 'qwen3-14b', 'qwen3-235b-a22b', 'qwen3-235b-a22b-think',
        'qwen3-30b-a3b', 'qwen3-30b-a3b-think', 'qwen3-32b', 'qwen3-4b', 'qwen3-8b',
        'qwen3-coder-480b-a35b-instruct', 'qwen3-coder-plus', 'qwen3-coder-plus-2025-07-22',
        'qwq-32b', 'qwq-plus', 'qwq-plus-2025-03-05', 'qwq-plus-latest', 'qvq-72b-preview',
        'gemma-2b-it', 'gemma-7b-it',
        'Dolphin3.0-R1-Mistral-24B', 'MiniMax-Hailuo-02', 'SparkDesk-v1.1', 'SparkDesk-v2.1', 'SparkDesk-v3.1', 'SparkDesk-v3.5',
        'prunaai/vace-14b', 'playground-v2.5',
        'text-davinci-edit-001', 'text-ada-001', 'text-babbage-001', 'text-curie-001',
        'babbage-002', 'davinci-002', 'text-davinci-edit-001',
        'chatgpt-4o-latest', 'gpt-3.5-turbo-0301', 'gpt-4-0314', 'gpt-4-32k-0314', 'gpt-4o-audio-preview-2025-06-03',
        'gpt-4o-search-preview-2025-03-11', 'o4-mini-deep-research', 'o4-mini-deep-research-2025-06-26',
        'ERNIE-3.5-8K', 'ERNIE-4.0-8K', 'ERNIE-Speed-128K', 'ERNIE-Speed-8K', 'Embedding-V1',
        'doubao-1-5-thinking-pro-m-250428', 'doubao-seed-1-6-thinking-250615', 'doubao-seed-1-6-thinking-250715',
        'doubao-seededit-3-0-i2i-250628', 'doubao-seedream-3-0-t2i-250415', 'ernie-x1-32k', 'ernie-x1-32k-preview',
        'ernie-x1-turbo-32k', 'ernie-x1-turbo-32k-preview'
    ];
    
    // === IMAGE GENERATION MODELS ===
    private $imageModels = [
        // DALL-E Models
        'dall-e-3', 'dall-e-2', 'gpt-image-1', 'gpt-image-1-all',
        
        // FLUX Models
        'flux-kontext-max', 'flux-dev', 'flux-pro', 'flux-pro-1.1-ultra', 'flux-pro-max', 'flux-schnell',
        'flux.1-dev', 'flux.1-kontext-dev', 'flux.1-kontext-pro', 'flux.1.1-pro',
        'black-forest-labs/flux-1.1-pro', 'black-forest-labs/flux-1.1-pro-ultra', 'black-forest-labs/flux-fill-dev',
        'black-forest-labs/flux-fill-pro', 'black-forest-labs/flux-kontext-dev', 'black-forest-labs/flux-kontext-max',
        'black-forest-labs/flux-kontext-pro', 'fal-ai/flux-1/dev', 'fal-ai/flux-1/dev/image-to-image',
        'fal-ai/flux-1/dev/redux', 'fal-ai/flux-1/schnell', 'fal-ai/flux-1/schnell/redux',
        'fal-ai/flux-pro/kontext', 'fal-ai/flux-pro/kontext/max', 'fal-ai/flux-pro/kontext/max/multi',
        'fal-ai/flux-pro/kontext/max/text-to-image', 'fal-ai/flux-pro/kontext/multi', 'fal-ai/flux-pro/kontext/text-to-image',
        'fal-ai/nano-banana', 'flux-kontext-apps/multi-image-kontext-max', 'flux-kontext-apps/multi-image-kontext-pro',
        'flux-kontext-max', 'flux-kontext-pro', 'flux-pro', 'flux-pro-1.1-ultra', 'flux-pro-max', 'flux-schnell',
        'flux.1-dev', 'flux.1-kontext-dev', 'flux.1-kontext-pro', 'flux.1.1-pro',
        
        // Stable Diffusion Models
        'stable-diffusion', 'stability-ai/sdxl', 'stability-ai/stable-diffusion', 'stability-ai/stable-diffusion-img2img',
        'stability-ai/stable-diffusion-inpainting', 'stable-diffusion-3-2b',
        
        // Midjourney Models
        'mj_blend', 'mj_custom_zoom', 'mj_describe', 'mj_high_variation', 'mj_imagine', 'mj_inpaint',
        'mj_low_variation', 'mj_modal', 'mj_pan', 'mj_reroll', 'mj_shorten', 'mj_upload', 'mj_uploads',
        'mj_upscale', 'mj_variation', 'mj_zoom',
        
        // Ideogram Models
        'ideogram-ai/ideogram-v2-turbo', 'ideogram_describe', 'ideogram_edit_V_3_DEFAULT', 'ideogram_edit_V_3_QUALITY',
        'ideogram_edit_V_3_TURBO', 'ideogram_generate_V_1', 'ideogram_generate_V_1_TURBO', 'ideogram_generate_V_2',
        'ideogram_generate_V_2_TURBO', 'ideogram_generate_V_3_DEFAULT', 'ideogram_generate_V_3_QUALITY',
        'ideogram_generate_V_3_TURBO', 'ideogram_reframe_V_3_DEFAULT', 'ideogram_reframe_V_3_QUALITY',
        'ideogram_reframe_V_3_TURBO', 'ideogram_remix_V_1', 'ideogram_remix_V_1_TURBO', 'ideogram_remix_V_2',
        'ideogram_remix_V_2_TURBO', 'ideogram_remix_V_3_DEFAULT', 'ideogram_remix_V_3_QUALITY',
        'ideogram_remix_V_3_TURBO', 'ideogram_replace_background_V_3_DEFAULT', 'ideogram_replace_background_V_3_QUALITY',
        'ideogram_replace_background_V_3_TURBO', 'ideogram_upscale',
        
        // Google Imagen Models
        'google/imagen-4', 'google/imagen-4-fast', 'google/imagen-4-ultra',
        
        // Other Image Models
        'swap_face', 'cjwbw/rembg', 'sujaykhandekar/object-removal', 'lucataco/remove-bg',
        'andreasjansson/stable-diffusion-animation', 'lucataco/flux-schnell-lora', 'lucataco/animate-diff',
        'recraft-ai/recraft-v3', 'recraft-ai/recraft-v3-svg', 'riffusion/riffusion',
        'gemini-2.0-flash-preview-image-generation', 'gemini-2.5-flash-image-preview', 'gemini-2.5-flash-lite-preview-06-17',
        'sora_image'
    ];
    
    // === AUDIO MODELS ===
    private $audioModels = [
        'whisper-1', 'tts-1', 'tts-1-hd', 'tts-1-1106', 'tts-1-hd-1106',
        'gpt-4o-audio-preview', 'gpt-4o-audio-preview-2024-10-01', 'gpt-4o-audio-preview-2024-12-17',
        'gpt-4o-audio-preview-2025-06-03', 'gpt-4o-mini-transcribe', 'gpt-4o-mini-tts', 'gpt-4o-transcribe',
        'suno_lyrics', 'suno_music'
    ];
    
    // === VIDEO MODELS ===
    private $videoModels = [
        'jimeng-videos', 'kling-effects', 'kling-image', 'kling-kolors-virtual-try-on', 'kling-lip-sync',
        'kling-video', 'kling-video-extend', 'luma_video_api', 'luma_video_extend_api', 'mai-ds-r1',
        'minimax/video-01', 'minimax/video-01-live', 'runwayml-gen3a_turbo-10', 'runwayml-gen3a_turbo-5',
        'runwayml-gen4_turbo-10', 'runwayml-gen4_turbo-5', 'veo2', 'veo2-fast', 'veo2-fast-components',
        'veo2-fast-frames', 'veo2-pro', 'veo3', 'veo3-fast', 'veo3-fast-frames', 'veo3-frames',
        'veo3-pro', 'veo3-pro-frames'
    ];
    
    // === EMBEDDING MODELS ===
    private $embeddingModels = [
        'text-embedding-3-large', 'text-embedding-3-small', 'text-embedding-ada-002', 'Embedding-V1',
        'Pro/BAAI/bge-reranker-v2-m3', 'Qwen/Qwen3-Reranker-0.6B', 'Qwen/Qwen3-Reranker-4B', 'Qwen/Qwen3-Reranker-8B',
        'netease-youdao/bce-reranker-base_v1'
    ];
    
    // === MODERATION MODELS ===
    private $moderationModels = [
        'text-moderation-latest', 'text-moderation-stable'
    ];
    
    // === SPECIAL PURPOSE MODELS ===
    private $specialModels = [
        'gpt-oss-120b', 'gpt-oss-20b', 'babbage-002', 'davinci-002', 'text-ada-001', 'text-babbage-001', 'text-curie-001'
    ];
    
    public function __construct() {
        $this->config = new Config();
        $this->apiKey = $this->config->getKey4UApiKey();
        
        // Không throw exception nếu không có API key, chỉ log warning
        if (!$this->apiKey) {
            error_log('Warning: Key4U API key not configured');
        }
        
        // Sử dụng cURL thay vì Guzzle
        $this->client = null; // Sẽ sử dụng cURL trong các method
    }
    
    /**
     * Lấy danh sách tất cả models có sẵn
     */
    public function getAllModels() {
        return [
            'chat' => $this->chatModels,
            'image' => $this->imageModels,
            'audio' => $this->audioModels,
            'video' => $this->videoModels,
            'embedding' => $this->embeddingModels,
            'moderation' => $this->moderationModels,
            'special' => $this->specialModels
        ];
    }
    
    /**
     * Lấy danh sách chat models
     */
    public function getChatModels() {
        return $this->chatModels;
    }
    
    /**
     * Lấy danh sách image models
     */
    public function getImageModels() {
        return $this->imageModels;
    }
    
    /**
     * Lấy danh sách audio models
     */
    public function getAudioModels() {
        return $this->audioModels;
    }
    
    /**
     * Lấy danh sách video models
     */
    public function getVideoModels() {
        return $this->videoModels;
    }
    
    /**
     * Lấy danh sách embedding models
     */
    public function getEmbeddingModels() {
        return $this->embeddingModels;
    }
    
    /**
     * Lấy danh sách moderation models
     */
    public function getModerationModels() {
        return $this->moderationModels;
    }
    
    /**
     * Lấy danh sách special models
     */
    public function getSpecialModels() {
        return $this->specialModels;
    }
    
    /**
     * Lấy top models được khuyến nghị
     */
    public function getTopModels() {
        return [
            'chat' => [
                'gpt-4-turbo', 'gpt-4o', 'gpt-4o-mini',
                'claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022',
                'gemini-2.0-flash', 'gemini-2.5-flash',
                'qwen3-235b-a22b', 'qwen3-30b-a3b',
                'deepseek-r1', 'deepseek-reasoner',
                'o1', 'o1-mini', 'o3', 'o3-mini'
            ],
            'image' => [
                'flux-kontext-max', 'flux-pro', 'dall-e-3',
                'stable-diffusion-3-2b', 'ideogram-ai/ideogram-v2-turbo'
            ],
            'audio' => [
                'whisper-1', 'tts-1-hd', 'gpt-4o-audio-preview'
            ],
            'video' => [
                'veo2', 'veo3', 'runwayml-gen4_turbo-10', 'kling-video'
            ]
        ];
    }
    
    /**
     * Chat với AI model
     */
    public function chat($message, $model = 'gpt-4-turbo', $options = []) {
        $model = $this->validateModel($model, 'chat');
        
        $requestData = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1000
        ];
        
        try {
            if (!$this->apiKey || $this->apiKey === 'your_key4u_api_key_here') {
                throw new Exception('Key4U API key not configured. Please set KEY4U_API_KEY in config.env');
            }
            
            // Sử dụng cURL thay vì Guzzle
            $url = $this->baseUrl . '/chat/completions';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($responseBody === false || !empty($error)) {
                throw new Exception('Failed to connect to Key4U API: ' . $error);
            }
            
            if ($httpCode !== 200) {
                throw new Exception('Key4U API returned HTTP ' . $httpCode . '. Response: ' . substr($responseBody, 0, 200));
            }
            
            return json_decode($responseBody, true);
            
        } catch (Exception $e) {
            throw new Exception('Key4U API Error: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Key4U API Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Tạo ảnh từ text
     */
    public function generateImage($prompt, $model = 'flux-kontext-max', $options = []) {
        $model = $this->validateModel($model, 'image');
        
        $requestData = [
            'model' => $model,
            'prompt' => $prompt,
            'n' => $options['n'] ?? 1,
            'size' => $options['size'] ?? '1024x1024',
            'response_format' => $options['response_format'] ?? 'url'
        ];
        
        try {
            $response = $this->client->request('POST', '/images/generations', [
                'json' => $requestData,
                'timeout' => 60
            ]);
            
            $responseBody = $response->getBody()->getContents();
            return json_decode($responseBody, true);
            
        } catch (ClientException $e) {
            throw new Exception('Key4U Image Generation Client Error: ' . $e->getMessage());
        } catch (ServerException $e) {
            throw new Exception('Key4U Image Generation Server Error: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Key4U Image Generation Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Chỉnh sửa ảnh - Sử dụng code Guzzle của bạn
     */
    public function editImage($prompt, $imagePath, $model = 'flux-kontext-max', $options = []) {
        $model = $this->validateModel($model, 'image');
        
        if (!file_exists($imagePath)) {
            throw new Exception("Image file not found: $imagePath");
        }
        
        // Tạo mảng dữ liệu multipart – dựa theo tham số từ Apifox
        $multipart = [
            [
                'name' => 'model',
                'contents' => $model
            ],
            [
                'name' => 'prompt',
                'contents' => $prompt
            ],
            [
                'name' => 'n',
                'contents' => $options['n'] ?? 1
            ],
            [
                'name' => 'size',
                'contents' => $options['size'] ?? '1024x1024'
            ],
            [
                'name' => 'response_format',
                'contents' => $options['response_format'] ?? 'b64_json'
            ]
        ];
        
        // Nếu có tệp hình ảnh, thêm dữ liệu tệp vào
        if ($imagePath !== false && file_exists($imagePath)) {
            $multipart[] = [
                'name' => 'image',
                'contents' => fopen($imagePath, 'r'),
                'filename' => basename($imagePath),
                'headers' => [
                    'Content-Type' => $this->getImageMimeType($imagePath)
                ]
            ];
        } else {
            throw new Exception("Cảnh báo: Tệp hình ảnh không tồn tại hoặc đường dẫn sai: $imagePath");
        }
        
        try {
            $response = $this->client->request('POST', '/images/edits', [
                'multipart' => $multipart,
                'timeout' => 60, // Tăng thời gian timeout lên 60 giây
                'debug' => false // Tắt debug để tránh spam log
            ]);
            
            $responseBody = $response->getBody()->getContents();
            return json_decode($responseBody, true);
            
        } catch (ClientException $e) {
            throw new Exception('Key4U Image Edit Client Error: ' . $e->getMessage());
        } catch (ServerException $e) {
            throw new Exception('Key4U Image Edit Server Error: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Key4U Image Edit Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Chuyển đổi text thành giọng nói
     */
    public function textToSpeech($text, $model = 'tts-1', $options = []) {
        $model = $this->validateModel($model, 'audio');
        
        $requestData = [
            'model' => $model,
            'input' => $text,
            'voice' => $options['voice'] ?? 'alloy',
            'response_format' => $options['response_format'] ?? 'mp3'
        ];
        
        return $this->makeRequest('/audio/speech', $requestData);
    }
    
    /**
     * Chuyển đổi giọng nói thành text
     */
    public function speechToText($audioPath, $model = 'whisper-1', $options = []) {
        $model = $this->validateModel($model, 'audio');
        
        if (!file_exists($audioPath)) {
            throw new Exception("Audio file not found: $audioPath");
        }
        
        $multipart = [
            [
                'name' => 'model',
                'contents' => $model
            ],
            [
                'name' => 'file',
                'contents' => fopen($audioPath, 'r'),
                'filename' => basename($audioPath),
                'headers' => [
                    'Content-Type' => $this->getAudioMimeType($audioPath)
                ]
            ],
            [
                'name' => 'response_format',
                'contents' => $options['response_format'] ?? 'json'
            ]
        ];
        
        return $this->makeMultipartRequest('/audio/transcriptions', $multipart);
    }
    
    /**
     * Lấy danh sách models theo loại
     */
    public function getAvailableModels() {
        return [
            'chat' => $this->chatModels,
            'image' => $this->imageModels,
            'audio' => $this->audioModels,
            'video' => $this->videoModels,
            'embedding' => $this->embeddingModels,
            'moderation' => $this->moderationModels,
            'special' => $this->specialModels
        ];
    }
    
    
    /**
     * Kiểm tra model có thuộc loại nào
     */
    public function getModelType($model) {
        if (in_array($model, $this->chatModels)) return 'chat';
        if (in_array($model, $this->imageModels)) return 'image';
        if (in_array($model, $this->audioModels)) return 'audio';
        if (in_array($model, $this->videoModels)) return 'video';
        if (in_array($model, $this->embeddingModels)) return 'embedding';
        if (in_array($model, $this->moderationModels)) return 'moderation';
        if (in_array($model, $this->specialModels)) return 'special';
        return 'unknown';
    }
    
    /**
     * Validate model và trả về fallback nếu cần
     */
    public function validateModel($model, $type = 'chat') {
        $allModels = $this->getAllModels();
        
        if (!in_array($model, $allModels)) {
            // Tìm model tương tự hoặc fallback
            switch ($type) {
                case 'chat':
                    return 'gpt-4-turbo';
                case 'image':
                    return 'flux-kontext-max';
                case 'audio':
                    return 'tts-1';
                case 'video':
                    return 'kling-video';
                case 'embedding':
                    return 'text-embedding-3-large';
                case 'moderation':
                    return 'text-moderation-latest';
                default:
                    return 'gpt-4-turbo';
            }
        }
        
        return $model;
    }
    
    /**
     * Lấy thông tin chi tiết về model
     */
    public function getModelInfo($model) {
        $type = $this->getModelType($model);
        $info = [
            'model' => $model,
            'type' => $type,
            'supported' => $type !== 'unknown'
        ];
        
        // Thêm thông tin cụ thể theo loại
        switch ($type) {
            case 'chat':
                $info['description'] = 'Text generation and conversation model';
                $info['capabilities'] = ['text_generation', 'conversation', 'reasoning'];
                break;
            case 'image':
                $info['description'] = 'Image generation and editing model';
                $info['capabilities'] = ['image_generation', 'image_editing', 'image_upscaling'];
                break;
            case 'audio':
                $info['description'] = 'Audio processing model';
                $info['capabilities'] = ['text_to_speech', 'speech_to_text', 'audio_transcription'];
                break;
            case 'video':
                $info['description'] = 'Video generation model';
                $info['capabilities'] = ['video_generation', 'video_editing', 'video_effects'];
                break;
            case 'embedding':
                $info['description'] = 'Text embedding model';
                $info['capabilities'] = ['text_embedding', 'similarity_search', 'semantic_analysis'];
                break;
            case 'moderation':
                $info['description'] = 'Content moderation model';
                $info['capabilities'] = ['content_moderation', 'safety_check', 'policy_enforcement'];
                break;
            case 'special':
                $info['description'] = 'Special purpose model';
                $info['capabilities'] = ['custom_processing', 'legacy_support'];
                break;
            default:
                $info['description'] = 'Unknown model type';
                $info['capabilities'] = [];
        }
        
        return $info;
    }
    
    /**
     * Lấy danh sách models theo nhà cung cấp
     */
    public function getModelsByProvider() {
        $providers = [
            'OpenAI' => [],
            'Anthropic' => [],
            'Google' => [],
            'Chinese' => [],
            'Other' => []
        ];
        
        foreach ($this->getAllModels() as $model) {
            if (strpos($model, 'gpt-') === 0 || strpos($model, 'o1') === 0 || strpos($model, 'o3') === 0 || strpos($model, 'o4') === 0 || strpos($model, 'dall-e') === 0 || strpos($model, 'whisper') === 0 || strpos($model, 'tts') === 0) {
                $providers['OpenAI'][] = $model;
            } elseif (strpos($model, 'claude') === 0) {
                $providers['Anthropic'][] = $model;
            } elseif (strpos($model, 'gemini') === 0 || strpos($model, 'google/') === 0) {
                $providers['Google'][] = $model;
            } elseif (strpos($model, 'qwen') === 0 || strpos($model, 'yi-') === 0 || strpos($model, 'glm-') === 0 || strpos($model, 'hunyuan') === 0 || strpos($model, 'kimi') === 0 || strpos($model, 'ernie') === 0 || strpos($model, 'doubao') === 0 || strpos($model, 'sparkdesk') === 0) {
                $providers['Chinese'][] = $model;
            } else {
                $providers['Other'][] = $model;
            }
        }
        
        return $providers;
    }
    
    
    /**
     * Tạo video từ text hoặc ảnh
     */
    public function generateVideo($prompt, $model = 'kling-video', $options = []) {
        if (!in_array($model, $this->videoModels)) {
            $model = 'kling-video'; // Fallback
        }
        
        $requestData = [
            'model' => $model,
            'prompt' => $prompt,
            'duration' => $options['duration'] ?? 5,
            'resolution' => $options['resolution'] ?? '720p',
            'fps' => $options['fps'] ?? 24
        ];
        
        return $this->makeRequest('/video/generations', $requestData);
    }
    
    /**
     * Tạo embedding từ text
     */
    public function createEmbedding($text, $model = 'text-embedding-3-large', $options = []) {
        if (!in_array($model, $this->embeddingModels)) {
            $model = 'text-embedding-3-large'; // Fallback
        }
        
        $requestData = [
            'model' => $model,
            'input' => $text,
            'encoding_format' => $options['encoding_format'] ?? 'float'
        ];
        
        return $this->makeRequest('/embeddings', $requestData);
    }
    
    /**
     * Kiểm tra nội dung có phù hợp không (moderation)
     */
    public function moderateContent($text, $model = 'text-moderation-latest', $options = []) {
        if (!in_array($model, $this->moderationModels)) {
            $model = 'text-moderation-latest'; // Fallback
        }
        
        $requestData = [
            'model' => $model,
            'input' => $text
        ];
        
        return $this->makeRequest('/moderations', $requestData);
    }
    
    /**
     * Xử lý các mô hình đặc biệt
     */
    public function processSpecialModel($model, $data, $options = []) {
        if (!in_array($model, $this->specialModels)) {
            throw new Exception("Unsupported special model: $model");
        }
        
        // Xử lý theo từng loại model đặc biệt
        switch ($model) {
            case 'gpt-oss-120b':
            case 'gpt-oss-20b':
                return $this->chat($data['message'], $model, $options);
            case 'babbage-002':
            case 'davinci-002':
                return $this->makeRequest('/completions', [
                    'model' => $model,
                    'prompt' => $data['prompt'],
                    'max_tokens' => $options['max_tokens'] ?? 100
                ]);
            default:
                throw new Exception("Special model processing not implemented: $model");
        }
    }
    
    /**
     * Hàm helper để thực hiện request
     */
    private function makeRequest($endpoint, $data) {
        try {
            $response = $this->client->request('POST', $endpoint, [
                'json' => $data,
                'timeout' => 60
            ]);
            
            $responseBody = $response->getBody()->getContents();
            return json_decode($responseBody, true);
            
        } catch (Exception $e) {
            throw new Exception('Key4U API Error: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Key4U API Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Hàm helper để thực hiện multipart request
     */
    private function makeMultipartRequest($endpoint, $multipart) {
        try {
            $response = $this->client->request('POST', $endpoint, [
                'multipart' => $multipart,
                'timeout' => 60
            ]);
            
            $responseBody = $response->getBody()->getContents();
            return json_decode($responseBody, true);
            
        } catch (Exception $e) {
            throw new Exception('Key4U API Error: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Key4U API Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Lấy MIME type của ảnh
     */
    private function getImageMimeType($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        return $mimeTypes[$extension] ?? 'image/jpeg';
    }
    
    /**
     * Lấy MIME type của audio
     */
    private function getAudioMimeType($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'ogg' => 'audio/ogg'
        ];
        return $mimeTypes[$extension] ?? 'audio/mpeg';
    }
}
?>
