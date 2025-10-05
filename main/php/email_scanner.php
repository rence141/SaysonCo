<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'email.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_users.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$search_query = isset($_GET['query']) ? $_GET['query'] : '';
$emails = [];
$message = '';

// Check if Gmail is authenticated
if (!isset($_SESSION['gmail_authenticated']) || $_SESSION['gmail_authenticated'] !== true) {
    $message = "You need to connect your Gmail account first.";
} else {
    // Scan emails
    $emails = scan_emails($search_query);
    
    if (empty($emails)) {
        $message = "No emails found matching your criteria.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Scanner - SaysonCo</title>
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="fonts/fonts.css">
    <link rel="icon" type="image/png" href="uploads/logo1.png">
    <?php include('theme_toggle.php'); ?>
    <style>
        .email-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background-color: var(--bg-secondary);
            border-radius: 10px;
        }
        
        .email-search {
            margin-bottom: 20px;
        }
        
        .email-search input {
            width: 70%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-input);
            color: var(--text-primary);
        }
        
        .email-search button {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .email-list {
            margin-top: 20px;
        }
        
        .email-item {
            padding: 15px;
            margin-bottom: 10px;
            background-color: var(--bg-primary);
            border-radius: 5px;
            border-left: 4px solid var(--primary-color);
        }
        
        .email-subject {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .email-from, .email-date {
            font-size: 0.9em;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }
        
        .email-snippet {
            margin-top: 10px;
        }
        
        .connect-gmail {
            text-align: center;
            margin: 50px 0;
        }
        
        .connect-gmail a {
            display: inline-block;
            padding: 12px 24px;
            background-color: #DB4437;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .no-emails {
            text-align: center;
            margin: 50px 0;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>Email Scanner</h1>
        
        <?php if (!isset($_SESSION['gmail_authenticated']) || $_SESSION['gmail_authenticated'] !== true): ?>
            <div class="connect-gmail">
                <p>Connect your Gmail account to scan emails</p>
                <a href="gmail_auth.php">Connect Gmail</a>
            </div>
        <?php else: ?>
            <div class="email-search">
                <form method="GET">
                    <input type="text" name="query" placeholder="Search emails..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="no-emails">
                    <p><?php echo $message; ?></p>
                </div>
            <?php endif; ?>
            
            <div class="email-list">
                <?php foreach ($emails as $email): ?>
                    <div class="email-item">
                        <div class="email-subject"><?php echo htmlspecialchars($email['subject']); ?></div>
                        <div class="email-from">From: <?php echo htmlspecialchars($email['from']); ?></div>
                        <div class="email-date">Date: <?php echo htmlspecialchars($email['date']); ?></div>
                        <div class="email-snippet"><?php echo htmlspecialchars($email['snippet']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>