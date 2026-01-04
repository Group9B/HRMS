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
<link rel="stylesheet" href="/hrms/nexusbot/assets/nexusbot.css?v=<?php echo time(); ?>">

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
        <?php
        // Dynamic Quick Actions
        $qa_user_id = $_SESSION['user_id'] ?? 0;
        $qa_role_id = $_SESSION['role_id'] ?? 0;

        // Default Actions
        $actions = [
            ['icon' => 'ti ti-calendar-check', 'label' => 'Attendance', 'query' => 'Check my attendance'],
            ['icon' => 'ti ti-beach', 'label' => 'Leave', 'query' => 'Show my leave balance'],
            ['icon' => 'ti ti-moon-stars', 'label' => 'Theme', 'query' => 'Toggle theme']
        ];

        if ($qa_user_id) {
            // Check Attendance Status for Clock In/Out
            // Re-using strict SQL to ensure valid employee link
            $qa_emp_query = "SELECT id FROM employees WHERE user_id = '$qa_user_id' LIMIT 1";
            $qa_emp_res = $mysqli->query($qa_emp_query);
            if ($qa_emp_res && $qa_emp_res->num_rows > 0) {
                $qa_emp = $qa_emp_res->fetch_assoc();
                $qa_emp_id = $qa_emp['id'];
                $today = date('Y-m-d');

                $qa_att_query = "SELECT check_in, check_out FROM attendance WHERE employee_id = '$qa_emp_id' AND date = '$today' LIMIT 1";
                $qa_att_res = $mysqli->query($qa_att_query);

                if ($qa_att_res && $qa_att_res->num_rows > 0) {
                    $qa_att = $qa_att_res->fetch_assoc();
                    if ($qa_att['check_in'] && !$qa_att['check_out']) {
                        // Clocked In -> Show Clock Out
                        array_unshift($actions, ['icon' => 'ti ti-clock-stop', 'label' => 'Clock Out', 'query' => 'Clock Out']);
                    } else {
                        // Already done for today (Clocked out)
                        // Maybe show nothing or summary? stick to default
                    }
                } else {
                    // Not Clocked In -> Show Clock In
                    array_unshift($actions, ['icon' => 'ti ti-clock-play', 'label' => 'Clock In', 'query' => 'Clock In']);
                }
            }
        }

        // Manager Actions
        if ($qa_role_id == 6) { // Manager
            $actions[] = ['icon' => 'ti ti-users', 'label' => 'My Team', 'query' => 'Show my team'];
            $actions[] = ['icon' => 'ti ti-check', 'label' => 'Approvals', 'query' => 'Show pending leave requests'];
        }

        // Render Buttons
        foreach ($actions as $action) {
            echo '<button class="nexus-quick-btn" data-query="' . htmlspecialchars($action['query']) . '">';
            echo '<i class="' . $action['icon'] . '"></i> ' . htmlspecialchars($action['label']);
            echo '</button>';
        }
        ?>
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
<script src="/hrms/nexusbot/assets/nexusbot.js?v=<?php echo time(); ?>"></script>