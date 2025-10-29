<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['IDACC'])) {
    header("Location: /TracNghiem/Guest/Login.php");
    exit;
}

// 3. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php'; // Sửa đường dẫn nếu cần

// 4. LẤY ID NGƯỜI DÙNG TỪ SESSION
$user_id = $_SESSION['IDACC'];

// 5. BIẾN LƯU THÔNG BÁO
$message = "";
$message_type = ""; // "success" hoặc "error"

// 6. XỬ LÝ KHI NGƯỜI DÙNG CẬP NHẬT (GỬI FORM POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Lấy dữ liệu đã chỉnh sửa từ form
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $ngay_sinh = $_POST['ngay_sinh'];
    
    // --- THAY ĐỔI 1: Lấy 'gioi_tinh' từ form ---
    $gioi_tinh = $_POST['gioi_tinh'];

    // Xử lý giá trị rỗng cho ngày sinh -> đổi thành NULL
    if (empty($ngay_sinh)) {
        $ngay_sinh = NULL;
    }
    // Xử lý giá trị rỗng cho giới tính -> đổi thành NULL (hoặc giá trị mặc định nếu ENUM không cho phép)
    if (empty($gioi_tinh)) {
        $gioi_tinh = NULL;
    }

    // --- THAY ĐỔI 2: Cập nhật câu lệnh UPDATE ---
    $stmt = $conn->prepare("UPDATE ACCOUNT SET ho_ten = ?, email = ?, ngay_sinh = ?, gioi_tinh = ? WHERE IDACC = ?");
    // "ssssi" = string, string, string, string, integer
    $stmt->bind_param("ssssi", $ho_ten, $email, $ngay_sinh, $gioi_tinh, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Cập nhật hồ sơ thành công!";
        $_SESSION['flash_type'] = "success";
        header("Location: profile.php");
        exit; 
    } else {
        if ($conn->errno == 1062) { 
            $message = "Lỗi: Email này đã được sử dụng bởi một tài khoản khác.";
        } else {
            // Hiển thị lỗi SQL nếu có
            $message = "Đã xảy ra lỗi khi cập nhật: " . $stmt->error;
        }
        $message_type = "error";
    }
    $stmt->close();
}

// 7. LUÔN LẤY DỮ LIỆU MỚI NHẤT TỪ CSDL ĐỂ HIỂN THỊ
// (Câu SELECT của bạn đã có 'gioi_tinh', nên không cần sửa)
$stmt_get = $conn->prepare("SELECT username, email, ho_ten, ngay_sinh, gioi_tinh, ngay_tao FROM ACCOUNT WHERE IDACC = ?");
$stmt_get->bind_param("i", $user_id);
$stmt_get->execute();
$result = $stmt_get->get_result();

if ($result->num_rows === 0) {
    echo "Lỗi: Không tìm thấy thông tin tài khoản.";
    exit;
}

$user = $result->fetch_assoc();
$stmt_get->close();
$conn->close();

// 8. XỬ LÝ (FORMAT) DỮ LIỆU ĐỂ ĐIỀN VÀO FORM
$username = htmlspecialchars($user['username']);
$ho_ten_value = !empty($user['ho_ten']) ? htmlspecialchars($user['ho_ten']) : "";
$email_value = !empty($user['email']) ? htmlspecialchars($user['email']) : "";
$ngay_sinh_value = !empty($user['ngay_sinh']) ? htmlspecialchars($user['ngay_sinh']) : "";

// --- THAY ĐỔI 3: Lấy giá trị 'gioi_tinh' hiện tại ---
$gioi_tinh_value = !empty($user['gioi_tinh']) ? htmlspecialchars($user['gioi_tinh']) : "";

$ngay_tao = !empty($user['ngay_tao']) 
            ? date("d/m/Y H:i", strtotime($user['ngay_tao'])) 
            : "<i>Không rõ</i>";

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật hồ sơ</title>
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
        .profile-form {
            margin-top: 20px;
        }
        .info-row {
            display: flex;
            align-items: center; 
            margin-bottom: 20px;
            font-size: 1.1em;
            line-height: 1.6;
        }
        .info-row label {
            font-weight: bold;
            color: #555;
            width: 180px;
            flex-shrink: 0;
        }
        
        /* --- THAY ĐỔI 4: Thêm 'select' vào CSS --- */
        .info-row span,
        .info-row input[type="text"],
        .info-row input[type="date"],
        .info-row input[type="email"],
        .info-row select { /* <-- ĐÃ THÊM 'select' VÀO ĐÂY */
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            /* Thêm dòng này để style select nhất quán */
            background-color: white; 
        }

        .info-row span.readonly {
            background: #f4f4f4;
            color: #777;
            border: 1px solid #ddd;
        }

        /* (CSS cho các nút và thông báo giữ nguyên) */
        .action-group { display: flex; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0; }
        .btn-primary, .btn-secondary { padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; text-align: center; }
        .btn-primary { background: #28a745; color: white; }
        .btn-primary:hover { background: #218838; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .success { background: #e6f7e9; color: #28a745; border: 1px solid #b7e9c7; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="container">
        <h1>Cập nhật Hồ sơ</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form class="profile-form" action="update_profile.php" method="POST">
            
            <div class="info-row">
                <label>Tên đăng nhập:</label>
                <span class="readonly"><?php echo $username; ?></span>
            </div>

            <div class="info-row">
                <label for="ho_ten">Họ và Tên:</label>
                <input type="text" name="ho_ten" id="ho_ten" value="<?php echo $ho_ten_value; ?>">
            </div>

            <div class="info-row">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo $email_value; ?>">
            </div>

            <div class="info-row">
                <label for="ngay_sinh">Ngày sinh:</label>
                <input type="date" name="ngay_sinh" id="ngay_sinh" value="<?php echo $ngay_sinh_value; ?>">
            </div>

            <div class="info-row">
                <label for="gioi_tinh">Giới tính:</label>
                <select name="gioi_tinh" id="gioi_tinh">
                    <option value="" <?php if(empty($gioi_tinh_value)) echo 'selected'; ?>>
                        -- Vui lòng chọn --
                    </option>
                    <option value="Nam" <?php if($gioi_tinh_value == "Nam") echo 'selected'; ?>>
                        Nam
                    </option>
                    <option value="Nữ" <?php if($gioi_tinh_value == "Nữ") echo 'selected'; ?>>
                        Nữ
                    </option>
                    <option value="Khác" <?php if($gioi_tinh_value == "Khác") echo 'selected'; ?>>
                        Khác
                    </option>
                </select>
            </div>
            
            <div class="info-row">
                <label>Tham gia ngày:</label>
                <span class="readonly"><?php echo $ngay_tao; ?></span>
            </div>
            
            <div class="action-group">
                <button type="submit" class="btn-primary">
                    Cập nhật
                </button>
                <a href="profile.php" class="btn-secondary">
                    Hủy bỏ
                </a>
            </div>

        </form>
    </div>

    <?php include __DIR__ . '/../Home/Footer.php'; ?>

</body>
</html>