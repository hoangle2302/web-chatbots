
# 🤖 Thư Viện AI - Ứng Dụng Chat AI Đơn Giản

> Một ứng dụng web đơn giản để chat với AI, hỗ trợ 449 mô hình AI khác nhau

## 📖 Giới thiệu

**Thư Viện AI** là một ứng dụng web cho phép bạn:
- 💬 Chat với AI (như ChatGPT, Claude, Gemini...)
- 📁 Upload và phân tích tài liệu
- 👤 Đăng ký tài khoản và quản lý thông tin
- 🎨 Sử dụng giao diện đẹp và dễ dùng

## 🔌 API Documentation

### 📊 Bảng API Endpoints
// ... existing code ...

| STT | Method | Endpoint | Mô tả | Auth Required | Request Body | Response | AI Response |
|-----|--------|----------|-------|---------------|--------------|----------|-------------|
| **AUTHENTICATION APIs** |
| 1 | `POST` | `/api/auth/register` | Đăng ký tài khoản mới | ❌ | `{username, password, email}` | `{success, message, user_id}` | "Đăng ký thành công!" |
| 2 | `POST` | `/api/auth/login` | Đăng nhập vào hệ thống | ❌ | `{username, password}` | `{success, token, user}` | "Đăng nhập thành công!" |
| 3 | `POST` | `/api/auth/logout` | Đăng xuất khỏi hệ thống | ✅ | `{}` | `{success, message}` | "Đăng xuất thành công!" |
| **USER MANAGEMENT APIs** |
| 4 | `GET` | `/api/user/profile` | Lấy thông tin profile | ✅ | - | `{success, user}` | Thông tin user (username, email) |
| 5 | `POST` | `/api/user/update` | Cập nhật thông tin user | ✅ | `{email, password?}` | `{success, message}` | "Cập nhật thông tin thành công!" |
| **CHAT APIs** |
| 6 | `GET` | `/api/chat/models` | Lấy danh sách AI models | ❌ | - | `{success, models[]}` | Danh sách 449 AI models |
| 7 | `POST` | `/api/chat/send` | Gửi tin nhắn tới AI | ✅ | `{message, model, conversation_id?}` | `{success, response, conversation_id}` | **Câu trả lời từ AI được chọn** |
| 8 | `GET` | `/api/chat/history` | Lấy lịch sử chat | ✅ | Query: `?conversation_id&limit&offset` | `{success, conversations[]}` | Lịch sử các cuộc hội thoại |
| 9 | `POST` | `/api/chat/conversation/create` | Tạo cuộc hội thoại mới | ✅ | `{title}` | `{success, conversation_id}` | "Tạo cuộc hội thoại mới thành công!" |
| 10 | `GET` | `/api/chat/conversation/{id}` | Lấy chi tiết cuộc hội thoại | ✅ | - | `{success, conversation}` | Chi tiết cuộc hội thoại và tin nhắn |
| **DOCUMENT APIs** |
| 11 | `POST` | `/api/documents/upload` | Upload tài liệu | ✅ | `FormData: file` | `{success, document_id, filename}` | "Upload thành công! Phân tích: [nội dung]" |
| 12 | `GET` | `/api/documents/list` | Danh sách tài liệu | ✅ | Query: `?limit&offset` | `{success, documents[]}` | Danh sách tài liệu đã upload |
| 13 | `GET` | `/api/documents/{id}` | Chi tiết tài liệu | ✅ | - | `{success, document}` | Nội dung và phân tích tài liệu |
| 14 | `POST` | `/api/documents/{id}/analyze` | Phân tích tài liệu với AI | ✅ | `{question, model}` | `{success, analysis}` | **Phân tích tài liệu theo câu hỏi** |
| 15 | `POST` | `/api/documents/{id}/delete` | Xóa tài liệu | ✅ | `{}` | `{success, message}` | "Xóa tài liệu thành công!" |

// ... existing code ...

### 🔄 HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| `200` | OK | Request thành công |
| `201` | Created | Tạo mới thành công |
| `400` | Bad Request | Dữ liệu request không hợp lệ |
| `401` | Unauthorized | Chưa đăng nhập hoặc token hết hạn |
| `403` | Forbidden | Không có quyền truy cập |
| `404` | Not Found | Không tìm thấy resource |
| `422` | Unprocessable Entity | Validation error |
| `500` | Internal Server Error | Lỗi server |


