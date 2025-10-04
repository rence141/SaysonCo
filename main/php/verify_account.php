<?php
session_start();
require_once __DIR__ . '/send_verification_email.php';

if (isset($_GET['email'])) {
    $email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);

    if ($email) {
        // Generate a verification token
        $token = bin2hex(random_bytes(16));
        $verificationLink = "https://meta-shark.onrender.com/main/php/confirm_email.php?email=$email&token=$token";

        // TODO: Save $token in your DB associated with the user

        if (send_verification_email($email, $verificationLink)) {
            echo "Verification email sent to $email";
        } else {
            echo "Failed to send verification email. Check logs.";
        }
    } else {
        echo "Invalid email address.";
    }
} else {
    echo "No email specified.";
}
?>
