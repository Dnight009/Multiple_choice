<?php
// Trang chủ trắc nghiệm
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Chủ Trắc Nghiệm</title>
    <link rel="stylesheet" href="../CSS/Home/home.css">
</head>
<body>
    <div class="main-content">
        <div class="card-grid">
            <!-- Đánh giá năng lực -->
            <div class="card">
                <div class="card-badge">Đ</div>
                <div class="card-title">Đánh giá năng lực</div>
                <div class="card-desc">
                    Kỳ thi đánh giá năng lực về cơ bản là bài kiểm tra tập trung đánh giá năng lực cơ bản của thí sinh chuẩn bị bước vào đại học.
                </div>
                <div class="card-stars">★★★★★</div>
                <div class="card-list">
                    + ĐHQG Hà Nội <a href="#">361 đề thi</a><br>
                    + ĐH Bách Khoa <a href="#">342 đề thi</a><br>
                    + ĐHQG Hồ Chí Minh <a href="#">328 đề thi</a>
                </div>
                <div class="card-footer">
                    <a href="#" class="card-link">Xem tất cả »</a>
                    <span class="card-count"><span style="color:#e74c3c;">&#10084;</span> 1.050 bộ đề</span>
                </div>
            </div>
            <!-- Tốt nghiệp THPT -->
            <div class="card">
                <div class="card-badge red">T</div>
                <div class="card-title red">Tốt nghiệp THPT</div>
                <div class="card-desc">
                    Luyện thi các bài tập trắc nghiệm trực tuyến sát đề có lời giải chi tiết giúp khả năng đạt được điểm cao.
                </div>
                <div class="card-stars">★★★★★</div>
                <div class="card-list">
                    + Hóa học <a href="#">1.986 đề thi</a><br>
                    + Tiếng Anh <a href="#">1.722 đề thi</a><br>
                    + Vật lý <a href="#">1.459 đề thi</a>
                </div>
                <div class="card-footer">
                    <a href="#" class="card-link">Xem tất cả »</a>
                    <span class="card-count"><span style="color:#e74c3c;">&#10084;</span> 10.726 bộ đề</span>
                </div>
            </div>
            <!-- Lớp 12 -->
            <div class="card">
                <div class="card-badge yellow">12</div>
                <div class="card-title yellow">Lớp 12</div>
                <div class="card-desc">
                    Luyện bài tập trắc nghiệm trực tuyến các môn Toán, Lý, Hóa, Sinh, Anh, Sử, Địa, GDCD theo hướng đánh giá năng lực.
                </div>
                <div class="card-stars">★★★★★</div>
                <div class="card-list">
                    + Toán <a href="#">2.046 đề thi</a><br>
                    + Hóa học <a href="#">1.985 đề thi</a><br>
                    + Vật lý <a href="#">1.438 đề thi</a>
                </div>
                <div class="card-footer">
                    <a href="#" class="card-link">Xem tất cả »</a>
                    <span class="card-count" style="color:#f1c40f;"><span style="color:#e74c3c;">&#10084;</span> 11.124 bộ đề</span>
                </div>
            </div>
            <!-- Lớp 11 -->
            <div class="card">
                <div class="card-badge yellow">11</div>
                <div class="card-title yellow">Lớp 11</div>
                <div class="card-desc">
                    Làm bài tập trắc nghiệm theo mức độ tư duy các môn Toán, Lý, Hóa, Sinh, Anh, Sử, Địa có ngay đáp án và lời giải chi tiết.
                </div>
                <div class="card-stars">★★★★★</div>
                <div class="card-list">
                    + Toán <a href="#">1.582 đề thi</a><br>
                    + Hóa học <a href="#">1.355 đề thi</a><br>
                    + Vật lý <a href="#">1.101 đề thi</a>
                </div>
                <div class="card-footer">
                    <a href="#" class="card-link">Xem tất cả »</a>
                </div>
            </div>
            <!-- Lớp 10 -->
            <div class="card">
                <div class="card-badge blue">10</div>
                <div class="card-title blue">Lớp 10</div>
                <div class="card-desc">
                    Làm bài tập trắc nghiệm có phương pháp và lời giải các môn Toán, Lý, Hóa, Sinh, Anh, Sử, Địa chắc kiến thức lớp 10.
                </div>
                <div class="card-stars">★★★★★</div>
                <div class="card-list">
                    + Toán <a href="#">1.504 đề thi</a><br>
                    + Tiếng Anh <a href="#">1.220 đề thi</a><br>
                    + Vật lý <a href="#">1.101 đề thi</a>
                </div>
                <div class="card-footer">
                    <a href="#" class="card-link">Xem tất cả »</a>
                </div>
            </div>
            <!-- Ôn vào 10 -->
            <div class="card">
                <div class="card-badge purple">10+</div>
                <div class="card-title purple">Ôn vào 10</div>
                <div class="card-desc">
                    Ôn thi vào 10 - Các chủ đề ôn luyện được phân hoạch bám sát theo cấu trúc, ma trận đề thi vào lớp 10 của các tỉnh, thành phố.
                </div>
                <div class="card-stars">★★★★★</div>
                <div class="card-list">
                    + Tiếng Anh <a href="#">114 đề thi</a><br>
                    + Văn <a href="#">106 đề thi</a><br>
                    + Toán <a href="#">94 đề thi</a>
                </div>
                <div class="card-footer">
                    <a href="#" class="card-link">Xem tất cả »</a>
                </div>
            </div>
        </div>
    </div>
    <?php include 'aichat.php'; ?>
    <?php include 'Footer.php'; ?>    
</body>
</html>