<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 2. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php'; // Sửa đường dẫn nếu cần

// 3. TRUY VẤN LẤY TẤT CẢ Ý KIẾN
// Dùng JOIN để lấy tên người dùng (username) từ bảng ACCOUNT
$sql = "SELECT C.*, A.username 
        FROM CONTRIBUTE_IDEAS AS C
        JOIN ACCOUNT AS A ON C.IDACC = A.IDACC
        ORDER BY C.ngay_dang DESC"; // Sắp xếp mới nhất lên đầu
        
$result = $conn->query($sql);
$ideas = [];
if ($result->num_rows > 0) {
    // Lấy tất cả kết quả vào một mảng
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
            font-family: Arial, sans-serif; 
            background: #f0f2f5; 
            margin: 0; 
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container { 
            max-width: 800px; 
            margin: 20px auto;
            background: #fff; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            flex-grow: 1;
        }
        h1 { text-align: center; color: #333; }
        
        /* CSS mới cho danh sách ý kiến */
        .idea-list-container {
            margin-top: 30px;
            border-top: 1px solid #eee;
        }
        .idea-card {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }
        .idea-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .idea-author {
            font-weight: bold;
            color: #2d98da; /* Màu xanh dương */
            font-size: 1.1em;
        }
        .idea-date {
            font-size: 0.9em;
            color: #777; /* Màu xám */
        }
        .idea-body {
            font-size: 1em;
            color: #333;
            line-height: 1.6;
            margin-bottom: 15px;
            white-space: pre-wrap; /* Giữ các lần xuống dòng của người dùng */
        }
        .idea-status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            font-size: 0.9em;
        }
        /* Style cho "Chờ xử lý" (màu cam) */
        .status-pending { 
            background: #fff3cd;
            color: #856404;
        }
        /* Style cho "Đã xử lý" (màu xanh lá) */
        .status-processed { 
            background: #e6f7e9;
            color: #28a745;
        }
        .no-ideas {
            text-align: center;
            color: #777;
            padding-top: 30px;
            font-style: italic;
        }
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
                        <div class="idea-header">
                            <span class="idea-author">
                                <?php echo htmlspecialchars($idea['username']); ?>
                            </span>
                            <span class="idea-date">
                                <?php echo date("H:i - d/m/Y", strtotime($idea['ngay_dang'])); ?>
                            </span>
                        </div>
                        
                        <div class="idea-body">
                            <?php echo htmlspecialchars($idea['noi_dung_y_kien']); ?>
                        </div>
                        
                        <?php if ($idea['status'] == 'đã xử lý'): ?>
                            <span class="idea-status status-processed">Đã xử lý</span>
                        <?php else: ?>
                            <span class="idea-status status-pending">Chờ xử lý</span>
                        <?php endif; ?>
                        
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