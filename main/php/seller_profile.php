
<?php
// Remove blank line at the beginning of the file to prevent headers already sent issue
session_start();
require_once("db.php");

// Check if user is logged in and is a seller
if (!isset($_SESSION["user_id"])) {
    header("Location: login_users.php");
    exit();
}

// Check if user has seller role
$user_role = $_SESSION['role'] ?? 'buyer';
if ($user_role !== 'seller' && $user_role !== 'admin') {
    header("Location: profile.php");
    exit();
}

// Get seller information
$seller_id = $_SESSION["user_id"];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();

// Get seller shop information
$sql = "SELECT * FROM seller_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$shop = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update seller profile
    if (isset($_POST["update_profile"])) {
        $shop_name = $_POST["shop_name"];
        $description = $_POST["description"];
        $phone = $_POST["phone"];
        $address = $_POST["address"];
        
        // Check if seller profile exists
        if ($shop) {
            // Update existing profile
            $sql = "UPDATE seller_profiles SET shop_name = ?, description = ?, phone = ?, address = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $shop_name, $description, $phone, $address, $seller_id);
        } else {
            // Create new profile
            $sql = "INSERT INTO seller_profiles (user_id, shop_name, description, phone, address) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issss", $seller_id, $shop_name, $description, $phone, $address);
        }
        
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh shop data
            $sql = "SELECT * FROM seller_profiles WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $seller_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $shop = $result->fetch_assoc();
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }
    
    // Handle profile image upload
    if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["profile_image"]["name"];
        $filetype = $_FILES["profile_image"]["type"];
        $filesize = $_FILES["profile_image"]["size"];
        
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $error_message = "Error: Please select a valid file format.";
        }
        
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $error_message = "Error: File size is larger than the allowed limit.";
        }
        
        // Verify MIME type of the file
        if (in_array($filetype, $allowed)) {
            // Check whether file exists before uploading it
            $target_dir = "uploads/seller_profiles/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $new_filename = uniqid() . "." . $ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                // Update database with new image path
                if ($shop) {
                    // Delete old image if exists
                    if (!empty($shop["profile_image"]) && file_exists($shop["profile_image"])) {
                        unlink($shop["profile_image"]);
                    }
                    
                    $sql = "UPDATE seller_profiles SET profile_image = ? WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $target_file, $seller_id);
                } else {
                    // Create new profile with image
                    $sql = "INSERT INTO seller_profiles (user_id, profile_image) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $seller_id, $target_file);
                }
                
                if ($stmt->execute()) {
                    $success_message = "Profile image updated successfully!";
                    // Refresh shop data
                    $sql = "SELECT * FROM seller_profiles WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $seller_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $shop = $result->fetch_assoc();
                } else {
                    $error_message = "Error updating profile image in database: " . $conn->error;
                }
            } else {
                $error_message = "Error uploading file.";
            }
        } else {
            $error_message = "Error: There was a problem with your upload. Please try again.";
        }
    }
    
    // Handle banner image upload
    if (isset($_FILES["banner_image"]) && $_FILES["banner_image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["banner_image"]["name"];
        $filetype = $_FILES["banner_image"]["type"];
        $filesize = $_FILES["banner_image"]["size"];
        
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $error_message = "Error: Please select a valid file format for banner.";
        }
        
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $error_message = "Error: Banner file size is larger than the allowed limit.";
        }
        
        // Verify MIME type of the file
        if (in_array($filetype, $allowed)) {
            // Check whether file exists before uploading it
            $target_dir = "uploads/seller_banners/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $new_filename = uniqid() . "." . $ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_file)) {
                // Update database with new image path
                if ($shop) {
                    // Delete old image if exists
                    if (!empty($shop["banner_image"]) && file_exists($shop["banner_image"])) {
                        unlink($shop["banner_image"]);
                    }
                    
                    $sql = "UPDATE seller_profiles SET banner_image = ? WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $target_file, $seller_id);
                } else {
                    // Create new profile with image
                    $sql = "INSERT INTO seller_profiles (user_id, banner_image) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $seller_id, $target_file);
                }
                
                if ($stmt->execute()) {
                    $success_message = "Banner image updated successfully!";
                    // Refresh shop data
                    $sql = "SELECT * FROM seller_profiles WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $seller_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $shop = $result->fetch_assoc();
                } else {
                    $error_message = "Error updating banner image in database: " . $conn->error;
                }
            } else {
                $error_message = "Error uploading banner file.";
            }
        } else {
            $error_message = "Error: There was a problem with your banner upload. Please try again.";
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Profile</title>
    <link rel="stylesheet" href="../../css/seller_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Seller Dashboard</h1>
            <nav>
                <ul>
                    <li><a href="seller_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="seller_profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Add Product</a></li>
                    <li><a href="seller_shop.php"><i class="fas fa-store"></i> My Shop</a></li>
                    <li><a href="seller_vouchers.php"><i class="fas fa-ticket-alt"></i> Vouchers</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="index.php"><i class="fas fa-home"></i> Main Site</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section class="profile-section">
                <h2>Seller Profile</h2>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="profile-container">
                    <div class="profile-header">
                        <div class="banner-container">
                            <?php if (isset($shop["banner_image"]) && !empty($shop["banner_image"])): ?>
                                <img src="<?php echo $shop["banner_image"]; ?>" alt="Shop Banner" class="banner-image">
                            <?php else: ?>
                                <div class="default-banner">
                                    <p>Upload a banner image to customize your shop</p>
                                </div>
                            <?php endif; ?>
                            
                            <form action="" method="post" enctype="multipart/form-data" class="banner-form">
                                <label for="banner_image" class="upload-banner-btn">
                                    <i class="fas fa-camera"></i> Change Banner
                                </label>
                                <input type="file" name="banner_image" id="banner_image" accept="image/*" style="display: none;">
                                <button type="submit" id="submit_banner" style="display: none;">Upload</button>
                            </form>
                        </div>
                        
                        <div class="profile-image-container">
                            <?php if (isset($shop["profile_image"]) && !empty($shop["profile_image"])): ?>
                                <img src="<?php echo $shop["profile_image"]; ?>" alt="Profile Image" class="profile-image">
                            <?php else: ?>
                                <div class="default-profile">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            
                            <form action="" method="post" enctype="multipart/form-data" class="profile-image-form">
                                <label for="profile_image" class="upload-profile-btn">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" name="profile_image" id="profile_image" accept="image/*" style="display: none;">
                                <button type="submit" id="submit_profile" style="display: none;">Upload</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="profile-details">
                        <form action="" method="post" class="profile-form">
                            <div class="form-group">
                                <label for="shop_name">Shop Name</label>
                                <input type="text" id="shop_name" name="shop_name" value="<?php echo isset($shop["shop_name"]) ? $shop["shop_name"] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Shop Description</label>
                                <textarea id="description" name="description" rows="4"><?php echo isset($shop["description"]) ? $shop["description"] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Contact Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo isset($shop["phone"]) ? $shop["phone"] : $seller["phone"]; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Business Address</label>
                                <textarea id="address" name="address" rows="3"><?php echo isset($shop["address"]) ? $shop["address"] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" value="<?php echo $seller["email"]; ?>" disabled>
                                <small>Email cannot be changed. This is your account email.</small>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
    
    <script>
        // Auto-submit forms when file is selected
        document.getElementById('profile_image').addEventListener('change', function() {
            document.getElementById('submit_profile').click();
        });
        
        document.getElementById('banner_image').addEventListener('change', function() {
            document.getElementById('submit_banner').click();
        });
    </script>
</body>
</html>
