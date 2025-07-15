<?php
// CORS headers for development (allow all origins)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

$bot_id = isset($_GET['bot_id']) ? intval($_GET['bot_id']) : 0;
if (!$bot_id) {
    echo json_encode(['error' => 'No bot_id provided']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'mira_chatbot');
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}
$stmt = $conn->prepare('SELECT name, logo, primary_color, secondary_color, knowledge_base FROM bots WHERE id = ?');
$stmt->bind_param('i', $bot_id);
$stmt->execute();
$result = $stmt->get_result();
$bot = $result->fetch_assoc();
if ($bot) {
    echo json_encode($bot);
} else {
    echo json_encode(['error' => 'Bot not found']);
} 