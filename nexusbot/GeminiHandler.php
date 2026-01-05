<?php
/**
 * GeminiHandler.php
 * Simplified Gemini integration - Plain text approach
 */

class GeminiHandler
{
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Analyze message to detect intent - Simplified approach
     */
    public function analyze(string $message): array
    {
        // Simple prompt asking for just the intent keyword
        $prompt = <<<EOT
Classify this HRMS query into ONE of these intents:
attendance, leave, leave_balance, payslip, profile, task, team, holiday, shift, performance, department, policy, company, greeting, thanks, goodbye, help, change_theme, general_chat, unknown

Query: "$message"

Reply with ONLY the intent word, nothing else.
EOT;

        $response = $this->callGemini($prompt);

        if ($response) {
            // Clean and extract intent
            $intent = strtolower(trim($response));
            $intent = preg_replace('/[^a-z_]/', '', $intent);

            // Validate it's a known intent
            $validIntents = ['attendance', 'leave', 'leave_balance', 'payslip', 'profile', 'task', 'team', 'holiday', 'shift', 'performance', 'department', 'policy', 'company', 'greeting', 'thanks', 'goodbye', 'help', 'change_theme', 'general_chat', 'unknown'];

            if (in_array($intent, $validIntents)) {
                return [
                    'intent' => $intent,
                    'sub_intent' => 'self',
                    'confidence' => 0.9,
                    'context' => []
                ];
            }
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
You are a helpful HR assistant. Answer this query based on the data provided.

Query: "$userQuery"
Data: $dataJson

Give a friendly, concise response. Do not invent facts.
EOT;

        $result = $this->callGemini($prompt);
        return $result ? $result : "I processed your request but couldn't generate a response.";
    }

    /**
     * Simple API call - no JSON mode
     */
    private function callGemini(string $prompt): ?string
    {
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ];

        $url = $this->apiUrl . '?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Gemini cURL Error: " . $error);
            return null;
        }

        $decoded = json_decode($response, true);

        // Check for API errors
        if (isset($decoded['error'])) {
            error_log("Gemini API Error: " . json_encode($decoded['error']));
            return null;
        }

        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($decoded['candidates'][0]['content']['parts'][0]['text']);
        }

        return null;
    }
}