<?php
/**
 * 📄 MODEL TÀI LIỆU
 * Quản lý tài liệu của người dùng
 */
class Document {
    private $conn;
    private $table_name = "documents";
    
    // Properties
    public $id;
    public $user_id;
    public $filename;
    public $original_name;
    public $file_path;
    public $file_size;
    public $file_type;
    public $content;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Tạo document mới
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, filename, original_name, file_path, file_size, file_type, content) 
                  VALUES (:user_id, :filename, :original_name, :file_path, :file_size, :file_type, :content)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->filename = htmlspecialchars(strip_tags($this->filename));
        $this->original_name = htmlspecialchars(strip_tags($this->original_name ?? ''));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));
        $this->file_type = htmlspecialchars(strip_tags($this->file_type));
        
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":filename", $this->filename);
        $stmt->bindParam(":original_name", $this->original_name);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":file_size", $this->file_size);
        $stmt->bindParam(":file_type", $this->file_type);
        $stmt->bindParam(":content", $this->content);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    /**
     * Lấy document theo ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy documents theo user ID
     */
    public function getByUserId($userId, $limit = 50, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Đếm số documents theo user ID
     */
    public function getCountByUserId($userId) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Lấy documents theo file type
     */
    public function getByFileType($userId, $fileType, $limit = 50, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND file_type LIKE :file_type
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $fileTypePattern = '%' . $fileType . '%';
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":file_type", $fileTypePattern);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Tìm kiếm documents
     */
    public function searchByUserId($userId, $keyword, $limit = 50, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  AND (original_name LIKE :keyword OR filename LIKE :keyword OR content LIKE :keyword)
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $searchPattern = '%' . $keyword . '%';
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":keyword", $searchPattern);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Kiểm tra document thuộc về user
     */
    public function belongsToUser($documentId, $userId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $documentId);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Lấy thống kê file
     */
    public function getFileStats($userId) {
        $query = "SELECT 
                    file_type,
                    COUNT(*) as count_by_type,
                    SUM(file_size) as total_size
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id
                  GROUP BY file_type";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy tất cả documents
     */
    public function getAll($limit = 100, $offset = 0) {
        $query = "SELECT d.*, u.username 
                  FROM " . $this->table_name . " d
                  LEFT JOIN users u ON d.user_id = u.id
                  ORDER BY d.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cập nhật document
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET filename = :filename, file_path = :file_path, 
                      file_size = :file_size, file_type = :file_type
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->filename = htmlspecialchars(strip_tags($this->filename));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));
        $this->file_type = htmlspecialchars(strip_tags($this->file_type));
        
        $stmt->bindParam(":filename", $this->filename);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":file_size", $this->file_size);
        $stmt->bindParam(":file_type", $this->file_type);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Xóa document
     */
    public function delete() {
        if (!$this->id) {
            return false;
        }
        
        // Lấy thông tin file trước khi xóa
        $document = $this->getById($this->id);
        if (!$document) {
            return false;
        }
        
        // Xóa file vật lý
        if (file_exists($document['file_path'])) {
            unlink($document['file_path']);
        }
        
        // Xóa record trong database
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Thống kê storage theo user
     */
    public function getStorageStatsByUser($userId) {
        $query = "SELECT 
                    COUNT(*) as total_files,
                    SUM(file_size) as total_size,
                    AVG(file_size) as avg_size
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Xóa documents cũ
     */
    public function deleteOldDocuments($days = 30) {
        // Lấy danh sách documents cũ
        $query = "SELECT id, file_path FROM " . $this->table_name . " 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":days", $days, PDO::PARAM_INT);
        $stmt->execute();
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Xóa files vật lý
        foreach ($documents as $doc) {
            if (file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
        }
        
        // Xóa records trong database
        $deleteQuery = "DELETE FROM " . $this->table_name . " 
                        WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $deleteStmt = $this->conn->prepare($deleteQuery);
        $deleteStmt->bindParam(":days", $days, PDO::PARAM_INT);
        
        return $deleteStmt->execute();
    }
}
?>