<?php
// 1. KẾT NỐI VÀ BẢO MẬT
session_start();
require_once '../Check/Connect.php'; // Đảm bảo đường dẫn này đúng với cấu trúc thư mục của bạn

// Kiểm tra quyền Admin (Nếu cần thiết thì mở comment ra)
// if (!isset($_SESSION['user_id']) || $_SESSION['quyen'] != 1) { header("Location: ../index.php"); exit(); }

$message = "";
$message_type = "";

// 2. XỬ LÝ LOGIC (XÓA / KHÔI PHỤC)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $class_id = (int)$_GET['id'];
    $action = $_GET['action'] ?? '';

    if ($action == 'delete') {
        // Soft Delete
        $stmt = $conn->prepare("UPDATE class SET trang_thai = 'đã xóa' WHERE ID_CLASS = ?");
        $stmt->bind_param("i", $class_id);
        if ($stmt->execute()) {
            $message = "Đã chuyển lớp học vào thùng rác.";
            $message_type = "success";
        } else {
            $message = "Lỗi: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    } elseif ($action == 'restore') {
        // Restore
        $stmt = $conn->prepare("UPDATE class SET trang_thai = 'đang hoạt động' WHERE ID_CLASS = ?");
        $stmt->bind_param("i", $class_id);
        if ($stmt->execute()) {
            $message = "Đã khôi phục lớp học.";
            $message_type = "success";
        }
        $stmt->close();
    }
}

// 3. TÌM KIẾM VÀ TRUY VẤN
$search = $_GET['search'] ?? '';
$search_param = "%" . $search . "%";

$sql = "SELECT 
            C.ID_CLASS, 
            C.ten_lop_hoc, 
            C.ngay_tao, 
            C.trang_thai,
            A.username AS gv_username, 
            A.ho_ten AS gv_hoten,
            COUNT(L.ID_LIST) as so_hoc_sinh
        FROM class C
        LEFT JOIN account A ON C.IDACC_teach = A.IDACC
        LEFT JOIN class_list L ON C.ID_CLASS = L.ID_CLASS
        WHERE (C.ten_lop_hoc LIKE ? OR A.ho_ten LIKE ? OR A.username LIKE ?)
        GROUP BY C.ID_CLASS, C.ten_lop_hoc, C.ngay_tao, C.trang_thai, A.username, A.ho_ten
        ORDER BY C.ngay_tao DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
