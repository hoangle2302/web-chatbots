<?php
/**
 * User Service - Xử lý logic người dùng
 */
class UserService {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Đăng nhập
     */
    public function login($username, $password) {
        $user = new User($this->conn);
        $userData = $user->getByUsername($username);
        
        if (!$userData) {
            return false;
        }
        
        // Kiểm tra password
        if (!password_verify($password, $userData['password'])) {
            // Tăng số lần đăng nhập sai
            $user->id = $userData['id'];
            $user->updateFailedLogin();
            return false;
        }
        
        // Reset số lần đăng nhập sai
        $user->id = $userData['id'];
        $user->resetFailedLogin();
        // Cập nhật thời gian đăng nhập gần nhất
        $user->updateLastLoginAt();
        
        // Ghi log
        $this->logUserAction($userData['id'], 'login', 'User logged in successfully');
        
        return $userData;
    }
    
    /**
     * Đăng ký
     */
    public function register($username, $password, $email = null, $displayName = null) {
        // Kiểm tra username đã tồn tại
        $user = new User($this->conn);
        $existingUser = $user->getByUsername($username);
        
        if ($existingUser) {
            throw new Exception('Username already exists');
        }
        
        // Tạo user mới
        $user->username = $username;
        $user->email = $email;
        $user->display_name = $displayName;
        $user->password = $password;
        $user->role = 'user';
        $user->is_active = 1;
        
        if ($user->create()) {
            // Ghi log
            $this->logUserAction($user->id, 'register', 'User registered successfully');
            return $user;
        }
        
        return false;
    }
    
    /**
     * Lấy thông tin user theo ID
     */
    public function getById($id) {
        $user = new User($this->conn);
        return $user->getById($id);
    }
    
    /**
     * Cập nhật thông tin user
     */
    public function update($id, $data) {
        $user = new User($this->conn);
        $userData = $user->getById($id);
        
        if (!$userData) {
            return false;
        }
        
        $query = "UPDATE users SET ";
        $params = [];
        $setParts = [];
        
        if (isset($data['username'])) {
            $setParts[] = "username = :username";
            $params[':username'] = $data['username'];
        }
        
        if (isset($data['email'])) {
            $setParts[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        
        if (isset($data['display_name'])) {
            $setParts[] = "display_name = :display_name";
            $params[':display_name'] = $data['display_name'];
        }
        
        if (isset($data['password'])) {
            $setParts[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['role'])) {
            $setParts[] = "role = :role";
            $params[':role'] = $data['role'];
        }
        
        if (isset($data['is_active'])) {
            $setParts[] = "is_active = :is_active";
            $params[':is_active'] = $data['is_active'];
        }
        
        if (empty($setParts)) {
            return false;
        }
        
        $query .= implode(', ', $setParts);
        $query .= " WHERE id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute($params);
        
        if ($result) {
            $this->logUserAction($id, 'update', 'User profile updated');
        }
        
        return $result;
    }
    
    /**
     * Xóa user
     */
    public function delete($id) {
        $user = new User($this->conn);
        $userData = $user->getById($id);
        
        if (!$userData) {
            return false;
        }
        
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        $result = $stmt->execute();
        
        if ($result) {
            $this->logUserAction($id, 'delete', 'User deleted');
        }
        
        return $result;
    }
    
    /**
     * Lấy danh sách tất cả users
     */
    public function getAll($limit = 100) {
        $query = "SELECT id, username, email, display_name, role, is_active, credits, created_at FROM users ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ghi log hành động của user
     */
    private function logUserAction($userId, $action, $detail = '') {
        $log = new Log($this->conn);
        $log->user_id = $userId;
        $log->action = $action;
        $log->detail = $detail;
        // capture best-effort client info if available in server vars
        $log->ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $log->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $log->create();
    }
    
    /**
     * Kiểm tra quyền admin
     */
    public function isAdmin($userId) {
        $user = $this->getById($userId);
        return $user && $user['role'] === 'admin';
    }
    
    /**
     * Lấy thống kê user
     */
    public function getStats($userId) {
        $stats = [];
        
        // Số lượng query AI
        $query = "SELECT COUNT(*) as total FROM ai_query_history WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $stats['total_queries'] = $stmt->fetch()['total'];
        
        // Số lượng documents
        $query = "SELECT COUNT(*) as total FROM documents WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $stats['total_documents'] = $stmt->fetch()['total'];
        
        // Query gần nhất
        $query = "SELECT timestamp FROM ai_query_history WHERE user_id = :user_id ORDER BY timestamp DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $lastQuery = $stmt->fetch();
        $stats['last_query'] = $lastQuery ? $lastQuery['timestamp'] : null;
        
        return $stats;
    }
}
?>

