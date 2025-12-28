<?php
/**
 * IntentRecognizer.php
 * NexusBot Intent Recognition Engine
 * 
 * Recognizes user intent from natural language queries using:
 * - Keyword matching
 * - Fuzzy string matching
 * - Pattern recognition
 */

class IntentRecognizer
{
    // Intent constants
    const INTENT_ATTENDANCE = 'attendance';
    const INTENT_LEAVE = 'leave';
    const INTENT_LEAVE_BALANCE = 'leave_balance';
    const INTENT_PAYSLIP = 'payslip';
    const INTENT_PROFILE = 'profile';
    const INTENT_TASK = 'task';
    const INTENT_TEAM = 'team';
    const INTENT_HOLIDAY = 'holiday';
    const INTENT_SHIFT = 'shift';
    const INTENT_PERFORMANCE = 'performance';
    const INTENT_DEPARTMENT = 'department';
    const INTENT_HELP = 'help';
    const INTENT_GREETING = 'greeting';
    const INTENT_THANKS = 'thanks';
    const INTENT_GOODBYE = 'goodbye';
    const INTENT_POLICY = 'policy';
    const INTENT_COMPANY = 'company';
    const INTENT_UNKNOWN = 'unknown';

    // Sub-intent for queries about others
    const SUBINTENT_SELF = 'self';
    const SUBINTENT_TEAM = 'team';
    const SUBINTENT_OTHER = 'other';
    const SUBINTENT_ALL = 'all';

