<?php
/**
 * PromptGuard.php
 * Advanced Prompt Injection Protection for NexusBot
 * 
 * Protects against:
 * - Prompt injection attacks
 * - Jailbreak attempts
 * - Role manipulation
 * - Instruction override attempts
 * - Hidden malicious payloads
 */

class PromptGuard
{
    // Risk levels
    const RISK_NONE = 0;
    const RISK_LOW = 1;
    const RISK_MEDIUM = 2;
    const RISK_HIGH = 3;
    const RISK_CRITICAL = 4;

    // Injection pattern categories
    private $injectionPatterns = [
        // Role manipulation attempts
        'role_manipulation' => [
            '/ignore\s+(all\s+)?(previous|prior|above)\s+(instructions?|rules?|prompts?)/i',
            '/forget\s+(everything|all|your)\s+(instructions?|programming|rules?)/i',
            '/you\s+are\s+(now|no\s+longer)\s+(a|an)/i',
            '/act\s+as\s+(if|though)\s+you\s+(are|were)/i',
            '/pretend\s+(to\s+be|you\s+are)/i',
            '/from\s+now\s+on,?\s+(you|ignore)/i',
            '/new\s+instructions?:/i',
            '/system\s*:\s*/i',
            '/\[system\]/i',
            '/\[admin\]/i',
            '/\[override\]/i',
            '/sudo\s+/i',
            '/admin\s+mode/i',
            '/debug\s+mode/i',
            '/developer\s+mode/i',
            '/jailbreak/i',
            '/DAN\s+mode/i',
        ],

        // Instruction injection
        'instruction_injection' => [
            '/^(assistant|bot|ai|system)\s*:/im',
            '/\{\{.*\}\}/s',  // Template injection
            '/\[\[.*\]\]/s',  // Alternative template
            '/<\|.*\|>/s',    // Special delimiters
            '/###\s*(instruction|system|prompt)/i',
            '/\bprompt\s*[:=]/i',
            '/\binstruction\s*[:=]/i',
            '/execute\s+(this|the\s+following)/i',
            '/run\s+(this|the\s+following)\s+(code|command)/i',
        ],

        // Data exfiltration attempts
        'data_exfiltration' => [
            '/show\s+(me\s+)?(all|every)\s+(user|employee|password|credential)/i',
            '/list\s+all\s+(users?|employees?|passwords?)/i',
            '/dump\s+(database|table|users?)/i',
            '/export\s+all/i',
            '/extract\s+(data|information|credentials?)/i',
            '/reveal\s+(secret|hidden|password)/i',
            '/bypass\s+(security|authentication|login)/i',
            '/disable\s+(security|protection|filter)/i',
        ],

        // Code execution attempts
        'code_execution' => [
            '/<\?php/i',
            '/\$_GET|\$_POST|\$_REQUEST|\$_SESSION/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec/i',
            '/passthru/i',
            '/`[^`]+`/',  // Backtick execution
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',  // Event handlers
        ],

        // SQL injection (enhanced)
        'sql_injection' => [
            '/;\s*(drop|delete|truncate|alter|update|insert)\s+/i',
            '/union\s+(all\s+)?select/i',
            '/or\s+[\'\"]?1[\'\"]?\s*=\s*[\'\"]?1/i',
            '/and\s+[\'\"]?1[\'\"]?\s*=\s*[\'\"]?0/i',
            '/--\s*$/m',
            '/\/\*.*\*\//s',
            '/;\s*--/i',
            '/\'\s*or\s+\'/i',
            '/\"\s*or\s+\"/i',
            '/benchmark\s*\(/i',
            '/sleep\s*\(/i',
            '/waitfor\s+delay/i',
        ],

        // Obfuscation attempts
        'obfuscation' => [
            '/[\x00-\x08\x0B\x0C\x0E-\x1F]/',  // Control characters
            '/[\x{200B}-\x{200D}\x{FEFF}]/u',   // Zero-width characters
            '/&#x?[0-9a-f]+;/i',               // HTML entities
            '/%[0-9a-f]{2}/i',                  // URL encoding in message
            '/\\\\u[0-9a-f]{4}/i',              // Unicode escapes
            '/base64[_\s]*decode/i',
            '/rot13/i',
            '/hex2bin/i',
        ],

        // Sensitive data probing
        'sensitive_probing' => [
            '/what\s+is\s+(your|the)\s+(api|secret)\s*key/i',
            '/show\s+(me\s+)?(your|the)\s+(source|code|configuration)/i',
            '/what\s+(are\s+)?(your|the)\s+system\s+prompt/i',
            '/reveal\s+(your|the)\s+instructions/i',
            '/print\s+(your|the)\s+(prompt|instructions)/i',
            '/tell\s+me\s+(your|the)\s+rules/i',
            '/what\s+were\s+you\s+(told|instructed)/i',
            '/repeat\s+(your|the)\s+instructions/i',
        ],
    ];

