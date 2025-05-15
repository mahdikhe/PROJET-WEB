<?php
require_once(__DIR__ . '/../../config/Database.php');
session_start();

header('Content-Type: application/json');

// Store your key securely - ideally in environment variables
$gemini_api_key = 'AIzaSyCOI6eLhW2FeipZcwKfRWlprPKcJWqUuV0';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }

        $projectType = $data['projectType'] ?? '';
        $projectName = $data['projectName'] ?? '';

        if (empty($projectType) || empty($projectName)) {
            throw new Exception('Project type and name are required');
        }

        // Improved prompt with strict JSON formatting
        $prompt = <<<PROMPT
        Generate exactly 4 task suggestions for a {$projectType} project titled "{$projectName}".
        Return ONLY a JSON array where each object has:
        - title (string)
        - description (string)
        - priority ("High", "Medium", or "Low")
        
        Example:
        [
            {
                "title": "Conduct market research",
                "description": "Gather data on target demographics",
                "priority": "High"
            }
        ]
        PROMPT;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'response_mime_type' => 'application/json'
            ]
        ];

        // Updated endpoint with correct model name
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$gemini_api_key}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new Exception("cURL Error: {$curl_error}");
        }

        if ($httpcode !== 200) {
            $error_details = json_decode($response, true)['error'] ?? $response;
            throw new Exception("API Error: " . print_r($error_details, true));
        }

        $result = json_decode($response, true);
        $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Directly parse the JSON response
        $suggestions = json_decode($generatedText, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Parse Error: " . json_last_error_msg() . "\nResponse: " . $generatedText);
        }

        echo json_encode([
            'success' => true,
            'suggestions' => $suggestions
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}