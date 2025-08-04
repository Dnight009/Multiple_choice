<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../css/Guest/Auth.css">
</head>
<body>
  <div class="login-container">
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
