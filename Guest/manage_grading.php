<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    header("Location: /Home/home.php");
    exit;
}
$teacher_id = $_SESSION['IDACC'];
$teacher_username = $_SESSION['username'] ?? 'Giáo viên';

// 2. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php';

// 3. BIẾN LƯU THÔNG BÁO
$message = "";
$message_type = "";
// [SỬA] Lấy flash message (nếu có) từ assign_quizzes.php
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

// 4. XỬ LÝ KHI GV TẠO HẠNG MỤC MỚI
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_category'])) {
    // (Code xử lý tạo hạng mục giữ nguyên)
    $ten_hang_muc = trim($_POST['ten_hang_muc']);
    $quy_tac_tinh = $_POST['quy_tac_tinh'];
    if (!empty($ten_hang_muc) && !empty($quy_tac_tinh)) {
        $stmt_insert = $conn->prepare("INSERT INTO HANG_MUC_DIEM (IDACC_teach, ten_hang_muc, quy_tac_tinh) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iss", $teacher_id, $ten_hang_muc, $quy_tac_tinh);
        if ($stmt_insert->execute()) {
            $message = "Tạo hạng mục điểm thành công!";
            $message_type = "success";
        } else {
            $message = "Lỗi: " . $stmt_insert->error;
            $message_type = "error";
        }
        $stmt_insert->close();
    } else {
        $message = "Vui lòng điền đầy đủ thông tin.";
        $message_type = "error";
    }
}

// 5. LẤY DANH SÁCH CÁC HẠNG MỤC ĐIỂM CỦA GIÁO VIÊN
$categories = [];
$stmt_get = $conn->prepare("SELECT * FROM HANG_MUC_DIEM WHERE IDACC_teach = ? ORDER BY ten_hang_muc ASC");
$stmt_get->bind_param("i", $teacher_id);
$stmt_get->execute();
$result = $stmt_get->get_result();
if ($result->num_rows > 0) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt_get->close();

