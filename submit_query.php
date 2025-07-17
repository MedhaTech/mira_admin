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

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$bot_id = intval($_POST['bot_id'] ?? 0);
$file_path = '';
if (!empty($_FILES['file']['tmp_name'])) {
    $upload_dir = __DIR__ . '/uploads/';
    $url_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    if (!is_writable($upload_dir)) chmod($upload_dir, 0777);
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf','png','jpg','jpeg','gif','txt','doc','docx'];
    if (in_array($ext, $allowed)) {
        $file_name = uniqid('query_', true) . '.' . $ext;
        $file_path = $url_dir . $file_name;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $file_name)) {
            echo json_encode(['success' => false, 'message' => 'File upload failed.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
        exit;
    }
}
if (!$name || !$email || !$phone || !$description || !$bot_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}
$stmt = $conn->prepare('INSERT INTO support_queries (bot_id, name, email, phone, category, description, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('issssss', $bot_id, $name, $email, $phone, $category, $description, $file_path);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Query submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save query.']);
} 