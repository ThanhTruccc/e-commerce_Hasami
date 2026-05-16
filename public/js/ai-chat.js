
/**
 * GlowViet AI Chat Consultant - Frontend Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    const aiChatWrapper = document.getElementById('aiChatWrapper');
    const aiChatBubble = document.getElementById('aiChatBubble');
    const aiChatWindow = document.getElementById('aiChatWindow');
    const aiClose = document.getElementById('aiClose');
    const aiInput = document.getElementById('aiInput');
    const aiSendBtn = document.getElementById('aiSendBtn');
    const aiChatMessages = document.getElementById('aiChatMessages');
    const suggestBtns = document.querySelectorAll('.ai-suggest-btn');

    // Toggle Chat Window
    aiChatBubble.addEventListener('click', () => {
        aiChatWrapper.classList.toggle('active');
        if (aiChatWrapper.classList.contains('active')) {
            aiInput.focus();
            scrollToBottom();
        }
    });

    aiClose.addEventListener('click', () => {
        aiChatWrapper.classList.remove('active');
    });

    // Send Message
    function sendMessage(text) {
        if (!text.trim()) return;

        // Append User Message
        appendMessage('user', text);
        aiInput.value = '';

        // Show Typing Indicator
        const typingId = showTyping();

        // Send AJAX request to AI Backend
        fetch(`${APP_URL}/ai/chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: text })
        })
        .then(response => response.json())
        .then(data => {
            hideTyping(typingId);
            if (data.status === 'success') {
                appendMessage('bot', data.reply);
            } else {
                appendMessage('bot', 'Xin lỗi, mình đang gặp chút trục trặc. Bạn thử lại sau nhé!');
            }
        })
        .catch(error => {
            hideTyping(typingId);
            console.error('Error:', error);
            appendMessage('bot', 'Không thể kết nối với máy chủ AI.');
        });
    }

    aiSendBtn.addEventListener('click', () => sendMessage(aiInput.value));
    
    aiInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage(aiInput.value);
    });

    // Suggestion Buttons
    suggestBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sendMessage(btn.innerText);
        });
    });

    function appendMessage(role, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-msg ai-msg-${role}`;
        msgDiv.innerText = text;
        aiChatMessages.appendChild(msgDiv);
        scrollToBottom();
    }

    function showTyping() {
        const id = 'typing-' + Date.now();
        const typingDiv = document.createElement('div');
        typingDiv.id = id;
        typingDiv.className = 'ai-msg ai-msg-bot';
        typingDiv.innerHTML = '<span class="spinner-grow spinner-grow-sm"></span> Đang suy nghĩ...';
        aiChatMessages.appendChild(typingDiv);
        scrollToBottom();
        return id;
    }

    function hideTyping(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function scrollToBottom() {
        aiChatMessages.scrollTop = aiChatMessages.scrollHeight;
    }
});
