<?php
session_start();
include("db.php");

// Check if there's a pending verification
$verification_type = isset($_GET['type']) ? $_GET['type'] : 'account';
$page_title = $verification_type == 'gmail' ? 'Gmail Verification' : ($verification_type == 'google' ? 'Google Login Verification' : 'Account Verification');

// For regular account verification
if ($verification_type == 'account' && !isset($_SESSION['pending_verification_user_id'])) {
    header("Location: login_users.php");
    exit();
}

// For Gmail verification
if ($verification_type == 'gmail' && !isset($_SESSION['gmail_verification_code'])) {
    header("Location: login_users.php");
    exit();
}

// For Google verification
if ($verification_type == 'google' && !isset($_SESSION['google_verification_code'])) {
    header("Location: login_users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = trim($_POST['otp']);
    
    // Handle Gmail verification
    if ($verification_type == 'gmail') {
        $stored_code = $_SESSION['gmail_verification_code'];
        $stored_time = $_SESSION['gmail_verification_time'];
        $current_time = time();
        $code_expiry = 10 * 60; // 10 minutes in seconds
        
        if ($current_time - $stored_time > $code_expiry) {
            $error = 'Verification code has expired. Please request a new code.';
        } elseif ($otp !== $stored_code) {
            $error = 'Invalid verification code. Please try again.';
        } else {
            // Code is valid, mark as verified
            $_SESSION['gmail_authenticated'] = true;
            
            // Clear verification code from session
            unset($_SESSION['gmail_verification_code']);
            unset($_SESSION['gmail_verification_time']);
            
            // Check if there's a pending email to send
            if (isset($_SESSION['email_pending'])) {
                $pending = $_SESSION['email_pending'];
                
                // Send the pending email
                require_once 'email.php';
                $sent = send_email($pending['to'], $pending['subject'], $pending['body']);
                
                // Clear the pending email
                unset($_SESSION['email_pending']);
                
                if ($sent) {
                    // Redirect to appropriate page based on the context
                    if (strpos($pending['subject'], 'Verification') !== false) {
                        header('Location: signup_success_users.php');
                        exit;
                    } else {
                        header('Location: index.php');
                        exit;
                    }
                } else {
                    $error = "Email sending failed after verification. Please try again.";
                }
            } else if (isset($_SESSION['auth_redirect'])) {
                $redirect = $_SESSION['auth_redirect'];
                unset($_SESSION['auth_redirect']);
                header("Location: $redirect");
                exit;
            } else {
                // Default redirect to profile page
                header("Location: profile.php?gmail_connected=1");
                exit;
            }
        }
    } elseif ($verification_type == 'google') {
        // Google login verification
        if (isset($_SESSION['google_verification_code']) && isset($_SESSION['google_verification_time'])) {
            $stored_code = $_SESSION['google_verification_code'];
            $code_time = $_SESSION['google_verification_time'];
            
            // Check if code is valid and not expired (10 minutes)
            if ($otp == $stored_code && (time() - $code_time) <= 600) {
                // Clear verification data
                unset($_SESSION['google_verification_code']);
                unset($_SESSION['google_verification_time']);
                
                // Proceed with Google login process
                session_write_close();
                header("Location: google_login_process.php");
                exit();
            } else {
                $error = "Invalid or expired verification code.";
            }
        } else {
            header("Location: login_users.php");
            exit();
        }
        // Regular account verification
        $userId = $_SESSION['pending_verification_user_id'];
        
        $sql = "SELECT verification_code, verification_expires FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $now = date('Y-m-d H:i:s');

        if ($user && $otp === $user['verification_code'] && $now <= $user['verification_expires']) {
            // Mark user as verified
            $upd = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL, verification_expires = NULL WHERE id = ?");
            $upd->bind_param("i", $userId);
            $upd->execute();

            // Clear session verification
            unset($_SESSION['pending_verification_user_id']);
            unset($_SESSION['pending_verification_email']);
            unset($_SESSION['pending_verification_role']);

            $success = "Account verified! You can now <a href='login_users.php'>login</a>.";
        } else {
            $error = "Invalid or expired verification code. <a href='verify_account.php'>Try again</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 18px;
            letter-spacing: 5px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background: #5cb85c;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn:hover {
            background: #4cae4c;
        }
        .error {
            color: #d9534f;
            margin-bottom: 15px;
        }
        .success {
            color: #5cb85c;
            margin-bottom: 15px;
        }
        .timer {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #777;
        }
        .resend {
            text-align: center;
            margin-top: 15px;
        }
        .resend a {
            color: #337ab7;
            text-decoration: none;
        }
        .resend a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php else: ?>
            <p>We've sent a verification code to your email address. Please enter the code below to verify your account.</p>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="otp">Verification Code</label>
                    <input type="text" id="otp" name="otp" maxlength="6" required autofocus>
                </div>
                
                <button type="submit" class="btn">Verify</button>
            </form>
            
            <div class="timer">
                <span id="countdown">10:00</span> remaining
            </div>
            
            <div class="resend">
                <a href="<?php echo $verification_type == 'gmail' ? 'gmail_auth.php' : 'verify_account.php'; ?>">Resend Code</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Countdown timer
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            var interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);
                
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;
                
                display.textContent = minutes + ":" + seconds;
                
                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "Expired";
                }
            }, 1000);
        }
        
        window.onload = function () {
            var tenMinutes = 60 * 10,
                display = document.querySelector('#countdown');
            startTimer(tenMinutes, display);
        };
    </script>
</body>
</html>
