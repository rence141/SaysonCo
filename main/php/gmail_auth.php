<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'email.php';
require_once 'db.php';

// Generate a 6-digit verification code
$verification_code = sprintf("%06d", mt_rand(100000, 999999));

// Store the verification code in session
$_SESSION['gmail_verification_code'] = $verification_code;
$_SESSION['gmail_verification_time'] = time();

// Get user email from session or request
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : (isset($_POST['email']) ? $_POST['email'] : '');

if (empty($user_email)) {
    header('Location: login_users.php?error=Email is required for verification');
    exit;
}

// Store email in session
$_SESSION['user_email'] = $user_email;

// Create email body with verification code
$subject = "SaysonCo Gmail Verification Code";
$body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .code { font-size: 24px; font-weight: bold; text-align: center; padding: 10px; background-color: #f5f5f5; border-radius: 5px; margin: 20px 0; letter-spacing: 5px; }
        .footer { font-size: 12px; color: #777; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>Gmail Verification Code</h2>
        <p>Your verification code for SaysonCo is:</p>
        <div class='code'>$verification_code</div>
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not request this code, please ignore this email.</p>
        <div class='footer'>
            &copy; " . date('Y') . " SaysonCo. All rights reserved.
        </div>
    </div>
</body>
</html>
";

// Send verification code via email
$sent = send_email($user_email, $subject, $body);

if ($sent) {
    // Redirect to verification page
    header('Location: confirm_otp.php?type=gmail');
} else {
    // If email sending fails, redirect with error
    header('Location: login_users.php?error=Failed to send verification code');
}
exit;
?>