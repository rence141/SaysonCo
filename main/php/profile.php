<?php
// Handle image upload
if (!empty($_FILES["profile_image"]["name"])) {
    // Absolute path inside container
    $targetDir = __DIR__ . "/uploads/";

    // Ensure directory exists and is writable
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            error_log("Failed to create uploads directory: $targetDir");
            $error = "Server error: cannot create upload directory.";
        }
    }

    if (empty($error)) {
        $fileName = $user_id . "_" . time() . "_" . basename($_FILES["profile_image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowedTypes = ["jpg", "jpeg", "png"];
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
                $profile_image = $fileName;

                // Delete old profile image if exists
                $currentImageQuery = "SELECT profile_image FROM users WHERE id = ?";
                $currentImageStmt = $conn->prepare($currentImageQuery);
                $currentImageStmt->bind_param("i", $user_id);
                $currentImageStmt->execute();
                $currentImageResult = $currentImageStmt->get_result();
                $currentImage = $currentImageResult->fetch_assoc()['profile_image'] ?? null;

                if ($currentImage && file_exists($targetDir . $currentImage)) {
                    unlink($targetDir . $currentImage);
                }
            } else {
                $error = "Error uploading profile image.";
            }
        } else {
            $error = "Only JPG, JPEG, and PNG files are allowed.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($user["fullname"]); ?> - Profile</title>
    <link rel="stylesheet" href="fonts/fonts.css">
    <link rel="icon" type="image/png" href="uploads/logo1.png">
    <style>
    
        body {
            font-family: Arial, sans-serif;
            background-image: url('uploads/');
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #333;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.2);
        }

        .profile-container img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #000000ff;
        }

        form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            background: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background: #0056b3;
        }

        .message {
            margin: 15px 0;
            padding: 12px;
            border-radius: 8px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body class="background">
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
            <img src="main/php/Uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture">
        <?php else: ?>
            <img src="<?php echo htmlspecialchars($default_profile_image); ?>" alt="Default Profile Picture">
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_image" accept="image/*">
            
            <!-- User ID Display (Read-only) -->
            <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; margin: 10px 0; border: 1px solid #dee2e6;">
                <label style="display: block; font-weight: bold; color: #495057; margin-bottom: 5px;">User ID:</label>
                <input type="text" value="<?php echo htmlspecialchars($user['id']); ?>" readonly 
                       style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; background: #e9ecef; color: #6c757d; font-family: monospace;">
                <small style="color: #6c757d; font-size: 0.8rem;">This is your unique identifier</small>
            </div>
            
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
</body>
</html>