<?php
session_start();
// Kết nối CSDL (Đường dẫn có thể khác tùy cấu trúc thư mục của bạn)
require_once '../Check/Connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Lấy dữ liệu từ form
    $username   = trim($_POST['username']);
    $password   = $_POST['password'];
    $repassword = $_POST['repassword'];
    
    // [MỚI] Lấy thêm dữ liệu cá nhân
    $ho_ten     = trim($_POST['ho_ten']);
    $ngay_sinh  = $_POST['ngay_sinh'];
    $gioi_tinh  = $_POST['gioi_tinh']; // Giá trị sẽ là 'Nam', 'Nữ' hoặc 'Khác'

    // 2. Kiểm tra dữ liệu
    if ($password !== $repassword) {
        $_SESSION['error'] = "Mật khẩu nhập lại không khớp!";
        header("Location: Register.php");
        exit();
    }

    // 3. Kiểm tra Username đã tồn tại chưa
    $stmt = $conn->prepare("SELECT IDACC FROM account WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Tên đăng nhập đã tồn tại, vui lòng chọn tên khác.";
        header("Location: Register.php");
        exit();
    }
    $stmt->close();

    // 4. Thêm người dùng mới vào CSDL
    // Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Mặc định quyền = 2 (Học sinh/User), Ngày tạo = NOW()
    // Câu lệnh INSERT cập nhật thêm cột ho_ten, ngay_sinh, gioi_tinh
    $sql = "INSERT INTO account (username, password, ho_ten, ngay_sinh, gioi_tinh, quyen, ngay_tao) 
            VALUES (?, ?, ?, ?, ?, 2, NOW())";
            
    $stmt_insert = $conn->prepare($sql);
    
    // 'sssss' nghĩa là 5 tham số kiểu string
    $stmt_insert->bind_param("sssss", $username, $hashed_password, $ho_ten, $ngay_sinh, $gioi_tinh);

    if ($stmt_insert->execute()) {
        $_SESSION['success'] = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
        header("Location: Login.php"); // Chuyển sang trang đăng nhập
    } else {
        $_SESSION['error'] = "Lỗi hệ thống: " . $conn->error;
        header("Location: Register.php");
    }

    $stmt_insert->close();
    $conn->close();
}
?>