<?php
session_start();

// đảm bảo chỉ xử lý POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// include kết nối (đường dẫn tương đối: file connect.php nằm 1 cấp trên)
require_once __DIR__ . '/../Check/Connect.php';

// Lấy dữ liệu từ form
$username = trim($_POST['username']);
$password = trim($_POST['password']);

// Chuẩn bị câu lệnh SQL chống SQL Injection
$stmt = $conn->prepare("SELECT IDACC, username, password, quyen FROM Account WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra user tồn tại
if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // Nếu mật khẩu lưu trong DB là hash (khuyến nghị) thì dùng password_verify
    // Nếu bạn lưu plain text (không khuyến khích) thì so sánh trực tiếp
    if (password_verify($password, $row['password']) || $password === $row['password']) {
        
        // Lưu thông tin vào session
        $_SESSION['IDACC'] = $row['IDACC'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['quyen'] = $row['quyen'];

        // Chuyển hướng theo quyền
        if ($row['quyen'] == 1) {
            header("Location: http://localhost:8000/Admin/Adminhome.php");
            exit();
        } else {
            header("Location: http://localhost:8000/Home/home.php");
            exit();
        }

    } else {
        // Sai mật khẩu
        echo "<script>alert('Sai mật khẩu!'); window.location.href='Login.php';</script>";
    }
} else {
    // Không tìm thấy username
    echo "<script>alert('Tài khoản không tồn tại!'); window.location.href='Login.php';</script>";
}

$stmt->close();
$conn->close();
?>
