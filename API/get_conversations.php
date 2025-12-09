<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1. BẢO MẬT: Phải đăng nhập
if (!isset($_SESSION['IDACC'])) {
    http_response_code(401); // Lỗi 401: Chưa xác thực
    echo json_encode(['error' => 'Bạn chưa đăng nhập.']);
    exit;
}
$my_id = (int)$_SESSION['IDACC'];

// 2. KẾT NỐI CSDL
require_once __DIR__ . '/../Check/Connect.php'; 

$sql_my_chats = "SELECT 
                    C.ID_CONVERSATION, 
                    C.is_group,
                    -- Lấy tên hiển thị (tên nhóm hoặc tên người kia)
                    (CASE 
                        WHEN C.is_group = 1 THEN C.conversation_name
                        WHEN C.is_group = 0 THEN (
                            SELECT A.username 
                            FROM CONVERSATION_PARTICIPANTS CP 
                            JOIN ACCOUNT A ON CP.IDACC = A.IDACC 
                            WHERE CP.ID_CONVERSATION = C.ID_CONVERSATION AND CP.IDACC != ?
                        )
                    END) AS display_name
                FROM 
                    CONVERSATIONS C
                JOIN 
                    CONVERSATION_PARTICIPANTS P ON C.ID_CONVERSATION = P.ID_CONVERSATION
                WHERE 
                    P.IDACC = ?
                GROUP BY 
                    C.ID_CONVERSATION
                ORDER BY 
                    C.created_at DESC";

$stmt_chats = $conn->prepare($sql_my_chats);
// "ii" = 2 tham số $my_id
$stmt_chats->bind_param("ii", $my_id, $my_id); 
$stmt_chats->execute();
$result_chats = $stmt_chats->get_result();

$conversations = [];
if ($result_chats->num_rows > 0) {
    $conversations = $result_chats->fetch_all(MYSQLI_ASSOC);
}

// 4. TRẢ VỀ DẠNG JSON
header('Content-Type: application/json');
echo json_encode($conversations);
$conn->close();
?>