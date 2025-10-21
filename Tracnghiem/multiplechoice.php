<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Giả định các file này ở thư mục gốc (lên 1 cấp)
require_once __DIR__ . '/../Check/Connect.php'; // Sửa theo đường dẫn của bạn
require_once __DIR__ . '/../vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\IOFactory;

// 2. CHUẨN BỊ BIẾN
$questions_from_excel = []; 
$ten_bo_de = "";
$trinh_do = "";
$lop_hoc = ""; // Sẽ lấy từ $_POST

// 3. KIỂM TRA NẾU CÓ FILE TỪ TRANG 'Create.php' GỬI QUA
// Dùng isset($_POST['tenbode']) để kiểm tra xem có phải từ form kia gửi qua không
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tenbode'])) {
    
    // Lấy thông tin đề từ form Create.php
    $ten_bo_de = $_POST['tenbode'];
    $trinh_do = $_POST['trinhdo'];
    
    // !!! SỬA LỖI QUAN TRỌNG: Phải lấy 'lophoc', không phải 'monhoc'
    if(isset($_POST['lophoc'])) {
        $lop_hoc = $_POST['lophoc'];
    }

    // Xử lý đọc file Excel (chỉ khi file được tải lên)
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        try {
            $excelFilePath = $_FILES['file']['tmp_name'];
            $spreadsheet = IOFactory::load($excelFilePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestDataRow();

            // Lặp từ dòng 2 (bỏ qua tiêu đề)
            for ($row = 2; $row <= $highestRow; $row++) {
                $cau_hoi  = $worksheet->getCell('A' . $row)->getValue();
                
                // Chỉ thêm nếu câu hỏi có nội dung
                if (!empty($cau_hoi)) {
                    $questions_from_excel[] = [
                        'cau_hoi'  => $cau_hoi,
                        'dap_an_1' => $worksheet->getCell('B' . $row)->getValue(),
                        'dap_an_2' => $worksheet->getCell('C' . $row)->getValue(),
                        'dap_an_3' => $worksheet->getCell('D' . $row)->getValue(),
                        'dap_an_4' => $worksheet->getCell('E' . $row)->getValue(),
                    ];
                }
            }
        } catch (Exception $e) {
            die("Lỗi khi đọc file Excel: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Review Câu Hỏi</title>
    <style>
    body { 
        font-family: Arial, sans-serif; 
        background: #f0f2f5; 
        display: flex; 
        justify-content: center; 
        padding: 40px; 
    }
    .container { 
        width: 900px; 
        background: #fff; 
        padding: 20px; 
        border-radius: 8px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
    }
    .question-form-block { 
        background: #79e0f2;
        border-radius: 15px; 
        padding: 20px; 
        margin-bottom: 20px; 
    }
    .question-form-block label { 
        background: #b3b3e6;
        color: #333;
        font-weight: bold;
        border-radius: 20px;
        padding: 10px 15px;
        width: 120px; 
        display: inline-block;
        text-align: center;
        margin-right: 10px;
    }
    .question-form-block input[type="text"] { 
        background: #b3b3e6;
        color: #333;
        border: none;
        border-radius: 20px;
        padding: 10px 15px;
        font-size: 15px;
    }
    .full-width { 
        width: calc(100% - 30px); /* Điều chỉnh lại cho đúng */
    }
    .input-wrapper { /* Thêm 1 div bọc input câu hỏi */
        padding-left: 135px; /* Đẩy input câu hỏi sang phải */
        margin-bottom: 10px;
    }
    .half-width { 
        width: calc(50% - 150px); 
    }
    .answer-row { 
        margin-top: 10px; 
        display: flex;
        align-items: center;
    }
    .answer-row label:nth-of-type(2) {
        margin-left: 10px;
    }
    .nav-buttons { 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        margin-top: 20px; 
    }
    .nav-buttons button, .add-btn {
        padding: 10px 25px;
        border: none;
        cursor: pointer;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
    }
    .nav-buttons button[type="submit"],
    .nav-buttons button[type="button"] {
        background: #b3e5fc;
        color: #333;
    }
    .add-btn {
        background: #d3d3d3;
        color: white;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        padding: 0;
        font-size: 24px;
        line-height: 50px;
        text-align: center;
    }
    </style>
</head>
<body>
    <div class="container">
        
        <form action="save_all_questions.php" method="POST">
            
            <h2>Xem trước và Sửa đổi câu hỏi</h2>
            <p><strong>Tên đề:</strong> <?php echo htmlspecialchars($ten_bo_de); ?></p>
            
            <input type="hidden" name="tenbode" value="<?php echo htmlspecialchars($ten_bo_de); ?>">
            <input type="hidden" name="trinhdo" value="<?php echo htmlspecialchars($trinh_do); ?>">
            <input type="hidden" name="lophoc" value="<?php echo htmlspecialchars($lop_hoc); ?>">

            <div id="question-list-container">
                
                <?php
                // !!! SỬA LỖI: Chỉ dùng 1 khối if/else (xóa khối lặp thừa)
                if (!empty($questions_from_excel)):
                    foreach ($questions_from_excel as $index => $q):
                ?>
                        <div class="question-form-block">
                            <div class="input-wrapper"> <input type="text" class="full-width" id="q-<?php echo $index; ?>-text" 
                                       name="question[<?php echo $index; ?>][cau_hoi]" 
                                       value="<?php echo htmlspecialchars($q['cau_hoi']); ?>" required>
                            </div>
                            <div class="answer-row">
                                <label for="q-<?php echo $index; ?>-a">A</label>
                                <input type="text" class="half-width" id="q-<?php echo $index; ?>-a"
                                       name="question[<?php echo $index; ?>][dap_an_1]" 
                                       value="<?php echo htmlspecialchars($q['dap_an_1']); ?>" required>
                                
                                <label for="q-<?php echo $index; ?>-b">B</label>
                                <input type="text" class="half-width" id="q-<?php echo $index; ?>-b"
                                       name="question[<?php echo $index; ?>][dap_an_2]" 
                                       value="<?php echo htmlspecialchars($q['dap_an_2']); ?>" required>
                            </div>
                            <div class="answer-row">
                                <label for="q-<?php echo $index; ?>-c">C</label>
                                <input type="text" class="half-width" id="q-<?php echo $index; ?>-c"
                                       name="question[<?php echo $index; ?>][dap_an_3]" 
                                       value="<?php echo htmlspecialchars($q['dap_an_3']); ?>" required>
                                
                                <label for="q-<?php echo $index; ?>-d">D</label>
                                <input type="text" class="half-width" id="q-<?php echo $index; ?>-d"
                                       name="question[<?php echo $index; ?>][dap_an_4]" 
                                       value="<?php echo htmlspecialchars($q['dap_an_4']); ?>" required>
                            </div>
                            <div class="answer-row">
                                <label for="q-<?php echo $index; ?>-correct">Đáp án đúng</label>
                                <input type="text" class="half-width" id="q-<?php echo $index; ?>-correct"
                                       name="question[<?php echo $index; ?>][dap_an_dung]" 
                                       value="4" required>
                            </div>
                        </div>
                <?php
                    endforeach;
                else:
                ?>
                    <div class="question-form-block">
                        <div class="input-wrapper">
                            <input type="text" class="full-width" id="q-0-text" 
                                   name="question[0][cau_hoi]" 
                                   placeholder="Nhập nội dung câu hỏi..." required>
                        </div>
                        <div class="answer-row">
                            <label for="q-0-a">A</label>
                            <input type="text" class="half-width" id="q-0-a"
                                   name="question[0][dap_an_1]" 
                                   placeholder="Nhập đáp án A" required>
                            
                            <label for="q-0-b">B</label>
                            <input type="text" class="half-width" id="q-0-b"
                                   name="question[0][dap_an_2]" 
                                   placeholder="Nhập đáp án B" required>
                        </div>
                        <div class="answer-row">
                            <label for="q-0-c">C</label>
                            <input type="text" class="half-width" id="q-0-c"
                                   name="question[0][dap_an_3]" 
                                   placeholder="Nhập đáp án C" required>
                            
                            <label for="q-0-d">D</label>
                            <input type="text" class="half-width" id="q-0-d"
                                   name="question[0][dap_an_4]" 
                                   placeholder="Nhập đáp án D" required>
                        </div>
                        <div class="answer-row">
                            <label for="q-0-correct">Đáp án đúng</label>
                            <input type="text" class="half-width" id="q-0-correct"
                                   name="question[0][dap_an_dung]" 
                                   value="4" required>
                        </div>
                    </div>
                <?php
                endif;
                ?>
            </div> <div class="nav-buttons">
                <button type="button" onclick="history.back()">Quay lại</button>
                <button type="button" id="add-question-btn" class="add-btn">+</button>
                
                <button type="submit">Lưu tất cả</button>
            </div>

        </form> </div> <script>
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('add-question-btn');
    const container = document.getElementById('question-list-container');
    let nextQuestionIndex = container.querySelectorAll('.question-form-block').length;

    addBtn.addEventListener('click', function() {
        const newIndex = nextQuestionIndex; 
        
        // Sửa lại HTML cho khối mới (thiếu input câu hỏi)
        const newBlockHTML = `
        <div class="question-form-block">
            <div class="input-wrapper">
                <input type="text" class="full-width" id="q-${newIndex}-text" name="question[${newIndex}][cau_hoi]" placeholder="Nhập câu hỏi mới..." required>
            </div>
            <div class="answer-row">
                <label for="q-${newIndex}-a">A</label>
                <input type="text" class="half-width" id="q-${newIndex}-a" name="question[${newIndex}][dap_an_1]" required>

                <label for="q-${newIndex}-b">B</label>
                <input type="text" class="half-width" id="q-${newIndex}-b" name="question[${newIndex}][dap_an_2]" required>
            </div>
            <div class="answer-row">
                <label for="q-${newIndex}-c">C</label>
                <input type="text" class="half-width" id="q-${newIndex}-c" name="question[${newIndex}][dap_an_3]" required>
            
                <label for="q-${newIndex}-d">D</label>
                <input type="text" class="half-width" id="q-${newIndex}-d" name="question[${newIndex}][dap_an_4]" required>
            </div>
            <div class="answer-row">
                <label for="q-${newIndex}-correct">Đáp án đúng</label>
                <input type="text" class="half-width" id="q-${newIndex}-correct" name="question[${newIndex}][dap_an_dung]" value="1" required>
            </div>
        </div>
        `;

        container.insertAdjacentHTML('beforeend', newBlockHTML);
        nextQuestionIndex++;
    });
});
</script>
</body>
</html>