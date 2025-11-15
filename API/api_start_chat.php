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

// 3. LẤY ID NGƯỜI KIA (từ POST)
$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['other_user_id']) || !is_numeric($input['other_user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID người dùng không hợp lệ.']);
    exit;
}
$other_user_id = (int)$input['other_user_id'];

$conn->begin_transaction();
try {
    // 4. KIỂM TRA XEM CHAT 1-với-1 ĐÃ TỒN TẠI CHƯA?
    // (Đây là câu SQL phức tạp nhất, dùng để tìm 1 phòng chat có 2 người)
    $sql_find = "SELECT P1.ID_CONVERSATION
                 FROM CONVERSATION_PARTICIPANTS AS P1
                 JOIN CONVERSATION_PARTICIPANTS AS P2 ON P1.ID_CONVERSATION = P2.ID_CONVERSATION
                 JOIN CONVERSATIONS AS C ON P1.ID_CONVERSATION = C.ID_CONVERSATION
                 WHERE
                     C.is_group = 0
                     AND P1.IDACC = ?
                     AND P2.IDACC = ?";
    $stmt_find = $conn->prepare($sql_find);
    $stmt_find->bind_param("ii", $my_id, $other_user_id);
    $stmt_find->execute();
    $result_find = $stmt_find->get_result();

    if ($result_find->num_rows > 0) {
        // 5. NẾU TÌM THẤY: Trả về ID phòng chat cũ
        $existing_chat = $result_find->fetch_assoc();
        $chat_id = $existing_chat['ID_CONVERSATION'];
    } else {
        // 6. NẾU KHÔNG TÌM THẤY: Tạo phòng chat mới
        
        // B1: Tạo phòng chat
        $stmt_create_conv = $conn->prepare("INSERT INTO CONVERSATIONS (is_group) VALUES (0)");
        $stmt_create_conv->execute();
        $chat_id = $conn->insert_id; // Lấy ID phòng vừa tạo
        $stmt_create_conv->close();

        // B2: Thêm 2 người vào phòng
        $stmt_add_users = $conn->prepare("INSERT INTO CONVERSATION_PARTICIPANTS (ID_CONVERSATION, IDACC) VALUES (?, ?), (?, ?)");
        $stmt_add_users->bind_param("iiii", $chat_id, $my_id, $chat_id, $other_user_id);
        $stmt_add_users->execute();
        $stmt_add_users->close();
    }
    
    $conn->commit();
    
    // 7. TRẢ VỀ JSON (ID phòng chat và tên người kia)
    // Lấy tên người kia để hiển thị
    $stmt_name = $conn->prepare("SELECT username FROM ACCOUNT WHERE IDACC = ?");
    $stmt_name->bind_param("i", $other_user_id);
    $stmt_name->execute();
    $chat_name = $stmt_name->get_result()->fetch_assoc()['username'];
    
    header('Content-Type: application/json');
    echo json_encode([
        'chat_id' => $chat_id,
        'chat_name' => $chat_name
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500); // Lỗi 500: Lỗi server
    echo json_encode(['error' => $e->getMessage()]);
}
$conn->close();
?>