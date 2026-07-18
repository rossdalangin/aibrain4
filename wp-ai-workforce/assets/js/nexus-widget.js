/**
 * Nexus AI Frontend Chat Widget
 */
(function() {
    const config = window.nexus_chat_config || {};

    function init() {
        createUI();
        bindEvents();
    }

    function createUI() {
        const container = document.createElement('div');
        container.id = 'nexus-chat-widget-container';
        container.innerHTML = `
            <div id="nexus-chat-window">
                <div class="nexus-chat-header">
                    <div class="nexus-chat-agent-avatar">${config.agent_name ? config.agent_name[0] : 'A'}</div>
                    <div class="nexus-chat-header-info">
                        <h3>${config.agent_name || 'AI Assistant'}</h3>
                        <p>${config.agent_position || 'Support Agent'}</p>
                    </div>
                </div>
                <div id="nexus-chat-messages">
                    <div class="nexus-msg nexus-msg-ai">
                        Hello! I am ${config.agent_name}, your ${config.agent_position}. How can I assist you today?
                    </div>
                </div>
                <div class="nexus-chat-input-container">
                    <input type="text" id="nexus-chat-input" placeholder="Type your message...">
                    <button id="nexus-chat-send" class="nexus-chat-send-btn">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                    </button>
                </div>
            </div>
            <div id="nexus-chat-trigger">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </div>
        `;
        document.body.appendChild(container);
    }

    function bindEvents() {
        const trigger = document.getElementById('nexus-chat-trigger');
        const windowEl = document.getElementById('nexus-chat-window');
        const input = document.getElementById('nexus-chat-input');
        const sendBtn = document.getElementById('nexus-chat-send');

        trigger.addEventListener('click', () => {
            windowEl.classList.toggle('active');
        });

        const sendMessage = async () => {
            const text = input.value.trim();
            if (!text) return;

            appendMessage(text, 'user');
            input.value = '';

            const typingId = showTyping();

            try {
                const response = await fetch(`${config.rest_url}nexus-ai/v1/public/chat`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        message: text,
                        session_id: getSessionId()
                    })
                });
                const data = await response.json();
                removeTyping(typingId);
                const responseText = data ? (data.response || data.message || data.error || 'No response from agent') : 'No response from agent';
                appendMessage(responseText, 'ai');
            } catch (e) {
                removeTyping(typingId);
                appendMessage("I'm sorry, I'm having trouble connecting right now. Please try again later.", 'ai');
            }
        };

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    }

    function appendMessage(text, sender) {
        const msgArea = document.getElementById('nexus-chat-messages');
        const msg = document.createElement('div');
        msg.className = `nexus-msg nexus-msg-${sender}`;
        msg.innerText = text;
        msgArea.appendChild(msg);
        msgArea.scrollTop = msgArea.scrollHeight;
    }

    function showTyping() {
        const id = 'typing-' + Date.now();
        const msgArea = document.getElementById('nexus-chat-messages');
        const div = document.createElement('div');
        div.id = id;
        div.className = 'nexus-typing';
        div.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
        msgArea.appendChild(div);
        msgArea.scrollTop = msgArea.scrollHeight;
        return id;
    }

    function removeTyping(id) {
        document.getElementById(id)?.remove();
    }

    function getSessionId() {
        let sid = localStorage.getItem('nexus_session_id');
        if (!sid) {
            sid = 'sess_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('nexus_session_id', sid);
        }
        return sid;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
