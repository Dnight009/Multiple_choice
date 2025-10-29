<?php
// 1. KHỞI ĐỘNG SESSION VÀ KIỂM TRA BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Phải là giáo viên mới được xóa
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    // Redirect or display error if not authorized
    header("Location: /TracNghiem/Home/home.php"); // Or login page
    exit;
}
$teacher_id = $_SESSION['IDACC'];

// 2. KIỂM TRA ID LỚP HỌC VÀ ID HỌC SINH TỪ URL
if (!isset($_GET['class_id']) || !filter_var($_GET['class_id'], FILTER_VALIDATE_INT) ||
    !isset($_GET['student_id']) || !filter_var($_GET['student_id'], FILTER_VALIDATE_INT)) {
    // Set error message and redirect back if IDs are invalid
    $_SESSION['flash_message'] = "Lỗi: ID lớp học hoặc học sinh không hợp lệ.";
    $_SESSION['flash_type'] = "error";
    // Try to redirect back, default to home if class_id is missing entirely
    $redirect_url = isset($_GET['class_id']) ? "manage_class.php?class_id=" . $_GET['class_id'] : "/TracNghiem/Home/home.php";
    header("Location: " . $redirect_url);
    exit;
}
$class_id = (int)$_GET['class_id'];
$student_id = (int)$_GET['student_id'];

// 3. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php';

// 4. KIỂM TRA QUYỀN SỞ HỮU LỚP HỌC
$check_sql = "SELECT IDACC_teach FROM CLASS WHERE ID_CLASS = ? AND IDACC_teach = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $class_id, $teacher_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_stmt->close(); // Close statement early

if ($check_result->num_rows !== 1) {
    // Set error message and redirect back if teacher doesn't own the class
    $_SESSION['flash_message'] = "Lỗi: Bạn không có quyền quản lý lớp học này.";
    $_SESSION['flash_type'] = "error";
    header("Location: manage_class.php?class_id=" . $class_id);
    exit;
}

// 5. THỰC HIỆN XÓA HỌC SINH KHỎI LỚP (DELETE from CLASS_LIST)
$delete_sql = "DELETE FROM CLASS_LIST WHERE ID_CLASS = ? AND IDACC_STUDENT = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("ii", $class_id, $student_id);

if ($delete_stmt->execute()) {
    // Kiểm tra xem có hàng nào thực sự bị xóa không
    if ($delete_stmt->affected_rows > 0) {
        $_SESSION['flash_message'] = "Đã xóa học sinh khỏi lớp thành công!";
        $_SESSION['flash_type'] = "success";
    } else {
        // Trường hợp học sinh không có trong lớp ngay từ đầu
        $_SESSION['flash_message'] = "Học sinh không tồn tại trong lớp này.";
        $_SESSION['flash_type'] = "error";
    }
} else {
    // Lỗi khi thực thi câu lệnh DELETE
    $_SESSION['flash_message'] = "Lỗi khi xóa học sinh: " . $delete_stmt->error;
    $_SESSION['flash_type'] = "error";
}

$delete_stmt->close();
$conn->close();

// 6. QUAY TRỞ LẠI TRANG QUẢN LÝ LỚP
header("Location: manage_class.php?class_id=" . $class_id);
exit;
?>