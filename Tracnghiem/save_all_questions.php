<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Sửa đường dẫn Connect.php cho đúng
require_once __DIR__ . '/../Check/Connect.php'; 

// 2. KIỂM TRA BẢO MẬT
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    die("Bạn không có quyền truy cập chức năng này.");
}

// 3. CHỈ CHẠY KHI FORM ĐƯỢC GỬI ĐI
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $conn->begin_transaction();

    try {
        // --- VIỆC 1: LƯU THÔNG TIN BỘ ĐỀ VÀO BẢNG TEN_DE ---
        
        $ten_bo_de = $_POST['tenbode'];
        $trinh_do = $_POST['trinhdo'];
        $lop_hoc = $_POST['lophoc']; 
        $id_giao_vien = $_SESSION['IDACC'];

        $thoi_luong = 45; // Mặc định là 45
        if (isset($_POST['thoi_luong_phut']) && !empty($_POST['thoi_luong_phut'])) {
            $thoi_luong = (int)$_POST['thoi_luong_phut'];
        }

        // [THÊM MỚI] Lấy ngày giờ (NULL nếu rỗng)
        // Nếu chuỗi rỗng, gán là NULL, nếu không, giữ nguyên giá trị
        $thoi_gian_bat_dau = !empty($_POST['thoi_gian_bat_dau']) ? $_POST['thoi_gian_bat_dau'] : NULL;
        $thoi_gian_ket_thuc = !empty($_POST['thoi_gian_ket_thuc']) ? $_POST['thoi_gian_ket_thuc'] : NULL;

        // [SỬA LẠI] Thêm 'thoi_gian_bat_dau', 'thoi_gian_ket_thuc' vào SQL
        $sql_de = "INSERT INTO TEN_DE (ten_de, trinh_do, lop_hoc, IDACC, thoi_luong_phut, thoi_gian_bat_dau, thoi_gian_ket_thuc) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_de = $conn->prepare($sql_de);
        // [SỬA LẠI] Thêm "ss" (string, string) cho 2 biến ngày giờ
        $stmt_de->bind_param("ssiiiss", 
            $ten_bo_de, 
            $trinh_do, 
            $lop_hoc, 
            $id_giao_vien, 
            $thoi_luong,
            $thoi_gian_bat_dau, // Biến mới
            $thoi_gian_ket_thuc // Biến mới
        );
        $stmt_de->execute();
        
        $id_de_vua_tao = $conn->insert_id;
        $stmt_de->close();

        // (Code gán lớp giữ nguyên)
        if (isset($_POST['assigned_classes']) && is_array($_POST['assigned_classes'])) {
            $sql_assign = "INSERT INTO DE_THI_LOP (ID_TD, ID_CLASS) VALUES (?, ?)";
            $stmt_assign = $conn->prepare($sql_assign);
            foreach ($_POST['assigned_classes'] as $class_id) {
                if (!empty($class_id)) {
                    $stmt_assign->bind_param("ii", $id_de_vua_tao, $class_id);
                    $stmt_assign->execute();
                }
            }
            $stmt_assign->close();
        }
        
        // (Code lưu câu hỏi giữ nguyên)
        if (isset($_POST['question']) && is_array($_POST['question'])) {
            $sql_cauhoi = "INSERT INTO CAU_HOI (cau_hoi, dap_an_1, dap_an_2, dap_an_3, dap_an_4, cau_tra_loi_dung, ID_TD) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_cauhoi = $conn->prepare($sql_cauhoi);
            
            foreach ($_POST['question'] as $q) {
                $cau_hoi_text = $q['cau_hoi'];
                $da_1 = $q['dap_an_1'];
                $da_2 = $q['dap_an_2'];
                $da_3 = $q['dap_an_3'];
                $da_4 = $q['dap_an_4'];
                $dap_an_dung_so = (int)$q['dap_an_dung'];

                // Sửa lại: Chỉ lưu nếu câu hỏi có nội dung (tránh lỗi)
                if(!empty(trim($cau_hoi_text))) {
                    $stmt_cauhoi->bind_param("sssssii", 
                        $cau_hoi_text, 
                        $da_1, $da_2, $da_3, $da_4, 
                        $dap_an_dung_so, 
                        $id_de_vua_tao
                    );
                    $stmt_cauhoi->execute();
                }
            }
            $stmt_cauhoi->close();
        }

        // Cam kết (commit)
        $conn->commit();

        // Chuyển hướng
        header('Location: view_quiz_details.php?id_de=' . $id_de_vua_tao);
        exit; 

    } catch (Exception $e) {
        $conn->rollback();
        echo "Có lỗi xảy ra, không thể lưu bộ đề: " . $e->getMessage();
    }

    $conn->close();

} else {
    echo "Phương thức truy cập không hợp lệ.";
}
?>