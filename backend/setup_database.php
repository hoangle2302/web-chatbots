<?php
/**
 * Database Setup Script
 * Tạo database và tables cần thiết cho ChatBot application
 */

require_once 'config/database.php';

try {
    // Kết nối MySQL server (chưa chọn database)
    $dsn = "mysql:host=127.0.0.1;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', 'Hoang@2005');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL server\n";
    
    // Tạo database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS db_data CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database 'db_data' created or already exists\n";
    
    // Chuyển sang database vừa tạo
    $pdo->exec("USE db_data");
    
    // Đọc và thực thi schema
    $schema = file_get_contents(__DIR__ . '/config/schema.sql');
    
    // Tách các câu lệnh SQL
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✅ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "⚠️  Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Tạo user demo
    $hashedPassword = password_hash('demo123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, password, status) VALUES (?, ?, ?, 'active')");
    $result = $stmt->execute(['Demo User', 'demo@chatbots.ai', $hashedPassword]);
    
    if ($result) {
        echo "✅ Demo user created: demo@chatbots.ai / demo123\n";
    }
    
    // Kiểm tra tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📊 Created tables:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "\n📝 You can now:\n";
    echo "  1. Open the web application\n";
    echo "  2. Register a new account or login with demo@chatbots.ai / demo123\n";
    echo "  3. Start chatting with AI!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "\n🔧 Please check:\n";
    echo "  1. MySQL server is running\n";
    echo "  2. Database credentials in config/database.php\n";
    echo "  3. User has permission to create databases\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>