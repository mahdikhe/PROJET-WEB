<?php
class Translator {
    private $apiKey;
    private $debug = true; // Enable debugging
    
    public function __construct() {
        $this->apiKey = 'AIzaSyDklI0-Q-lp8qnpk2u7e_y-em3-UMBfg-k';
        if ($this->debug) {
            error_log("Translator initialized with API key: " . substr($this->apiKey, 0, 5) . "...");
        }
    }
    
    public function translateToFrench($text) {
        try {
            if ($this->debug) {
                error_log("Starting translation for text: " . substr($text, 0, 50) . "...");
            }
            
            // Updated URL to use the correct API endpoint for Gemini 2.0 Flash
            $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Traduis ce texte en français. Conserve le format et la mise en page d'origine avec les sauts de ligne : " . $text
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'topP' => 0.8,
                    'topK' => 40
                ]
            ];
            
            if ($this->debug) {
                error_log("Making request to URL: " . $url);
                error_log("Request data: " . json_encode($data));
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '?key=' . $this->apiKey);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            // Add SSL verification options - Note: In production, you should enable proper SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            
            if ($this->debug) {
                error_log("Raw API Response: " . $response);
            }
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                error_log("CURL Error: " . $error);
                throw new Exception("CURL Error: " . $error);
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($this->debug) {
                error_log("HTTP Response Code: " . $httpCode);
            }
            
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($this->debug) {
                error_log("Decoded Response: " . json_encode($result));
            }
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                if ($this->debug) {
                    error_log("Translation successful");
                }
                
                // Preserve the original formatting from the response
                $translatedText = $result['candidates'][0]['content']['parts'][0]['text'];
                
                return [
                    'success' => true,
                    'translated_text' => $translatedText
                ];
            } else {
                $error = 'Translation failed: Invalid response from Gemini API';
                if (isset($result['error'])) {
                    $error .= ' - ' . json_encode($result['error']);
                }
                error_log($error);
                throw new Exception($error);
            }
        } catch (Exception $e) {
            error_log("Translation Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}