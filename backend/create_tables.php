<?php
/**
 * Simple Database Setup
 */

try {
    // Kết nối trực tiếp
    $host = '127.0.0.1';
    $dbname = 'db_data';
    $username = 'root';
    $password = 'Hoang@2005';
    
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Kết nối MySQL thành công\n";
    
    // Tạo database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$dbname' đã được tạo\n";
    
    // Chọn database
    $pdo->exec("USE $dbname");
    
    // Tạo bảng users
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_email (email),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($sql);
    echo "✅ Bảng 'users' đã được tạo\n";
    
    // Tạo bảng chat_sessions
    $sql = "
    CREATE TABLE IF NOT EXISTS chat_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_name VARCHAR(200) DEFAULT 'New Chat',
        ai_model VARCHAR(100) DEFAULT 'gpt-3.5-turbo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($sql);
    echo "✅ Bảng 'chat_sessions' đã được tạo\n";
    
    // Tạo bảng messages
    $sql = "
    CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT NOT NULL,
        user_id INT NOT NULL,
        message_type ENUM('user', 'assistant', 'system') NOT NULL,
        content TEXT NOT NULL,
        ai_model VARCHAR(100) DEFAULT NULL,
        tokens_used INT DEFAULT 0,
        response_time DECIMAL(5,3) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_session_id (session_id),
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        INDEX idx_message_type (message_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($sql);
    echo "✅ Bảng 'messages' đã được tạo\n";
    
    // Tạo bảng login_attempts
    $sql = "
    CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(150) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        success BOOLEAN DEFAULT FALSE,
        user_agent TEXT,
        INDEX idx_email_time (email, attempt_time),
        INDEX idx_ip_time (ip_address, attempt_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($sql);
    echo "✅ Bảng 'login_attempts' đã được tạo\n";
    
    // Tạo user test
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(['test@example.com']);
    $userExists = $stmt->fetchColumn() > 0;
    
    if (!$userExists) {
        $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['Test User', 'test@example.com', $hashedPassword]);
        echo "✅ User test đã được tạo: test@example.com / 123456\n";
    } else {
        echo "ℹ️  User test đã tồn tại\n";
    }
    
    echo "\n🎉 Setup hoàn tất!\n";
    echo "📊 Database: $dbname\n";
    echo "🏠 Host: $host\n";
    echo "👤 Test account: test@example.com / 123456\n";
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}
?>