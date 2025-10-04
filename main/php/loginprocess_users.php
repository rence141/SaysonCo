<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include("db.php");
require_once 'email.php'; // Make sure the path is correct


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT id, fullname, email, password, role, is_verified FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            // Generate OTP
            $code = sprintf('%06d', random_int(0, 999999));
            $expiryAt = date('Y-m-d H:i:s', time() + 15*60); // 15 min

            $upd = $conn->prepare("UPDATE users SET verification_code = ?, verification_expires = ? WHERE id = ?");
            $upd->bind_param("ssi", $code, $expiryAt, $user['id']);
            $upd->execute();

            // Save in session for verification
            $_SESSION['pending_verification_user_id'] = $user['id'];
            $_SESSION['pending_verification_email'] = $user['email'];
            $_SESSION['pending_verification_role'] = $user['role'];

            // Send OTP email
            $subject = 'Your SaysonCo verification code';
            $body = "Hello,<br>Your verification code is: <b>$code</b><br>This code expires in 15 minutes.";
            send_email($user['email'], $subject, $body);

            header("Location: verify_account.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }

    if (!empty($error)) {
        header("Location: login_users.php?error=" . urlencode($error));
        exit();
    }
}
