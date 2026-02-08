<?php
/**
 * api.php (Advanced Version)
 * NexusBot API Endpoint - Enhanced with:
 * - Advanced security checks
 * - Request logging
 * - Analytics tracking
 * - Rate limiting with backoff
 * - CSRF protection option
 */

// Set JSON response header
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Include database and dependencies
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/NexusBot.php';


// Configuration
define('NEXUSBOT_DEBUG', false); // Set to true for development
define('NEXUSBOT_LOG_FILE', __DIR__ . '/logs/nexusbot.log');
define('NEXUSBOT_RATE_LIMIT', 30); // Max requests per minute
define('NEXUSBOT_RATE_WINDOW', 60); // Window in seconds
define('NEXUSBOT_MAX_MESSAGE_LENGTH', 500);

// Disable error display for API JSON integrity
error_reporting(0);
ini_set('display_errors', 0);

/**
 * Log request for analytics
 */
function logRequest(array $data): void
{
    if (!NEXUSBOT_DEBUG)
        return;

    $logDir = dirname(NEXUSBOT_LOG_FILE);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $logEntry = date('Y-m-d H:i:s') . ' | ' . json_encode($data) . PHP_EOL;
    @file_put_contents(NEXUSBOT_LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Advanced rate limiting with exponential backoff (Bot Specific)
 */
function checkBotRateLimit(): array
{
    $rateLimitKey = 'nexusbot_rate_' . ($_SESSION['user_id'] ?? 'anon');
    $violationsKey = 'nexusbot_violations_' . ($_SESSION['user_id'] ?? 'anon');

    // Initialize if needed
    if (!isset($_SESSION[$rateLimitKey])) {
        $_SESSION[$rateLimitKey] = [
            'count' => 0,
            'window_start' => time()
        ];
    }
    if (!isset($_SESSION[$violationsKey])) {
        $_SESSION[$violationsKey] = 0;
    }

    $rateData = &$_SESSION[$rateLimitKey];
    $violations = &$_SESSION[$violationsKey];

    // Reset if window expired
    if (time() - $rateData['window_start'] > NEXUSBOT_RATE_WINDOW) {
        $rateData['count'] = 0;
        $rateData['window_start'] = time();
        // Decay violations over time
        if ($violations > 0) {
            $violations--;
        }
    }

    // Calculate rate limit with backoff for repeat violators
    $effectiveLimit = max(5, NEXUSBOT_RATE_LIMIT - ($violations * 5));

    // Check limit
    if ($rateData['count'] >= $effectiveLimit) {
        $violations++;
        $waitTime = min(60, 10 * $violations); // Exponential backoff
        return [
            'allowed' => false,
            'wait_seconds' => $waitTime,
            'message' => "Too many requests. Please wait {$waitTime} seconds before trying again."
        ];
    }

    $rateData['count']++;
    return ['allowed' => true];
}

/**
 * Validate request origin (basic CSRF-like protection)
 */
function validateOrigin(): bool
{
    // Skip validation if disabled
    if (defined('NEXUSBOT_SKIP_ORIGIN_CHECK') && NEXUSBOT_SKIP_ORIGIN_CHECK) {
        return true;
    }

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';

    // Check if request is from same origin
    if (!empty($origin)) {
        $originHost = parse_url($origin, PHP_URL_HOST);
        if ($originHost && $originHost === $host) {
            return true;
        }
    }

    if (!empty($referer)) {
        $refererHost = parse_url($referer, PHP_URL_HOST);
        if ($refererHost && $refererHost === $host) {
            return true;
        }
    }

    // Allow if no origin/referer (direct API call in same session)
    if (empty($origin) && empty($referer)) {
        return true;
    }

    return false;
}

/**
 * Send JSON response
 */
function sendResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);

    // Add security headers
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

    // Log the response
    logRequest([
        'type' => 'response',
        'status' => $statusCode,
        'success' => $data['success'] ?? false
    ]);

    exit;
}

/**
 * Get user context for NexusBot
 */
function getUserContext($mysqli): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $userId = $_SESSION['user_id'];

    // Get user details with role
    $sql = "SELECT u.id as user_id, u.company_id, u.role_id, u.username, u.email, 
                   r.name as role_name, e.id as employee_id
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            LEFT JOIN employees e ON u.id = e.user_id
            WHERE u.id = ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}

/**
 * Handle special API commands
 */
