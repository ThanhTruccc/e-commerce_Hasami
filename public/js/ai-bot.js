/**
 * Hasami AI Chat Consultant - Frontend Logic
 */

function initAIChat() {
    const aiChatWrapper = document.getElementById('aiChatWrapper');
    const aiChatBubble = document.getElementById('aiChatBubble');
    const aiChatWindow = document.getElementById('aiChatWindow');
    const aiClose = document.getElementById('aiClose');
    const aiInput = document.getElementById('aiInput');
    const aiSendBtn = document.getElementById('aiSendBtn');
    const aiChatMessages = document.getElementById('aiChatMessages');
    const suggestBtns = document.querySelectorAll('.ai-suggest-btn');

    if (!aiChatWrapper || !aiChatBubble) {
        console.warn('AI Chat elements not found.');
        return;
    }

    // Load Chat History from Server
    function loadChatHistory() {
        aiChatMessages.innerHTML = '<div class="text-center py-3 text-muted"><span class="spinner-border spinner-border-sm me-1"></span> Đang tải lịch sử tư vấn...</div>';
        
        fetch(`${APP_URL}/ai/history`)
        .then(response => response.json())
        .then(data => {
            aiChatMessages.innerHTML = `
                <div class="ai-msg ai-msg-bot">
                    Chào bạn! Mình là trợ lý AI của Hasami. Bạn cần tư vấn về sản phẩm hay cách chăm sóc da hôm nay?
                </div>
            `;
            if (data.status === 'success' && data.history && data.history.length > 0) {
                data.history.forEach(msg => {
                    appendMessage(msg.sender, msg.message, false);
                });
            }
            scrollToBottom();
        })
        .catch(error => {
            console.error('Error loading history:', error);
            aiChatMessages.innerHTML = `
                <div class="ai-msg ai-msg-bot">
                    Chào bạn! Mình là trợ lý AI của Hasami. Bạn cần tư vấn về sản phẩm hay cách chăm sóc da hôm nay?
                </div>
            `;
            scrollToBottom();
        });
    }

    // Toggle Chat Window
    aiChatBubble.addEventListener('click', (e) => {
        e.preventDefault();
        aiChatWrapper.classList.toggle('active');
        if (aiChatWrapper.classList.contains('active')) {
            setTimeout(() => { if (aiInput) aiInput.focus(); }, 100);
            loadChatHistory();
        }
    });

    if (aiClose) {
        aiClose.addEventListener('click', (e) => {
            e.preventDefault();
            aiChatWrapper.classList.remove('active');
        });
    }

    // Helper to format Markdown to HTML safely
    function formatMarkdown(text) {
        // Escape HTML to prevent XSS
        let escaped = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
        
        // Bold: **text**
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Italic: *text*
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Bullet points: - item or * item
        let lines = escaped.split('\n');
        let inList = false;
        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();
            if (line.startsWith('- ') || line.startsWith('* ')) {
                let content = line.substring(2);
                if (!inList) {
                    lines[i] = '<ul class="ps-3 mb-2"><li>' + content + '</li>';
                    inList = true;
                } else {
                    lines[i] = '<li>' + content + '</li>';
                }
            } else {
                if (inList) {
                    lines[i] = '</ul>' + lines[i];
                    inList = false;
                }
            }
        }
        if (inList) {
            lines[lines.length - 1] += '</ul>';
        }
        escaped = lines.join('\n');
        
        // Convert newlines to <br>
        escaped = escaped.replace(/\n/g, '<br>');
        
        return escaped;
    }

    // Send Message
    function sendMessage(text) {
        if (!text.trim()) return;

        // Append User Message
        appendMessage('user', text);
        aiInput.value = '';

        // Show Typing Indicator
        const typingId = showTyping();

        // Send AJAX request to AI Backend - renamed endpoint to bypass InfinityFree mod_security 403
        fetch(`${APP_URL}/ai/ask`, {
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

    if (aiSendBtn) {
        aiSendBtn.addEventListener('click', (e) => {
            e.preventDefault();
            sendMessage(aiInput.value);
        });
    }
    
    if (aiInput) {
        aiInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage(aiInput.value);
        });
    }

    // Suggestion Buttons
    suggestBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            sendMessage(btn.innerText);
        });
    });

    function appendMessage(role, text, shouldScroll = true) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-msg ai-msg-${role}`;
        if (role === 'bot') {
            msgDiv.innerHTML = formatMarkdown(text);
        } else {
            msgDiv.innerText = text;
        }
        aiChatMessages.appendChild(msgDiv);
        if (shouldScroll) {
            scrollToBottom();
        }
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
}

// Khởi chạy khi DOM đã sẵn sàng (phòng trường hợp Cloudflare/hosting load script bất đồng bộ)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAIChat);
} else {
    initAIChat();
}
