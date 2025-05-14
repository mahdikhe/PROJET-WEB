<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin


require_once 'user/config.php';
require_once 'event/model/model.php';
require_once 'event/controller/conttroler.php';

try {
    $eventController = new EventController();
    $page = $_GET['page'] ?? 'dashboard';
    
    // Handle form submission for new event
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'create_event') {
            $eventData = [
                'title' => $_POST['eventTitle'] ?? '',
                'type' => $_POST['eventType'] ?? '',
                'description' => $_POST['description'] ?? '',
                'start_date' => $_POST['startDate'] ?? '',
                'start_time' => $_POST['startTime'] ?? '',
                'end_date' => $_POST['endDate'] ?? '',
                'end_time' => $_POST['endTime'] ?? '',
                'format' => $_POST['eventFormat'] ?? '',
                'location' => $_POST['location'] ?? '',
                'online_url' => $_POST['onlineUrl'] ?? '',
                'capacity' => $_POST['capacity'] ?? 0,
                'ticket_type' => $_POST['ticketType'] ?? '',
                'price' => $_POST['price'] ?? 0
            ];
            
            $result = $eventController->addEvent(new Event(
                $eventData['title'],
                $eventData['type'],
                $eventData['description'],
                $eventData['start_date'],
                $eventData['start_time'],
                $eventData['end_date'],
                $eventData['end_time'],
                $eventData['format'],
                $eventData['location'],
                $eventData['online_url'],
                (int)$eventData['capacity'],
                $eventData['ticket_type'],
                (float)$eventData['price']
            ));
            
            if ($result) {
                $_SESSION['success'] = 'Event created successfully';
                header('Location: dashboardgol.php?page=events');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to create event';
                header('Location: dashboardgol.php?page=new_event');
                exit;
            }
        }
    }

    // Get dashboard statistics
    $totalEvents = $eventController->getTotalEvents();
    $upcomingEvents = $eventController->getUpcomingEvents();
    $totalReservations = $eventController->getTotalReservations();
    $totalRevenue = $eventController->getTotalRevenue();
    
    // Get all events for charts and lists
    $events = $eventController->listEvents();
    
    // Initialize counters
    $onlineCount = 0;
    $offlineCount = 0;
    $reservationsByEvent = [];
    $eventNames = [];
    $eventReservationCounts = [];
    
    if ($events) {
        foreach ($events as $eventData) {
            if ($eventData['event_format'] === 'online') {
                $onlineCount++;
            } else {
                $offlineCount++;
            }
            
            $eventId = $eventData['event_id'];
            $eventReservations = $eventController->getReservationsByEvent($eventId);
            $reservationsByEvent[$eventId] = $eventReservations;
            
            $eventNames[] = $eventData['event_title'];
            $eventReservationCounts[] = count($eventReservations);
        }
    }

    // Store messages in session if they exist
    if (isset($_GET['success'])) {
        $_SESSION['success'] = $_GET['success'];
    }
    if (isset($_GET['error'])) {
        $_SESSION['error'] = $_GET['error'];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Required dependencies -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="event/view/backoffice/ui.css">
    <style>
        /* ... existing styles from both dashboards ... */
        
        /* Additional styles for unified dashboard */
        .dashboard-section {
            margin-bottom: 32px;
        }
        
        .dashboard-section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-light);
        }
        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 24px;
        }
        
        .stat-icon.users { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .stat-icon.events { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .stat-icon.reservations { background: rgba(155, 89, 182, 0.1); color: #9b59b6; }
        .stat-icon.revenue { background: rgba(241, 196, 15, 0.1); color: #f1c40f; }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-medium);
        }
        
        .nav-section {
            margin-bottom: 24px;
        }
        
        .nav-section-header {
            padding: 12px 24px;
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-section-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-section-menu li {
            margin-bottom: 4px;
        }
        
        .nav-section-menu a {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.95rem;
        }
        
        .nav-section-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding-left: 28px;
        }
        
        .nav-section-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border-left: 4px solid #fff;
        }
        
        .nav-section-menu a i {
            width: 20px;
            margin-right: 12px;
            font-size: 1rem;
            text-align: center;
        }
        
        /* Icon colors for different sections */
        .nav-section-menu a i.fa-users { color: #3498db; }
        .nav-section-menu a i.fa-user-cog { color: #e74c3c; }
        .nav-section-menu a i.fa-chart-bar { color: #2ecc71; }
        .nav-section-menu a i.fa-map-marker-alt { color: #f1c40f; }
        .nav-section-menu a i.fa-calendar-check { color: #9b59b6; }
        .nav-section-menu a i.fa-plus-circle { color: #1abc9c; }
        .nav-section-menu a i.fa-ticket-alt { color: #e67e22; }
        .nav-section-menu a i.fa-globe { color: #34495e; }
    </style>
</head>
<body>
    <?php include_once 'user/views/backoffice/partials/sidebar.php'; ?>

    <!-- Main content container -->
    <div class="container">
        <?php 
        // Display session messages
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <p>' . htmlspecialchars($_SESSION['success']) . '</p>
                  </div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>' . htmlspecialchars($_SESSION['error']) . '</p>
                  </div>';
            unset($_SESSION['error']);
        }

        if ($page === 'dashboard'): 
            // Include dashboard content
            include 'user/views/backoffice/partials/dashboardadmin_content.php';
        elseif ($page === 'events'): 
            // Include events list content
            include 'event/view/backoffice/read.php';
        elseif ($page === 'new_event'): 
            // Include new event form
            include 'event/view/backoffice/newEvent.php';
        elseif ($page === 'reservations'): 
            // Include reservations content
            include 'event/view/backoffice/read.php';
        elseif ($page === 'users'): 
            // Include users list content
            include 'user/views/backoffice/partials/allusers_content.php';
        elseif ($page === 'user_management'): 
            // Include user management content
            include 'user/views/backoffice/partials/edituser_content.php';
        elseif ($page === 'user_stats'): 
            // Include user statistics content
            include 'user/views/backoffice/partials/stats_content.php';
        elseif ($page === 'user_map'): 
            // Include user map content
            include 'user/views/backoffice/partials/map_content.php';
        endif; 
        ?>
    </div>

    <script>
        // Form validation and field toggling
        function toggleLocationFields() {
            const eventFormat = document.getElementById('eventFormat');
            if (eventFormat) {
                const locationGroup = document.getElementById('locationGroup');
                const onlineUrlGroup = document.getElementById('onlineUrlGroup');
                const locationInput = document.getElementById('location');
                const onlineUrlInput = document.getElementById('onlineUrl');
                
                if (eventFormat.value === 'online') {
                    locationGroup.style.display = 'none';
                    onlineUrlGroup.style.display = 'block';
                    locationInput.required = false;
                    onlineUrlInput.required = true;
                } else if (eventFormat.value === 'inPerson') {
                    locationGroup.style.display = 'block';
                    onlineUrlGroup.style.display = 'none';
                    locationInput.required = true;
                    onlineUrlInput.required = false;
                }
            }
        }
        
        function togglePriceField() {
            const ticketType = document.getElementById('ticketType');
            if (ticketType) {
                const priceGroup = document.getElementById('priceGroup');
                const priceInput = document.getElementById('price');
                
                if (ticketType.value === 'paid') {
                    priceGroup.style.display = 'block';
                    priceInput.required = true;
                } else {
                    priceGroup.style.display = 'none';
                    priceInput.required = false;
                    priceInput.value = 0;
                }
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('create-event-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const startDate = new Date(document.getElementById('startDate').value + ' ' + document.getElementById('startTime').value);
                    const endDate = new Date(document.getElementById('endDate').value + ' ' + document.getElementById('endTime').value);
                    
                    if (endDate <= startDate) {
                        e.preventDefault();
                        alert('End date and time must be after start date and time');
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php
} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    header('Location: dashboardgol.php');
    exit;
}
?>
