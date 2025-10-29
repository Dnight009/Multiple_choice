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

// 3. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php';

// 4. LẤY VÀ KIỂM TRA ID LỚP HỌC TỪ URL
if (!isset($_GET['class_id']) || !filter_var($_GET['class_id'], FILTER_VALIDATE_INT)) {
    die("ID lớp học không hợp lệ.");
}
$class_id = (int)$_GET['class_id'];

// 5. XÁC THỰC QUYỀN SỞ HỮU LỚP
$stmt_class = $conn->prepare("SELECT ten_lop_hoc, trang_thai FROM CLASS WHERE ID_CLASS = ? AND IDACC_teach = ?");
$stmt_class->bind_param("ii", $class_id, $teacher_id);
$stmt_class->execute();
$result_class = $stmt_class->get_result();
if ($result_class->num_rows === 0) {
    die("Lỗi: Bạn không có quyền xem lớp học này.");
}
$class_info = $result_class->fetch_assoc();
$ten_lop_hoc = $class_info['ten_lop_hoc'];
$stmt_class->close();

// 6. TRUY VẤN LẤY DANH SÁCH HỌC SINH TRONG LỚP
// Lấy thêm IDACC của học sinh để dùng cho nút xóa
$sql_students = "SELECT A.IDACC, A.username, A.ho_ten, A.email, A.ngay_sinh
                 FROM CLASS_LIST AS L
                 JOIN ACCOUNT AS A ON L.IDACC_STUDENT = A.IDACC
                 WHERE L.ID_CLASS = ?
                 ORDER BY A.ho_ten, A.username";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("i", $class_id);
$stmt_students->execute();
$result_students = $stmt_students->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý lớp: <?php echo htmlspecialchars($ten_lop_hoc); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 1000px; /* Tăng độ rộng container */ margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); flex-grow: 1; }
        h1 { color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
        h2 { color: #555; }
        .student-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .student-table th, .student-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .student-table th { background-color: #f9f9f9; font-weight: bold; }
        .student-table tr:nth-child(even) { background-color: #fdfdfd; }
        .student-table tr:hover { background-color: #f1f1f1; }
        .no-students { text-align: center; color: #777; font-style: italic; margin-top: 30px; }
        .page-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-back { display: inline-block; background: #6c757d; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .btn-back:hover { background: #5a6268; }
        .btn-add-student { display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .btn-add-student:hover { background: #218838; }

        /* --- CSS MỚI CHO CÁC NÚT TRONG BẢNG --- */
        .action-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            margin-right: 10px; /* Khoảng cách giữa các nút */
        }
        .action-link:hover {
            text-decoration: underline;
        }
        .delete-link {
            color: #e74c3c; /* Màu đỏ */
        }
        .delete-link:hover {
            color: #c0392b;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="container">
        <div class="page-actions">
            <a href="List_Class.php" class="btn-back">« Quay lại danh sách lớp</a>

            <?php 
                if ($class_info['trang_thai'] == 'đang hoạt động'): 
            ?>
                <a href="add_student_to_class.php?class_id=<?php echo $class_id; ?>" class="btn-add-student">+ Thêm học sinh</a>
            <?php 
            endif; 
            ?>
        </div>

        <h1>Quản lý lớp: <?php echo htmlspecialchars($ten_lop_hoc); ?></h1>
        <h2>Danh sách học sinh (<?php echo $result_students->num_rows; ?> thành viên)</h2>

        <?php if ($result_students->num_rows > 0): ?>
            <table class="student-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên đăng nhập</th>
                        <th>Họ và Tên</th>
                        <th>Email</th>
                        <th>Lịch sử</th> <th>Hành động</th> </tr>
                </thead>
                <tbody>
                    <?php
                    $stt = 1;
                    while ($student = $result_students->fetch_assoc()):
                        $student_id = $student['IDACC']; // Lấy ID học sinh
                        $ho_ten = !empty($student['ho_ten']) ? htmlspecialchars($student['ho_ten']) : '<i>Chưa cập nhật</i>';
                        $email = !empty($student['email']) ? htmlspecialchars($student['email']) : '<i>Chưa cập nhật</i>';
                        // $ngay_sinh = !empty($student['ngay_sinh']) ? date("d/m/Y", strtotime($student['ngay_sinh'])) : '<i>Chưa cập nhật</i>';
                    ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td>
                                <?php
                                    $username = $student['username'];
                                    // Get the last 3 characters
                                    $last_three = substr($username, -3); 
                                    // Create the masked string
                                    $masked_username = "*******" . htmlspecialchars($last_three);
                                    echo $masked_username;
                                ?>
                            </td>
                            <td><?php echo $ho_ten; ?></td>
                            <td><?php echo $email; ?></td>
                            <td>
                                <a href="student_history.php?student_id=<?php echo $student_id; ?>&class_id=<?php echo $class_id; ?>" class="action-link">
                                    Xem
                                </a>
                            </td>

                            <td>
                                <a href="remove_student_from_class.php?student_id=<?php echo $student_id; ?>&class_id=<?php echo $class_id; ?>"
                                   class="action-link delete-link"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa học sinh này khỏi lớp?');">
                                    Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-students">Chưa có học sinh nào tham gia lớp học này.</p>
        <?php endif; ?>

    </div>

    <?php
    $stmt_students->close();
    $conn->close();
    include __DIR__ . '/../Home/Footer.php';
    ?>
</body>
</html>