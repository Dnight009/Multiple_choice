<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. BẢO MẬT: BẮT BUỘC PHẢI LÀ GIÁO VIÊN (quyen = 3) ĐỂ VÀO TRANG NÀY
// (Sử dụng đúng biến session bạn đã dùng trong navbar)
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    // Nếu không, đuổi về trang chủ
    header("Location: /TracNghiem/Home/home.php");
    exit;
}

// Lấy ID của giáo viên đang đăng nhập
$teacher_id = $_SESSION['IDACC'];

// 3. KẾT NỐI DATABASE
// (Sử dụng đường dẫn file Connect.php của bạn)
require_once __DIR__ . '/../Check/Connect.php'; 

// 4. CHUẨN BỊ DỮ LIỆU "DỊCH" (Giống home.php)
$trinh_do_map = [
    'de'         => 'Dễ',
    'binhthuong' => 'Bình thường',
    'kho'        => 'Khó',
    'nangcao'    => 'Nâng cao',
    'tonghop'    => 'Tổng Hợp'
];

// 5. VIẾT CÂU TRUY VẤN SQL (ĐÃ THAY ĐỔI)
// Chỉ lấy các đề có IDACC bằng với ID của giáo viên
$sql = "SELECT T.*, A.username 
        FROM TEN_DE AS T
        JOIN ACCOUNT AS A ON T.IDACC = A.IDACC
        WHERE T.IDACC = ? 
        ORDER BY T.ngay_tao DESC";

// Dùng prepared statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id); // "i" = integer
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Các bộ đề đã tạo</title>
    <link rel="stylesheet" href="../CSS/Home/home.css">
</head>
<body>

    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="main-content">
        
        <h1 style="text-align: center; margin-top: 20px; color: #333;">
            Các bộ đề bạn đã tạo
        </h1>

        <div class="card-grid">
            
            <?php
            // 6. VÒNG LẶP (Giống hệt home.php)
            if ($result->num_rows > 0) {
                while ($de_thi = $result->fetch_assoc()) {
                    
                    // Lấy và "dịch" dữ liệu
                    $trinh_do_raw = $de_thi['trinh_do'];
                    $trinh_do_text = $trinh_do_map[$trinh_do_raw] ?? $trinh_do_raw;
                    $lop_hoc_text = "Lớp " . htmlspecialchars($de_thi['lop_hoc']);
                    $ngay_tao_formatted = date("d/m/Y", strtotime($de_thi['ngay_tao']));

                    // Sửa đường dẫn cho link (vì file này ở Tracnghiem/)
                    // Chỉ cần gọi thẳng tên file view_quiz_details.php
                    $link_to_view = "view_quiz_details.php?id_de=" . $de_thi['ID_TD'];
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
                            <a href="<?php echo $link_to_view; ?>" class="card-link">Xem chi tiết »</a>
                        </div>
                    </div>
                    <?php
                } // Kết thúc vòng lặp while
            } else {
                // Nếu không có bộ đề nào
                echo "<p style='text-align: center; width: 100%;'>Bạn chưa tạo bộ đề nào.</p>";
            }
            
            // 7. ĐÓNG KẾT NỐI
            $stmt->close();
            $conn->close();
            ?>
            
        </div> </div> <?php include __DIR__ . '/../Home/aichat.php'; ?>
    <?php include __DIR__ . '/../Home/Footer.php'; ?>     
</body>
</html>