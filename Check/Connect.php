<?php
$servername = "localhost";
$username   = "root";      // mặc định XAMPP
$password   = "Hoang2707";          // mặc định trống
$dbname     = "tracnghiem"; // database bạn tạo

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
