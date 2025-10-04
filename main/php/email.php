<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

function send_email($to, $subject, $body) {
    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/google_credentials.json');
    $client->addScope(Google_Service_Gmail::GMAIL_SEND);
    $client->setAccessType('offline');

    // Load tokens
    $tokenPath = __DIR__ . '/gmail_tokens.json';
    if (!file_exists($tokenPath)) {
        error_log("No token file found. Run google_login.php first.");
        return false;
    }
    $token = json_decode(file_get_contents($tokenPath), true);
    $client->setAccessToken($token);

    // Refresh if expired
    if ($client->isAccessTokenExpired()) {
        if (isset($token['refresh_token'])) {
            $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        } else {
            error_log("Refresh token missing. Re-authorize the app.");
            return false;
        }
    }

    $service = new Google_Service_Gmail($client);

    $rawMessage = "From: Meta Shark <metshark@example.com>\r\n";
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
