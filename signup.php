<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Mira Chat Bot</title>
    <style>
        body { background: #e6f0ff; font-family: Arial, sans-serif; margin: 0; }
        .container { max-width: 350px; margin: 4rem auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(21,101,192,0.1); padding: 2rem; }
        h2 { color: #1565c0; text-align: center; }
        label { display: block; margin: 1rem 0 0.5rem; color: #1565c0; }
        input[type=text], input[type=password] { width: 100%; padding: 0.5rem; border: 1px solid #b3c6e0; border-radius: 5px; }
        button { width: 100%; background: #1565c0; color: #fff; border: none; padding: 0.7rem; border-radius: 5px; font-size: 1rem; margin-top: 1.5rem; cursor: pointer; }
        button:hover { background: #003c8f; }
        .login-link { text-align: center; margin-top: 1rem; }
        .login-link a { color: #1565c0; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up for Mira</h2>
        <form method="post" action="signup_process.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Sign Up</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html> 