<?php
// (Code session_start() và kiểm tra đăng nhập của bạn sẽ ở đây)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['IDACC'])) {
    header("Location: /Guest/Login.php");
    exit;
}
$my_id = $_SESSION['IDACC'];
$my_username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    
    <meta charset="UTF-8">
    <title>Trò chuyện</title>
    <link rel="stylesheet" href="../CSS/Chat/chat.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include __DIR__ . '/../Home/navbar.php'; ?>
    <div class="chat-container">
        
        <aside class="chat-sidebar">
            <div class="sidebar-header">
                <h3>Tin nhắn</h3>
                <button title="Tạo nhóm mới">+</button>
            </div>
            
            <div class="search-bar">
                <input type="text" id="search-user-input" placeholder="Tìm kiếm người dùng...">
                <div id="search-results-box">
                    </div>
            </div>
            
            <div class="chat-list" id="chat-list-container">
                </div>
        </aside>
        
        <main class="chat-main" id="chat-main">
            <div id="welcome-message" class="welcome-message">
                <h2>Chào mừng, <?php echo htmlspecialchars($my_username); ?>!</h2>
                <p>Hãy chọn một cuộc trò chuyện hoặc tìm kiếm bạn bè để bắt đầu.</p>
            </div>

            <div id="chat-window" class="hidden">
                <div class="chat-header" id="chat-window-header">
                    </div>
                
                <div class="messages-container" id="messages-container">
                    </div>
                
                <form class="message-form" id="message-form">
                    <input type="text" id="message-input" placeholder="Nhập tin nhắn..." autocomplete="off">
                    <button type="submit">Gửi</button>
                </form>
            </div>
        </main>
    </div>
    <script>
        const MY_USER_ID = <?php echo json_encode($my_id); ?>;
    </script>      
    <script src="../Java/chat.js"></script>
    <?php include __DIR__ . '/../Home/Footer.php'; ?>
    
    </body>
</html>