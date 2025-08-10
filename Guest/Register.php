<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../css/Guest/Auth.css">
</head>
<body>
  <div class="login-container">
    <?php
      if (!empty($_SESSION['error'])) {
        echo '<p style="color:red;">'.htmlspecialchars($_SESSION['error']).'</p>';
        unset($_SESSION['error']);
      }
      if (!empty($_SESSION['success'])) {
        echo '<p style="color:green;">'.htmlspecialchars($_SESSION['success']).'</p>';
        unset($_SESSION['success']);
      }
    ?>
    <form action="process_register.php" method="post" class="login-box fade-in">
      <h2>Sign Up</h2>
      
      <label>Username</label>
      <input type="text" name="username" placeholder="Choose a username" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="Enter password" required>

      <label>Re-Password</label>
      <input type="password" name="repassword" placeholder="Repeat password" required>

      <button type="submit" class="login-btn">SIGN UP</button>

      <p class="signup-text">Already have an account? <a href="Login.php">LOGIN</a></p>
    </form>
  </div>
</body>
</html>
