// ===== STAR RATING =====
document.addEventListener('DOMContentLoaded', function () {

    // Star rating interaction
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('ratingInput');

    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const value = index + 1;
            if (ratingInput) ratingInput.value = value;
            stars.forEach((s, i) => {
                s.classList.toggle('active', i < value);
            });
        });
        star.addEventListener('mouseover', () => {
            stars.forEach((s, i) => s.classList.toggle('active', i <= index));
        });
    });

    const starContainer = document.querySelector('.star-rating');
    if (starContainer) {
        starContainer.addEventListener('mouseleave', () => {
            const val = ratingInput ? parseInt(ratingInput.value) || 0 : 0;
            stars.forEach((s, i) => s.classList.toggle('active', i < val));
        });
    }

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert-auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.style.boxShadow = window.scrollY > 10
                ? '0 4px 20px rgba(108,99,255,0.15)'
                : '0 2px 8px rgba(108,99,255,0.08)';
        });
    }

    // Animate stat numbers
    const statValues = document.querySelectorAll('.stat-value[data-target]');
    statValues.forEach(el => {
        const target = parseInt(el.getAttribute('data-target'));
        let current = 0;
        const step = Math.ceil(target / 40);
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString();
            if (current >= target) clearInterval(timer);
        }, 30);
    });

    // Mobile navbar toggle
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
        });
    }

    // Ticket count validation
    const ticketInput = document.getElementById('ticketsBooked');
    const maxSeats = document.getElementById('maxSeats');
    if (ticketInput && maxSeats) {
        ticketInput.addEventListener('input', () => {
            const max = parseInt(maxSeats.value);
            const val = parseInt(ticketInput.value);
            if (val > max) {
                ticketInput.value = max;
            }
        });
    }

    // Confirm delete
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // ===== CHATBOT WIDGET =====
    const path = window.location.pathname;
    if (path.includes('/login') || path.includes('/register') || path.includes('/signup')) {
        if (window.location.search.includes('logout=true')) {
            sessionStorage.removeItem('chatHistory');
        }
        return; // Do not inject chatbot on auth pages
    }

    // Inject chatbot HTML
    const chatbotHtml = `
        <div id="chatbot-widget" style="position:fixed;bottom:20px;right:20px;z-index:9999;font-family:'Inter',sans-serif;">
            <button id="chatbot-toggle" style="width:60px;height:60px;border-radius:50%;background:var(--primary);color:#0f0f0f;border:none;box-shadow:0 4px 12px rgba(212,175,55,0.3);font-size:1.5rem;cursor:pointer;transition:transform 0.3s;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-robot"></i>
            </button>
            <div id="chatbot-window" style="display:none;width:320px;height:450px;background:#151515;border:1px solid var(--border-color);border-radius:var(--radius-lg);position:absolute;bottom:70px;right:0;box-shadow:0 8px 24px rgba(0,0,0,0.4);flex-direction:column;overflow:hidden;">
                <div style="background:var(--card-bg);padding:1rem;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;">
                    <h4 style="margin:0;font-size:1rem;color:var(--text-dark);"><i class="fas fa-robot" style="color:var(--primary);margin-right:0.5rem;"></i> Smart Assistant</h4>
                    <button id="chatbot-close" style="background:none;border:none;color:var(--text-muted);cursor:pointer;"><i class="fas fa-times"></i></button>
                </div>
                <div id="chatbot-messages" style="flex:1;padding:1rem;overflow-y:auto;display:flex;flex-direction:column;gap:0.75rem;">
                </div>
                <div style="padding:0.75rem;border-top:1px solid var(--border-color);display:flex;gap:0.5rem;background:var(--card-bg);">
                    <input type="text" id="chatbot-input" placeholder="Type a message..." style="flex:1;background:#0f0f0f;border:1px solid var(--border-color);color:var(--text-dark);padding:0.5rem;border-radius:var(--radius-sm);outline:none;font-size:0.88rem;"/>
                    <button id="chatbot-send" style="background:var(--primary);color:#0f0f0f;border:none;padding:0 0.75rem;border-radius:var(--radius-sm);cursor:pointer;"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', chatbotHtml);

    const toggleBtn = document.getElementById('chatbot-toggle');
    const closeBtn = document.getElementById('chatbot-close');
    const chatWindow = document.getElementById('chatbot-window');
    const chatInput = document.getElementById('chatbot-input');
    const chatSend = document.getElementById('chatbot-send');
    const chatMessages = document.getElementById('chatbot-messages');

    // Restore history
    let history = JSON.parse(sessionStorage.getItem('chatHistory') || '[]');
    if (history.length === 0) {
        history.push({ text: "Hello! I am your SmartCampus Assistant. Ask me about events or schedule clashes!", isUser: false });
        sessionStorage.setItem('chatHistory', JSON.stringify(history));
    }

    function renderMessage(text, isUser, save = true) {
        const msgDiv = document.createElement('div');
        msgDiv.style.padding = '0.75rem';
        msgDiv.style.borderRadius = '0.5rem';
        msgDiv.style.fontSize = '0.88rem';
        msgDiv.style.maxWidth = '85%';
        msgDiv.style.lineHeight = '1.4';
        
        if (isUser) {
            msgDiv.style.alignSelf = 'flex-end';
            msgDiv.style.background = '#2c2c2c';
            msgDiv.style.color = '#fff';
        } else {
            msgDiv.style.alignSelf = 'flex-start';
            msgDiv.style.background = 'rgba(212,175,55,0.05)';
            msgDiv.style.color = 'var(--text-dark)';
            msgDiv.style.borderLeft = '2px solid var(--primary)';
            text = text.replace(/\n/g, '<br/>');
        }
        msgDiv.innerHTML = text;
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        if (save) {
            history.push({ text: text, isUser: isUser });
            sessionStorage.setItem('chatHistory', JSON.stringify(history));
        }
    }

    // Render loaded history
    history.forEach(msg => renderMessage(msg.text, msg.isUser, false));

    function toggleChat() {
        if (chatWindow.style.display === 'none') {
            chatWindow.style.display = 'flex';
            toggleBtn.style.transform = 'scale(0)';
            setTimeout(() => {
                chatInput.focus();
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, 100);
        } else {
            chatWindow.style.display = 'none';
            toggleBtn.style.transform = 'scale(1)';
        }
    }

    toggleBtn.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    async function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        renderMessage(text, true);
        chatInput.value = '';
        chatInput.disabled = true;
        chatSend.disabled = true;

        try {
            const response = await fetch('/api/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });

            if (response.status === 401) {
                renderMessage("Please log in to use the chatbot.", false);
            } else {
                const data = await response.json();
                renderMessage(data.reply || "No response received.", false);
            }
        } catch (error) {
            renderMessage("Network error. Please try again later.", false);
        } finally {
            chatInput.disabled = false;
            chatSend.disabled = false;
            chatInput.focus();
        }
    }

    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
});
