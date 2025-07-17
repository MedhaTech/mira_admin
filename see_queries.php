<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$username = htmlspecialchars($_SESSION['user']);
include_once 'db_connect.php';
$bot_id = isset($_GET['bot_id']) ? intval($_GET['bot_id']) : 0;
if (!$bot_id) {
    die('No bot_id provided.');
}
// Check bot ownership
$stmt = $conn->prepare('SELECT * FROM bots WHERE id = ?');
$stmt->bind_param('i', $bot_id);
$stmt->execute();
$bot = $stmt->get_result()->fetch_assoc();
if (!$bot) {
    die('Bot not found.');
}
// Fetch queries for this bot
$stmt = $conn->prepare('SELECT * FROM support_queries WHERE bot_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $bot_id);
$stmt->execute();
$queries = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Queries for <?php echo htmlspecialchars($bot['name']); ?></title>
    <style>
        body { background: #e6f0ff; font-family: Arial, sans-serif; margin: 0; }
        .container { max-width: 900px; margin: 2rem auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(21,101,192,0.1); padding: 2rem; }
        h2 { color: #1565c0; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
        th, td { border: 1px solid #b3c6e6; padding: 0.7rem; text-align: left; }
        th { background: #f6f8fa; color: #1565c0; }
        tr:nth-child(even) { background: #f9fbff; }
        .back-link { display: inline-block; margin-top: 1.5rem; color: #1565c0; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .file-link { color: #1cae4e; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Queries for Bot: <?php echo htmlspecialchars($bot['name']); ?></h2>
        <?php if ($queries->num_rows === 0): ?>
            <p>No queries found for this bot.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Category</th>
                <th>Description</th>
                <th>File</th>
                <th>Submitted At</th>
            </tr>
            <?php while ($q = $queries->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($q['name']); ?></td>
                <td><?php echo htmlspecialchars($q['email']); ?></td>
                <td><?php echo htmlspecialchars($q['phone']); ?></td>
                <td><?php echo htmlspecialchars($q['category']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($q['description'])); ?></td>
                <td>
                    <?php if ($q['file_path']): ?>
                        <a class="file-link" href="<?php echo htmlspecialchars($q['file_path']); ?>" target="_blank">Download</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($q['created_at']); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
        <a class="back-link" href="dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html> 