<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KẾT NỐI DATABASE (Sửa đường dẫn file Connect.php nếu cần)
require_once __DIR__ . '/../Check/Connect.php'; 

// 3. BIẾN LƯU THÔNG BÁO
$message = "";
$message_type = ""; // "success" hoặc "error"

// 4. KIỂM TRA ĐĂNG NHẬP
// Người dùng phải đăng nhập mới được góp ý
if (!isset($_SESSION['IDACC'])) {
    // Nếu chưa, chuyển hướng về trang đăng nhập (Sửa đường dẫn nếu cần)
    header("Location: ../Guest/login.php");
    exit;
}

// 5. XỬ LÝ KHI NGƯỜI DÙNG GỬI FORM (phương thức POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Kiểm tra xem nội dung có bị trống không
    if (isset($_POST['noi_dung_y_kien']) && !empty(trim($_POST['noi_dung_y_kien']))) {
        
        // Lấy nội dung và ID người dùng
        $noi_dung = trim($_POST['noi_dung_y_kien']);
        $id_user = $_SESSION['IDACC'];

        // 6. DÙNG PREPARED STATEMENT ĐỂ LƯU VÀO CSDL (Bảo mật)
        $sql = "INSERT INTO CONTRIBUTE_IDEAS (IDACC, noi_dung_y_kien) VALUES (?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_user, $noi_dung); // "i" = integer, "s" = string
        
        if ($stmt->execute()) {
            $message = "Cảm ơn bạn đã đóng góp ý kiến! Chúng tôi đã ghi nhận.";
            $message_type = "success";
        } else {
            $message = "Đã xảy ra lỗi. Vui lòng thử lại sau. Lỗi: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
        
    } else {
        $message = "Vui lòng nhập nội dung ý kiến của bạn trước khi gửi.";
        $message_type = "error";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đóng góp ý kiến</title>
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
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            flex-grow: 1; /* Đẩy footer xuống dưới */
        }
        h1 { text-align: center; color: #333; }
        textarea { 
            width: 100%; 
            height: 150px; 
            padding: 12px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            font-size: 16px; 
            box-sizing: border-box; /* Quan trọng để padding không làm vỡ layout */
        }
        button[type="submit"] { 
            background: #2d98da; 
            color: white; 
            padding: 12px 25px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
            display: block; 
            margin-top: 15px; 
            font-weight: bold;
        }
        .idea-form button[type="submit"]:hover { background: #2587c4; }
        
        /* CSS cho thông báo */
        .message { 
            padding: 15px; 
            margin-top: 20px; 
            border-radius: 5px; 
            font-weight: bold;
        }
        .success { background: #e6f7e9; color: #28a745; border: 1px solid #b7e9c7; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        /* Thêm vào cuối thẻ <style> của gop_y.php */
        .button-group {
            display: flex;
            align-items: center;
            gap: 15px; /* Khoảng cách giữa 2 nút */
            margin-top: 15px;
        }

/* Sửa lại nút submit cũ để xóa margin-top */
        .idea-form button[type="submit"] {
            margin-top: 0; 
        }

/* CSS cho nút "Xem danh sách" */
        .btn-secondary {
            background: #f4f4f4; /* Màu xám nhạt */
            color: #333;
            padding: 12px 25px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none; /* Bỏ gạch chân của thẻ <a> */
            display: inline-block;
            transition: background 0.2s;
        }
        .btn-secondary:hover {
            background: #e9e9e9;
        }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="container">
        <h1>Đóng góp ý kiến xây dựng website</h1>
        <p>Chúng tôi luôn lắng nghe ý kiến của bạn để cải thiện trang web tốt hơn. Vui lòng để lại góp ý của bạn bên dưới.</p>
        
        <form action="gop_y.php" method="POST" class="idea-form">
            
            <label for="idea_content" style="font-weight: bold; margin-bottom: 10px; display: block;">
                Nội dung ý kiến của bạn:
            </label>
            
            <textarea name="noi_dung_y_kien" id="noi_dung_y_kien" required></textarea>
            
            <div class="button-group">
                <button type="submit">Gửi ý kiến</button>    
                <a href="gop_y_list.php" class="btn-secondary"> Xem danh sách góp ý   </a>
            </div>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

    </div>

    <?php include __DIR__ . '/../Home/Footer.php'; ?>

</body>
</html>