<?php
include("db.php");

if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND verification_token = ? AND is_verified = 0 LIMIT 1");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();

        echo "✅ Email verified successfully! You can now <a href='login_users.php'>login</a>.";
    } else {
        echo "❌ Invalid or expired verification link.";
    }

    $stmt->close();
}
$conn->close();
?>
