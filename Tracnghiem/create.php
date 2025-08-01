<?php
// filepath: c:\xampp\htdocs\TracNghiem\Tracnghiem\create.php
include '../Home/navbar.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo câu hỏi trắc nghiệm</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            display: flex;
            gap: 32px;
        }
        .left-panel {
            flex: 2;
        }
        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .question-box {
            width: 100%;
            min-height: 90px;
            border: 2px solid #222;
            border-radius: 22px;
            margin-bottom: 18px;
            padding: 18px 22px;
            font-size: 20px;
            background: #fff;
            box-sizing: border-box;
            display: flex;
            align-items: center;
        }
        .answers-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }
        .answer-box {
            min-height: 70px;
            border: 2px solid #222;
            border-radius: 16px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 0;
        }
        .form-group {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
        }
        .form-group select, .form-group input, .form-group button {
            font-size: 15px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #bbb;
            background: #fff;
        }
        .form-group button {
            border: 1px solid #222;
            background: #f5f5f5;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }
        .form-group button:hover {
            background: #e3f2fd;
        }
        .right-panel .form-group {
            justify-content: flex-start;
        }
        .right-panel .form-group button, .right-panel .form-group select {
            min-width: 90px;
        }
        .right-panel .form-group input[type="text"] {
            min-width: 60px;
        }
        .right-panel .form-group .wide-btn {
            min-width: 180px;
        }
        .right-panel .form-group .medium-btn {
            min-width: 120px;
        }
        .right-panel .form-group .small-btn {
            min-width: 60px;
        }
        .right-panel .quick-view, .right-panel .quick-download {
            border: 2px solid #222;
            border-radius: 18px;
            background: #fff;
            min-height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 0;
        }
        .right-panel .quick-download {
            min-height: 40px;
            font-size: 16px;
        }
        .answer-key-input {
            width: 60px;
            font-size: 18px;
            text-align: center;
            border: 2px solid #2d98da;
            border-radius: 8px;
            margin-left: 8px;
        }
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                gap: 0;
            }
            .right-panel {
                margin-top: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left panel: Câu hỏi và đáp án -->
        <div class="left-panel">
            <div class="question-box">
                <input type="text" id="question" placeholder="Câu hỏi" style="width:100%;border:none;font-size:20px;outline:none;background:transparent;">
            </div>
            <div class="answers-grid">
                <div class="answer-box">
                    <input type="text" id="answerA" placeholder="A." style="width:90%;border:none;font-size:20px;outline:none;background:transparent;">
                </div>
                <div class="answer-box">
                    <input type="text" id="answerB" placeholder="B." style="width:90%;border:none;font-size:20px;outline:none;background:transparent;">
                </div>
                <div class="answer-box">
                    <input type="text" id="answerC" placeholder="C." style="width:90%;border:none;font-size:20px;outline:none;background:transparent;">
                </div>
                <div class="answer-box">
                    <input type="text" id="answerD" placeholder="D." style="width:90%;border:none;font-size:20px;outline:none;background:transparent;">
                </div>
            </div>
        </div>
        <!-- Right panel: Các nút chức năng -->
        <div class="right-panel">
            <div class="form-group">
                <select id="selectBoDe">
                    <option>Chọn bộ đề</option>
                    <option>Bộ đề 1</option>
                    <option>Bộ đề 2</option>
                    <option value="create_new">+ Tạo bộ đề mới...</option>
                </select>
                <select>
                    <option>Trình độ</option>
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                    <option>5</option>
                </select>
            </div>
            <div id="modalBoDe" style="display:none;position:fixed;z-index:99999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);align-items:center;justify-content:center;">
                <div style="background:#fff;padding:32px 24px;border-radius:12px;min-width:320px;box-shadow:0 4px 24px rgba(0,0,0,0.15);position:relative;">
                    <h3 style="margin-top:0;">Tạo bộ đề mới</h3>
                    <input type="text" id="inputTenBoDe" placeholder="Nhập tên bộ đề..." style="width:100%;padding:8px 12px;font-size:16px;border-radius:6px;border:1px solid #bbb;margin-bottom:18px;">
                    <div style="text-align:right;">
                        <button onclick="closeModalBoDe()" style="padding:6px 18px;border-radius:6px;border:1px solid #bbb;background:#eee;margin-right:8px;">Hủy</button>
                        <button onclick="taoBoDeMoi()" style="padding:6px 18px;border-radius:6px;border:1px solid #2d98da;background:#2d98da;color:#fff;">Tạo</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <select>
                    <option>Môn</option>
                    <option>Toán</option>
                    <option>Lý</option>
                    <option>Hóa</option>
                </select>
                <span>
                    Đáp án:
                    <input type="text" id="answerKey" class="answer-key-input" maxlength="1" placeholder="A-D">
                </span>
            </div>
            <div class="form-group" style="position:relative;">
                <button class="small-btn" id="btnTao" type="button" onclick="validateForm()">Tạo</button>
                <span id="tao-warn" style="display:none;position:absolute;right:-30px;top:50%;transform:translateY(-50%);cursor:pointer;">
                    <span style="color:#e74c3c;font-size:22px;" title="Có trường chưa điền">&#9888;</span>
                </span>
            </div>
            <div class="form-group">
                <button class="wide-btn">Tải file đề tạo nhanh</button>
            </div>
            <div class="quick-view">
                xem nhanh file đã tải
            </div>
        </div>
    </div>
    <script>
        // Chuyển focus khi nhấn Enter
        document.getElementById('question').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('answerA').focus();
            }
        });
        document.getElementById('answerA').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('answerB').focus();
            }
        });
        document.getElementById('answerB').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('answerC').focus();
            }
        });
        document.getElementById('answerC').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('answerD').focus();
            }
        });
        // Có thể thêm chuyển tiếp từ answerD sang đáp án nếu muốn
        document.getElementById('answerD').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('answerKey').focus();
             }
        });
        document.getElementById('selectBoDe').addEventListener('change', function() {
            if (this.value === 'create_new') {
                document.getElementById('modalBoDe').style.display = 'flex';
                setTimeout(function() {
                    document.getElementById('inputTenBoDe').focus();
                }, 100);
                this.value = "Chọn bộ đề";
                }
        });
        function closeModalBoDe() {
            document.getElementById('modalBoDe').style.display = 'none';
            document.getElementById('inputTenBoDe').value = '';
        }
        function taoBoDeMoi() {
            var ten = document.getElementById('inputTenBoDe').value.trim();
            if (!ten) {
                alert('Vui lòng nhập tên bộ đề!');
                document.getElementById('inputTenBoDe').focus();
                return;
            }
            // Thêm bộ đề mới vào select
            var select = document.getElementById('selectBoDe');
            var option = document.createElement('option');
            option.text = ten;
            option.value = ten;
            // Thêm vào trước dòng "Tạo bộ đề mới"
            select.add(option, select.options[select.options.length - 1]);
            select.value = ten;
            closeModalBoDe();
        }
        function validateForm() {
    // Xóa icon cảnh báo cũ
            document.querySelectorAll('.warn-icon').forEach(e => e.remove());
            let valid = true;

    // Kiểm tra câu hỏi
            let q = document.getElementById('question');
            if (!q.value.trim() || q.value.trim() === q.placeholder) {
                showWarn(q);
                valid = false;
            }

            // Kiểm tra đáp án
            ['A','B','C','D'].forEach(ch => {
                let ans = document.getElementById('answer'+ch);
                if (!ans.value.trim() || ans.value.trim() === ans.placeholder) {
                    showWarn(ans);
                    valid = false;
                }
            });

            // Kiểm tra bộ đề
            let selectBoDe = document.getElementById('selectBoDe');
            if (selectBoDe.selectedIndex === 0) {
                showWarn(selectBoDe);
                valid = false;
            }

            // Kiểm tra trình độ
            let selectTrinhDo = selectBoDe.parentNode.querySelectorAll('select')[1];
            if (selectTrinhDo.selectedIndex === 0) {
                showWarn(selectTrinhDo);
                valid = false;
            }

            // Kiểm tra môn
            let selectMon = document.querySelector('.right-panel select:nth-of-type(3)');
            if (selectMon && selectMon.selectedIndex === 0) {
                showWarn(selectMon);
                valid = false;
            }

            // Kiểm tra đáp án đúng
            let answerKey = document.getElementById('answerKey');
            if (!answerKey.value.trim() || !['A','B','C','D'].includes(answerKey.value.trim().toUpperCase())) {
                showWarn(answerKey);
                valid = false;
            }

            if (!valid) {
                // Có lỗi, không submit
                return false;
            }
            // Nếu hợp lệ, xử lý tiếp (submit form hoặc lưu dữ liệu)
            alert('Tạo thành công!');
}

function showWarn(input) {
    // Nếu đã có icon thì không thêm nữa
    if (input.parentNode.querySelector('.warn-icon')) return;
    let warn = document.createElement('span');
    warn.className = 'warn-icon';
    warn.title = 'Vui lòng điền thông tin!';
    warn.innerHTML = ' <span style="color:#e74c3c;font-size:18px;vertical-align:middle;">&#9888;</span>';
    // Nếu là select hoặc input, thêm sau nó
    if (input.tagName === 'SELECT' || input.tagName === 'INPUT') {
        input.parentNode.appendChild(warn);
    } else {
        input.appendChild(warn);
    }
}
    </script>    
    <?php include '../Home/aichat.php'; ?>
    <?php include '../Home/Footer.php'; ?>
</body>
</html>


