<?php
/**
 * NexusBot.php (Advanced Version)
 * Main NexusBot AI Engine - Enhanced with:
 * - Prompt injection protection
 * - Conversation context
 * - Action handling
 * - Advanced NLP
 * - Security logging
 */

require_once __DIR__ . '/IntentRecognizer.php';
require_once __DIR__ . '/SecurityFilter.php';
require_once __DIR__ . '/QueryHandler.php';
require_once __DIR__ . '/KnowledgeBase.php';
require_once __DIR__ . '/PromptGuard.php';
require_once __DIR__ . '/ConversationContext.php';
require_once __DIR__ . '/ActionHandler.php';

require_once __DIR__ . '/GroqHandler.php';
require_once __DIR__ . '/WidgetRenderer.php';
require_once __DIR__ . '/DynamicQueryBuilder.php';

class NexusBot
{
    private $mysqli;
    private $recognizer;
    private $groq;
    private $security;
    private $queryHandler;
    private $knowledge;
    private $promptGuard;
    private $context;
    private $actionHandler;
    private $widgetRenderer;
    private $dynamicQueryBuilder;
    private $userContext;

    // Bot metadata
    const BOT_NAME = 'NexusBot';
    const BOT_VERSION = '2.0.0';

    // Response types
    const TYPE_TEXT = 'text';
    const TYPE_DATA = 'data';
    const TYPE_HELP = 'help';
    const TYPE_ERROR = 'error';
    const TYPE_ACTION = 'action';
    const TYPE_SECURITY = 'security';

    /**
     * Initialize NexusBot with database connection and user context
     */
    public function __construct($mysqli, array $userContext)
    {
        $this->mysqli = $mysqli;
        $this->userContext = $userContext;

        // Initialize all components
        $this->recognizer = new IntentRecognizer();
        $this->groq = new GroqHandler(); // Reads from .env automatically
        $this->security = new SecurityFilter($mysqli, $userContext);
        $this->queryHandler = new QueryHandler($mysqli, $this->security);
        $this->knowledge = new KnowledgeBase();
        $this->promptGuard = new PromptGuard();
        $this->context = new ConversationContext($mysqli, $userContext['user_id'] ?? 0);
        $this->actionHandler = new ActionHandler($mysqli, $this->security, $userContext);
        $this->widgetRenderer = new WidgetRenderer($mysqli, $userContext['employee_id'] ?? 0, $userContext['user_id'] ?? 0);
        $this->dynamicQueryBuilder = new DynamicQueryBuilder($mysqli, $userContext);
    }

