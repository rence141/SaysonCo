<?php
session_start();
if (!isset($_SESSION['pending_verification_user_id'])) {
    header("Location: login_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Account</title>
</head>
<body>
<h2>Enter Verification Code</h2>
<form action="confirm_otp.php" method="POST">
    <input type="text" name="otp" placeholder="6-digit code" required>
    <button type="submit">Verify</button>
</form>
</body>
</html>
