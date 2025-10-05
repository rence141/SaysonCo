<?php
session_start();
include("db.php");
require_once "email.php";

// Handle token verification
if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ? AND verification_token = ? AND is_verified = 0 LIMIT 1");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();

        // Send welcome email using Gmail API
        $subject = "Welcome to SaysonCo!";
        $body = "
            <h2>Welcome to SaysonCo!</h2>
            <p>Hi <b>{$user['fullname']}</b>,</p>
            <p>Your email has been successfully verified. You can now enjoy all the features of SaysonCo.</p>
            <p>Thank you for joining us!</p>
        ";
        
        send_email($email, $subject, $body);

        // Success message with styled HTML
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Email Verified - SaysonCo</title>
            <link rel='stylesheet' href='../../css/index.css'>
            <style>
                .verification-container {
                    max-width: 600px;
                    margin: 50px auto;
                    padding: 30px;
                    background-color: #111;
                    border-radius: 10px;
                    text-align: center;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                }
                .success-icon {
                    font-size: 60px;
                    color: #44D62C;
                    margin-bottom: 20px;
                }
                .login-button {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 12px 30px;
                    background-color: #44D62C;
                    color: #000;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    transition: all 0.3s ease;
                }
                .login-button:hover {
                    background-color: #3ab526;
                    transform: translateY(-2px);
                }
            </style>
        </head>
        <body>
            <div class='verification-container'>
                <div class='success-icon'>✅</div>
                <h1>Email Verified Successfully!</h1>
                <p>Your account has been activated. You can now log in and enjoy all the features of SaysonCo.</p>
                <a href='login_users.php' class='login-button'>Log In Now</a>
            </div>
        </body>
        </html>
        ";
    } else {
        // Error message with styled HTML
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verification Failed - SaysonCo</title>
            <link rel='stylesheet' href='../../css/index.css'>
            <style>
                .verification-container {
                    max-width: 600px;
                    margin: 50px auto;
                    padding: 30px;
                    background-color: #111;
                    border-radius: 10px;
                    text-align: center;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                }
                .error-icon {
                    font-size: 60px;
                    color: #ff3b30;
                    margin-bottom: 20px;
                }
                .retry-button {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 12px 30px;
                    background-color: #44D62C;
                    color: #000;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    transition: all 0.3s ease;
                }
                .retry-button:hover {
                    background-color: #3ab526;
                    transform: translateY(-2px);
                }
            </style>
        </head>
        <body>
            <div class='verification-container'>
                <div class='error-icon'>❌</div>
                <h1>Verification Failed</h1>
                <p>The verification link is invalid or has expired. Please try signing up again or contact support.</p>
                <a href='signup_users.php' class='retry-button'>Try Again</a>
            </div>
        </body>
        </html>
        ";
    }

    $stmt->close();
} else {
    // No token or email provided
    header("Location: login_users.php");
    exit;
}

$conn->close();
?>
