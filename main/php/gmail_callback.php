<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'email.php';

// Handle the callback from Google OAuth
if (isset($_GET['code'])) {
    $redirect_uri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
                    $_SERVER['HTTP_HOST'] . 
                    dirname($_SERVER['PHP_SELF']) . 
                    '/gmail_callback.php';
    
    $success = handle_gmail_callback($_GET['code'], $redirect_uri);
    
    if ($success) {
        $_SESSION['gmail_authenticated'] = true;
        
        // Redirect based on where the user came from
        if (isset($_SESSION['auth_redirect'])) {
            $redirect = $_SESSION['auth_redirect'];
            unset($_SESSION['auth_redirect']);
            header("Location: $redirect");
        } else {
            // Default redirect to profile page
            header("Location: profile.php?gmail_connected=1");
        }
        exit;
    } else {
        // Authentication failed
        header("Location: profile.php?gmail_error=1");
        exit;
    }
} else {
    // No code provided
    header("Location: profile.php?gmail_error=2");
    exit;
}
?>