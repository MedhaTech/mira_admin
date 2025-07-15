<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$username = htmlspecialchars($_SESSION['user']);

// MySQL connection settings
$host = 'localhost';
$db = 'mira_chatbot';
$user = 'root'; // Change if not default
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
// Get bots for this user
$stmt = $conn->prepare('SELECT * FROM bots WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$bots = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mira Chat Bot - Dashboard</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #e6f0ff; }
        .topbar {
            width: 100%;
            background: #1565c0;
            color: #fff;
            padding: 0.8rem 2rem;
            font-size: 1.4rem;
            font-weight: bold;
            letter-spacing: 1px;
            box-shadow: 0 2px 8px rgba(21,101,192,0.08);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }
        .topbar .logo-link {
            color: #fff;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: bold;
            letter-spacing: 1px;
            transition: opacity 0.15s;
        }
        .topbar .logo-link:hover {
            opacity: 0.7;
        }
        .sidenav {
            position: fixed;
            top: 56px;
            left: 0;
            width: 180px;
            height: calc(100vh - 56px);
            background: #fff;
            box-shadow: 2px 0 8px rgba(21,101,192,0.07);
            display: flex;
            flex-direction: column;
            padding-top: 2rem;
        }
        .sidenav a, .sidenav form button {
            color: #1565c0;
            text-decoration: none;
            font-size: 1.08rem;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            padding: 0.7rem 1.5rem;
            border-radius: 4px;
            margin: 0.2rem 0;
            text-align: left;
            transition: background 0.13s;
        }
        .sidenav a:hover, .sidenav form button:hover {
            background: #e6f0ff;
        }
        .main {
            margin-left: 200px;
            margin-top: 70px;
            max-width: 1200px;
            padding: 2rem;
        }
        .welcome {
            font-size: 1.1rem;
            color: #1565c0;
            margin-bottom: 2rem;
        }
        .bot-list {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }
        .bot-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(21,101,192,0.1);
            padding: 1.5rem;
            width: 250px;
            text-align: center;
            position: relative;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .bot-card:hover {
            box-shadow: 0 6px 18px rgba(21,101,192,0.18);
            transform: translateY(-4px) scale(1.03);
        }
        .bot-card img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
            background: #f0f4fa;
        }
        .bot-card h3 { margin: 0.5rem 0 0.2rem; color: #1565c0; }
        .bot-card .color-swatches {
            margin: 0.5rem 0;
        }
        .bot-card .color-swatches span {
            display:inline-block;width:18px;height:18px;border-radius:3px;margin-right:4px;
        }
        .bot-card .open-link {
            display: inline-block;
            margin-top: 0.5rem;
            color: #1565c0;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.05rem;
            transition: color 0.15s;
        }
        .bot-card .open-link:hover { color: #003c8f; }
        .delete-btn {
            background: #c62828;
            color: #fff;
            border: none;
            padding: 0.4rem 1.2rem;
            border-radius: 5px;
            font-size: 0.95rem;
            margin-top: 0.7rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .delete-btn:hover { background: #8e0000; }
        @media (max-width: 900px) {
            .main { padding: 1rem; margin-left: 0; }
            .bot-list { gap: 1rem; }
            .bot-card { width: 100%; }
            .sidenav { display: none; }
        }
    </style>
</head>
<body>
    <div class="topbar"><a href="index.php" class="logo-link">MiraChatBot</a></div>
    <div class="sidenav">
        <a href="dashboard.php">Dashboard</a>
        <a href="create_bot.php">Create New Bot</a>
        <form method="post" action="logout.php" style="margin:0;">
            <button type="submit">Logout</button>
        </form>
    </div>
    <div class="main">
        <div class="welcome">Welcome, <?php echo $username; ?>!</div>
        <h2>Your Bots</h2>
        <div class="bot-list">
            <?php while ($bot = $bots->fetch_assoc()): ?>
                <div class="bot-card">
                    <img src="<?php echo htmlspecialchars($bot['logo'] ?: 'default_logo.png'); ?>" alt="Bot Logo">
                    <h3><?php echo htmlspecialchars($bot['name']); ?></h3>
                    <div class="color-swatches">
                        <span style="background:<?php echo htmlspecialchars($bot['primary_color']); ?>;"></span>
                        <span style="background:<?php echo htmlspecialchars($bot['secondary_color']); ?>;"></span>
                    </div>
                    <a class="open-link" href="http://127.0.0.1:5001?bot_id=<?php echo $bot['id']; ?>" target="_blank">Test Bot</a>
                    <br>
                    <a class="open-link" href="edit_bot.php?bot_id=<?php echo $bot['id']; ?>" style="color:#fff;background:#1565c0;padding:0.4rem 1.2rem;border-radius:5px;display:inline-block;margin-top:0.7rem;margin-bottom:0.7rem;">Edit</a>
                    <form method="post" action="delete_bot.php" style="margin-top:0.5rem;">
                        <input type="hidden" name="bot_id" value="<?php echo $bot['id']; ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this bot?');">Delete</button>
                    </form>
                </div>
            <?php endwhile; ?>
            <?php if ($bots->num_rows === 0): ?>
                <p>You have no bots yet. Click 'Create New Bot' to get started!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 