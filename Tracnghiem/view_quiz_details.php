<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Check/Connect.php'; 

// 2. LẤY ID NGƯỜI DÙNG VÀ QUYỀN
$user_id = $_SESSION['IDACC'] ?? 0;
// [QUAN TRỌNG] Đảm bảo 'quyen' của bạn được lưu trong session
// 1 = Admin, 2 = Học Sinh, 3 = Giáo Viên
$user_quyen = $_SESSION['quyen'] ?? 'guest'; 

// 3. LẤY ID ĐỀ THI
if (!isset($_GET['id_de']) || empty($_GET['id_de'])) {
    die("Không tìm thấy đề thi.");
}
$de_thi_id = (int)$_GET['id_de'];

// 4. LẤY THÔNG TIN ĐỀ THI (Lấy mọi thứ)
$stmt_de = $conn->prepare("SELECT * FROM TEN_DE WHERE ID_TD = ?");
$stmt_de->bind_param("i", $de_thi_id);
$stmt_de->execute();
$result_de = $stmt_de->get_result();
if ($result_de->num_rows === 0) {
    die("Đề thi không tồn tại.");
}
$de_thi = $result_de->fetch_assoc();
$stmt_de->close();

// 5. KIỂM TRA QUYỀN SỞ HỮU (CHO GIÁO VIÊN)
// $is_owner chỉ TRUE khi: quyen = 3 (Giáo viên) VÀ IDACC của đề = IDACC của người xem
$is_owner = ($user_quyen == '3' && $de_thi['IDACC'] == $user_id);

// 6. KIỂM TRA QUYỀN TRUY CẬP (CHO HỌC SINH)
$co_quyen_truy_cap = false;
$stmt_check_public = $conn->prepare("SELECT COUNT(*) as count FROM DE_THI_LOP WHERE ID_TD = ?");
$stmt_check_public->bind_param("i", $de_thi_id);
$stmt_check_public->execute();
$is_private = $stmt_check_public->get_result()->fetch_assoc()['count'] > 0;
$stmt_check_public->close();

if (!$is_private) {
    $co_quyen_truy_cap = true; // Đề công khai, ai cũng có quyền
} elseif ($user_id > 0 && $user_quyen == '2') { // Nếu là học sinh ('2') và đề là riêng tư
    // Kiểm tra xem học sinh này có trong lớp được gán không
    $sql_check_permission = "SELECT COUNT(L.ID_LIST) as count
                             FROM DE_THI_LOP AS D
                             JOIN CLASS_LIST AS L ON D.ID_CLASS = L.ID_CLASS
                             WHERE D.ID_TD = ? AND L.IDACC_STUDENT = ?";
    $stmt_check_permission = $conn->prepare($sql_check_permission);
    $stmt_check_permission->bind_param("ii", $de_thi_id, $user_id);
    $stmt_check_permission->execute();
    $is_in_class = $stmt_check_permission->get_result()->fetch_assoc()['count'] > 0;
    if ($is_in_class) {
        $co_quyen_truy_cap = true; // Có quyền vì học sinh có trong lớp
    }
    $stmt_check_permission->close();
}

// Nếu là giáo viên (chủ đề) thì luôn có quyền
if ($is_owner) {
    $co_quyen_truy_cap = true;
}

// RÀO CẢN SỐ 1: CHẶN NẾU KHÔNG CÓ QUYỀN TRUY CẬP LỚP
if (!$co_quyen_truy_cap) {
    die("Bạn không có quyền truy cập hoặc không thuộc lớp được gán để làm bài thi này.");
}

// HÀM HELPER (ĐỂ FORMAT NGÀY GIỜ CHO INPUT)
function format_datetime_for_input($datetime_str) {
    if (empty($datetime_str)) return "";
    return date("Y-m-d\TH:i", strtotime($datetime_str));
}

