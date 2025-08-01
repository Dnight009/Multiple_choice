<?php
?>
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
<style>
.navbar {
    background: #2d98da;
    padding: 0 32px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.navbar .logo {
    color: #fff;
    font-size: 22px;
    font-weight: bold;
    text-decoration: none;
    letter-spacing: 1px;
}
.nav-links {
    display: flex;
    gap: 24px;
    align-items: center;
}
.nav-links a, .dropbtn {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    padding: 8px 12px;
    cursor: pointer;
    background: none;
    border: none;
    outline: none;
    transition: color 0.2s;
}
.nav-links a:hover, .dropbtn:hover {
    color: #f1c40f;
}
.dropdown {
    position: relative;
}
.dropdown-content {
    display: none;
    position: absolute;
    top: 38px;
    left: 0;
    background: #fff;
    min-width: 220px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    border-radius: 6px;
    z-index: 100;
    padding: 8px 0;
    list-style: none;
}
.dropdown-content.show {
    display: block;
}
.dropdown-content > li > a {
    color: #2d98da;
    padding: 10px 24px;
    display: block;
    text-decoration: none;
    font-weight: 500;
    white-space: nowrap;
}
.dropdown-content > li > a:hover {
    background: #f5f6fa;
    color: #3867d6;
}
.has-submenu {
    position: relative;
}
.has-submenu > a:after {
    content: "▶";
    float: right;
    font-size: 12px;
    margin-top: 4px;
}
.submenu {
    display: none;
    position: absolute;
    left: 100%;
    top: 0;
    background: #fff;
    min-width: 180px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    border-radius: 6px;
    z-index: 101;
    padding: 8px 0;
    list-style: none;
}
.has-submenu:hover > .submenu {
    display: block;
}
.submenu li a {
    color: #2d98da;
    padding: 10px 24px;
    display: block;
    text-decoration: none;
    font-weight: 400;
    white-space: nowrap;
}
.submenu li a:hover {
    background: #f5f6fa;
    color: #3867d6;
}
.search-profile {
    display: flex;
    align-items: center;
    gap: 16px;
}
.search-profile input[type="text"] {
    padding: 6px 12px;
    border-radius: 4px;
    border: none;
    font-size: 15px;
    outline: none;
}
.profile-btn {
    background: #fff;
    color: #2d98da;
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}
.profile-btn:hover {
    background: #f1c40f;
    color: #fff;
}
</style>
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