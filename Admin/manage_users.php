<?php
// --- KẾT NỐI DATABASE ---
// Sử dụng file Connect.php để lấy biến $conn
require_once '../Check/Connect.php'; 

$message = ""; // Biến để lưu thông báo

// --- XỬ LÝ CÁC HÀNH ĐỘNG (DELETE, PROMOTE, DEMOTE, EDIT) ---

// 1. XỬ LÝ XÓA USER
if (isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    
    // Thêm một lớp bảo vệ: Không cho xóa admin (quyen = 1)
    $check_admin_sql = "SELECT quyen FROM account WHERE IDACC = $id_to_delete";
    $result = $conn->query($check_admin_sql);
    $user = $result->fetch_assoc();

    if ($user && $user['quyen'] != 1) {
        $sql = "DELETE FROM account WHERE IDACC = $id_to_delete";
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='alert alert-success'>Xóa người dùng thành công!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi khi xóa: " . $conn->error . "</div>";
        }
    } elseif ($user['quyen'] == 1) {
        $message = "<div class='alert alert-danger'>Không thể xóa tài khoản Admin!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Không tìm thấy người dùng!</div>";
    }
}

// 2. XỬ LÝ CẤP QUYỀN GIÁO VIÊN (QUYEN = 3)
if (isset($_GET['promote_id'])) {
    $id_to_promote = (int)$_GET['promote_id'];
    $sql = "UPDATE account SET quyen = 3 WHERE IDACC = $id_to_promote AND quyen = 2";
    if ($conn->query($sql) === TRUE) {
        $message = "<div class='alert alert-success'>Cấp quyền Giáo viên thành công!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi khi cấp quyền: " . $conn->error . "</div>";
    }
}

// 3. (MỚI) XỬ LÝ XÓA QUYỀN GIÁO VIÊN (QUYEN = 2)
if (isset($_GET['demote_id'])) {
    $id_to_demote = (int)$_GET['demote_id'];
    // Chỉ xóa quyền GV (quyen=3), không ảnh hưởng Admin (quyen=1)
    $sql = "UPDATE account SET quyen = 2 WHERE IDACC = $id_to_demote AND quyen = 3";
    if ($conn->query($sql) === TRUE) {
        $message = "<div class='alert alert-success'>Xóa quyền Giáo viên thành công! (Đã chuyển về Học sinh)</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi khi xóa quyền: " . $conn->error . "</div>";
    }
}

// 4. XỬ LÝ CẬP NHẬT (EDIT) TỪ FORM POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $id_to_edit = (int)$_POST['idacc'];
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $quyen = (int)$_POST['quyen'];

    // Dùng prepared statement để bảo mật
    $stmt = $conn->prepare("UPDATE account SET ho_ten = ?, email = ?, quyen = ? WHERE IDACC = ?");
    $stmt->bind_param("ssii", $ho_ten, $email, $quyen, $id_to_edit);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Cập nhật thông tin thành công!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi khi cập nhật: " . $stmt->error . "</div>";
    }
    $stmt->close();
}


// --- LẤY DỮ LIỆU ĐỂ HIỂN THỊ ---
$edit_user_data = null;
if (isset($_GET['edit_id'])) {
    // Lấy thông tin user cần sửa để điền vào form
    $id_to_edit = (int)$_GET['edit_id'];
    $sql = "SELECT IDACC, username, email, ho_ten, quyen FROM account WHERE IDACC = $id_to_edit";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $edit_user_data = $result->fetch_assoc();
    } else {
        $message = "<div class='alert alert-danger'>Không tìm thấy người dùng để sửa!</div>";
    }
} else {
    // Lấy danh sách tất cả user
    $sql = "SELECT IDACC, username, email, ho_ten, quyen, ngay_tao FROM account ORDER BY IDACC ASC";
    $all_users = $conn->query($sql);
}

