<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. BAO GỒM CÁC FILE CẦN THIẾT
include __DIR__ . '/../Home/navbar.php'; 
require_once __DIR__ . '/../Check/Connect.php';

// 2. THÊM MẢNG "DỊCH" (Giống file home.php)
$trinh_do_map = [
    'de'         => 'Dễ',
    'binhthuong' => 'Bình thường',
    'kho'        => 'Khó',
    'nangcao'    => 'Nâng cao',
    'tonghop'    => 'Tổng Hợp'
];

// 3. KIỂM TRA TỪ KHÓA
if (isset($_GET['q']) && !empty($_GET['q'])) {
    
    $search_query = $_GET['q']; 
    $search_term = "%" . $search_query . "%"; 

    // 4. SỬA LẠI CÂU SQL ĐỂ GIỐNG HOME.PHP (thêm JOIN)
    $sql = "SELECT T.*, A.username 
            FROM TEN_DE AS T
            JOIN ACCOUNT AS A ON T.IDACC = A.IDACC
            WHERE T.ten_de LIKE ?"; // Tìm kiếm theo tên đề
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    $result = false;
    $search_query = "";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả tìm kiếm</title>
    
    <link rel="stylesheet" href="../CSS/Home/home.css"> 
    
    </head>
<body>
    <div class="main-content">
        
        <?php if ($result): ?>
            <h1 style="text-align: center; margin-top: 20px; color: #333;">
                Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($search_query); ?>"
            </h1>
            
            <?php if ($result->num_rows > 0): ?>
                <p style="text-align: center; font-size: 1.1em; color: #555;">
                    Tìm thấy <?php echo $result->num_rows; ?> kết quả.
                </p>
                
                <div class="card-grid">
                
                <?php 
                // 7. SỬA LẠI VÒNG LẶP (Dùng HTML của home.php)
                while ($de_thi = $result->fetch_assoc()): 
                    // Xử lý dữ liệu (y hệt home.php)
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
                <?php endwhile; ?>

                </div> <?php else: ?>
                <p style='text-align: center; width: 100%; margin-top: 30px;'>
                    Không tìm thấy bộ đề nào khớp với từ khóa của bạn.
                </p>
            <?php endif; ?>

        <?php else: ?>
            <h1 style="text-align: center; margin-top: 20px;">
                Vui lòng nhập từ khóa để tìm kiếm
            </h1>
        <?php endif; ?>

    </div> <?php 
    // Đóng kết nối
    if ($result) {
        $stmt->close();
    }
    $conn->close();
    
    include __DIR__ . '/../Home/Footer.php'; 
    ?>
</body>
</html>