<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'email.php';

// Redirect URI should be the URL to gmail_callback.php
$redirect_uri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
                $_SERVER['HTTP_HOST'] . 
                dirname($_SERVER['PHP_SELF']) . 
                '/gmail_callback.php';

// Get the authentication URL
$auth_url = get_gmail_auth_url($redirect_uri);

// Redirect to Google's OAuth page
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit;
?>