    // Blocklist of exact phrases
    private $blockedPhrases = [
        'ignore previous instructions',
        'disregard all prior',
        'forget your programming',
        'you are now',
        'new persona',
        'jailbreak mode',
        'developer override',
        'admin access granted',
        'bypass security',
        'disable protection',
        'reveal system prompt',
        'show me the code',
        'dump database',
        'export all users',
    ];

    // Maximum message length
    private $maxLength = 1000;

    // Suspicious character thresholds
    private $maxSpecialChars = 0.3; // 30% max special characters
    private $maxUppercase = 0.7;    // 70% max uppercase

    /**
     * Analyze message for potential prompt injection
     * 
     * @param string $message User input
     * @return array Analysis result with risk level and details
     */
    public function analyze(string $message): array
    {
        $risks = [];
        $totalRisk = self::RISK_NONE;

        // Check message length
        if (strlen($message) > $this->maxLength) {
            $risks[] = [
                'type' => 'length_exceeded',
                'level' => self::RISK_MEDIUM,
                'detail' => 'Message exceeds maximum length'
            ];
            $totalRisk = max($totalRisk, self::RISK_MEDIUM);
        }

        // Check for blocked phrases
        $blockedFound = $this->checkBlockedPhrases($message);
        if (!empty($blockedFound)) {
            $risks[] = [
                'type' => 'blocked_phrase',
                'level' => self::RISK_CRITICAL,
                'detail' => 'Blocked phrase detected: ' . implode(', ', $blockedFound)
            ];
            $totalRisk = self::RISK_CRITICAL;
        }

        // Check injection patterns
        foreach ($this->injectionPatterns as $category => $patterns) {
            $matches = $this->checkPatterns($message, $patterns);
            if (!empty($matches)) {
                $level = $this->getCategoryRiskLevel($category);
                $risks[] = [
                    'type' => $category,
                    'level' => $level,
                    'detail' => 'Pattern matches found',
                    'matches' => $matches
                ];
                $totalRisk = max($totalRisk, $level);
            }
        }

        // Check character distribution anomalies
        $charAnalysis = $this->analyzeCharacterDistribution($message);
        if ($charAnalysis['suspicious']) {
            $risks[] = [
                'type' => 'character_anomaly',
                'level' => self::RISK_LOW,
                'detail' => $charAnalysis['reason']
            ];
            $totalRisk = max($totalRisk, self::RISK_LOW);
        }

        // Check for nested structures (potential payload hiding)
        if ($this->hasNestedStructures($message)) {
            $risks[] = [
                'type' => 'nested_structure',
                'level' => self::RISK_MEDIUM,
                'detail' => 'Suspicious nested structures detected'
            ];
            $totalRisk = max($totalRisk, self::RISK_MEDIUM);
        }

        // Check for repetitive patterns (common in attacks)
        if ($this->hasRepetitivePatterns($message)) {
            $risks[] = [
                'type' => 'repetitive_pattern',
                'level' => self::RISK_LOW,
                'detail' => 'Unusual repetitive patterns detected'
            ];
            $totalRisk = max($totalRisk, self::RISK_LOW);
        }

        return [
            'is_safe' => $totalRisk < self::RISK_HIGH,
            'risk_level' => $totalRisk,
            'risk_name' => $this->getRiskName($totalRisk),
            'risks' => $risks,
            'should_block' => $totalRisk >= self::RISK_HIGH,
            'sanitized_message' => $this->sanitize($message)
        ];
    }

    /**
     * Check for blocked phrases
     */
    private function checkBlockedPhrases(string $message): array
    {
        $found = [];
        $lowerMessage = strtolower($message);

        foreach ($this->blockedPhrases as $phrase) {
            if (strpos($lowerMessage, $phrase) !== false) {
                $found[] = $phrase;
            }
        }

        return $found;
    }

