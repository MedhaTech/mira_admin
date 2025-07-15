<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$username = $_SESSION['user'];

// MySQL connection settings
$host = 'localhost';
$db = 'mira_chatbot';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
// Get user ID
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$userRow = $result->fetch_assoc();
$user_id = $userRow['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bot_id'])) {
    $bot_id = intval($_POST['bot_id']);
    // Get bot and check ownership
    $stmt = $conn->prepare('SELECT logo FROM bots WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $bot_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bot = $result->fetch_assoc();
    if ($bot) {
        // Delete logo file if exists
        if (!empty($bot['logo']) && file_exists(__DIR__ . '/' . $bot['logo'])) {
            unlink(__DIR__ . '/' . $bot['logo']);
        }
        // Delete bot from DB
        $stmt = $conn->prepare('DELETE FROM bots WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $bot_id, $user_id);
        $stmt->execute();
    }
}
header('Location: dashboard.php');
exit(); 