    /**
     * Process user message and return response
     * 
     * @param string $message User's input message
     * @return array Response with status and message
     */
    public function process(string $message): array
    {
        // Validate authentication
        if (!$this->security->isAuthenticated()) {
            return $this->errorResponse('Please log in to use NexusBot.');
        }

        // Reset context if idle too long
        if ($this->context->shouldReset()) {
            $this->context->clear();
        }

        // ============ SECURITY LAYER 1: Prompt Injection Protection ============
        $securityAnalysis = $this->promptGuard->analyze($message);

        if ($securityAnalysis['should_block']) {
            // Log the suspicious activity
            $this->promptGuard->logSuspiciousActivity(
                $securityAnalysis,
                (string) ($this->userContext['user_id'] ?? 'unknown'),
                $message
            );

            // Return safe blocking response
            return $this->successResponse(
                $this->promptGuard->getBlockingResponse($securityAnalysis),
                self::TYPE_SECURITY
            );
        }

        // Use sanitized message
        $sanitizedMessage = $securityAnalysis['sanitized_message'];

        // ============ SECURITY LAYER 2: Content Security ============
        if ($this->security->detectMaliciousIntent($sanitizedMessage)) {
            return $this->successResponse(
                $this->security->getCredentialProtectionMessage(),
                self::TYPE_SECURITY
            );
        }

        // Check for empty message
        if (empty(trim($sanitizedMessage))) {
            file_put_contents('c:/xampp/htdocs/HRMS/debug_trace.log', "NexusBot: Empty Message\n", FILE_APPEND);
            return $this->successResponse(
                $this->knowledge->getHelpMenu(),
                self::TYPE_HELP
            );
        }

        // ============ CONTEXT AWARENESS ============
        // Check for pending action
        $pendingAction = $this->context->get(ConversationContext::CTX_PENDING_ACTION);

        // Handle cancel/abort for any pending action
        if ($pendingAction && $this->isCancelRequest($sanitizedMessage)) {
            $this->context->remove(ConversationContext::CTX_PENDING_ACTION);
            $this->context->addMessage('user', $sanitizedMessage);
            $response = "Okay, cancelled! What else can I help you with?";
            $this->context->addMessage('bot', $response);
            return $this->successResponse($response, self::TYPE_TEXT);
        }

        // Continue pending action
        if ($pendingAction) {
            return $this->handlePendingAction($sanitizedMessage, $pendingAction);
        }

        // ============ FOLLOW-UP DETECTION ============
        if ($this->context->isFollowUp($sanitizedMessage)) {
            $sanitizedMessage = $this->context->resolvePronouns($sanitizedMessage);
            $lastIntent = $this->context->get(ConversationContext::CTX_LAST_INTENT);
            if ($lastIntent) {
                // Enhance intent recognition with context
                $sanitizedMessage = $this->enhanceWithContext($sanitizedMessage, $lastIntent);
            }
        }

        // ============ ACTION DETECTION ============
        $actionType = $this->actionHandler->isActionRequest($sanitizedMessage);
        if ($actionType) {
            return $this->handleAction($actionType, $sanitizedMessage);
        }

        // ============ INTENT RECOGNITION ============
        // Try Groq/LLaMA first, with graceful fallback to native bot
        error_log('NexusBot: Attempting Groq AI analysis...');

        // Get recent history for context (last 5 messages)
        $history = $this->context->getHistory(5);
        $intent = $this->groq->analyze($sanitizedMessage, $history);
        $isAI = true;

        // Fallback to native if Groq fails (returns null) or returns unknown
        if ($intent === null || !isset($intent['intent']) || $intent['intent'] === 'unknown') {
            error_log('NexusBot: Groq failed or returned unknown, falling back to native bot');
            $intent = $this->recognizer->recognize($sanitizedMessage);
            $isAI = false;
        } else {
            error_log('NexusBot: Groq successfully analyzed intent: ' . $intent['intent']);
        }

        // Gather safe user context for AI
        $safeContext = [
            'name' => $this->userContext['username'] ?? 'Employee',
            'role' => $this->userContext['role'] ?? 'User',
            'city' => 'Unknown' // Add city from DB if available later
        ];

        // Add to conversation history
        $this->context->addMessage('user', $sanitizedMessage, ['intent' => $intent['intent']]);

        // Update context
        $this->context->set(ConversationContext::CTX_LAST_INTENT, $intent['intent']);
        $this->context->set(ConversationContext::CTX_CURRENT_TOPIC, $intent['intent']);

        // Route to appropriate handler
        $result = $this->handleIntent($intent);

        // Generate Response
        // If AI was used and we have data/success, let AI phrase the response
        if ($isAI && $result['success']) {
            // Only generate detailed text for data responses, not simple conversational ones that return direct strings
            // However, handleIntent largely returns standard arrays.

            // Check if it's a data response or direct message
            // Get history again to ensure latest context
            $history = $this->context->getHistory(5);

            if ($intent['intent'] === 'general_query' || $intent['intent'] === 'greeting' || $intent['intent'] === 'thanks' || $intent['intent'] === 'goodbye' || $intent['intent'] === 'help') {
                $finalMessage = $this->groq->generateResponse($sanitizedMessage, ['context' => 'conversation'], $safeContext, $history);
                $response = $this->successResponse($finalMessage, self::TYPE_TEXT);
            } elseif (isset($result['data'])) {
                $finalMessage = $this->groq->generateResponse($sanitizedMessage, $result['data'], $safeContext, $history);


                // Attach Dynamic Widgets
                $widget = null;
                switch ($intent['intent']) {
                    case 'attendance':
                        $widget = $this->widgetRenderer->attendanceWidget();
                        break;
                    case 'leave_balance':
                    case 'leave':
                        $widget = $this->widgetRenderer->leaveWidget();
                        break;
                    case 'task':
                        $widget = $this->widgetRenderer->tasksWidget();
                        break;
                    case 'team':
                        $widget = $this->widgetRenderer->teamWidget();
                        break;
                }

                $response = $this->successResponse($finalMessage, self::TYPE_DATA, [
                    'data' => $result['data'],
                    'widget' => $widget
                ]);
            } else {
                // For info responses without complex data structure
                $response = $this->successResponse($result['message'], $result['type'] ?? self::TYPE_TEXT);
            }
        } else {
            // Use standard Response
            if ($result['success']) {
                $response = $this->successResponse(
                    $result['message'],
                    isset($result['data']) && !empty($result['data']) ? self::TYPE_DATA : ($result['type'] ?? 'info'),
                    $result['data'] ?? null
                );
            } else {
                $response = $this->successResponse(
                    $result['message'],
                    self::TYPE_ERROR
                );
            }
        }

        // Add bot response to history
        $this->context->addMessage('bot', $response['message'], ['type' => $response['type']]);

        // Add source indicator
        $response['source'] = $isAI ? 'groq' : 'native';

        return $response;
    }

