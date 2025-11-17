<?php
// --- KẾT NỐI DATABASE ---
require_once '../Check/Connect.php'; 

$message = ""; // Biến để lưu thông báo

// --- XỬ LÝ XÓA ĐỀ THI ---
if (isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    
    // Dùng prepared statement để bảo mật
    $stmt = $conn->prepare("DELETE FROM ten_de WHERE ID_TD = ?");
    $stmt->bind_param("i", $id_to_delete);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Xóa đề thi thành công! (Tất cả câu hỏi liên quan cũng đã được xóa).</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi khi xóa: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// --- LẤY DỮ LIỆU ĐỂ HIỂN THỊ ---
// Lấy danh sách tất cả đề thi và thông tin người tạo
// Sử dụng LEFT JOIN để vẫn hiển thị đề thi ngay cả khi tài khoản người tạo đã bị xóa
$sql = "SELECT 
            td.ID_TD, 
            td.ten_de, 
            td.trinh_do, 
            td.lop_hoc, 
            td.ngay_tao, 
            ac.ho_ten, 
            ac.username 
        FROM ten_de AS td
        LEFT JOIN account AS ac ON td.IDACC = ac.IDACC
        ORDER BY td.ngay_tao DESC"; // Sắp xếp theo ngày tạo mới nhất

$all_tests = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đề Thi</title>
    <link rel="stylesheet" href="../CSS/Admin/AdminHome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* CSS cho bảng (tương tự manage_users.php) */
        .dashboard section {
            background: #fff;
            border-radius: 7px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 25px;
            margin-top: 20px;
        }
        .test-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .test-table th, .test-table td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
        }
        .test-table th {
            background-color: #f4f6fa;
            color: #333;
        }
        .test-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .test-table tr:hover {
            background-color: #f1f1f1;
        }
        .action-link {
            display: inline-block;
            padding: 5px 10px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            margin: 2px;
            transition: opacity 0.2s;
        }
        .action-link:hover { opacity: 0.8; }
        .btn-delete { background-color: #ed5565; }

        /* CSS cho thông báo */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Master Admin</h2>
            </div>
            <ul class="sidebar-menu">
                <li class="active">Dashboard</li>
                <li><a href="manage_ideas.php" style="color:white; text-decoration:none;">Duyệt Góp Ý</a></li>
                <li><a href="manage_users.php" style="color:white; text-decoration:none;">Quản lý User</a></li>                
                <li><a href="manager_de.php" style="color:white; text-decoration:none;">Quản lý Đề thi</a></li>
            </ul>
        </aside>
        <main class="dashboard">
            <div class="dashboard-header">
                <h1>Quản lý Đề Thi</h1>
                <div class="profile">Laravel Admin</div>
            </div>

            <?php echo $message; // Hiển thị thông báo (nếu có) ?>
            
            <section id="tests">
                <h2>Danh sách đề thi (<?php echo $all_tests->num_rows; ?>)</h2>
                
                <table class="test-table">
                    <thead>
                        <tr>
                            <th>ID Đề</th>
                            <th>Tên Đề Thi</th>
                            <th>Người tạo</th>
                            <th>Lớp</th>
                            <th>Trình độ</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($all_tests->num_rows > 0): ?>
                            <?php while($row = $all_tests->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['ID_TD']; ?></td>
                                    <td><?php echo htmlspecialchars($row['ten_de']); ?></td>
                                    <td>
                                        <?php 
                                            // Hiển thị Tên đầy đủ, nếu không có thì hiển thị username
                                            echo htmlspecialchars($row['ho_ten'] ? $row['ho_ten'] : ($row['username'] ? $row['username'] : 'N/A')); 
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['lop_hoc']); ?></td>
                                    <td><?php echo htmlspecialchars($row['trinh_do']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_tao'])); ?></td>
                                    <td>
                                        <a href="manager_de.php?delete_id=<?php echo $row['ID_TD']; ?>" class="action-link btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa đề thi này không? Mọi câu hỏi trong đề cũng sẽ bị xóa vĩnh viễn!');">Xóa</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Không có đề thi nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

        </main>
    </div>
</body>
</html>
<?php
$conn->close(); // Đóng kết nối database
?>