    // Keyword patterns for each intent
    private $intentPatterns = [
        self::INTENT_GREETING => [
            'keywords' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'greetings'],
            'weight' => 1.0
        ],
        self::INTENT_THANKS => [
            'keywords' => ['thank', 'thanks', 'thank you', 'thx', 'appreciated', 'helpful'],
            'weight' => 1.0
        ],
        self::INTENT_GOODBYE => [
            'keywords' => ['bye', 'goodbye', 'see you', 'later', 'take care', 'cya'],
            'weight' => 1.0
        ],
        self::INTENT_HELP => [
            'keywords' => ['help', 'what can you do', 'features', 'capabilities', 'how to', 'guide', 'assist', 'support', 'menu'],
            'weight' => 1.0
        ],
        self::INTENT_ATTENDANCE => [
            'keywords' => ['attendance', 'check in', 'check out', 'checkin', 'checkout', 'present', 'absent', 'working hours', 'work hours', 'time', 'punch', 'clock in', 'clock out'],
            'weight' => 1.2
        ],
        self::INTENT_LEAVE => [
            'keywords' => ['leave', 'time off', 'vacation', 'sick', 'casual', 'apply leave', 'leave request', 'leave status', 'pending leave', 'approved leave'],
            'weight' => 1.2
        ],
        self::INTENT_LEAVE_BALANCE => [
            'keywords' => ['leave balance', 'remaining leave', 'leaves left', 'available leave', 'leave quota', 'how many leaves'],
            'weight' => 1.5
        ],
        self::INTENT_PAYSLIP => [
            'keywords' => ['payslip', 'salary', 'pay', 'payment', 'wage', 'earnings', 'deduction', 'net salary', 'gross salary', 'compensation', 'income'],
            'weight' => 1.2
        ],
        self::INTENT_PROFILE => [
            'keywords' => ['profile', 'my details', 'my info', 'personal', 'about me', 'my data', 'employee code', 'joining date', 'my name', 'my email', 'my contact'],
            'weight' => 1.0
        ],
        self::INTENT_TASK => [
            'keywords' => ['task', 'assignment', 'assigned', 'to do', 'todo', 'pending task', 'work', 'project', 'deadline', 'due date'],
            'weight' => 1.2
        ],
        self::INTENT_TEAM => [
            'keywords' => ['team', 'colleague', 'coworker', 'team member', 'my team', 'group'],
            'weight' => 1.0
        ],
        self::INTENT_HOLIDAY => [
            'keywords' => ['holiday', 'holidays', 'off day', 'public holiday', 'festival', 'next holiday', 'upcoming holiday'],
            'weight' => 1.2
        ],
        self::INTENT_SHIFT => [
            'keywords' => ['shift', 'timing', 'schedule', 'work schedule', 'shift time', 'start time', 'end time', 'office hours'],
            'weight' => 1.0
        ],
        self::INTENT_PERFORMANCE => [
            'keywords' => ['performance', 'appraisal', 'review', 'rating', 'score', 'evaluation', 'kra', 'kpi', 'feedback'],
            'weight' => 1.0
        ],
        self::INTENT_DEPARTMENT => [
            'keywords' => ['department', 'dept', 'designation', 'position', 'role', 'job title'],
            'weight' => 1.0
        ],
        self::INTENT_POLICY => [
            'keywords' => ['policy', 'policies', 'rule', 'rules', 'guideline', 'procedure', 'regulation'],
            'weight' => 1.0
        ],
        self::INTENT_COMPANY => [
            'keywords' => ['company', 'organization', 'org', 'about company', 'company info'],
            'weight' => 1.0
        ]
    ];

    // Patterns that indicate query is about someone else
    private $otherTargetPatterns = [
        '/show\s+(all|everyone|other)/i',
        '/list\s+(all|employees?)/i',
        '/employee\s+\d+/i',
        '/for\s+employee/i',
        '/(his|her|their)\s+/i',
        '/other\s+(employee|user|person)/i',
        '/someone\s+else/i',
        '/all\s+employees?/i'
    ];

    // Patterns that indicate query is about team
    private $teamTargetPatterns = [
        '/my\s+team/i',
        '/team\s+(attendance|leave|performance)/i',
        '/subordinate/i',
        '/direct\s+report/i',
        '/reporting\s+to\s+me/i',
        '/under\s+me/i'
    ];

    // Time context patterns
    private $timePatterns = [
        'today' => '/today|this day/i',
        'yesterday' => '/yesterday/i',
        'this_week' => '/this week|current week/i',
        'last_week' => '/last week|previous week/i',
        'this_month' => '/this month|current month/i',
        'last_month' => '/last month|previous month/i',
        'this_year' => '/this year|current year/i',
        'next_month' => '/next month/i'
    ];

    /**
     * Recognize intent from user message
     * 
     * @param string $message User's input message
     * @return array Intent details including intent, subintent, confidence, and context
     */
    public function recognize(string $message): array
    {
        $message = strtolower(trim($message));

        // Check for empty message
        if (empty($message)) {
            return $this->buildResult(self::INTENT_UNKNOWN, self::SUBINTENT_SELF, 0, []);
        }

        $scores = [];

        // Calculate score for each intent
        foreach ($this->intentPatterns as $intent => $config) {
            $score = $this->calculateIntentScore($message, $config['keywords'], $config['weight']);
            if ($score > 0) {
                $scores[$intent] = $score;
            }
        }

        // Sort by score descending
        arsort($scores);

        // Get best match
        if (!empty($scores)) {
            $bestIntent = array_key_first($scores);
            $confidence = min($scores[$bestIntent] / 2, 1.0); // Normalize to 0-1

            // Determine subintent (self, team, other, all)
            $subIntent = $this->detectSubIntent($message);

            // Extract time context
            $timeContext = $this->extractTimeContext($message);

            // Extract any mentioned IDs or names
            $entities = $this->extractEntities($message);

            return $this->buildResult($bestIntent, $subIntent, $confidence, [
                'time_context' => $timeContext,
                'entities' => $entities,
                'original_message' => $message
            ]);
        }

        return $this->buildResult(self::INTENT_UNKNOWN, self::SUBINTENT_SELF, 0, [
            'original_message' => $message
        ]);
    }

    /**
     * Calculate intent score based on keyword matching
     */
    private function calculateIntentScore(string $message, array $keywords, float $weight): float
    {
        $score = 0;

        foreach ($keywords as $keyword) {
            // Exact match
            if (strpos($message, $keyword) !== false) {
                $score += 2 * $weight;
            }
            // Fuzzy match for typos
            else {
                $words = explode(' ', $message);
                foreach ($words as $word) {
                    $similarity = 0;
                    similar_text(strtolower($word), strtolower($keyword), $similarity);
                    if ($similarity > 80) {
                        $score += ($similarity / 100) * $weight;
                    }
                }
            }
        }

        return $score;
    }

    /**
     * Detect if query is about self, team, other, or all
     */
    private function detectSubIntent(string $message): string
    {
        // Check for team patterns
        foreach ($this->teamTargetPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return self::SUBINTENT_TEAM;
            }
        }

        // Check for other/all patterns
        foreach ($this->otherTargetPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                // Distinguish between "other" and "all"
                if (preg_match('/all|everyone|list/i', $message)) {
                    return self::SUBINTENT_ALL;
                }
                return self::SUBINTENT_OTHER;
            }
        }

        // Default to self
        return self::SUBINTENT_SELF;
    }

    /**
     * Extract time context from message
     */
    private function extractTimeContext(string $message): ?string
    {
        foreach ($this->timePatterns as $context => $pattern) {
            if (preg_match($pattern, $message)) {
                return $context;
            }
        }
        return null;
    }

    /**
     * Extract entities (IDs, names, dates) from message
     */
    private function extractEntities(string $message): array
    {
        $entities = [];

        // Look for employee IDs
        if (preg_match('/employee\s*#?\s*(\d+)/i', $message, $matches)) {
            $entities['employee_id'] = (int) $matches[1];
        }

        // Look for specific months
        $months = [
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december'
        ];
        foreach ($months as $index => $month) {
            if (stripos($message, $month) !== false) {
                $entities['month'] = $index + 1;
                $entities['month_name'] = ucfirst($month);
            }
        }

        // Look for years
        if (preg_match('/\b(20\d{2})\b/', $message, $matches)) {
            $entities['year'] = (int) $matches[1];
        }

        return $entities;
    }

    /**
     * Build standardized result array
     */
    private function buildResult(string $intent, string $subIntent, float $confidence, array $context): array
    {
        return [
            'intent' => $intent,
            'sub_intent' => $subIntent,
            'confidence' => round($confidence, 2),
            'context' => $context
        ];
    }

    /**
     * Check if intent is a conversational one (greeting, thanks, etc.)
     */
    public function isConversationalIntent(string $intent): bool
    {
        return in_array($intent, [
            self::INTENT_GREETING,
            self::INTENT_THANKS,
            self::INTENT_GOODBYE,
            self::INTENT_HELP
        ]);
    }

    /**
     * Check if intent requires data access
     */
    public function requiresDataAccess(string $intent): bool
    {
        return in_array($intent, [
            self::INTENT_ATTENDANCE,
            self::INTENT_LEAVE,
            self::INTENT_LEAVE_BALANCE,
            self::INTENT_PAYSLIP,
            self::INTENT_PROFILE,
            self::INTENT_TASK,
            self::INTENT_TEAM,
            self::INTENT_PERFORMANCE,
            self::INTENT_SHIFT
        ]);
    }
}
?>