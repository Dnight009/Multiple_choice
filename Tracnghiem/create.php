<?php
// 1. KHỐI PHP NÀY ĐÃ CÓ (GIỮ NGUYÊN)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Check/Connect.php'; 

if (!isset($_SESSION['IDACC']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '3') {
    header("Location: /TracNghiem/Home/home.php"); 
    exit;
}
$teacher_id = $_SESSION['IDACC'];

$lop_cua_toi = [];
$stmt_lop = $conn->prepare("SELECT ID_CLASS, ten_lop_hoc 
                            FROM CLASS 
                            WHERE IDACC_teach = ? AND trang_thai = 'đang hoạt động'
                            ORDER BY ten_lop_hoc ASC");
$stmt_lop->bind_param("i", $teacher_id);
$stmt_lop->execute();
$result_lop = $stmt_lop->get_result();
if ($result_lop->num_rows > 0) {
    $lop_cua_toi = $result_lop->fetch_all(MYSQLI_ASSOC);
}
$stmt_lop->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tạo bộ đề</title>
    <link rel="stylesheet" href="../CSS/Tracnghiem/create.css">
    <style>
        .form-box input[type="number"] {
            background-color: #92b4ec;
            padding: 10px 15px;
            border: none;
            border-radius: 20px;
            width: 100%;
            color: #333;
            font-family: 'Segoe UI', sans-serif;
            font-size: 15px;
        }
        .form-box input[type="number"]::placeholder {
            color: #555;
            opacity: 0.7;
        }
        .multi-select-container {
            position: relative; 
            background-color: #92b4ec;
            border-radius: 20px;
            padding: 5px 10px;
            width: 100%;
            box-sizing: border-box; 
            min-height: 48px; 
            display: flex;
            flex-wrap: wrap; 
            gap: 5px; 
        }
        .pill {
            background-color: #ffffff;
            color: #2d98da;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px; 
            height: fit-content; 
        }
        .pill-close {
            cursor: pointer;
            font-weight: bold;
            color: #e74c3c;
        }
        .multi-select-input {
            flex: 1; 
            min-width: 150px; 
            border: none;
            outline: none;
            background: none; 
            padding: 5px 0;
            font-size: 15px;
            color: #333;
            font-family: 'Segoe UI', sans-serif;
        }
        .multi-select-input::placeholder { color: #555; opacity: 0.7; }
        .search-results {
            display: none; 
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            width: calc(100% - 20px); 
            left: 10px;
            top: 100%; 
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            margin-top: 5px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .result-item {
            padding: 10px 15px;
            cursor: pointer;
            color: #333;
        }
        .result-item:hover { background-color: #f0f0f0; }
    </style>
</head>
<body>

  <?php include __DIR__ . '/../Home/navbar.php'; ?>

  <form class="container" method="post" action="../Tracnghiem/multiplechoice.php" enctype="multipart/form-data">
    <div class="form-box">
      <label for="tenbode">Tên bộ đề:</label>
      <input type="text" name="tenbode" id="tenbode" required>

      <label for="trinhdo">Trình độ:</label>
      <select name="trinhdo" id="trinhdo" required>
        <option value="" disabled selected>-- Chọn trình độ --</option>
        <option value="de">Dễ</option>
        <option value="binhthuong">Bình thường</option>
        <option value="kho">Khó</option>
        <option value="nangcao">Nâng cao</option>
        <option value="tonghop">Tổng Hợp</option>
      </select>

      <label for="lophoc">Phân loại (Khối lớp):</label> 
      <select name="lophoc" id="lophoc" required>
        <option value="" disabled selected>-- Chọn khối lớp --</option>
        <?php for ($i = 1; $i <= 12; $i++) { echo "<option value=\"$i\">Lớp $i</option>"; } ?>
      </select>

      <label for="thoi_luong">Thời gian (phút):</label>
      <input type="number" name="thoi_luong_phut" id="thoi_luong" min="1" placeholder="Mặc định là 45 phút">

      <label for="assign_classes_search">Gán cho các lớp:</label>
      <div class="multi-select-container">
          <div class="pills-container" id="pills-container">
          </div>
          <input type="text" id="assign_classes_search" class="multi-select-input"
                 placeholder="Gõ để tìm lớp... (Để trống nếu đề công khai)">
          <div class="search-results" id="search-results"></div>
      </div>

      <label for="file">Tải file excel:</label>
      <input type="file" name="file" id="file" accept=".xlsx,.xls">
    </div>

    <div class="button-group">
      <button type="button" onclick="history.back()">Quay lại</button>
      <button type="submit">Tiếp theo</button>
    </div>
  </form>

  <script>
    const allClasses = <?php echo json_encode($lop_cua_toi); ?>;
    
    console.log("Các lớp đã tải:", allClasses); 

    const searchInput = document.getElementById('assign_classes_search');
    const resultsContainer = document.getElementById('search-results');
    const pillsContainer = document.getElementById('pills-container');

    function showResults() {
        const searchTerm = searchInput.value.toLowerCase();
        resultsContainer.innerHTML = ''; 
        const selectedIDs = new Set(
            Array.from(pillsContainer.querySelectorAll('.pill'))
                 .map(pill => pill.dataset.id)
        );
        const filtered = allClasses.filter(cls => {
            const matchesSearch = cls.ten_lop_hoc.toLowerCase().includes(searchTerm);
            const notSelected = !selectedIDs.has(String(cls.ID_CLASS)); // Sửa: so sánh string
            
            return matchesSearch && notSelected; 
        });
        if (filtered.length === 0) {
            resultsContainer.innerHTML = '<div class="result-item" style="cursor:default; background:none;">Không tìm thấy (hoặc đã chọn hết).</div>';
        } else {
            filtered.forEach(cls => {
                const item = document.createElement('div');
                item.className = 'result-item';
                item.dataset.id = cls.ID_CLASS; 
                item.dataset.name = cls.ten_lop_hoc; 
                item.textContent = cls.ten_lop_hoc;
                resultsContainer.appendChild(item);
            });
        }
        resultsContainer.style.display = 'block';
    }

    function addPill(e) {
        if (!e.target.classList.contains('result-item') || !e.target.dataset.id) {
            return;
        }

        const id = e.target.dataset.id;
        const name = e.target.dataset.name;

        const pill = document.createElement('div');
        pill.className = 'pill';
        pill.dataset.id = id;

        pill.innerHTML = `
            <span>${name}</span>
            <span class="pill-close" data-id="${id}">&times;</span>
            <input type="hidden" name="assigned_classes[]" value="${id}">
        `;
        
        pillsContainer.appendChild(pill);
        searchInput.value = ''; 
        resultsContainer.style.display = 'none'; 
        searchInput.focus();
    }

    function removePill(e) {
        if (!e.target.classList.contains('pill-close')) {
            return;
        }
        
        const pill = e.target.closest('.pill');
        if (pill) {
            pill.remove();
            showResults(); 
        }
    }

    searchInput.addEventListener('input', showResults); 
    searchInput.addEventListener('focus', showResults); 
    resultsContainer.addEventListener('click', addPill); 
    pillsContainer.addEventListener('click', removePill); 

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.multi-select-container')) {
            resultsContainer.style.display = 'none';
        }
    });
  </script>

  <?php include __DIR__ . '/../Home/aichat.php'; ?>
  <?php include __DIR__ . '/../Home/Footer.php'; ?>

</body>
</html>