    /**
     * Check patterns and return matches
     */
    private function checkPatterns(string $message, array $patterns): array
    {
        $matches = [];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $match)) {
                $matches[] = $match[0];
            }
        }

        return $matches;
    }

    /**
     * Get risk level for pattern category
     */
    private function getCategoryRiskLevel(string $category): int
    {
        $levels = [
            'role_manipulation' => self::RISK_CRITICAL,
            'instruction_injection' => self::RISK_CRITICAL,
            'data_exfiltration' => self::RISK_HIGH,
            'code_execution' => self::RISK_CRITICAL,
            'sql_injection' => self::RISK_CRITICAL,
            'obfuscation' => self::RISK_MEDIUM,
            'sensitive_probing' => self::RISK_HIGH,
        ];

        return $levels[$category] ?? self::RISK_MEDIUM;
    }

    /**
     * Analyze character distribution for anomalies
     */
    private function analyzeCharacterDistribution(string $message): array
    {
        if (empty($message)) {
            return ['suspicious' => false];
        }

        $length = mb_strlen($message);

        // Count special characters
        $specialCount = preg_match_all('/[^a-zA-Z0-9\s]/', $message);
        $specialRatio = $specialCount / $length;

        if ($specialRatio > $this->maxSpecialChars) {
            return [
                'suspicious' => true,
                'reason' => 'High ratio of special characters'
            ];
        }

        // Count uppercase
        $upperCount = preg_match_all('/[A-Z]/', $message);
        $letterCount = preg_match_all('/[a-zA-Z]/', $message);

        if ($letterCount > 10) {
            $upperRatio = $upperCount / $letterCount;
            if ($upperRatio > $this->maxUppercase) {
                return [
                    'suspicious' => true,
                    'reason' => 'Unusual uppercase ratio'
                ];
            }
        }

        return ['suspicious' => false];
    }

    /**
     * Check for nested structures that might hide payloads
     */
    private function hasNestedStructures(string $message): bool
    {
        // Check for deeply nested brackets/braces
        $patterns = [
            '/\{[^{}]*\{[^{}]*\{/s',        // Triple nested braces
            '/\[[^\[\]]*\[[^\[\]]*\[/s',    // Triple nested brackets
            '/\([^()]*\([^()]*\(/s',         // Triple nested parentheses
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for suspicious repetitive patterns
     */
    private function hasRepetitivePatterns(string $message): bool
    {
        // Check for repeated words (more than 5 times)
        $words = str_word_count(strtolower($message), 1);
        $wordCounts = array_count_values($words);

        foreach ($wordCounts as $word => $count) {
            if (strlen($word) > 2 && $count > 5) {
                return true;
            }
        }

        // Check for repeated character sequences
        if (preg_match('/(.{3,})\1{3,}/', $message)) {
            return true;
        }

        return false;
    }

    /**
     * Get human-readable risk name
     */
    private function getRiskName(int $level): string
    {
        $names = [
            self::RISK_NONE => 'None',
            self::RISK_LOW => 'Low',
            self::RISK_MEDIUM => 'Medium',
            self::RISK_HIGH => 'High',
            self::RISK_CRITICAL => 'Critical'
        ];

        return $names[$level] ?? 'Unknown';
    }

    /**
     * Sanitize message by removing/escaping dangerous content
     */
    public function sanitize(string $message): string
    {
        // Remove control characters
        $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $message);

        // Remove zero-width characters
        $message = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $message);

        // Normalize whitespace
        $message = preg_replace('/\s+/', ' ', $message);

        // Trim
        $message = trim($message);

        // Limit length
        if (strlen($message) > $this->maxLength) {
            $message = substr($message, 0, $this->maxLength);
        }

        // HTML encode
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        return $message;
    }

    /**
     * Get blocking response for high-risk messages
     */
    public function getBlockingResponse(array $analysis): string
    {
        $responses = [
            "I noticed something unusual in your message. Please rephrase your question about HR-related topics.",
            "For security reasons, I can only respond to standard HR queries. Try asking about attendance, leaves, or payslips.",
            "Your message contains content I cannot process. Please ask a direct question about your employee information.",
            "I'm designed to help with HR queries only. Please ask about your attendance, leave balance, payslips, or tasks."
        ];

        return $responses[array_rand($responses)];
    }

    /**
     * Log suspicious activity (call this for monitoring)
     */
    public function logSuspiciousActivity(array $analysis, string $userId, string $originalMessage): void
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'risk_level' => $analysis['risk_name'],
            'risk_score' => $analysis['risk_level'],
            'risks' => $analysis['risks'],
            'message_length' => strlen($originalMessage),
            'message_preview' => substr($analysis['sanitized_message'], 0, 100)
        ];

        // Log to error log (in production, use proper logging system)
        error_log("NexusBot Security Alert: " . json_encode($logData));
    }
}
?>