$classes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Lớp học</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* --- 1. RESET & CORE STYLES (GIỐNG ADMINHOME) --- */
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9; /* Nền xám nhạt chủ đạo */
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }
        a { text-decoration: none; }

        /* --- 2. SIDEBAR (MENU TRÁI - MÀU TỐI) --- */
        .sidebar {
            width: 250px;
            background-color: #2c3e50; /* Màu xanh đen đậm */
            color: #ecf0f1;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            background-color: #1a252f;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #34495e;
        }
        .sidebar-header h2 { margin: 0; font-size: 22px; text-transform: uppercase; color: #fff; letter-spacing: 1px; }
        
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-menu li { border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: #bdc3c7; /* Màu chữ xám nhạt */
            font-size: 15px;
            transition: all 0.3s;
        }
        /* Hiệu ứng hover và trạng thái active */
        .sidebar-menu a:hover, .sidebar-menu li.active-item a {
            background-color: #34495e;
            color: #fff;
            border-left: 4px solid #3498db; /* Vạch xanh bên trái */
            padding-left: 16px;
        }

        /* --- 3. MAIN CONTENT (KHU VỰC BÊN PHẢI) --- */
        .dashboard {
            margin-left: 250px; /* Chừa chỗ cho sidebar */
            width: calc(100% - 250px);
            padding: 20px;
        }

        /* Header trắng phía trên */
        .dashboard-header {
            background: #fff;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-header h1 { margin: 0; font-size: 24px; color: #333; }

        /* Form tìm kiếm */
        .search-box { display: flex; gap: 10px; }
        .search-box input {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
            font-size: 14px;
            width: 250px;
        }
        .search-box button {
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        .search-box button:hover { background-color: #2980b9; }

        /* --- 4. TABLE STYLES (PHONG CÁCH PHẲNG) --- */
        .table-container {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden; /* Bo góc cho table */
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        .data-table th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
        }
        .data-table tr:hover { background-color: #fbfbfb; }
        
        /* Text styles trong bảng */
        .teacher-name { font-weight: 600; color: #2c3e50; display: block; }
        .teacher-username { font-size: 12px; color: #95a5a6; }
        .student-count { 
            background: #eef2f7; color: #2c3e50; 
            padding: 4px 10px; border-radius: 12px; 
            font-weight: bold; font-size: 13px;
        }

        /* Badges trạng thái */
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .status-active { background-color: #d5f5e3; color: #2ecc71; }
        .status-deleted { background-color: #fadbd8; color: #e74c3c; }

        /* Nút hành động */
        .btn-action {
            font-size: 13px; font-weight: 600; 
            padding: 5px 10px; border-radius: 3px; transition: 0.2s;
        }
        .btn-del { color: #e74c3c; background: #fdf0ed; }
        .btn-del:hover { background: #e74c3c; color: white; }
        .btn-restore { color: #27ae60; background: #edf7f0; }
        .btn-restore:hover { background: #27ae60; color: white; }

        /* Thông báo Alert */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; color: #fff; font-weight: 500; }
        .alert.success { background-color: #2ecc71; }
        .alert.error { background-color: #e74c3c; }

    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Master Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="Adminhome.php">Dashboard</a></li>
            
            <li><a href="manage_ideas.php">Duyệt Góp Ý</a></li>
            <li><a href="manage_users.php">Quản lý User</a></li>
            
            <li class="active-item"><a href="manage_classes.php">Quản lý Lớp học</a></li>
            
            <li><a href="manager_de.php">Quản lý Đề thi</a></li>
        </ul>
    </aside>

    <main class="dashboard">
        
        <div class="dashboard-header">
            <h1>Quản lý Lớp học</h1>
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Tìm tên lớp hoặc giáo viên..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Tìm kiếm</button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="25%">Tên Lớp</th>
                        <th width="25%">Giáo viên CN</th>
                        <th width="15%">Sĩ số</th>
                        <th width="15%">Ngày tạo</th>
                        <th width="15%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($classes) > 0): ?>
                        <?php foreach ($classes as $cls): ?>
                            <tr>
                                <td>#<?php echo $cls['ID_CLASS']; ?></td>
                                <td>
                                    <strong style="color:#34495e; font-size:1.1em;">
                                        <?php echo htmlspecialchars($cls['ten_lop_hoc']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="teacher-name">
                                        <?php echo htmlspecialchars($cls['gv_hoten'] ?? 'Chưa cập nhật'); ?>
                                    </span>
                                    <span class="teacher-username">@<?php echo htmlspecialchars($cls['gv_username']); ?></span>
                                </td>
                                <td>
                                    <span class="student-count"><?php echo $cls['so_hoc_sinh']; ?> HS</span>
                                </td>
                                <td><?php echo date("d/m/Y", strtotime($cls['ngay_tao'])); ?></td>
                                <td>
                                    <?php if ($cls['trang_thai'] == 'đang hoạt động'): ?>
                                        <a href="manage_classes.php?action=delete&id=<?php echo $cls['ID_CLASS']; ?>&search=<?php echo urlencode($search); ?>" 
                                           class="btn-action btn-del"
                                           onclick="return confirm('Xóa lớp này?');">
                                           Xóa
                                        </a>
                                    <?php else: ?>
                                        <span class="status-badge status-deleted" style="margin-right:5px;">Đã xóa</span>
                                        <a href="manage_classes.php?action=restore&id=<?php echo $cls['ID_CLASS']; ?>&search=<?php echo urlencode($search); ?>" 
                                           class="btn-action btn-restore"
                                           onclick="return confirm('Khôi phục lớp này?');">
                                           Khôi phục
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #7f8c8d;">
                                Không tìm thấy lớp học nào.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>
<?php $conn->close(); ?>