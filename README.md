# 🧠 Thư Viện AI – Nền tảng Chat Đa Mô Hình AI

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4.svg)](https://www.php.net/)
[![Python](https://img.shields.io/badge/Python-3.10%2B-3776ab.svg)](https://www.python.org/)
[![FastAPI](https://img.shields.io/badge/FastAPI-ready-009485.svg)](https://fastapi.tiangolo.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1.svg)](https://www.mysql.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> **Thư Viện AI** là nền tảng chat AI đa mô hình với hơn **500+ AI models**, hỗ trợ xử lý tài liệu, tạo file tự động, và quản lý người dùng. Dự án kết hợp **PHP backend**, **FastAPI microservice** và **frontend thuần HTML/CSS/JS**.

---

## 📑 Mục lục

- [✨ Tính năng nổi bật](#-tính-năng-nổi-bật)
- [🏗 Kiến trúc hệ thống](#-kiến-trúc-hệ-thống)
- [🧰 Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
- [🚀 Hướng dẫn cài đặt](#-hướng-dẫn-cài-đặt)
- [⚙️ Cấu hình](#️-cấu-hình)
- [🎯 Sử dụng hệ thống](#-sử-dụng-hệ-thống)
- [📡 API Documentation](#-api-documentation)
- [🤝 Đóng góp](#-đóng-góp)

---

## ✨ Tính năng nổi bật

### 👤 Người dùng 

- **💬 Chat với 500+ AI Models**
  - GPT-4, Claude 3.5, Gemini Pro, Qwen, DeepSeek, Doubao...
  - Hỗ trợ text, image, audio, video models
  - Tìm kiếm và lọc models theo nhà cung cấp, tính năng

- **📄 Quản lý tài liệu**
  - Upload tài liệu: PDF, DOCX, XLSX, TXT, CSV...
  - Xem trước tài liệu trực tuyến
  - Download tài liệu đã upload
  - Phân tích tài liệu bằng AI
  - OCR (Nhận dạng văn bản từ hình ảnh)

- **📝 Tạo file tự động**
  - Tạo file Python, JavaScript, Markdown, HTML, CSS...
  - Sinh code theo yêu cầu từ AI
  - Download file đã tạo

- **💾 Lưu trữ và quản lý**
  - Lưu lịch sử hội thoại trong localStorage
  - Quản lý nhiều cuộc trò chuyện
  - Lưu lịch sử trên server
  - Tìm kiếm lịch sử chat

- **💰 Hệ thống Credits**
  - Mỗi câu hỏi trừ 1 credit
  - Nạp credits qua admin
  - Theo dõi số credits còn lại

### 👨‍💼 Quản trị viên

- **📊 Dashboard thống kê**
  - Tổng số người dùng, credits đã sử dụng
  - Thống kê tài liệu đã upload
  - Nhật ký hoạt động hệ thống

- **👥 Quản lý người dùng**
  - Xem danh sách người dùng
  - Khóa/mở khóa tài khoản
  - Cấp/thu hồi credits
  - Xem lịch sử hoạt động

- **🔧 Cấu hình hệ thống**
  - Quản lý API keys (Key4U, OpenAI)
  - Cấu hình môi trường
  - Xem logs hệ thống

### 🚀 AI Tool

- **🔄 Xử lý tài liệu**
  - Trích xuất nội dung từ PDF, DOCX, XLSX, TXT
  - OCR từ hình ảnh (PDF với hình ảnh)
  - Phân tích và xử lý nội dung

- **🤖 Tích hợp AI**
  - Giao tiếp với Key4U API
  - Hỗ trợ OpenAI API
  - Đồng bộ API keys giữa PHP và Python

- **📁 Tạo file**
  - Sinh code theo yêu cầu
  - Tạo file với nhiều định dạng
  - Hỗ trợ download file

---

## 🏗 Kiến trúc hệ thống

### Cấu trúc thư mục

```
chatbots-web/
├── config.env                  # Cấu hình chung (API keys, database)
├── config.env.example          # File mẫu cấu hình
├── requirements.txt            # Python dependencies
├── start.bat                   # Script khởi động hệ thống (Windows)
├── README.md                   # Tài liệu này
│
├── data/                       # Dữ liệu và uploads
│   ├── database/               # Database schema
│   │   └── thuvien_ai.sql      # SQL schema
│   └── uploads/                # Thư mục lưu file upload
│
└── src/
    ├── php-backend/            # Backend PHP
    │   ├── api/                # API endpoints
    │   │   ├── auth.php        # Authentication (login, register)
    │   │   ├── chat-real.php   # Chat với AI models
    │   │   ├── ai-tool.php     # Proxy đến FastAPI
    │   │   ├── documents.php   # Quản lý tài liệu
    │   │   ├── models.php      # Danh sách AI models
    │   │   ├── admin.php       # Admin dashboard API
    │   │   └── health.php      # Health check
    │   │
    │   ├── config/             # Cấu hình
    │   │   ├── Config.php      # Đọc config từ .env
    │   │   └── Database.php    # Kết nối database
    │   │
    │   ├── middleware/         # Middleware
    │   │   └── AuthMiddleware.php  # JWT authentication
    │   │
    │   ├── models/             # Database models
    │   │   ├── User.php        # User model
    │   │   ├── Document.php    # Document model
    │   │   ├── AIQueryHistory.php  # Chat history
    │   │   └── Log.php         # Log model
    │   │
    │   ├── services/           # Business logic
    │   │   ├── Key4UService.php    # Key4U API client
    │   │   ├── AIToolService.php   # FastAPI client
    │   │   ├── DocumentService.php # Document management
    │   │   ├── UserService.php     # User management
    │   │   └── QwenService.php     # Qwen API client
    │   │
    │   ├── tools/              # Tools và utilities
    │   │   ├── AI tool/        # FastAPI microservice
    │   │   │   ├── main.py     # FastAPI app
    │   │   │   └── core/       # Core modules
    │   │   │       ├── ai_client.py      # AI API client
    │   │   │       ├── file_parser.py    # Parse files
    │   │   │       ├── file_generator.py # Generate files
    │   │   │       └── format_detector.py # Detect file type
    │   │   │
    │   │   ├── init-db.php     # Khởi tạo database
    │   │   └── init-mysql.php  # MySQL setup
    │   │
    │   ├── composer.json       # PHP dependencies
    │   ├── router.php          # Routing
    │   └── index.php           # Entry point
    │
    └── web/                    # Frontend (HTML/CSS/JS)
        ├── index.html          # Trang chủ (Chat interface)
        ├── login.html          # Đăng nhập
        ├── register.html       # Đăng ký
        ├── dashboard.html      # Dashboard người dùng
        ├── document-manager.html   # Quản lý tài liệu
        ├── admin-dashboard.html    # Admin dashboard
        ├── admin-login.html        # Admin login
        ├── pricing.html        # Nạp credits
        ├── contact.html        # Liên hệ
        ├── config.js           # Frontend config
        ├── script-backend.js   # Main JavaScript
        ├── load-models.js      # Load AI models
        └── style.css           # Styles
```

### Kiến trúc mạng

```
┌─────────────┐
│   Browser   │
│  (Frontend) │
│  Port 8002  │
└──────┬──────┘
       │ HTTP/HTTPS
       │
       ▼
┌─────────────┐
│  PHP Backend│
│  Port 8000  │
│  (Router)   │
└──────┬──────┘
       │
       ├──────────┬──────────────┐
       │          │              │
       ▼          ▼              ▼
┌─────────┐ ┌─────────┐ ┌─────────────┐
│ MySQL   │ │ FastAPI │ │  Key4U API  │
│Database │ │Port 8001│ │   (External)│
└─────────┘ └─────────┘ └─────────────┘
```

**Luồng xử lý:**
1. **Frontend** gửi request đến **PHP Backend** (Port 8000)
2. **PHP Backend** xác thực JWT, kiểm tra credits, log request
3. Nếu cần xử lý file/AI: PHP gửi request đến **FastAPI** (Port 8001)
4. **FastAPI** xử lý file, gọi **Key4U API** hoặc **OpenAI API**
5. Response được trả về Frontend qua PHP Backend

---

## 🧰 Yêu cầu hệ thống

| Thành phần | Phiên bản khuyến nghị | Ghi chú |
|------------|----------------------|---------|
| **PHP** | 8.2+ | Bật extensions: `curl`, `pdo_mysql`, `json`, `fileinfo`, `mbstring` |
| **Python** | 3.10+ | Cần `venv`, `pip` |
| **MySQL** | 8.0+ hoặc MariaDB 10.6+ | Hoặc dùng MySQL trong XAMPP |
| **Composer** | 2.0+ | Quản lý PHP dependencies |
| **OS** | Windows 10/11, macOS, Linux | Khuyến nghị Windows với XAMPP |

**Khuyến nghị:** Sử dụng **XAMPP** để dễ dàng cài đặt PHP, MySQL và Apache cùng lúc.

---

## 🚀 Hướng dẫn cài đặt

### Bước 1: Cài đặt XAMPP

1. **Tải XAMPP:**
   - Truy cập: https://www.apachefriends.org/download.html
   - Download phiên bản cho Windows (khoảng 150MB)
   - Chạy installer với quyền Administrator

2. **Cài đặt:**
   - Chọn thư mục cài đặt (mặc định: `C:\xampp`)
   - Chọn các thành phần:
     - ✅ **Apache** (bắt buộc)
     - ✅ **MySQL** (bắt buộc)
     - ✅ **PHP** (bắt buộc)
     - ✅ **phpMyAdmin** (khuyến nghị)

3. **Khởi động XAMPP:**
   - Mở **XAMPP Control Panel**
   - Start **Apache** và **MySQL**
   - Kiểm tra: `http://localhost` và `http://localhost/phpmyadmin`

### Bước 2: Cấu hình PHP

1. **Mở `php.ini`:**
   - Vị trí: `C:\xampp\php\php.ini`
   - Hoặc: XAMPP Control Panel → Apache → Config → PHP (php.ini)

2. **Bật extensions:**
   ```ini
   extension=curl
   extension=pdo_mysql
   extension=mysqli
   extension=fileinfo
   extension=json
   extension=mbstring
   ```

3. **Cấu hình upload (tùy chọn):**
   ```ini
   upload_max_filesize = 64M
   post_max_size = 64M
   memory_limit = 256M
   max_execution_time = 300
   ```

4. **Thêm PHP vào PATH:**
   - System Properties → Environment Variables
   - Thêm vào `Path`: `C:\xampp\php`
   - Khởi động lại Command Prompt

5. **Kiểm tra:**
   ```cmd
   php --version
   ```

### Bước 3: Cài đặt Python

1. **Tải Python:**
   - Truy cập: https://www.python.org/downloads/
   - Download Python 3.10+ cho Windows

2. **Cài đặt:**
   - ✅ **Quan trọng:** Đánh dấu **"Add Python to PATH"**
   - Chọn **"Install Now"**

3. **Kiểm tra:**
   ```cmd
   python --version
   pip --version
   ```

### Bước 4: Cài đặt Composer

1. **Tải Composer:**
   - Truy cập: https://getcomposer.org/download/
   - Download `Composer-Setup.exe` cho Windows

2. **Cài đặt:**
   - Chạy installer
   - Đảm bảo PHP đã có trong PATH

3. **Kiểm tra:**
   ```cmd
   composer --version
   ```

### Bước 5: Clone và cấu hình dự án

1. **Clone repository:**
   ```bash
   git clone https://github.com/your-org/chatbots-web.git
   cd chatbots-web
   ```

2. **Tạo file cấu hình:**
   ```bash
   # Windows
   copy config.env.example config.env
   
   # Linux/macOS
   cp config.env.example config.env
   ```

3. **Chỉnh sửa `config.env`:**
   ```env
   # API Keys
   KEY4U_API_KEY=sk-key4u-your-key-here
   AI_TOOL_BASE_URL=http://127.0.0.1:8001
   AI_TOOL_TIMEOUT=120
   
   # Database (XAMPP mặc định)
   DB_HOST=localhost
   DB_NAME=thuvien_ai
   DB_USERNAME=root
   DB_PASSWORD=
   
   # JWT Secret (thay đổi trong production!)
   JWT_SECRET=thuvien-ai-super-secret-jwt-key-change-this
   
   # Server Ports
   SERVER_PORT=8000
   ```

### Bước 6: Setup Database

#### Cách 1: Dùng phpMyAdmin (Khuyến nghị)

1. **Truy cập phpMyAdmin:**
   - Mở: `http://localhost/phpmyadmin`
   - Đăng nhập:
     - Username: `root`
     - Password: (để trống nếu XAMPP mặc định)

2. **Tạo database:**
   - Click tab **"Databases"**
   - Nhập tên: `thuvien_ai`
   - Chọn Collation: `utf8mb4_unicode_ci`
   - Click **"Create"**

3. **Import schema:**
   - Click vào database `thuvien_ai`
   - Click tab **"Import"**
   - Chọn file: `data/database/thuvien_ai.sql`
   - Click **"Go"**

#### Cách 2: Dùng MySQL Command Line

```cmd
cd C:\xampp\mysql\bin
mysql.exe -u root -p
```

```sql
CREATE DATABASE thuvien_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE thuvien_ai;
SOURCE C:/path/to/chatbots-web/data/database/thuvien_ai.sql;
EXIT;
```

### Bước 7: Cài đặt Dependencies

#### 7.1. Python Dependencies

1. **Tạo virtual environment:**
   ```cmd
   cd src\php-backend\tools\AI tool
   python -m venv .venv
   ```

2. **Kích hoạt virtual environment:**
   ```cmd
   # Windows Command Prompt
   .venv\Scripts\activate.bat
   
   # Windows PowerShell
   .venv\Scripts\Activate.ps1
   # Nếu lỗi, chạy:
   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
   
   # Linux/macOS
   source .venv/bin/activate
   ```

3. **Nâng cấp pip:**
   ```cmd
   python -m pip install --upgrade pip
   ```

4. **Cài đặt dependencies:**
   ```cmd
   # Từ thư mục gốc dự án
   cd C:\path\to\chatbots-web
   pip install -r requirements.txt
   ```

   **Danh sách thư viện:**
   - `fastapi` - Web framework
   - `uvicorn` - ASGI server
   - `python-dotenv` - Đọc .env
   - `openai` - OpenAI API client
   - `PyPDF2` - Đọc PDF
   - `python-docx` - Đọc Word
   - `pandas` - Xử lý Excel/CSV
   - `fpdf2` - Tạo PDF
   - `python-multipart` - Form data
   - `pytesseract` - OCR
   - `pdf2image` - PDF to image
   - `Pillow` - Image processing

#### 7.2. PHP Dependencies

1. **Cài đặt Composer packages:**
   ```cmd
   cd src\php-backend
   composer install
   ```

   **Hoặc production mode:**
   ```cmd
   composer install --no-dev --optimize-autoloader
   ```

   **Danh sách thư viện:**
   - `guzzlehttp/guzzle` - HTTP client
   - `firebase/php-jwt` - JWT tokens

2. **Kiểm tra:**
   ```cmd
   # Kiểm tra vendor folder
   dir vendor
   ```

### Bước 8: Khởi động hệ thống

#### Cách 1: Dùng script tự động (Windows) - Khuyến nghị

```cmd
# Trong thư mục gốc dự án
start.bat
```

Script này sẽ:
1. ✅ Kiểm tra PHP và Python đã cài đặt
2. ✅ Tạo virtual environment nếu chưa có
3. ✅ Dừng các tiến trình cũ (nếu có)
4. ✅ Khởi động 3 server:
   - PHP Backend: `http://127.0.0.1:8000`
   - FastAPI AI Tool: `http://127.0.0.1:8001`
   - Frontend: `http://127.0.0.1:8002`
5. ✅ Tự động mở trình duyệt

#### Cách 2: Khởi động thủ công

**Terminal 1 - PHP Backend:**
```cmd
cd src\php-backend
php -d upload_max_filesize=64M -d post_max_size=64M -d memory_limit=256M -S 127.0.0.1:8000 router.php
```

**Terminal 2 - FastAPI:**
```cmd
cd src\php-backend\tools\AI tool
.venv\Scripts\activate  # Windows
# hoặc: source .venv/bin/activate  # Linux/macOS
uvicorn main:app --host 127.0.0.1 --port 8001 --reload
```

**Terminal 3 - Frontend:**
```cmd
cd src\web
php -S 127.0.0.1:8002
```

### Bước 9: Truy cập hệ thống

- **Frontend:** http://127.0.0.1:8002
- **Backend API:** http://127.0.0.1:8000/api/health.php
- **FastAPI Docs:** http://127.0.0.1:8001/docs
- **phpMyAdmin:** http://localhost/phpmyadmin

---

## ⚙️ Cấu hình

### File `config.env` (thư mục gốc)

```env
# API Keys
KEY4U_API_KEY=sk-key4u-your-key-here
AI_TOOL_BASE_URL=http://127.0.0.1:8001
AI_TOOL_TIMEOUT=120

# Database
DB_HOST=localhost
DB_NAME=thuvien_ai
DB_USERNAME=root
DB_PASSWORD=

# JWT Secret (thay đổi trong production!)
JWT_SECRET=thuvien-ai-super-secret-jwt-key-change-this

# Server Ports
SERVER_PORT=8000
```

### File `src/php-backend/config.env` (nếu có)

File này sẽ được ưu tiên nếu tồn tại, nếu không sẽ dùng `config.env` ở thư mục gốc.

### File `src/php-backend/tools/AI tool/.env` (tùy chọn)

```env
KEY4U_API_KEY=sk-key4u-your-key-here
KEY4U_API_URL=https://api.key4u.shop/v1/chat/completions
AI_MODEL=gpt-4o
```

---

## 🎯 Sử dụng hệ thống

### Đăng ký và Đăng nhập

1. **Đăng ký tài khoản:**
   - Truy cập: http://127.0.0.1:8002/register.html
   - Điền thông tin: username, email, password
   - Click **"Đăng ký"**

2. **Đăng nhập:**
   - Truy cập: http://127.0.0.1:8002/login.html
   - Nhập username và password
   - Click **"Đăng nhập"**

3. **Admin Login:**
   - Truy cập: http://127.0.0.1:8002/admin-login.html
   - Đăng nhập với tài khoản admin

### Chat với AI

1. **Chọn AI Model:**
   - Mở sidebar bên trái
   - Tìm kiếm hoặc lọc models
   - Click vào model để chọn

2. **Gửi tin nhắn:**
   - Nhập câu hỏi vào ô chat
   - Click **"Gửi"** hoặc nhấn Enter
   - Đợi AI trả lời

3. **Upload file:**
   - Click vào biểu tượng 📎
   - Chọn file (PDF, DOCX, TXT...)
   - Gửi kèm tin nhắn

4. **Tạo file:**
   - Yêu cầu AI tạo file: "Tạo file Python tính toán cơ bản"
   - AI sẽ tạo file và cung cấp link download

### Quản lý tài liệu

1. **Upload tài liệu:**
   - Truy cập: http://127.0.0.1:8002/document-manager.html
   - Click **"Chọn tài liệu"**
   - Chọn file và upload

2. **Xem tài liệu:**
   - Click nút **"👁️ Xem"** trên card tài liệu
   - Xem nội dung trong modal

3. **Download tài liệu:**
   - Click nút **"📥 Tải xuống"** trên card tài liệu
   - File sẽ được tải về máy

4. **Phân tích tài liệu:**
   - Click nút **"🤖 Phân tích AI"** trên card tài liệu
   - AI sẽ phân tích và hiển thị kết quả

### Admin Dashboard

1. **Truy cập:**
   - Đăng nhập với tài khoản admin
   - Truy cập: http://127.0.0.1:8002/admin-dashboard.html

2. **Quản lý người dùng:**
   - Xem danh sách người dùng
   - Khóa/mở khóa tài khoản
   - Cấp/thu hồi credits

3. **Thống kê:**
   - Xem tổng số người dùng
   - Xem tổng credits đã sử dụng
   - Xem nhật ký hoạt động

---

## 📡 API Documentation

### Authentication

| Endpoint | Method | Mô tả |
|----------|--------|-------|
| `/api/auth.php?action=register` | POST | Đăng ký tài khoản mới |
| `/api/auth.php?action=login` | POST | Đăng nhập, trả JWT token |
| `/api/auth.php?action=profile` | GET | Lấy thông tin user (cần token) |

**Ví dụ Register:**
```json
POST /api/auth.php?action=register
{
  "username": "user123",
  "email": "user@example.com",
  "password": "password123"
}
```

**Ví dụ Login:**
```json
POST /api/auth.php?action=login
{
  "username": "user123",
  "password": "password123"
}
```

### Chat & AI

| Endpoint | Method | Mô tả |
|----------|--------|-------|
| `/api/chat-real.php` | POST | Chat với AI models |
| `/api/ai-tool` | POST | Xử lý tài liệu qua FastAPI |
| `/api/models.php` | GET | Danh sách AI models |

**Ví dụ Chat:**
```json
POST /api/chat-real.php
Headers: {
  "Authorization": "Bearer <JWT_TOKEN>"
}
Body: {
  "model": "gpt-4",
  "message": "Xin chào!",
  "conversation_id": "optional-conversation-id"
}
```

### Documents

| Endpoint | Method | Mô tả |
|----------|--------|-------|
| `/api/documents.php?action=upload` | POST | Upload tài liệu |
| `/api/documents.php?action=list` | GET | Danh sách tài liệu |
| `/api/documents.php?action=get&id=<id>` | GET | Lấy thông tin tài liệu |
| `/api/documents.php?action=download&id=<id>` | GET | Download tài liệu |
| `/api/documents.php?action=delete&id=<id>` | DELETE | Xóa tài liệu |
| `/api/documents.php?action=search&q=<query>` | GET | Tìm kiếm tài liệu |

**Ví dụ Upload:**
```javascript
const formData = new FormData();
formData.append('file', fileInput.files[0]);

fetch('/api/documents.php?action=upload', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer <JWT_TOKEN>'
  },
  body: formData
});
```

### Admin

| Endpoint | Method | Mô tả |
|----------|--------|-------|
| `/api/admin.php?action=users` | GET | Danh sách users (cần admin) |
| `/api/admin.php?action=stats` | GET | Thống kê hệ thống (cần admin) |
| `/api/admin.php?action=update-credits` | POST | Cập nhật credits (cần admin) |
| `/api/admin.php?action=lock-user` | POST | Khóa user (cần admin) |

### System

| Endpoint | Method | Mô tả |
|----------|--------|-------|
| `/api/health.php` | GET | Health check |

**Lưu ý:** Hầu hết API yêu cầu header `Authorization: Bearer <JWT_TOKEN>`

---

## 🤝 Đóng góp

### Quy trình đóng góp

1. **Fork repository** và tạo branch mới:
   ```bash
   git checkout -b feature/ten-tinh-nang
   ```

2. **Commit changes:**
   ```bash
   git commit -m "Thêm tính năng XYZ"
   ```

3. **Push và tạo Pull Request:**
   ```bash
   git push origin feature/ten-tinh-nang
   ```

### Hướng dẫn code

- Sử dụng **tiếng Việt** cho comments và log messages
- Format code theo chuẩn PSR-12 (PHP) và PEP 8 (Python)
- Viết commit message rõ ràng, mô tả đầy đủ thay đổi
- Test kỹ trước khi commit

### Báo lỗi

Khi báo lỗi, vui lòng cung cấp:
- **OS và phiên bản:** Windows 10, macOS 13, etc.
- **PHP version:** `php --version`
- **Python version:** `python --version`
- **MySQL version:** Xem trong phpMyAdmin
- **Logs:** Console logs, server logs, error messages
- **Steps to reproduce:** Các bước tái hiện lỗi

---

## 📞 Liên hệ và hỗ trợ

- **GitHub Issues:** https://github.com/your-org/chatbots-web/issues
- **Email:** support@thuvienai.example (tùy chọn)

---

## 👥 Thông tin nhóm

- **Trần Hải Bằng** – 080205005769 (Nhóm trưởng)
- **Lê Huy Hoàng** – 077205003839 (Thư ký)
- **Lương Thị Bích Hằng** – Thành viên
- **Phan Minh Hòa** – Thành viên
- **Hồ Ngọc Quyền** – Thành viên

---

## 📄 Giấy phép

Dự án được phát hành dưới giấy phép **[MIT License](LICENSE)**.

---

## 🎯 Tóm tắt nhanh

### Cài đặt với XAMPP (10-15 phút)

```cmd
# 1. Cài XAMPP và khởi động Apache + MySQL
# 2. Cài Python 3.10+ và Composer
# 3. Clone repo và cấu hình config.env
# 4. Tạo database qua phpMyAdmin
# 5. Cài đặt Python requirements:
cd C:\path\to\chatbots-web
python -m venv src\php-backend\tools\AI tool\.venv
src\php-backend\tools\AI tool\.venv\Scripts\activate
pip install -r requirements.txt
# 6. Cài đặt PHP dependencies:
cd src\php-backend
composer install
# 7. Chạy start.bat
```

### Cài đặt requirements nhanh

**Python requirements (từ thư mục gốc):**
```cmd
cd C:\path\to\chatbots-web
pip install -r requirements.txt
```

**PHP dependencies (từ thư mục backend):**
```cmd
cd src\php-backend
composer install
```

### Truy cập

- **Frontend:** http://127.0.0.1:8002
- **Backend API:** http://127.0.0.1:8000/api/health.php
- **FastAPI Docs:** http://127.0.0.1:8001/docs
- **phpMyAdmin:** http://localhost/phpmyadmin

---

**© 2025 Thư Viện AI** – Xây dựng với ❤️ bằng PHP, FastAPI và JavaScript.
