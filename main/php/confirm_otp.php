<?php
session_start();
include("db.php");

if (!isset($_SESSION['pending_verification_user_id'])) {
    header("Location: login_users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['pending_verification_user_id'];
    $otp = trim($_POST['otp']);

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

        echo "Account verified! You can now <a href='login_users.php'>login</a>.";
    } else {
        echo "Invalid or expired verification code. <a href='verify_account.php'>Try again</a>.";
    }
}
