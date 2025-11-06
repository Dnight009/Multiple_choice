<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT (Giữ nguyên)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    header("Location: /TracNghiem/Home/home.php");
    exit;
}
$teacher_id = $_SESSION['IDACC'];

// 2. KẾT NỐI CSDL (Giữ nguyên)
require_once __DIR__ . '/../Check/Connect.php';

// 3. LẤY ID HẠNG MỤC TỪ URL VÀ XÁC THỰC (Giữ nguyên)
if (!isset($_GET['id_hang_muc']) || !filter_var($_GET['id_hang_muc'], FILTER_VALIDATE_INT)) {
    die("ID hạng mục không hợp lệ.");
}
$id_hang_muc = (int)$_GET['id_hang_muc'];

$stmt_check = $conn->prepare("SELECT ten_hang_muc, quy_tac_tinh FROM HANG_MUC_DIEM WHERE ID_HANG_MUC = ? AND IDACC_teach = ?");
$stmt_check->bind_param("ii", $id_hang_muc, $teacher_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    die("Lỗi: Bạn không có quyền quản lý hạng mục này.");
}
$category = $result_check->fetch_assoc();
$ten_hang_muc = $category['ten_hang_muc'];
$quy_tac_tinh = $category['quy_tac_tinh']; 
$stmt_check->close();

