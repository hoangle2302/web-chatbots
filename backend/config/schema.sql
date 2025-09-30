-- Updated ChatBot Database Schema
-- Tạo database
CREATE DATABASE IF NOT EXISTS chatbots_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE chatbots_ai;

-- Bảng users (người dùng)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    preferred_ai_model VARCHAR(100) DEFAULT 'claude-3-5-sonnet-latest',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    last_activity TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Bảng login_attempts (theo dõi đăng nhập)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email_time (email, attempt_time),
    INDEX idx_success (success)
);

-- Bảng chat_sessions (phiên chat)
CREATE TABLE IF NOT EXISTS chat_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Cuộc trò chuyện mới',
    ai_model VARCHAR(100) DEFAULT 'claude-3-5-sonnet-latest',
    message_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_updated (user_id, updated_at),
    INDEX idx_created_at (created_at)
);

-- Bảng messages (tin nhắn)
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    chat_session_id INT NOT NULL,
    content TEXT NOT NULL,
    is_user BOOLEAN DEFAULT TRUE,
    ai_model VARCHAR(100) NULL,
    tokens_used INT DEFAULT 0,
    processing_time DECIMAL(5,3) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (chat_session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
    INDEX idx_chat_created (chat_session_id, created_at),
    INDEX idx_is_user (is_user)
);

-- Bảng user_ai_models (AI models tùy chỉnh của user)
CREATE TABLE IF NOT EXISTS user_ai_models (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    model_id VARCHAR(100) NOT NULL,
    model_name VARCHAR(150) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_model (user_id, model_id),
    INDEX idx_user_id (user_id)
);

-- Bảng user_activities (hoạt động của user)
CREATE TABLE IF NOT EXISTS user_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_action (action)
);

-- Bảng system_settings (cài đặt hệ thống)
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key (setting_key)
);

-- Insert dữ liệu mặc định
INSERT IGNORE INTO system_settings (setting_key, setting_value, description) VALUES
('app_name', 'Chatbots AI', 'Tên ứng dụng'),
('app_version', '1.0.0', 'Phiên bản ứng dụng'),
('max_message_length', '10000', 'Độ dài tối đa của tin nhắn'),
('default_ai_model', 'claude-3-5-sonnet-latest', 'AI model mặc định'),
('session_timeout', '3600', 'Thời gian session (giây)'),
('max_login_attempts', '5', 'Số lần đăng nhập tối đa'),
('login_lockout_time', '900', 'Thời gian khóa tài khoản (giây)');

-- Tạo user admin mặc định
INSERT IGNORE INTO users (name, email, password, status) VALUES 
('Admin User', 'admin@chatbots.ai', '$2y$10$YourHashedPasswordHere', 'active');

-- Tạo indexes để tối ưu performance
CREATE INDEX IF NOT EXISTS idx_users_email_status ON users(email, status);
CREATE INDEX IF NOT EXISTS idx_messages_content_search ON messages(content(100));
CREATE INDEX IF NOT EXISTS idx_chat_sessions_title_search ON chat_sessions(title);