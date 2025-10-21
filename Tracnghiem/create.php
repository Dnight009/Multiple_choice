<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tạo bộ đề</title>
    <link rel="stylesheet" href="../CSS/Tracnghiem/create.css">
</head>
<body>

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

      <label for="lophoc">Lớp học:</label>
      <select name="lophoc" id="lophoc" required>
        <option value="" disabled selected>-- Chọn lớp học --</option>
        <?php
        for ($i = 1; $i <= 12; $i++) {
            echo "<option value=\"$i\">Lớp $i</option>";
        }
        ?>
      </select>

      <label for="file">Tải file excel:</label>
      <input type="file" name="file" id="file" accept=".xlsx,.xls">
    </div>

    <div class="button-group">
      <button type="button" onclick="history.back()">Quay lại</button>
      <button type="submit">Tiếp theo</button>
    </div>
  </form>

</body>
</html>
