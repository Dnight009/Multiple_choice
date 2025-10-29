<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KIỂM TRA ĐĂNG NHẬP
// Bắt buộc phải đăng nhập mới xem được trang này
if (!isset($_SESSION['IDACC'])) {
    // Nếu chưa, chuyển hướng về trang đăng nhập
    // (Sửa đường dẫn /TracNghiem/Guest/Login.php nếu cần)
    header("Location: /TracNghiem/Guest/Login.php");
    exit;
}

// 3. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php'; 

$flash_message = "";
$flash_type = "";
if (isset($_SESSION['flash_message'])) {
    // Lấy thông báo
    $flash_message = $_SESSION['flash_message'];
    $flash_type = $_SESSION['flash_type'] ?? 'success';
    
    // Xóa thông báo khỏi session để nó không hiện lại
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}
// 4. LẤY ID NGƯỜI DÙNG TỪ SESSION
$user_id = $_SESSION['IDACC'];

// 5. TRUY VẤN LẤY THÔNG TIN TÀI KHOẢN
// Dùng prepared statement để an toàn
$stmt = $conn->prepare("SELECT username, email, ho_ten, ngay_sinh, gioi_tinh, ngay_tao FROM ACCOUNT WHERE IDACC = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Lỗi hiếm gặp: có session nhưng không có user
    echo "Lỗi: Không tìm thấy thông tin tài khoản.";
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// 6. XỬ LÝ (FORMAT) DỮ LIỆU ĐỂ HIỂN THỊ
// Tên đăng nhập
$username = htmlspecialchars($user['username']);
// Họ tên (cho phép NULL)
$ho_ten = !empty($user['ho_ten']) ? htmlspecialchars($user['ho_ten']) : "<i>Chưa cập nhật</i>";
// Email (cho phép NULL)
$email = !empty($user['email']) ? htmlspecialchars($user['email']) : "<i>Chưa cập nhật</i>";
// Ngày sinh (cho phép NULL)
$ngay_sinh = !empty($user['ngay_sinh']) 
             ? date("d/m/Y", strtotime($user['ngay_sinh'])) 
             : "<i>Chưa cập nhật</i>";
// giới tính (cho phép NULL)
$gioi_tinh  = !empty($user['gioi_tinh']) ? htmlspecialchars($user['gioi_tinh']) : "<i>Chưa cập nhật</i>";
// Ngày tạo (cho phép NULL)
$ngay_tao = !empty($user['ngay_tao']) 
            ? date("d/m/Y H:i", strtotime($user['ngay_tao'])) 
            : "<i>Không rõ</i>";
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
            margin: 20px auto; /* Căn giữa */
            background: #fff; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            flex-grow: 1; /* Đẩy footer xuống dưới */
        }
        h1 { 
            text-align: center; 
            color: #333; 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 15px;
        }
        
        .profile-box {
            margin-top: 20px;
        }
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
            width: 180px; /* Cố định độ rộng label */
            flex-shrink: 0;
        }
        .info-row span {
            color: #111;
        }

        .action-group {
            display: flex;
            gap: 15px; /* Khoảng cách giữa các nút */
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        
        /* Style chung cho các nút */
        .btn-primary, .btn-secondary {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        /* Nút chính (màu xanh) */
        .btn-primary {
            background: #2d98da; 
            color: white; 
        }
        .btn-primary:hover { background: #2587c4; }

        /* Nút phụ (màu cam/vàng) */
        .btn-secondary {
            background: #f39c12; 
            color: white;
        }
        .btn-secondary:hover { background: #e67e22; }
        /* Thêm vào <style> của profile.php */
        .message { 
            padding: 15px; 
            margin-bottom: 20px; /* Tách biệt với nội dung */
            border-radius: 5px; 
            font-weight: bold;
            text-align: center;
        }
        .success { background: #e6f7e9; color: #28a745; border: 1px solid #b7e9c7; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="container">
        <h1>Hồ sơ cá nhân</h1>
        
        <div class="profile-box">
            
            <div class="info-row">
                <label>Tên đăng nhập:</label>
                <span><?php echo $username; ?></span>
            </div>

            <div class="info-row">
                <label>Họ và Tên:</label>
                <span><?php echo $ho_ten; ?></span>
            </div>

            <div class="info-row">
                <label>Email:</label>
                <span><?php echo $email; ?></span>
            </div>

            <div class="info-row">
                <label>Ngày sinh:</label>
                <span><?php echo $ngay_sinh; ?></span>
            </div>

            <div class="info-row">
                <label>Giới tính:</label>
                <span><?php echo $gioi_tinh; ?></span>
            </div>

            <div class="info-row">
                <label>Tham gia ngày:</label>
                <span><?php echo $ngay_tao; ?></span>
            </div>

        </div>

        <div class="action-group">
            
            <a href="../Guest/change_password.php" class="btn-primary"> Đổi mật khẩu </a>
            <a href="../Home/gop_y.php" class="btn-secondary"> Xin cấp quyền Giáo viên </a>
            <a href="../Guest/update_profile.php" class="btn-secondary"> Cập Nhập </a>

        </div>

    </div>

    <?php include __DIR__ . '/../Home/Footer.php'; ?>

</body>
</html>