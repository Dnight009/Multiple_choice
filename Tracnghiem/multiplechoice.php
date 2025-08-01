<?php
// filepath: c:\xampp\htdocs\TracNghiem\Tracnghiem\multiplechoice.php
// Trang trắc nghiệm
include '../Home/navbar.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trắc nghiệm</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f6fa;
        }
        .main-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 320px;
            background: #fff;
            border-right: 1px solid #eee;
            padding: 24px 0 24px 0;
            overflow-y: auto;
        }
        .sidebar h3 {
            margin: 0 0 16px 24px;
            font-size: 18px;
            color: #2d98da;
        }
        .exam-list {
            list-style: none;
            padding: 0 24px;
            margin: 0;
        }
        .exam-list li {
            margin-bottom: 8px;
        }
        .exam-list label {
            display: block;
            padding: 10px 12px;
            border-radius: 6px;
            cursor: pointer;
            background: #f5f6fa;
            transition: background 0.2s;
        }
        .exam-list input[type="radio"] {
            margin-right: 8px;
        }
        .exam-list input[type="radio"]:checked + span {
            color: #2d98da;
            font-weight: bold;
        }
        .exam-list label.selected {
            background: #eaf6ff;
            border-left: 4px solid #2d98da;
        }
        .content {
            flex: 1;
            padding: 40px 32px;
        }
        .question-box {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 32px 24px;
            margin-bottom: 24px;
        }
        .question-box h4 {
            margin: 0 0 12px 0;
            font-size: 18px;
            color: #222;
        }
        .question-box p {
            font-style: italic;
            color: #444;
        }
        .answers {
            margin-top: 18px;
        }
        .answers label {
            display: block;
            margin-bottom: 10px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .answers input[type="radio"] {
            margin-right: 8px;
        }
        .answers label:hover {
            background: #f1f8ff;
        }
        .question-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            margin-top: 16px;
        }
        .question-nav button {
            background: #2d98da;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 8px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .question-nav button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .flip-btn {
            float: right;
            background: #fffbe6;
            color: #b7950b;
            border: 1px solid #f1c40f;
            border-radius: 4px;
            padding: 4px 12px;
            font-size: 15px;
            cursor: pointer;
            margin-top: 8px;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="sidebar">
        <h3>Danh sách bài học<br><span style="font-size:13px;color:#888;">(316 đề thi)</span></h3>
        <ul class="exam-list" id="examList">
            <li>
                <label class="selected">
                    <input type="radio" name="exam" checked>
                    <span>Đề thi ĐGNL ĐHQG HCM 2025 có đáp án (Đề 1)</span>
                </label>
            </li>
            <li>
                <label>
                    <input type="radio" name="exam">
                    <span>Đề thi ĐGNL ĐHQG HCM 2025 có đáp án (Đề 2)</span>
                </label>
            </li>
            <li>
                <label>
                    <input type="radio" name="exam">
                    <span>Đề thi ĐGNL ĐHQG HCM 2025 có đáp án (Đề 3)</span>
                </label>
            </li>
            <!-- Thêm các đề khác -->
        </ul>
    </div>
    <div class="content">
        <div class="question-box" id="questionBox">
            <button class="flip-btn" onclick="alert('Chức năng lật thẻ!')">Lật thẻ</button>
            <h4>PHẦN 1: SỬ DỤNG NGÔN NGỮ<br>1.1. TIẾNG VIỆT (30 CÂU)</h4>
            <p>
                Xác định thành ngữ trong đoạn văn sau: <br>
                <i>“Lí Thông lân la gợi chuyện, rồi gạ cùng Thạch Sanh kết nghĩa anh em. Sớm mồ côi cha mẹ, tứ cố vô thân, nay có người săn sóc đến mình, Thạch Sanh cảm động, vui vẻ nhận lời” (Thạch Sanh)</i>
            </p>
            <div class="answers">
                <label><input type="radio" name="answer">A. Kết nghĩa anh em.</label>
                <label><input type="radio" name="answer">B. Mồ côi cha mẹ.</label>
                <label><input type="radio" name="answer">C. Tứ cố vô thân.</label>
                <label><input type="radio" name="answer">D. Đoạn văn trên không có thành ngữ.</label>
            </div>
        </div>
        <div class="question-nav">
            <button id="prevBtn" disabled>&larr;</button>
            <span>1 / 120</span>
            <button id="nextBtn">&rarr;</button>
        </div>
    </div>
</div>
</body>