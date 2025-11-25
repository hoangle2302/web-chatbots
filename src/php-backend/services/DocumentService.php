<?php
/**
 * Document Service - Xử lý upload và phân tích tài liệu
 */
class DocumentService {
    private $uploadDir;
    private $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    public function __construct() {
        $this->uploadDir = __DIR__ . '/../../data/uploads/';
        
        // Tạo thư mục upload nếu chưa tồn tại
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Xử lý upload file
     */
    public function processUpload($file) {
        // Kiểm tra lỗi upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload failed: ' . $this->getUploadErrorMessage($file['error']));
        }
        
        // Kiểm tra kích thước file
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File too large. Maximum size: ' . ($this->maxFileSize / 1024 / 1024) . 'MB');
        }
        
        // Lấy thông tin file
        $originalName = $file['name'];
        $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // Kiểm tra loại file
        if (!in_array($fileType, $this->allowedTypes)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes));
        }
        
        // Tạo tên file unique
        $filename = uniqid() . '_' . time() . '.' . $fileType;
        $filePath = $this->uploadDir . $filename;
        
        // Di chuyển file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Trích xuất nội dung
        $content = $this->extractContent($filePath, $fileType);
        
        return [
            'filename' => $filename,
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $file['size'],
            'content' => $content
        ];
    }
    
    /**
     * Trích xuất nội dung từ file
     */
    private function extractContent($filePath, $fileType) {
        switch ($fileType) {
            case 'txt':
                return $this->extractFromTxt($filePath);
                
            case 'pdf':
                return $this->extractFromPdf($filePath);
                
            case 'doc':
            case 'docx':
                return $this->extractFromWord($filePath);
                
            case 'xls':
            case 'xlsx':
                return $this->extractFromExcel($filePath);
                
            default:
                return 'Nội dung không thể trích xuất từ loại file này.';
        }
    }
    
    /**
     * Trích xuất từ file TXT
     */
    private function extractFromTxt($filePath) {
        $content = file_get_contents($filePath);
        return $this->cleanText($content);
    }
    
    /**
     * Trích xuất từ file PDF
     */
    private function extractFromPdf($filePath) {
        // Sử dụng shell command để trích xuất PDF
        $command = "pdftotext -layout \"$filePath\" -";
        $content = shell_exec($command);
        
        if ($content === null) {
            return 'Không thể trích xuất nội dung từ file PDF. Vui lòng cài đặt pdftotext.';
        }
        
        return $this->cleanText($content);
    }
    
    /**
     * Trích xuất từ file Word
     */
    private function extractFromWord($filePath) {
        // Sử dụng shell command để trích xuất Word
        $command = "unzip -p \"$filePath\" word/document.xml | sed -e 's/<[^>]*>//g'";
        $content = shell_exec($command);
        
        if ($content === null) {
            return 'Không thể trích xuất nội dung từ file Word. Vui lòng cài đặt unzip.';
        }
        
        return $this->cleanText($content);
    }
    
    /**
     * Trích xuất từ file Excel
     */
    private function extractFromExcel($filePath) {
        // Sử dụng shell command để trích xuất Excel
        $command = "unzip -p \"$filePath\" xl/sharedStrings.xml | sed -e 's/<[^>]*>//g'";
        $content = shell_exec($command);
        
        if ($content === null) {
            return 'Không thể trích xuất nội dung từ file Excel. Vui lòng cài đặt unzip.';
        }
        
        return $this->cleanText($content);
    }
    
    /**
     * Làm sạch text
     */
    private function cleanText($text) {
        // Loại bỏ HTML tags
        $text = strip_tags($text);
        
        // Loại bỏ ký tự đặc biệt
        $text = preg_replace('/[^\p{L}\p{N}\s.,!?;:()-]/u', ' ', $text);
        
        // Chuẩn hóa khoảng trắng
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Loại bỏ dòng trống
        $text = preg_replace('/\n\s*\n/', "\n", $text);
        
        return trim($text);
    }
    
    /**
     * Lấy thông báo lỗi upload
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File too large';
            case UPLOAD_ERR_PARTIAL:
                return 'File upload incomplete';
            case UPLOAD_ERR_NO_FILE:
                return 'No file uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Xóa file
     */
    public function deleteFile($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Lấy danh sách file đã upload
     */
    public function getUploadedFiles() {
        $files = [];
        $dir = opendir($this->uploadDir);
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $filePath = $this->uploadDir . $file;
                $files[] = [
                    'filename' => $file,
                    'file_path' => $filePath,
                    'file_size' => filesize($filePath),
                    'created_at' => date('Y-m-d H:i:s', filemtime($filePath))
                ];
            }
        }
        
        closedir($dir);
        return $files;
    }
}
?>

