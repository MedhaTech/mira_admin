<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Gemini API Configuration
$GEMINI_API_KEY = 'AIzaSyAzGRJ-6AeIMAl_ugfKq5ML2cp7065_e_Y'; // Replace this with your actual API key
$GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $GEMINI_API_KEY;

function callGeminiAPI($prompt) {
    global $GEMINI_API_URL;
    
    $payload = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GEMINI_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ['error' => 'API request failed with code: ' . $httpCode];
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return ['error' => 'Invalid API response'];
    }
    
    return ['success' => true, 'text' => $data['candidates'][0]['content']['parts'][0]['text']];
}

function analyzeUserQuestions($userMessages) {
    if (empty($userMessages)) {
        return [];
    }
    
    // Create a better prompt for Gemini to analyze the questions
    $prompt = "Analyze the following user messages from a chatbot conversation and identify the top 5 most frequently asked questions or topics. 

    IMPORTANT: Create natural, meaningful questions that users would actually ask. For example:
    - If users ask 'hi', 'hello', 'who are you' → group as 'Who are you?' 
    - If users ask about a company name → 'What is [Company Name]?'
    - If users ask about services → 'What services do you offer?'
    - If users ask about contact info → 'How can I contact you?'

    User messages to analyze:
";
    
    foreach ($userMessages as $msg) {
        $prompt .= "- " . $msg . "\n";
    }
    
    $prompt .= "\nPlease return ONLY a valid JSON array with 'question' and 'count' fields. Group similar questions together and count how many times each type of question was asked.

Example format:
[{\"question\": \"Who are you?\", \"count\": 15}, {\"question\": \"What is Medha Tech?\", \"count\": 8}, {\"question\": \"How can I contact you?\", \"count\": 5}]

Return only the JSON array, nothing else.";
    
    $result = callGeminiAPI($prompt);
    
    if (isset($result['error'])) {
        // Fallback: simple analysis
        return simpleFallbackAnalysis($userMessages);
    }
    
    // Try to parse the JSON response
    $jsonStart = strpos($result['text'], '[');
    $jsonEnd = strrpos($result['text'], ']');
    
    if ($jsonStart !== false && $jsonEnd !== false) {
        $jsonStr = substr($result['text'], $jsonStart, $jsonEnd - $jsonStart + 1);
        $parsed = json_decode($jsonStr, true);
        if (is_array($parsed)) {
            return array_slice($parsed, 0, 5); // Return top 5
        }
    }
    
    // Fallback if JSON parsing fails
    return simpleFallbackAnalysis($userMessages);
}

function simpleFallbackAnalysis($userMessages) {
    $question_counts = [];
    
    foreach ($userMessages as $msg) {
        $msg = trim($msg);
        if (!empty($msg)) {
            $question_counts[$msg] = ($question_counts[$msg] ?? 0) + 1;
        }
    }
    
    arsort($question_counts);
    $top_questions = array_slice($question_counts, 0, 5, true);
    
    $questions = [];
    foreach ($top_questions as $question => $count) {
        $questions[] = [
            'question' => $question,
            'count' => $count
        ];
    }
    
    return $questions;
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'analyze_questions') {
        $userMessages = $input['messages'] ?? [];
        $result = analyzeUserQuestions($userMessages);
        echo json_encode(['success' => true, 'questions' => $result]);
    } else {
        echo json_encode(['error' => 'Invalid action']);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?> 