<?php
// Get current page for active state
$current_page = $_GET['page'] ?? 'dashboard';
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">
        <h2>Admin Dashboard</h2>
    </div>
    
    <!-- User Management Section -->
    <div class="nav-section">
        <div class="nav-section-header">User Management</div>
        <ul class="nav-section-menu">
            <li>
                <a href="/test1/dashboardgol.php?page=users" class="<?php echo $current_page === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> All Users
                </a>
            </li>
            <li>
                <a href="/test1/dashboardgol.php?page=user_management" class="<?php echo $current_page === 'user_management' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i> User Management
                </a>
            </li>
            <li>
                <a href="/test1/dashboardgol.php?page=user_stats" class="<?php echo $current_page === 'user_stats' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> User Statistics
                </a>
            </li>
            <li>
                <a href="/test1/dashboardgol.php?page=user_map" class="<?php echo $current_page === 'user_map' ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt"></i> User Map
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Event Management Section -->
    <div class="nav-section">
        <div class="nav-section-header">Event Management</div>
        <ul class="nav-section-menu">
            <li>
                <a href="/test1/dashboardgol.php?page=dashboard" class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="/test1/dashboardgol.php?page=events" class="<?php echo $current_page === 'events' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> Events List
                </a>
            </li>
            <li>
                <a href="/test1/dashboardgol.php?page=new_event" class="<?php echo $current_page === 'new_event' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i> Create Event
                </a>
            </li>
            <li>
                <a href="/test1/dashboardgol.php?page=reservations" class="<?php echo $current_page === 'reservations' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i> View Reservations
                </a>
            </li>
            <li>
                <a href="/test1/event/view/frontoffice/events.php" target="_blank">
                    <i class="fas fa-globe"></i> Public Portal
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">A</div>
        <div class="user-details">
            <p class="user-name">Admin User</p>
            <p class="user-role">Administrator</p>
        </div>
    </div>
</div>

<!-- Mobile toggle button -->
<button class="sidebar-toggle">
    <i class="fas fa-bars"></i>
</button>

<script>
    // Sidebar toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        const container = document.querySelector('.container');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                container.classList.toggle('sidebar-active');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnToggle = sidebarToggle.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth <= 992) {
                    sidebar.classList.remove('active');
                    container.classList.remove('sidebar-active');
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('active');
                    container.classList.remove('sidebar-active');
                }
            });
        }
    });
</script> 