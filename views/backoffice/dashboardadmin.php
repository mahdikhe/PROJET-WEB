<?php
session_start();

// Security checks
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../views/frontoffice/login.php");
    exit();
}

if ($_SESSION['is_admin'] != 1) {
    header("Location: dashboard.php");
    exit();
}

define('BASE_PATHadmin', dirname(__DIR__, 2));
require_once BASE_PATHadmin.'/models/User.php';

$userModel = new User();

// Get statistics
$totalUsers = $userModel->getTotalUsers();
$activeToday = $userModel->getActiveTodayCount();
$allUsers = $userModel->getAllUsersWithLastLogin();
$systemIssues = 0;

// Calculate average time spent (in minutes)
$averageTimeSpent = $userModel->getAverageTimeSpent();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/dashboardadminstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Additional styles for time display */
        .time-display {
            font-size: 1.2rem;
            font-weight: 600;
        }
        .time-unit {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .widget-icon.info {
            background-color: #17a2b8;
            color: white;
        }
    </style>
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
                    <li class="active"><a href="dashboardadmin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="allusers.php"><i class="fas fa-users"></i> User Management</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> System Settings</a></li>
                    <li><a href="#"><i class="fas fa-file-alt"></i> Reports</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="/projet web fr/controllers/logoutController.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <header class="admin-header">
                <h1>Admin Dashboard</h1>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Search...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </header>

            <!-- Dashboard Widgets -->
            <div class="dashboard-widgets">
                <div class="widget">
                    <div class="widget-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="widget-info">
                        <h3>Total Users</h3>
                        <p><?php echo $totalUsers; ?></p>
                    </div>
                </div>
                
                <div class="widget">
                    <div class="widget-icon success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="widget-info">
                        <h3>Active Today</h3>
                        <p><?php echo $activeToday; ?></p>
                    </div>
                </div>
                
                <!-- Average Time Spent Widget -->
                <div class="widget">
                    <div class="widget-icon info">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="widget-info">
                        <h3>Avg. Time Spent</h3>
                        <div class="time-display">
                            <?php echo $averageTimeSpent; ?> <span class="time-unit">mins</span>
                        </div>
                    </div>
                </div>
                
                <div class="widget">
                    <div class="widget-icon danger">
                        <i class="fas fa-bug"></i>
                    </div>
                    <div class="widget-info">
                        <h3>System Issues</h3>
                        <p><?php echo $systemIssues; ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Users Section -->
            <div class="recent-activity">
                <div class="section-header">
                    <h2>Recent Users</h2>
                    <a href="allusers.php" class="btn view-all-btn">View All</a>
                </div>
                
                <div class="users-list">
                    <?php foreach ($allUsers as $user): ?>
                        <div class="user-item">
                            <div class="user-icon">
                                <?php if (isset($_SESSION['profile_picture'])): ?>
                                  <img src="../../assets/uploads/profile_pictures/<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile Picture">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="user-details">
                                <p class="user-name"><?php echo htmlspecialchars($user['username']); ?></p>
                                <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                <p class="last-login">
                                    Last login: <?php echo $user['last_login'] ? date('M j, Y g:i a', strtotime($user['last_login'])) : 'Never'; ?>
                                </p>
                            </div>
                            <div class="user-admin-status">
                                <?php echo ($user['is_admin'] == 1) ? '<span class="admin-badge">Admin</span>' : '<span class="user-badge">User</span>'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>