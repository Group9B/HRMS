<?php
/**
 * KnowledgeBase.php
 * NexusBot Knowledge Base - FAQ and Help Responses
 * 
 * Contains pre-defined responses for common queries about HRMS features
 */

class KnowledgeBase
{
    /**
     * Get help menu with all available capabilities
     */
    public function getHelpMenu(): string
    {
        return "👋 **Hello! I'm NexusBot, your HRMS assistant.**\n\n" .
            "Here's what I can help you with:\n\n" .
            "📅 **Attendance**\n" .
            "• Check your attendance status\n" .
            "• View attendance for today/this week/this month\n\n" .
            "🏖️ **Leaves**\n" .
            "• Check your leave balance\n" .
            "• View pending leave requests\n" .
            "• See approved/rejected leaves\n\n" .
            "💰 **Payroll**\n" .
            "• View your payslips\n" .
            "• Check salary details\n\n" .
            "👤 **Profile**\n" .
            "• View your profile information\n" .
            "• Check department and designation\n\n" .
            "📋 **Tasks**\n" .
            "• View assigned tasks\n" .
            "• Check pending work\n\n" .
            "📆 **Holidays**\n" .
            "• View upcoming holidays\n" .
            "• Check holiday calendar\n\n" .
            "⏰ **Shift**\n" .
            "• Check your shift timings\n\n" .
            "💡 **Tips:** Try asking questions like:\n" .
            "• \"What's my attendance today?\"\n" .
            "• \"How many leaves do I have?\"\n" .
            "• \"Show my latest payslip\"\n" .
            "• \"When is the next holiday?\"";
    }

    /**
     * Get greeting responses
     */
    public function getGreeting(string $userName = ''): string
    {
        $greetings = [
            "Hello{name}! 👋 I'm NexusBot, your HRMS assistant. How can I help you today?",
            "Hi{name}! 😊 Welcome to StaffSync. What would you like to know?",
            "Hey{name}! 🤖 I'm here to help with your HR queries. What do you need?",
            "Greetings{name}! 💼 I'm NexusBot. Ask me anything about your attendance, leaves, payslips, and more!"
        ];

        $greeting = $greetings[array_rand($greetings)];
        $nameStr = $userName ? ", {$userName}" : "";
        return str_replace('{name}', $nameStr, $greeting);
    }

    /**
     * Get thank you responses
     */
    public function getThanksResponse(): string
    {
        $responses = [
            "You're welcome! 😊 Let me know if you need anything else.",
            "Happy to help! 👍 Feel free to ask more questions.",
            "Glad I could assist! 🌟 I'm here whenever you need me.",
            "No problem at all! 💼 Anything else you'd like to know?"
        ];

        return $responses[array_rand($responses)];
    }

    /**
     * Get goodbye responses
     */
    public function getGoodbyeResponse(): string
    {
        $responses = [
            "Goodbye! 👋 Have a great day!",
            "Take care! 🌟 See you next time!",
            "Bye for now! 💼 Don't hesitate to ask if you need help later!",
            "See you! 😊 Have a productive day ahead!"
        ];

        return $responses[array_rand($responses)];
    }

    /**
     * Get unknown intent response
     */
    public function getUnknownResponse(): string
    {
        $responses = [
            "I'm not sure I understand. 🤔 Could you rephrase that?\n\nTry asking about:\n• Attendance\n• Leave balance\n• Payslips\n• Tasks\n• Holidays",
            "Hmm, I didn't quite catch that. 😅 Here are some things I can help with:\n• Your attendance status\n• Leave information\n• Salary/payslip details\n• Assigned tasks",
            "I'm not certain what you're looking for. 🤷 Type 'help' to see all the things I can assist with!",
            "Sorry, I couldn't understand that request. Try asking about your attendance, leaves, payslips, or tasks. Or type 'help' for more options!"
        ];

        return $responses[array_rand($responses)];
    }

