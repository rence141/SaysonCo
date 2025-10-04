<?php
session_start();
include("db.php");
require_once "send_email.php";
 // Gmail API send_email function

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Validate password match
    if ($password !== $confirm_password) {
        die("<p style='color:red;'>Passwords do not match. <a href='signup_users.php'>Try again</a></p>");
    }

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        die("<p style='color:red;'>Email already registered. <a href='signup_users.php'>Try again</a></p>");
    }
    $checkEmail->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Generate verification token
    $token = bin2hex(random_bytes(32));

    // Insert user with verification_token and is_verified = 0
    $sql = "INSERT INTO users (fullname, email, phone, password, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $fullname, $email, $phone, $hashedPassword, $token);

    if ($stmt->execute()) {
        $verify_link = "http://yourdomain.com/php/verify_account.php?token=$token&email=" . urlencode($email);

        $subject = "Verify your Meta Shark Account";
        $body = "
            <h2>Welcome to Meta Shark!</h2>
            <p>Hi <b>$fullname</b>,</p>
            <p>Please verify your email by clicking the link below:</p>
            <p><a href='$verify_link'>Verify My Email</a></p>
            <br>
            <p>If you did not sign up, please ignore this email.</p>
        ";

        send_verification_email($email, $token);


        echo "<p style='color:green;'>Signup successful! Please check your email to verify your account.</p>";
    } else {
        die("<p style='color:red;'>Error: Could not register user. <a href='signup_users.php'>Try again</a></p>");
    }
}
?>
