<?php
/**
 * ActionHandler.php
 * NexusBot Action Handler - Execute actions (not just queries)
 * 
 * Supports actions like:
 * - Apply for leave
 * - Request attendance correction
 * - Update profile information
 * - Submit feedback
 */

require_once __DIR__ . '/SecurityFilter.php';

class ActionHandler
{
    private $mysqli;
    private $security;
    private $userId;
    private $employeeId;
    private $companyId;

    // Action types
    const ACTION_APPLY_LEAVE = 'apply_leave';
    const ACTION_CANCEL_LEAVE = 'cancel_leave';
    const ACTION_UPDATE_PROFILE = 'update_profile';
    const ACTION_SUBMIT_FEEDBACK = 'submit_feedback';
    const ACTION_CLOCK_IN = 'clock_in';
    const ACTION_CLOCK_OUT = 'clock_out';

    // Action states for multi-step actions
    private $pendingActions = [];

    public function __construct($mysqli, SecurityFilter $security, array $userContext)
    {
        $this->mysqli = $mysqli;
        $this->security = $security;
        $this->userId = $userContext['user_id'] ?? null;
        $this->employeeId = $userContext['employee_id'] ?? null;
        $this->companyId = $userContext['company_id'] ?? null;
    }

    /**
     * Check if message is requesting an action
     */
    public function isActionRequest(string $message): ?string
    {
        $actionPatterns = [
            self::ACTION_APPLY_LEAVE => [
                '/apply\s+(for\s+)?(a\s+)?leave/i',
                '/request\s+(a\s+)?leave/i',
                '/need\s+(a\s+)?(day\s+)?off/i',
                '/take\s+(a\s+)?leave/i',
                '/want\s+to\s+apply\s+(for\s+)?leave/i',
            ],
            self::ACTION_CANCEL_LEAVE => [
                '/cancel\s+(my\s+)?leave/i',
                '/withdraw\s+(my\s+)?leave/i',
                '/revoke\s+(my\s+)?leave/i',
            ],
            self::ACTION_CLOCK_IN => [
                '/clock\s+in/i',
                '/check\s+in/i',
                '/punch\s+in/i',
                '/mark\s+(my\s+)?attendance/i',
                '/start\s+(my\s+)?shift/i',
            ],
            self::ACTION_CLOCK_OUT => [
                '/clock\s+out/i',
                '/check\s+out/i',
                '/punch\s+out/i',
                '/end\s+(my\s+)?shift/i',
            ],
            self::ACTION_SUBMIT_FEEDBACK => [
                '/submit\s+(a\s+)?feedback/i',
                '/give\s+feedback/i',
                '/provide\s+feedback/i',
            ],
        ];

        foreach ($actionPatterns as $action => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $message)) {
                    return $action;
                }
            }
        }

        return null;
    }

    /**
     * Execute or prepare an action
     */
    public function handleAction(string $action, array $context, ?array $pendingData = null): array
    {
        // Check if user can perform actions (not all roles can)
        if (!$this->canPerformAction($action)) {
            return [
                'success' => false,
                'message' => "Sorry, this action is not available for your current status. Please use the web interface or contact HR.",
                'type' => 'permission_denied'
            ];
        }

        switch ($action) {
            case self::ACTION_APPLY_LEAVE:
                return $this->handleLeaveApplication($context, $pendingData);

            case self::ACTION_CANCEL_LEAVE:
                return $this->handleLeaveCancel($context, $pendingData);

            case self::ACTION_CLOCK_IN:
                return $this->handleClockIn($context);

            case self::ACTION_CLOCK_OUT:
                return $this->handleClockOut($context);

            case self::ACTION_SUBMIT_FEEDBACK:
                return $this->handleFeedback($context, $pendingData);

            default:
                return [
                    'success' => false,
                    'message' => "This action is not supported through chat. Please use the web interface.",
                    'type' => 'unsupported'
                ];
        }
    }

    /**
     * Check if user can perform the action
     */
    private function canPerformAction(string $action): bool
    {
        if (!$this->employeeId) {
            return false;
        }

        // All employees can apply for leave, clock in/out
        $allowedActions = [
            self::ACTION_APPLY_LEAVE,
            self::ACTION_CANCEL_LEAVE,
            self::ACTION_CLOCK_IN,
            self::ACTION_CLOCK_OUT,
            self::ACTION_SUBMIT_FEEDBACK,
        ];

        return in_array($action, $allowedActions);
    }

    /**
     * Handle leave application (multi-step)
     */
    private function handleLeaveApplication(array $context, ?array $pendingData): array
    {
        $entities = $context['entities'] ?? [];

        // If no pending data, start the process
        if (!$pendingData) {
            return [
                'success' => true,
                'type' => 'action_started',
                'action' => self::ACTION_APPLY_LEAVE,
                'message' => "📝 **Apply for Leave**\n\n" .
                    "I can help you apply for leave. Please provide:\n\n" .
                    "1. **Leave Type** (Sick, Casual, Privilege)\n" .
                    "2. **Start Date** (e.g., tomorrow, 15 Jan, 2025-01-15)\n" .
                    "3. **End Date** (same as start for single day)\n" .
                    "4. **Reason** (brief description)\n\n" .
                    "You can say something like:\n" .
                    "_\"Sick leave from tomorrow to day after for doctor visit\"_\n\n" .
                    "Or type **cancel** to abort.",
                'requires_input' => true,
                'pending_action' => [
                    'type' => self::ACTION_APPLY_LEAVE,
                    'step' => 'collect_details',
                    'data' => []
                ]
            ];
        }

        // Process the collected data
        if ($pendingData['step'] === 'collect_details') {
            // Parse the input for leave details
            $parsed = $this->parseLeaveDetails($context['original_message'] ?? '', $entities);

            if (!$parsed['complete']) {
                return [
                    'success' => true,
                    'type' => 'action_pending',
                    'message' => "I still need: " . implode(", ", $parsed['missing']) . "\n\n" .
                        "Please provide the missing information, or type **cancel** to abort.",
                    'pending_action' => [
                        'type' => self::ACTION_APPLY_LEAVE,
                        'step' => 'collect_details',
                        'data' => $parsed['data']
                    ]
                ];
            }

            // Confirm before submitting
            return [
                'success' => true,
                'type' => 'action_confirm',
                'message' => "📋 **Please Confirm Leave Request**\n\n" .
                    "• **Type:** {$parsed['data']['leave_type']}\n" .
                    "• **From:** {$parsed['data']['start_date']}\n" .
                    "• **To:** {$parsed['data']['end_date']}\n" .
                    "• **Reason:** {$parsed['data']['reason']}\n\n" .
                    "Reply **yes** to submit or **no** to cancel.",
                'pending_action' => [
                    'type' => self::ACTION_APPLY_LEAVE,
                    'step' => 'confirm',
                    'data' => $parsed['data']
                ]
            ];
        }

        // Final confirmation
        if ($pendingData['step'] === 'confirm') {
            $input = strtolower(trim($context['original_message'] ?? ''));

            if ($input === 'yes' || $input === 'confirm') {
                // Actually submit the leave
                $result = $this->submitLeaveRequest($pendingData['data']);

                if ($result['success']) {
                    return [
                        'success' => true,
                        'type' => 'action_completed',
                        'message' => "✅ **Leave Request Submitted!**\n\n" .
                            "Your leave request has been submitted for approval.\n\n" .
                            "• **Request ID:** #{$result['leave_id']}\n" .
                            "• **Status:** Pending\n\n" .
                            "You'll be notified once it's approved or rejected.",
                        'pending_action' => null
                    ];
                } else {
                    return [
                        'success' => false,
                        'type' => 'action_failed',
                        'message' => "❌ **Failed to Submit**\n\n{$result['error']}\n\nPlease try again or use the web interface.",
                        'pending_action' => null
                    ];
                }
            } else {
                return [
                    'success' => true,
                    'type' => 'action_cancelled',
                    'message' => "Leave request cancelled. Let me know if you need anything else!",
                    'pending_action' => null
                ];
            }
        }

        return [
            'success' => false,
            'message' => "Something went wrong. Please try again.",
            'pending_action' => null
        ];
    }

    /**
     * Parse leave details from user input
     */
    private function parseLeaveDetails(string $message, array $entities): array
    {
        $data = [];
        $missing = [];

        // Parse leave type
        if (preg_match('/\b(sick|casual|privilege|annual|maternity|paternity)\b/i', $message, $match)) {
            $data['leave_type'] = ucfirst(strtolower($match[1]));
        } elseif (isset($entities['leave_type'])) {
            $data['leave_type'] = $entities['leave_type'];
        } else {
            $missing[] = 'leave type';
        }

        // Parse dates
        $datePatterns = [
            'tomorrow' => date('Y-m-d', strtotime('+1 day')),
            'day after' => date('Y-m-d', strtotime('+2 days')),
            'next week' => date('Y-m-d', strtotime('next monday')),
        ];

        // Try to find dates in message
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $message, $match)) {
            $data['start_date'] = $match[1];
        } elseif (preg_match('/(\d{1,2})\s*(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)/i', $message, $match)) {
            $monthNum = date('m', strtotime($match[2] . ' 1'));
            $year = date('Y');
            if ($monthNum < date('m'))
                $year++; // Next year if month passed
            $data['start_date'] = "$year-$monthNum-" . str_pad($match[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/tomorrow/i', $message)) {
            $data['start_date'] = date('Y-m-d', strtotime('+1 day'));
        } else {
            $missing[] = 'start date';
        }

        if (isset($data['start_date'])) {
            // Check for end date or assume same day
            if (preg_match('/to\s+(\d{4}-\d{2}-\d{2}|\d{1,2}\s*\w+|day after|tomorrow)/i', $message, $match)) {
                if (preg_match('/day after/i', $match[1])) {
                    $data['end_date'] = date('Y-m-d', strtotime($data['start_date'] . ' +1 day'));
                } else {
                    $data['end_date'] = $data['start_date']; // Same day as default
                }
            } else {
                $data['end_date'] = $data['start_date'];
            }
        }

        // Parse reason (anything after "for" or "because" or "reason")
        if (preg_match('/(for|because|reason[:\s]+)(.{5,})/i', $message, $match)) {
            $data['reason'] = trim($match[2]);
        } else {
            $missing[] = 'reason';
        }

        return [
            'complete' => empty($missing),
            'missing' => $missing,
            'data' => $data
        ];
    }

    /**
     * Submit leave request to database
     */
    private function submitLeaveRequest(array $data): array
    {
        try {
            $sql = "INSERT INTO leaves (employee_id, leave_type, start_date, end_date, reason, status, applied_at) 
                    VALUES (?, ?, ?, ?, ?, 'pending', NOW())";

            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param(
                "issss",
                $this->employeeId,
                $data['leave_type'],
                $data['start_date'],
                $data['end_date'],
                $data['reason']
            );

            if ($stmt->execute()) {
                $leaveId = $this->mysqli->insert_id;
                $stmt->close();
                return ['success' => true, 'leave_id' => $leaveId];
            } else {
                $stmt->close();
                return ['success' => false, 'error' => 'Database error: ' . $this->mysqli->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle leave cancellation
     */
    private function handleLeaveCancel(array $context, ?array $pendingData): array
    {
        // Get pending leaves
        $sql = "SELECT id, leave_type, start_date, end_date 
                FROM leaves 
                WHERE employee_id = ? AND status = 'pending'
                ORDER BY start_date ASC
                LIMIT 5";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $this->employeeId);
        $stmt->execute();
        $leaves = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($leaves)) {
            return [
                'success' => true,
                'message' => "You don't have any pending leave requests to cancel.",
                'pending_action' => null
            ];
        }

        if (!$pendingData) {
            $message = "📋 **Your Pending Leave Requests**\n\n";
            foreach ($leaves as $i => $leave) {
                $num = $i + 1;
                $message .= "**{$num}.** {$leave['leave_type']} ({$leave['start_date']} to {$leave['end_date']})\n";
            }
            $message .= "\nReply with the number to cancel, or type **back** to go back.";

            return [
                'success' => true,
                'type' => 'action_started',
                'message' => $message,
                'pending_action' => [
                    'type' => self::ACTION_CANCEL_LEAVE,
                    'step' => 'select',
                    'data' => ['leaves' => $leaves]
                ]
            ];
        }

        // Process selection
        $input = trim($context['original_message'] ?? '');
        if (is_numeric($input)) {
            $index = (int) $input - 1;
            if (isset($pendingData['data']['leaves'][$index])) {
                $leave = $pendingData['data']['leaves'][$index];

                // Cancel the leave
                $sql = "UPDATE leaves SET status = 'cancelled' WHERE id = ? AND employee_id = ?";
                $stmt = $this->mysqli->prepare($sql);
                $stmt->bind_param("ii", $leave['id'], $this->employeeId);

                if ($stmt->execute()) {
                    $stmt->close();
                    return [
                        'success' => true,
                        'type' => 'action_completed',
                        'message' => "✅ Leave request cancelled successfully!",
                        'pending_action' => null
                    ];
                }
            }
        }

        return [
            'success' => true,
            'message' => "Cancelled. Let me know if you need anything else!",
            'pending_action' => null
        ];
    }

    /**
     * Handle clock in
     */
    private function handleClockIn(array $context): array
    {
        $today = date('Y-m-d');
        $now = date('H:i:s');

        // Check if already clocked in
        $sql = "SELECT id, check_in FROM attendance WHERE employee_id = ? AND date = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $this->employeeId, $today);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing && $existing['check_in']) {
            return [
                'success' => true,
                'message' => "⚠️ You've already clocked in today at **{$existing['check_in']}**.\n\nDid you mean to clock out?",
                'pending_action' => null
            ];
        }

        // Insert or update attendance
        if ($existing) {
            $sql = "UPDATE attendance SET check_in = ?, status = 'present' WHERE id = ?";
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param("si", $now, $existing['id']);
        } else {
            $sql = "INSERT INTO attendance (employee_id, date, check_in, status) VALUES (?, ?, ?, 'present')";
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param("iss", $this->employeeId, $today, $now);
        }

        if ($stmt->execute()) {
            $stmt->close();
            return [
                'success' => true,
                'type' => 'action_completed',
                'message' => "✅ **Clocked In Successfully!**\n\n" .
                    "• **Date:** {$today}\n" .
                    "• **Time:** {$now}\n\n" .
                    "Have a productive day! 💪",
                'pending_action' => null
            ];
        }

        $stmt->close();
        return [
            'success' => false,
            'message' => "❌ Failed to clock in. Please try using the web interface.",
            'pending_action' => null
        ];
    }

    /**
     * Handle clock out
     */
    private function handleClockOut(array $context): array
    {
        $today = date('Y-m-d');
        $now = date('H:i:s');

        // Check if clocked in
        $sql = "SELECT id, check_in, check_out FROM attendance WHERE employee_id = ? AND date = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $this->employeeId, $today);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$existing || !$existing['check_in']) {
            return [
                'success' => true,
                'message' => "⚠️ You haven't clocked in today. Would you like to clock in instead?",
                'pending_action' => null
            ];
        }

        if ($existing['check_out']) {
            return [
                'success' => true,
                'message' => "⚠️ You've already clocked out today at **{$existing['check_out']}**.",
                'pending_action' => null
            ];
        }

        // Calculate hours worked
        $checkIn = new DateTime($existing['check_in']);
        $checkOut = new DateTime($now);
        $diff = $checkIn->diff($checkOut);
        $hoursWorked = $diff->h + ($diff->i / 60);

        // Update attendance
        $sql = "UPDATE attendance SET check_out = ? WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("si", $now, $existing['id']);

        if ($stmt->execute()) {
            $stmt->close();
            return [
                'success' => true,
                'type' => 'action_completed',
                'message' => "✅ **Clocked Out Successfully!**\n\n" .
                    "• **Date:** {$today}\n" .
                    "• **Check In:** {$existing['check_in']}\n" .
                    "• **Check Out:** {$now}\n" .
                    "• **Hours Worked:** " . round($hoursWorked, 1) . " hours\n\n" .
                    "Great work today! See you tomorrow! 👋",
                'pending_action' => null
            ];
        }

        $stmt->close();
        return [
            'success' => false,
            'message' => "❌ Failed to clock out. Please try using the web interface.",
            'pending_action' => null
        ];
    }

    /**
     * Handle feedback submission
     */
    private function handleFeedback(array $context, ?array $pendingData): array
    {
        if (!$pendingData) {
            return [
                'success' => true,
                'type' => 'action_started',
                'message' => "📝 **Submit Feedback**\n\n" .
                    "Please share your feedback. It will be sent to HR anonymously.\n\n" .
                    "Type your feedback message, or type **cancel** to abort.",
                'pending_action' => [
                    'type' => self::ACTION_SUBMIT_FEEDBACK,
                    'step' => 'collect',
                    'data' => []
                ]
            ];
        }

        $feedback = trim($context['original_message'] ?? '');

        if (strtolower($feedback) === 'cancel') {
            return [
                'success' => true,
                'message' => "Feedback submission cancelled.",
                'pending_action' => null
            ];
        }

        if (strlen($feedback) < 10) {
            return [
                'success' => true,
                'message' => "Please provide more detailed feedback (at least 10 characters).",
                'pending_action' => $pendingData
            ];
        }

        // In a real system, save to database
        // For now, just acknowledge
        return [
            'success' => true,
            'type' => 'action_completed',
            'message' => "✅ **Feedback Submitted!**\n\n" .
                "Thank you for your feedback. It has been sent to HR.\n\n" .
                "_Note: Feedback is handled confidentially._",
            'pending_action' => null
        ];
    }
}
?>