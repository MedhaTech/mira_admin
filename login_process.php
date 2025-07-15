<?php
session_start();
// MySQL connection settings
$host = 'localhost';
$db = 'mira_chatbot';
$user = 'root'; // Change if not default
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username && $password) {
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            header('Location: dashboard.php');
            exit();
        }
    }
    // Login failed
    header('Location: login.php?error=1');
    exit();
} else {
    header('Location: login.php');
    exit();
} 