<?php
session_start();
$bot_id = isset($_GET['bot_id']) ? intval($_GET['bot_id']) : 0;
if (!$bot_id) {
    die('No bot_id provided.');
}
$conn = new mysqli('localhost', 'root', '', 'mira_chatbot');
if ($conn->connect_error) {
    die('Database connection failed');
}
$stmt = $conn->prepare('SELECT name, logo, primary_color, secondary_color FROM bots WHERE id = ?');
$stmt->bind_param('i', $bot_id);
$stmt->execute();
$result = $stmt->get_result();
$bot = $result->fetch_assoc();
if (!$bot) {
    die('Bot not found.');
}
$bot_name = htmlspecialchars($bot['name']);
$bot_logo = htmlspecialchars($bot['logo'] ?: 'default_logo.png');
$primary_color = htmlspecialchars($bot['primary_color'] ?: '#1565c0');
$secondary_color = htmlspecialchars($bot['secondary_color'] ?: '#e6f0ff');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $bot_name; ?> - MiraChatBot</title>
    <style>
        body { background: <?php echo $secondary_color; ?>; font-family: Arial, sans-serif; margin: 0; }
        .bot-header {
            background: <?php echo $primary_color; ?>;
            color: #fff;
            padding: 2rem 0 1rem 0;
            text-align: center;
        }
        .bot-header img {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #fff;
            object-fit: cover;
            margin-bottom: 0.5rem;
        }
        .bot-header h1 { margin: 0.5rem 0 0.2rem; font-size: 2rem; }
        .chat-widget-container { display: flex; justify-content: center; margin: 2rem 0; }
    </style>
</head>
<body>
    <div class="bot-header">
        <img src="<?php echo $bot_logo; ?>" alt="Bot Logo">
        <h1><?php echo $bot_name; ?></h1>
        <div style="font-size:1.1rem;opacity:0.8;">Powered by MiraChatBot</div>
    </div>
    <div class="chat-widget-container">
        <iframe id="chat-widget" src="/chat_ui.html?bot_id=<?php echo $bot_id; ?>" style="width:370px;height:650px;border:none;border-radius:18px;box-shadow:0 8px 32px rgba(21,101,192,0.18);"></iframe>
    </div>
    <script>
    // Pass bot_id to the chat widget via postMessage if needed
    // (If your chat widget JS needs to know the bot_id, you can use this)
    </script>
</body>
</html> 