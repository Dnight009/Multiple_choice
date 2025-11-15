<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1. BẢO MẬT: Phải đăng nhập
if (!isset($_SESSION['IDACC'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bạn chưa đăng nhập.']);
    exit;
}
$my_id = (int)$_SESSION['IDACC'];

// 2. KẾT NỐI CSDL
require_once __DIR__ . '/../Check/Connect.php';

// 3. LẤY ID CHAT (từ URL, ví dụ: ?id=123)
if (!isset($_GET['chat_id']) || !is_numeric($_GET['chat_id'])) {
    http_response_code(400); // Lỗi 400: Yêu cầu sai
    echo json_encode(['error' => 'ID cuộc trò chuyện không hợp lệ.']);
    exit;
}
$chat_id = (int)$_GET['chat_id'];

// 4. BẢO MẬT: KIỂM TRA XEM TÔI CÓ QUYỀN TRONG PHÒNG NÀY KHÔNG
$stmt_check = $conn->prepare("SELECT ID_CP FROM CONVERSATION_PARTICIPANTS WHERE ID_CONVERSATION = ? AND IDACC = ?");
$stmt_check->bind_param("ii", $chat_id, $my_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    http_response_code(403); // Lỗi 403: Bị cấm
    echo json_encode(['error' => 'Bạn không có quyền xem cuộc trò chuyện này.']);
    exit;
}
$stmt_check->close();

// 5. LẤY TIN NHẮN (50 tin nhắn cuối)
$sql_messages = "SELECT M.ID_MESSAGE,M.message_content, M.sent_at, A.username, M.IDACC_sender
                 FROM MESSAGES AS M
                 JOIN ACCOUNT AS A ON M.IDACC_sender = A.IDACC
                 WHERE M.ID_CONVERSATION = ?
                 ORDER BY M.sent_at ASC
                 LIMIT 50"; // (Bạn có thể thêm phân trang sau)
$stmt_msg = $conn->prepare($sql_messages);
$stmt_msg->bind_param("i", $chat_id);
$stmt_msg->execute();
$result_messages = $stmt_msg->get_result();
$messages = $result_messages->fetch_all(MYSQLI_ASSOC);

// 6. TRẢ VỀ JSON
header('Content-Type: application/json');
echo json_encode($messages);
$conn->close();
?>