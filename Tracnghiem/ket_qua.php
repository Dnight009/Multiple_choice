<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['IDACC'])) {
    header("Location: /TracNghiem/Guest/Login.php");
    exit;
}
require_once __DIR__ . '/../Check/Connect.php';

$hoc_sinh_id = $_SESSION['IDACC'];
$de_thi_id = 0;
$diem = 0;
$so_cau_dung = 0;
$tong_so_cau = 0;
$ten_de = "";

// 2. XỬ LÝ KHI HỌC SINH NỘP BÀI
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_de_thi'])) {
    
    $de_thi_id = (int)$_POST['id_de_thi'];
    
    // [SỬA 1] Lấy thời gian bắt đầu và tính toán tổng thời gian
    $start_time = (int)$_POST['start_time']; // Lấy từ input ẩn
    $end_time_unix = time(); // Thời gian nộp (tính bằng giây)
    
    // Tính tổng thời gian làm bài (bằng giây)
    $tong_thoi_gian_lam_bai = $end_time_unix - $start_time;

    $thoi_gian_hien_thi = gmdate("H:i:s", $tong_thoi_gian_lam_bai);
    // Lấy mảng câu trả lời của học sinh
    $student_answers = $_POST['answers'] ?? [];

    // Lấy đáp án đúng từ CSDL
    $stmt_answers = $conn->prepare("SELECT ID_CH, cau_tra_loi_dung FROM CAU_HOI WHERE ID_TD = ?");
    $stmt_answers->bind_param("i", $de_thi_id);
    $stmt_answers->execute();
    $result_answers = $stmt_answers->get_result();
    
    $correct_answers = [];
    while ($row = $result_answers->fetch_assoc()) {
        $correct_answers[$row['ID_CH']] = $row['cau_tra_loi_dung'];
    }
    $stmt_answers->close();
    
    $tong_so_cau = count($correct_answers);
    $so_cau_dung = 0;

    // 3. SO SÁNH ĐÁP ÁN (Giữ nguyên)
    if ($tong_so_cau > 0) {
        foreach ($correct_answers as $id_cau_hoi => $dap_an_dung) {
            if (isset($student_answers[$id_cau_hoi])) {
                if ((string)$student_answers[$id_cau_hoi] === (string)$dap_an_dung) {
                    $so_cau_dung++;
                }
            }
        }
        $diem = round(($so_cau_dung / $tong_so_cau) * 10, 2);
    }

    // 4. LƯU KẾT QUẢ VÀO BẢNG KETQUA_LAMBAI
    
    // [SỬA 2] Chuyển thời gian nộp sang dạng DATETIME để lưu
    $thoi_gian_nop_sql = date('Y-m-d H:i:s', $end_time_unix);
    
    // [SỬA 3] Cập nhật câu lệnh INSERT
    // (Đổi 'thoi_gian_nop' -> 'thoi_gian_nop_bai')
    // (Thêm 'tong_thoi_gian_lam_bai')
    $stmt_save = $conn->prepare(
        "INSERT INTO ketqua_lambai (IDACC, ID_TD, thoi_gian_nop_bai, diem, tong_thoi_gian_lam_bai) 
         VALUES (?, ?, ?, ?, ?)"
    );
    // [SỬA 4] Cập nhật bind_param (thêm "i" cho $tong_thoi_gian_lam_bai)
    $stmt_save->bind_param("iissi", $hoc_sinh_id, $de_thi_id, $thoi_gian_nop_sql, $diem, $tong_thoi_gian_lam_bai);
    
    $stmt_save->execute();
    $stmt_save->close();

    // Lấy tên đề để hiển thị (Giữ nguyên)
    $stmt_de = $conn->prepare("SELECT ten_de FROM TEN_DE WHERE ID_TD = ?");
    $stmt_de->bind_param("i", $de_thi_id);
    $stmt_de->execute();
    $ten_de = $stmt_de->get_result()->fetch_assoc()['ten_de'];
    $stmt_de->close();
    
    $conn->close();

} else {
    die("Không có dữ liệu bài làm.");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả bài làm</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; flex-direction: column; min-height: 100vh; margin: 0; }
        .container { max-width: 700px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); flex-grow: 1; text-align: center; }
        .score { font-size: 80px; font-weight: bold; color: #28a745; margin: 20px 0; }
        .details { font-size: 1.2em; color: #333; }
        .btn-back { display: inline-block; margin-top: 30px; background: #2d98da; color: white; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../Home/navbar.php'; ?>
    
    <div class="container">
        <h1>Kết quả bài làm</h1>
        <h2><?php echo htmlspecialchars($ten_de); ?></h2>
        
        <div class="details">
            Bạn đã trả lời đúng:
            <strong><?php echo $so_cau_dung; ?> / <?php echo $tong_so_cau; ?> câu</strong>
            
            <br> Thời gian hoàn thành: <strong><?php echo $thoi_gian_hien_thi; ?></strong>
        </div>
        
        <div class="score"><?php echo number_format($diem, 2); ?></div>
        
        <a href="/TracNghiem/Home/home.php" class="btn-back">Quay về trang chủ</a>
    </div>

    <?php include __DIR__ . '/../Home/Footer.php'; ?>
</body>
</html>