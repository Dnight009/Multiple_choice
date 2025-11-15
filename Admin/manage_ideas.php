<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT ADMIN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Chỉ Admin (quyen = 1) mới được vào
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '1') {
    die("Bạn không có quyền truy cập trang này.");
}

// 2. KẾT NỐI CSDL
require_once __DIR__ . '/../Check/Connect.php'; // Sửa đường dẫn nếu cần

$message = ""; $message_type = "";

// 3. XỬ LÝ KHI ADMIN PHẢN HỒI (GỬI FORM)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idea_id'])) {
    $idea_id = (int)$_POST['idea_id'];
    $new_status = $_POST['status']; // 'đã chấp nhận' hoặc 'không chấp nhận'
    $ghi_chu = trim($_POST['ghi_chu']);

    // Kiểm tra xem có phải là 2 giá trị hợp lệ không
    if (in_array($new_status, ['đã chấp nhận', 'không chấp nhận']) && !empty($ghi_chu)) {
        
        $stmt_update = $conn->prepare("UPDATE CONTRIBUTE_IDEAS SET status = ?, ghi_chu = ? WHERE ID_IDEAS = ?");
        $stmt_update->bind_param("ssi", $new_status, $ghi_chu, $idea_id);
        
        if ($stmt_update->execute()) {
            $message = "Đã cập nhật trạng thái thành công!";
            $message_type = "success";
        } else {
            $message = "Lỗi khi cập nhật: " . $stmt_update->error;
            $message_type = "error";
        }
        $stmt_update->close();
        
    } else {
        $message = "Vui lòng chọn trạng thái và nhập ghi chú lý do.";
        $message_type = "error";
    }
}

// 4. LẤY TẤT CẢ GÓP Ý (Ưu tiên 'chờ xử lý' lên đầu)
$sql_get_ideas = "SELECT C.*, A.username 
                  FROM CONTRIBUTE_IDEAS AS C
                  JOIN ACCOUNT AS A ON C.IDACC = A.IDACC
                  ORDER BY FIELD(C.status, 'chờ xử lý') DESC, C.ngay_dang DESC";
                  
$result_ideas = $conn->query($sql_get_ideas);
$ideas = $result_ideas->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Duyệt Góp Ý</title>
    <link rel="stylesheet" href="../CSS/Admin/AdminHome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .dashboard { flex-direction: column; } 
        .form-container, .idea-item {
            background: #fff; padding: 20px; border-radius: 7px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px;
        }
        .idea-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .idea-author { font-weight: bold; color: #2d98da; }
        .idea-date { color: #777; }
        .idea-body { margin: 15px 0; line-height: 1.6; }
        
        /* CSS cho 3 trạng thái */
        .idea-status { font-weight: bold; padding: 5px 10px; border-radius: 5px; display: inline-block; font-size: 0.9em; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-accepted { background: #e6f7e9; color: #28a745; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .admin-form textarea { width: 100%; box-sizing: border-box; min-height: 60px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-top: 10px; }
        .admin-form .actions { display: flex; gap: 10px; margin-top: 10px; align-items: center; }
        .btn-submit { background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-reject { background: #e74c3c; }
        
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .success { background: #e6f7e9; color: #28a745; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Master Admin</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="Adminhome.php" style="color:white; text-decoration:none;">Dashboard</a></li>
                <li class="active">Duyệt Góp Ý</li>
            </ul>
        </aside>

        <main class="dashboard">
            <h1>Quản lý Góp Ý / Yêu cầu</h1>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="idea-list">
                <?php if(empty($ideas)): ?>
                    <p>Không có góp ý nào.</p>
                <?php else: ?>
                    <?php foreach ($ideas as $idea): ?>
                        <div class="idea-item">
                            <div class="idea-header">
                                <span class="idea-author"><?php echo htmlspecialchars($idea['username']); ?> (ID: <?php echo $idea['IDACC']; ?>)</span>
                                <span class="idea-date"><?php echo date("d/m/Y H:i", strtotime($idea['ngay_dang'])); ?></span>
                            </div>
                            <div class="idea-body">
                                <?php echo htmlspecialchars($idea['noi_dung_y_kien']); ?>
                            </div>
                            
                            <?php if ($idea['status'] == 'chờ xử lý'): ?>
                                <form class="admin-form" action="manage_ideas.php" method="POST">
                                    <input type="hidden" name="idea_id" value="<?php echo $idea['ID_IDEAS']; ?>">
                                    <label for="ghi_chu_<?php echo $idea['ID_IDEAS']; ?>">Ghi chú (Lý do):</label>
                                    <textarea name="ghi_chu" id="ghi_chu_<?php echo $idea['ID_IDEAS']; ?>" required></textarea>
                                    <div class="actions">
                                        <button type="submit" name="status" value="đã chấp nhận" class="btn-submit">Chấp nhận</button>
                                        <button type="submit" name="status" value="không chấp nhận" class="btn-submit btn-reject">Từ chối</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <?php if ($idea['status'] == 'đã chấp nhận'): ?>
                                    <span class="idea-status status-accepted">Đã chấp nhận</span>
                                <?php else: ?>
                                    <span class="idea-status status-rejected">Không chấp nhận</span>
                                <?php endif; ?>
                                <p style="margin: 10px 0 0 0; font-style: italic;">
                                    <strong>Ghi chú:</strong> <?php echo htmlspecialchars($idea['ghi_chu']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>