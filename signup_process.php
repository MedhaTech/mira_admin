<?php
session_start();
include_once 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username && $password) {
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            // Username exists
            header('Location: signup.php?error=exists');
            exit();
        }
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->bind_param('ss', $username, $hashed);
        $stmt->execute();
        $_SESSION['user'] = $username;
        header('Location: dashboard.php');
        exit();
    }
    // Signup failed
    header('Location: signup.php?error=1');
    exit();
} else {
    header('Location: signup.php');
    exit();
} 