<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng đến với Trắc Nghiệm Online</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #74ebd5 0%, #9face6 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .welcome-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            animation: fadeIn 1.5s ease-in-out;
            max-width: 90%;
            width: 400px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2em;
        }

        p {
            color: #555;
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        /* Hiệu ứng Loading (Spinner) */
        .spinner {
            margin: 0 auto 20px;
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Nút bấm thủ công (phòng khi JS lỗi hoặc người dùng muốn nhanh) */
        .btn-go {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: background 0.3s;
            font-size: 0.9em;
        }

        .btn-go:hover {
            background-color: #2980b9;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="welcome-container">
        <h1>Xin Chào!</h1>
        <p>Đang đưa bạn đến hệ thống trắc nghiệm...</p>
        
        <div class="spinner"></div>

        <a href="TracNghiem/Home/home.php" class="btn-go">Truy cập ngay</a>
    </div>

    <script>
        // Thời gian chờ: 3000ms = 3 giây
        setTimeout(function() {
            // [QUAN TRỌNG] Kiểm tra đúng đường dẫn thư mục của bạn
            window.location.href = '../Home/home.php';
        }, 3000);
    </script>
</body>
</html>