// ==========================================================
// RÀO CẢN SỐ 2: KIỂM TRA NGÀY GIỜ (CHỈ DÀNH CHO HỌC SINH)
// (Giáo viên $is_owner sẽ bỏ qua bước này)
// ==========================================================
if ($user_quyen == '2') { // QUAN TRỌNG: Kiểm tra nếu là học sinh
    $now = time(); // Thời gian hiện tại (tính bằng giây)
    
    // Chuyển đổi thời gian CSDL ( dạng '2025-11-10 20:00:00') sang giây
    $start = !empty($de_thi['thoi_gian_bat_dau']) ? strtotime($de_thi['thoi_gian_bat_dau']) : 0;
    $end = !empty($de_thi['thoi_gian_ket_thuc']) ? strtotime($de_thi['thoi_gian_ket_thuc']) : 0;

    // Kịch bản 1: Đề có hẹn giờ bắt đầu VÀ bây giờ chưa đến giờ
    if ($start > 0 && $now < $start) {
        die("Chưa đến thời gian làm bài. Đề thi sẽ mở lúc: " . date("d/m/Y H:i", $start));
    }
    
    // Kịch bản 2: Đề có hẹn giờ kết thúc VÀ bây giờ đã quá giờ
    if ($end > 0 && $now > $end) {
        die("Đã hết hạn làm bài (Hết hạn lúc: " . date("d/m/Y H:i", $end) . ").");
    }
}
// ==========================================================
// KẾT THÚC RÀO CẢN NGÀY GIỜ
// ==========================================================


// 8. LẤY TẤT CẢ CÂU HỎI
$stmt_ch = $conn->prepare("SELECT * FROM CAU_HOI WHERE ID_TD = ? ORDER BY ID_CH ASC");
$stmt_ch->bind_param("i", $de_thi_id);
$stmt_ch->execute();
$result_cauhoi = $stmt_ch->get_result();
$questions = $result_cauhoi->fetch_all(MYSQLI_ASSOC);
$stmt_ch->close();

// 9. LẤY DỮ LIỆU CHO GIÁO VIÊN (NẾU LÀ GIÁO VIÊN)
$lop_cua_toi = [];
$lop_da_gan = [];
if ($is_owner) {
    $stmt_lop = $conn->prepare("SELECT ID_CLASS, ten_lop_hoc FROM CLASS WHERE IDACC_teach = ? AND trang_thai = 'đang hoạt động'");
    $stmt_lop->bind_param("i", $user_id); // Dùng $user_id (là ID của giáo viên)
    $stmt_lop->execute();
    $result_lop = $stmt_lop->get_result();
    $lop_cua_toi = $result_lop->fetch_all(MYSQLI_ASSOC);
    $stmt_lop->close();
    
    $stmt_gan = $conn->prepare("SELECT c.ID_CLASS, c.ten_lop_hoc FROM DE_THI_LOP d JOIN CLASS c ON d.ID_CLASS = c.ID_CLASS WHERE d.ID_TD = ?");
    $stmt_gan->bind_param("i", $de_thi_id);
    $stmt_gan->execute();
    $result_gan = $stmt_gan->get_result();
    $lop_da_gan = $result_gan->fetch_all(MYSQLI_ASSOC);
    $stmt_gan->close();
}

