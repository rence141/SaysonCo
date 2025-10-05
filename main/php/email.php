<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/aiConfig.php';

// Import Google API classes
use Google\Client as Google_Client;
use Google\Service\Gmail as Google_Service_Gmail;
use Google\Service\Gmail\Message as Google_Service_Gmail_Message;

/**
 * Send an email using Gmail API
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML supported)
 * @return bool True if email sent successfully, false otherwise
 */
function send_email($to, $subject, $body) {
    try {
        $client = new Google_Client();
        $client->setApplicationName('SaysonCo Email System');
        $client->setAuthConfig(__DIR__ . '/google_credentials.json');
        $client->addScope(Google_Service_Gmail::GMAIL_SEND);
        $client->addScope(Google_Service_Gmail::GMAIL_READONLY);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        
        // Check if we have tokens in session
        if (!isset($_SESSION['gmail_access_token'])) {
            // Redirect to authentication page if no token is available
            $auth_url = get_gmail_auth_url();
            $_SESSION['email_pending'] = [
                'to' => $to,
                'subject' => $subject,
                'body' => $body
            ];
            header('Location: ' . $auth_url);
            exit;
        } else {
            $client->setAccessToken($_SESSION['gmail_access_token']);
            
            // Refresh token if expired
            if ($client->isAccessTokenExpired()) {
                if (isset($_SESSION['gmail_refresh_token'])) {
                    $client->fetchAccessTokenWithRefreshToken($_SESSION['gmail_refresh_token']);
                    $_SESSION['gmail_access_token'] = $client->getAccessToken();
                } else {
                    // If no refresh token, we need to re-authenticate
                    return false;
                }
            }
        }
        
        $service = new Google_Service_Gmail($client);
        
        // Create the email
        $rawMessage = "From: SaysonCo <noreply@saysonco.com>\r\n";
        $rawMessage .= "To: <$to>\r\n";
        $rawMessage .= "Subject: $subject\r\n";
        $rawMessage .= "MIME-Version: 1.0\r\n";
        $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $rawMessage .= $body;
        
        $mime = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');
        $msg = new Google_Service_Gmail_Message();
        $msg->setRaw($mime);
        
        $service->users_messages->send('me', $msg);
        return true;
    } catch (Exception $e) {
        error_log("Gmail API Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Scan emails for a specific user
 * 
 * @param string $query Gmail search query
 * @param int $max_results Maximum number of emails to retrieve
 * @return array Array of email messages
 */
function scan_emails($query = '', $max_results = 10) {
    try {
        $client = new Google_Client();
        $client->setApplicationName('SaysonCo Email System');
        $client->setAuthConfig(__DIR__ . '/google_credentials.json');
        $client->addScope(Google_Service_Gmail::GMAIL_READONLY);
        $client->setAccessType('offline');
        
        // Check if we have tokens in session
        if (!isset($_SESSION['gmail_access_token'])) {
            return [];
        }
        
        $client->setAccessToken($_SESSION['gmail_access_token']);
        
        // Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            if (isset($_SESSION['gmail_refresh_token'])) {
                $client->fetchAccessTokenWithRefreshToken($_SESSION['gmail_refresh_token']);
                $_SESSION['gmail_access_token'] = $client->getAccessToken();
            } else {
                return [];
            }
        }
        
        $service = new Google_Service_Gmail($client);
        
        // List messages
        $opt_param = ['maxResults' => $max_results];
        if (!empty($query)) {
            $opt_param['q'] = $query;
        }
        
        $messages = [];
        $messageList = $service->users_messages->listUsersMessages('me', $opt_param);
        
        foreach ($messageList->getMessages() as $message) {
            $msg = $service->users_messages->get('me', $message->getId());
            $headers = $msg->getPayload()->getHeaders();
            
            $email = [
                'id' => $message->getId(),
                'subject' => '',
                'from' => '',
                'date' => '',
                'snippet' => $msg->getSnippet()
            ];
            
            foreach ($headers as $header) {
                if ($header->getName() == 'Subject') {
                    $email['subject'] = $header->getValue();
                } elseif ($header->getName() == 'From') {
                    $email['from'] = $header->getValue();
                } elseif ($header->getName() == 'Date') {
                    $email['date'] = $header->getValue();
                }
            }
            
            $messages[] = $email;
        }
        
        return $messages;
    } catch (Exception $e) {
        error_log("Gmail API Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get Gmail authentication URL
 * 
 * @param string $redirect_uri Redirect URI after authentication
 * @return string Authentication URL
 */
function get_gmail_auth_url($redirect_uri = null) {
    $client = new Google_Client();
    $client->setApplicationName('SaysonCo Email System');
    $client->setAuthConfig(__DIR__ . '/google_credentials.json');
    $client->setRedirectUri($redirect_uri ?: 'https://meta-shark.onrender.com/main/php/google_callback.php');
    $client->addScope(Google_Service_Gmail::GMAIL_SEND);
    $client->addScope(Google_Service_Gmail::GMAIL_READONLY);
    $client->setAccessType('offline');
    $client->setPrompt('consent');
    
    return $client->createAuthUrl();
}

/**
 * Handle Gmail authentication callback
 * 
 * @param string $code Authorization code from Google
 * @param string $redirect_uri Redirect URI used for authentication
 * @return bool True if authentication successful, false otherwise
 */
function handle_gmail_callback($code, $redirect_uri = null) {
    try {
        $client = new Google_Client();
        $client->setApplicationName('SaysonCo Email System');
        $client->setAuthConfig(__DIR__ . '/google_credentials.json');
        $client->setRedirectUri($redirect_uri ?: 'https://meta-shark.onrender.com/main/php/google_callback.php');
        
        $token = $client->fetchAccessTokenWithAuthCode($code);
        
        if (isset($token['access_token'])) {
            $_SESSION['gmail_access_token'] = $token['access_token'];
            
            if (isset($token['refresh_token'])) {
                $_SESSION['gmail_refresh_token'] = $token['refresh_token'];
            }
            
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Gmail API Error: " . $e->getMessage());
        return false;
    }
}