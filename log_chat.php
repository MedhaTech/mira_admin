<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'db_connect.php';

$bot_id = intval($_POST['bot_id'] ?? 0);
$session_id = trim($_POST['session_id'] ?? '');
$user_message = trim($_POST['user_message'] ?? '');
$bot_reply = trim($_POST['bot_reply'] ?? '');

if (!$bot_id || !$session_id || !$user_message || !$bot_reply) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if conversation exists for this session
    $stmt = $conn->prepare('SELECT id FROM conversations WHERE bot_id = ? AND session_id = ?');
    $stmt->bind_param('is', $bot_id, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversation = $result->fetch_assoc();
    
    if ($conversation) {
        // Update existing conversation
        $conversation_id = $conversation['id'];
        $stmt = $conn->prepare('UPDATE conversations SET total_messages = total_messages + 2, last_message_at = NOW() WHERE id = ?');
        $stmt->bind_param('i', $conversation_id);
        $stmt->execute();
    } else {
        // Create new conversation
        $stmt = $conn->prepare('INSERT INTO conversations (bot_id, session_id, total_messages) VALUES (?, ?, 2)');
        $stmt->bind_param('is', $bot_id, $session_id);
        $stmt->execute();
        $conversation_id = $conn->insert_id;
    }
    
    // Add user message
    $stmt = $conn->prepare('INSERT INTO conversation_messages (conversation_id, message_type, message_text) VALUES (?, "user", ?)');
    $stmt->bind_param('is', $conversation_id, $user_message);
    $stmt->execute();
    
    // Add bot reply
    $stmt = $conn->prepare('INSERT INTO conversation_messages (conversation_id, message_type, message_text) VALUES (?, "bot", ?)');
    $stmt->bind_param('is', $conversation_id, $bot_reply);
    $stmt->execute();
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Conversation logged successfully.']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to log conversation: ' . $e->getMessage()]);
} 