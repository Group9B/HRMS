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
            btn.addEventListener('click', async () => {
                const query = btn.dataset.query;
                if (query) {
                    // Hide Clock In/Out button immediately on click
                    if (query.toLowerCase().includes('clock in') || query.toLowerCase().includes('clock out')) {
                        btn.style.transition = 'all 0.3s ease';
                        btn.style.opacity = '0';
                        btn.style.transform = 'scale(0.8)';
                        setTimeout(() => btn.remove(), 300);
                    }
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
                if (data.client_action) {
                    this.handleClientAction(data.client_action);
                }
                this.addMessage(data.message, 'bot', data.type, data.source, data.widget);
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
    addMessage(content, sender, type = 'text', source = null, widget = null) {
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
        
        // Append Widget if present
        if (widget) {
            bubbleDiv.innerHTML += this.renderWidget(widget);
        }

        const timeDiv = document.createElement('div');
        timeDiv.className = 'nexus-time';
        
        // AI Badge
        if (source === 'groq' || source === 'gemini') {
            const badge = `<span class="nexus-ai-badge" title="Powered by AI"><i class="ti ti-sparkles"></i> AI</span> `;
            timeDiv.innerHTML = badge + this.formatTime(new Date());
        } else if (source === 'native') {
            const badge = `<span class="nexus-bot-badge" title="Rule-based Bot"><i class="ti ti-robot"></i> Bot</span> `;
            timeDiv.innerHTML = badge + this.formatTime(new Date());
        } else {
            timeDiv.textContent = this.formatTime(new Date());
        }

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
        
        // Bind widget actions
        if (widget && widget.actions) {
            const btns = messageDiv.querySelectorAll('.nexus-action-btn');
            btns.forEach(btn => {
                btn.addEventListener('click', () => {
                   const action = btn.dataset.action;
                   // Handle Quick Actions (like clock in/out) directly via chat
                   if (action === 'clock_in') this.sendQuickQuery('Clock In');
                   if (action === 'clock_out') this.sendQuickQuery('Clock Out');
                   if (action === 'apply_leave') window.location.href = '/hrms/employee/leave.php'; 
                });
            });
        }
    }

    sendQuickQuery(query) {
        this.inputField.value = query;
        this.sendMessage();
    }

    /**
     * Format message content (simple markdown-like parsing)
     */
    formatMessage(content) {
        if (!content) return '';

        // Allow rich cards without escaping
        if (content.trim().startsWith('<div class="nexus-card')) {
            return content;
        }

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
    /**
     * Handle client-side actions (like theme switching)
     */
    handleClientAction(action) {
        if (!action || !action.type) return;

        switch (action.type) {
            case 'change_theme':
                if (action.theme === 'dark') {
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                    // If there's a theme toggler button on page, update its icon/state if needed
                    const themeIcon = document.querySelector('.theme-toggler i');
                    if (themeIcon) {
                        themeIcon.className = themeIcon.className.replace('ti-moon', 'ti-sun');
                    }
                } else {
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                    localStorage.setItem('theme', 'light');
                    // If there's a theme toggler button on page, update its icon/state if needed
                    const themeIcon = document.querySelector('.theme-toggler i');
                    if (themeIcon) {
                        themeIcon.className = themeIcon.className.replace('ti-sun', 'ti-moon');
                    }
                }
                break;

            case 'toggle_theme':
                const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                // Update icon if exists
                const toggleIcon = document.querySelector('.theme-toggler i');
                if (toggleIcon) {
                    if (newTheme === 'dark') {
                        toggleIcon.className = toggleIcon.className.replace('ti-moon', 'ti-sun');
                    } else {
                        toggleIcon.className = toggleIcon.className.replace('ti-sun', 'ti-moon');
                    }
                }
                break;
        }
    }

    /**
     * Render dynamic widget
     */
    renderWidget(widget) {
        if (!widget) return '';
        
        switch (widget.type) {
            case 'attendance': return this.renderAttendanceWidget(widget);
            case 'leave': return this.renderLeaveWidget(widget);
            case 'tasks': return this.renderTasksWidget(widget);
            case 'team': return this.renderTeamWidget(widget);
            default: return '';
        }
    }

    renderAttendanceWidget(data) {
        const statusClass = data.today.status === 'active' ? 'text-success' : (data.today.status === 'completed' ? 'text-secondary' : 'text-warning');
        const statusLabel = data.today.status === 'active' ? 'Clocked In' : (data.today.status === 'completed' ? 'Clocked Out' : 'Not Started');
        
        // Safety check for null times
        const checkIn = data.today.check_in ? data.today.check_in.substring(0,5) : '--:--';
        const checkOut = data.today.check_out ? data.today.check_out.substring(0,5) : '--:--';
        
        let html = `
            <div class="nexus-widget-card">
                <div class="nexus-widget-header">
                    <span><i class="ti ti-calendar-check"></i> Attendance</span>
                    <span class="nexus-badge ${statusClass}">${statusLabel}</span>
                </div>
                <div class="nexus-widget-body">
                    <div class="nexus-stat-row">
                        <div>
                            <small>Check In</small>
                            <strong>${checkIn}</strong>
                        </div>
                        <div>
                            <small>Check Out</small>
                            <strong>${checkOut}</strong>
                        </div>
                    </div>
                    <div class="nexus-mini-chart mt-2">
                        <div class="d-flex justify-content-between text-muted x-small">
                            <span>Week Hours</span>
                            <span>${data.week.total_hours}h</span>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-primary" style="width: ${Math.min((data.week.total_hours/40)*100, 100)}%"></div>
                        </div>
                    </div>
                </div>
                ${this.renderActions(data.actions)}
            </div>
        `;
        return html;
    }

    renderLeaveWidget(data) {
        let balancesHtml = data.balances.map(b => `
            <div class="mb-2">
                <div class="d-flex justify-content-between small mb-1">
                    <span>${b.type}</span>
                    <span>${b.remaining}/${b.total}</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-${b.percentage > 80 ? 'danger' : 'success'}" style="width: ${b.percentage}%"></div>
                </div>
            </div>
        `).join('');

        return `
            <div class="nexus-widget-card">
                <div class="nexus-widget-header">
                    <span><i class="ti ti-beach"></i> Leave Balance</span>
                </div>
                <div class="nexus-widget-body">
                    ${balancesHtml}
                </div>
                ${this.renderActions(data.actions)}
            </div>
        `;
    }

    renderTasksWidget(data) {
        if (!data.tasks || data.tasks.length === 0) {
            return `<div class="nexus-widget-card"><div class="nexus-widget-body text-center text-muted">No pending tasks! 🎉</div></div>`;
        }

        let tasksHtml = data.tasks.map(t => `
            <div class="nexus-task-item ${t.priority}">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div class="text-truncate" style="max-width: 180px;">${t.title}</div>
                    <span class="badge bg-${t.status === 'completed' ? 'success' : 'secondary'} x-small">${t.status}</span>
                </div>
                <div class="d-flex justify-content-between mt-1 text-muted x-small">
                    <span><i class="ti ti-calendar"></i> ${t.due_date}</span>
                    ${t.priority === 'high' ? '<span class="text-danger">High Priority</span>' : ''}
                </div>
            </div>
        `).join('');

        return `
            <div class="nexus-widget-card">
                <div class="nexus-widget-header">
                    <span><i class="ti ti-list-check"></i> Pending Tasks (${data.total_pending})</span>
                </div>
                <div class="nexus-widget-body p-0">
                    ${tasksHtml}
                </div>
            </div>
        `;
    }

    renderTeamWidget(data) {
        let membersHtml = data.members.map(m => `
            <div class="nexus-team-member d-flex justify-content-between align-items-center p-2 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="avatar x-small me-2 bg-${m.status === 'present' ? 'success' : 'secondary'} text-white rounded-circle flex-shrink-0">
                        ${m.name.charAt(0)}
                    </div>
                    <div>
                        <div class="fw-bold small">${m.name}</div>
                        <div class="text-muted x-small">${m.department}</div>
                    </div>
                </div>
                <span class="badge bg-${m.status === 'present' ? 'success-lt' : 'secondary-lt'}">${m.status}</span>
            </div>
        `).join('');

        return `
            <div class="nexus-widget-card">
                <div class="nexus-widget-header">
                    <span><i class="ti ti-users"></i> My Team</span>
                    <span class="badge bg-primary-lt">${data.summary.present}/${data.summary.total} Present</span>
                </div>
                <div class="nexus-widget-body p-0 scrollable" style="max-height: 200px; overflow-y: auto;">
                    ${membersHtml}
                </div>
            </div>
        `;
    }

    renderActions(actions) {
        if (!actions || actions.length === 0) return '';
        
        const btns = actions.map(pid => `
            <button class="btn btn-sm btn-${pid.style || 'primary'} w-100 mt-2 nexus-action-btn" data-action="${pid.action}">
                ${pid.label}
            </button>
        `).join('');
        
        return `<div class="nexus-widget-footer">${btns}</div>`;
    }
}

// Initialize NexusBot when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.nexusBot = new NexusBot();
});
