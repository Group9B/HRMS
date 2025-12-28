<?php
/**
 * chat_widget.php
 * NexusBot Chat Widget Component
 * 
 * Include this file in your layout to add the chatbot
 * Usage: <?php include __DIR__ . '/../nexusbot/chat_widget.php'; ?>
 * 
 * Requirements:
 * - User must be logged in (checks session)
 * - Tabler Icons CSS must be loaded
 */

// Only show if user is logged in
if (!isset($_SESSION['user_id'])) {
    return;
}
?>

<!-- NexusBot Chat Widget CSS -->
<link rel="stylesheet" href="/hrms/nexusbot/assets/nexusbot.css">

<!-- NexusBot Toggle Button -->
<button class="nexus-toggle-btn" id="nexus-toggle" aria-label="Open chat assistant">
    <i class="ti ti-message-chatbot"></i>
    <i class="ti ti-x"></i>
</button>

<!-- NexusBot Chat Window -->
<div class="nexus-chat-window" id="nexus-chat-window">
    <!-- Header -->
    <div class="nexus-header">
        <div class="nexus-avatar">
            <i class="ti ti-robot"></i>
        </div>
        <div class="nexus-info">
            <h4 class="nexus-name">NexusBot</h4>
            <div class="nexus-status">
                <span class="nexus-status-dot"></span>
                <span>Online • HRMS Assistant</span>
            </div>
        </div>
        <button class="nexus-close-btn" id="nexus-close" aria-label="Close chat">
            <i class="ti ti-x"></i>
        </button>
    </div>

    <!-- Messages Container -->
    <div class="nexus-messages" id="nexus-messages">
        <!-- Messages will be added here dynamically -->
    </div>

    <!-- Quick Actions -->
    <div class="nexus-quick-actions">
        <button class="nexus-quick-btn" data-query="What's my attendance today?">
            <i class="ti ti-calendar-check"></i> Attendance
        </button>
        <button class="nexus-quick-btn" data-query="What is my leave balance?">
            <i class="ti ti-beach"></i> Leave
        </button>
        <button class="nexus-quick-btn" data-query="Show my latest payslip">
            <i class="ti ti-receipt"></i> Payslip
        </button>
        <button class="nexus-quick-btn" data-query="What are my tasks?">
            <i class="ti ti-checklist"></i> Tasks
        </button>
        <button class="nexus-quick-btn" data-query="When is the next holiday?">
            <i class="ti ti-confetti"></i> Holidays
        </button>
    </div>

    <!-- Input Area -->
    <div class="nexus-input-area">
        <div class="nexus-input-wrapper">
            <textarea class="nexus-input" id="nexus-input" placeholder="Ask me anything..." rows="1"
                maxlength="500"></textarea>
        </div>
        <button class="nexus-send-btn" id="nexus-send" disabled aria-label="Send message">
            <i class="ti ti-send"></i>
        </button>
    </div>
</div>

<!-- NexusBot JavaScript -->
<script src="/hrms/nexusbot/assets/nexusbot.js"></script>