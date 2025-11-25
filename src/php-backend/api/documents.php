<?php
/**
 * Document Management API endpoints
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$document = new Document($db);
$log = new Log($db);
$auth = new AuthMiddleware();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Require authentication for all document operations
$auth->requireAuth();
$token = $auth->getTokenFromRequest();
$current_user = $auth->getCurrentUser($token);

// Route requests
switch ($method) {
    case 'GET':
        switch ($action) {
            case 'list':
                handleListDocuments($document, $current_user);
                break;
            case 'get':
                handleGetDocument($document, $current_user);
                break;
            case 'download':
                handleDownloadDocument($document, $current_user);
                break;
            case 'search':
                handleSearchDocuments($document, $current_user);
                break;
            case 'stats':
                handleGetStats($document, $current_user);
                break;
            default:
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Action not found',
                    'code' => 'NOT_FOUND'
                ]);
        }
        break;
    case 'POST':
        switch ($action) {
            case 'upload':
                handleUploadDocument($document, $log, $current_user);
                break;
            default:
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Action not found',
                    'code' => 'NOT_FOUND'
                ]);
        }
        break;
    case 'PUT':
        switch ($action) {
            case 'update':
                handleUpdateDocument($document, $log, $current_user);
                break;
            default:
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Action not found',
                    'code' => 'NOT_FOUND'
                ]);
        }
        break;
    case 'DELETE':
        switch ($action) {
            case 'delete':
                handleDeleteDocument($document, $log, $current_user);
                break;
            default:
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Action not found',
                    'code' => 'NOT_FOUND'
                ]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed',
            'code' => 'METHOD_NOT_ALLOWED'
        ]);
}

/**
 * Handle list documents
 */
function handleListDocuments($document, $current_user) {
    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $file_type = $_GET['file_type'] ?? '';
        
        $offset = ($page - 1) * $limit;
        
        if ($file_type) {
            $documents = $document->getByFileType($current_user['user_id'], $file_type, $limit, $offset);
        } else {
            $documents = $document->getByUserId($current_user['user_id'], $limit, $offset);
        }
        
        $total = $document->getCountByUserId($current_user['user_id']);
        
        // Format response
        $formatted_docs = array_map(function($doc) {
            return [
                'id' => $doc['id'],
                'filename' => $doc['filename'],
                'original_name' => $doc['original_name'],
                'file_type' => $doc['file_type'],
                'file_size' => $doc['file_size'],
                'created_at' => $doc['created_at'],
                'updated_at' => $doc['updated_at']
            ];
        }, $documents);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [
                'documents' => $formatted_docs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("List documents error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'code' => 'INTERNAL_ERROR'
        ]);
    }
}

/**
 * Handle get single document
 */
function handleGetDocument($document, $current_user) {
    try {
        $document_id = $_GET['id'] ?? '';
        
        if (empty($document_id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Document ID is required',
                'code' => 'MISSING_ID'
            ]);
            return;
        }
        
        // Check if document belongs to user
        if (!$document->belongsToUser($document_id, $current_user['user_id'])) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Document not found',
                'code' => 'NOT_FOUND'
            ]);
            return;
        }
        
        $doc = $document->getById($document_id);
        
        if (!$doc) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Document not found',
                'code' => 'NOT_FOUND'
            ]);
            return;
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $doc['id'],
                'filename' => $doc['filename'],
                'original_name' => $doc['original_name'],
                'file_path' => $doc['file_path'],
                'file_type' => $doc['file_type'],
                'file_size' => $doc['file_size'],
                'content' => $doc['content'],
                'created_at' => $doc['created_at'],
                'updated_at' => $doc['updated_at']
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Get document error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'code' => 'INTERNAL_ERROR'
        ]);
    }
}

/**
 * Handle download document
 */
