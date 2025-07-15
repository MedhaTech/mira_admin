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

$bot_id = isset($_GET['bot_id']) ? intval($_GET['bot_id']) : 0;
if (!$bot_id) {
    die('No bot_id provided.');
}
// Fetch bot data
$stmt = $conn->prepare('SELECT * FROM bots WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $bot_id, $user_id);
$stmt->execute();
$bot_result = $stmt->get_result();
$bot = $bot_result->fetch_assoc();
if (!$bot) {
    die('Bot not found or you do not have permission to edit this bot.');
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $primary_color = trim($_POST['primary_color'] ?? '#1565c0');
    $secondary_color = trim($_POST['secondary_color'] ?? '#e6f0ff');
    $knowledge_base = $bot['knowledge_base']; // Default to existing
    // Handle knowledge base: file takes precedence over textarea
    if (!empty($_FILES['knowledge_file']['tmp_name'])) {
        $kb_file = $_FILES['knowledge_file']['tmp_name'];
        if ($_FILES['knowledge_file']['type'] === 'text/plain') {
            $knowledge_base = file_get_contents($kb_file);
        } else {
            $err = 'Knowledge base file must be a .txt file.';
        }
    } elseif (isset($_POST['knowledge_base'])) {
        $knowledge_base = $_POST['knowledge_base'];
    }
    // Handle logo upload
    $logo_path = $bot['logo']; // Default to existing
    if (!empty($_FILES['logo']['tmp_name'])) {
        $logo_dir = __DIR__ . '/uploads/';
        $logo_url_dir = 'uploads/';
        if (!is_dir($logo_dir)) mkdir($logo_dir, 0777, true);
        if (!is_writable($logo_dir)) chmod($logo_dir, 0777);
        $logo_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($logo_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $logo_name = uniqid('logo_', true) . '.' . $logo_ext;
            $logo_path = $logo_url_dir . $logo_name;
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logo_dir . $logo_name)) {
                $err = 'Failed to upload logo. Check folder permissions.';
            }
        } else {
            $err = 'Logo must be an image file (jpg, jpeg, png, gif).';
        }
    }
    if (!$err && $name && $knowledge_base) {
        $stmt = $conn->prepare('UPDATE bots SET name=?, logo=?, primary_color=?, secondary_color=?, knowledge_base=? WHERE id=? AND user_id=?');
        $stmt->bind_param('ssssssi', $name, $logo_path, $primary_color, $secondary_color, $knowledge_base, $bot_id, $user_id);
        $stmt->execute();
        header('Location: dashboard.php');
        exit();
    } elseif (!$err) {
        $err = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Bot</title>
    <style>
        body { background: #e6f0ff; font-family: Arial, sans-serif; margin: 0; }
        .container { max-width: 500px; margin: 2rem auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(21,101,192,0.1); padding: 2rem; }
        h2 { color: #1565c0; margin-bottom: 1.5rem; }
        label { display: block; margin-top: 1rem; color: #1565c0; font-weight: 600; }
        input[type="text"], textarea { width: 100%; padding: 0.7rem; border-radius: 6px; border: 1.2px solid #b3c6e6; margin-top: 0.3rem; font-size: 1rem; }
        input[type="color"] { margin-left: 0.5rem; }
        .color-row { display: flex; gap: 1.5rem; margin-top: 1rem; }
        .color-preview { display: inline-block; width: 24px; height: 24px; border-radius: 4px; border: 1px solid #b3c6e6; margin-left: 0.5rem; vertical-align: middle; }
        .logo-preview { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-top: 0.5rem; background: #f0f4fa; }
        .error { color: #c62828; margin-top: 1rem; }
        button { background: #1565c0; color: #fff; border: none; border-radius: 6px; padding: 0.7rem 1.5rem; font-size: 1.1rem; margin-top: 1.5rem; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #003c8f; }
        .back-link { display: inline-block; margin-top: 1.5rem; color: #1565c0; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Bot</h2>
        <?php if ($err): ?><div class="error"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" id="botForm">
            <label for="name">Bot Name *</label>
            <input type="text" id="name" name="name" maxlength="255" required value="<?php echo htmlspecialchars($bot['name']); ?>">

            <label for="logo">Bot Logo (jpg, png, gif)</label>
            <input type="file" id="logo" name="logo" accept="image/*" onchange="previewLogo(event)">
            <?php if ($bot['logo']): ?>
                <img id="logoPreview" class="logo-preview" src="<?php echo htmlspecialchars($bot['logo']); ?>" alt="Current Logo">
            <?php else: ?>
                <img id="logoPreview" class="logo-preview" style="display:none;" alt="Logo Preview">
            <?php endif; ?>

            <div class="color-row">
                <div>
                    <label for="primary_color">Primary Color</label>
                    <input type="color" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($bot['primary_color'] ?: '#1565c0'); ?>" onchange="updateColorPreview('primary_color')">
                    <span class="color-preview" id="primary_color_preview" style="background:<?php echo htmlspecialchars($bot['primary_color'] ?: '#1565c0'); ?>;"></span>
                </div>
                <div>
                    <label for="secondary_color">Secondary Color</label>
                    <input type="color" id="secondary_color" name="secondary_color" value="<?php echo htmlspecialchars($bot['secondary_color'] ?: '#e6f0ff'); ?>" onchange="updateColorPreview('secondary_color')">
                    <span class="color-preview" id="secondary_color_preview" style="background:<?php echo htmlspecialchars($bot['secondary_color'] ?: '#e6f0ff'); ?>;"></span>
                </div>
            </div>

            <label for="knowledge_base">Knowledge Base (paste text)</label>
            <textarea id="knowledge_base" name="knowledge_base" placeholder="Paste or type your bot's knowledge base here..." rows="7"><?php echo htmlspecialchars($bot['knowledge_base']); ?></textarea>
            <div class="or">OR</div>
            <label for="knowledge_file">Upload Knowledge Base (.txt)</label>
            <input type="file" id="knowledge_file" name="knowledge_file" accept=".txt">

            <button type="submit">Save Changes</button>
        </form>
        <a class="back-link" href="dashboard.php">&larr; Back to Dashboard</a>
    </div>
    <script>
    function updateColorPreview(id) {
        var color = document.getElementById(id).value;
        document.getElementById(id + '_preview').style.background = color;
    }
    function previewLogo(event) {
        var file = event.target.files[0];
        var preview = document.getElementById('logoPreview');
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }
    </script>
</body>
</html> 