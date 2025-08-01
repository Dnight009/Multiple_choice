<?php
?>
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
<style>
#ai-chat-widget {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 9999;
    font-family: Arial, sans-serif;
}
#ai-chat-fab {
    width: 56px;
    height: 56px;
    background: #2d98da;
    color: #fff;
    border-radius: 50%;
    box-shadow: 0 4px 16px rgba(44,62,80,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    cursor: pointer;
    transition: background 0.2s;
    position: relative;
}
#ai-chat-fab:hover {
    background: #3867d6;
}
#ai-chat-box {
    display: none;
    flex-direction: column;
    width: 340px;
    background: #fff;
    border-radius: 12px 12px 0 0;
    box-shadow: 0 4px 24px rgba(44, 62, 80, 0.18);
    overflow: hidden;
    animation: fadeInUp 0.2s;
    position: relative;
}
@keyframes fadeInUp {
    from { transform: translateY(40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
#ai-chat-widget.open #ai-chat-box {
    display: flex;
}
#ai-chat-widget.open #ai-chat-fab {
    display: none;
}
#ai-chat-header {
    background: #2d98da;
    color: #fff;
    padding: 12px 18px;
    font-weight: bold;
    font-size: 17px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
}
#ai-chat-body {
    display: flex;
    flex-direction: column;
    height: 380px;
    background: #f5f6fa;
    padding: 0 0 8px 0;
}
#ai-chat-messages {
    height: 300px;
    overflow-y: auto;
    padding: 12px;
    font-size: 15px;
}
.ai-msg, .user-msg {
    margin-bottom: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    max-width: 85%;
    word-break: break-word;
}
.ai-msg {
    background: #e3f2fd;
    color: #222;
    align-self: flex-start;
}
.user-msg {
    background: #2d98da;
    color: #fff;
    align-self: flex-end;
    margin-left: auto;
}
#ai-chat-form {
    display: flex;
    border-top: 1px solid #eee;
    padding: 8px;
    background: #fff;
}
#ai-chat-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    outline: none;
}
#ai-chat-form button {
    background: #2d98da;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 0 18px;
    margin-left: 8px;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s;
}
#ai-chat-form button:hover {
    background: #3867d6;
}
@media (max-width: 600px) {
    #ai-chat-box {
        width: 96vw;
        min-width: 0;
    }
    #ai-chat-widget {
        right: 4vw;
    }
}
</style>
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

    // Hiển thị phản hồi AI giả lập
    setTimeout(function() {
        var aiMsg = document.createElement('div');
        aiMsg.className = 'ai-msg';
        aiMsg.innerText = '🤖 AI: Cảm ơn bạn đã hỏi! (Demo)';
        messages.appendChild(aiMsg);
        messages.scrollTop = messages.scrollHeight;
    }, 800);

    return false;
}
</script>