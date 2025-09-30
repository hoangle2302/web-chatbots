<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

Auth::requireLogin();

$database = new Database();
$db = $database->getConnection();

$user = Auth::getCurrentUser();
$error = '';
$success = '';

// Handle AI model selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ai_model'])) {
    $selectedModel = $_POST['ai_model'];
    
    // Validate AI model
    $stmt = $db->prepare("SELECT id FROM ai_models WHERE model_key = ? AND is_active = 1");
    $stmt->execute([$selectedModel]);
    
    if ($stmt->fetch()) {
        // Update user's preferred AI model
        $stmt = $db->prepare("UPDATE users SET preferred_ai_model = ? WHERE id = ?");
        $stmt->execute([$selectedModel, $user['id']]);
        
        // Update session
        $_SESSION['preferred_ai_model'] = $selectedModel;
        
        $success = 'Đã cập nhật AI model thành công!';
        
        // Update user data
        $user['preferred_ai_model'] = $selectedModel;
    } else {
        $error = 'AI model không hợp lệ!';
    }
}

// Get available AI models
$stmt = $db->prepare("SELECT * FROM ai_models WHERE is_active = 1 ORDER BY name");
$stmt->execute();
$aiModels = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn AI Model - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/ai-models.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-robot"></i>
                    <span><?php echo APP_NAME; ?></span>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                <li class="active"><a href="ai-models.php"><i class="fas fa-brain"></i> AI Models</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-brain"></i> Chọn AI Model</h1>
                <p>Lựa chọn mô hình AI phù hợp với nhu cầu của bạn</p>
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
            
            <div class="ai-models-grid">
                <?php foreach ($aiModels as $model): ?>
                    <div class="ai-model-card <?php echo $model['model_key'] === $user['preferred_ai_model'] ? 'selected' : ''; ?>">
                        <div class="model-header" style="border-color: <?php echo $model['color']; ?>;">
                            <div class="model-icon" style="color: <?php echo $model['color']; ?>;">
                                <i class="<?php echo $model['icon']; ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($model['name']); ?></h3>
                            <?php if ($model['model_key'] === $user['preferred_ai_model']): ?>
                                <div class="selected-badge">
                                    <i class="fas fa-check"></i> Đang sử dụng
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="model-content">
                            <p class="model-description"><?php echo htmlspecialchars($model['description']); ?></p>
                            
                            <div class="model-specs">
                                <div class="spec-item">
                                    <span class="spec-label">Max Tokens:</span>
                                    <span class="spec-value"><?php echo number_format($model['max_tokens']); ?></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Temperature:</span>
                                    <span class="spec-value"><?php echo $model['temperature']; ?></span>
                                </div>
                            </div>
                            
                            <form method="POST" class="model-form">
                                <input type="hidden" name="ai_model" value="<?php echo $model['model_key']; ?>">
                                <button type="submit" class="btn <?php echo $model['model_key'] === $user['preferred_ai_model'] ? 'btn-selected' : 'btn-primary'; ?>">
                                    <?php if ($model['model_key'] === $user['preferred_ai_model']): ?>
                                        <i class="fas fa-check"></i> Đang sử dụng
                                    <?php else: ?>
                                        <i class="fas fa-arrow-right"></i> Chọn Model
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="model-info">
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Thông tin về AI Models</h3>
                    <div class="info-content">
                        <div class="info-item">
                            <h4>GPT-3.5 Turbo</h4>
                            <p>Phù hợp cho hầu hết các tác vụ thường ngày, tốc độ nhanh và chi phí thấp.</p>
                        </div>
                        <div class="info-item">
                            <h4>GPT-4</h4>
                            <p>Mô hình mạnh nhất, phù hợp cho các tác vụ phức tạp, sáng tạo và phân tích chuyên sâu.</p>
                        </div>
                        <div class="info-item">
                            <h4>Claude</h4>
                            <p>Tập trung vào an toàn và tính chính xác, phù hợp cho các tác vụ nghiêm túc.</p>
                        </div>
                        <div class="info-item">
                            <h4>Google Gemini</h4>
                            <p>Hỗ trợ đa phương tiện (text, image, audio) và tích hợp tốt với các dịch vụ Google.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>