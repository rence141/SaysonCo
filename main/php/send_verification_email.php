<?php
require_once __DIR__ . '/email.php';

/**
 * Send account verification email.
 * 
 * @param string $to Recipient email
 * @param string $verificationLink Verification URL
 * @return bool
 */
function send_verification_email($to, $verificationLink) {
    $subject = "Verify Your Meta Shark Account";
    $body = "Hello,<br><br>Click this link to verify your account:<br>";
    $body .= "<a href='$verificationLink'>$verificationLink</a><br><br>Thanks!";
    
    return send_gmail_email($to, $subject, $body);
}
?>
