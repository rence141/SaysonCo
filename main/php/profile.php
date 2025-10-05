<?php
session_start();
include("db.php");

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

// ✅ Fetch user info from actual table
$stmt = $conn->prepare("SELECT id, fullname, email, number, profile_image, user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found in database.");
}

// Default profile image
$default_profile_image = "uploads/default-avatar.svg";

// ✅ Handle image upload
if (!empty($_FILES["profile_image"]["name"])) {
    $targetDir = __DIR__ . "/uploads/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $fileName = $user_id . "_" . time() . "_" . basename($_FILES["profile_image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = ["jpg", "jpeg", "png", "svg"];

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
            // Delete old image if exists
            if (!empty($user["profile_image"]) && file_exists($targetDir . $user["profile_image"])) {
                unlink($targetDir . $user["profile_image"]);
            }

            // Update DB
            $stmt = $conn->prepare("UPDATE users SET profile_image=? WHERE id=?");
            $stmt->bind_param("si", $fileName, $user_id);
            $stmt->execute();

            $user["profile_image"] = $fileName;
            $success = "Profile image updated successfully.";
        } else {
            $error = "Error uploading profile image.";
        }
    } else {
        $error = "Only JPG, JPEG, PNG, and SVG files are allowed.";
    }
}

// ✅ Handle profile update (when not uploading image)
if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($_FILES["profile_image"]["name"])) {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = trim($_POST["password"]);

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, number=?, password=? WHERE id=?");
        $stmt->bind_param("ssssi", $fullname, $email, $phone, $hashed, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, number=? WHERE id=?");
        $stmt->bind_param("sssi", $fullname, $email, $phone, $user_id);
    }

    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
        $user["fullname"] = $fullname;
        $user["email"] = $email;
        $user["number"] = $phone;
    } else {
        $error = "Error updating profile.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($user["first_name"] . " " . $user["last_name"]); ?> - Profile</title>
    <link rel="stylesheet" href="fonts/fonts.css">
    <link rel="icon" type="image/png" href="uploads/logo1.png">
    <style>
        /* same CSS you already had */
    </style>
</head>
<body>
    <div class="navbar">
        <a href="shop.php" class="logo">Meta Shark</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="profile-container">
        <h1>Edit Profile</h1>

        <?php if (!empty($success)) echo "<div class='message success'>$success</div>"; ?>
        <?php if (!empty($error)) echo "<div class='message error'>$error</div>"; ?>

        <!-- Profile Picture -->
        <?php if (!empty($user["profile_image"]) && file_exists("uploads/" . $user["profile_image"])): ?>
            <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture">
        <?php else: ?>
            <img src="<?php echo htmlspecialchars($default_profile_image); ?>" alt="Default Profile Picture">
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_image" accept="image/*">
            
            <!-- User ID -->
            <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; margin: 10px 0;">
                <label>User ID:</label>
                <input type="text" value="<?php echo htmlspecialchars($user['id']); ?>" readonly>
                <small>This is your unique identifier</small>
            </div>
            
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['number']); ?>" required>
            <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
</body>
</html>
