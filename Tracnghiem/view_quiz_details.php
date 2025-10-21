<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Check/Connect.php'; 

if (!isset($_GET['id_de']) || empty($_GET['id_de'])) {
    echo "Không tìm thấy đề thi.";
    exit;
}

$id_de = $_GET['id_de'];

// --- LẤY THÔNG TIN CHUNG CỦA BỘ ĐỀ ---
$stmt_de = $conn->prepare("SELECT * FROM TEN_DE WHERE ID_TD = ?");
$stmt_de->bind_param("i", $id_de);
$stmt_de->execute();
$result_de = $stmt_de->get_result();

if ($result_de->num_rows === 0) {
    echo "Đề thi không tồn tại.";
    exit;
}
$de_thi = $result_de->fetch_assoc();
$stmt_de->close();


// --- LẤY TẤT CẢ CÂU HỎI CỦA BỘ ĐỀ NÀY ---
$stmt_ch = $conn->prepare("SELECT * FROM CAU_HOI WHERE ID_TD = ? ORDER BY ID_CH ASC");
$stmt_ch->bind_param("i", $id_de);
$stmt_ch->execute();
$result_cauhoi = $stmt_ch->get_result();

$trinh_do_raw = $de_thi['trinh_do']; 
$trinh_do_map = [
    'de'         => 'Dễ',
    'binhthuong' => 'Bình thường',
    'kho'        => 'Khó',
    'nangcao'    => 'Nâng cao',
    'tonghop'    => 'Tổng Hợp'
];
$trinh_do_text = $trinh_do_map[$trinh_do_raw] ?? $trinh_do_raw;


$lop_hoc_raw = $de_thi['lop_hoc'];
$lop_hoc_text = "Lớp " . htmlspecialchars($lop_hoc_raw);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết bộ đề</title>
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
        .quiz-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .question-view-block { 
            background: #79e0f2;
            border-radius: 15px; 
            padding: 20px; 
            margin-bottom: 20px; 
        }
        .box {
            background: #b3b3e6;
            color: #333;
            border-radius: 20px;
            padding: 10px 15px;
            font-size: 15px;
            display: flex;
            align-items: center;
        }
        .question-row {
            margin-bottom: 10px;
        }
        .question-row .box {
            width: 100%;
            font-weight: bold;
        }
        .answer-pair-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .answer-item {
            display: flex;
            width: 49%;
            align-items: center;
        }
        .answer-item .label {
            width: 60px;
            font-weight: bold;
            text-align: center;
            flex-shrink: 0;
        }
        .answer-item .content {
            margin-left: 10px;
            flex-grow: 1;
        }
        .correct-answer-row {
            display: flex;
            align-items: center;
        }
        .correct-answer-row .label {
            width: 150px;
            font-weight: bold;
            flex-shrink: 0;
        }
        .correct-answer-row .content {
            margin-left: 10px;
            max-width: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($de_thi['ten_de']); ?></h1>
            
            <p>
                <strong>Lớp:</strong> <?php echo $lop_hoc_text; ?> | 
                <strong>Trình độ:</strong> <?php echo htmlspecialchars($trinh_do_text); ?>
            </p>
            
        </div>

        <h2>Nội dung các câu hỏi (<?php echo $result_cauhoi->num_rows; ?> câu)</h2>

        <?php
        if ($result_cauhoi->num_rows > 0) {
            $stt = 1;
            while ($row = $result_cauhoi->fetch_assoc()) {
                ?>
                <div class="question-view-block">
                    
                    <div class="question-row">
                        <div class="box">
                            Câu <?php echo $stt++; ?>: <?php echo htmlspecialchars($row['cau_hoi']); ?>
                        </div>
                    </div>
                    
                    <div class="answer-pair-row">
                        <div class="answer-item">
                            <div class="box label">A</div>
                            <div class="box content"><?php echo htmlspecialchars($row['dap_an_1']); ?></div>
                        </div>
                        <div class="answer-item">
                            <div class="box label">B</div>
                            <div class="box content"><?php echo htmlspecialchars($row['dap_an_2']); ?></div>
                        </div>
                    </div>
                    
                    <div class="answer-pair-row">
                        <div class="answer-item">
                            <div class="box label">C</div>
                            <div class="box content"><?php echo htmlspecialchars($row['dap_an_3']); ?></div>
                        </div>
                        <div class="answer-item">
                            <div class="box label">D</div>
                            <div class="box content"><?php echo htmlspecialchars($row['dap_an_4']); ?></div>
                        </div>
                    </div>
                    
                    <?php
                        // Dùng thống nhất với các file khác của bạn
                        if (isset($_SESSION['quyen']) && $_SESSION['quyen'] == '3') { 
                    ?>
                    <div class="correct-answer-row">
                        <div class="box label">Đáp án đúng</div>
                        <div class="box content"><?php echo htmlspecialchars($row['cau_tra_loi_dung']); ?></div>
                    </div>
                    <?php
                    }
                    ?>
                    

                </div>
                <?php
            }
        } else {
            echo "<p>Bộ đề này chưa có câu hỏi nào.</p>";
        }
        
        $stmt_ch->close();
        $conn->close();
        ?>
    </div>
</body>
</html>