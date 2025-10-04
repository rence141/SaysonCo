<?php
$host = "sql12.freesqldatabase.com";
$user = "sql12801294";
$pass = "your_password_here";   // get this from your FreeSQLDatabase dashboard
$dbname = "sql12801294";        // usually same as username
$port = 3306;                    // default port for remote MySQL


// Create connection
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully";
?>  


