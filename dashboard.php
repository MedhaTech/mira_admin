<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$username = htmlspecialchars($_SESSION['user']);

include_once 'db_connect.php';
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
        <a href="reports.php">Reports</a>
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
                    <a class="open-link" href="see_queries.php?bot_id=<?php echo $bot['id']; ?>" style="color:#fff;background:#1cae4e;padding:0.4rem 1.2rem;border-radius:5px;display:inline-block;margin-top:0.7rem;margin-bottom:0.7rem;">See Queries</a>
                    <button type="button" class="embed-btn" data-bot-id="<?php echo $bot['id']; ?>" style="background:#ffb300;color:#fff;padding:0.4rem 1.2rem;border:none;border-radius:5px;display:inline-block;margin-top:0.7rem;margin-bottom:0.7rem;cursor:pointer;">Embed</button>
                    <span class="embed-copied-msg" style="display:none;color:#388e3c;font-size:0.95rem;margin-left:8px;">Copied!</span>
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
<script>
// Embed button clipboard logic
function getEmbedCode(botId, logo, primaryColor, secondaryColor) {
    var loc = window.location;
    var flaskUrl = loc.protocol + '//' + loc.hostname + ':5001';
    // Escape logo URL for JS string
    logo = logo.replace(/'/g, "\\'");
    primaryColor = primaryColor || '#F72534';
    secondaryColor = secondaryColor || '#ff4a5a';
    return `<script>\n(function () {\n    if (window.BubbyWidgetLoaded) return;\n    window.BubbyWidgetLoaded = true;\n    var FLASK_URL = '` + flaskUrl + `';\n    var BOT_ID = '` + botId + `';\n    var BOT_LOGO = '` + logo + `';\n    var PRIMARY_COLOR = '` + primaryColor + `';\n    var SECONDARY_COLOR = '` + secondaryColor + `';\n    var fab = document.createElement('div');\n    fab.style.position = 'fixed';\n    fab.style.bottom = '32px';\n    fab.style.right = '32px';\n    fab.style.width = '64px';\n    fab.style.height = '60px';\n    fab.style.background = PRIMARY_COLOR;\n    fab.style.borderRadius = '50%';\n    fab.style.boxShadow = '0 4px 16px rgba(0,0,0,0.18)';\n    fab.style.display = 'flex';\n    fab.style.alignItems = 'center';\n    fab.style.justifyContent = 'center';\n    fab.style.cursor = 'pointer';\n    fab.style.zIndex = '99999';\n    fab.title = 'Chat with Mira';\n    var img = document.createElement('img');\n    img.src = BOT_LOGO || 'https://cdn-icons-png.flaticon.com/512/4712/4712035.png';\n    img.style.width = '36px';\n    img.style.height = '36px';\n    img.alt = 'Chat with Mira';\n    fab.appendChild(img);\n    var iframe = document.createElement('iframe');\n    iframe.src = FLASK_URL + '/?bot_id=' + encodeURIComponent(BOT_ID) + '&logo=' + encodeURIComponent(BOT_LOGO) + '&primary_color=' + encodeURIComponent(PRIMARY_COLOR) + '&secondary_color=' + encodeURIComponent(SECONDARY_COLOR);\n    iframe.style.position = 'fixed';\n    iframe.style.bottom = '32px';\n    iframe.style.right = '32px';\n    iframe.style.width = '370px';\n    iframe.style.borderRadius = '18px';\n    iframe.style.boxShadow = '0 8px 32px rgba(247,37,52,0.18)';\n    iframe.style.zIndex = '100000';\n    iframe.style.display = 'none';\n    iframe.allowTransparency = 'true';\n    iframe.style.height = '570px';\n    iframe.style.border = 'none';\n    iframe.style.margin = '0';\n    iframe.style.padding = '10';\n    fab.onclick = function (e) {\n        e.stopPropagation();\n        iframe.style.display = (iframe.style.display === 'none') ? 'block' : 'none';\n        fab.style.display = (iframe.style.display === 'block') ? 'none' : 'flex';\n    };\n    document.addEventListener('click', function (e) {\n        if (iframe.style.display === 'block' && !iframe.contains(e.target) && !fab.contains(e.target)) {\n            iframe.style.display = 'none';\n            fab.style.display = 'flex';\n        }\n    });\n    window.addEventListener('message', function (event) {\n        if (event.data && event.data.type === 'bubby-close') {\n            iframe.style.display = 'none';\n            fab.style.display = 'flex';\n        }\n    });\n    document.body.appendChild(fab);\n    document.body.appendChild(iframe);\n})();\n<\/script>`;
}
document.querySelectorAll('.embed-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var botId = btn.getAttribute('data-bot-id');
        var botRow = btn.closest('.bot-card');
        var logo = botRow.querySelector('img').src;
        var primaryColor = botRow.querySelector('.color-swatches span:first-child').style.background;
        var secondaryColor = botRow.querySelector('.color-swatches span:last-child').style.background;
        var embedCode = getEmbedCode(botId, logo, primaryColor, secondaryColor);
        // Copy to clipboard
        navigator.clipboard.writeText(embedCode).then(function() {
            // Show copied message
            var msg = btn.nextElementSibling;
            msg.style.display = 'inline';
            setTimeout(function() { msg.style.display = 'none'; }, 1200);
        });
    });
});
</script>
</html> 