<?php
/**
 * GeminiHandler.php
 * Handles integration with Google's Gemini API for advanced NLP
 */

class GeminiHandler
{
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Analyze message to detect intent and entities
     */
    public function analyze(string $message): array
    {
        $prompt = <<<EOT
You are an HRMS assistant. Analyze the user's message and extract the intent and entities.
Return a JSON object with the following structure:
{
    "intent": "string",
    "sub_intent": "string", // self, team, other, all
    "confidence": float,
    "context": {
        "time_context": "string or null", // today, yesterday, this_week, last_week, this_month, last_month, this_year, next_month
        "entities": {
            "employee_id": int,
            "month": int or null,
            "year": int or null,
            "name": "string or null",
            "leave_type": "string or null" 
        }
    }
}

Available Intents:
- attendance, leave, leave_balance, payslip, profile, task, team, holiday, shift, performance, department, policy, company
- greeting, thanks, goodbye, help, unknown
- change_theme (for requests to switch light/dark mode)

User Message: "$message"
EOT;

        $response = $this->callGemini($prompt, true);

        if ($response && isset($response['intent'])) {
            return $response;
        }

        return [
            'intent' => 'unknown',
            'sub_intent' => 'self',
            'confidence' => 0,
            'context' => []
        ];
    }

    /**
     * Generate a natural language response based on data
     */
    public function generateResponse(string $userQuery, array $data): string
    {
        $dataJson = json_encode($data);
        $prompt = <<<EOT
You are a helpful HR assistant.
User Query: "$userQuery"
System Data: $dataJson

Generate a friendly, concise natural language response to the user based on the system data provided to answer their query.
Do not invent facts. If the data indicates an error or empty result, explain it defined politely.
EOT;

        $result = $this->callGemini($prompt, false);
        return $result ? $result : "I processed your request but couldn't generate a text response.";
    }

    private function callGemini(string $prompt, bool $jsonMode)
    {
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ];

        if ($jsonMode) {
            $payload['generationConfig'] = ['responseMimeType' => 'application/json'];
        }

        $url = $this->apiUrl . '?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        // Timeout for responsiveness (Optimistic Fallback: 4s)
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return null;
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $decoded['candidates'][0]['content']['parts'][0]['text'];
            return $jsonMode ? json_decode($text, true) : $text;
        }

        return null;
    }
}
?>