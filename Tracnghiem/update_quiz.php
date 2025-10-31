<?php
// 1. KHỞI ĐỘNG VÀ BẢO MẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    die("Bạn không có quyền.");
}
require_once __DIR__ . '/../Check/Connect.php';

$teacher_id = $_SESSION['IDACC'];

// 2. XỬ LÝ KHI GIÁO VIÊN "LƯU"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_de_thi'])) {
    
    $de_thi_id = (int)$_POST['id_de_thi'];
    
    // 3. KIỂM TRA QUYỀN SỞ HỮU (An toàn)
    $stmt_check = $conn->prepare("SELECT IDACC FROM TEN_DE WHERE ID_TD = ? AND IDACC = ?");
    $stmt_check->bind_param("ii", $de_thi_id, $teacher_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        die("Bạn không phải chủ sở hữu của đề thi này.");
    }
    $stmt_check->close();

    $conn->begin_transaction();
    try {
        
        // 4. CẬP NHẬT CÁC LỚP ĐƯỢC GÁN
        // Xóa tất cả các gán ghép cũ
        $stmt_clear_lop = $conn->prepare("DELETE FROM DE_THI_LOP WHERE ID_TD = ?");
        $stmt_clear_lop->bind_param("i", $de_thi_id);
        $stmt_clear_lop->execute();
        $stmt_clear_lop->close();
        
        // Thêm các gán ghép mới
        if (isset($_POST['assigned_classes']) && is_array($_POST['assigned_classes'])) {
            $sql_assign = "INSERT INTO DE_THI_LOP (ID_TD, ID_CLASS) VALUES (?, ?)";
            $stmt_assign = $conn->prepare($sql_assign);
            foreach ($_POST['assigned_classes'] as $class_id) {
                if (!empty($class_id)) {
                    $stmt_assign->bind_param("ii", $de_thi_id, $class_id);
                    $stmt_assign->execute();
                }
            }
            $stmt_assign->close();
        }

        // 5. CẬP NHẬT CÁC CÂU HỎI (Câu đã có)
        if (isset($_POST['question']) && is_array($_POST['question'])) {
            $stmt_update_q = $conn->prepare("UPDATE CAU_HOI SET 
                                            cau_hoi = ?, 
                                            dap_an_1 = ?, 
                                            dap_an_2 = ?, 
                                            dap_an_3 = ?, 
                                            dap_an_4 = ?, 
                                            cau_tra_loi_dung = ? 
                                            WHERE ID_CH = ? AND ID_TD = ?");
                                            
            foreach ($_POST['question'] as $id_ch => $q) {
                // $id_ch là số (ví dụ '123') -> đây là câu hỏi cũ
                if (is_numeric($id_ch)) {
                    $stmt_update_q->bind_param("sssssiii", 
                        $q['cau_hoi'], 
                        $q['dap_an_1'], $q['dap_an_2'], $q['dap_an_3'], $q['dap_an_4'],
                        $q['cau_tra_loi_dung'],
                        $id_ch,
                        $de_thi_id
                    );
                    $stmt_update_q->execute();
                }
            }
            $stmt_update_q->close();
        }

        // 6. THÊM CÂU HỎI MỚI
        if (isset($_POST['new_question']) && is_array($_POST['new_question'])) {
            $stmt_insert_q = $conn->prepare("INSERT INTO CAU_HOI 
                                            (ID_TD, cau_hoi, dap_an_1, dap_an_2, dap_an_3, dap_an_4, cau_tra_loi_dung) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($_POST['new_question'] as $q) {
                // Chỉ thêm nếu câu hỏi có nội dung
                if (!empty(trim($q['cau_hoi']))) {
                    $stmt_insert_q->bind_param("isssssi", 
                        $de_thi_id,
                        $q['cau_hoi'], 
                        $q['dap_an_1'], $q['dap_an_2'], $q['dap_an_3'], $q['dap_an_4'],
                        $q['cau_tra_loi_dung']
                    );
                    $stmt_insert_q->execute();
                }
            }
            $stmt_insert_q->close();
        }
        
        // 7. XÓA CÁC CÂU HỎI
        if (isset($_POST['delete_question_ids']) && !empty($_POST['delete_question_ids'])) {
            $ids_to_delete = $_POST['delete_question_ids']; // Đây là chuỗi '12,13,14'
            // Chạy SQL (Cẩn thận: Chỉ xóa nếu nó thuộc đề thi này)
            $sql_delete = "DELETE FROM CAU_HOI WHERE ID_TD = ? AND ID_CH IN ($ids_to_delete)";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $de_thi_id);
            $stmt_delete->execute();
            $stmt_delete->close();
        }

        // 8. COMMIT VÀ CHUYỂN HƯỚNG
        $conn->commit();
        $_SESSION['flash_message'] = "Cập nhật đề thi thành công!";
        $_SESSION['flash_type'] = "success";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash_message'] = "Lỗi! Cập nhật thất bại: " . $e->getMessage();
        $_SESSION['flash_type'] = "error";
    }

    $conn->close();
    header("Location: view_quiz_details.php?id_de=" . $de_thi_id);
    exit;

} else {
    die("Truy cập không hợp lệ.");
}
?>