function handleDownloadDocument($document, $current_user) {
    try {
        $document_id = $_GET['id'] ?? '';
        
        if (empty($document_id)) {
            // Return JSON error
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Document ID is required',
                'code' => 'MISSING_ID'
            ]);
            exit;
        }
        
        // Check if document belongs to user
        if (!$document->belongsToUser($document_id, $current_user['user_id'])) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Document not found',
                'code' => 'NOT_FOUND'
            ]);
            exit;
        }
        
        $doc = $document->getById($document_id);
        
        if (!$doc) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Document not found',
                'code' => 'NOT_FOUND'
            ]);
            exit;
        }
        
        // Get file path - try multiple locations
        $file_path = $doc['file_path'];
        $filename = $doc['filename'];
        
        // List of possible upload directories
        $possible_dirs = [
            $file_path, // Try original path first
            __DIR__ . '/../../data/uploads/' . $filename,
            __DIR__ . '/../../../data/uploads/' . $filename,
            __DIR__ . '/../../src/data/uploads/' . $filename,
            __DIR__ . '/../../../src/data/uploads/' . $filename,
        ];
        
        // Find the file
        $found_file = null;
        foreach ($possible_dirs as $path) {
            if (file_exists($path)) {
                $found_file = $path;
                break;
            }
        }
        
        // If file not found, return error
        if (!$found_file) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'File not found on server. Tried paths: ' . implode(', ', $possible_dirs),
                'code' => 'FILE_NOT_FOUND'
            ]);
            exit;
        }
        
        $file_path = $found_file;
        
        // Get file info
        $file_size = filesize($file_path);
        $file_type = $doc['file_type'] ?? mime_content_type($file_path);
        $original_name = $doc['original_name'] ?? $doc['filename'];
        
        // Clean output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Clear all previous headers
        header_remove();
        
        // Set headers for file download
        header('Content-Type: ' . ($file_type ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . addslashes($original_name) . '"');
        header('Content-Length: ' . $file_size);
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization');
        
        // Disable output buffering
        @ini_set('output_buffering', 'Off');
        @ini_set('zlib.output_compression', 'Off');
        
        // Output file
        readfile($file_path);
        exit;
        
    } catch (Exception $e) {
        error_log("Download document error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Clean output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error: ' . $e->getMessage(),
            'code' => 'INTERNAL_ERROR'
        ]);
        exit;
    }
}

/**
 * Handle search documents
 */
function handleSearchDocuments($document, $current_user) {
    try {
        $keyword = $_GET['q'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        
        if (empty($keyword)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Search keyword is required',
                'code' => 'MISSING_KEYWORD'
            ]);
            return;
        }
        
        $offset = ($page - 1) * $limit;
        $documents = $document->searchByUserId($current_user['user_id'], $keyword, $limit, $offset);
        
        // Format response
        $formatted_docs = array_map(function($doc) {
            return [
                'id' => $doc['id'],
                'filename' => $doc['filename'],
                'original_name' => $doc['original_name'],
                'file_type' => $doc['file_type'],
                'file_size' => $doc['file_size'],
                'created_at' => $doc['created_at'],
                'updated_at' => $doc['updated_at']
            ];
        }, $documents);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [
                'documents' => $formatted_docs,
                'keyword' => $keyword,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => count($formatted_docs),
                    'pages' => ceil(count($formatted_docs) / $limit)
                ]
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Search documents error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'code' => 'INTERNAL_ERROR'
        ]);
    }
}

/**
 * Handle upload document
 */
