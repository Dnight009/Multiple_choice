<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KẾT NỐI DATABASE
// (Giả sử file Connect.php của bạn ở trong thư mục /Check/)
require_once __DIR__ . '/../Check/Connect.php'; 

// 3. BIẾN LƯU THÔNG BÁO
$message = "";
$message_type = ""; // "success" hoặc "error"

// 4. KIỂM TRA BẢO MẬT (RẤT QUAN TRỌNG)
// Phải đăng nhập
if (!isset($_SESSION['IDACC'])) {
    // Nếu chưa, chuyển về trang đăng nhập (cùng thư mục Guest)
    header("Location: Login.php"); 
    exit;
}
// Phải là giáo viên (Giả sử quyen = 3 là giáo viên)
if (!isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    // Nếu là học sinh hoặc vai trò khác, đuổi về trang chủ
    header("Location: /TracNghiem/Home/home.php");
    exit;
}

// Nếu qua được 2 vòng kiểm tra, lấy ID của giáo viên
$teacher_id = $_SESSION['IDACC'];

// 5. XỬ LÝ KHI GIÁO VIÊN GỬI FORM
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Kiểm tra tên lớp có được gửi lên và không rỗng
    if (isset($_POST['ten_lop_hoc']) && !empty(trim($_POST['ten_lop_hoc']))) {
        
        $ten_lop = trim($_POST['ten_lop_hoc']);

        // 6. CHUẨN BỊ LỆNH SQL
        // Chúng ta chỉ cần chèn 2 cột này.
        // ID_CLASS (tự tăng), ngay_tao (default), trang_thai (default)
        $sql = "INSERT INTO CLASS (IDACC_teach, ten_lop_hoc) VALUES (?, ?)";
        
        $stmt = $conn->prepare($sql);
        // "i" = integer (IDACC_teach), "s" = string (ten_lop_hoc)
        $stmt->bind_param("is", $teacher_id, $ten_lop); 

        if ($stmt->execute()) {
            $message = "Tạo lớp học '" . htmlspecialchars($ten_lop) . "' thành công!";
            $message_type = "success";
        } else {
            $message = "Đã xảy ra lỗi khi tạo lớp. Lỗi: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();

    } else {
        $message = "Vui lòng nhập tên lớp học.";
        $message_type = "error";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Lớp Học Mới</title>
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
            max-width: 600px; 
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
        .form-group input[type="text"] {
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
        /* Thêm vào cuối <style> */
        .button-group {
            display: flex;
            gap: 15px; /* Khoảng cách giữa 2 nút */
            margin-top: 20px;
        }

/* Style cho cả 2 nút trong nhóm */
        .button-group button, .button-group a {
            flex: 1; /* Chia độ rộng bằng nhau */
            text-align: center;
        }

/* Style cho nút "Coi danh sách" (thẻ <a>) */
        .btn-secondary {
            background: #f4f4f4; 
            color: #333;
            padding: 12px 25px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px; 
            font-weight: bold;
            text-decoration: none;
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
        <h1>Tạo Lớp Học Mới</h1>
        
        <?php if (!empty($message)): ?>
            <div classmessage <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="class_create.php" method="POST" style="margin-top: 20px;">
            
            <div class="form-group">
                <label for="ten_lop_hoc">Tên lớp học:</label>
                <input type="text" name="ten_lop_hoc" id="ten_lop_hoc" 
                       placeholder="Ví dụ: Lớp 12A1 - Trường THCS Võ Minh Đức" required>
            </div>
            <div class="button-group">
                <button type="submit">Tạo lớp</button>
                <a href="List_Class.php" class="btn-secondary"> Coi danh sách lớp đã tạo </a>
            </div>
        </form>

    </div>

    <?php include __DIR__ . '/../Home/Footer.php'; ?>

</body>
</html>