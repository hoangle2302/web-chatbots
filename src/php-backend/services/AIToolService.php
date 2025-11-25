<?php

require_once __DIR__ . '/../config/Config.php';

class AIToolService
{
    private $baseUrl;
    private $timeout;
    private $internalKey;

    public function __construct()
    {
        $config = new Config();
        $aiToolConfig = $config->getAiToolConfig();

        // URL FastAPI nội bộ nhận request xử lý file
        $this->baseUrl = rtrim($aiToolConfig['base_url'] ?? 'http://127.0.0.1:8001', '/');
        // Thời gian chờ tối đa khi gọi qua mạng nội bộ
        $this->timeout = (int)($aiToolConfig['timeout'] ?? 120);
        // Khóa nội bộ (nếu cấu hình) để xác thực giữa PHP ↔ FastAPI
        $this->internalKey = $aiToolConfig['api_key'] ?? '';
    }

    /**
     * Gửi file và prompt tới AI tool, nhận về kết quả hoặc file.
     */
    public function processFile(string $filePath, string $originalName, string $prompt, string $outputFormat = 'auto'): array
    {
        if (!is_readable($filePath)) {
            throw new InvalidArgumentException('File đầu vào không hợp lệ hoặc không thể đọc.');
        }

        $prompt = trim($prompt);
        if ($prompt === '') {
            throw new InvalidArgumentException('Prompt không được để trống.');
        }

        // Đoán mime-type để FastAPI biết kiểu file nhận được
        $mime = $this->detectMimeType($filePath, $originalName);
        $curlFile = new CURLFile($filePath, $mime, $originalName ?: basename($filePath));

        $postFields = [
            'file' => $curlFile,
            'user_prompt' => $prompt,
            'output_format' => $outputFormat ?: 'auto'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/process-file',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => $this->timeout,
        ]);

        $headers = [];
        if (!empty($this->internalKey)) {
            // Header xác thực nội bộ (FastAPI có thể kiểm tra)
            $headers[] = 'X-Internal-Key: ' . $this->internalKey;
            $headers[] = 'Authorization: Bearer ' . $this->internalKey;
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Không thể kết nối tới AI tool: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $headerString = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        if ($statusCode >= 400) {
            $decoded = json_decode($body, true);
            $message = is_array($decoded) && isset($decoded['error']) ? $decoded['error'] : $body;
            throw new RuntimeException("AI tool trả về lỗi ({$statusCode}): " . $message);
        }

        $contentType = $this->extractHeader($headerString, 'Content-Type');
        $contentDisposition = $this->extractHeader($headerString, 'Content-Disposition');

        if ($contentDisposition) {
            // FastAPI trả về file: lưu ra file tạm rồi trả đường dẫn cho PHP API
            $filename = $this->parseFilenameFromDisposition($contentDisposition) ?? 'result.bin';
            $tmpPath = $this->storeTempFile($body, $filename);

            return [
                'type' => 'file',
                'path' => $tmpPath,
                'filename' => $filename,
                'mime_type' => $contentType ?: 'application/octet-stream'
            ];
        }

        // Trường hợp trả JSON/text thuần
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'type' => 'json',
                'data' => $decoded
            ];
        }

        return [
            'type' => 'text',
            'data' => $body
        ];
    }

    private function detectMimeType(string $filePath, string $originalName): string
    {
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($filePath);
            if ($mime !== false) {
                return $mime;
            }
        }

        // fallback: dựa vào đuôi file để chọn mime-type cơ bản
        $ext = strtolower(pathinfo($originalName ?: $filePath, PATHINFO_EXTENSION));
        $map = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'txt' => 'text/plain',
            'json' => 'application/json',
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'md' => 'text/markdown',
            'html' => 'text/html',
            'py' => 'text/x-python',
            'js' => 'text/javascript',
            'ts' => 'text/plain'
        ];

        return $map[$ext] ?? 'application/octet-stream';
    }

    private function extractHeader(string $headers, string $name): ?string
    {
        $pattern = '/^' . preg_quote($name, '/') . ':\s*(.+)$/mi';
        if (preg_match($pattern, $headers, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function parseFilenameFromDisposition(string $disposition): ?string
    {
        if (preg_match('/filename\*=UTF-8\'\'([^;]+)/', $disposition, $matches)) {
            return rawurldecode(trim($matches[1], '"'));
        }

        if (preg_match('/filename="?([^";]+)"?/', $disposition, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function storeTempFile(string $content, string $filename): string
    {
        // Lưu nội dung nhận được vào file tạm (để PHP trả về cho client)
        $tmpPath = tempnam(sys_get_temp_dir(), 'aitool_');
        if ($tmpPath === false) {
            throw new RuntimeException('Không thể tạo file tạm để lưu kết quả.');
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (!empty($extension)) {
            $newPath = $tmpPath . '.' . $extension;
            if (!@rename($tmpPath, $newPath)) {
                // Nếu rename thất bại, vẫn sử dụng đường dẫn cũ
                $newPath = $tmpPath;
            }
            $tmpPath = $newPath;
        }

        if (file_put_contents($tmpPath, $content) === false) {
            throw new RuntimeException('Không thể ghi dữ liệu kết quả vào file tạm.');
        }

        return $tmpPath;
    }
}


