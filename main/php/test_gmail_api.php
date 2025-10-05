<?php
session_start();
require_once 'email.php';
require_once 'db.php';

// Set page title and header
$pageTitle = "Gmail API Test";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SaysonCo</title>
    <link rel="stylesheet" href="../../css/index.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #111;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .test-section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #333;
            border-radius: 5px;
        }
        .test-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #44D62C;
        }
        .test-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #44D62C;
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .test-button:hover {
            background-color: #3ab526;
        }
        .result-box {
            margin-top: 15px;
            padding: 10px;
            background-color: #222;
            border-radius: 5px;
            min-height: 100px;
        }
        .success {
            color: #44D62C;
        }
        .error {
            color: #ff3b30;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #333;
            background-color: #222;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1><?php echo $pageTitle; ?></h1>
        <p>Use this page to test Gmail API functionality</p>

        <?php
        // Process test actions
        $result = "";
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'test_auth':
                    $authUrl = get_gmail_auth_url();
                    $result = "<p>Authentication URL generated. <a href='$authUrl' target='_blank'>Click here to authenticate</a></p>";
                    break;
                    
                case 'test_send':
                    $to = $_POST['email'];
                    $subject = "Test Email from SaysonCo";
                    $body = "<h2>Test Email</h2><p>This is a test email sent from the Gmail API integration test.</p>";
                    
                    $sent = send_email($to, $subject, $body);
                    if ($sent) {
                        $result = "<p class='success'>Email sent successfully to $to!</p>";
                    } else {
                        $result = "<p class='error'>Failed to send email. Make sure you're authenticated with Gmail API.</p>";
                    }
                    break;
                    
                case 'test_scan':
                    $query = $_POST['query'] ?? 'label:inbox';
                    $emails = scan_emails($query, 5);
                    
                    if (is_array($emails) && count($emails) > 0) {
                        $result = "<p class='success'>Found " . count($emails) . " emails matching query: $query</p><ul>";
                        foreach ($emails as $email) {
                            $result .= "<li><strong>From:</strong> {$email['from']} <br><strong>Subject:</strong> {$email['subject']} <br><strong>Date:</strong> {$email['date']}</li>";
                        }
                        $result .= "</ul>";
                    } else {
                        $result = "<p class='error'>No emails found or error scanning emails. Make sure you're authenticated with Gmail API.</p>";
                    }
                    break;
            }
        }
        ?>

        <div class="test-section">
            <div class="test-title">1. Gmail API Authentication</div>
            <form method="post">
                <input type="hidden" name="action" value="test_auth">
                <button type="submit" class="test-button">Generate Auth URL</button>
            </form>
            <div class="result-box"><?php echo isset($result) && $_POST['action'] == 'test_auth' ? $result : ''; ?></div>
        </div>

        <div class="test-section">
            <div class="test-title">2. Send Test Email</div>
            <form method="post">
                <input type="hidden" name="action" value="test_send">
                <div class="form-group">
                    <label for="email">Recipient Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="test-button">Send Test Email</button>
            </form>
            <div class="result-box"><?php echo isset($result) && $_POST['action'] == 'test_send' ? $result : ''; ?></div>
        </div>

        <div class="test-section">
            <div class="test-title">3. Scan Emails</div>
            <form method="post">
                <input type="hidden" name="action" value="test_scan">
                <div class="form-group">
                    <label for="query">Search Query (e.g., label:inbox, from:example@gmail.com):</label>
                    <input type="text" id="query" name="query" value="label:inbox" required>
                </div>
                <button type="submit" class="test-button">Scan Emails</button>
            </form>
            <div class="result-box"><?php echo isset($result) && $_POST['action'] == 'test_scan' ? $result : ''; ?></div>
        </div>

        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>