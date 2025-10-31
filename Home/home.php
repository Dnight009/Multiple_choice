<?php
// Trang chủ trắc nghiệm
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

// 3. [ĐÃ SỬA] DÙNG LẠI CÂU SQL GỐC CỦA BẠN
// Lấy 12 bộ đề mới nhất (không lọc)
// Việc kiểm tra quyền sẽ do view_quiz_details.php xử lý
$sql = "SELECT T.*, A.username 
        FROM TEN_DE AS T
        JOIN ACCOUNT AS A ON T.IDACC = A.IDACC
        ORDER BY T.ngay_tao DESC 
        LIMIT 12";

$result = $conn->query($sql);
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
            
            <?php
            // 4. BẮT ĐẦU VÒNG LẶP (Giữ nguyên)
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
                echo "<p style='text-align: center; width: 100%;'>Chưa có bộ đề nào được tạo.</p>";
            }
            
            $conn->close();
            ?>
            
        </div> 
    </div> 
    <?php include 'aichat.php'; ?>
    <?php include 'Footer.php'; ?>     
</body>
</html>