<?php
// Start session at the very beginning
session_start();

// Prevent any output before headers
ob_start();

// Enable error logging to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-errors.log');
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Create a log file
$logFile = __DIR__ . '/ai-chat-debug.log';

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function sendJsonResponse($data) {
    ob_clean(); // Clear any previous output
    echo json_encode($data);
    exit;
}

try {
    writeLog('=== New Request ===');
    writeLog('Request Method: ' . $_SERVER['REQUEST_METHOD']);
    writeLog('Request Headers: ' . json_encode(getallheaders()));

    // Initialize chat history if not exists
    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [];
        // Add initial greeting if this is the first message
        if (empty($_SESSION['chat_history'])) {
            $_SESSION['chat_history'][] = [
                'role' => 'assistant',
                'content' => "Hello! I'm your event assistant. How can I help you today?"
            ];
        }
    }

    // Check if required files exist
    $configFile = dirname(__DIR__) . "/config.php";
    $modelFile = dirname(__DIR__) . "/model/model.php";
    $controllerFile = dirname(__DIR__) . "/controller/conttroler.php";

    writeLog('Checking required files:');
    writeLog('Config file exists: ' . (file_exists($configFile) ? 'Yes' : 'No'));
    writeLog('Model file exists: ' . (file_exists($modelFile) ? 'Yes' : 'No'));
    writeLog('Controller file exists: ' . (file_exists($controllerFile) ? 'Yes' : 'No'));

    if (!file_exists($configFile) || !file_exists($modelFile) || !file_exists($controllerFile)) {
        throw new Exception("Required files are missing");
    }

    // Include database connection
    require_once $configFile;
    require_once $modelFile;
    require_once $controllerFile;

    // Function to get upcoming events
    function getUpcomingEvents() {
        try {
            writeLog('Creating EventController instance');
            $eventController = new EventController();
            
            writeLog('Fetching events');
            $events = $eventController->listEvents();
            writeLog('Events fetched: ' . json_encode($events));
            
            if (empty($events)) {
                writeLog('No events found');
                return [];
            }
            
            // Sort events by date
            usort($events, function($a, $b) {
                return strtotime($a['start_date'] . ' ' . $a['start_time']) - strtotime($b['start_date'] . ' ' . $b['start_time']);
            });
            
            writeLog('Events sorted successfully');
            return $events;
        } catch (Exception $e) {
            writeLog('Error in getUpcomingEvents: ' . $e->getMessage());
            writeLog('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    // Function to make API request to Google's Generative AI
    function generateAIResponse($message) {
        writeLog('Generating AI response for message: ' . $message);
        
        // Get upcoming events
        $events = getUpcomingEvents();
        $eventsContext = "";
        
        if (!empty($events)) {
            $eventsContext = "Here are our exciting upcoming events with their details:\n";
            foreach ($events as $event) {
                // Get available seats
                $eventController = new EventController();
                $availableSeats = $eventController->getEventAvailableSeats($event['event_id']);
                $totalReservations = $eventController->getTotalReservations($event['event_id']);
                
                $eventsContext .= sprintf(
                    "- %s\n" .
                    "  Date: %s\n" .
                    "  Time: %s\n" .
                    "  Format: %s\n" .
                    "  Location: %s\n" .
                    "  Capacity: %d seats\n" .
                    "  Available: %d seats\n" .
                    "  Price: $%.2f\n" .
                    "  Type: %s\n\n",
                    $event['event_title'],
                    date("j F Y", strtotime($event['start_date'])),
                    $event['start_time'],
                    $event['event_format'],
                    $event['event_format'] === 'online' ? 'Online Event' : $event['location'],
                    $event['capacity'],
                    $availableSeats,
                    $event['price'],
                    $event['event_type']
                );
            }
        } else {
            $eventsContext = "We're currently planning some exciting events. Please check back soon!";
        }
        
        writeLog('Events context prepared: ' . $eventsContext);
        
        $apiKey = "AIzaSyCMKC26jKGWz8YXgaz9Al_J0vDk0in31MY";
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
        
        // Get conversation history from session
        $chatHistory = array_slice($_SESSION['chat_history'], -5);
        $conversationContext = "";
        foreach ($chatHistory as $chat) {
            $conversationContext .= ($chat['role'] === 'user' ? "User" : "Assistant") . ": " . $chat['content'] . "\n";
        }
        
        // Create a more contextual and sales-oriented prompt
        $prompt = "You are a friendly and enthusiastic event sales assistant for a website. Your goal is to help users find and book events that interest them. Use this context about upcoming events and the conversation history to provide a contextual response:\n\n" .
                  "Events Information:\n" . $eventsContext . "\n" .
                  "Recent Conversation:\n" . $conversationContext . "\n" .
                  "Current User Question: " . $message . "\n\n" .
                  "Guidelines for your response:\n" .
                  "1. Keep responses short and to the point (2-3 sentences max)\n" .
                  "2. Be casual and conversational, like texting a friend\n" .
                  "3. Use simple, direct language\n" .
                  "4. Avoid unnecessary emojis and formatting\n" .
                  "5. If asked about price, just state the amount clearly\n" .
                  "6. If asked about location, just give the venue name\n" .
                  "7. If asked about time, just give the date and time\n" .
                  "8. Only mention available seats if specifically asked\n" .
                  "9. End with a simple question if needed\n" .
                  "10. Maintain context from previous messages\n" .
                  "11. If the user asks for shorter answers, keep it even more concise\n" .
                  "12. Avoid repeating information unless specifically asked";
        
        writeLog('Prepared prompt: ' . $prompt);
        
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 1024,
            ]
        ];

        writeLog('API Request URL: ' . $url);
        writeLog('API Request Data: ' . json_encode($data));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
            writeLog('Curl error: ' . $curlError);
            writeLog('Curl error number: ' . curl_errno($ch));
            throw new Exception("API request failed: " . $curlError);
        }
        
        writeLog('API Response Code: ' . $httpCode);
        writeLog('API Response: ' . $response);
        
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            writeLog('Decoded response: ' . json_encode($result));
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'];
                // Add AI response to chat history
                $_SESSION['chat_history'][] = ['role' => 'assistant', 'content' => $aiResponse];
                writeLog('Successfully generated AI response: ' . $aiResponse);
                return $aiResponse;
            } else {
                writeLog('Error: Unexpected API response structure');
                writeLog('Response structure: ' . json_encode($result));
                
                if (isset($result['error'])) {
                    writeLog('API Error: ' . json_encode($result['error']));
                    throw new Exception("API Error: " . $result['error']['message']);
                }
                throw new Exception("Unexpected API response structure");
            }
        } else {
            writeLog('Error: API request failed with status code ' . $httpCode);
            if ($response) {
                $errorData = json_decode($response, true);
                if (isset($errorData['error'])) {
                    writeLog('API Error Details: ' . json_encode($errorData['error']));
                    throw new Exception("API Error: " . $errorData['error']['message']);
                }
            }
            throw new Exception("API request failed with status code " . $httpCode);
        }
    }

    // Handle incoming chat messages
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        writeLog('Raw input: ' . $rawInput);
        
        $input = json_decode($rawInput, true);
        writeLog('Decoded input: ' . json_encode($input));
        
        $message = isset($input['message']) ? $input['message'] : '';
        writeLog('Extracted message: ' . $message);
        
        if (!empty($message)) {
            // Add user message to chat history
            $_SESSION['chat_history'][] = ['role' => 'user', 'content' => $message];
            
            $response = generateAIResponse($message);
            writeLog('Sending response: ' . $response);
            sendJsonResponse(['response' => $response]);
        } else {
            writeLog('Error: Empty message received');
            sendJsonResponse(['error' => 'No message provided']);
        }
    } else {
        writeLog('Error: Invalid request method ' . $_SERVER['REQUEST_METHOD']);
        sendJsonResponse(['error' => 'Invalid request method']);
    }

} catch (Exception $e) {
    writeLog('Exception caught: ' . $e->getMessage());
    writeLog('Stack trace: ' . $e->getTraceAsString());
    sendJsonResponse(['error' => $e->getMessage()]);
}

writeLog('=== End Request ===');

// Clear any output buffer and send response
ob_end_clean();
?> 