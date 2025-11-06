<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    header("Location: /TracNghiem/Home/home.php");
    exit;
}
$teacher_id = $_SESSION['IDACC'];

// 2. KẾT NỐI CSDL
require_once __DIR__ . '/../Check/Connect.php';

// 3. LẤY ID HẠNG MỤC TỪ URL VÀ XÁC THỰC
if (!isset($_GET['id_hang_muc']) || !filter_var($_GET['id_hang_muc'], FILTER_VALIDATE_INT)) {
    die("ID hạng mục không hợp lệ.");
}
$id_hang_muc = (int)$_GET['id_hang_muc'];

// Xác thực xem GV này có sở hữu hạng mục này không
$stmt_check = $conn->prepare("SELECT ten_hang_muc FROM HANG_MUC_DIEM WHERE ID_HANG_MUC = ? AND IDACC_teach = ?");
$stmt_check->bind_param("ii", $id_hang_muc, $teacher_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    die("Lỗi: Bạn không có quyền quản lý hạng mục này.");
}
$category = $result_check->fetch_assoc();
$ten_hang_muc = $category['ten_hang_muc'];
$stmt_check->close();

// 4. XỬ LÝ KHI GV "LƯU" GÁN GHÉP
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_assignments'])) {
    
    $conn->begin_transaction();
    try {
        // 1. Xóa tất cả các gán ghép cũ
        $stmt_clear = $conn->prepare("DELETE FROM HANG_MUC_DE_THI WHERE ID_HANG_MUC = ?");
        $stmt_clear->bind_param("i", $id_hang_muc);
        $stmt_clear->execute();
        $stmt_clear->close();
        
        // 2. Thêm các gán ghép mới
        if (isset($_POST['assigned_quizzes']) && is_array($_POST['assigned_quizzes'])) {
            $stmt_insert = $conn->prepare("INSERT INTO HANG_MUC_DE_THI (ID_HANG_MUC, ID_TD) VALUES (?, ?)");
            foreach ($_POST['assigned_quizzes'] as $id_de_thi) {
                $stmt_insert->bind_param("ii", $id_hang_muc, $id_de_thi);
                $stmt_insert->execute();
            }
            $stmt_insert->close();
        }
        
        $conn->commit();
        // Quay lại trang quản lý với thông báo thành công
        $_SESSION['flash_message'] = "Cập nhật đề thi cho hạng mục thành công!";
        $_SESSION['flash_type'] = "success";
        header("Location: manage_grading.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        // Ở lại trang và báo lỗi
        $message = "Lỗi khi lưu: " . $e->getMessage();
        $message_type = "error";
    }
}


// 5. LẤY DỮ LIỆU ĐỂ HIỂN THỊ
// 5a. Lấy tất cả các đề thi của giáo viên này
$all_quizzes = [];
$stmt_quizzes = $conn->prepare("SELECT ID_TD, ten_de FROM TEN_DE WHERE IDACC = ? ORDER BY ten_de ASC");
$stmt_quizzes->bind_param("i", $teacher_id);
$stmt_quizzes->execute();
$result_quizzes = $stmt_quizzes->get_result();
$all_quizzes = $result_quizzes->fetch_all(MYSQLI_ASSOC);
$stmt_quizzes->close();

