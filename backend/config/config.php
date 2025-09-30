<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'chatbot_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('APP_NAME', 'HngLe AI ChatBot');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/ChatBots');

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 8);

// AI Models Configuration
define('AI_MODELS', [
    'gpt-3.5' => [
        'name' => 'GPT-3.5 Turbo',
        'description' => 'Mô hình AI thông minh và nhanh chóng',
        'icon' => 'fas fa-robot',
        'color' => '#10a37f'
    ],
    'gpt-4' => [
        'name' => 'GPT-4',
        'description' => 'Mô hình AI mạnh mẽ nhất hiện tại',
        'icon' => 'fas fa-brain',
        'color' => '#8b5cf6'
    ],
    'claude' => [
        'name' => 'Claude',
        'description' => 'AI assistant thông minh từ Anthropic',
        'icon' => 'fas fa-user-robot',
        'color' => '#f97316'
    ],
    'gemini' => [
        'name' => 'Google Gemini',
        'description' => 'AI multimodal từ Google',
        'icon' => 'fas fa-star',
        'color' => '#0ea5e9'
    ]
]);

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'doc', 'docx']);

// Pagination
define('MESSAGES_PER_PAGE', 50);
define('CHATS_PER_PAGE', 20);

// Error reporting
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>