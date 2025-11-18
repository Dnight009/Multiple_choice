<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['IDACC'])) {
    header("Location: /Guest/Login.php");
    exit;
}

// 3. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php'; 

$flash_message = "";
$flash_type = "";
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    $flash_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}
// 4. LẤY ID NGƯỜI DÙNG TỪ SESSION
$user_id = $_SESSION['IDACC'];
$user_role = $_SESSION['quyen'] ?? 0; // Lấy quyền để check (chỉ hiện cho học sinh)

// 5. TRUY VẤN LẤY THÔNG TIN TÀI KHOẢN
$stmt = $conn->prepare("SELECT username, email, ho_ten, ngay_sinh, gioi_tinh, ngay_tao FROM ACCOUNT WHERE IDACC = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Lỗi: Không tìm thấy thông tin tài khoản.";
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// 6. XỬ LÝ DỮ LIỆU
$username = htmlspecialchars($user['username']);
$ho_ten = !empty($user['ho_ten']) ? htmlspecialchars($user['ho_ten']) : "<i>Chưa cập nhật</i>";
$email = !empty($user['email']) ? htmlspecialchars($user['email']) : "<i>Chưa cập nhật</i>";
$ngay_sinh = !empty($user['ngay_sinh']) ? date("d/m/Y", strtotime($user['ngay_sinh'])) : "<i>Chưa cập nhật</i>";
$gioi_tinh  = !empty($user['gioi_tinh']) ? htmlspecialchars($user['gioi_tinh']) : "<i>Chưa cập nhật</i>";
$ngay_tao = !empty($user['ngay_tao']) ? date("d/m/Y H:i", strtotime($user['ngay_tao'])) : "<i>Không rõ</i>";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ cá nhân - <?php echo $username; ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f0f2f5; 
            margin: 0; 
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container { 
            max-width: 800px; 
            margin: 20px auto;
            background: #fff; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            flex-grow: 1;
        }
        h1 { 
            text-align: center; 
            color: #333; 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 15px;
        }
        .profile-box { margin-top: 20px; }
        .info-row {
            display: flex;
            margin-bottom: 15px;
            font-size: 1.1em;
            line-height: 1.6;
            border-bottom: 1px dashed #eee;
            padding-bottom: 15px;
        }
        .info-row label {
            font-weight: bold;
            color: #555;
            width: 180px;
            flex-shrink: 0;
        }
        .info-row span { color: #111; }

        /* CSS CHO CARD AI (GỢI Ý) */
        .ai-suggestion-card {
            background: linear-gradient(135deg, #e0f7fa 0%, #e1bee7 100%);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            border: 1px solid #b2ebf2;
            position: relative;
        }
        .ai-title {
            font-weight: bold;
            color: #6a1b9a;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .ai-content {
            color: #333;
            line-height: 1.6;
            font-style: italic;
        }
        /* Hiệu ứng loading */
        .ai-loading {
            color: #555;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .spinner {
            width: 16px; height: 16px;
            border: 3px solid #ccc;
            border-top: 3px solid #6a1b9a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .action-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        .btn-primary, .btn-secondary {
            padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; text-align: center;
        }
        .btn-primary { background: #2d98da; color: white; }
        .btn-primary:hover { background: #2587c4; }
        .btn-secondary { background: #f39c12; color: white; }
        .btn-secondary:hover { background: #e67e22; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .success { background: #e6f7e9; color: #28a745; border: 1px solid #b7e9c7; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="container">
        
        <?php if (!empty($flash_message)): ?>
            <div class="message <?php echo htmlspecialchars($flash_type); ?>">
                <?php echo htmlspecialchars($flash_message); ?>
            </div>
        <?php endif; ?>

        <h1>Hồ sơ cá nhân</h1>
        
        <div class="profile-box">
            <div class="info-row"><label>Tên đăng nhập:</label><span><?php echo $username; ?></span></div>
            <div class="info-row"><label>Họ và Tên:</label><span><?php echo $ho_ten; ?></span></div>
            <div class="info-row"><label>Email:</label><span><?php echo $email; ?></span></div>
            <div class="info-row"><label>Ngày sinh:</label><span><?php echo $ngay_sinh; ?></span></div>
            <div class="info-row"><label>Giới tính:</label><span><?php echo $gioi_tinh; ?></span></div>
            <div class="info-row"><label>Tham gia ngày:</label><span><?php echo $ngay_tao; ?></span></div>
        </div>

        <?php if ($user_role == '2'): ?>
        <div class="ai-suggestion-card">
            <div class="ai-title">🤖 Góc học tập AI</div>
            <div class="ai-content" id="ai-advice-content">
                <div class="ai-loading">
                    <div class="spinner"></div>
                    Đang phân tích kết quả học tập của bạn...
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="action-group">
            <a href="../Guest/change_password.php" class="btn-primary"> Đổi mật khẩu </a>
            <a href="../Home/gop_y.php" class="btn-secondary"> Xin cấp quyền Giáo viên </a>
            <a href="../Guest/update_profile.php" class="btn-secondary"> Cập Nhật </a>
        </div>

    </div>

    <?php include __DIR__ . '/../Home/Footer.php'; ?>

    <?php if ($user_role == '2'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gọi API lấy lời khuyên
            fetch('../API/get_learning_advice.php')
                .then(response => response.json())
                .then(data => {
                    const contentDiv = document.getElementById('ai-advice-content');
                    if (data.advice) {
                        // Hiển thị lời khuyên từ AI (dùng markdown-like simple replacement nếu cần)
                        // Ở đây hiển thị text thuần nhưng xử lý xuống dòng
                        contentDiv.innerHTML = data.advice.replace(/\n/g, '<br>');
                    } else if (data.error) {
                        contentDiv.innerHTML = '<span style="color:red">Lỗi: ' + data.error + '</span>';
                    }
                })
                .catch(error => {
                    document.getElementById('ai-advice-content').innerHTML = 'Không thể kết nối đến trợ lý AI lúc này.';
                    console.error('AI Error:', error);
                });
        });
    </script>
    <?php endif; ?>

</body>
</html>