    /**
     * Get policy information
     */
    public function getPolicyInfo(string $policyType = 'general'): string
    {
        $policies = [
            'leave' => "📋 **Leave Policy Information**\n\n" .
                "Leave policies are set by your company administrator. To view your specific leave entitlements:\n\n" .
                "1. Ask me 'What is my leave balance?'\n" .
                "2. Check the Leave section in your dashboard\n" .
                "3. Contact HR for detailed policy documents\n\n" .
                "Common leave types include:\n" .
                "• Sick Leave\n" .
                "• Casual Leave\n" .
                "• Privilege Leave\n" .
                "• Maternity/Paternity Leave",

            'attendance' => "📋 **Attendance Policy Information**\n\n" .
                "Your attendance is tracked based on your assigned shift timings.\n\n" .
                "Key points:\n" .
                "• Check-in at the start of your shift\n" .
                "• Check-out at the end of your shift\n" .
                "• Late arrivals may be marked accordingly\n" .
                "• Contact HR for specific grace period policies",

            'general' => "📋 **Company Policies**\n\n" .
                "For detailed company policies, please:\n\n" .
                "1. Check the Policies section in your dashboard\n" .
                "2. Contact your HR department\n" .
                "3. Refer to your employee handbook\n\n" .
                "I can help you with:\n" .
                "• Leave policy basics\n" .
                "• Attendance guidelines\n" .
                "• Shift information"
        ];

        return $policies[$policyType] ?? $policies['general'];
    }

    /**
     * Get response for no data found
     */
    public function getNoDataResponse(string $dataType): string
    {
        $responses = [
            'attendance' => "📅 No attendance records found for the specified period. This could mean:\n• You haven't checked in yet\n• No data for the selected timeframe",
            'leave' => "🏖️ No leave records found. You haven't applied for any leaves in the selected period.",
            'leave_balance' => "🏖️ Leave balance information is not available. Please contact HR to set up your leave allocation.",
            'payslip' => "💰 No payslip records found. Payslips are generated monthly by your HR/Admin.",
            'task' => "📋 No tasks assigned to you currently. Enjoy the free time! 😊",
            'holiday' => "📆 No upcoming holidays found in the calendar.",
            'team' => "👥 No team members found. You may not be assigned to a team yet.",
            'performance' => "📊 No performance records found for the specified period."
        ];

        return $responses[$dataType] ?? "No data found for your request.";
    }

    /**
     * Get formatting helper for data display
     */
    public function formatDataResponse(string $title, array $data): string
    {
        $response = "**{$title}**\n\n";

        foreach ($data as $key => $value) {
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            $response .= "• **{$formattedKey}:** {$value}\n";
        }

        return $response;
    }

    /**
     * Get attendance status emoji
     */
    public function getAttendanceEmoji(string $status): string
    {
        $emojis = [
            'present' => '✅',
            'absent' => '❌',
            'leave' => '🏖️',
            'half-day' => '⏰',
            'holiday' => '🎉',
            'late' => '⚠️'
        ];

        return $emojis[strtolower($status)] ?? '📅';
    }

    /**
     * Get leave status emoji
     */
    public function getLeaveStatusEmoji(string $status): string
    {
        $emojis = [
            'pending' => '⏳',
            'approved' => '✅',
            'rejected' => '❌',
            'cancelled' => '🚫'
        ];

        return $emojis[strtolower($status)] ?? '📋';
    }

    /**
     * Get task status emoji
     */
    public function getTaskStatusEmoji(string $status): string
    {
        $emojis = [
            'pending' => '📝',
            'in_progress' => '🔄',
            'completed' => '✅',
            'cancelled' => '🚫'
        ];

        return $emojis[strtolower($status)] ?? '📋';
    }

    /**
     * Format date for display
     */
    public function formatDate(string $date, string $format = 'd M Y'): string
    {
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : $date;
    }

    /**
     * Format currency for display
     */
    public function formatCurrency(float $amount, string $currency = 'INR'): string
    {
        $symbols = [
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£'
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format($amount, 2);
    }
}
?>