<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. BẢO MẬT: BẮT BUỘC PHẢI LÀ GIÁO VIÊN
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    header("Location: /TracNghiem/Home/home.php");
    exit;
}
$teacher_id = $_SESSION['IDACC'];

// 3. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php'; 

// 4. VIẾT CÂU TRUY VẤN SQL (ĐÃ CẬP NHẬT)
// THÊM 'ORDER BY C.trang_thai ASC'
// -> Để xếp "đang hoạt động" (vần 'd') lên trước "đã xóa" (vần 'd')
//    (Nếu ENUM là ('đang hoạt động', 'đã xóa'), nó sẽ tự xếp đúng)
//    Thêm 'C.ngay_tao DESC' để xếp mới nhất lên đầu trong mỗi nhóm
$sql = "SELECT C.*, A.username 
        FROM CLASS AS C
        JOIN ACCOUNT AS A ON C.IDACC_teach = A.IDACC
        WHERE C.IDACC_teach = ? 
        ORDER BY C.trang_thai ASC, C.ngay_tao DESC"; // <-- THAY ĐỔI QUAN TRỌNG

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Các lớp học đã tạo</title>
    <link rel="stylesheet" href="../CSS/Home/home.css">
    <style>
        /* Làm mờ các lớp "đã xóa" */
        .card.deleted {
            opacity: 0.6;
            background-color: #f9f9f9;
            border: 1px dashed #aaa;
        }
        .card.deleted .card-title {
            color: #777;
        }

        /* --- THÊM CSS CHO NÚT XÓA --- */
        /* Sửa lại footer để chứa 2 nút */
        .card-footer {
            display: flex;
            justify-content: space-between; /* Đẩy 2 nút ra 2 bên */
            align-items: center;
        }
        /* Style cho nút "Xóa lớp" */
        .card-link-delete {
            font-size: 14px;
            color: #e74c3c; /* Màu đỏ */
            text-decoration: none;
            font-weight: bold;
            padding: 5px;
        }
        .card-link-delete:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="main-content">
        
        <h1 style="text-align: center; margin-top: 20px; color: #333;">
            Danh sách lớp học của bạn
        </h1>

        <div class="card-grid">
            
            <?php
            // 5. BẮT ĐẦU VÒNG LẶP (ĐÃ CẬP NHẬT HTML BÊN TRONG)
            if ($result->num_rows > 0) {
                while ($class = $result->fetch_assoc()) {
                    
                    $ngay_tao_formatted = date("d/m/Y", strtotime($class['ngay_tao']));
                    $link_to_view = "manage_class.php?class_id=" . $class['ID_CLASS'];
                    
                    // Gán class 'deleted' nếu trạng thái là "đã xóa"
                    $card_class = ($class['trang_thai'] == 'đã xóa') ? 'card deleted' : 'card';
                    ?>
                    
                    <div class="<?php echo $card_class; ?>">
                        
                        <div class="card-badge blue">
                            <?php echo htmlspecialchars($class['trang_thai']); ?>
                        </div>
                        
                        <div class="card-title blue">
                            <?php echo htmlspecialchars($class['ten_lop_hoc']); ?>
                        </div>
                        
                        <div class="card-desc">
                            ID Lớp: <?php echo $class['ID_CLASS']; ?>
                        </div>
                        
                        <div class="card-list">
                            + Tác giả: <?php echo htmlspecialchars($class['username']); ?><br>
                            + Ngày tạo: <?php echo $ngay_tao_formatted; ?><br>
                        </div>
                        
                        <div class="card-footer">
                            <a href="<?php echo $link_to_view; ?>" class="card-link">Quản lý lớp »</a>
                            
                            <?php
                            // Chỉ hiển thị nút "Xóa" nếu lớp đang hoạt động
                            if ($class['trang_thai'] == 'đang hoạt động'):
                            ?>
                                <a href="delete_class.php?class_id=<?php echo $class['ID_CLASS']; ?>" 
                                   class="card-link-delete" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa lớp học này không?');">
                                    Xóa lớp
                                </a>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                    
                    <?php
                } 
            } else {
                echo "<p style='text-align: center; width: 100%;'>Bạn chưa tạo lớp học nào.</p>";
            }
            
            $stmt->close();
            $conn->close();
            ?>
            
        </div> 
    </div> 

    <?php include __DIR__ . '/../Home/aichat.php'; ?>
    <?php include __DIR__ . '/../Home/Footer.php'; ?>     
</body>
</html>