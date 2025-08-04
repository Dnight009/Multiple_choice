<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tạo bộ đề</title>
    <link rel="stylesheet" href="../CSS/Tracnghiem/create.css">
</head>
<body>

  <form class="container" method="post" action="submit_bode.php" enctype="multipart/form-data">
    <div class="form-box">
      <label for="tenbode">Tên bộ đề:</label>
      <input type="text" name="tenbode" id="tenbode" required>

      <label for="trinhdo">Trình độ:</label>
      <input type="text" name="trinhdo" id="trinhdo" required>

      <label for="monhoc">Môn học:</label>
      <input type="text" name="monhoc" id="monhoc" required>

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
