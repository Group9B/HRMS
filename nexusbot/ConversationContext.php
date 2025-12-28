<?php
/**
 * ConversationContext.php
 * Advanced conversation memory and context tracking for NexusBot
 * 
 * Maintains:
 * - Conversation history
 * - User preferences
 * - Context awareness
 * - Session state
 */

class ConversationContext
{
    private $mysqli;
    private $userId;
    private $sessionId;
    private $history = [];
    private $contextData = [];
    private $maxHistoryLength = 10;

    // Context keys
    const CTX_LAST_INTENT = 'last_intent';
    const CTX_LAST_ENTITY = 'last_entity';
    const CTX_CURRENT_TOPIC = 'current_topic';
    const CTX_USER_NAME = 'user_name';
    const CTX_PENDING_ACTION = 'pending_action';
    const CTX_CLARIFICATION_NEEDED = 'clarification_needed';

    public function __construct($mysqli, int $userId)
    {
        $this->mysqli = $mysqli;
        $this->userId = $userId;
        $this->sessionId = session_id();
        $this->loadFromSession();
    }

    /**
     * Load context from session
     */
    private function loadFromSession(): void
    {
        $sessionKey = 'nexusbot_context_' . $this->userId;

        if (isset($_SESSION[$sessionKey])) {
            $data = $_SESSION[$sessionKey];
            $this->history = $data['history'] ?? [];
            $this->contextData = $data['context'] ?? [];
        }
    }

    /**
     * Save context to session
     */
    private function saveToSession(): void
    {
        $sessionKey = 'nexusbot_context_' . $this->userId;

        $_SESSION[$sessionKey] = [
            'history' => $this->history,
            'context' => $this->contextData,
            'updated_at' => time()
        ];
    }

    /**
     * Add a message to conversation history
     */
    public function addMessage(string $role, string $content, array $metadata = []): void
    {
        $message = [
            'role' => $role, // 'user' or 'bot'
            'content' => $content,
            'timestamp' => time(),
            'metadata' => $metadata
        ];

        $this->history[] = $message;

        // Trim history if too long
        if (count($this->history) > $this->maxHistoryLength) {
            $this->history = array_slice($this->history, -$this->maxHistoryLength);
        }

        $this->saveToSession();
    }

    /**
     * Get conversation history
     */
    public function getHistory(int $limit = 5): array
    {
        return array_slice($this->history, -$limit);
    }

    /**
     * Set context data
     */
    public function set(string $key, $value): void
    {
        $this->contextData[$key] = $value;
        $this->saveToSession();
    }

    /**
     * Get context data
     */
    public function get(string $key, $default = null)
    {
        return $this->contextData[$key] ?? $default;
    }

    /**
     * Check if context has key
     */
    public function has(string $key): bool
    {
        return isset($this->contextData[$key]);
    }

    /**
     * Remove context data
     */
    public function remove(string $key): void
    {
        unset($this->contextData[$key]);
        $this->saveToSession();
    }

    /**
     * Clear all context
     */
    public function clear(): void
    {
        $this->history = [];
        $this->contextData = [];
        $this->saveToSession();
    }

    /**
     * Get last user message
     */
    public function getLastUserMessage(): ?array
    {
        for ($i = count($this->history) - 1; $i >= 0; $i--) {
            if ($this->history[$i]['role'] === 'user') {
                return $this->history[$i];
            }
        }
        return null;
    }

    /**
     * Get last bot response
     */
    public function getLastBotResponse(): ?array
    {
        for ($i = count($this->history) - 1; $i >= 0; $i--) {
            if ($this->history[$i]['role'] === 'bot') {
                return $this->history[$i];
            }
        }
        return null;
    }

    /**
     * Check if user is following up on previous topic
     */
    public function isFollowUp(string $message): bool
    {
        $followUpIndicators = [
            '/^(and|also|what about|how about|tell me more)/i',
            '/^(yes|no|okay|ok|sure|please)/i',
            '/^(that|this|it|those|these)\b/i',
            '/^more\s+(details?|info|information)/i',
            '/^(show|get|give)\s+me\s+more/i',
        ];

        foreach ($followUpIndicators as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve pronouns based on context
     */
    public function resolvePronouns(string $message): string
    {
        $lastEntity = $this->get(self::CTX_LAST_ENTITY);

        if (!$lastEntity) {
            return $message;
        }

        $pronouns = ['it', 'that', 'this', 'those', 'these'];

        foreach ($pronouns as $pronoun) {
            if (preg_match('/\b' . $pronoun . '\b/i', $message)) {
                // Don't replace if it's part of a larger phrase
                if (!preg_match('/\b' . $pronoun . '\s+(is|was|are|were|has|have)/i', $message)) {
                    $message = preg_replace('/\b' . $pronoun . '\b/i', $lastEntity, $message, 1);
                }
            }
        }

        return $message;
    }

    /**
     * Get summary of current context for bot
     */
    public function getSummary(): array
    {
        return [
            'has_history' => !empty($this->history),
            'message_count' => count($this->history),
            'current_topic' => $this->get(self::CTX_CURRENT_TOPIC),
            'last_intent' => $this->get(self::CTX_LAST_INTENT),
            'pending_action' => $this->get(self::CTX_PENDING_ACTION),
            'needs_clarification' => $this->get(self::CTX_CLARIFICATION_NEEDED, false),
        ];
    }

    /**
     * Get recent topics discussed
     */
    public function getRecentTopics(): array
    {
        $topics = [];

        foreach ($this->history as $msg) {
            if (isset($msg['metadata']['intent'])) {
                $topics[] = $msg['metadata']['intent'];
            }
        }

        return array_unique($topics);
    }

    /**
     * Check if this is a new conversation
     */
    public function isNewConversation(): bool
    {
        return empty($this->history);
    }

    /**
     * Get time since last interaction
     */
    public function getIdleTime(): int
    {
        if (empty($this->history)) {
            return PHP_INT_MAX;
        }

        $lastMessage = end($this->history);
        return time() - $lastMessage['timestamp'];
    }

    /**
     * Should reset context (idle too long)
     */
    public function shouldReset(): bool
    {
        // Reset if idle for more than 30 minutes
        return $this->getIdleTime() > 1800;
    }
}
?>