function handleSpecialCommands(string $command, array $userContext, $mysqli): ?array
{
    switch ($command) {
        case 'clear':
        case 'clear_context':
        case 'reset':
            // Clear conversation context
            $sessionKey = 'nexusbot_context_' . ($userContext['user_id'] ?? 0);
            unset($_SESSION[$sessionKey]);
            return [
                'success' => true,
                'message' => "🔄 Conversation cleared! Start fresh - how can I help you?",
                'type' => 'system'
            ];

        case 'info':
        case 'about':
        case 'version':
            return [
                'success' => true,
                'message' => "🤖 **NexusBot v2.0**\n\n" .
                    "Advanced AI-powered HRMS assistant with:\n" .
                    "• Prompt injection protection\n" .
                    "• Role-based access control\n" .
                    "• Conversation memory\n" .
                    "• Action capabilities\n\n" .
                    "Made with ❤️ for StaffSync",
                'type' => 'info'
            ];

        case 'history':
            $bot = new NexusBot($mysqli, $userContext);
            $history = $bot->getHistory(5);
            if (empty($history)) {
                return [
                    'success' => true,
                    'message' => "No conversation history yet. Start chatting!",
                    'type' => 'info'
                ];
            }
            $msg = "📜 **Recent Conversation**\n\n";
            foreach ($history as $h) {
                $role = $h['role'] === 'user' ? 'You' : 'NexusBot';
                $preview = strlen($h['content']) > 50 ? substr($h['content'], 0, 50) . '...' : $h['content'];
                $msg .= "**{$role}:** {$preview}\n";
            }
            return [
                'success' => true,
                'message' => $msg,
                'type' => 'info'
            ];

        default:
            return null;
    }
}

// ============ Main Request Handler ============

// Log incoming request
logRequest([
    'type' => 'request',
    'method' => $_SERVER['REQUEST_METHOD'],
    'user_id' => $_SESSION['user_id'] ?? null,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse([
        'success' => false,
        'message' => 'Method not allowed',
        'allowed_methods' => ['POST']
    ], 405);
}

// Validate origin
if (!validateOrigin()) {
    logRequest(['type' => 'security', 'reason' => 'invalid_origin']);
    sendResponse([
        'success' => false,
        'message' => 'Invalid request origin'
    ], 403);
}

// Check authentication - Allow Guests
// Rate limiting is done per session, even for guests

// Check rate limit
$rateCheck = checkBotRateLimit();
if (!$rateCheck['allowed']) {
    logRequest(['type' => 'rate_limit', 'user_id' => $_SESSION['user_id'] ?? null]);
    sendResponse([
        'success' => false,
        'message' => $rateCheck['message'],
        'retry_after' => $rateCheck['wait_seconds']
    ], 429);
}

// Get request body
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Validate JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    sendResponse([
        'success' => false,
        'message' => 'Invalid JSON in request body'
    ], 400);
}

// Validate input
if (!$data || !isset($data['message'])) {
    sendResponse([
        'success' => false,
        'message' => 'Invalid request. Message is required.',
        'example' => ['message' => 'What is my leave balance?']
    ], 400);
}

$message = trim($data['message']);

// Validate message length
if (strlen($message) > NEXUSBOT_MAX_MESSAGE_LENGTH) {
    sendResponse([
        'success' => false,
        'message' => 'Message too long. Maximum ' . NEXUSBOT_MAX_MESSAGE_LENGTH . ' characters.',
        'length' => strlen($message),
        'max_length' => NEXUSBOT_MAX_MESSAGE_LENGTH
    ], 400);
}

// Check authentication - Allow Guests
$userContext = null;
if (isLoggedIn()) {
    $userContext = getUserContext($mysqli);
} else {
    // Create Guest Context
    $userContext = [
        'user_id' => 0,
        'username' => 'Guest',
        'role_id' => 0,
        'role_name' => 'Guest',
        'company_id' => 0,
        'email' => '',
        'employee_id' => 0
    ];
}

if (!$userContext) {
    // Fallback if login session exists but DB lookup failed
    logRequest(['type' => 'error', 'reason' => 'user_context_failed']);
    $userContext = [
        'user_id' => 0,
        'username' => 'Guest',
        'role_id' => 0,
        'role_name' => 'Guest',
        'company_id' => 0
    ];
}

// Check for special commands (prefixed with /)
if (preg_match('/^\/(\w+)$/', $message, $matches)) {
    $specialResponse = handleSpecialCommands($matches[1], $userContext, $mysqli);
    if ($specialResponse) {
        sendResponse($specialResponse);
    }
}

// Initialize NexusBot and process message
try {
    $startTime = microtime(true);

    $bot = new NexusBot($mysqli, $userContext);
    $response = $bot->process($message);

    $processingTime = round((microtime(true) - $startTime) * 1000, 2);
    $response['processing_time_ms'] = $processingTime;

    // Log successful processing
    logRequest([
        'type' => 'processed',
        'user_id' => $userContext['user_id'],
        'processing_time_ms' => $processingTime,
        'response_type' => $response['type'] ?? 'unknown'
    ]);

    sendResponse($response);

} catch (Exception $e) {
    // Log error
    error_log("NexusBot Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    logRequest([
        'type' => 'error',
        'user_id' => $userContext['user_id'] ?? null,
        'error' => $e->getMessage()
    ]);

    sendResponse([
        'success' => false,
        'message' => 'An error occurred while processing your request. Please try again.',
        'type' => 'error',
        'error_code' => 'INTERNAL_ERROR'
    ], 500);
}
?>