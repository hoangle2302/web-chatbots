<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'full_name' => trim($_POST['full_name'] ?? '')
    ];
    
    $result = Auth::register($data);
    
    if ($result['success']) {
        $success = $result['message'] . ' Bạn có thể đăng nhập ngay bây giờ.';
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <i class="fas fa-robot"></i>
                    <h1><?php echo APP_NAME; ?></h1>
                </div>
                <p>Tạo tài khoản mới</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form" id="registerForm">
                <div class="form-group">
                    <label for="full_name">Họ và tên</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="full_name" name="full_name" required 
                               value="<?php echo htmlspecialchars($data['full_name'] ?? ''); ?>"
                               placeholder="Nhập họ và tên">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <div class="input-group">
                        <i class="fas fa-at"></i>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>"
                               placeholder="Nhập tên đăng nhập"
                               pattern="[a-zA-Z0-9_]{3,20}"
                               title="3-20 ký tự, chỉ chữ cái, số và dấu gạch dưới">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                               placeholder="Nhập email của bạn">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required 
                               placeholder="Nhập mật khẩu"
                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="form-hint">Tối thiểu <?php echo PASSWORD_MIN_LENGTH; ?> ký tự</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Nhập lại mật khẩu">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="agree_terms" required>
                        <span class="checkmark"></span>
                        Tôi đồng ý với <a href="#" target="_blank">điều khoản sử dụng</a> và <a href="#" target="_blank">chính sách bảo mật</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Đăng ký
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
            </div>
        </div>
        
        <div class="auth-bg">
            <div class="bg-pattern"></div>
        </div>
    </div>
    
    <script src="../assets/js/auth.js"></script>
</body>
</html>