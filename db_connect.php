<?php
$host = '192.185.129.71';
$db = 'medha_mira';
$user = 'medha_mira_admin'; 
$pass = 'T^vF]OsVtc4a';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}