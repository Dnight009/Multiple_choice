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

// 3. LẤY TỪ KHÓA TÌM KIẾM
$query = $_GET['q'] ?? '';
if (empty($query)) {
    echo json_encode([]); // Trả về mảng rỗng nếu không tìm gì
    exit;
}
$search_term = "%" . $query . "%";

// 4. SQL TÌM KIẾM
// Chỉ tìm Học sinh (quyen=2) và Giáo viên (quyen=3)
// KHÔNG tìm Admin (quyen=1) và KHÔNG tìm chính mình
$sql_search = "SELECT IDACC, username, ho_ten 
               FROM ACCOUNT 
               WHERE (quyen = '2' OR quyen = '3') 
                 AND IDACC != ?
                 AND (username LIKE ? OR ho_ten LIKE ?)
               LIMIT 10"; // Giới hạn 10 kết quả

$stmt_search = $conn->prepare($sql_search);
$stmt_search->bind_param("iss", $my_id, $search_term, $search_term);
$stmt_search->execute();
$result = $stmt_search->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

// 5. TRẢ VỀ JSON
header('Content-Type: application/json');
echo json_encode($users);
$conn->close();
?>