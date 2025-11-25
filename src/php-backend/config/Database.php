<?php
/**
 * 🗄️ QUẢN LÝ KẾT NỐI DATABASE
 * Hỗ trợ cả MySQL và SQLite
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;
    private $db_type;
    
    public function __construct() {
        $this->loadConfig();
        $this->determineDatabaseType();
    }
    
    /**
     * Load cấu hình từ file config.env
     */
    private function loadConfig() {
        $envFile = __DIR__ . '/../../config.env';
        
        if (!file_exists($envFile)) {
            $envFile = dirname(__DIR__, 3) . '/config.env';
        }
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    $_ENV[$key] = $value;
                }
            }
        }
    }
    
    /**
     * Xác định loại database
     */
    private function determineDatabaseType() {
        if (isset($_ENV['DATABASE_PATH']) && !empty($_ENV['DATABASE_PATH'])) {
            // SQLite
            $this->db_type = 'sqlite';
            $this->db_name = __DIR__ . '/../../' . $_ENV['DATABASE_PATH'];
            $this->host = null;
            $this->username = null;
            $this->password = null;
            $this->port = null;
        } else {
            // MySQL
            $this->db_type = 'mysql';
            $this->host = $_ENV['DB_HOST'] ?? 'localhost';
            $this->db_name = $_ENV['DB_NAME'] ?? 'thuvien_ai';
            $this->username = $_ENV['DB_USERNAME'] ?? 'root';
            $this->password = $_ENV['DB_PASSWORD'] ?? '';
            $this->port = $_ENV['DB_PORT'] ?? 3306;
        }
    }
    
    /**
     * Lấy kết nối database
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                if ($this->db_type === 'sqlite') {
                    $this->conn = new PDO("sqlite:" . $this->db_name);
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } else {
                    // Retry logic for MySQL connection
                    $maxRetries = 10;
                    $connected = false;
                    
                    while (!$connected && $maxRetries--) {
                        try {
                            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
                            $this->conn = new PDO($dsn, $this->username, $this->password);
                            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $connected = true;
                        } catch (PDOException $e) {
                            error_log("Waiting for MySQL... Retries left: " . ($maxRetries + 1));
                            echo "Waiting for MySQL... \n";
                            sleep(3);
                        }
                    }
                    
                    if (!$connected) {
                        die("Could not connect to database!");
                    }
                }
                
                // Tạo schema nếu cần
                $this->createSchema();
                
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Tạo schema database
     */
    private function createSchema() {
        if ($this->db_type === 'sqlite') {
            $this->createSQLiteSchema();
        } else {
            $this->createMySQLSchema();
        }
    }
    
    /**
     * Tạo schema SQLite
     */
    private function createSQLiteSchema() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(80) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'user',
            is_active BOOLEAN DEFAULT 1,
            failed_login_count INTEGER DEFAULT 0,
            credits INTEGER DEFAULT 10,
            email VARCHAR(255),
            display_name VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS ai_query_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            model VARCHAR(100),
            prompt TEXT,
            response TEXT,
            tokens_used INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        
        CREATE TABLE IF NOT EXISTS documents (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            filename VARCHAR(255),
            file_path VARCHAR(500),
            file_size INTEGER,
            file_type VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        
        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action VARCHAR(100),
            detail TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        ";
        
        $this->conn->exec($sql);
    }
    
    /**
     * Tạo schema MySQL
     */
    private function createMySQLSchema() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(80) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'user',
            is_active BOOLEAN DEFAULT TRUE,
            failed_login_count INT DEFAULT 0,
            credits INT DEFAULT 10,
            email VARCHAR(255),
            display_name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        CREATE TABLE IF NOT EXISTS ai_query_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            model VARCHAR(100),
            prompt TEXT,
            response TEXT,
            tokens_used INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        CREATE TABLE IF NOT EXISTS documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            filename VARCHAR(255),
            file_path VARCHAR(500),
            file_size INT,
            file_type VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        CREATE TABLE IF NOT EXISTS logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100),
            detail TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $this->conn->exec($sql);
    }
    
    /**
     * Đóng kết nối
     */
    public function closeConnection() {
        $this->conn = null;
    }
    
    /**
     * Lấy loại database
     */
    public function getDatabaseType() {
        return $this->db_type;
    }
}
?>