### 🔧 Backend Development (PHP)



#### 🚧 Cần phát triển:

**Authentication System:**
- [ ] `POST /api/auth/register` - Đăng ký tài khoản
- [ ] `POST /api/auth/login` - Đăng nhập
- [ ] `POST /api/auth/logout` - Đăng xuất
- [ ] JWT token generation & validation
- [ ] Password hashing với bcrypt

**User Management:**
- [ ] `GET /api/user/profile` - Lấy thông tin user
- [ ] `POST /api/user/update` - Cập nhật thông tin

**Chat System:**
- [ ] `GET /api/chat/models` - Danh sách AI models
- [ ] `POST /api/chat/send` - Gửi tin nhắn
- [ ] `GET /api/chat/history` - Lịch sử chat
- [ ] `POST /api/chat/conversation/create` - Tạo cuộc hội thoại
- [ ] `GET /api/chat/conversation/{id}` - Chi tiết cuộc hội thoại

**Document Management:**
- [ ] `POST /api/documents/upload` - Upload file
- [ ] `GET /api/documents/list` - Danh sách file
- [ ] `GET /api/documents/{id}` - Chi tiết file
- [ ] `POST /api/documents/{id}/analyze` - Phân tích file
- [ ] `POST /api/documents/{id}/delete` - Xóa file

**Database Schema:**
- [ ] Users table
- [ ] Conversations table  
- [ ] Messages table
- [ ] Documents table
- [ ] Migration scripts

### 🎨 Frontend Development

**API Integration:**
- [ ] Axios/Fetch API setup
- [ ] Token management
- [ ] Error handling
- [ ] Loading states

**UI Components:**
- [ ] Login/Register forms
- [ ] Chat interface
- [ ] File upload component
- [ ] User profile page

### 🧪 Testing

**API Testing:**
- [ ] Test tất cả 15 endpoints
- [ ] Authentication flow testing
- [ ] Error handling testing
- [ ] File upload testing


## 🚀 Cách cài đặt

### Yêu cầu
- **PHP 8.4+** (ngôn ngữ lập trình backend)
- **Python 3.x** (để chạy server frontend)
- **Windows 10/11**

### Khởi động
```bash
# Chạy lệnh này trong thư mục dự án
.\start-ai.bat
```

### Truy cập ứng dụng
- Frontend: http://127.0.0.1:8001/index.html
- API Base URL: http://127.0.0.1:8000/api

## 📁 Cấu trúc dự án

```
ThuVienAI/
├── src/
│   ├── php-backend/          # Backend PHP
│   │   ├── api/              # API endpoints
│   │   │   ├── auth/         # Authentication APIs (1-3)
│   │   │   ├── user/         # User APIs (4-5)
│   │   │   ├── chat/         # Chat APIs (6-10)
│   │   │   └── documents/    # Document APIs (11-15)
│   │   ├── config/           # Database config
│   │   ├── models/           # Data models
│   │   └── utils/            # Helper functions
│   └── web/                  # Frontend
├── data/                     # Database & uploads
└── README.md
```

## 👥 Nhóm phát triển

| STT | Họ và Tên | MSSV | Vai Trò |
|-----|-----------|------|---------|
| 01 | Trần Hải Bằng | 000 | Nhóm Trưởng |
| 02 | Lê Huy Hoàng | 077205003839 | Thành Viên |
| 03 | Lương Thị Bích Hằng | 000 | Thành Viên |
| 04 | Phan Minh Hòa | 000 | Thành Viên |
| 05 | Hồ Ngọc Quyền | 000 | Thành Viên |

---

**🎉 Chúc bạn phát triển thành công!**

*Được xây dựng với ❤️ bằng PHP, JavaScript và công nghệ web hiện đại.*
```


## 📊 **Bảng API rõ ràng:**
- 15 endpoints được đánh số thứ tự
- Phân loại theo nhóm chức năng
- Cột Method (GET/POST) rõ ràng
- Cột Auth Required (✅/❌)
- Request Body và Response summary

## 🔄 **HTTP Status Codes:**
- Bảng mã lỗi chuẩn
- Giải thích ý nghĩa từng mã

## 📋 **Checklist theo API:**
- Liên kết trực tiếp với từng endpoint trong bảng
- Ưu tiên phát triển theo phase

