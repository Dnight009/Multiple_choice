<?php
// Trang danh sách đề theo lớp
include 'navbar.php'; 

// 1. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php'; 

// 2. CHUẨN BỊ DỮ LIỆU "DỊCH"
$trinh_do_map = [
    'de'         => 'Dễ',
    'binhthuong' => 'Bình thường',
    'kho'        => 'Khó',
    'nangcao'    => 'Nâng cao',
    'tonghop'    => 'Tổng Hợp'
];

// ******************************************************
// --- THAY ĐỔI BẮT ĐẦU TỪ ĐÂY ---
// ******************************************************

// 3. LẤY SỐ LỚP TỪ URL VÀ KIỂM TRA
if (!isset($_GET['lop']) || !filter_var($_GET['lop'], FILTER_VALIDATE_INT)) {
    // Nếu không có ?lop=... hoặc nó không phải là số, thì báo lỗi
    echo "<h1 style='text-align: center; margin-top: 20px;'>Lớp học không hợp lệ.</h1>";
    include 'Footer.php';
    exit; // Dừng chạy
}
$lop_hoc_can_tim = (int)$_GET['lop']; // Lấy số lớp (ví dụ: 10)

// 4. VIẾT CÂU TRUY VẤN SQL ĐÃ SỬA ĐỔI
// Thay vì LIMIT 12, chúng ta dùng WHERE T.lop_hoc = ?
// Dùng prepared statement để chống SQL Injection
$sql = "SELECT T.*, A.username 
        FROM TEN_DE AS T
        JOIN ACCOUNT AS A ON T.IDACC = A.IDACC
        WHERE T.lop_hoc = ? 
        ORDER BY T.ngay_tao DESC";

// Chuẩn bị và thực thi
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lop_hoc_can_tim); // "i" = integer
$stmt->execute();
$result = $stmt->get_result();

// ******************************************************
// --- KẾT THÚC THAY ĐỔI ---
// ******************************************************
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Các đề trắc nghiệm Lớp <?php echo $lop_hoc_can_tim; ?></title>
    <link rel="stylesheet" href="../CSS/Home/home.css">
</head>
<body>
    <div class="main-content">
        
        <h1 style="text-align: center; margin-top: 20px; color: #333;">
            Các bộ đề trắc nghiệm - Lớp <?php echo $lop_hoc_can_tim; ?>
        </h1>

        <div class="card-grid">
            
            <?php
            // Vòng lặp này giữ nguyên y hệt file home.php
            if ($result->num_rows > 0) {
                while ($de_thi = $result->fetch_assoc()) {
                    
                    $trinh_do_raw = $de_thi['trinh_do'];
                    $trinh_do_text = $trinh_do_map[$trinh_do_raw] ?? $trinh_do_raw;
                    $lop_hoc_text = "Lớp " . htmlspecialchars($de_thi['lop_hoc']);
                    $ngay_tao_formatted = date("d/m/Y", strtotime($de_thi['ngay_tao']));
                    $link_to_view = "../Tracnghiem/view_quiz_details.php?id_de=" . $de_thi['ID_TD'];
                    ?>
                    
                    <div class="card">
                        <div class="card-badge yellow"><?php echo htmlspecialchars($de_thi['lop_hoc']); ?></div>
                        <div class="card-title yellow"><?php echo htmlspecialchars($de_thi['ten_de']); ?></div>
                        <div class="card-desc">
                            Bài trắc nghiệm dành cho <strong><?php echo $lop_hoc_text; ?></strong>.
                            Mức độ: <strong><?php echo htmlspecialchars($trinh_do_text); ?></strong>.
                        </div>
                        <div class="card-stars">★★★★★</div>
                        <div class="card-list">
                            + Tác giả: <?php echo htmlspecialchars($de_thi['username']); ?><br>
                            + Ngày tạo: <?php echo $ngay_tao_formatted; ?><br>
                        </div>
                        <div class="card-footer">
                            <a href="<?php echo $link_to_view; ?>" class="card-link">Làm bài ngay »</a>
                        </div>
                    </div>
                    <?php
                } 
            } else {
                // Sửa lại thông báo lỗi nếu không tìm thấy
                echo "<p style='text-align: center; width: 100%;'>Chưa có bộ đề nào được tạo cho Lớp $lop_hoc_can_tim.</p>";
            }
            
            // 5. ĐÓNG KẾT NỐI
            $stmt->close(); // Đóng statement
            $conn->close(); // Đóng kết nối
            ?>
            
        </div> 
    </div> 
    <?php include 'aichat.php'; ?>
    <?php include 'Footer.php'; ?>     
</body>
</html>