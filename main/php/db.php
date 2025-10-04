<?php
$host = "sql12.freesqldatabase.com";
$user = "sql12801294";
$pass = "sptMsAVPRz";  
$dbname = "sql12801294";  
$port = 3306;  

// Create connection
$conn = @new mysqli($host, $user, $pass, $dbname, $port);

// Check connection
if ($conn->connect_errno) {
    // Stop the script silently or log error without output
    error_log("Database connection failed: " . $conn->connect_error);
    exit; // prevents any output
}
?>
