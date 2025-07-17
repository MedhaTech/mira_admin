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

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $primary_color = trim($_POST['primary_color'] ?? '#1565c0');
    $secondary_color = trim($_POST['secondary_color'] ?? '#e6f0ff');
    $knowledge_base = '';
    // Handle knowledge base: textarea or file
    if (!empty($_POST['knowledge_base'])) {
        $knowledge_base = $_POST['knowledge_base'];
    } elseif (!empty($_FILES['knowledge_file']['tmp_name'])) {
        $kb_file = $_FILES['knowledge_file']['tmp_name'];
        if ($_FILES['knowledge_file']['type'] === 'text/plain') {
            $knowledge_base = file_get_contents($kb_file);
        } else {
            $err = 'Knowledge base file must be a .txt file.';
        }
    }
    // Handle logo upload
    $logo_path = '';
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
        $stmt = $conn->prepare('INSERT INTO bots (user_id, name, logo, primary_color, secondary_color, knowledge_base) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssss', $user_id, $name, $logo_path, $primary_color, $secondary_color, $knowledge_base);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Bot - Mira Chat Bot</title>
    <style>
        body { background: #e6f0ff; font-family: Arial, sans-serif; margin: 0; }
        .container { max-width: 500px; margin: 3rem auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(21,101,192,0.1); padding: 2rem; }
        h2 { color: #1565c0; text-align: center; }
        label { display: block; margin: 1rem 0 0.5rem; color: #1565c0; }
        input[type=text], input[type=color], textarea { width: 100%; padding: 0.5rem; border: 1px solid #b3c6e0; border-radius: 5px; }
        textarea { min-height: 120px; }
        input[type=file] { margin-top: 0.5rem; }
        .color-row { display: flex; gap: 1rem; }
        .color-row > div { flex: 1; position: relative; }
        .color-preview {
            width: 32px; height: 32px; border-radius: 6px; border: 1px solid #b3c6e0;
            position: absolute; right: 0.5rem; top: 2.2rem;
        }
        .logo-preview {
            display: block; margin: 1rem auto 0.5rem; width: 80px; height: 80px; object-fit: cover; border-radius: 50%; background: #f0f4fa; border: 1px solid #b3c6e0;
        }
        button { width: 100%; background: #1565c0; color: #fff; border: none; padding: 0.8rem; border-radius: 5px; font-size: 1.1rem; margin-top: 1.5rem; cursor: pointer; }
        button:hover { background: #003c8f; }
        .back-link { display: block; text-align: center; margin-top: 1.5rem; color: #1565c0; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .error { color: #c00; text-align: center; margin-bottom: 1rem; }
        .or { text-align: center; margin: 1rem 0; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create New Bot</h2>
        <?php if ($err): ?><div class="error"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" id="botForm">
            <label for="name">Bot Name *</label>
            <input type="text" id="name" name="name" maxlength="255" required>

            <label for="logo">Bot Logo (jpg, png, gif)</label>
            <input type="file" id="logo" name="logo" accept="image/*" onchange="previewLogo(event)">
            <img id="logoPreview" class="logo-preview" style="display:none;" alt="Logo Preview">

            <div class="color-row">
                <div>
                    <label for="primary_color">Primary Color</label>
                    <input type="color" id="primary_color" name="primary_color" value="#1565c0" onchange="updateColorPreview('primary_color')">
                    <span class="color-preview" id="primary_color_preview" style="background:#1565c0;"></span>
                </div>
                <div>
                    <label for="secondary_color">Secondary Color</label>
                    <input type="color" id="secondary_color" name="secondary_color" value="#e6f0ff" onchange="updateColorPreview('secondary_color')">
                    <span class="color-preview" id="secondary_color_preview" style="background:#e6f0ff;"></span>
                </div>
            </div>

            <label for="knowledge_base">Knowledge Base (paste text)</label>
            <textarea id="knowledge_base" name="knowledge_base" placeholder="Paste or type your bot's knowledge base here..."></textarea>
            <div class="or">OR</div>
            <label for="knowledge_file">Upload Knowledge Base (.txt)</label>
            <input type="file" id="knowledge_file" name="knowledge_file" accept=".txt">

            <button type="submit">Create Bot</button>
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