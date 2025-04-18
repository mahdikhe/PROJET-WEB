<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

define('BASE_PATH', dirname(__DIR__, 2)); // Goes up 2 levels from views/frontoffice

// Include required files
require_once BASE_PATH.'/config/database.php';
require_once BASE_PATH.'/models/User.php';

$user = new User();
$userData = $user->getUserById($_SESSION['user_id']);

// Get first 2 letters of username
$initials = strtoupper(substr($userData['username'], 0, 2));


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Your App Name</title>
    <link rel="stylesheet" href="../../assets/css/dashboardstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        window.addEventListener('scroll', function () {
            if (window.scrollY > 10) {
                document.body.classList.add('scrolled');
            } else {
                document.body.classList.remove('scrolled');
            }
        });
    </style>
</head>
<body>
    <!-- New Top Navigation Sidebar -->
    <nav class="top-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-leaf"></i>
                <span>CityPulse</span>
            </div>
            <div class="nav-buttons">
                <a href="#" class="nav-btn">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="#" class="nav-btn">
                    <i class="fas fa-chart-line"></i>
                    <span>Posts</span>
                </a>
                <a href="#" class="nav-btn">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events</span>
                </a>
                <a href="#" class="nav-btn">
                    <i class="fas fa-envelope"></i>
                    <span>Forums</span>
                </a>
                <a href="#" class="nav-btn">
                    <i class="fas fa-cog"></i>
                    <span>Offre d'emploi</span>
                </a>
                <a href="#" class="nav-btn">
                    <i class="fas fa-question-circle"></i>
                    <span>Help</span>
                </a>
            </div>
            <div class="nav-user">
    <?php 
    // Initialize variables
    $imagePath = '';
    $showImage = false;
    
    if (!empty($userData['profile_picture'])) {
        // Try multiple possible path formats
        $possiblePaths = [
            '/assets/uploads/profile_pictures/' . basename($userData['profile_picture']),  // Absolute path from root
            '../assets/uploads/profile_pictures/' . basename($userData['profile_picture']), // Relative path
            $userData['profile_picture'] // Raw database value
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
             class="nav-profile-image" 
             alt="Profile Picture"
             onerror="this.style.display='none'; document.querySelector('.nav-user .profile-initials').style.display='flex'">
    <?php endif; ?>
    
    <div class="profile-initials" style="<?= $showImage ? 'display:none' : 'display:flex' ?>">
        <?= $initials ?>
    </div>
</div>
            
        </div>
    </nav>

    <!-- Existing Dashboard Content -->
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($userData['username']); ?>!</h1>
            <a href="/projet web fr/controllers/logoutController.php" class="btn btn-logout">Logout</a>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Profile Completion</h3>
                <div class="progress-bar">
                    <div class="progress" style="width: 75%;"></div>
                </div>
                <span>75%</span>
            </div>
            
            <div class="stat-card">
             <h3>Last Login</h3>
                <p>
                <?php 
            if (!empty($userData['last_login'])) {
                // Format the database timestamp nicely
                $lastLogin = new DateTime($userData['last_login']);
                echo $lastLogin->format('F j, Y g:i a');
            } else {
                echo 'First login!';
            }
        ?>
    </p>
</div>
        </div>
        
        <div class="dashboard-actions">
            <a href="profile.php" class="btn btn-primary">
                <i class="fas fa-user-edit"></i> EDIT PROFILE
            </a>
            <a href="accsettings.php" class="btn btn-outline">
                <i class="fas fa-cogs"></i> ACCOUNT SETTINGS
            </a>
        </div>
        
        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <ul class="activity-list">
                <li>Logged in successfully</li>
                <li>Updated profile picture</li>
                <li>Changed password</li>
            </ul>
        </div>
    </div>
</body>
</html>