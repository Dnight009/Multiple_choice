<?php
header("Content-Type: application/json");

$api_key = "AIzaSyBUmLMLSBIuTDqdsCMJrAG6RxhBWWiS-8M"; // 🔑 Đổi thành API key bạn lấy ở Google AI Studio
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key='.$api_key;
// Nhận dữ liệu từ frontend
$input = json_decode(file_get_contents("php://input"), true);
$userText = $input['message'] ?? '';

$data = [
    "contents" => [[
        "parts" => [[ "text" => $userText ]]
    ]]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// --- PHẦN GỠ LỖI ---
// Kiểm tra xem Google API có trả về thông báo lỗi không
if (isset($result['error'])) {
    // Nếu có, gửi thông báo lỗi cụ thể về frontend
    $reply = "Lỗi từ API Google: " . $result['error']['message'];
} else {
    // Nếu không có lỗi, cố gắng lấy câu trả lời
    $reply = $result['candidates'][0]['content']['parts'][0]['text'] 
             ?? "Xin lỗi, mình không nhận được phản hồi từ AI. (Không có lỗi cụ thể)";
}
// --- KẾT THÚC PHẦN GỠ LỖI ---

echo json_encode(["reply" => $reply]);
