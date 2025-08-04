<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Nhập câu hỏi</title>
  <link rel="stylesheet" href="../CSS/Tracnghiem/multiplechoice.css">
</head>
<body>

<div class="container fade-in">
  
  <div id="question-list">
    <!-- Câu hỏi tự động từ Excel sẽ được hiển thị tại đây -->
    <!-- Câu hỏi thủ công thêm bằng JS cũng ở đây -->
  </div>

  <div class="controls">
    <button onclick="addQuestion()">+</button>
  </div>

  <div class="buttons">
    <button onclick="history.back()">Quay lại</button>
    <button type="button" onclick="submitAll()">Tiếp theo</button>
  </div>
</div>

<script>
  let questionCount = 0;

  function addQuestion(data = {}) {
    const container = document.getElementById("question-list");
    const qDiv = document.createElement("div");
    qDiv.classList.add("question-block");
    qDiv.innerHTML = `
      <label>Câu hỏi:</label>
      <input type="text" name="questions[${questionCount}][question]" value="${data.question || ''}" required>

      <input type="text" name="questions[${questionCount}][a]" placeholder="A" value="${data.a || ''}" required>
      <input type="text" name="questions[${questionCount}][b]" placeholder="B" value="${data.b || ''}" required>
      <input type="text" name="questions[${questionCount}][c]" placeholder="C" value="${data.c || ''}" required>
      <input type="text" name="questions[${questionCount}][d]" placeholder="D" value="${data.d || ''}" required>

      <input type="text" name="questions[${questionCount}][correct]" placeholder="Đáp án đúng" value="${data.correct || ''}" required>
    `;
    container.appendChild(qDiv);
    questionCount++;
  }

  function submitAll() {
    alert("Gửi toàn bộ câu hỏi (chưa kết nối backend xử lý)");
  }

  // Đoạn JS này sẽ được dùng khi parse Excel xong -> inject vào HTML
  // Ví dụ:
  // addQuestion({ question: "2+2=?", a: "3", b: "4", c: "5", d: "6", correct: "B" });
</script>

</body>
</html>
