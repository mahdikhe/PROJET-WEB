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

require_once __DIR__.'/../../models/User.php';
$userModel = new User();
$users = $userModel->getUsersByCountry();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Map</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <!-- Your Styles -->
    <link rel="stylesheet" href="../../assets/css/dashboardadminstyle.css">
    <link rel="stylesheet" href="../../assets/css/mapstyle.css">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard-container">
        <!-- Sidebar -->
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
                    <li><a href="allusers.php"><i class="fas fa-users"></i> User Management</a></li>
                    <li class="active"><a href="map.php"><i class="fas fa-map-marked-alt"></i> User Map</a></li>
                    <li><a href="#"><i class="fas fa-file-alt"></i> Reports</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="/projet web fr/controllers/logoutController.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <div>
                    <a href="dashboardadmin.php" class="go-back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <h1>User Geographic Distribution</h1>
                </div>
            </header>

            <div class="map-container">
                <div id="userMap"></div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the map
        const map = L.map('userMap').setView([20, 0], 2);
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18
        }).addTo(map);
        
        // Add user markers if available
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <?php if (!empty($user['latitude']) && !empty($user['longitude'])): ?>
                    L.marker([<?= $user['latitude'] ?>, <?= $user['longitude'] ?>])
                        .addTo(map)
                        .bindPopup(`
                            <div style="min-width:200px">
                                <h3 style="margin:0 0 10px 0; color:#23683B">
                                    <img src="https://flagcdn.com/w20/<?= strtolower($user['country_code']) ?>.png" 
                                         style="vertical-align:middle; margin-right:5px" 
                                         alt="<?= htmlspecialchars($user['country_name']) ?>">
                                    <?= htmlspecialchars($user['country_name']) ?>
                                </h3>
                                <p style="margin:5px 0">
                                    <i class="fas fa-users" style="color:#23683B; width:20px"></i> 
                                    <strong><?= $user['user_count'] ?></strong> user(s)
                                </p>
                            </div>
                        `);
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>