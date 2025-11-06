<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc phải đăng nhập mới xem được trang này
if (!isset($_SESSION['IDACC'])) {
    header("Location: /TracNghiem/Guest/Login.php");
    exit;
}
$student_id = $_SESSION['IDACC'];

// 2. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php'; // Sửa đường dẫn nếu cần

// 3. TRUY VẤN LỊCH SỬ LÀM BÀI CỦA CHÍNH HỌC SINH NÀY
// Dùng JOIN để lấy Tên Đề từ bảng TEN_DE
$sql = "SELECT 
            T.ten_de,
            K.diem,
            K.thoi_gian_nop_bai,
            K.tong_thoi_gian_lam_bai
        FROM 
            ketqua_lambai AS K
        JOIN 
            TEN_DE AS T ON K.ID_TD = T.ID_TD
        WHERE 
            K.IDACC = ?
        ORDER BY 
            K.thoi_gian_nop_bai DESC"; // Xếp bài mới nhất lên đầu

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
if ($result->num_rows > 0) {
    $history = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử làm bài</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f0f2f5; 
            margin: 0; 
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container { 
            max-width: 900px; 
            margin: 20px auto;
            background: #fff; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            flex-grow: 1;
        }
        h1 { 
            text-align: center; 
            color: #333; 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 15px;
        }
        
        /* CSS cho bảng lịch sử */
        .history-table {
            width: 100%;
            border-collapse: collapse; /* Gộp viền */
            margin-top: 20px;
        }
        .history-table th, .history-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .history-table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .history-table tr:nth-child(even) {
            background-color: #fdfdfd;
        }
        .history-table tr:hover {
            background-color: #f1f1f1;
        }
        .no-history {
            text-align: center;
            color: #777;
            font-style: italic;
            margin-top: 30px;
        }
        .score-pass {
            font-weight: bold;
            color: #28a745; /* Màu xanh lá */
        }
        .score-fail {
            font-weight: bold;
            color: #e74c3c; /* Màu đỏ */
        }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="container">
        <h1>Lịch sử làm bài</h1>
        
        <?php if (count($history) > 0): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên bộ đề</th>
                        <th>Điểm (Thang 10)</th>
                        <th>Thời gian làm bài</th>
                        <th>Ngày nộp bài</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stt = 1;
                    foreach ($history as $row): 
                        // Format điểm (2 số thập phân)
                        $diem_formatted = number_format($row['diem'], 2);
                        
                        // Format thời gian làm bài (từ giây sang H:i:s)
                        $thoi_gian_lam = gmdate("H:i:s", $row['tong_thoi_gian_lam_bai']);
                        
                        // Format ngày nộp
                        $ngay_nop = date("d/m/Y H:i", strtotime($row['thoi_gian_nop_bai']));
                        
                        // Đặt class màu cho điểm (ví dụ: >= 5 là Đạt)
                        $diem_class = ($row['diem'] >= 5) ? 'score-pass' : 'score-fail';
                    ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo htmlspecialchars($row['ten_de']); ?></td>
                            <td class="<?php echo $diem_class; ?>">
                                <?php echo $diem_formatted; ?>
                            </td>
                            <td><?php echo $thoi_gian_lam; ?></td>
                            <td><?php echo $ngay_nop; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-history">Bạn chưa hoàn thành bài thi nào.</p>
        <?php endif; ?>

    </div>

    <?php include __DIR__ . '/../Home/aichat.php'; ?>
    <?php include __DIR__ . '/../Home/Footer.php'; ?>

</body>
</html>