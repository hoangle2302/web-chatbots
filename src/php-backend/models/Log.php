<?php
/**
 * 📝 MODEL LOG
 * Quản lý log hoạt động của hệ thống
 */
class Log {
    private $conn;
    private $table_name = "logs";
    
    // Properties
    public $id;
    public $user_id;
    public $action;
    public $detail;
    public $ip_address;
    public $user_agent;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Tạo log mới
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, action, detail, ip_address, user_agent) 
                  VALUES (:user_id, :action, :detail, :ip_address, :user_agent)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->action = htmlspecialchars(strip_tags($this->action));
        $this->detail = htmlspecialchars(strip_tags($this->detail));
        $this->ip_address = $this->getClientIP();
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":action", $this->action);
        $stmt->bindParam(":detail", $this->detail);
        $stmt->bindParam(":ip_address", $this->ip_address);
        $stmt->bindParam(":user_agent", $this->user_agent);
        
        return $stmt->execute();
    }
    
    /**
     * Lấy IP của client
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Lấy logs theo user ID
     */
    public function getByUserId($userId, $limit = 50) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy logs theo action
     */
    public function getByAction($action, $limit = 50) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE action = :action 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy tất cả logs
     */
    public function getAll($limit = 100, $offset = 0) {
        $query = "SELECT l.*, u.username 
                  FROM " . $this->table_name . " l
                  LEFT JOIN users u ON l.user_id = u.id
                  ORDER BY l.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Xóa logs cũ
     */
    public function deleteOldLogs($days = 30) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":days", $days, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>