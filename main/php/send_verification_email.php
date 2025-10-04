<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

function send_verification_email($to, $verificationLink) {
    $client = new Google_Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
    $client->addScope(Google_Service_Gmail::GMAIL_SEND);
    $client->setAccessType('offline');

    // Load tokens from session (or database)
    if (!isset($_SESSION['access_token'])) return false;

    $client->setAccessToken([
        'access_token' => $_SESSION['access_token'],
        'refresh_token' => $_SESSION['refresh_token']
    ]);

    // Refresh if expired
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($_SESSION['refresh_token']);
        $_SESSION['access_token'] = $client->getAccessToken()['access_token'];
    }

    $service = new Google_Service_Gmail($client);

    $subject = "Verify Your Account";
    $body = "Click the link to verify your account: <a href='$verificationLink'>$verificationLink</a>";

    $rawMessage = "From: Meta Shark <metashark@gmail.com>\r\n";
    $rawMessage .= "To: <$to>\r\n";
    $rawMessage .= "Subject: $subject\r\n";
    $rawMessage .= "MIME-Version: 1.0\r\n";
    $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $rawMessage .= $body;

    $mime = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');

    $msg = new Google_Service_Gmail_Message();
    $msg->setRaw($mime);

    try {
        $service->users_messages->send('me', $msg);
        return true;
    } catch (Exception $e) {
        error_log("Gmail API Error: " . $e->getMessage());
        return false;
    }
}

// Usage example
$verificationLink = "https://meta-shark.onrender.com/main/php/verify_account.php?email=receiver@example.com";
if (send_verification_email("receiver@example.com", $verificationLink)) {
    echo "Verification email sent!";
} else {
    echo "Failed to send email.";
}
