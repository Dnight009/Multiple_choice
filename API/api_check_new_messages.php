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

// 3. LẤY DỮ LIỆU TỪ JS
// ID phòng chat đang xem
$chat_id = (int)($_GET['chat_id'] ?? 0); 
// ID của tin nhắn cuối cùng mà bạn thấy
$last_message_id = (int)($_GET['last_id'] ?? 0); 

if ($chat_id === 0) {
    echo json_encode([]); // Không làm gì nếu không có chat_id
    exit;
}

// 4. KIỂM TRA QUYỀN (Copy từ file get_messages)
$stmt_check = $conn->prepare("SELECT ID_CP FROM CONVERSATION_PARTICIPANTS WHERE ID_CONVERSATION = ? AND IDACC = ?");
$stmt_check->bind_param("ii", $chat_id, $my_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Bạn không có quyền xem.']);
    exit;
}
$stmt_check->close();

// 5. CÂU SQL QUAN TRỌNG NHẤT
// Tìm tất cả tin nhắn trong phòng này
// CÓ ID LỚN HƠN ID cuối cùng bạn thấy
$sql_new = "SELECT M.ID_MESSAGE, M.message_content, M.sent_at, A.username, M.IDACC_sender
            FROM MESSAGES AS M
            JOIN ACCOUNT AS A ON M.IDACC_sender = A.IDACC
            WHERE M.ID_CONVERSATION = ? AND M.ID_MESSAGE > ?
            ORDER BY M.sent_at ASC";

$stmt_new = $conn->prepare($sql_new);
$stmt_new->bind_param("ii", $chat_id, $last_message_id);
$stmt_new->execute();
$result = $stmt_new->get_result();
$new_messages = $result->fetch_all(MYSQLI_ASSOC);

// 6. TRẢ VỀ JSON (sẽ là mảng rỗng [] nếu không có tin mới)
header('Content-Type: application/json');
echo json_encode($new_messages);
$conn->close();
?>