// 6. [THÊM MỚI] CHUẨN BỊ CÂU LỆNH ĐỂ LẤY CÁC ĐỀ ĐÃ GÁN
// Chuẩn bị 1 lần, dùng nhiều lần trong vòng lặp (hiệu quả)
$stmt_get_quizzes = $conn->prepare("SELECT T.ten_de 
                                    FROM HANG_MUC_DE_THI AS H
                                    JOIN TEN_DE AS T ON H.ID_TD = T.ID_TD
                                    WHERE H.ID_HANG_MUC = ?");

// Mảng "dịch" quy tắc (giữ nguyên)
$quy_tac_map = [
    'trungbinh' => 'Lấy điểm trung bình',
    'caonhat'   => 'Lấy điểm cao nhất',
    'duynhat'   => 'Lấy điểm của 1 bài duy nhất'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sổ điểm</title>
    <link rel="stylesheet" href="../CSS/Admin/AdminHome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .dashboard {
            display: flex; 
            gap: 30px;
        }
        .main-content {
            flex: 2; 
        }
        .sidebar-content {
            flex: 1; 
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 7px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top; /* Căn lề trên cho dễ nhìn */
        }
        th { background-color: #f9f9f9; }

        /* [THÊM MỚI] CSS cho danh sách đề thi trong bảng */
        td ul {
            margin: 0;
            padding-left: 20px;
            font-size: 0.95em;
            color: #555;
        }
        td ul li {
            margin-bottom: 5px;
        }

        /* [SỬA LẠI] CSS cho nút hành động */
        .action-link {
            text-decoration: none; 
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 8px; 
            color: #fff;
            display: inline-block; /* Thêm */
            margin-bottom: 5px; /* Thêm */
        }
        .action-view {
            background-color: #28a745;
        }
        .action-view:hover {
            background-color: #218838;
        }
        .action-edit {
            background-color: #007bff;
        }
        .action-edit:hover {
            background-color: #0069d9;
        }
        
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 7px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .success { background: #e6f7e9; color: #28a745; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Bảng điều khiển</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="/Guest/dashboard.php" style="color:white; text-decoration:none;">Dashboard</a></li>
                <li class="active"><a href="/Guest/manage_grading.php" style="color:white; text-decoration:none;">Quản lý Sổ điểm</a></li>
                <li><a href="/Tracnghiem/list_created.php" style="color:white; text-decoration:none;">Quản Lý Đề Thi</a></li>
                <li><a href="/Guest/List_Class.php" style="color:white; text-decoration:none;">Quản Lý Lớp Học</a></li>
                <hr style="border-color: #1f2a38;">
                <li><a href="/Home/home.php" style="color:white; text-decoration:none;">Về Trang Chủ</a></li>
            </ul>
        </aside>
        
        <main class="dashboard">
            
            <div class="main-content">
                <h1>Quản lý Hạng mục điểm (Sổ điểm)</h1>

                <div class="form-container">
                    <h2>Tạo Hạng mục/Cột điểm mới</h2>
                    
                    <?php if (!empty($message)): ?>
                        <div class="message <?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="manage_grading.php" method="POST">
                        <div class="form-group">
                            <label for="ten_hang_muc">Tên hạng mục (Cột điểm):</label>
                            <input type="text" name="ten_hang_muc" id="ten_hang_muc" placeholder="Ví dụ: Kiểm tra 15 phút (Lý - Lần 1)" required>
                        </div>
                        <div class="form-group">
                            <label for="quy_tac_tinh">Quy tắc tính điểm:</label>
                            <select name="quy_tac_tinh" id="quy_tac_tinh" required>
                                <option value="" disabled selected>-- Chọn quy tắc --</option>
                                <option value="duynhat">Chỉ lấy 1 bài (Gán 1 đề)</option>
                                <option value="caonhat">Lấy điểm cao nhất (Gán nhiều đề)</option>
                                <option value="trungbinh">Lấy điểm trung bình (Gán nhiều đề)</option>
                            </select>
                        </div>
                        <button type="submit" name="create_category" class="btn-submit">Tạo mới</button>
                    </form>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Tên Hạng mục</th>
                            <th>Quy tắc tính điểm</th>
                            <th>Các đề thi đã gán</th> <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cat['ten_hang_muc']); ?></td>
                                    <td><?php echo $quy_tac_map[$cat['quy_tac_tinh']]; ?></td>
                                    
                                    <td>
                                        <?php
                                        // Chạy câu lệnh đã chuẩn bị ở trên
                                        $stmt_get_quizzes->bind_param("i", $cat['ID_HANG_MUC']);
                                        $stmt_get_quizzes->execute();
                                        $result_quizzes = $stmt_get_quizzes->get_result();
                                        
                                        if ($result_quizzes->num_rows > 0) {
                                            echo '<ul>';
                                            while ($quiz = $result_quizzes->fetch_assoc()) {
                                                echo '<li>' . htmlspecialchars($quiz['ten_de']) . '</li>';
                                            }
                                            echo '</ul>';
                                        } else {
                                            echo '<i>Chưa gán đề nào</i>';
                                        }
                                        ?>
                                    </td>
                                    
                                    <td>
                                        <a href="view_gradebook.php?id_hang_muc=<?php echo $cat['ID_HANG_MUC']; ?>" class="action-link action-view">
                                            Coi Sổ Điểm
                                        </a>
                                        <a href="assign_quizzes.php?id_hang_muc=<?php echo $cat['ID_HANG_MUC']; ?>" class="action-link action-edit">
                                            Sửa/Gán Đề
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Bạn chưa tạo hạng mục điểm nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <aside class="sidebar-content">
                <div class="form-container">
                    <h2>Cách hoạt động</h2>
                    <p>Đây là Sổ điểm của bạn. Mỗi "Hạng mục" là một cột điểm (15 phút, 1 tiết, ...).</p>
                    <ol>
                        <li><strong>Tạo Hạng mục:</strong> Dùng form bên trái để tạo cột điểm (ví dụ: "KT 15 phút").</li>
                        <li><strong>Gán Đề thi:</strong> Bấm "Sửa/Gán Đề" ở bảng dưới. Bạn sẽ được chọn các đề thi (`TEN_DE`) bạn đã tạo để gán vào hạng mục này.</li>
                        <li><strong>Coi điểm:</strong> Bấm "Coi Sổ Điểm" để xem điểm của học sinh (sau khi học sinh làm bài).</li>
                    </ol>
                </div>
            </aside>

        </main>
    </div>

    <?php 
    // [THÊM MỚI] Đóng các kết nối CSDL ở cuối file
    $stmt_get_quizzes->close();
    $conn->close(); 
    ?>
</body>
</html>