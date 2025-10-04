<?php
require __DIR__ . '/../../vendor/autoload.php';
include("db.php");
session_start();

$client = new Google_Client();
$client->setClientId(getenv('GOOGLE_CLIENT_ID'));
$client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
$client->setRedirectUri(getenv('GOOGLE_REDIRECT_URI'));
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        error_log("Google OAuth error: " . $token['error']);
        header("Location: ../../login_users.php?error=Google login failed");
        exit;
    }

    $client->setAccessToken($token);
    $oauth = new Google_Service_Oauth2($client);
    $googleUser = $oauth->userinfo->get();

    $_SESSION['google_email'] = $googleUser->email;
    $_SESSION['google_name'] = $googleUser->name;

    session_write_close();
    header("Location: https://meta-shark.onrender.com/main/php/google_login_process.php");
    exit;

} else {
    error_log("Google OAuth code not provided");
    header("Location: ../../login_users.php?error=Google login failed");
    exit;
}
?>
