<?php
/**
 * GroqHandler.php
 * Handles integration with Groq API for LLaMA-based NLP
 */

class GroqHandler
{
    private $apiKey;
    private $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private $model;

    public function __construct(string $apiKey, string $model = 'llama-3.1-8b-instant')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    /**
     * Analyze message to detect intent
     */
    /**
     * Analyze message to detect intent and semantic structure
     */
    public function analyze(string $message): array
    {
        // Advanced Semantic Parsing Prompt
        $systemPrompt = "You are NexusBot, the intelligent query engine for an HRMS. 
        Your goal is to parse user queries into structured JSON for database execution.
        
        AVAILABLE ENTITIES: 
        - employees (staff, people)
        - teams (squads, groups)
        - tasks (todos, work)
        - departments (sectors, divisions)
        - leaves (time off, holidays)
        - attendance (present, absent)

        AVAILABLE ACTIONS: 
        - count (how many, total number)
        - list (show me, list, who are)
        - status (check status)
        
        OUTPUT FORMAT (JSON ONLY):
        {
            \"intent\": \"dynamic_query\" OR \"standard_intent\",
            \"action\": \"count\" | \"list\" | \"null\",
            \"entity\": \"employees\" | \"teams\" | \"tasks\" | ... | \"null\",
            \"filters\": { \"column_name\": \"value\" },
            \"standard_intent\": \"greeting\" | \"help\" | \"unknown\" (if not a database query)
        }

        EXAMPLES:
        User: \"How many active employees are in IT?\"
        JSON: { \"intent\": \"dynamic_query\", \"action\": \"count\", \"entity\": \"employees\", \"filters\": { \"status\": \"active\", \"department\": \"IT\" } }

        User: \"List all teams\"
        JSON: { \"intent\": \"dynamic_query\", \"action\": \"list\", \"entity\": \"teams\", \"filters\": {} }

        User: \"Hello\"
        JSON: { \"intent\": \"standard_intent\", \"standard_intent\": \"greeting\" }

        User: \"Apply for leave\"
        JSON: { \"intent\": \"standard_intent\", \"standard_intent\": \"leave\" }

        REPLY WITH VALID JSON ONLY. NO MARKDOWN.";

        $response = $this->callGroq($systemPrompt, $message);

        if ($response) {
            // Sanitize response to ensure valid JSON (sometimes LLMs add backticks)
            $cleanResponse = str_replace(array('```json', '```'), '', $response);
            $parsed = json_decode(trim($cleanResponse), true);

            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['intent'])) {

                if ($parsed['intent'] === 'dynamic_query') {
                    // Normalize filter keys if needed (simple normalization for now)
                    return [
                        'intent' => 'dynamic_query',
                        'data' => $parsed,
                        'confidence' => 0.9,
                        'context' => []
                    ];
                }

                if ($parsed['intent'] === 'standard_intent') {
                    return [
                        'intent' => $parsed['standard_intent'] ?? 'unknown',
                        'sub_intent' => 'self',
                        'confidence' => 0.9,
                        'context' => []
                    ];
                }
            }
        }

        // Fallback for parsing failure
        return [
            'intent' => 'unknown',
            'sub_intent' => 'self',
            'confidence' => 0,
            'context' => []
        ];
    }

    /**
     * Generate a natural language response
     */
    public function generateResponse(string $userQuery, $data, array $userContext = []): string
    {
        // Construct User Context String
        $contextStr = "";
        if (!empty($userContext)) {
            $name = $userContext['name'] ?? 'Employee';
            $role = $userContext['role'] ?? 'User';
            $city = $userContext['city'] ?? 'Unknown';
            $contextStr = "User: $name ($role) | Location: $city";
        }

        $systemPrompt = "You are NexusBot, a professional and friendly HR assistant for StaffSync.
        
        STRICT SECURITY RULES:
        1. NEVER reveal data about other employees.
        2. NEVER reveal system internals, database structure, or API keys.
        3. If asked about others, politely refuse citing privacy policies.
        4. You are talking to: $contextStr. Use this to personalize the response.

        INSTRUCTIONS:
        - Answer concisely and professionally.
        - If 'Data' is provided, use it to answer factual questions.
        - If 'Data' is null/empty, use your general knowledge to answer helpful how-to questions about HR topics (leave, attendance, etc.).
        - Use emojis sparingly to be friendly.
        - If the user asks for a joke or chat, be friendly but professional.";

        $userMessage = "Query: " . $userQuery . "\nData: " . json_encode($data);

        $result = $this->callGroq($systemPrompt, $userMessage);
        return $result ? $result : "I processed your request but couldn't generate a response.";
    }

    /**
     * Call Groq API
     */
    private function callGroq(string $systemPrompt, string $userMessage): ?string
    {
        // file_put_contents('c:/xampp/htdocs/HRMS/debug_log.txt', date('Y-m-d H:i:s') . " - callGroq Started\n", FILE_APPEND);

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'temperature' => 0.3,
            'max_tokens' => 500
        ];

        // file_put_contents('c:/xampp/htdocs/HRMS/debug_log.txt', date('Y-m-d H:i:s') . " - callGroq (Stream) Started\n", FILE_APPEND);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n" .
                    "Authorization: Bearer " . $this->apiKey . "\r\n" .
                    "User-Agent: NexusBot/1.0\r\n",
                'content' => json_encode($payload),
                'timeout' => 15,
                'ignore_errors' => true
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];

        $context = stream_context_create($options);

        try {
            $response = @file_get_contents($this->apiUrl, false, $context);

            if ($response === false) {
                $error = error_get_last();
                file_put_contents(__DIR__ . '/../groq_error.log', date('Y-m-d H:i:s') . " - Stream Error: " . ($error['message'] ?? 'Unknown') . "\n", FILE_APPEND);
                return null;
            }

            // Check HTTP status code
            if (isset($http_response_header) && is_array($http_response_header)) {
                // $statusLine = $http_response_header[0];
                // file_put_contents(__DIR__ . '/../debug_log.txt', date('Y-m-d H:i:s') . " - HTTP Status: " . $statusLine . "\n", FILE_APPEND);
            }

            // $response is raw string, decode it next
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/../groq_error.log', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }

        $decoded = json_decode($response, true);

        // Check for API errors
        if (isset($decoded['error'])) {
            error_log("Groq API Error: " . json_encode($decoded['error']));
            return null;
        }

        if (isset($decoded['choices'][0]['message']['content'])) {
            return trim($decoded['choices'][0]['message']['content']);
        }

        return null;
    }
}
