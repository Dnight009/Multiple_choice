<?php
// 1. KHỞI ĐỘNG MỌI THỨ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Sửa đường dẫn Connect.php cho đúng (theo code bạn cung cấp)
require_once __DIR__ . '/../Check/Connect.php'; 

// 2. KIỂM TRA BẢO MẬT
// Phải đăng nhập và là giáo viên mới được lưu
if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '2') {
    die("Bạn không có quyền truy cập chức năng này.");
}

// 3. CHỈ CHẠY KHI FORM ĐƯỢC GỬI ĐI (phương thức POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Bắt đầu một giao dịch (transaction)
    // Giúp đảm bảo an toàn: hoặc lưu thành công cả đề VÀ câu hỏi, hoặc không lưu gì cả.
    $conn->begin_transaction();

    try {
        // --- VIỆC 1: LƯU THÔNG TIN BỘ ĐỀ VÀO BẢNG TEN_DE ---
        
        // Lấy dữ liệu từ các trường input "hidden"
        $ten_bo_de = $_POST['tenbode'];
        $trinh_do = $_POST['trinhdo'];
        $lop_hoc = $_POST['lophoc']; // Lấy từ <input type="hidden" name="lophoc">
        $id_giao_vien = $_SESSION['IDACC']; // Lấy ID giáo viên từ session

        // Chuẩn bị câu lệnh SQL
        $sql_de = "INSERT INTO TEN_DE (ten_de, trinh_do, lop_hoc, IDACC) 
                   VALUES (?, ?, ?, ?)";
        
        $stmt_de = $conn->prepare($sql_de);
        // "ssii" = string, string, integer, integer
        $stmt_de->bind_param("ssii", $ten_bo_de, $trinh_do, $lop_hoc, $id_giao_vien);
        $stmt_de->execute();
        
        // Lấy ID của bộ đề VỪA MỚI TẠO (ID_TD)
        $id_de_vua_tao = $conn->insert_id;
        $stmt_de->close();

        // --- VIỆC 2: LƯU TẤT CẢ CÂU HỎI VÀO BẢNG CAU_HOI ---
        
        // Kiểm tra xem có mảng 'question' được gửi lên không
        if (isset($_POST['question']) && is_array($_POST['question'])) {
            
            // Chuẩn bị câu lệnh SQL (chỉ cần chuẩn bị 1 lần)
            $sql_cauhoi = "INSERT INTO CAU_HOI (cau_hoi, dap_an_1, dap_an_2, dap_an_3, dap_an_4, cau_tra_loi_dung, ID_TD) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_cauhoi = $conn->prepare($sql_cauhoi);
            
            // Lặp qua từng câu hỏi mà người dùng gửi lên
            foreach ($_POST['question'] as $q) {
                // $q chính là mảng ['cau_hoi' => '...', 'dap_an_1' => '...', ...]
                $cau_hoi_text = $q['cau_hoi'];
                $da_1 = $q['dap_an_1'];
                $da_2 = $q['dap_an_2'];
                $da_3 = $q['dap_an_3'];
                $da_4 = $q['dap_an_4'];
                $dap_an_dung_so = (int)$q['dap_an_dung']; // Ép kiểu thành số nguyên

                // Gắn dữ liệu mới vào câu lệnh đã chuẩn bị và thực thi
                $stmt_cauhoi->bind_param("sssssii", 
                    $cau_hoi_text, 
                    $da_1, $da_2, $da_3, $da_4, 
                    $dap_an_dung_so, 
                    $id_de_vua_tao // Đây là khóa ngoại liên kết câu hỏi với bộ đề
                );
                $stmt_cauhoi->execute();
            }
            $stmt_cauhoi->close();
        }

        // Nếu mọi thứ chạy đến đây mà không có lỗi,
        // thì cam kết (commit) để lưu vĩnh viễn thay đổi vào CSDL
        $conn->commit();

        // Chuyển hướng người dùng đến trang xem chi tiết (chúng ta đã làm)
        header('Location: view_quiz_details.php?id_de=' . $id_de_vua_tao);
        exit; // Luôn exit sau khi chuyển hướng

    } catch (Exception $e) {
        // Nếu có bất kỳ lỗi nào xảy ra (ví dụ: mất kết nối, sai kiểu dữ liệu...),
        // thì hủy bỏ (rollback) mọi thay đổi
        $conn->rollback();
        echo "Có lỗi xảy ra, không thể lưu bộ đề: " . $e->getMessage();
    }

    // 5. Đóng kết nối
    $conn->close();

} else {
    // Nếu ai đó cố tình gõ địa chỉ file này trên thanh URL
    echo "Phương thức truy cập không hợp lệ.";
}
?>