<?php
/**
 * 🤖 MODEL LỊCH SỬ AI QUERY
 * Quản lý lịch sử tương tác với AI
 */
class AIQueryHistory {
    private $conn;
    private $table_name = "ai_query_history";
    
    // Properties
    public $id;
    public $user_id;
    public $model;
    public $prompt;
    public $response;
    public $tokens_used;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Tạo record mới
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, model, prompt, response, tokens_used) 
                  VALUES (:user_id, :model, :prompt, :response, :tokens_used)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->model = htmlspecialchars(strip_tags($this->model));
        $this->prompt = htmlspecialchars(strip_tags($this->prompt));
        $this->response = htmlspecialchars(strip_tags($this->response));
        $this->tokens_used = $this->tokens_used ?? 0;
        
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":prompt", $this->prompt);
        $stmt->bindParam(":response", $this->response);
        $stmt->bindParam(":tokens_used", $this->tokens_used);
        
        return $stmt->execute();
    }
    
    /**
     * Lấy lịch sử theo user ID
     */
    public function getByUserId($userId, $limit = 50) {
        try {
            if (!$this->conn) {
                error_log("Database connection is null in AIQueryHistory::getByUserId");
                return [];
            }
            
            if (!$userId) {
                error_log("User ID is null or empty in AIQueryHistory::getByUserId");
                return [];
            }
            
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE user_id = :user_id 
                      ORDER BY timestamp DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                $errorInfo = $this->conn->errorInfo();
                error_log("Failed to prepare query in AIQueryHistory::getByUserId: " . print_r($errorInfo, true));
                return [];
            }
            
            // Cast userId và limit sang int để đảm bảo type đúng
            $userId = (int) $userId;
            $limit = (int) $limit;
            
            $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                error_log("Failed to execute query in AIQueryHistory::getByUserId: " . print_r($errorInfo, true));
                return [];
            }
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results ? $results : [];
        } catch (PDOException $e) {
            error_log("PDOException in AIQueryHistory::getByUserId: " . $e->getMessage());
            error_log("SQL Error Code: " . $e->getCode());
            // Nếu lỗi do table không tồn tại, trả về array rỗng thay vì throw error
            if ($e->getCode() == '42S02') { // Table doesn't exist
                error_log("Table " . $this->table_name . " does not exist, returning empty array");
                return [];
            }
            return [];
        } catch (Exception $e) {
            error_log("Exception in AIQueryHistory::getByUserId: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy lịch sử theo model
     */
    public function getByModel($model, $limit = 50) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE model = :model 
                  ORDER BY timestamp DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":model", $model);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy tất cả lịch sử
     */
    public function getAll($limit = 100, $offset = 0) {
        $query = "SELECT h.*, u.username 
                  FROM " . $this->table_name . " h
                  LEFT JOIN users u ON h.user_id = u.id
                  ORDER BY h.timestamp DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Thống kê tokens theo user
     */
    public function getTokenStatsByUser($userId) {
        $query = "SELECT 
                    COUNT(*) as total_queries,
                    SUM(tokens_used) as total_tokens,
                    AVG(tokens_used) as avg_tokens,
                    model
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  GROUP BY model";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Xóa lịch sử cũ
     */
    public function deleteOldHistory($days = 30) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE timestamp < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":days", $days, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>