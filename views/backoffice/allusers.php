<?php
session_start();

// Security checks
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['is_admin'] != 1) {
    header("Location: dashboard.php");
    exit();
}

define('BASE_PATHadmin', dirname(__DIR__, 2));
require_once BASE_PATHadmin.'/models/User.php';

// Handle delete action
if (isset($_GET['delete_id'])) {
    $user = new User();
    $deleteId = (int)$_GET['delete_id'];
    
    // Prevent self-deletion
    if ($deleteId != $_SESSION['user_id']) {
        if ($user->deleteUser($deleteId)) {
            $_SESSION['success_message'] = "User deleted successfully";
        } else {
            $_SESSION['error_message'] = "Failed to delete user";
        }
    } else {
        $_SESSION['error_message'] = "You cannot delete yourself";
    }
    
    header("Location: allusers.php");
    exit();
}

// Handle search and get users
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$userModel = new User();
$allUsers = $userModel->getAllUsersWithLastLogin($searchQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/dashboardadminstyle.css">
    <link rel="stylesheet" href="../../assets/css/allusersstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="admin-profile">
                <div class="profile-pic">
                    <?php if (isset($_SESSION['profile_picture'])): ?>
                        <img src="../assets/uploads/profile_pictures/<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
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
            <!-- Display success/error messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert success">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            <header class="admin-header">
                <h1>User Management</h1>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
                    </div>
                </div>
            </header>
            
            
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert error">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

            <!-- Search Bar -->
            <div class="search-container">
                <form method="GET" action="allusers.php">
                    <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>

            <!-- Users Table -->
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allUsers as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['is_admin'] == 1): ?>
                                    <span class="admin-badge">Admin</span>
                                <?php else: ?>
                                    <span class="user-badge">User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['last_login']): ?>
                                    <?php echo date('M j, Y g:i a', strtotime($user['last_login'])); ?>
                                <?php else: ?>
                                    Never logged in
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edituser.php?id=<?php echo $user['id']; ?>" class="action-btn edit"><i class="fas fa-edit"></i></a>
                                <a href="allusers.php?delete_id=<?php echo $user['id']; ?>" class="action-btn delete" 
                                   onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($user['username']); ?>? This action cannot be undone.')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination would go here -->
            <!-- <div class="pagination">
                <a href="#">&laquo;</a>
                <a href="#" class="current">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">&raquo;</a>
            </div> -->
        </div>
    </div>
</body>
</html>