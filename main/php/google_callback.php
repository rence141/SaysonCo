<?php
require __DIR__ . '/../../vendor/autoload.php';
include("db.php");
session_start();

// Import the required Google API classes
use Google\Client;
use Google\Service\Oauth2;
use Google\Service\Oauth2\Userinfo;

// Handle verification error (access_denied)
if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
    // Store the email if available
    $userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : 'LORENZEZZ0987@gmail.com';
    
    // Display a user-friendly error page
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Google Verification Required</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
            .error-container { background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin-top: 20px; }
            h1 { color: #d9534f; }
            .btn { display: inline-block; background: #5cb85c; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
            .steps { background-color: #eaeaea; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Google Verification Required</h1>
            <p>This application is currently in testing mode and has not completed the Google verification process.</p>
            <p>Your email <strong>' . htmlspecialchars($userEmail) . '</strong> needs to be added as a test user in Google Cloud Console.</p>
            
            <div class="steps">
                <h3>How to fix this:</h3>
                <p>The application administrator needs to add your email as a test user in the Google Cloud Console.</p>
                <p>Please contact the administrator and provide them with your email address.</p>
            </div>
            
            <p><a href="login_users.php" class="btn">Return to Login</a></p>
            <p><a href="google_oauth_guide.md" target="_blank">View Detailed Instructions</a></p>
        </div>
    </body>
    </html>';
    exit;
}

// Use the new namespace for Google Client
$client = new Client();
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
    // Use the new namespace for Google Service Oauth2
    $oauth = new Oauth2($client);
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
