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
                        <li><a href="#">Lớp 1</a></li>
                        <li><a href="#">Lớp 2</a></li>
                        <li><a href="#">Lớp 3</a></li>
                        <li><a href="#">Lớp 4</a></li>
                        <li><a href="#">Lớp 5</a></li>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#">Cấp 2</a>
                    <ul class="submenu">
                        <li><a href="#">Lớp 6</a></li>
                        <li><a href="#">Lớp 7</a></li>
                        <li><a href="#">Lớp 8</a></li>
                        <li><a href="#">Lớp 9</a></li>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#">Cấp 3</a>
                    <ul class="submenu">
                        <li><a href="#">Lớp 10</a></li>
                        <li><a href="#">Lớp 11</a></li>
                        <li><a href="#">Lớp 12</a></li>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#">Ôn thi</a>
                    <ul class="submenu">
                        <li><a href="#">Ôn thi tuyển sinh cấp 3</a></li>
                        <li><a href="#">Ôn thi tuyển sinh đại học</a></li>
                    </ul>
                </li>
                <li><a href="#">Theo môn học</a></li>               
                <li><a href="#">Đại học</a></li>
            </ul>
        </div>        
        <a href="#">Các bài trắc nghiệm</a>
        <a href="/Tracnghiem/create.php">Tạo bộ đề</a>
    </div>
    <div class="search-profile">
        <input type="text" placeholder="Tìm kiếm...">        
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
                <li><a href="/User/profile.php">Thông tin cá nhân</a></li>
                <li><a href="/User/change_password.php">Đổi mật khẩu</a></li>
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
