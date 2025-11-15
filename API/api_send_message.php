<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1. BẢO MẬT
if (!isset($_SESSION['IDACC'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bạn chưa đăng nhập.']);
    exit;
}
$my_id = (int)$_SESSION['IDACC'];

// 2. KẾT NỐI CSDL
require_once __DIR__ . '/../Check/Connect.php';

// 3. LẤY DỮ LIỆU JSON (vì chúng ta sẽ gửi bằng fetch)
$input = json_decode(file_get_contents("php://input"), true);
$chat_id = (int)($input['chat_id'] ?? 0);
$content = trim($input['message_content'] ?? '');

if (empty($chat_id) || empty($content)) {
    http_response_code(400);
    echo json_encode(['error' => 'Dữ liệu không hợp lệ.']);
    exit;
}

// 4. (Tùy chọn, nên có) KIỂM TRA XEM TÔI CÓ QUYỀN TRONG PHÒNG NÀY KHÔNG
// (Code này giống hệt file get_messages.php)
$stmt_check = $conn->prepare("SELECT ID_CP FROM CONVERSATION_PARTICIPANTS WHERE ID_CONVERSATION = ? AND IDACC = ?");
$stmt_check->bind_param("ii", $chat_id, $my_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Bạn không có quyền gửi tin nhắn vào phòng này.']);
    exit;
}
$stmt_check->close();

// 5. LƯU TIN NHẮN
$sql = "INSERT INTO MESSAGES (ID_CONVERSATION, IDACC_sender, message_content) 
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $chat_id, $my_id, $content);

if ($stmt->execute()) {
    // 6. TRẢ VỀ THÀNH CÔNG
    // (Chúng ta có thể trả về chính tin nhắn đó, nhưng 'status' là đủ)
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
}
$stmt->close();
$conn->close();
?>