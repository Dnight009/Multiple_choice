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

// 4. LẤY VÀ KIỂM TRA ID LỚP HỌC TỪ URL
if (!isset($_GET['class_id']) || !filter_var($_GET['class_id'], FILTER_VALIDATE_INT)) {
    die("ID lớp học không hợp lệ.");
}
$class_id = (int)$_GET['class_id'];

// 5. XÁC THỰC QUYỀN SỞ HỮU LỚP (Kiểm tra xem GV có phải chủ lớp không)
$stmt_class_check = $conn->prepare("SELECT ten_lop_hoc FROM CLASS WHERE ID_CLASS = ? AND IDACC_teach = ?");
$stmt_class_check->bind_param("ii", $class_id, $teacher_id);
$stmt_class_check->execute();
$result_class_check = $stmt_class_check->get_result();
if ($result_class_check->num_rows === 0) {
    die("Lỗi: Bạn không có quyền quản lý lớp học này.");
}
$class_info = $result_class_check->fetch_assoc();
$ten_lop_hoc = $class_info['ten_lop_hoc'];
$stmt_class_check->close();

// 6. BIẾN LƯU THÔNG BÁO
$message = "";
$message_type = ""; // "success" or "error"

// 7. XỬ LÝ KHI GIÁO VIÊN THÊM HỌC SINH (GỬI FORM POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy ID học sinh từ form
    // Input list='students-list' sẽ gửi value (IDACC) khi submit
    if (isset($_POST['student_id']) && filter_var($_POST['student_id'], FILTER_VALIDATE_INT)) {
        $student_id = (int)$_POST['student_id'];

        // Kiểm tra xem học sinh có tồn tại và đúng là học sinh không (quyen = 2)
        $stmt_check_student = $conn->prepare("SELECT IDACC FROM ACCOUNT WHERE IDACC = ? AND quyen = '2'");
        $stmt_check_student->bind_param("i", $student_id);
        $stmt_check_student->execute();
        $result_check_student = $stmt_check_student->get_result();

        if ($result_check_student->num_rows === 1) {
            // Kiểm tra xem học sinh đã có trong lớp chưa
            $stmt_check_exist = $conn->prepare("SELECT ID_LIST FROM CLASS_LIST WHERE ID_CLASS = ? AND IDACC_STUDENT = ?");
            $stmt_check_exist->bind_param("ii", $class_id, $student_id);
            $stmt_check_exist->execute();
            $result_check_exist = $stmt_check_exist->get_result();

            if ($result_check_exist->num_rows === 0) {
                // Nếu chưa có, thêm vào bảng CLASS_LIST
                $stmt_add = $conn->prepare("INSERT INTO CLASS_LIST (ID_CLASS, IDACC_STUDENT) VALUES (?, ?)");
                $stmt_add->bind_param("ii", $class_id, $student_id);
                if ($stmt_add->execute()) {
                    $message = "Đã thêm học sinh vào lớp thành công!";
                    $message_type = "success";
                } else {
                    $message = "Lỗi khi thêm học sinh: " . $stmt_add->error;
                    $message_type = "error";
                }
                $stmt_add->close();
            } else {
                // Nếu đã có
                $message = "Học sinh này đã có trong lớp.";
                $message_type = "error";
            }
            $stmt_check_exist->close();
        } else {
            $message = "Lỗi: Không tìm thấy học sinh hợp lệ với ID này.";
            $message_type = "error";
        }
        $stmt_check_student->close();

    } else {
        $message = "Lỗi: Vui lòng chọn một học sinh hợp lệ từ danh sách.";
        $message_type = "error";
    }
}