// 4. LẤY DANH SÁCH CÁC ĐỀ THI ĐƯỢC GÁN (Giữ nguyên)
$assigned_quizzes = [];
$stmt_quizzes = $conn->prepare("SELECT T.ID_TD, T.ten_de 
                                FROM HANG_MUC_DE_THI AS H
                                JOIN TEN_DE AS T ON H.ID_TD = T.ID_TD
                                WHERE H.ID_HANG_MUC = ?");
$stmt_quizzes->bind_param("i", $id_hang_muc);
$stmt_quizzes->execute();
$result_quizzes = $stmt_quizzes->get_result();
while ($row = $result_quizzes->fetch_assoc()) {
    $assigned_quizzes[$row['ID_TD']] = $row['ten_de'];
}
$stmt_quizzes->close();

// 5. LẤY TẤT CẢ ĐIỂM CỦA HỌC SINH (Giữ nguyên)
$students_data = [];
$total_quizzes_assigned = count($assigned_quizzes); // Đếm số lượng đề đã gán

if ($total_quizzes_assigned > 0) {
    $quiz_ids_string = implode(',', array_keys($assigned_quizzes));
    $sql_scores = "SELECT 
                        K.IDACC, 
                        A.username, 
                        A.ho_ten, 
                        K.ID_TD, 
                        K.diem
                   FROM 
                        ketqua_lambai AS K
                   JOIN 
                        ACCOUNT AS A ON K.IDACC = A.IDACC
                   WHERE 
                        K.ID_TD IN ($quiz_ids_string) AND A.quyen = '2'
                   ORDER BY 
                        A.ho_ten, A.username, K.ID_TD";
    $result_scores = $conn->query($sql_scores);
    
    if ($result_scores && $result_scores->num_rows > 0) {
        while ($row = $result_scores->fetch_assoc()) {
            $student_id = $row['IDACC'];
            $quiz_id = $row['ID_TD'];
            
            if (!isset($students_data[$student_id])) {
                $students_data[$student_id] = [
                    'info' => [
                        'username' => $row['username'],
                        'ho_ten' => !empty($row['ho_ten']) ? $row['ho_ten'] : $row['username']
                    ],
                    'scores' => [] 
                ];
            }
            $students_data[$student_id]['scores'][$quiz_id] = $row['diem'];
        }
    }
}
$conn->close();

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
    <title>Sổ điểm: <?php echo htmlspecialchars($ten_hang_muc); ?></title>
    <link rel="stylesheet" href="../CSS/Admin/AdminHome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <style>
        .dashboard { flex-direction: column; }
        .main-content { width: 100%; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 7px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            /* Sửa: Dùng border-collapse thay vì border-spacing */
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th { 
            background-color: #f4f7f6; 
            text-align: center; /* Căn giữa tiêu đề */
        }
        /* Căn giữa cho các ô điểm */
        td:nth-child(4), td:nth-child(5) {
            text-align: center;
        }
        
        /* Căn giữa theo chiều dọc cho các ô gộp */
        td[rowspan] {
            vertical-align: top;
            padding-top: 15px; /* Đẩy chữ lên 1 chút */
        }
        
        .final-score {
            font-weight: bold;
            font-size: 1.2em;
            color: #fff;
            background-color: #28a745; /* Màu xanh lá */
        }
        .score-fail {
            background-color: #e74c3c; /* Màu đỏ nếu < 5 */
        }
        .score-not-complete {
             background-color: #777; /* Màu xám nếu chưa làm hết */
        }
        
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Bảng điều khiển</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="/TracNghiem/Teacher/dashboard.php" style="color:white; text-decoration:none;">Dashboard</a></li>
                <li class="active"><a href="/TracNghiem/Teacher/manage_grading.php" style="color:white; text-decoration:none;">Quản lý Sổ điểm</a></li>
                </ul>
        </aside>
        
        <main class="dashboard">
            <a href="manage_grading.php" class="btn-back">« Quay lại Sổ điểm</a>
            
            <h1>Sổ điểm: <?php echo htmlspecialchars($ten_hang_muc); ?></h1>
            <p><strong>Quy tắc tính điểm:</strong> <?php echo $quy_tac_map[$quy_tac_tinh]; ?></p>
            
            <div class="main-content">
                
                <table>
                    <thead>
                        <tr>
                            <th>ID Học Sinh</th>
                            <th>Họ và Tên</th>
                            <th>Tên Đề</th>
                            <th>Điểm</th>
                            <th>Điểm Tổng Kết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students_data) > 0 && $total_quizzes_assigned > 0): ?>
                            <?php foreach ($students_data as $student_id => $data): ?>
                                
                                <?php
                                // --- LOGIC TÍNH ĐIỂM (THEO YÊU CẦU MỚI) ---
                                $student_scores = []; // Mảng lưu các điểm đã làm
                                $all_done = true; // Giả sử đã làm hết

                                // 1. Kiểm tra xem đã làm hết chưa
                                foreach ($assigned_quizzes as $id_td => $ten_de) {
                                    if (isset($data['scores'][$id_td])) {
                                        $student_scores[] = $data['scores'][$id_td]; // Lưu điểm đã làm
                                    } else {
                                        $all_done = false; // Phát hiện 1 bài chưa làm
                                    }
                                }
                                
                                // 2. Tính điểm
                                $final_grade = 0.00;
                                $final_score_class = 'score-not-complete'; // Mặc định là màu xám (chưa làm hết)

                                if ($all_done && !empty($student_scores)) {
                                    // CHỈ TÍNH ĐIỂM NẾU ĐÃ LÀM HẾT
                                    if ($quy_tac_tinh == 'caonhat') {
                                        $final_grade = max($student_scores);
                                    } elseif ($quy_tac_tinh == 'trungbinh') {
                                        $final_grade = array_sum($student_scores) / count($student_scores);
                                    } else { // 'duynhat'
                                        $final_grade = $student_scores[0]; 
                                    }
                                    
                                    // Đặt class màu (xanh hoặc đỏ)
                                    $final_score_class = ($final_grade < 5) ? 'score-fail' : '';
                                
                                } 
                                // Nếu $all_done = false, thì $final_grade vẫn là 0
                                
                                $final_grade_display = number_format($final_grade, 2);
                                // --- KẾT THÚC LOGIC TÍNH ĐIỂM ---
                                ?>

                                <?php 
                                // --- LOGIC HIỂN THỊ BẢNG (DÙNG rowspan) ---
                                $is_first_row = true;
                                foreach ($assigned_quizzes as $id_td => $ten_de): 
                                ?>
                                    <tr>
                                        <?php if ($is_first_row): ?>
                                            <td rowspan="<?php echo $total_quizzes_assigned; ?>" style="text-align: center;">
                                                <?php echo $student_id; ?>
                                            </td>
                                            <td rowspan="<?php echo $total_quizzes_assigned; ?>">
                                                <?php echo htmlspecialchars($data['info']['ho_ten']); ?>
                                            </td>
                                        <?php endif; ?>

                                        <td><?php echo htmlspecialchars($ten_de); ?></td>
                                        
                                        <td style="text-align: center;">
                                            <?php 
                                            if (isset($data['scores'][$id_td])) {
                                                echo number_format($data['scores'][$id_td], 2);
                                            } else {
                                                echo '<i>Chưa làm</i>';
                                            }
                                            ?>
                                        </td>

                                        <?php if ($is_first_row): ?>
                                            <td rowspan="<?php echo $total_quizzes_assigned; ?>" 
                                                class="final-score <?php echo $final_score_class; ?>">
                                                <?php echo $final_grade_display; ?>
                                            </td>
                                        <?php 
                                            $is_first_row = false; // Đánh dấu đã hiển thị hàng đầu tiên
                                        endif; 
                                        ?>
                                    </tr>
                                <?php endforeach; // Kết thúc lặp các đề thi ?>
                            <?php endforeach; // Kết thúc lặp học sinh ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">
                                    Chưa có học sinh nào làm bài (hoặc hạng mục này chưa được gán đề thi).
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
        </main>
    </div>
</body>
</html>