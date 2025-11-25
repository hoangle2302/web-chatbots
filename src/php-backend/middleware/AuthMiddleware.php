<?php
/**
 * 🔐 MIDDLEWARE XÁC THỰC JWT
 * Xử lý authentication và authorization
 */
class AuthMiddleware {
    private $secret_key;
    private $algorithm = 'HS256';
    
    public function __construct() {
        $this->loadConfig();
        $this->secret_key = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this-in-production';
    }
    
    /**
     * Load cấu hình từ file config.env
     */
    private function loadConfig() {
        $envFiles = [
            __DIR__ . '/../../config.env',
            __DIR__ . '/../../../config.env',
            __DIR__ . '/../../../../config.env'
        ];

        foreach ($envFiles as $envFile) {
            if (!file_exists($envFile)) {
                continue;
            }

            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }

            break;
        }
    }
    
    /**
     * Tạo JWT token
     */
    public function generateToken($user_id, $username, $role = 'user') {
        $payload = [
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        return $this->encodeJWT($payload);
    }
    
    /**
     * Xác thực JWT token
     */
    public function validateToken($token) {
        try {
            $payload = $this->decodeJWT($token);
            
            // Kiểm tra expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }
            
            return $payload;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy thông tin user từ token
     */
    public function getCurrentUser($token) {
        $payload = $this->validateToken($token);
        return $payload ? $payload : null;
    }
    
    /**
     * Kiểm tra user đã đăng nhập
     */
    public function isAuthenticated() {
        $authHeader = $this->getAuthorizationHeader();
        
        if (!$this->isBearerHeader($authHeader)) {
            return false;
        }
        
        $token = substr($authHeader, 7);
        return $this->validateToken($token) !== false;
    }
    
    /**
     * Lấy token từ request
     */
    public function getTokenFromRequest() {
        $authHeader = $this->getAuthorizationHeader();
        
        if (!$this->isBearerHeader($authHeader)) {
            return null;
        }
        
        return substr($authHeader, 7);
    }

    /**
     * Lấy Authorization header, hỗ trợ các server khác nhau
     */
    private function getAuthorizationHeader() {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        return is_string($authHeader) ? trim($authHeader) : null;
    }

    /**
     * Kiểm tra header có phải dạng Bearer token không
     */
    private function isBearerHeader($authHeader) {
        if (!$authHeader || !is_string($authHeader)) {
            return false;
        }

        return stripos($authHeader, 'Bearer ') === 0;
    }
    
    /**
     * Kiểm tra quyền admin
     */
    public function isAdmin($token) {
        $payload = $this->validateToken($token);
        return $payload && $payload['role'] === 'admin';
    }
    
    /**
     * Yêu cầu authentication cho API
     */
    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
            exit;
        }
    }
    
    /**
     * Encode JWT token
     */
    private function encodeJWT($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload = json_encode($payload);
        
        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret_key, true);
        $base64Signature = $this->base64UrlEncode($signature);
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Decode JWT token
     */
    private function decodeJWT($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }
        
        list($base64Header, $base64Payload, $base64Signature) = $parts;
        
        // Verify signature
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret_key, true);
        $expectedSignature = $this->base64UrlEncode($signature);
        
        if (!hash_equals($base64Signature, $expectedSignature)) {
            throw new Exception('Invalid signature');
        }
        
        $payload = json_decode($this->base64UrlDecode($base64Payload), true);
        
        if (!$payload) {
            throw new Exception('Invalid payload');
        }
        
        return $payload;
    }
    
    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
?>