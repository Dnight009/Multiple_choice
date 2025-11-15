<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Đặt ảnh mặc định
$avatar = "/images/default-avatar.png";

// Nếu đã đăng nhập và có ảnh trong session thì dùng ảnh đó
if (isset($_SESSION['avatar']) && !empty($_SESSION['avatar'])) {
    $avatar = $_SESSION['avatar'];
}
?>
<header>
    <link rel="stylesheet" href="../CSS/Home/navbar.css">
</header>
<nav class="navbar">
    <a href="#" class="logo">Trắc Nghiệm</a>
    <div class="nav-links">
        <a href="/Home/home.php">Trang chủ</a>
        <div class="dropdown" id="dropdownMenu">
            <a href="#" class="dropbtn" id="dropdownBtn">Danh mục &#9776;</a>
            <ul class="dropdown-content" id="dropdownContent">
                <li class="has-submenu">
                    <a href="#">Cấp 1</a>
                    <ul class="submenu">
                        <?php
                        // Tạo link cho các lớp từ 1 đến 5
                        for ($i = 1; $i <= 5; $i++) {
                            // Link sẽ trỏ đến file quiz_list.php (chúng ta sẽ tạo ở Bước 2)
                            echo "<li><a href='/Home/quiz_list.php?lop=$i'>Lớp $i</a></li>";
                        }
                        ?>
                    </ul>
                </li>                
                <li class="has-submenu">
                    <a href="#">Cấp 2</a>
                    <ul class="submenu">
                        <?php
                        // Tạo link cho các lớp từ 6 đến 9
                        for ($i = 6; $i <= 9; $i++) {
                            echo "<li><a href='/Home/quiz_list.php?lop=$i'>Lớp $i</a></li>";
                        }
                        ?>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#">Cấp 3</a>
                    <ul class="submenu">
                        <?php
                        // Tạo link cho các lớp từ 10 đến 12
                        for ($i = 10; $i <= 12; $i++) {
                            echo "<li><a href='/Home/quiz_list.php?lop=$i'>Lớp $i</a></li>";
                        }
                        ?>
                    </ul>
                </li>
                
                </ul>
        </div>        
        <a href="../Home/gop_y.php">đóng góp ý kiến </a>
        <a href="../Guest/history.php">Lịch sử làm bài </a>
        <a href="../Chat/chat.php">Nhắn tin </a>              
        
        <?php
        if (isset($_SESSION['quyen']) && $_SESSION['quyen'] == '3') {   
            echo '<a href="../Guest/dashboard.php">Quản lý</a>';
            }
        ?>
    </div>
    <div class="search-profile">
        <form action="../Home/search_results.php" method="GET" class="search-form-container">
            
            <input type="text" name="q" placeholder="Tìm kiếm..." required onkeydown="if (event.key === 'Enter') { this.form.submit(); }">
            
            <button type="submit" class="search-submit-btn">&#128269;</button>
        
        </form>
    </div>
    
    <div class="profile-dropdown">
        <button class="profile-btn" id="profileBtn" title="Tài khoản">
            <?php if (!isset($_SESSION['username'])): ?>
                <span>&#128100;</span>
            <?php else: ?>
                <img src="<?php echo $avatar; ?>" alt="Avatar" class="avatar-img">
            <?php endif; ?>
        </button>
        <ul class="profile-menu" id="profileMenu">
            <?php if (!isset($_SESSION['username'])): ?>
                <li><a href="/Guest/Login.php">Đăng nhập</a></li>
                <li><a href="/Guest/Register.php">Đăng ký</a></li>
            <?php else: ?>
                <li><a href="../Guest/profile.php">Thông tin cá nhân</a></li>
                <li><a href="../Guest/change_password.php">Đổi mật khẩu</a></li>
                <li><a href="/Guest/Logout.php">Đăng xuất</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown danh mục
    const btn = document.getElementById('dropdownBtn');
    const content = document.getElementById('dropdownContent');
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        content.classList.toggle('show');
    });
    document.addEventListener('click', function(e) {
        if (!btn.contains(e.target) && !content.contains(e.target)) {
            content.classList.remove('show');
        }
    });

    // Dropdown user
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    profileMenu.style.display = "none";
    profileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        profileMenu.style.display = profileMenu.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', function(e) {
        if (!profileBtn.contains(e.target)) {
            profileMenu.style.display = 'none';
        }
    });
});
</script>
