<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/login.php");
    exit();
}

$baseDir = dirname(__DIR__, 2);
include 'C:\xampp\htdocs\projet web fr\config\database.php';
include 'C:\xampp\htdocs\projet web fr\models\User.php';

$user = new User();
$userData = $user->getUserById($_SESSION['user_id']);

$passwordError = '';
$profileError = '';
$error = '';
$successMessage = '';
$initials = !empty($userData['username']) ? strtoupper(substr($userData['username'], 0, 2)) : 'ME';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (!password_verify($currentPassword, $userData['password'])) {
        $passwordError = "Current password is incorrect";
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = "New passwords don't match";
    } elseif (strlen($newPassword) < 8) {
        $passwordError = "Password must be at least 8 characters";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($user->updatePassword($_SESSION['user_id'], $hashedPassword)) {
            $successMessage = "Password updated successfully!";
        } else {
            $passwordError = "Failed to update password. Please try again.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    if ($user->deleteUser($_SESSION['user_id'])) {
        session_destroy();
        header("Location: ../frontoffice/welcome.php?account_deleted=true");
        exit();
    } else {
        $error = "Failed to delete account. Please try again.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $uploadDir = $baseDir . '../assets/uploads/profile_pictures/';

   /* if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $profileError = "Failed to create upload directory.";
            error_log("Failed to create directory: " . $uploadDir);
        }
    }
*/
    $fileExtension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $fileName = uniqid('profile_', true) . '.' . $fileExtension;
    $targetFile = $uploadDir . $fileName;

    $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 2 * 1024 * 1024;

    if (!in_array($fileExtension, $validExtensions)) {
        $profileError = "Invalid file type. Only JPG, JPEG, PNG, GIF allowed.";
    } elseif ($_FILES['profile_picture']['size'] > $maxFileSize) {
        $profileError = "File too large. Max 2MB allowed.";
    } elseif (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
        $profileError = "Failed to move uploaded file.";
        error_log("Upload error: " . $_FILES['profile_picture']['error']);
    } else {
        $relativePath = '/projet web fr/assets/uploads/profile_pictures/' . $fileName;
        if ($user->updateProfilePicture($_SESSION['user_id'], $relativePath)) {
            $successMessage = "Profile picture updated successfully!";
            $userData = $user->getUserById($_SESSION['user_id']);
        } else {
            $profileError = "Failed to update database record.";
            unlink($targetFile);
        }
    }
}

$profilePic = !empty($userData['profile_picture']) ? '/' . ltrim($userData['profile_picture'], '/') : '';
$showImage = !empty($profilePic);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | Your App Name</title>
    <link rel="stylesheet" href="../../assets/css/accsettingstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-leaf"></i>
                <span>EcoApp</span>
            </div>
            <div class="nav-buttons">
                <a href="dashboard.php" class="nav-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
            </div>
            <div class="nav-user">
    <?php 
    // Initialisation de la variable image
    $imagePath = '';
    $showImage = false;
    
    if (!empty($userData['profile_picture'])) {
        // Recherche d'un chemin d'image valide
        $possiblePaths = [
            '/projet web fr/assets/uploads/profile_pictures/' . basename($userData['profile_picture']),
            '../assets/uploads/profile_pictures/' . basename($userData['profile_picture']),
            $userData['profile_picture']
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                $imagePath = $path;
                $showImage = true;
                break;
            }
        }
    }
    ?>
    
    <?php if ($showImage): ?>
        <img src="<?= htmlspecialchars($imagePath) ?>" 
             class="profile-picture" 
             alt="Profile Picture"
             onerror="this.style.display='none'; document.querySelector('.nav-user .profile-initials').style.display='flex'">
    <?php endif; ?>
    
    <div class="profile-initials" style="<?= $showImage ? 'display:none' : 'display:flex' ?>">
        <?= $initials ?>
    </div>
</div>

        </div>
    </nav>

    <div class="settings-container">
        <div class="settings-header">
            <h1><i class="fas fa-user-cog"></i> Account Settings</h1>
            <p>Manage your account preferences and security</p>
        </div>

        <?php if ($successMessage): ?>
        <div class="alert success">
            <?= htmlspecialchars($successMessage) ?>
        </div>
        <?php endif; ?>

        <div class="settings-card">
            <h2><i class="fas fa-user-shield"></i> Change Password</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <small>Must be at least 8 characters</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <?php if ($passwordError): ?>
                <div class="alert error">
                    <?= htmlspecialchars($passwordError) ?>
                </div>
                <?php endif; ?>
                <button type="submit" name="change_password" class="btn btn-primary">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </form>
        </div>

        <div class="profile-picture-container">
            <img src="<?= htmlspecialchars($profilePic ?: 'https://ui-avatars.com/api/?name=' . urlencode($userData['username']) . '&size=150&background=23683B&color=fff') ?>" 
                 class="profile-picture" alt="Profile Picture">
            <form class="profile-picture-form" method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" accept="image/*" required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Picture
                </button>
            </form>
        </div>

        <div class="settings-card danger-zone">
            <h2><i class="fas fa-exclamation-triangle"></i> Danger Zone</h2>
            <p>Permanently delete your account and all associated data.</p>
            <form method="POST" onsubmit="return confirm('Are you absolutely sure? This cannot be undone!');">
                <button type="submit" name="delete_account" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Delete My Account
                </button>
                <?php if (isset($error)): ?>
                <div class="alert error">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <script src="../assets/js/account-settings.js"></script>
</body>
</html>
