<?php
/**
 * 🏥 API KIỂM TRA SỨC KHỎE HỆ THỐNG
 * Kiểm tra trạng thái hoạt động của hệ thống
 */

// ===== HEADERS =====
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Xử lý preflight OPTIONS request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ===== HEALTH CHECK =====
try {
    // Kiểm tra database connection
    $dbStatus = 'unknown';
    $dbError = null;
    
    try {
        require_once __DIR__ . '/../config/Database.php';
        $database = new Database();
        $db = $database->getConnection();
        $dbStatus = 'connected';
    } catch (Exception $e) {
        $dbStatus = 'error';
        $dbError = $e->getMessage();
    }
    
    // Kiểm tra các service quan trọng
    $services = [
        'database' => $dbStatus,
        'php_version' => PHP_VERSION,
        'server_time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get()
    ];
    
    // Thêm thông tin database nếu có lỗi
    if ($dbError) {
        $services['database_error'] = $dbError;
    }
    
    // Response
    $response = [
        'success' => true,
        'status' => 'healthy',
        'message' => 'Hệ thống hoạt động bình thường',
        'data' => $services,
        'timestamp' => time()
    ];
    
    // Nếu database có lỗi, đánh dấu status
    if ($dbStatus === 'error') {
        $response['status'] = 'degraded';
        $response['message'] = 'Hệ thống hoạt động nhưng database có vấn đề';
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
        'timestamp' => time()
    ]);
}
?>