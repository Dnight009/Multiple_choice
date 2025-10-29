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
require_once __DIR__ . '/../Check/Connect.php';

// 4. BIẾN LƯU THÔNG BÁO
$message = "";
$message_type = ""; // "success" hoặc "error"

// 5. XỬ LÝ KHI NGƯỜI DÙNG GỬI FORM
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Lấy ID người dùng từ session
    $user_id = $_SESSION['IDACC'];

    // Lấy dữ liệu từ form
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Bắt đầu kiểm tra ---

    // 1. Kiểm tra Mật khẩu mới có khớp không
    if ($new_password !== $confirm_password) {
        $message = "Lỗi: Mật khẩu mới và mật khẩu xác nhận không khớp!";
        $message_type = "error";
    } 
    // 2. Kiểm tra Mật khẩu mới có đủ mạnh không (ví dụ: ít nhất 6 ký tự)
    elseif (strlen($new_password) < 6) {
        $message = "Lỗi: Mật khẩu mới phải có ít nhất 6 ký tự!";
        $message_type = "error";
    } 
    // 3. Nếu mật khẩu khớp, kiểm tra mật khẩu cũ
    else {
        
        // Truy vấn CSDL để lấy mật khẩu (đã băm) hiện tại
        $stmt = $conn->prepare("SELECT password FROM ACCOUNT WHERE IDACC = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $current_hashed_password = $user['password'];

            // Dùng password_verify() để so sánh mật khẩu cũ người dùng nhập
            // với mật khẩu đã băm trong CSDL
            if (password_verify($old_password, $current_hashed_password)) {
                
                // --- THÀNH CÔNG: Mật khẩu cũ chính xác ---

                // Băm mật khẩu mới trước khi lưu
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Cập nhật mật khẩu mới vào CSDL
                $update_stmt = $conn->prepare("UPDATE ACCOUNT SET password = ? WHERE IDACC = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $message = "Đổi mật khẩu thành công!";
                    $message_type = "success";
                } else {
                    $message = "Lỗi: Không thể cập nhật mật khẩu. Vui lòng thử lại.";
                    $message_type = "error";
                }
                $update_stmt->close();
                
            } else {
                // LỖI: Mật khẩu cũ không đúng
                $message = "Lỗi: Mật khẩu cũ không chính xác!";
                $message_type = "error";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu</title>
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
            max-width: 600px; /* Thu nhỏ container */
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
        
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            color: #555;
            margin-bottom: 8px;
        }
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box; /* Quan trọng */
        }
        
        button[type="submit"] {
            background: #2d98da; 
            color: white; 
            padding: 12px 25px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            width: 100%; /* Nút full-width */
        }
        button[type="submit"]:hover { background: #2587c4; }

        /* CSS cho thông báo */
        .message { 
            padding: 15px; 
            margin-top: 20px; 
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
        <h1>Đổi mật khẩu</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="change_password.php" method="POST" style="margin-top: 20px;">
            
            <div class="form-group">
                <label for="old_password">Mật khẩu cũ:</label>
                <input type="password" name="old_password" id="old_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">Mật khẩu mới (ít nhất 6 ký tự):</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            
            <button type="submit">Cập nhật mật khẩu</button>
        </form>

    </div>

    <?php include __DIR__ . '/../Home/Footer.php'; ?>

</body>
</html>