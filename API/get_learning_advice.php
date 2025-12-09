<?php
// 1. KHỞI ĐỘNG
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// 2. BẢO MẬT
if (!isset($_SESSION['IDACC'])) {
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit;
}
$user_id = $_SESSION['IDACC'];

// 3. KẾT NỐI CSDL
require_once __DIR__ . '/../Check/Connect.php';

// -------------------------------------------------------
// BƯỚC A: LẤY LỊCH SỬ LÀM BÀI (KÈM NỘI DUNG CÂU HỎI)
// -------------------------------------------------------
$sql_history = "SELECT K.ID_TD, T.ten_de, K.diem, T.trinh_do 
                FROM ketqua_lambai K
                JOIN TEN_DE T ON K.ID_TD = T.ID_TD
                WHERE K.IDACC = ?
                ORDER BY K.thoi_gian_nop_bai DESC
                LIMIT 3"; // Chỉ lấy 3 bài gần nhất để phân tích sâu

$stmt = $conn->prepare($sql_history);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_history = $stmt->get_result();

$history_text = "";
$taken_quiz_ids = []; // Lưu ID các đề đã làm để loại trừ khi gợi ý

if ($result_history->num_rows > 0) {
    while ($row = $result_history->fetch_assoc()) {
        $taken_quiz_ids[] = $row['ID_TD'];
        
        // Với mỗi bài đã làm, lấy ngẫu nhiên 3 câu hỏi để AI hiểu "nội dung" bài đó là gì
        // (Ví dụ: Thấy câu hỏi về tích phân -> AI biết đây là bài Toán Giải Tích)
        $sql_sample_questions = "SELECT cau_hoi FROM CAU_HOI WHERE ID_TD = " . $row['ID_TD'] . " LIMIT 3";
        $res_questions = $conn->query($sql_sample_questions);
        $questions_sample = [];
        while ($q = $res_questions->fetch_assoc()) {
            // Cắt ngắn câu hỏi nếu quá dài để tiết kiệm token
            $questions_sample[] = mb_substr(strip_tags($q['cau_hoi']), 0, 100) . "...";
        }
        $questions_str = implode("; ", $questions_sample);

        $history_text .= "- Bài đã làm: '{$row['ten_de']}' (Mức độ: {$row['trinh_do']}). Điểm: {$row['diem']}/10.\n";
        $history_text .= "  (Nội dung bài thi gồm các câu như: $questions_str)\n";
    }
} else {
    echo json_encode(['advice' => 'Chào bạn mới! Bạn chưa làm bài nào cả. Hãy thử chọn một đề thi trong danh sách để bắt đầu hành trình học tập nhé.']);
    exit;
}
$stmt->close();

// -------------------------------------------------------
// BƯỚC B: LẤY DANH SÁCH ĐỀ THI ĐỂ GỢI Ý (CHƯA LÀM)
// -------------------------------------------------------
$taken_ids_str = implode(',', $taken_quiz_ids);
if (empty($taken_ids_str)) $taken_ids_str = '0';

// Lấy 5 đề thi ngẫu nhiên mà học sinh CHƯA làm
$sql_suggest = "SELECT ten_de, trinh_do 
                FROM TEN_DE 
                WHERE ID_TD NOT IN ($taken_ids_str) 
                ORDER BY RAND() 
                LIMIT 5";
$result_suggest = $conn->query($sql_suggest);

$suggestion_list_text = "";
if ($result_suggest->num_rows > 0) {
    while ($row = $result_suggest->fetch_assoc()) {
        $suggestion_list_text .= "- {$row['ten_de']} ({$row['trinh_do']})\n";
    }
}

$conn->close();

$api_key = "AIzaSyBUmLMLSBIuTDqdsCMJrAG6RxhBWWiS-8M"; 
// Đổi sang gemini-1.5-flash chạy cho ổn định và hạn mức cao hơn
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key='.$api_key;

$prompt = "Bạn là một cố vấn học tập AI thân thiện. Dưới đây là dữ liệu học tập của học sinh:\n\n" .
          "1. LỊCH SỬ LÀM BÀI GẦN ĐÂY:\n" . $history_text . "\n" .
          "2. DANH SÁCH CÁC ĐỀ THI CÓ SẴN TRÊN HỆ THỐNG:\n" . $suggestion_list_text . "\n" .
          "YÊU CẦU:\n" .
          "- Dựa vào điểm số và 'nội dung câu hỏi' ở mục 1, hãy phân tích ngắn gọn điểm mạnh/yếu của học sinh (ví dụ: yếu toán hình, tốt tiếng anh...).\n" .
          "- Từ danh sách mục 2, hãy ĐỀ XUẤT 2-3 bài thi cụ thể mà học sinh nên làm tiếp để cải thiện.\n" .
          "- Văn phong khích lệ, ngắn gọn (dưới 150 từ), xưng hô là 'bạn'.";

$data = [
    "contents" => [[
        "parts" => [[ "text" => $prompt ]]
    ]]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Tắt SSL cho XAMPP

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Lỗi kết nối: ' . curl_error($ch)]);
    exit;
}
curl_close($ch);

$result_ai = json_decode($response, true);

if (isset($result_ai['error'])) {
    echo json_encode(['error' => 'AI Error: ' . $result_ai['error']['message']]);
    exit;
}

$advice = $result_ai['candidates'][0]['content']['parts'][0]['text'] ?? "AI đang suy nghĩ...";

echo json_encode(['advice' => $advice]);
?>