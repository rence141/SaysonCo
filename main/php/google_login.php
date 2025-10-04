<?php
require __DIR__ . '/../../vendor/autoload.php';
session_start();

$client = new Google_Client();

// Use credentials from environment variable
$credentials_json = getenv('GOOGLE_CREDENTIALS');
if (!$credentials_json) {
    die("Google credentials not found in environment variables.");
}
$client->setAuthConfig(json_decode($credentials_json, true));

$client->setRedirectUri('https://your-render-domain.com/main/php/google_callback.php');
$client->addScope("email");
$client->addScope("profile");

// Redirect user to Google's OAuth consent screen
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
