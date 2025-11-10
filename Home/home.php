<?php
// 1. KẾT NỐI DATABASE
require_once __DIR__ . '/../Check/Connect.php'; 

// 2. CHUẨN BỊ DỮ LIỆU "DỊCH"
$trinh_do_map = [
    'de'         => 'Dễ',
    'binhthuong' => 'Bình thường',
    'kho'        => 'Khó',
    'nangcao'    => 'Nâng cao',
    'tonghop'    => 'Tổng Hợp'
];

// --- [BƯỚC 1] LOGIC LỌC VÀ PHÂN TRANG (ĐÃ VIẾT LẠI) ---

// 1a. Đặt số lượng đề mỗi trang
$items_per_page = 9;

// 1b. Lấy trang hiện tại (mặc định là 1)
$current_page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;

// 1c. Lấy tất cả các giá trị lọc từ URL (nếu có)
$search_ten_de = $_GET['ten_de'] ?? '';
$search_nguoi_tao = $_GET['nguoi_tao'] ?? '';
$selected_trinh_do = $_GET['trinh_do'] ?? 'all';
$selected_lop_hoc = $_GET['lop_hoc'] ?? 'all';
$selected_year = $_GET['year'] ?? 'all';

// 1d. Xây dựng câu lệnh SQL động
$sql_base = "FROM TEN_DE AS T JOIN ACCOUNT AS A ON T.IDACC = A.IDACC";
$where_clauses = []; // Mảng chứa các điều kiện WHERE
$param_types = "";   // Chuỗi chứa kiểu dữ liệu (ví dụ: 'sisi')
$param_values = [];  // Mảng chứa các giá trị

// Thêm điều kiện lọc
if (!empty($search_ten_de)) {
    $where_clauses[] = "T.ten_de LIKE ?";
    $param_types .= "s";
    $param_values[] = "%" . $search_ten_de . "%";
}
if (!empty($search_nguoi_tao)) {
    $where_clauses[] = "A.username LIKE ?"; // Lọc theo tên người tạo
    $param_types .= "s";
    $param_values[] = "%" . $search_nguoi_tao . "%";
}
if ($selected_trinh_do != 'all') {
    $where_clauses[] = "T.trinh_do = ?";
    $param_types .= "s";
    $param_values[] = $selected_trinh_do;
}
if ($selected_lop_hoc != 'all') {
    $where_clauses[] = "T.lop_hoc = ?";
    $param_types .= "i";
    $param_values[] = $selected_lop_hoc;
}
if ($selected_year != 'all') {
    $where_clauses[] = "YEAR(T.ngay_tao) = ?";
    $param_types .= "i";
    $param_values[] = $selected_year;
}

// Ghép các điều kiện WHERE lại
$sql_where = "";
if (!empty($where_clauses)) {
    $sql_where = " WHERE " . implode(" AND ", $where_clauses);
}

// 1e. Lấy TỔNG SỐ ĐỀ THI (đã lọc)
$sql_count = "SELECT COUNT(T.ID_TD) as total " . $sql_base . $sql_where;
$stmt_count = $conn->prepare($sql_count);
if (!empty($param_types)) {
    $stmt_count->bind_param($param_types, ...$param_values);
}
$stmt_count->execute();
$total_items = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_count->close();

// 1f. Tính toán phân trang
$total_pages = ceil($total_items / $items_per_page); 
$offset = ($current_page - 1) * $items_per_page;

// --- [BƯỚC 2] CÂU TRUY VẤN SQL CHÍNH (ĐÃ LỌC) ---

$sql = "SELECT T.*, A.username " . $sql_base . $sql_where . " ORDER BY T.ngay_tao DESC LIMIT ?, ?";

$stmt = $conn->prepare($sql);
// Thêm 2 tham số của LIMIT vào
$all_param_types = $param_types . "ii"; // 'sisi' + 'ii'
$all_param_values = array_merge($param_values, [$offset, $items_per_page]);

if (!empty($all_param_types)) {
    $stmt->bind_param($all_param_types, ...$all_param_values);
}
$stmt->execute();
$result = $stmt->get_result();

