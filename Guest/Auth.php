<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký & Đăng nhập tài khoản</title>
    <link rel="stylesheet" href="../CSS/Guest/auth.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="auth-container">
        <form class="auth-form" id="register-form" action="register_process.php" method="POST">
            <h2>Đăng ký tài khoản</h2>
            <div class="form-row">
                <label for="name">Tên:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-row">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-row">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-row">
                <label for="confirm_password">Nhập lại mật khẩu:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn main-btn">Đăng ký</button>
                <div class="social-btn">
                    <a href="#" title="Google" class="circle-btn">G</a>
                    <a href="#" title="Facebook" class="circle-btn">F</a>
                </div>
            </div>
            <div class="switch-form">
                <span>Bạn đã có tài khoản?</span>
                <!-- Nút đăng nhập sẽ mở modal đăng nhập -->
                <button type="button" class="link-btn" onclick="showLogin()">Đăng nhập</button>
            </div>
        </form>

        <!-- Modal Login Form -->
        <div class="modal" id="login-modal">
            <div class="modal-content">
                <span class="close" onclick="hideLogin()">&times;</span>
                <!-- Form đăng nhập chỉ chuyển hướng qua ../Home/home.php -->
                <form id="login-form" action="../Home/home.php" method="POST">
                    <h2>Đăng nhập</h2>
                    <div class="form-row">
                        <label for="login_username">Tên đăng nhập:</label>
                        <input type="text" id="login_username" name="login_username" required>
                    </div>
                    <div class="form-row">
                        <label for="login_password">Mật khẩu:</label>
                        <input type="password" id="login_password" name="login_password" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn main-btn">Đăng nhập</button>
                        <div class="social-btn">
                            <a href="#" title="Google" class="circle-btn">G</a>
                            <a href="#" title="Facebook" class="circle-btn">F</a>
                        </div>
                    </div>
                    <div class="switch-form">
                        <button type="button" class="link-btn" onclick="showForgot()">Quên mật khẩu?</button>
                        <button type="button" class="link-btn" onclick="hideLogin()">Quay lại đăng ký</button>
                    </div>
                </form>
                <!-- Modal Forgot Password -->
                <form id="forgot-form" style="display:none;" action="forgot_process.php" method="POST">
                    <h2>Quên mật khẩu</h2>
                    <div class="form-row">
                        <label for="forgot_username">Tên đăng nhập hoặc Email:</label>
                        <input type="text" id="forgot_username" name="forgot_username" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn main-btn">Gửi yêu cầu</button>
                        <button type="button" class="link-btn" onclick="showLoginForm()">Quay lại đăng nhập</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    function showLogin() {
        document.getElementById('login-modal').style.display = 'flex';
        document.getElementById('login-form').style.display = 'block';
        document.getElementById('forgot-form').style.display = 'none';
    }
    function hideLogin() {
        document.getElementById('login-modal').style.display = 'none';
    }
    function showForgot() {
        document.getElementById('login-form').style.display = 'none';
        document.getElementById('forgot-form').style.display = 'block';
    }
    function showLoginForm() {
        document.getElementById('forgot-form').style.display = 'none';
        document.getElementById('login-form').style.display = 'block';
    }
    // Đóng modal khi bấm ra ngoài
    window.onclick = function(event) {
        var modal = document.getElementById('login-modal');
        if (event.target == modal) {
            hideLogin();
        }
    }
</script>
</body>
</html>