<?php
/**
 * 👤 MODEL NGƯỜI DÙNG
 * Quản lý thông tin và hoạt động của người dùng
 */
class User {
    private $conn;
    private $table_name = "users";
    
    // Properties
    public $id;
    public $username;
    public $email;
    public $display_name;
    public $password;
    public $role;
    public $is_active;
    public $failed_login_count;
    public $last_login_at;
    public $created_at;
    public $credits;
    public $last_daily_credit_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Tạo người dùng mới
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, display_name, password, role, is_active, credits) 
                  VALUES (:username, :email, :display_name, :password, :role, :is_active, :credits)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = $this->email ? htmlspecialchars(strip_tags($this->email)) : null;
        $this->display_name = $this->display_name ? htmlspecialchars(strip_tags($this->display_name)) : null;
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->role = $this->role ?? 'user';
        $this->is_active = $this->is_active ?? 1;
        $this->credits = $this->credits ?? 0;
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":display_name", $this->display_name);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":credits", $this->credits);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    /**
     * Lấy thông tin user theo ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy thông tin user theo username
     */
    public function getByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cập nhật thông tin user
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, email = :email, display_name = :display_name, 
                      role = :role, is_active = :is_active, credits = :credits
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = $this->email ? htmlspecialchars(strip_tags($this->email)) : null;
        $this->display_name = $this->display_name ? htmlspecialchars(strip_tags($this->display_name)) : null;
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":display_name", $this->display_name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":credits", $this->credits);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Cập nhật password
     */
    public function updatePassword($newPassword) {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Cập nhật failed login count
     */
    public function updateFailedLogin() {
        $query = "UPDATE " . $this->table_name . " 
                  SET failed_login_count = failed_login_count + 1 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
    
    /**
     * Reset failed login count
     */
    public function resetFailedLogin() {
        $query = "UPDATE " . $this->table_name . " 
                  SET failed_login_count = 0, last_login_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
    
    /**
     * Thêm credits
     */
    public function addCredits($userId, $amount) {
        $query = "UPDATE " . $this->table_name . " 
                  SET credits = credits + :amount 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":id", $userId);
        return $stmt->execute();
    }
    
    /**
     * Trừ credits
     */
    public function deductCredits($userId, $amount) {
        $query = "UPDATE " . $this->table_name . " 
                  SET credits = credits - :amount 
                  WHERE id = :id AND credits >= :amount";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":id", $userId);
        return $stmt->execute();
    }

    /**
     * Cộng credits hàng ngày cho user (nếu chưa nhận trong ngày)
     */
    public function grantDailyCreditsIfNeeded($userId, $amount = 5) {
        if ($amount <= 0) {
            return [
                'granted' => false,
                'credits' => null,
                'last_daily_credit_at' => null
            ];
        }

        $query = "SELECT credits, last_daily_credit_at FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId, PDO::PARAM_INT);
        $stmt->execute();
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userRow) {
            return [
                'granted' => false,
                'credits' => null,
                'last_daily_credit_at' => null
            ];
        }

        $lastGrantedAt = $userRow['last_daily_credit_at'] ?? null;
        $now = new DateTime('now');
        $todayKey = $now->format('Y-m-d');
        $alreadyGranted = false;

        if (!empty($lastGrantedAt)) {
            try {
                $lastDate = new DateTime($lastGrantedAt);
                $alreadyGranted = $lastDate->format('Y-m-d') >= $todayKey;
            } catch (Exception $e) {
                $alreadyGranted = false;
            }
        }

        if ($alreadyGranted) {
            return [
                'granted' => false,
                'credits' => intval($userRow['credits']),
                'last_daily_credit_at' => $lastGrantedAt
            ];
        }

        $grantedAt = $now->format('Y-m-d H:i:s');
        $todayParam = $todayKey;
        $updateQuery = "UPDATE " . $this->table_name . " 
                        SET credits = credits + :amount, 
                            last_daily_credit_at = :granted_at, 
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE id = :id 
                          AND (last_daily_credit_at IS NULL OR date(last_daily_credit_at) < :today_key)";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(":amount", $amount, PDO::PARAM_INT);
        $updateStmt->bindParam(":granted_at", $grantedAt);
        $updateStmt->bindParam(":id", $userId, PDO::PARAM_INT);
        $updateStmt->bindParam(":today_key", $todayParam);
        $updateStmt->execute();

        if ($updateStmt->rowCount() === 0) {
            return [
                'granted' => false,
                'credits' => intval($userRow['credits']),
                'last_daily_credit_at' => $userRow['last_daily_credit_at']
            ];
        }

        $refreshStmt = $this->conn->prepare("SELECT credits, last_daily_credit_at FROM " . $this->table_name . " WHERE id = :id");
        $refreshStmt->bindParam(":id", $userId, PDO::PARAM_INT);
        $refreshStmt->execute();
        $updatedRow = $refreshStmt->fetch(PDO::FETCH_ASSOC);

        return [
            'granted' => true,
            'credits' => intval($updatedRow['credits'] ?? $userRow['credits']),
            'last_daily_credit_at' => $updatedRow['last_daily_credit_at'] ?? $grantedAt
        ];
    }
    
    /**
     * Đếm số lượng users
     */
    public function count($conditions = []) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name;
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $key => $value) {
                $whereClause[] = "$key = :$key";
                $params[$key] = $value;
            }
            $query .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Đếm số admin
     */
    public function countAdmins() {
        return $this->count(['role' => 'admin']);
    }
    
    /**
     * Xóa user
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    /**
     * Lấy tất cả users
     */
    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT id, username, email, display_name, role, is_active, credits, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cập nhật credits của user
     */
    public function updateCredits() {
        $query = "UPDATE " . $this->table_name . " 
                  SET credits = :credits, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":credits", $this->credits, PDO::PARAM_INT);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
?>