// Hàm trợ giúp để hiển thị tên Quyền
function hienThiQuyen($quyen) {
    switch ($quyen) {
        case 1: return 'Admin';
        case 2: return 'Học sinh';
        case 3: return 'Giáo viên';
        default: return 'Không xác định';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Người Dùng</title>
    <link rel="stylesheet" href="../CSS/Admin/AdminHome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Thêm CSS cho bảng và form */
        .dashboard section {
            background: #fff;
            border-radius: 7px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 25px;
            margin-top: 20px;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .user-table th, .user-table td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
        }
        .user-table th {
            background-color: #f4f6fa;
            color: #333;
        }
        .user-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .user-table tr:hover {
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
        .action-link:hover {
            opacity: 0.8;
        }
        .btn-edit { background-color: #2db7f5; }
        .btn-delete { background-color: #ed5565; }
        .btn-promote { background-color: #47d147; }
        .btn-demote { background-color: #f5a623; } /* Nút xóa quyền mới */

        /* CSS cho form sửa */
        .edit-form {
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Quan trọng */
        }
        .form-group input[type="text"][disabled] {
            background: #eee;
        }
        .btn-submit {
            background: #3578e5;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .btn-submit:hover {
            background: #2a62bc;
        }
        .btn-cancel {
            display: inline-block;
            background: #aaa;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 1em;
            margin-left: 10px;
        }

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
                <li><a href="Adminhome.php" style="color:white; text-decoration:none;">Dashboard</a></li>
                <li><a href="manage_ideas.php" style="color:white; text-decoration:none;">Duyệt Góp Ý</a></li>
                <li><a href="manage_users.php" style="color:white; text-decoration:none;">Quản lý User</a></li>                
                <li><a href="manager_de.php" style="color:white; text-decoration:none;">Quản lý Đề thi</a></li>
            </ul>
        </aside>
        <main class="dashboard">
            <div class="dashboard-header">
                <h1>Quản lý Người Dùng</h1>
                <div class="profile"><a href="../Guest/profile.php">Laravel Admin</a></div>
            </div>

            <?php echo $message; // Hiển thị thông báo (nếu có) ?>

            <?php if ($edit_user_data): // --- HIỂN THỊ FORM EDIT NẾU CÓ ?edit_id=... --- ?>
            
            <section id="edit-user-form">
                <h2>Sửa thông tin người dùng: <?php echo htmlspecialchars($edit_user_data['username']); ?></h2>
                <form class="edit-form" method="POST" action="manage_users.php">
                    <input type="hidden" name="idacc" value="<?php echo $edit_user_data['IDACC']; ?>">
                    
                    <div class="form-group">
                        <label for="username">Tên đăng nhập (Không thể đổi)</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($edit_user_data['username']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="ho_ten">Họ và Tên</label>
                        <input type="text" id="ho_ten" name="ho_ten" value="<?php echo htmlspecialchars($edit_user_data['ho_ten']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_user_data['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="quyen">Quyền</label>
                        <select id="quyen" name="quyen">
                            <option value="1" <?php echo ($edit_user_data['quyen'] == 1) ? 'selected' : ''; ?>>Admin</option>
                            <option value="2" <?php echo ($edit_user_data['quyen'] == 2) ? 'selected' : ''; ?>>Học sinh</option>
                            <option value="3" <?php echo ($edit_user_data['quyen'] == 3) ? 'selected' : ''; ?>>Giáo viên</option>
                        </select>
                    </div>

                    <button type="submit" name="edit_user" class="btn-submit">Lưu thay đổi</button>
                    <a href="manage_users.php" class="btn-cancel">Hủy</a>
                </form>
            </section>

            <?php else: // --- HIỂN THỊ DANH SÁCH USER (MẶC ĐỊNH) --- ?>
            
            <section id="users">
                <h2>Danh sách người dùng (<?php echo $all_users->num_rows; ?>)</h2>
                
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ và Tên</th>
                            <th>Email</th>
                            <th>Quyền</th>
                            <th>Ngày tạo</th>
                            <th style="width: 25%;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($all_users->num_rows > 0): ?>
                            <?php while($row = $all_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['IDACC']; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ho_ten']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><strong><?php echo hienThiQuyen($row['quyen']); ?></strong></td>
                                    <td><?php echo $row['ngay_tao'] ? date('d/m/Y', strtotime($row['ngay_tao'])) : 'N/A'; ?></td>
                                    <td>
                                        <a href="manage_users.php?edit_id=<?php echo $row['IDACC']; ?>" class="action-link btn-edit">Sửa</a>
                                        
                                        <?php if ($row['quyen'] == 2): // Chỉ hiển thị nút 'Cấp quyền' cho Học sinh ?>
                                            <a href="manage_users.php?promote_id=<?php echo $row['IDACC']; ?>" class="action-link btn-promote" onclick="return confirm('Bạn có chắc muốn cấp quyền Giáo viên cho user này?');">Cấp quyền GV</a>
                                        <?php endif; ?>

                                        <?php if ($row['quyen'] == 3): // (MỚI) Chỉ hiển thị nút 'Xóa quyền' cho Giáo viên ?>
                                            <a href="manage_users.php?demote_id=<?php echo $row['IDACC']; ?>" class="action-link btn-demote" onclick="return confirm('Bạn có chắc muốn xóa quyền Giáo viên của user này? (sẽ chuyển về Học sinh)');">Xóa quyền GV</a>
                                        <?php endif; ?>

                                        <?php if ($row['quyen'] != 1): // Không cho xóa Admin ?>
                                            <a href="manage_users.php?delete_id=<?php echo $row['IDACC']; ?>" class="action-link btn-delete" onclick="return confirm('Bạn có chắc chắn muốn XÓA HẲN user này không? Hành động này không thể hoàn tác!');">Xóa User</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Không có người dùng nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <?php endif; ?>

        </main>
    </div>
</body>
</html>
<?php
$conn->close(); // Đóng kết nối database
?>