// 8. LẤY DANH SÁCH TẤT CẢ HỌC SINH (quyen = 1) ĐỂ HIỂN THỊ TRONG DATALIST
// Sắp xếp theo tên đăng nhập để dễ tìm
$sql_all_students = "SELECT IDACC, username, ho_ten FROM ACCOUNT WHERE quyen = '2' ORDER BY username ASC";
$result_all_students = $conn->query($sql_all_students);
$students = [];
if ($result_all_students->num_rows > 0) {
    $students = $result_all_students->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm học sinh vào lớp: <?php echo htmlspecialchars($ten_lop_hoc); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 700px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); flex-grow: 1; }
        h1 { color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; color: #555; margin-bottom: 8px; }
        .form-group input[type="text"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; }
        .button-group { display: flex; gap: 15px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
        .btn-primary, .btn-secondary { padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; text-align: center; flex: 1; /* Chia đều nút */}
        .btn-primary { background: #28a745; color: white; }
        .btn-primary:hover { background: #218838; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .message { padding: 15px; margin-top: 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .success { background: #e6f7e9; color: #28a745; border: 1px solid #b7e9c7; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../Home/navbar.php'; ?>

    <div class="container">
        <h1>Thêm học sinh vào lớp: <?php echo htmlspecialchars($ten_lop_hoc); ?></h1>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="add_student_to_class.php?class_id=<?php echo $class_id; ?>" method="POST" style="margin-top: 20px;">
            <div class="form-group">
                <label for="student-search">Tìm và chọn học sinh:</label>
                <input type="text" id="student-search" name="student_search" list="students-list" placeholder="Gõ tên đăng nhập hoặc họ tên..." required autocomplete="off">

                <datalist id="students-list">
                    <?php foreach ($students as $student): ?>
                        <?php
                            // Chỉ lấy họ tên, nếu không có thì dùng tạm username
                            $display_text = !empty($student['ho_ten']) ? htmlspecialchars($student['ho_ten']) : $student['username'];
                        ?>
                        <option value="<?php echo $student['IDACC']; ?>">
                           <?php echo $display_text; ?>
                        </option>
                    <?php endforeach; ?>
                </datalist>

                <input type="hidden" name="student_id" id="student_id">
            </div>

            <div class="button-group">
                <a href="manage_class.php?class_id=<?php echo $class_id; ?>" class="btn-secondary">Quay lại</a>
                <button type="submit" class="btn-primary">Thêm học sinh</button>
            </div>
        </form>
    </div>

    <script>
        // Lắng nghe sự kiện khi người dùng nhập vào ô tìm kiếm
        document.getElementById('student-search').addEventListener('input', function(e) {
            var input = e.target;
            var list = input.getAttribute('list');
            var options = document.querySelectorAll('#' + list + ' option');
            var hiddenInput = document.getElementById('student_id');
            var inputValue = input.value;

            hiddenInput.value = ''; // Reset hidden input

            // Kiểm tra xem giá trị nhập vào có khớp chính xác với ID (value) của option nào không
            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                if (option.value === inputValue) {
                    // Nếu khớp ID (người dùng chọn trực tiếp), gán ID vào input ẩn
                    hiddenInput.value = option.value;
                    // Tùy chọn: Hiển thị tên đầy đủ trong ô input sau khi chọn
                    // input.value = option.innerText;
                    break;
                }
            }
            // Nếu không khớp ID, kiểm tra xem có khớp với text hiển thị không
             if (hiddenInput.value === '') {
                 for (var i = 0; i < options.length; i++) {
                    var option = options[i];
                    if(option.innerText === inputValue) {
                        hiddenInput.value = option.value;
                        break;
                    }
                }
             }
        });

        // Đảm bảo student_id được gửi đi là ID chứ không phải tên
        document.querySelector('form').addEventListener('submit', function(e) {
            var searchInput = document.getElementById('student-search');
            var hiddenInput = document.getElementById('student_id');
            
            // Nếu hidden input rỗng nhưng search input có giá trị -> người dùng gõ tên nhưng chưa chọn
            if (hiddenInput.value === '' && searchInput.value.trim() !== '') {
                 // Tìm lại ID dựa trên tên trong datalist
                 var list = searchInput.getAttribute('list');
                 var options = document.querySelectorAll('#' + list + ' option');
                 var found = false;
                 for (var i = 0; i < options.length; i++) {
                     if(options[i].innerText === searchInput.value) {
                         hiddenInput.value = options[i].value;
                         found = true;
                         break;
                     }
                 }
                 if(!found) {
                    alert('Vui lòng chọn một học sinh hợp lệ từ danh sách.');
                    e.preventDefault(); // Ngăn form submit
                 }
            } else if (hiddenInput.value === '') {
                 alert('Vui lòng chọn một học sinh.');
                 e.preventDefault(); // Ngăn form submit
            }
        });
    </script>

    <?php include __DIR__ . '/../Home/Footer.php'; ?>

</body>
</html>