<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Vẫn kiểm tra đăng nhập
if (!isset($_SESSION['IDACC'])) {
    header("Location: /TracNghiem/Guest/Login.php");
    exit;
}
require_once __DIR__ . '/../Check/Connect.php'; // Sửa đường dẫn nếu cần

// 2. TRUY VẤN LẤY TẤT CẢ Ý KIẾN
// [SỬA] Thêm cột C.ghi_chu vào
$sql = "SELECT C.*, A.username 
        FROM CONTRIBUTE_IDEAS AS C
        JOIN ACCOUNT AS A ON C.IDACC = A.IDACC
        ORDER BY C.ngay_dang DESC"; 
        
$result = $conn->query($sql);
$ideas = [];
if ($result->num_rows > 0) {
    $ideas = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách ý kiến đóng góp</title>
    
    <style>
        body { 
            font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0;
            display: flex; flex-direction: column; min-height: 100vh;
        }
        .container { 
            max-width: 900px; /* Mở rộng container */
            margin: 20px auto; background: #fff; padding: 25px; 
            border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            flex-grow: 1;
        }
        h1 { text-align: center; color: #333; }
        
        .idea-list-container { 
            margin-top: 30px; 
            border-top: 1px solid #eee; 
        }
        
        /* Dùng CSS Grid để chia 2 cột */
        .idea-card {
            display: grid;
            grid-template-columns: 2fr 1fr; /* Cột 1 (Nội dung) 2 phần, Cột 2 (Phản hồi) 1 phần */
            gap: 20px;
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }
        .idea-content { } /* Cột 1 */
        .idea-response { /* Cột 2 */
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        
        .idea-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .idea-author { font-weight: bold; color: #2d98da; font-size: 1.1em; }
        .idea-date { font-size: 0.9em; color: #777; }
        .idea-body { font-size: 1em; color: #333; line-height: 1.6; white-space: pre-wrap; }
        
        /* Style cho cột Phản hồi */
        .idea-response-title {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
        }
        .idea-status {
            font-weight: bold; padding: 5px 10px; border-radius: 5px;
            display: inline-block; font-size: 0.9em; margin-bottom: 10px;
        }
        /* Style cho 3 trạng thái */
        .status-pending { background: #fff3cd; color: #856404; }
        .status-accepted { background: #e6f7e9; color: #28a745; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        /* Style cho Ghi chú / Lý do */
        .idea-note {
            font-size: 0.95em;
            color: #333;
            font-style: italic;
            line-height: 1.5;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            margin-top: 10px;
            white-space: pre-wrap; /* Giữ định dạng xuống dòng của Admin */
        }
        .no-ideas { text-align: center; color: #777; padding-top: 30px; font-style: italic; }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="container">
        <h1>Danh sách ý kiến đóng góp</h1>
        
        <div class="idea-list-container">
            
            <?php if (count($ideas) > 0): ?>
                <?php foreach ($ideas as $idea): ?>
                    <div class="idea-card">
                        
                        <div class="idea-content">
                            <div class="idea-header">
                                <span class="idea-author">
                                    <?php echo htmlspecialchars($idea['username']); ?>
                                </span>
                                <span class="idea-date">
                                    <?php echo date("d/m/Y H:i", strtotime($idea['ngay_dang'])); ?>
                                </span>
                            </div>
                            <div class="idea-body">
                                <?php echo htmlspecialchars($idea['noi_dung_y_kien']); ?>
                            </div>
                        </div>
                        
                        <div class="idea-response">
                            <span class="idea-response-title">Phản hồi:</span>
                            
                            <?php if ($idea['status'] == 'đã chấp nhận'): ?>
                                <span class="idea-status status-accepted">Đã chấp nhận</span>
                            <?php elseif ($idea['status'] == 'không chấp nhận'): // Sửa theo CSDL của bạn ?>
                                <span class="idea-status status-rejected">Không chấp nhận</span>
                            <?php else: // 'chờ xử lý' ?>
                                <span class="idea-status status-pending">Chờ xử lý</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($idea['ghi_chu'])): ?>
                                <div class="idea-note">
                                    <?php echo htmlspecialchars($idea['ghi_chu']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-ideas">Chưa có ý kiến đóng góp nào.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../Home/Footer.php'; ?>
</body>
</html>