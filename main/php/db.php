<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$port = getenv('DB_PORT');

// Create connection
$conn = @new mysqli($host, $user, $pass, $dbname, $port);

// Check connection
if ($conn->connect_errno) {
    // Log error silently
    error_log("Database connection failed: " . $conn->connect_error);
    exit; // prevents any output to the user
}
?>
