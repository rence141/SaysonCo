<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

function send_email($to, $subject, $body) {
    $client = new Google_Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
    $client->addScope(Google_Service_Gmail::GMAIL_SEND);
    $client->setAccessType('offline');

    if (!isset($_SESSION['access_token'], $_SESSION['refresh_token'])) return false;

    $client->setAccessToken([
        'access_token' => $_SESSION['access_token'],
        'refresh_token' => $_SESSION['refresh_token']
    ]);

    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($_SESSION['refresh_token']);
        $_SESSION['access_token'] = $client->getAccessToken()['access_token'];
    }

    $service = new Google_Service_Gmail($client);

    $rawMessage = "From: Meta Shark <me@example.com>\r\n";
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
