<?php
include_once 'db_connect.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test connection
    if ($conn->ping()) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
        exit;
    }
    
    // Check if tables exist
    $tables = ['users', 'bots', 'conversations', 'conversation_messages', 'support_queries'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
        }
    }
    
    // Check if there are any bots
    $result = $conn->query("SELECT COUNT(*) as count FROM bots");
    $row = $result->fetch_assoc();
    echo "<p>Total bots in database: " . $row['count'] . "</p>";
    
    // Check if there are any conversations
    $result = $conn->query("SELECT COUNT(*) as count FROM conversations");
    $row = $result->fetch_assoc();
    echo "<p>Total conversations in database: " . $row['count'] . "</p>";
    
    // Check if there are any conversation messages
    $result = $conn->query("SELECT COUNT(*) as count FROM conversation_messages");
    $row = $result->fetch_assoc();
    echo "<p>Total conversation messages in database: " . $row['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 