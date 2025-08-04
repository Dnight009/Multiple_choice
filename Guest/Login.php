<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="../CSS/Guest/Auth.css">
</head>
<body>
  <div class="login-container">
    <form action="login.php" method="post" class="login-box fade-in">
      <h2>Login</h2>
      
      <label>Username</label>
      <input type="text" name="username" placeholder="Type your username" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="Type your password" required>

      <div class="forgot"><a href="#">Forgot password?</a></div>

      <button type="submit" class="login-btn">LOGIN</button>

      <p class="social-text">Or Sign Up Using</p>
      <div class="social-icons">
        <a href="#"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v5/icons/facebook.svg" alt="Facebook"></a>
        <a href="#"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v5/icons/twitter.svg" alt="Twitter"></a>
        <a href="#"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v5/icons/google.svg" alt="Google"></a>
      </div>

      <p class="signup-text">Or Sign Up Using <a href="Register.php">SIGN UP</a></p>
    </form>
  </div>
</body>
</html>
