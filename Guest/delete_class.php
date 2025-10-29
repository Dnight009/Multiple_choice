<?php
// 1. KHỞI ĐỘNG SESSION VÀ KIỂM TRA BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Phải là giáo viên mới được xóa
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    die("Bạn không có quyền thực hiện hành động này.");
}
$teacher_id = $_SESSION['IDACC'];

// 2. KIỂM TRA ID LỚP HỌC (TỪ URL)
if (!isset($_GET['class_id']) || !filter_var($_GET['class_id'], FILTER_VALIDATE_INT)) {
    die("ID lớp học không hợp lệ.");
}
$class_id_to_delete = (int)$_GET['class_id'];

// 3. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php';

// 4. KIỂM TRA QUYỀN SỞ HỮU (RẤT QUAN TRỌNG)
// Kiểm tra xem giáo viên này có thực sự sở hữu lớp học này không
$check_sql = "SELECT IDACC_teach FROM CLASS WHERE ID_CLASS = ? AND IDACC_teach = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $class_id_to_delete, $teacher_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 1) {
    // --- NẾU ĐÚNG LÀ CHỦ SỞ HỮU ---

    // 5. THỰC HIỆN "XÓA MỀM" (SOFT DELETE)
    // Thay vì "DELETE", chúng ta dùng "UPDATE"
    $update_sql = "UPDATE CLASS SET trang_thai = 'đã xóa' WHERE ID_CLASS = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $class_id_to_delete);
    
    if ($update_stmt->execute()) {
        // Xóa thành công
    } else {
        // Có lỗi
        die("Xóa thất bại: " . $update_stmt->error);
    }
    $update_stmt->close();

} else {
    // Nếu không sở hữu (hoặc lớp không tồn tại)
    die("Lỗi: Bạn không có quyền xóa lớp học này.");
}

$check_stmt->close();
$conn->close();

// 6. QUAY TRỞ LẠI TRANG DANH SÁCH LỚP
// Người dùng sẽ thấy lớp vừa xóa bị mờ đi và nằm ở cuối
header("Location: List_Class.php");
exit;
?>