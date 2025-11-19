<?php
// (MỚI) Bắt đầu session và kết nối CSDL
session_start(); 
require_once '../Check/Connect.php';

/**
 * (MỚI) Hàm trợ giúp để chạy truy vấn COUNT(*) và lấy kết quả
 * Điều này giúp code gọn gàng hơn
 */
function getCount($conn, $sql_query) {
    $result = $conn->query($sql_query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_array();
        return $row[0]; // Trả về giá trị của cột đầu tiên (kết quả COUNT)
    }
    return 0; // Trả về 0 nếu có lỗi hoặc không có kết quả
}

// --- THỰC HIỆN CÁC TRUY VẤN DATABASE ---

// 1. Tổng số User
$totalUsers = getCount($conn, "SELECT COUNT(IDACC) FROM account");

// 2. User đăng ký tháng này
$usersThisMonth = getCount($conn, "SELECT COUNT(IDACC) FROM account WHERE YEAR(ngay_tao) = YEAR(CURDATE()) AND MONTH(ngay_tao) = MONTH(CURDATE())");

// 3. User đăng ký năm nay
$usersThisYear = getCount($conn, "SELECT COUNT(IDACC) FROM account WHERE YEAR(ngay_tao) = YEAR(CURDATE())");

// 4. Tổng số đề thi
$totalTests = getCount($conn, "SELECT COUNT(ID_TD) FROM ten_de");

// --- [THÊM MỚI] CÁC CHỈ SỐ BẠN YÊU CẦU ---

// 5. Thống kê Lớp học (Hoạt động / Tổng)
$totalClasses = getCount($conn, "SELECT COUNT(ID_CLASS) FROM class");
$activeClasses = getCount($conn, "SELECT COUNT(ID_CLASS) FROM class WHERE trang_thai = 'đang hoạt động'");

// 6. Số Góp ý chưa xử lý
$pendingIdeas = getCount($conn, "SELECT COUNT(ID_IDEAS) FROM contribute_ideas WHERE status = 'chờ xử lý'");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../CSS/Admin/AdminHome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Master Admin</h2>
            </div>
            <ul class="sidebar-menu">
                <li class="active">Dashboard</li>
                <li><a href="manage_ideas.php" style="color:white; text-decoration:none;">Duyệt Góp Ý</a></li>
                <li><a href="manage_users.php" style="color:white; text-decoration:none;">Quản lý User</a></li>                
                <li><a href="manager_de.php" style="color:white; text-decoration:none;">Quản lý Đề thi</a></li>
            </ul>
        </aside>
        <main class="dashboard">
            <div class="dashboard-header">
                <h1>Dashboard <span class="subtext">Control panel</span></h1>
                <div class="profile"><a href="../Guest/profile.php">Laravel Admin</a></div>
            </div>
            
            <div class="card-grid">
                
                <div class="card card-pink">
                    <div class="card-value"><?= $totalUsers ?></div>
                    <div class="card-label">Tổng số User</div>
                    <a href="manage_users.php" class="card-link">Quản lý User <span>&#8594;</span></a>
                </div>

                <div class="card card-green">
                    <div class="card-value"><?= $usersThisMonth ?></div>
                    <div class="card-label">User đăng ký (Tháng này)</div>
                    <a href="manage_users.php" class="card-link">Chi tiết <span>&#8594;</span></a>
                </div>

                <div class="card card-blue">
                    <div class="card-value"><?= $usersThisYear ?></div>
                    <div class="card-label">User đăng ký (Năm nay)</div>
                    <a href="manage_users.php" class="card-link">Chi tiết <span>&#8594;</span></a>
                </div>
                
                <div class="card card-orange">
                    <div class="card-value"><?= $totalTests ?></div>
                    <div class="card-label">Tổng số Đề thi</div>
                    <a href="manager_de.php" class="card-link">Quản lý Đề thi <span>&#8594;</span></a>
                </div>
                
                <div class="card card-red">
                    <div class="card-value"><?= $activeClasses ?> / <?= $totalClasses ?></div>
                    <div class="card-label">Lớp hoạt động / Tổng</div>
                    <a href="../Admin/manage_classes.php" class="card-link">Quản lý <span>&#8594;</span></a>
                </div>

                <div class="card card-blue"> <div class="card-value"><?= $pendingIdeas ?></div>
                    <div class="card-label">Góp ý chờ xử lý</div>
                    <a href="manage_ideas.php" class="card-link">Xử lý ngay <span>&#8594;</span></a>
                </div>

            </div>
                        
        </main>
    </div>
</body>
</html>
<?php
// (MỚI) Đóng kết nối CSDL
$conn->close();
?>