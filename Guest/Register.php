<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng ký tài khoản</title>
  <link rel="stylesheet" href="../CSS/Guest/Auth.css">
</head>
<body>
  <div class="login-container">
    <?php
      if (!empty($_SESSION['error'])) {
        echo '<p style="color:red; text-align:center;">'.htmlspecialchars($_SESSION['error']).'</p>';
        unset($_SESSION['error']);
      }
      if (!empty($_SESSION['success'])) {
        echo '<p style="color:green; text-align:center;">'.htmlspecialchars($_SESSION['success']).'</p>';
        unset($_SESSION['success']);
      }
    ?>
    <form action="process_register.php" method="post" class="login-box fade-in">
      <h2>Đăng Ký</h2>
      
      <label>Tên đăng nhập (Username)</label>
      <input type="text" name="username" placeholder="Nhập tên đăng nhập" required>

      <label>Họ và tên</label>
      <input type="text" name="ho_ten" placeholder="Nhập họ tên đầy đủ" required>

      <label>Ngày sinh</label>
      <input type="date" name="ngay_sinh" required>

      <label>Giới tính</label>
      <select name="gioi_tinh" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
          <option value="Nam">Nam</option>
          <option value="Nữ">Nữ</option>
          <option value="Khác">Khác</option>
      </select>

      <label>Mật khẩu</label>
      <input type="password" name="password" placeholder="Nhập mật khẩu" required>

      <label>Nhập lại mật khẩu</label>
      <input type="password" name="repassword" placeholder="Nhập lại mật khẩu" required>

      <button type="submit" class="login-btn">ĐĂNG KÝ</button>

      <p class="signup-text">Đã có tài khoản? <a href="Login.php">ĐĂNG NHẬP</a></p>
    </form>
  </div>
</body>
</html>