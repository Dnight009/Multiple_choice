<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng xuất thành công</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .logout-box {
            background: #fff;
            padding: 40px 60px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
        }
        .logout-box h2 {
            color: #2d98da;
            margin-bottom: 16px;
        }
        .logout-box p {
            color: #333;
            margin-bottom: 24px;
        }
        .btn-home {
            display: inline-block;
            background: #2d98da;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn-home:hover {
            background: #3867d6;
        }
    </style>
</head>
<body>
    <div class="logout-box">
        <h2>Cám ơn bạn đã sử dụng!</h2>
        <p>Bạn đã đăng xuất thành công.</p>
        <a href="/Home/home.php" class="btn-home">Quay về Trang chủ</a>
    </div>
</body>
</html>