function handleUploadDocument($document, $log, $current_user) {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'No file uploaded or upload error',
                'code' => 'UPLOAD_ERROR'
            ]);
            return;
        }
        
        $file = $_FILES['file'];
        $original_name = $file['name'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_type = $file['type'];
        
        // Validate file size (max 10MB)
        $max_size = 10 * 1024 * 1024; // 10MB
        if ($file_size > $max_size) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'File size too large. Maximum 10MB allowed.',
                'code' => 'FILE_TOO_LARGE'
            ]);
            return;
        }
        
        // Validate file type
        $allowed_types = [
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/json',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/csv',
            'application/rtf'
        ];
        
        if (!in_array($file_type, $allowed_types)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'File type not allowed',
                'code' => 'INVALID_FILE_TYPE'
            ]);
            return;
        }
        
        // Create upload directory if not exists
        $upload_dir = __DIR__ . '/../../data/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file_tmp, $file_path)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save file',
                'code' => 'SAVE_FAILED'
            ]);
            return;
        }
        
        // Extract content for text files
        $content = null;
        if (strpos($file_type, 'text/') === 0 || $file_type === 'application/json') {
            $content = file_get_contents($file_path);
            // Limit content size for database storage
            if (strlen($content) > 1000000) { // 1MB limit for content
                $content = substr($content, 0, 1000000) . '... [Content truncated]';
            }
        }
        
        // Save document record
        $document->user_id = $current_user['user_id'];
        $document->filename = $filename;
        $document->original_name = $original_name;
        $document->file_path = $file_path;
        $document->file_type = $file_type;
        $document->file_size = $file_size;
        $document->content = $content;
        
        if ($document->create()) {
            // Log upload
            $log->user_id = $current_user['user_id'];
            $log->action = 'document_uploaded';
            $log->detail = "Document uploaded: {$original_name}";
            $log->create();
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => [
                    'id' => $document->id,
                    'filename' => $document->filename,
                    'original_name' => $document->original_name,
                    'file_type' => $document->file_type,
                    'file_size' => $document->file_size,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            // Clean up uploaded file if database save failed
            unlink($file_path);
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save document record',
                'code' => 'SAVE_FAILED'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Upload document error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'code' => 'INTERNAL_ERROR'
        ]);
    }
}

/**
 * Handle update document
 */
function handleUpdateDocument($document, $log, $current_user) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Document ID is required',
                'code' => 'MISSING_ID'
            ]);
            return;
        }
        
        // Check if document belongs to user
        if (!$document->belongsToUser($input['id'], $current_user['user_id'])) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Document not found',
                'code' => 'NOT_FOUND'
            ]);
            return;
        }
        
        // Update document
        $document->id = $input['id'];
        $document->user_id = $current_user['user_id'];
        $document->filename = $input['filename'] ?? '';
        $document->original_name = $input['original_name'] ?? '';
        $document->file_path = $input['file_path'] ?? '';
        $document->file_type = $input['file_type'] ?? '';
        $document->file_size = $input['file_size'] ?? 0;
        $document->content = $input['content'] ?? null;
        
        if ($document->update()) {
            // Log update
            $log->user_id = $current_user['user_id'];
            $log->action = 'document_updated';
            $log->detail = "Document updated: ID {$input['id']}";
            $log->create();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Document updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update document',
                'code' => 'UPDATE_FAILED'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Update document error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'code' => 'INTERNAL_ERROR'
        ]);
    }
}

/**
 * Handle delete document
 */
function handleDeleteDocument($document, $log, $current_user) {
    try {
        $document_id = $_GET['id'] ?? '';
        
        if (empty($document_id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Document ID is required',
                'code' => 'MISSING_ID'
            ]);
            return;
        }
        
        // Check if document belongs to user
        if (!$document->belongsToUser($document_id, $current_user['user_id'])) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Document not found',
                'code' => 'NOT_FOUND'
            ]);
            return;
        }
        
        // Get document info before deletion
        $doc = $document->getById($document_id);
        
        // Delete document record
        $document->id = $document_id;
        $document->user_id = $current_user['user_id'];
        
        if ($document->delete()) {
            // Delete physical file
            if ($doc && file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
            
            // Log deletion
            $log->user_id = $current_user['user_id'];
            $log->action = 'document_deleted';
            $log->detail = "Document deleted: {$doc['original_name']}";
            $log->create();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete document',
                'code' => 'DELETE_FAILED'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Delete document error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'code' => 'INTERNAL_ERROR'
        ]);
    }
}

/**
 * Handle get file statistics
 */
function handleGetStats($document, $current_user) {
    try {
        $stats = $document->getFileStats($current_user['user_id']);
        $total_files = $document->getCountByUserId($current_user['user_id']);
        
        $total_size = 0;
        $file_types = [];
        
        foreach ($stats as $stat) {
            $total_size += $stat['total_size'] ?? 0;
            $file_types[] = [
                'type' => $stat['file_type'],
                'count' => $stat['count_by_type'],
                'size' => $stat['total_size'] ?? 0
            ];
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [
                'total_files' => $total_files,
                'total_size' => $total_size,
                'file_types' => $file_types
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Get stats error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'code' => 'INTERNAL_ERROR'
        ]);
    }
}
?>

