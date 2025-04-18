<?php
session_start();

// Security checks
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../frontoffice/login.php");
    exit();
}

if ($_SESSION['is_admin'] != 1) {
    header("Location: ../frontoffice/dashboard.php");
    exit();
}

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH.'/models/User.php';

$userModel = new User();

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user data
$userToEdit = $userModel->getUserById($userId);

if (!$userToEdit) {
    $_SESSION['error_message'] = "User not found";
    header("Location: allusers.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
    $password = trim($_POST['password'] ?? '');
    
    // Basic validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } elseif ($userModel->isEmailTaken($email, $userId)) {
        $errors[] = "Email is already in use by another account";
    }
    
    // Handle profile picture upload
    $profilePicture = $userToEdit['profile_picture'];
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = BASE_PATH.'/assets/uploads/profile_pictures/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($fileInfo, $_FILES['profile_picture']['tmp_name']);
        
        if (in_array($detectedType, $allowedTypes)) {
            // Delete old profile picture if it exists and isn't the default
            if ($profilePicture && !str_contains($profilePicture, 'default')) {
                @unlink($uploadDir . $profilePicture);
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $newFilename = uniqid('profile_') . '.' . $extension;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadDir . $newFilename)) {
                $profilePicture = $newFilename;
            }
        } else {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    }
    
    // If no errors, update user
    if (empty($errors)) {
        $updateData = [
            'username' => $username,
            'email' => $email,
            'is_admin' => $isAdmin,
            'profile_picture' => $profilePicture
        ];
        
        // Only update password if provided
        if (!empty($password)) {
            $updateData['password'] = $password;
        }
        
        if ($userModel->updateUser($userId, $updateData)) {
            $_SESSION['success_message'] = "User updated successfully";
            
            // If admin is editing their own profile, update session data
            if ($userId == $_SESSION['user_id']) {
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['profile_picture'] = $profilePicture;
                $_SESSION['is_admin'] = $isAdmin;
            }
            
            header("Location: allusers.php");
            exit();
        } else {
            $errors[] = "Failed to update user";
        }
    }
    
    // If we got here, there were errors
    $_SESSION['error_message'] = implode("<br>", $errors);
}

// Include header and navigation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/dashboardadminstyle.css">
    <link rel="stylesheet" href="../../assets/css/edituserstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="admin-profile">
                <div class="profile-pic">
                    <?php if (isset($_SESSION['profile_picture'])): ?>
                        <img src="../../assets/uploads/profile_pictures/<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                <p>Administrator</p>
            </div>
            
            <nav class="admin-nav">
                <ul>
                    <li><a href="dashboardadmin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="allusers.php"><i class="fas fa-users"></i> User Management</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> System Settings</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="/projet web fr/controllers/logoutController.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <header class="admin-header">
                <h1>Edit User</h1>
                <div class="header-actions">
                    <a href="allusers.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Users</a>
                </div>
            </header>
            
            <!-- Display error messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert error">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Edit User Form -->
            <div class="edit-user-form">
                <form method="POST" action="edituser.php?id=<?php echo $userId; ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userToEdit['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userToEdit['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password">
                    </div>
                    
                    <div class="form-group">
                        <label for="is_admin">Role</label>
                        <div class="checkbox-container">
                            <input type="checkbox" id="is_admin" name="is_admin" value="1" <?php echo $userToEdit['is_admin'] ? 'checked' : ''; ?>>
                            <label for="is_admin">Administrator</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Current Profile Picture</label>
                        <div class="current-profile-pic">
                            <?php if ($userToEdit['profile_picture']): ?>
                                <img src="../../assets/uploads/profile_pictures/<?php echo htmlspecialchars($userToEdit['profile_picture']); ?>" alt="Current Profile Picture">
                            <?php else: ?>
                                <i class="fas fa-user-circle no-pic"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_picture">Change Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="save-btn"><i class="fas fa-save"></i> Save Changes</button>
                        <a href="allusers.php" class="cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>