// Lấy flash message (nếu có)
$flash_message = "";
$flash_type = "";
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    $flash_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết bộ đề</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; flex-direction: column; margin: 0; padding: 0; min-height: 100vh; }
        .container { width: 900px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 20px auto; }
        .quiz-header { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .success { background: #e6f7e9; color: #28a745; border: 1px solid #b7e9c7; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
    
    <?php if ($is_owner): // ---- NẾU LÀ GIÁO VIÊN (CHỦ SỞ HỮU) ---- ?>
    <style>
        /* (CSS Pillbox) */
        .multi-select-container { position: relative; background-color: #f0f0f0; border-radius: 10px; padding: 5px 10px; width: 100%; box-sizing: border-box; min-height: 48px; display: flex; flex-wrap: wrap; gap: 5px; }
        .pill { background-color: #2d98da; color: #ffffff; padding: 5px 10px; border-radius: 15px; font-size: 14px; font-weight: bold; display: flex; align-items: center; gap: 8px; height: fit-content; }
        .pill-close { cursor: pointer; font-weight: bold; color: #fff; opacity: 0.7; }
        .pill-close:hover { opacity: 1; }
        .multi-select-input { flex: 1; min-width: 150px; border: none; outline: none; background: none; padding: 5px 0; font-size: 15px; }
        .search-results { display: none; position: absolute; background: #fff; border: 1px solid #ccc; border-radius: 10px; width: calc(100% - 20px); left: 10px; top: 100%; max-height: 200px; overflow-y: auto; z-index: 100; margin-top: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .result-item { padding: 10px 15px; cursor: pointer; color: #333; }
        .result-item:hover { background-color: #f0f0f0; }
        
        /* (CSS Form Sửa Câu Hỏi) */
        .question-edit-block { background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        
        .question-edit-block input[type="text"], 
        .question-edit-block input[type="number"],
        input[type="datetime-local"] { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            box-sizing: border-box; 
            margin-top: 5px; 
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        
        .question-edit-block label, .form-group label { 
            font-weight: bold; 
            margin-top: 10px; 
            display: block; 
        }
        .answer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn-delete-question { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; float: right; }
        .form-actions { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn-save, .btn-add { background: #28a745; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; }
    </style>
    
    <?php else: // ---- NẾU LÀ HỌC SINH ---- ?>
    <style>
        /* (CSS Học sinh) */
        .question-view-block { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 15px; }
        .question-text { font-weight: bold; font-size: 1.2em; margin-bottom: 15px; }
        .answer-options { display: flex; flex-direction: column; gap: 10px; }
        .answer-label { display: block; background: #f0f0f0; padding: 12px; border-radius: 5px; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; }
        .answer-label:hover { background: #e0e0e0; }
        input[type="radio"]:checked + .answer-label { background: #e6f7e9; border-color: #28a745; font-weight: bold; }
        input[type="radio"] { display: none; }
        .btn-submit-quiz { background: #2d98da; color: white; border: none; padding: 15px 30px; font-size: 18px; border-radius: 5px; cursor: pointer; display: block; width: 100%; margin-top: 20px; }
        #quiz-timer { position: fixed; bottom: 20px; right: 20px; background: rgba(0, 0, 0, 0.8); color: white; padding: 10px 20px; border-radius: 5px; font-size: 1.5em; font-weight: bold; z-index: 1000; }
    </style>
    <?php endif; ?>
</head>

<body>
    <?php include '../Home/navbar.php'; ?>
    <div class="container">
    
        <?php if (!empty($flash_message)): ?>
            <div class="message <?php echo htmlspecialchars($flash_type); ?>">
                <?php echo htmlspecialchars($flash_message); ?>
            </div>
        <?php endif; ?>

        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($de_thi['ten_de']); ?></h1>
            <p style="margin: 5px 0;">
                <strong>Thời gian làm bài:</strong> <?php echo $de_thi['thoi_luong_phut']; ?> phút
            </p>
            <p style="margin: 5px 0;">
                <strong>Mở lúc:</strong> 
                <?php echo !empty($de_thi['thoi_gian_bat_dau']) ? date("d/m/Y H:i", strtotime($de_thi['thoi_gian_bat_dau'])) : "Mọi lúc"; ?>
            </p>
            <p style="margin: 5px 0;">
                <strong>Đóng lúc:</strong> 
                <?php echo !empty($de_thi['thoi_gian_ket_thuc']) ? date("d/m/Y H:i", strtotime($de_thi['thoi_gian_ket_thuc'])) : "Không giới hạn"; ?>
            </p>
        </div>

        <?php if ($is_owner): // ===================== GIAO DIỆN GIÁO VIÊN (SỬA ĐỀ) ===================== ?>
        
        <form action="update_quiz.php" method="POST" id="edit-quiz-form">
            <input type="hidden" name="id_de_thi" value="<?php echo $de_thi_id; ?>">
            
            <h3>Gán cho các lớp</h3>
            <div class="multi-select-container">
                <div class="pills-container" id="pills-container">
                    <?php foreach ($lop_da_gan as $lop): ?>
                        <div class="pill" data-id="<?php echo $lop['ID_CLASS']; ?>">
                            <span><?php echo htmlspecialchars($lop['ten_lop_hoc']); ?></span>
                            <span class="pill-close" data-id="<?php echo $lop['ID_CLASS']; ?>">&times;</span>
                            <input type="hidden" name="assigned_classes[]" value="<?php echo $lop['ID_CLASS']; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="text" id="assign_classes_search" class="multi-select-input" placeholder="Gõ để tìm & thêm lớp...">
                <div class="search-results" id="search-results"></div>
            </div>
            
            <div class="answer-grid" style="margin-top: 15px;">
                <div class="form-group">
                    <label>Ngày giờ bắt đầu:</label>
                    <input type="datetime-local" name="thoi_gian_bat_dau" 
                           id="thoi_gian_bat_dau"
                           value="<?php echo format_datetime_for_input($de_thi['thoi_gian_bat_dau']); ?>">
                </div>
                <div class="form-group">
                    <label>Ngày giờ kết thúc:</label>
                    <input type="datetime-local" name="thoi_gian_ket_thuc" 
                           id="thoi_gian_ket_thuc"
                           value="<?php echo format_datetime_for_input($de_thi['thoi_gian_ket_thuc']); ?>">
                </div>
            </div>
            
            <hr style="margin: 20px 0;">

            <h2>Nội dung câu hỏi (<?php echo count($questions); ?> câu)</h2>
            <div id="question-list">
                <?php foreach ($questions as $index => $q): ?>
                <div class="question-edit-block" data-question-id="<?php echo $q['ID_CH']; ?>">
                    <button type="button" class="btn-delete-question" data-id="<?php echo $q['ID_CH']; ?>">Xóa</button>
                    <strong>Câu <?php echo $index + 1; ?>:</strong>
                    <label>Câu hỏi:</label>
                    <input type="text" name="question[<?php echo $q['ID_CH']; ?>][cau_hoi]" value="<?php echo htmlspecialchars($q['cau_hoi']); ?>">
                    <div class="answer-grid">
                        <div><label>Đáp án 1 (A):</label><input type="text" name="question[<?php echo $q['ID_CH']; ?>][dap_an_1]" value="<?php echo htmlspecialchars($q['dap_an_1']); ?>"></div>
                        <div><label>Đáp án 2 (B):</label><input type="text" name="question[<?php echo $q['ID_CH']; ?>][dap_an_2]" value="<?php echo htmlspecialchars($q['dap_an_2']); ?>"></div>
                        <div><label>Đáp án 3 (C):</label><input type="text" name="question[<?php echo $q['ID_CH']; ?>][dap_an_3]" value="<?php echo htmlspecialchars($q['dap_an_3']); ?>"></div>
                        <div><label>Đáp án 4 (D):</label><input type="text" name="question[<?php echo $q['ID_CH']; ?>][dap_an_4]" value="<?php echo htmlspecialchars($q['dap_an_4']); ?>"></div>
                    </div>
                    <label>Đáp án đúng (Nhập 1, 2, 3, hoặc 4):</label>
                    <input type="number" name="question[<?php echo $q['ID_CH']; ?>][cau_tra_loi_dung]" value="<?php echo htmlspecialchars($q['cau_tra_loi_dung']); ?>" min="1" max="4">
                </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="delete_question_ids" id="delete_question_ids">
            <div class="form-actions">
                <button type="button" id="btn-add-question" class="btn-add">Thêm câu hỏi mới</button>
                <button type="submit" id="btn-save-all" class="btn-save">Lưu tất cả thay đổi</button>
            </div>
        </form>
        

        <?php else: // ===================== GIAO DIỆN HỌC SINH (LÀM BÀI) ===================== ?>
        
        <form action="ket_qua.php" method="POST" id="quiz-form">
            <input type="hidden" name="id_de_thi" value="<?php echo $de_thi_id; ?>">
            <input type="hidden" name="start_time" value="<?php echo time(); ?>">
            
            <?php foreach ($questions as $index => $q): ?>
            <div class="question-view-block">
                <div class="question-text">Câu <?php echo $index + 1; ?>: <?php echo htmlspecialchars($q['cau_hoi']); ?></div>
                <div class="answer-options">
                    <input type="radio" name="answers[<?php echo $q['ID_CH']; ?>]" id="q<?php echo $q['ID_CH']; ?>_1" value="1">
                    <label class="answer-label" for="q<?php echo $q['ID_CH']; ?>_1"><strong>A.</strong> <?php echo htmlspecialchars($q['dap_an_1']); ?></label>
                    <input type="radio" name="answers[<?php echo $q['ID_CH']; ?>]" id="q<?php echo $q['ID_CH']; ?>_2" value="2">
                    <label class="answer-label" for="q<?php echo $q['ID_CH']; ?>_2"><strong>B.</strong> <?php echo htmlspecialchars($q['dap_an_2']); ?></label>
                    <input type="radio" name="answers[<?php echo $q['ID_CH']; ?>]" id="q<?php echo $q['ID_CH']; ?>_3" value="3">
                    <label class="answer-label" for="q<?php echo $q['ID_CH']; ?>_3"><strong>C.</strong> <?php echo htmlspecialchars($q['dap_an_3']); ?></label>
                    <input type="radio" name="answers[<?php echo $q['ID_CH']; ?>]" id="q<?php echo $q['ID_CH']; ?>_4" value="4">
                    <label class="answer-label" for="q<?php echo $q['ID_CH']; ?>_4"><strong>D.</strong> <?php echo htmlspecialchars($q['dap_an_4']); ?></label>
                </div>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn-submit-quiz" onclick="return confirm('Bạn có chắc chắn muốn nộp bài không?');">Nộp bài</button>
        </form>

        <div id="quiz-timer"><?php echo $de_thi['thoi_luong_phut']; ?>:00</div>
        
        <?php endif; // Kết thúc khối if($is_owner) ?>
        
    </div>
    <?php include '../Home/Footer.php'; ?>

    
    <?php if ($is_owner): // ---- SCRIPT CHO GIÁO VIÊN ---- ?>
    <script>
        // (JavaScript cho Pill-box giữ nguyên)
        const allClasses = <?php echo json_encode($lop_cua_toi); ?>;
        const searchInput = document.getElementById('assign_classes_search');
        const resultsContainer = document.getElementById('search-results');
        const pillsContainer = document.getElementById('pills-container');
        function showResults() {
            const searchTerm = searchInput.value.toLowerCase();
            resultsContainer.innerHTML = '';
            const selectedIDs = new Set(Array.from(pillsContainer.querySelectorAll('.pill')).map(p => p.dataset.id));
            const filtered = allClasses.filter(cls => {
                const matchesSearch = cls.ten_lop_hoc.toLowerCase().includes(searchTerm);
                const notSelected = !selectedIDs.has(String(cls.ID_CLASS));
                return matchesSearch && notSelected;
            });
            if (filtered.length === 0) {
                resultsContainer.innerHTML = '<div class="result-item" style="cursor:default;">Không tìm thấy.</div>';
            } else {
                filtered.forEach(cls => {
                    const item = document.createElement('div');
                    item.className = 'result-item';
                    item.dataset.id = cls.ID_CLASS;
                    item.dataset.name = cls.ten_lop_hoc;
                    item.textContent = cls.ten_lop_hoc;
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
            pill.innerHTML = `<span>${name}</span><span class="pill-close" data-id="${id}">&times;</span><input type="hidden" name="assigned_classes[]" value="${id}">`;
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

        // (JavaScript Thêm/Xóa câu hỏi giữ nguyên)
        let newQuestionCounter = 0;
        const questionList = document.getElementById('question-list');
        const deleteInput = document.getElementById('delete_question_ids');
        document.getElementById('btn-add-question').addEventListener('click', function() {
            newQuestionCounter++;
            const newIndex = 'new_' + newQuestionCounter;
            const newBlock = document.createElement('div');
            newBlock.className = 'question-edit-block';
            newBlock.innerHTML = `
                <button type="button" class="btn-delete-question" data-id="new">Xóa</button>
                <strong>Câu hỏi mới ${newQuestionCounter}:</strong>
                <label>Câu hỏi:</label>
                <input type="text" name="new_question[${newIndex}][cau_hoi]" placeholder="Nhập nội dung câu hỏi...">
                <div class="answer-grid">
                    <div><label>Đáp án 1 (A):</label><input type="text" name="new_question[${newIndex}][dap_an_1]"></div>
                    <div><label>Đáp án 2 (B):</label><input type="text" name="new_question[${newIndex}][dap_an_2]"></div>
                    <div><label>Đáp án 3 (C):</label><input type="text" name="new_question[${newIndex}][dap_an_3]"></div>
                    <div><label>Đáp án 4 (D):</label><input type="text" name="new_question[${newIndex}][dap_an_4]"></div>
                </div>
                <label>Đáp án đúng (1-4):</label>
                <input type="number" name="new_question[${newIndex}][cau_tra_loi_dung]" value="1" min="1" max="4">
            `;
            questionList.appendChild(newBlock);
        });
        questionList.addEventListener('click', function(e) {
            if (!e.target.classList.contains('btn-delete-question')) return;
            const btn = e.target;
            const questionBlock = btn.closest('.question-edit-block');
            const questionId = btn.dataset.id;
            if (questionId !== 'new') {
                const currentDeletedIds = deleteInput.value.split(',').filter(Boolean);
                currentDeletedIds.push(questionId);
                deleteInput.value = currentDeletedIds.join(',');
            }
            questionBlock.remove();
        });
        
        // [THÊM MỚI] JavaScript cho Rào cản Ngày giờ (y hệt Create.php)
        const startTimeInput = document.getElementById('thoi_gian_bat_dau');
        const endTimeInput = document.getElementById('thoi_gian_ket_thuc');
        startTimeInput.addEventListener('change', function() {
            const startTime = startTimeInput.value;
            if (startTime) {
                endTimeInput.min = startTime;
                if (endTimeInput.value && endTimeInput.value < startTime) {
                    endTimeInput.value = '';
                }
            } else {
                endTimeInput.min = '';
            }
        });
        endTimeInput.addEventListener('change', function() {
            if (startTimeInput.value && endTimeInput.value && endTimeInput.value < startTimeInput.value) {
                alert('Lỗi: Ngày kết thúc không được sớm hơn ngày bắt đầu.');
                endTimeInput.value = '';
            }
        });
    </script>
    
    <?php else: // ---- SCRIPT CHO HỌC SINH ---- ?>
    <script>
        // (JavaScript cho Timer giữ nguyên)
        const timerDisplay = document.getElementById('quiz-timer');
        const form = document.getElementById('quiz-form');
        let durationInSeconds = <?php echo (int)$de_thi['thoi_luong_phut'] * 60; ?>;
        const timer = setInterval(function() {
            let minutes = Math.floor(durationInSeconds / 60);
            let seconds = durationInSeconds % 60;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            timerDisplay.textContent = minutes + ':' + seconds;
            if (--durationInSeconds < 0) {
                clearInterval(timer);
                alert('Đã hết thời gian làm bài! Bài của bạn sẽ được nộp tự động.');
                form.submit();
            }
        }, 1000); 
    </script>
    <?php endif; ?>
    
</body>
</html>