<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. BẢO MẬT VÀ LẤY ID
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    header("Location: /TracNghiem/Home/home.php");
    exit;
}
$teacher_id = $_SESSION['IDACC'];
$teacher_username = $_SESSION['username'] ?? 'Giáo viên'; // Lấy tên GV

// 2. [THÊM MỚI] KẾT NỐI VÀ ĐẾM DỮ LIỆU
require_once __DIR__ . '/../Check/Connect.php'; // Sửa đường dẫn nếu cần

// Đếm tổng số đề thi của giáo viên này
$stmt_de = $conn->prepare("SELECT COUNT(ID_TD) as total FROM TEN_DE WHERE IDACC = ?");
$stmt_de->bind_param("i", $teacher_id);
$stmt_de->execute();
$total_de = $stmt_de->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_de->close();

// Đếm tổng số lớp của giáo viên này (chỉ đếm lớp đang hoạt động)
$stmt_lop = $conn->prepare("SELECT COUNT(ID_CLASS) as total FROM CLASS WHERE IDACC_teach = ? AND trang_thai = 'đang hoạt động'");
$stmt_lop->bind_param("i", $teacher_id);
$stmt_lop->execute();
$total_lop = $stmt_lop->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_lop->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bảng điều khiển Giáo viên</title>
    <link rel="stylesheet" href="../CSS/Admin/AdminHome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Bảng điều khiển</h2>
            </div>
            <ul class="sidebar-menu">
                <li class="active"><a href="/TracNghiem/dashboard.php" style="color:white; text-decoration:none;">Dashboard</a></li>
                
                <li><a href="/Tracnghiem/create.php" style="color:white; text-decoration:none;">Tạo Bộ Đề</a></li>
                <li><a href="/Tracnghiem/list_created.php" style="color:white; text-decoration:none;">Quản Lý Đề Thi</a></li>
                <li><a href="/Guest/class_create.php" style="color:white; text-decoration:none;">Tạo Lớp Học</a></li>
                <li><a href="/Guest/List_Class.php" style="color:white; text-decoration:none;">Quản Lý Lớp Học</a></li>
                <li><a href="/Guest/manage_grading.php" style="color:white; text-decoration:none;">Quản lý Sổ điểm</a></li>
                <hr style="border-color: #1f2a38;">
                <li><a href="/Home/home.php" style="color:white; text-decoration:none;">Về Trang Chủ</a></li>
            </ul>
        </aside>
        
        <main class="dashboard">
            <div class="dashboard-header">
                <h1>Giáo viên:<span class="subtext">Bảng điều khiển </span></h1>
                <div class="profile"><?php echo htmlspecialchars($teacher_username); ?></div>
            </div>

            <div class="card-grid">

                <div class="card card-blue">
                    <div class="card-value"><?php echo $total_de; ?></div>
                    <div class="card-label">Tổng số đề đã tạo</div>
                    <a href="/TracNghiem/list_created.php" class="card-link">Quản lý <span>&#8594;</span></a>
                </div>

                <div class="card card-green">
                    <div class="card-value"><?php echo $total_lop; ?></div>
                    <div class="card-label">Lớp học đang hoạt động</div>
                    <a href="/Guest/List_Class.php" class="card-link">Quản lý <span>&#8594;</span></a>
                </div>
                
                <div class="card card-orange">
                    <div class="card-value">+</div>
                    <div class="card-label">Tạo Bộ Đề Mới</div>
                    <a href="/Tracnghiem/create.php" class="card-link">Bắt đầu <span>&#8594;</span></a>
                </div>
                
                <div class="card card-red">
                    <div class="card-value">+</div>
                    <div class="card-label">Tạo Lớp Học Mới</div>
                    <a href="/Guest/class_create.php" class="card-link">Bắt đầu <span>&#8594;</span></a>
                </div>

            </div>
        </main>
    </div>
</body>
</html>