// 5b. Lấy các đề thi đã được gán cho hạng mục này
$assigned_quizzes = [];
$stmt_assigned = $conn->prepare("SELECT T.ID_TD, T.ten_de 
                                 FROM HANG_MUC_DE_THI AS H
                                 JOIN TEN_DE AS T ON H.ID_TD = T.ID_TD
                                 WHERE H.ID_HANG_MUC = ?");
$stmt_assigned->bind_param("i", $id_hang_muc);
$stmt_assigned->execute();
$result_assigned = $stmt_assigned->get_result();
$assigned_quizzes = $result_assigned->fetch_all(MYSQLI_ASSOC);
$stmt_assigned->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Gán Đề thi</title>
    <link rel="stylesheet" href="../CSS/Admin/AdminHome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .form-container { background: #fff; padding: 20px; border-radius: 7px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .success { background: #e6f7e9; color: #28a745; }
        .error { background: #f8d7da; color: #721c24; }
        
        /* CSS cho "Pillbox" (giống Create.php) */
        .multi-select-container { position: relative; background-color: #f0f0f0; border-radius: 10px; padding: 5px 10px; width: 100%; box-sizing: border-box; min-height: 48px; display: flex; flex-wrap: wrap; gap: 5px; }
        .pill { background-color: #2d98da; color: #ffffff; padding: 5px 10px; border-radius: 15px; font-size: 14px; font-weight: bold; display: flex; align-items: center; gap: 8px; height: fit-content; }
        .pill-close { cursor: pointer; font-weight: bold; color: #fff; opacity: 0.7; }
        .pill-close:hover { opacity: 1; }
        .multi-select-input { flex: 1; min-width: 150px; border: none; outline: none; background: none; padding: 5px 0; font-size: 15px; }
        .search-results { display: none; position: absolute; background: #fff; border: 1px solid #ccc; border-radius: 10px; width: calc(100% - 20px); left: 10px; top: 100%; max-height: 200px; overflow-y: auto; z-index: 100; margin-top: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .result-item { padding: 10px 15px; cursor: pointer; color: #333; }
        .result-item:hover { background-color: #f0f0f0; }
        
        .button-group { display: flex; gap: 15px; margin-top: 20px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; }
        .btn-back { background: #6c757d; color: white; padding: 12px 20px; border-radius: 5px; text-decoration: none; font-size: 16px; }
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
            <h1>Gán đề thi cho hạng mục: "<?php echo htmlspecialchars($ten_hang_muc); ?>"</h1>
            
            <div class="form-container">
                <form action="assign_quizzes.php?id_hang_muc=<?php echo $id_hang_muc; ?>" method="POST">
                    
                    <?php if (!empty($message)): ?>
                        <div class="message <?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <h3>Chọn các đề thi thuộc hạng mục này</h3>
                    <div class="multi-select-container">
                        <div class="pills-container" id="pills-container">
                            <?php foreach ($assigned_quizzes as $quiz): ?>
                                <div class="pill" data-id="<?php echo $quiz['ID_TD']; ?>">
                                    <span><?php echo htmlspecialchars($quiz['ten_de']); ?></span>
                                    <span class="pill-close" data-id="<?php echo $quiz['ID_TD']; ?>">&times;</span>
                                    <input type="hidden" name="assigned_quizzes[]" value="<?php echo $quiz['ID_TD']; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" id="assign_quiz_search" class="multi-select-input" placeholder="Gõ để tìm đề thi...">
                        <div class="search-results" id="search-results"></div>
                    </div>
                    
                    <div class="button-group">
                        <a href="manage_grading.php" class="btn-back">Hủy bỏ</a>
                        <button type="submit" name="save_assignments" class="btn-save">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const allQuizzes = <?php echo json_encode($all_quizzes); ?>;
        const searchInput = document.getElementById('assign_quiz_search');
        const resultsContainer = document.getElementById('search-results');
        const pillsContainer = document.getElementById('pills-container');

        function showResults() {
            const searchTerm = searchInput.value.toLowerCase();
            resultsContainer.innerHTML = '';
            const selectedIDs = new Set(Array.from(pillsContainer.querySelectorAll('.pill')).map(p => p.dataset.id));
            
            const filtered = allQuizzes.filter(quiz => {
                const matchesSearch = quiz.ten_de.toLowerCase().includes(searchTerm);
                const notSelected = !selectedIDs.has(String(quiz.ID_TD));
                return matchesSearch && notSelected;
            });

            if (filtered.length === 0) {
                resultsContainer.innerHTML = '<div class="result-item" style="cursor:default;">Không tìm thấy.</div>';
            } else {
                filtered.forEach(quiz => {
                    const item = document.createElement('div');
                    item.className = 'result-item';
                    item.dataset.id = quiz.ID_TD;
                    item.dataset.name = quiz.ten_de;
                    item.textContent = quiz.ten_de;
                    resultsContainer.appendChild(item);
                });
            }
            resultsContainer.style.display = 'block';
        }
        function addPill(e) {
            if (!e.target.classList.contains('result-item') || !e.target.dataset.id) return;
            const id = e.target.dataset.id;
            const name = e.target.dataset.name;
            const pill = document.createElement('div');
            pill.className = 'pill';
            pill.dataset.id = id;
            pill.innerHTML = `
                <span>${name}</span>
                <span class="pill-close" data-id="${id}">&times;</span>
                <input type="hidden" name="assigned_quizzes[]" value="${id}">
            `;
            pillsContainer.appendChild(pill);
            searchInput.value = '';
            resultsContainer.style.display = 'none';
            searchInput.focus();
        }
        function removePill(e) {
            if (!e.target.classList.contains('pill-close')) return;
            const pill = e.target.closest('.pill');
            if (pill) pill.remove();
        }
        searchInput.addEventListener('input', showResults);
        searchInput.addEventListener('focus', showResults);
        resultsContainer.addEventListener('click', addPill);
        pillsContainer.addEventListener('click', removePill);
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.multi-select-container')) resultsContainer.style.display = 'none';
        });
    </script>
</body>
</html>