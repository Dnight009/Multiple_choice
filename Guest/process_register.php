<?php
session_start();

// đảm bảo chỉ xử lý POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}
    
// include kết nối (đường dẫn tương đối: file connect.php nằm 1 cấp trên)
require_once __DIR__ . '/../Check/Connect.php';

// Lấy dữ liệu và validate cơ bản
$username   = trim($_POST['username'] ?? '');
$password   = $_POST['password'] ?? '';
$repassword = $_POST['repassword'] ?? '';

if ($username === '' || $password === '' || $repassword === '') {
    $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin.";
    header('Location: register.php');
    exit;
}

if ($password !== $repassword) {
    $_SESSION['error'] = "Mật khẩu nhập lại không khớp.";
    header('Location: register.php');
    exit;
}

// kiểm tra username hợp lệ (ví dụ: chỉ cho phép chữ số, chữ cái, gạch dưới, từ 3-50 ký tự)
if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
    $_SESSION['error'] = "Tên tài khoản không hợp lệ (3-50 ký tự: chữ, số, gạch dưới).";
    header('Location: register.php');
    exit;
}

// kiểm tra đã tồn tại username chưa
$checkStmt = $conn->prepare("SELECT IDACC FROM Account WHERE username = ? LIMIT 1");
if (!$checkStmt) {
    $_SESSION['error'] = "Lỗi chuẩn bị truy vấn: " . $conn->error;
    header('Location: register.php');
    exit;
}
$checkStmt->bind_param('s', $username);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $_SESSION['error'] = "Tên tài khoản đã tồn tại.";
    header('Location: register.php');
    exit;
}
$checkStmt->close();

// hash mật khẩu
$hashed = password_hash($password, PASSWORD_DEFAULT);

// quyền mặc định = 2 (giáo viên)
$quyen = 2;

// chèn vào DB
$insert = $conn->prepare("INSERT INTO Account (username, password, quyen) VALUES (?, ?, ?)");
if (!$insert) {
    $_SESSION['error'] = "Lỗi chuẩn bị chèn: " . $conn->error;
    header('Location: register.php');
    exit;
}
$insert->bind_param('ssi', $username, $hashed, $quyen);

if ($insert->execute()) {
    $_SESSION['success'] = "Đăng ký thành công. Bạn có thể đăng nhập ngay bây giờ.";
    $insert->close();
    // đóng kết nối an toàn
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    header('Location: register.php');
    exit;
} else {
    $_SESSION['error'] = "Lỗi khi lưu tài khoản: " . $insert->error;
    $insert->close();
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    header('Location: register.php');
    exit;
}
