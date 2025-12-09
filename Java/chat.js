document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Lấy các DOM element (Giữ nguyên)
    const chatListContainer = document.getElementById('chat-list-container');
    const chatWindow = document.getElementById('chat-window');
    const welcomeMessage = document.getElementById('welcome-message');
    const messagesContainer = document.getElementById('messages-container');
    const chatHeader = document.getElementById('chat-window-header');
    const searchInput = document.getElementById('search-user-input');
    const searchResultsBox = document.getElementById('search-results-box');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');

    // 2. BIẾN TOÀN CỤC (Giữ nguyên)
    let currentChatId = null;
    let typingTimer; 
    let lastMessageId = 0; 
    let currentPollingInterval = null; 
    const POLLING_INTERVAL = 3000; 

    /**
     * HÀM 1: Tải danh sách chat (cột trái)
     */
    async function loadConversations() {
        try {
            // [SỬA] Đảm bảo đường dẫn đúng (viết thường)
            const response = await fetch('../API/get_conversations.php');
            const conversations = await response.json();
            chatListContainer.innerHTML = '';
            if (conversations.length === 0) {
                chatListContainer.innerHTML = '<p style="padding: 10px; text-align: center;">Chưa có cuộc trò chuyện nào.</p>';
                return;
            }
            conversations.forEach(chat => {
                const chatItem = document.createElement('div');
                chatItem.className = 'chat-item';
                chatItem.dataset.chatId = chat.ID_CONVERSATION; 
                chatItem.dataset.chatName = chat.display_name;
                chatItem.innerHTML = `
                    <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="avt">
                    <div class="chat-item-info">
                        <strong>${chat.display_name}</strong>
                        <span>...</span>
                    </div>
                `;
                chatListContainer.appendChild(chatItem);
            });
        } catch (error) {
            console.error(error);
            chatListContainer.innerHTML = '<p>Lỗi khi tải danh sách chat.</p>';
        }
    }

    /**
     * HÀM 2: Tải tin nhắn (cột phải)
     */
    async function loadChat(chatId, chatName) {
        if (currentPollingInterval) {
            clearInterval(currentPollingInterval);
        }
        currentChatId = chatId;
        lastMessageId = 0; 
        welcomeMessage.classList.add('hidden');
        chatWindow.classList.remove('hidden');
        chatHeader.textContent = chatName;
        messagesContainer.innerHTML = '<p>Đang tải tin nhắn...</p>';

        try {
            const oldActive = chatListContainer.querySelector('.active');
            if (oldActive) oldActive.classList.remove('active');
            const newActive = chatListContainer.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
            if (newActive) newActive.classList.add('active');

            // [SỬA] Đảm bảo đường dẫn đúng (viết thường)
            const response = await fetch(`../API/get_messages.php?chat_id=${chatId}`);
            const messages = await response.json();

            messagesContainer.innerHTML = '';
            if (messages.length === 0) {
                messagesContainer.innerHTML = '<p style="text-align: center;">Chưa có tin nhắn nào.</p>';
            } else {
                messages.forEach(msg => {
                    appendMessage(msg); 
                });
                scrollToBottom();
            }
            startPolling(chatId);
        } catch (error) {
            console.error(error);
            messagesContainer.innerHTML = '<p>Lỗi khi tải tin nhắn.</p>';
        }
    }
    
    /**
     * HÀM 3: Hiển thị 1 tin nhắn
     */
    function appendMessage(msg) {
        // (Kiểm tra trùng lặp: Nếu tin nhắn đã tồn tại thì không thêm)
        if (document.getElementById(`msg-${msg.ID_MESSAGE}`)) {
            return; 
        }

        if (msg.ID_MESSAGE > lastMessageId) {
            lastMessageId = msg.ID_MESSAGE;
        }
        const msgDiv = document.createElement('div');
        msgDiv.className = 'msg';
        msgDiv.id = `msg-${msg.ID_MESSAGE}`; // Gán ID cho tin nhắn
        
        if (msg.IDACC_sender == MY_USER_ID) { 
            msgDiv.classList.add('from-me');
        } else {
            msgDiv.classList.add('from-them');
            msgDiv.innerHTML = `<div class="msg-sender">${msg.username}</div>`;
        }
        const msgContent = document.createElement('span');
        msgContent.textContent = msg.message_content;
        msgDiv.appendChild(msgContent);
        messagesContainer.appendChild(msgDiv);
    }
    
    /**
     * HÀM 4: Cuộn xuống dưới cùng
     */
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    /**
     * HÀM 5: Bắt đầu Polling
     */
    function startPolling(chatId) {
        currentPollingInterval = setInterval(() => {
            checkNewMessages(chatId);
        }, POLLING_INTERVAL);
    }

    /**
     * HÀM 6: Kiểm tra tin nhắn mới (Polling)
     */
    async function checkNewMessages(chatId) {
        if (chatId !== currentChatId) return; 
        try {
            // [SỬA] Đảm bảo đường dẫn đúng (viết thường)
            const response = await fetch(`../API/api_check_new_messages.php?chat_id=${chatId}&last_id=${lastMessageId}`);
            const newMessages = await response.json();
            
            if (newMessages.length > 0) {
                newMessages.forEach(msg => {
                    appendMessage(msg);
                });
                scrollToBottom();
            }
        } catch (error) {
            console.error("Lỗi polling:", error);
        }
    }
    
    /**
     * HÀM 7: Tìm kiếm người dùng
     */
    async function searchUsers() {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            searchResultsBox.innerHTML = '';
            searchResultsBox.style.display = 'none';
            return;
        }
        try {
            // [SỬA] Đảm bảo đường dẫn đúng (viết thường)
            const response = await fetch(`../API/api_search_user.php?q=${query}`);
            const users = await response.json();
            searchResultsBox.innerHTML = '';
            if (users.length === 0) {
                searchResultsBox.innerHTML = '<div class="chat-item">Không tìm thấy ai.</div>';
            } else {
                users.forEach(user => {
                    const userItem = document.createElement('div');
                    userItem.className = 'chat-item';
                    userItem.dataset.userId = user.IDACC;
                    userItem.dataset.userName = user.username;
                    userItem.innerHTML = `
                        https://cdn-icons-png.flaticon.com/512/149/149071.png
                        <div class="chat-item-info">
                            <strong>${user.ho_ten || user.username}</strong>
                            <span>${user.username}</span>
                        </div>
                    `;
                    searchResultsBox.appendChild(userItem);
                });
            }
            searchResultsBox.style.display = 'block';
        } catch (error) {
            console.error('Lỗi tìm kiếm:', error);
        }
    }

    /**
     * HÀM 8: Bắt đầu cuộc trò chuyện mới
     */
    async function startChat(otherUserId, otherUserName) {
        searchResultsBox.style.display = 'none';
        searchInput.value = '';
        try {
            // [SỬA] Đảm bảo đường dẫn đúng (viết thường)
            const response = await fetch('../API/api_start_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ other_user_id: otherUserId })
            });
            const data = await response.json();
            if (data.chat_id) {
                loadConversations();
                loadChat(data.chat_id, data.chat_name);
            }
        } catch (error) {
            console.error('Lỗi khi bắt đầu chat:', error);
        }
    }

    /**
     * HÀM 9: Gửi tin nhắn - [ĐÃ SỬA LỖI]
     */
    async function sendMessage(e) {
        e.preventDefault(); 
        const content = messageInput.value.trim();
        if (!content || !currentChatId) return; 
        
        // [XÓA BỎ] Không hiển thị tin nhắn tạm (Optimistic Update)
        // const optimisticMsg = { ... };
        // appendMessage(optimisticMsg);
        
        const tempContent = content;
        messageInput.value = ''; // Xóa input ngay

        try {
            // [SỬA] Gửi tin nhắn VÀ CHỜ PHẢN HỒI
            const response = await fetch('../API/api_send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    chat_id: currentChatId,
                    message_content: tempContent
                })
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // [THÊM MỚI] Gửi thành công, GỌI POLL NGAY LẬP TỨC
                // (Hàm này sẽ lấy tin nhắn thật về, bao gồm tin nhắn của mình)
                checkNewMessages(currentChatId);
            } else {
                // Nếu gửi thất bại, báo lỗi và trả lại tin nhắn
                alert('Gửi tin nhắn thất bại. Vui lòng thử lại.');
                messageInput.value = tempContent;
            }
            
        } catch (error) {
            console.error('Lỗi gửi tin nhắn:', error);
            alert('Gửi tin nhắn thất bại. Vui lòng thử lại.');
            messageInput.value = tempContent;
        }
    }

    // 5. GÁN CÁC SỰ KIỆN (Giữ nguyên)
    chatListContainer.addEventListener('click', function(e) {
        const chatItem = e.target.closest('.chat-item');
        if (!chatItem) return;
        loadChat(chatItem.dataset.chatId, chatItem.dataset.chatName);
    });
    searchInput.addEventListener('keyup', () => {
        clearTimeout(typingTimer); 
        typingTimer = setTimeout(searchUsers, 500);
    });
    searchResultsBox.addEventListener('click', function(e) {
        const userItem = e.target.closest('.chat-item');
        if (!userItem) return;
        startChat(userItem.dataset.userId, userItem.dataset.userName);
    });
    messageForm.addEventListener('submit', sendMessage);

    // 6. CHẠY HÀM ĐẦU TIÊN KHI TẢI TRANG
    loadConversations();
});