// --- [BƯỚC 3] LẤY DANH SÁCH NĂM (ĐỂ LÀM DROPDOWN) ---
$years = [];
$years_sql = "SELECT DISTINCT YEAR(ngay_tao) as nam FROM TEN_DE ORDER BY nam DESC";
$years_result = $conn->query($years_sql);
if ($years_result->num_rows > 0) {
    while($row = $years_result->fetch_assoc()) {
        $years[] = $row['nam'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Chủ Trắc Nghiệm</title>
    <link rel="stylesheet" href="../CSS/Home/home.css">
    
    <style>
        .main-content {
            width: 100%; max-width: 1200px; margin: 20px auto;
            padding: 0 15px; box-sizing: border-box;
        }
        
        /* [SỬA LẠI] CSS CHO THANH LỌC NÂNG CAO */
        .filter-bar {
            background: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .filter-grid {
            display: grid;
            /* 5 cột cho 5 bộ lọc và 1 nút */
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            align-items: flex-end; /* Căn các phần tử xuống dưới */
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-group label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 0.9em;
        }
        .filter-group input[type="text"],
        .filter-group select {
            padding: 9px 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 15px;
        }
        .btn-filter {
            background: #2d98da;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            height: 39px; /* Bằng chiều cao input */
        }
        
        /* CSS Phân trang (giữ nguyên) */
        .pagination { display: flex; justify-content: center; padding: 20px 0; list-style: none; margin-top: 30px; }
        .pagination a { color: #2d98da; padding: 8px 16px; text-decoration: none; border: 1px solid #ddd; margin: 0 4px; border-radius: 4px; transition: background-color 0.2s; }
        .pagination a:hover { background-color: #f0f0f0; }
        .pagination a.active { background-color: #2d98da; color: white; border-color: #2d98da; }
        .pagination a.disabled { color: #aaa; pointer-events: none; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="main-content">
    
        <div class="filter-bar">
            <form action="home.php" method="GET" class="filter-grid">
                
                <div class="filter-group">
                    <label for="ten_de">Tên đề:</label>
                    <input type="text" name="ten_de" id="ten_de" 
                           placeholder="Nhập tên đề..."
                           value="<?php echo htmlspecialchars($search_ten_de); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="nguoi_tao">Người tạo:</label>
                    <input type="text" name="nguoi_tao" id="nguoi_tao" 
                           placeholder="Nhập tên người tạo..."
                           value="<?php echo htmlspecialchars($search_nguoi_tao); ?>">
                </div>

                <div class="filter-group">
                    <label for="trinh_do">Trình độ:</label>
                    <select name="trinh_do" id="trinh_do">
                        <option value="all">Tất cả trình độ</option>
                        <?php foreach ($trinh_do_map as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php if ($selected_trinh_do == $key) echo 'selected'; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="lop_hoc">Khối lớp:</label>
                    <select name="lop_hoc" id="lop_hoc">
                        <option value="all">Tất cả khối lớp</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php if ($selected_lop_hoc == $i) echo 'selected'; ?>>
                                Lớp <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="year-select">Năm:</label>
                    <select name="year" id="year-select">
                        <option value="all">Tất cả các năm</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php if ($selected_year == $year) echo 'selected'; ?>>
                                Năm <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>&nbsp;</label> <button type="submit" class="btn-filter">Tìm kiếm</button>
                </div>
            </form>
        </div>

        <div class="card-grid">
            
            <?php
            // Vòng lặp (giữ nguyên)
            if ($result->num_rows > 0) {
                while ($de_thi = $result->fetch_assoc()) {
                    // (Code xử lý $info_html giữ nguyên)
                    $trinh_do_raw = $de_thi['trinh_do'];
                    $trinh_do_text = $trinh_do_map[$trinh_do_raw] ?? $trinh_do_raw;
                    $lop_hoc_text = "Lớp " . htmlspecialchars($de_thi['lop_hoc']);
                    $link_to_view = "../Tracnghiem/view_quiz_details.php?id_de=" . $de_thi['ID_TD'];
                    $info_html = "+ Tác giả: " . htmlspecialchars($de_thi['username']) . "<br>";
                    if (!empty($de_thi['danh_sach_lop'])) {
                        $info_html .= "<span class='info-label'>Gán cho:</span> <strong>" . htmlspecialchars($de_thi['danh_sach_lop']) . "</strong>";
                    } else {
                        $bat_dau_text = !empty($de_thi['thoi_gian_bat_dau']) ? date("d/m H:i", strtotime($de_thi['thoi_gian_bat_dau'])) : "Mọi lúc";
                        $ket_thuc_text = !empty($de_thi['thoi_gian_ket_thuc']) ? date("d/m H:i", strtotime($de_thi['thoi_gian_ket_thuc'])) : "Không giới hạn";
                        $info_html .= "<span class='time-info'>";
                        $info_html .= "<span class='info-label'>Bắt đầu:</span> " . $bat_dau_text . "<br>";
                        $info_html .= "<span class='info-label'>Kết thúc:</span> " . $ket_thuc_text;
                        $info_html .= "</span>";
                    }
                    ?>
                    
                    <div class="card">
                        <div class="card-badge yellow"><?php echo htmlspecialchars($de_thi['lop_hoc']); ?></div>
                        <div class="card-title yellow"><?php echo htmlspecialchars($de_thi['ten_de']); ?></div>
                        <div class="card-desc">
                            Mức độ: <strong><?php echo htmlspecialchars($trinh_do_text); ?></strong>.
                        </div>
                        <div class="card-stars">★★★★★</div>
                        <div class="card-list">
                            <?php echo $info_html; ?>
                        </div>
                        <div class="card-footer">
                            <a href="<?php echo $link_to_view; ?>" class="card-link">Làm bài ngay »</a>
                        </div>
                    </div>
                    <?php
                } 
            } else {
                echo "<p style='text-align: center; width: 100%;'>Không tìm thấy bộ đề nào khớp với điều kiện lọc.</p>";
            }
            $stmt->close();
            $conn->close();
            ?>
        </div> 

        <div class="pagination">
            <?php 
            // Lấy TẤT CẢ các tham số (year, ten_de,...) từ URL hiện tại
            $query_params = $_GET;
            unset($query_params['page']); // Xóa 'page' cũ đi
            
            // Hàm trợ giúp để tạo link mới
            function get_page_link($page, $params) {
                $params['page'] = $page;
                // http_build_query sẽ tự tạo link đúng: ?ten_de=abc&year=2024&page=2
                return '?' . http_build_query($params);
            }
            
            if ($total_pages > 1): 
            ?>
                <a href="<?php echo get_page_link($current_page - 1, $query_params); ?>" 
                   class="<?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                   &laquo;
                </a>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="<?php echo get_page_link($i, $query_params); ?>" 
                       class="<?php echo ($i == $current_page) ? 'active' : ''; ?>">
                       <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <a href="<?php echo get_page_link($current_page + 1, $query_params); ?>" 
                   class="<?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                   &raquo;
                </a>
            <?php endif; ?>
        </div>

    </div> 
    
    <?php include 'aichat.php'; ?>
    <?php include 'Footer.php'; ?>     
</body>
</html>