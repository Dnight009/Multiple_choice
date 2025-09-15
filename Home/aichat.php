<?php
?>
<header>

 <link rel="stylesheet" href="../CSS/Home/aichat.css">
</header>
<!-- Chat AI Floating Widget -->
<div id="ai-chat-widget" class="">
    <div id="ai-chat-fab" onclick="toggleAIChat()" title="Chat AI">
        🤖
    </div>
    <div id="ai-chat-box">
        <div id="ai-chat-header">
            <span>🤖 Chat AI</span>
            <span id="ai-chat-toggle-btn" onclick="toggleAIChat()" style="cursor:pointer;">&#10006;</span>
        </div>
        <div id="ai-chat-body">
            <div id="ai-chat-messages"></div>
            <form id="ai-chat-form" onsubmit="return sendAIMessage();">
                <input type="text" id="ai-chat-input" placeholder="Nhập câu hỏi..." autocomplete="off" />
                <button type="submit">Gửi</button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleAIChat() { 
        var widget = document.getElementById('ai-chat-widget'); 
        widget.classList.toggle('open'); 
    }
function sendAIMessage() {
    var input = document.getElementById('ai-chat-input');
    var messages = document.getElementById('ai-chat-messages');
    var text = input.value.trim();
    if (!text) return false;

    // Hiển thị tin nhắn người dùng
    var userMsg = document.createElement('div');
    userMsg.className = 'user-msg';
    userMsg.innerText = text;
    messages.appendChild(userMsg);

    input.value = '';
    messages.scrollTop = messages.scrollHeight;

    // Gửi lên server (gemini.php)
    fetch("gemini.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: text })
    })
    .then(res => res.json())
    .then(data => {
        var aiMsg = document.createElement('div');
        aiMsg.className = 'ai-msg';
        aiMsg.innerText = "🤖 " + data.reply;
        messages.appendChild(aiMsg);
        messages.scrollTop = messages.scrollHeight;
    })
    .catch(err => {
        var aiMsg = document.createElement('div');
        aiMsg.className = 'ai-msg';
        aiMsg.innerText = "⚠️ Lỗi: " + err;
        messages.appendChild(aiMsg);
    });

    return false;
}

</script>