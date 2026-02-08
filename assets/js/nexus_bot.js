
(function() {
    'use strict';

    // Configuration
    const BOT_NAME = 'Nexus';
    const WELCOME_MESSAGE = "Hi! I'm Nexus, your virtual assistant. I can tell you about our HRMS features, pricing, and benefits. How can I help you today?";
    
    // Knowledge Base (Simple Keyword Matching)
    // Structure: keywords (array of strings), response (string)
    const KNOWLEDGE_BASE = [
        {
            keywords: ['hello', 'hi', 'hey', 'greetings', 'start'],
            response: "Hello! I'm here to help you understand StaffSync. Ask me about features, payroll, or how to get started."
        },
        {
            keywords: ['pricing', 'cost', 'subscription', 'price', 'plan'],
            response: "We offer flexible pricing plans tailored to your business size. You can start with a 14-day free trial. Check our <a href='/hrms/subscription/purchase.php' class='text-primary'>Pricing Page</a> for details."
        },
        {
            keywords: ['feature', 'function', 'what can you do', 'capabilities'],
            response: "StaffSync is packed with features: <ul><li>Smart Attendance & Geofencing</li><li>Automated Payroll & Tax</li><li>Performance Appraisals</li><li>Recruitment & Onboarding</li><li>Leave Management</li></ul> Ask me about a specific feature for more info!"
        },
        {
            keywords: ['payroll', 'salary', 'tax', 'pay'],
            response: "Our Payroll module automates salary calculations, handles statutory deductions (taxes, PF), and generates payslips instantly. It integrates directly with attendance data."
        },
        {
            keywords: ['attendance', 'tracking', 'clock', 'time'],
            response: "Attendance tracking uses biometrics or mobile geofencing. Employees can clock in/out via mobile, and managers get real-time reports on lateness and overtime."
        },
        {
            keywords: ['leave', 'holiday', 'vacation', 'off'],
            response: "Employees can apply for leave directly from their dashboard. Managers receive instant notifications for approval. We support custom leave policies and holiday calendars."
        },
        {
            keywords: ['recruit', 'hiring', 'onboard', 'candidate'],
            response: "Recruitment is streamlined from job posting to onboarding. Track applicants, schedule interviews, and send offer letters—all within StaffSync."
        },
        {
            keywords: ['security', 'safe', 'data', 'privacy'],
            response: "Security is our top priority. We use bank-grade encryption for your data, ensure GDPR compliance, and perform regular backups. Your employee data is safe with us."
        },
        {
            keywords: ['demo', 'trial', 'try', 'test'],
            response: "You can book a free demo or start a 14-day trial without a credit card. <a href='/hrms/register.php' class='text-primary'>Click here to Sign Up</a>."
        },
        {
            keywords: ['support', 'help', 'contact'],
            response: "Our support team is available 24/7. You can contact us via email at support@staffsync.com or use the help desk in your dashboard."
        },
        {
            keywords: ['benefits', 'advantage', 'why'],
            response: "StaffSync saves you time and money by automating routine tasks. It improves employee engagement and provides data-driven insights. Check our <a href='/hrms/benefits.php' class='text-primary'>Benefits Page</a>."
        }
    ];

    const DEFAULT_RESPONSE = "I'm not sure I understand. I can answer questions about our features, pricing, or security. Could you rephrase that?";

    // Inject CSS
    const style = document.createElement('style');
    style.textContent = `
        #nexus-bot-launcher {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        #nexus-bot-launcher:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        #nexus-bot-launcher i {
            color: white;
            font-size: 32px;
        }
        #nexus-bot-container {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            max-width: 90%;
            height: 500px;
            max-height: 70vh;
            background: var(--bs-body-bg, #fff);
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            z-index: 9999;
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
            transition: opacity 0.3s ease, transform 0.3s ease;
            overflow: hidden;
        }
        #nexus-bot-container.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }
        .nexus-header {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nexus-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }
        .nexus-message {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            line-height: 1.4;
            word-wrap: break-word;
        }
        .nexus-message.bot {
            background-color: #f1f3f5;
            color: #333;
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }
        [data-bs-theme="dark"] .nexus-message.bot {
            background-color: #343a40;
            color: #e9ecef;
        }
        .nexus-message.user {
            background-color: #0d6efd;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
        }
        .nexus-input-area {
            padding: 10px;
            border-top: 1px solid var(--bs-border-color);
            display: flex;
            gap: 10px;
            background-color: var(--bs-body-bg);
        }
        .nexus-input-area input {
            flex: 1;
            border-radius: 20px;
            padding: 8px 15px;
            border: 1px solid var(--bs-border-color);
            outline: none;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }
        .nexus-input-area button {
            background: #0d6efd;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }
        .nexus-input-area button:hover {
            background: #0b5ed7;
        }
    `;
    document.head.appendChild(style);

    // Inject HTML
    const launcher = document.createElement('div');
    launcher.id = 'nexus-bot-launcher';
    launcher.innerHTML = '<i class="ti ti-message-chatbot"></i>';
    launcher.title = "Chat with Nexus";
    document.body.appendChild(launcher);

    const container = document.createElement('div');
    container.id = 'nexus-bot-container';
    container.innerHTML = `
        <div class="nexus-header">
            <div class="d-flex align-items-center gap-2">
                <i class="ti ti-robot fs-4"></i>
                <span class="fw-bold">Nexus Assistant</span>
            </div>
            <button id="nexus-close" class="btn btn-sm text-white border-0"><i class="ti ti-x fs-5"></i></button>
        </div>
        <div class="nexus-messages" id="nexus-messages"></div>
        <div class="nexus-input-area">
            <input type="text" id="nexus-input" placeholder="Type a message..." aria-label="Type message">
            <button id="nexus-send" aria-label="Send"><i class="ti ti-send"></i></button>
        </div>
    `;
    document.body.appendChild(container);

    // DOM Elements
    const messagesContainer = document.getElementById('nexus-messages');
    const inputField = document.getElementById('nexus-input');
    const sendButton = document.getElementById('nexus-send');
    const closeButton = document.getElementById('nexus-close');

    // State
    let isOpen = false;

    // Functions
    function toggleChat() {
        isOpen = !isOpen;
        if (isOpen) {
            container.classList.add('active');
            inputField.focus();
            if (messagesContainer.children.length === 0) {
                addMessage(WELCOME_MESSAGE, 'bot');
            }
        } else {
            container.classList.remove('active');
        }
    }

    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('nexus-message', sender);
        // Securely set HTML content for bot messages (trusted source), text for user (untrusted)
        if (sender === 'bot') {
            messageDiv.innerHTML = text; // Allow HTML links in bot responses
        } else {
            messageDiv.textContent = text;
        }
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function getBotResponse(userText) {
        const lowerText = userText.toLowerCase();
        
        // Check for exact matches first, then partials
        for (const item of KNOWLEDGE_BASE) {
            // Using logic: if ANY keyword is found in the user text
            if (item.keywords.some(keyword => lowerText.includes(keyword))) {
                return item.response;
            }
        }
        return DEFAULT_RESPONSE;
    }

    function handleSend() {
        const text = inputField.value.trim();
        if (!text) return;

        // User message
        addMessage(text, 'user');
        inputField.value = '';

        // Bot response (simulated delay)
        setTimeout(() => {
            const response = getBotResponse(text);
            addMessage(response, 'bot');
        }, 500);
    }

    // Event Listeners
    launcher.addEventListener('click', toggleChat);
    closeButton.addEventListener('click', toggleChat);
    
    sendButton.addEventListener('click', handleSend);
    
    inputField.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSend();
        }
    });

})();
