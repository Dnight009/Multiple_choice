<?php
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
        <button class="profile-btn" title="Trang cá nhân">
            <span>&#128100;</span>
        </button>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('dropdownBtn');
    const content = document.getElementById('dropdownContent');
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        content.classList.toggle('show');
    });
    // Đóng dropdown khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (!btn.contains(e.target) && !content.contains(e.target)) {
            content.classList.remove('show');
        }
    });
});
</script>