    /**
     * Check for internet connectivity
     */
    private function checkInternetConnection(): bool
    {
        $ch = curl_init("https://www.google.com");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // Head request only
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // 3 seconds timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Handle potential local SSL issues

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode >= 200 && $httpCode < 400);
    }

    /**
     * Handle action requests
     */
    private function handleAction(string $actionType, string $message): array
    {
        $context = [
            'original_message' => $message,
            'entities' => $this->recognizer->recognize($message)['context']['entities'] ?? []
        ];

        $result = $this->actionHandler->handleAction($actionType, $context);

        // Store pending action in context if needed
        if (isset($result['pending_action']) && $result['pending_action']) {
            $this->context->set(ConversationContext::CTX_PENDING_ACTION, $result['pending_action']);
        } else {
            $this->context->remove(ConversationContext::CTX_PENDING_ACTION);
        }

        $this->context->addMessage('user', $message, ['action' => $actionType]);
        $this->context->addMessage('bot', $result['message'], ['type' => $result['type'] ?? 'action']);

        $response = $this->successResponse($result['message'], self::TYPE_ACTION);

        if (isset($result['client_action'])) {
            $response['client_action'] = $result['client_action'];
        }

        return $response;
    }

    /**
     * Handle pending action continuation
     */
    private function handlePendingAction(string $message, array $pendingAction): array
    {
        $context = [
            'original_message' => $message,
            'entities' => $this->recognizer->recognize($message)['context']['entities'] ?? []
        ];

        $result = $this->actionHandler->handleAction(
            $pendingAction['type'],
            $context,
            $pendingAction
        );

        // Update or clear pending action
        if (isset($result['pending_action']) && $result['pending_action']) {
            $this->context->set(ConversationContext::CTX_PENDING_ACTION, $result['pending_action']);
        } else {
            $this->context->remove(ConversationContext::CTX_PENDING_ACTION);
        }

        $this->context->addMessage('user', $message);
        $this->context->addMessage('bot', $result['message']);

        return $this->successResponse($result['message'], self::TYPE_ACTION);
    }

    /**
     * Check if user wants to cancel current action
     */
    private function isCancelRequest(string $message): bool
    {
        $cancelPatterns = [
            '/^(cancel|abort|stop|nevermind|never\s*mind|forget\s*it|back|quit|exit)$/i',
            '/^(no\s*thanks?|nope|nah)$/i',
        ];

        foreach ($cancelPatterns as $pattern) {
            if (preg_match($pattern, trim($message))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enhance message with context for better intent recognition
     */
    private function enhanceWithContext(string $message, string $lastIntent): string
    {
        // Add context hints based on last intent
        $contextHints = [
            IntentRecognizer::INTENT_ATTENDANCE => 'attendance ',
            IntentRecognizer::INTENT_LEAVE => 'leave ',
            IntentRecognizer::INTENT_PAYSLIP => 'payslip salary ',
            IntentRecognizer::INTENT_TASK => 'task ',
        ];

        if (isset($contextHints[$lastIntent])) {
            return $contextHints[$lastIntent] . $message;
        }

        return $message;
    }

    /**
     * Route intent to appropriate handler
     */
    private function handleIntent(array $intent): array
    {
        $intentType = $intent['intent'];
        $subIntent = $intent['sub_intent'];
        $context = $intent['context'];

        // Handle conversational intents
        if ($this->recognizer->isConversationalIntent($intentType)) {
            return $this->handleConversationalIntent($intentType);
        }

        // Handle general chat (Groq) - Return basics to let main process loop handle generation
        if (in_array($intentType, ['general_query', 'greeting', 'thanks', 'goodbye', 'help'])) {
            return [
                'success' => true,
                'data' => null
            ];
        }

        // Handle data-related intents
        switch ($intentType) {
            case 'dynamic_query':
                $result = $this->dynamicQueryBuilder->execute($intent['data']);
                break;

            case IntentRecognizer::INTENT_ATTENDANCE:
                $result = $this->queryHandler->getAttendance($context, $subIntent);
                break;

            case IntentRecognizer::INTENT_LEAVE_BALANCE:
                $result = $this->queryHandler->getLeaveBalance($context, $subIntent);
                break;

            case IntentRecognizer::INTENT_LEAVE:
                $result = $this->queryHandler->getLeaves($context, $subIntent);
                break;

            case IntentRecognizer::INTENT_PAYSLIP:
                $result = $this->queryHandler->getPayslips($context, $subIntent);
                break;

            case IntentRecognizer::INTENT_PROFILE:
                $result = $this->queryHandler->getProfile($context, $subIntent);
                break;

            case IntentRecognizer::INTENT_TASK:
                $result = $this->queryHandler->getTasks($context, $subIntent);
                break;

            case IntentRecognizer::INTENT_HOLIDAY:
                $result = $this->queryHandler->getHolidays($context);
                break;

            case IntentRecognizer::INTENT_SHIFT:
                $result = $this->queryHandler->getShift($context);
                break;

            case IntentRecognizer::INTENT_PERFORMANCE:
                $result = $this->queryHandler->getPerformance($context, $subIntent);
                break;

            case IntentRecognizer::INTENT_TEAM:
                $result = $this->queryHandler->getTeam($context);
                break;

            case IntentRecognizer::INTENT_POLICY:
                $policyType = 'general';
                if (isset($context['original_message'])) {
                    if (stripos($context['original_message'], 'leave') !== false) {
                        $policyType = 'leave';
                    } elseif (stripos($context['original_message'], 'attendance') !== false) {
                        $policyType = 'attendance';
                    }
                }
                return $this->successResponse(
                    $this->knowledge->getPolicyInfo($policyType),
                    'info'
                );

            case IntentRecognizer::INTENT_COMPANY:
                $result = $this->getCompanyInfo();
                break;

            case IntentRecognizer::INTENT_DEPARTMENT:
                $result = $this->queryHandler->getProfile($context, 'self');
                break;

            case IntentRecognizer::INTENT_UNKNOWN:
            default:
                // Check if context can help
                $lastIntent = $this->context->get(ConversationContext::CTX_LAST_INTENT);
                if ($lastIntent && $intent['confidence'] < 0.3) {
                    return $this->successResponse(
                        "I'm not sure what you mean. Are you still asking about **{$lastIntent}**?\n\n" .
                        "You can ask me about:\n• Attendance\n• Leave balance\n• Payslips\n• Tasks\n• Holidays\n\n" .
                        "Or type **help** to see all options.",
                        self::TYPE_HELP
                    );
                }
                return $this->successResponse(
                    $this->knowledge->getUnknownResponse(),
                    self::TYPE_HELP
                );
        }

        // Store entity for context
        if (!empty($context['entities'])) {
            $this->context->set(
                ConversationContext::CTX_LAST_ENTITY,
                key($context['entities']) . ' ' . current($context['entities'])
            );
        }

        // Format result
        if ($result['success']) {
            return $this->successResponse(
                $result['message'],
                isset($result['data']) && !empty($result['data']) ? self::TYPE_DATA : 'info',
                $result['data'] ?? null
            );
        } else {
            return $this->successResponse(
                $result['message'],
                self::TYPE_ERROR
            );
        }
    }

    /**
     * Handle conversational intents (greetings, thanks, etc.)
     */
    private function handleConversationalIntent(string $intent): array
    {
        $userName = $this->userContext['username'] ?? '';

        switch ($intent) {
            case IntentRecognizer::INTENT_GREETING:
                // Personalized greeting based on context
                $greeting = $this->knowledge->getGreeting($userName);

                // Add context-aware suggestions
                if ($this->context->isNewConversation()) {
                    $greeting .= "\n\n💡 **Quick Actions:**\n";
                    $greeting .= "• Check attendance\n";
                    $greeting .= "• View leave balance\n";
                    $greeting .= "• Show latest payslip\n";
                    $greeting .= "• Apply for leave\n";
                    $greeting .= "• Clock in/out";
                }

                return $this->successResponse($greeting, 'greeting');

            case IntentRecognizer::INTENT_THANKS:
                return $this->successResponse(
                    $this->knowledge->getThanksResponse(),
                    'thanks'
                );

            case IntentRecognizer::INTENT_GOODBYE:
                // Clear context on goodbye
                $this->context->clear();
                return $this->successResponse(
                    $this->knowledge->getGoodbyeResponse(),
                    'goodbye'
                );

            case IntentRecognizer::INTENT_HELP:
                return $this->successResponse(
                    $this->getAdvancedHelpMenu(),
                    self::TYPE_HELP
                );

            default:
                return $this->successResponse(
                    $this->knowledge->getUnknownResponse(),
                    self::TYPE_HELP
                );
        }
    }

    /**
     * Get advanced help menu with action capabilities
     */
    private function getAdvancedHelpMenu(): string
    {
        $baseHelp = $this->knowledge->getHelpMenu();

        $actionHelp = "\n\n🎯 **Actions I Can Perform:**\n" .
            "• Apply for leave - _\"I want to apply for sick leave\"_\n" .
            "• Clock in/out - _\"Clock me in\"_ or _\"Clock out\"_\n" .
            "• Cancel leave - _\"Cancel my leave request\"_\n" .
            "• Submit feedback - _\"I want to submit feedback\"_\n\n" .
            "🔒 **Security:** I'm protected against prompt injection and only access data you're authorized to see.";

        return $baseHelp . $actionHelp;
    }

    /**
     * Get company information
     */
    private function getCompanyInfo(): array
    {
        $companyId = $this->security->getCompanyId();

        if (!$companyId) {
            return [
                'success' => false,
                'message' => 'Company information not available.'
            ];
        }

        $sql = "SELECT name, address, email, phone FROM companies WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $companyId);
        $stmt->execute();
        $company = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$company) {
            return [
                'success' => false,
                'message' => 'Company information not found.'
            ];
        }

        $response = "🏢 **Company Information**\n\n";
        $response .= "**Name:** {$company['name']}\n";
        if (!empty($company['address'])) {
            $response .= "**Address:** {$company['address']}\n";
        }
        if (!empty($company['email'])) {
            $response .= "**Email:** {$company['email']}\n";
        }
        if (!empty($company['phone'])) {
            $response .= "**Phone:** {$company['phone']}\n";
        }

        return [
            'success' => true,
            'data' => $company,
            'message' => $response
        ];
    }

    /**
     * Build success response
     */
    private function successResponse(string $message, string $type = self::TYPE_TEXT, ?array $data = null): array
    {
        $response = [
            'success' => true,
            'message' => $message,
            'type' => $type,
            'bot' => self::BOT_NAME,
            'version' => self::BOT_VERSION,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        // Add context summary for debugging (only in dev)
        if (defined('NEXUSBOT_DEBUG') && NEXUSBOT_DEBUG) {
            $response['_context'] = $this->context->getSummary();
        }

        return $response;
    }

    /**
     * Build error response
     */
    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'type' => self::TYPE_ERROR,
            'bot' => self::BOT_NAME,
            'version' => self::BOT_VERSION,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get bot info
     */
    public static function getInfo(): array
    {
        return [
            'name' => self::BOT_NAME,
            'version' => self::BOT_VERSION,
            'description' => 'Advanced AI-powered HRMS assistant with secure role-based data access, action capabilities, and prompt injection protection',
            'features' => [
                'Prompt injection protection',
                'Role-based access control',
                'Multi-tenancy support',
                'Conversation context',
                'Action execution',
                'Natural language understanding'
            ]
        ];
    }

    /**
     * Clear user's conversation context
     */
    public function clearContext(): void
    {
        $this->context->clear();
    }

    /**
     * Get conversation history
     */
    public function getHistory(int $limit = 5): array
    {
        return $this->context->getHistory($limit);
    }
}
?>