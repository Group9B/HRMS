/**
 * NexusBot Chat Widget JavaScript
 * Handles chat interactions, message sending, and UI updates
 */

class NexusBot {
    constructor() {
        this.apiUrl = '/hrms/nexusbot/api.php';
        this.isOpen = false;
        this.isTyping = false;
        this.messageHistory = [];
        
        this.init();
    }

    /**
     * Initialize the chat widget
     */
    init() {
        this.toggleBtn = document.getElementById('nexus-toggle');
        this.chatWindow = document.getElementById('nexus-chat-window');
        this.messagesContainer = document.getElementById('nexus-messages');
        this.inputField = document.getElementById('nexus-input');
        this.sendBtn = document.getElementById('nexus-send');
        this.closeBtn = document.getElementById('nexus-close');
        this.quickActions = document.querySelectorAll('.nexus-quick-btn');

        if (!this.toggleBtn || !this.chatWindow) {
            console.error('NexusBot: Required elements not found');
            return;
        }

        this.bindEvents();
        this.loadWelcomeMessage();
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Toggle button
        this.toggleBtn.addEventListener('click', () => this.toggle());
        
        // Close button
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.close());
        }

        // Send button
        if (this.sendBtn) {
            this.sendBtn.addEventListener('click', () => this.sendMessage());
        }

        // Input field - Enter to send, Shift+Enter for new line
        if (this.inputField) {
            this.inputField.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // Auto-resize textarea
            this.inputField.addEventListener('input', () => {
                this.inputField.style.height = 'auto';
                this.inputField.style.height = Math.min(this.inputField.scrollHeight, 100) + 'px';
                this.updateSendButton();
            });
        }

        // Quick action buttons
        this.quickActions.forEach(btn => {
            btn.addEventListener('click', () => {
                const query = btn.dataset.query;
                if (query) {
                    this.inputField.value = query;
                    this.sendMessage();
                }
            });
        });

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    }

    /**
     * Toggle chat window
     */
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    /**
     * Open chat window
     */
    open() {
        this.isOpen = true;
        this.chatWindow.classList.add('open');
        this.toggleBtn.classList.add('active');
        
        // Focus input
        setTimeout(() => {
            if (this.inputField) {
                this.inputField.focus();
            }
        }, 300);

        // Scroll to bottom
        this.scrollToBottom();
    }

    /**
     * Close chat window
     */
    close() {
        this.isOpen = false;
        this.chatWindow.classList.remove('open');
        this.toggleBtn.classList.remove('active');
    }

    /**
     * Send message to API
     */
    async sendMessage() {
        const message = this.inputField.value.trim();
        
        if (!message || this.isTyping) {
            return;
        }

        // Handle local commands
        if (message.startsWith('/')) {
            const cmd = message.substring(1).toLowerCase();
            if (cmd === 'clear' || cmd === 'reset') {
                this.clearMessages();
                this.inputField.value = '';
                return;
            }
        }

        // Clear input
        this.inputField.value = '';
        this.inputField.style.height = 'auto';
        this.updateSendButton();

        // Add user message to chat
        this.addMessage(message, 'user');

        // Show typing indicator
        this.showTyping();

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message })
            });

            // Handle non-200 responses
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                this.hideTyping();
                this.addMessage(errorData.message || `Error: ${response.status}`, 'bot', 'error');
                return;
            }

            const data = await response.json();

            // Hide typing indicator
            this.hideTyping();

            if (data.success) {
                this.addMessage(data.message, 'bot', data.type);
            } else {
                this.addMessage(data.message || 'An error occurred. Please try again.', 'bot', 'error');
            }

        } catch (error) {
            console.error('NexusBot Error:', error);
            this.hideTyping();
            this.addMessage('Unable to connect. Please check your connection and try again.', 'bot', 'error');
        }

        // Focus back on input
        this.inputField.focus();
    }

    /**
     * Clear all messages from UI and session
     */
    async clearMessages() {
        try {
            await fetch(this.apiUrl, {
                method: 'POST',
                body: JSON.stringify({ message: '/clear' })
            });
        } catch (e) {}
        
        this.messagesContainer.innerHTML = '';
        this.messageHistory = [];
        this.loadWelcomeMessage();
        this.addMessage('Conversation history cleared!', 'bot', 'system');
    }

    /**
     * Add message to chat
     */
    addMessage(content, sender, type = 'text') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `nexus-message ${sender} type-${type}`;

        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = 'nexus-bubble';
        
        // Special icons for different types
        let icon = '';
        if (type === 'security') icon = '<i class="ti ti-shield-lock" style="color: #dc3545; margin-right: 8px;"></i>';
        if (type === 'error') icon = '<i class="ti ti-alert-circle" style="color: #dc3545; margin-right: 8px;"></i>';
        if (type === 'action') icon = '<i class="ti ti-bolt" style="color: #ffc107; margin-right: 8px;"></i>';
        if (type === 'system') icon = '<i class="ti ti-settings" style="color: #6c757d; margin-right: 8px;"></i>';

        // Format content (convert markdown-like syntax to HTML)
        bubbleDiv.innerHTML = icon + this.formatMessage(content);

        const timeDiv = document.createElement('div');
        timeDiv.className = 'nexus-time';
        timeDiv.textContent = this.formatTime(new Date());

        messageDiv.appendChild(bubbleDiv);
        messageDiv.appendChild(timeDiv);

        // Remove welcome message if exists
        const welcome = this.messagesContainer.querySelector('.nexus-welcome');
        if (welcome) {
            welcome.remove();
        }

        this.messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();

        // Save to history
        this.messageHistory.push({ content, sender, type, timestamp: new Date() });
    }

    /**
     * Format message content (simple markdown-like parsing)
     */
    formatMessage(content) {
        if (!content) return '';

        // Escape HTML first
        let formatted = content
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Bold: **text** or __text__
        formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formatted = formatted.replace(/__(.*?)__/g, '<strong>$1</strong>');

        // Italic: *text* or _text_
        formatted = formatted.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Line breaks
        formatted = formatted.replace(/\n/g, '<br>');

        // Bullet points: • or - at start of line
        formatted = formatted.replace(/<br>• /g, '<br>&bull; ');
        formatted = formatted.replace(/<br>- /g, '<br>&bull; ');

        // Emojis are already supported

        return formatted;
    }

    /**
     * Format timestamp
     */
    formatTime(date) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    /**
     * Show typing indicator
     */
    showTyping() {
        this.isTyping = true;
        
        const typingDiv = document.createElement('div');
        typingDiv.className = 'nexus-message bot';
        typingDiv.id = 'nexus-typing-indicator';
        
        typingDiv.innerHTML = `
            <div class="nexus-typing">
                <div class="nexus-typing-dot"></div>
                <div class="nexus-typing-dot"></div>
                <div class="nexus-typing-dot"></div>
            </div>
        `;

        this.messagesContainer.appendChild(typingDiv);
        this.scrollToBottom();
    }

    /**
     * Hide typing indicator
     */
    hideTyping() {
        this.isTyping = false;
        
        const typingIndicator = document.getElementById('nexus-typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    /**
     * Scroll messages to bottom
     */
    scrollToBottom() {
        if (this.messagesContainer) {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }
    }

    /**
     * Update send button state
     */
    updateSendButton() {
        if (this.sendBtn) {
            this.sendBtn.disabled = !this.inputField.value.trim();
        }
    }

    /**
     * Load welcome message on first open
     */
    loadWelcomeMessage() {
        if (this.messagesContainer && this.messageHistory.length === 0) {
            const welcomeDiv = document.createElement('div');
            welcomeDiv.className = 'nexus-welcome';
            welcomeDiv.innerHTML = `
                <div class="nexus-welcome-icon">
                    <i class="ti ti-robot"></i>
                </div>
                <h4>Welcome to NexusBot!</h4>
                <p>I'm your HRMS assistant. Ask me about attendance, leaves, payslips, and more.</p>
            `;
            this.messagesContainer.appendChild(welcomeDiv);
        }
    }
}

// Initialize NexusBot when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.nexusBot = new NexusBot();
});
