<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'email.php';

// Handle the callback from Google OAuth
if (isset($_GET['code'])) {
    $redirect_uri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
                    $_SERVER['HTTP_HOST'] . 
                    dirname($_SERVER['PHP_SELF']) . 
                    '/google_callback.php';
    
    $success = handle_gmail_callback($_GET['code'], $redirect_uri);
    
    if ($success) {
        $_SESSION['gmail_authenticated'] = true;
        
        // Check if there's a pending email to send
        if (isset($_SESSION['email_pending'])) {
            $pending = $_SESSION['email_pending'];
            
            // Send the pending email
            $sent = send_email($pending['to'], $pending['subject'], $pending['body']);
            
            // Clear the pending email
            unset($_SESSION['email_pending']);
            
            if ($sent) {
                // Redirect to appropriate page based on the context
                if (strpos($pending['subject'], 'Verification') !== false) {
                    header('Location: signup_success_users.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                echo "Email sending failed after authentication. Please try again.";
            }
        } else if (isset($_SESSION['auth_redirect'])) {
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