<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session
echo "<!-- Session Debug:";
echo "\nSession Status: " . session_status();
echo "\nSession ID: " . session_id();
echo "\nSession Variables:";
print_r($_SESSION);
echo "\n-->";

// Add this function at the top of the file, after the includes
function canModifyReservation($eventDate, $eventTime) {
    $eventDateTime = new DateTime($eventDate . ' ' . $eventTime);
    $currentDateTime = new DateTime();
    $interval = $currentDateTime->diff($eventDateTime);
    return $interval->days >= 1 || ($interval->days == 0 && $interval->h >= 24);
}

try {
    $eventController = new EventController();
    
    // Debug database connection
    echo "<!-- Database Debug:";
    try {
        $pdo = Config::getConnexion();
        echo "\nDatabase Connection: Successful";
        
        // Test query to check reservations table
        $testQuery = "SELECT COUNT(*) as count FROM reservations";
        $stmt = $pdo->query($testQuery);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nTotal Reservations in Database: " . $result['count'];
        
        // Test query to check events table
        $testQuery = "SELECT COUNT(*) as count FROM events";
        $stmt = $pdo->query($testQuery);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nTotal Events in Database: " . $result['count'];
    } catch (PDOException $e) {
        echo "\nDatabase Connection Error: " . $e->getMessage();
    }
    echo "\n-->";
    
    // Get all events
    $events = $eventController->listEvents();
    
    // Check if email is provided for filtering reservations
    $userEmail = isset($_GET['email']) ? $_GET['email'] : '';
    
    // Get user's reservations if email is available
    $userReservations = [];
    if (!empty($userEmail)) {
        $userReservations = $eventController->getReservationsByEmail($userEmail);
    }
    
    // Get all reservations for display
    $allReservations = [];
    foreach ($events as $event) {
        $eventId = $event['event_id'];
        $reservations = $eventController->getReservationsByEvent($eventId);
        if (!empty($reservations)) {
            $allReservations[$eventId] = count($reservations);
        } else {
            $allReservations[$eventId] = 0;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Events Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../stylse.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- QRCode.js library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <!-- Load functions.js first -->
    <script src="../functions.js"></script>
    <!-- Load ai-generator.js after functions.js -->
    <script src="../ai-generator.js"></script>
    <script>
        // Test if QRCode library is loaded
        window.addEventListener('load', function() {
            console.log('QRCode library loaded:', typeof QRCode !== 'undefined');
            if (typeof QRCode !== 'undefined') {
                console.log('QRCode version:', QRCode.version);
            }
            console.log('Functions.js loaded:', typeof generateQRCode !== 'undefined');
        });
    </script>
    <style>
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .main-nav {
            display: flex;
            gap: 30px;
        }
        
        .main-nav a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .main-nav a:hover {
            background-color: var(--background);
            color: var(--primary);
        }
        
        .main-nav a.active {
            background-color: var(--primary);
            color: white;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            padding: 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--background) 0%, var(--white) 100%);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 1rem;
            color: var(--text-medium);
            margin-bottom: 8px;
        }
        
        .stat-growth {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        
        .event-filters {
            background-color: var(--white);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .filter-search {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .filter-select {
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            min-width: 200px;
        }
        
        .filter-btn {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: var(--primary);
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .filter-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .event-card {
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .event-cover {
            height: 200px;
            background-color: var(--background);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary);
        }
        
        .event-details {
            padding: 24px;
        }
        
        .event-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-dark);
        }
        
        .event-date, .event-location {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-medium);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .event-format {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-medium);
            margin-bottom: 8px;
        }
        
        .event-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 16px 0;
        }
        
        .tag {
            background-color: var(--background);
            color: var(--text-medium);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            transition: background-color 0.3s ease;
        }
        
        .tag:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
        }
        
        .attendees {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-medium);
            font-size: 0.9rem;
        }
        
        .btn-register {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 24px;
            color: var(--text-dark);
        }
        
        .no-events {
            text-align: center;
            padding: 60px 0;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .no-events i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 16px;
        }
        
        .no-events p {
            color: var(--text-medium);
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .main-nav {
                width: 100%;
                justify-content: space-between;
            }
            
            .filter-search {
                flex-direction: column;
            }
            
            .filter-select {
                width: 100%;
            }
            
            .events-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .registered-events {
            margin-top: 40px;
            padding-top: 40px;
            border-top: 1px solid var(--border-color);
        }
        
        .registered-events-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .registered-events-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .registered-events-count {
            background-color: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .registered-event-card {
            background-color: var(--white);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .registered-event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .registered-event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .registered-event-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .registered-event-date {
            color: var(--text-medium);
            font-size: 0.9rem;
        }
        
        .registered-event-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
        }
        
        .registered-event-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-medium);
        }
        
        .registered-event-detail i {
            color: var(--primary);
        }
        
        .registered-event-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }
        
        .btn-view-details {
            background-color: var(--primary);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-view-details:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .no-registered-events {
            text-align: center;
            padding: 40px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .no-registered-events i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 16px;
        }
        
        .no-registered-events p {
            color: var(--text-medium);
            margin-bottom: 8px;
        }
        
        .search-form {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 24px;
            margin-bottom: 30px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .your-events {
            margin-top: 40px;
        }
        
        .no-events {
            text-align: center;
            padding: 40px 0;
            color: var(--text-medium);
        }
        
        .no-events i {
            font-size: 48px;
            color: var(--border-color);
            margin-bottom: 15px;
            display: block;
        }
        
        .reservation-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }
        
        .seat-control {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 10px 0;
        }
        
        .seat-input {
            width: 50px;
            text-align: center;
            padding: 5px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        
        .decrease-seats, .increase-seats {
            padding: 5px 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--background);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .decrease-seats:hover, .increase-seats:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .decrease-seats:disabled, .increase-seats:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .warning {
            color: var(--danger);
            margin-top: 5px;
        }
        
        .time-remaining {
            color: var(--text-medium);
            font-size: 0.9rem;
        }
        
        .modify-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* QR Code Modal Styles */
        .qr-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .qr-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            position: relative;
        }

        .qr-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .qr-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.2rem;
        }

        .qr-details {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .qr-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: #555;
        }

        .qr-detail:last-child {
            margin-bottom: 0;
        }

        .qr-detail i {
            color: #007bff;
            width: 20px;
            text-align: center;
        }

        .qr-detail span {
            flex: 1;
        }

        .close-qr {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            line-height: 1;
        }

        .qr-body {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .qr-code {
            padding: 10px;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .qr-footer {
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .download-qr {
            padding: 8px 16px;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .download-qr:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .download-qr i {
            font-size: 14px;
        }

        /* Add these styles to your existing styles */
        .event-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .event-actions .show-qr {
            padding: 6px 10px;
            border-radius: 4px;
            background-color: #17a2b8;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-actions .show-qr:hover {
            background-color: #138496;
            transform: scale(1.05);
        }

        .event-actions .show-qr i {
            font-size: 16px;
            display: inline-block;
        }

        .btn-register {
            padding: 6px 12px;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .promo-content {
            white-space: pre-wrap;
            font-size: 1.1rem;
            line-height: 1.6;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
        }

        .generate-promo-btn {
            transition: all 0.3s ease;
        }

        .generate-promo-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .generate-promo-btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Header/Navigation -->
    <header>
        <div class="container header-container">
            <a href="#" class="logo">
                <h2>City pluse</h2>
            </a>
            <nav class="main-nav">
                <a href="#" class="active">Events</a>
                <a href="../backoffice/dashboard.php">Admin Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Upcoming Events</h1>
        </div>

        <!-- Email search form -->
        <div class="search-form">
            <h2 class="card-title">Find Your Reservations</h2>
            <form action="events.php" method="get">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">View My Reservations</button>
            </form>
        </div>

        <!-- mta3 eamil -->
        <?php if (!empty($userEmail) && !empty($userReservations)): ?>
        <div class="your-events">
            <h2 class="section-header">Your Reserved Events</h2>
            <div class="events-list">
                <?php foreach ($userReservations as $reservation): 
                    $canModify = canModifyReservation($reservation['start_date'], $reservation['start_time']);
                    $eventDateTime = new DateTime($reservation['start_date'] . ' ' . $reservation['start_time']);
                    $currentDateTime = new DateTime();
                    $interval = $currentDateTime->diff($eventDateTime);
                    $hoursUntilEvent = ($interval->days * 24) + $interval->h;
                ?>
                <div class="event-item">
                    <div class="event-content">
                        <div class="event-date">
                            <i class="far fa-calendar"></i> <?php echo date("j F Y", strtotime($reservation['start_date'])); ?> at <?php echo $reservation['start_time']; ?>
                        </div>
                        <div class="event-title"><?php echo htmlspecialchars($reservation['event_title']); ?></div>
                        <div class="event-location">
                            <i class="fas <?php echo $reservation['event_format'] === 'online' ? 'fa-video' : 'fa-map-marker-alt'; ?>"></i> <?php echo htmlspecialchars($reservation['location']); ?>
                        </div>
                        <div class="event-stats">
                            <div class="stat">
                                <i class="fas fa-chair"></i> <?php echo $reservation['seats_reserved']; ?> <?php echo $reservation['seats_reserved'] > 1 ? 'seats' : 'seat'; ?> reserved
                            </div>
                            <?php if (!$canModify): ?>
                            <div class="stat warning">
                                <i class="fas fa-exclamation-triangle"></i> Cannot modify (less than 24h before event)
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="event-actions">
                            <?php if ($canModify): ?>
                            <div class="reservation-actions">
                                <form action="modifyReservation.php" method="post" class="modify-form">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                    <input type="hidden" name="event_id" value="<?php echo $reservation['event_id']; ?>">
                                    <input type="hidden" name="current_seats" value="<?php echo $reservation['seats_reserved']; ?>">
                                    
                                    <div class="seat-control">
                                        <button type="button" class="btn btn-outline btn-sm decrease-seats" <?php echo $reservation['seats_reserved'] <= 1 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" name="new_seats" value="<?php echo $reservation['seats_reserved']; ?>" min="1" max="10" class="seat-input">
                                        <button type="button" class="btn btn-outline btn-sm increase-seats" <?php echo $reservation['seats_reserved'] >= 10 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    
                                    <button type="submit" name="action" value="update" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save"></i> Update Seats
                                    </button>
                                    <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this reservation?');">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </form>
                            </div>
                            <?php else: ?>
                            <div class="reservation-info">
                                <span class="time-remaining">
                                    <i class="fas fa-clock"></i> <?php echo $hoursUntilEvent; ?> hours until event
                                </span>
                            </div>
                            <?php endif; ?>
                            <button class="btn btn-info btn-sm show-qr-btn" 
                                    data-event-id="<?php echo $reservation['event_id']; ?>" 
                                    data-event-title="<?php echo htmlspecialchars($reservation['event_title']); ?>"
                                    data-event-date="<?php echo date("j F Y", strtotime($reservation['start_date'])); ?> at <?php echo $reservation['start_time']; ?>"
                                    data-event-location="<?php echo htmlspecialchars($reservation['event_format'] === 'online' ? 'Online Event' : $reservation['location']); ?>"
                                    data-event-format="<?php echo $reservation['event_format'] === 'online' ? 'Online' : 'In-Person'; ?>"
                                    data-event-capacity="<?php echo (isset($reservation['seats_reserved']) ? $reservation['seats_reserved'] : 0); ?> Attendees | <?php echo (isset($reservation['capacity']) && isset($reservation['seats_reserved']) ? $reservation['capacity'] - $reservation['seats_reserved'] : 0); ?> Available">
                                <i class="fas fa-qrcode"></i> Show QR Code
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Event Stats -->
        <div class="dashboard-grid">
            <div class="card stat-card">
                <div class="stat-value"><?php echo count($events); ?></div>
                <div class="stat-label">Total Events</div>
                <div class="stat-growth">Available for registration</div>
            </div>
            <?php 
            $onlineCount = 0;
            $inPersonCount = 0;
            foreach ($events as $event) {
                if ($event['event_format'] === 'online') {
                    $onlineCount++;
                } else {
                    $inPersonCount++;
                }
            }
            ?>
            <div class="card stat-card">
                <div class="stat-value"><?php echo $onlineCount; ?></div>
                <div class="stat-label">Online Events</div>
                <div class="stat-growth">Virtual experiences</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value"><?php echo $inPersonCount; ?></div>
                <div class="stat-label">In-Person Events</div>
                <div class="stat-growth">Physical attendance</div>
            </div>
            <div class="card stat-card">
                <?php
                $comingUp = 0;
                $today = date('Y-m-d');
                foreach ($events as $event) {
                    if ($event['start_date'] >= $today) {
                        $comingUp++;
                    }
                }
                ?>
                <div class="stat-value"><?php echo $comingUp; ?></div>
                <div class="stat-label">Coming Soon</div>
                <div class="stat-growth">Don't miss out</div>
            </div>
        </div>

        <!-- Event Filters -->
        <div class="event-filters">
            <div class="filter-search">
                <input type="search" placeholder="Search events..." class="search-input" id="searchEvents">
                <select class="filter-select" id="filterType">
                    <option value="">All Types</option>
                    <option value="conference">Conference</option>
                    <option value="seminar">Seminar</option>
                    <option value="workshop">Workshop</option>
                    <option value="networking">Networking</option>
                </select>
                <button class="filter-btn" id="filterBtn">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>

        <!-- Event List -->
        <h2 class="section-header">Browse All Events</h2>
        
        <?php if (empty($events)): ?>
        <div class="no-events">
            <i class="fas fa-calendar-times"></i>
            <p>No events available at the moment.</p>
            <p>Please check back later for new events.</p>
        </div>
        <?php else: ?>
        
        <div class="events-grid">
            <?php foreach ($events as $eventData): 
                // Create Event object from fetched data
                $event = new Event(
                    $eventData['event_title'],
                    $eventData['event_type'],
                    $eventData['description'],
                    $eventData['start_date'],
                    $eventData['start_time'],
                    $eventData['end_date'],
                    $eventData['end_time'],
                    $eventData['event_format'],
                    $eventData['location'] ?? '',
                    $eventData['online_url'] ?? '',
                    (int)$eventData['capacity'],
                    $eventData['ticket_type'],
                    (float)$eventData['price'],
                    (int)$eventData['event_id']
                );
                
                // Format the date
                $startDate = date("j F Y", strtotime($event->getStartDate()));
                
                // Get available seats and reservations
                $availableSeats = $eventController->getEventAvailableSeats($event->getEventId());
                $totalReservations = $eventController->getTotalReservations($event->getEventId());
                
                // Pick icon based on event format
                $iconClass = $event->getEventFormat() === 'online' ? 'fa-video' : 'fa-map-marker-alt';
            ?>
            <div class="card event-card" data-event-id="<?php echo $event->getEventId(); ?>">
                <div class="event-cover">
                    <i class="fas <?php echo $iconClass; ?>"></i>
                </div>
                <div class="event-details">
                    <div class="event-date">
                        <i class="far fa-calendar"></i> 
                        <span class="date-text"><?php echo $startDate; ?> at <?php echo $event->getStartTime(); ?></span>
                    </div>
                    <div class="event-title"><?php echo htmlspecialchars($event->getEventTitle()); ?></div>
                    <div class="event-location">
                        <i class="fas <?php echo $iconClass; ?>"></i> 
                        <span class="location-text"><?php echo htmlspecialchars($event->getEventFormat() === 'online' ? 'Online Event' : $event->getLocation()); ?></span>
                    </div>
                    <div class="event-format">
                        <i class="fas fa-info-circle"></i>
                        <span class="format-text"><?php echo $event->getEventFormat() === 'online' ? 'Online' : 'In-Person'; ?></span>
                    </div>
                    <div class="event-tags">
                        <?php 
                        $tags = explode(',', $event->getEventType());
                        foreach ($tags as $tag):
                            if (trim($tag) !== ''):
                        ?>
                        <span class="tag"><?php echo trim($tag); ?></span>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </div>
                    <div class="event-footer">
                        <div class="attendees">
                            <i class="fas fa-user"></i> 
                            <span class="attendees-text"><?php 
                                $totalReservations = isset($totalReservations) ? $totalReservations : 0;
                                $availableSeats = isset($availableSeats) ? $availableSeats : 0;
                                echo $totalReservations . ' Attendees | ' . $availableSeats . ' Available';
                            ?></span>
                        </div>
                        <div class="event-actions">
                            <a href="makeReservation.php?event_id=<?php echo $event->getEventId(); ?>" class="btn btn-primary btn-sm btn-register">
                                Register
                            </a>
                            <button class="btn btn-info btn-sm show-qr-btn" 
                                    data-event-id="<?php echo $event->getEventId(); ?>" 
                                    data-event-title="<?php echo htmlspecialchars($event->getEventTitle()); ?>"
                                    data-event-date="<?php echo $startDate; ?> at <?php echo $event->getStartTime(); ?>"
                                    data-event-location="<?php echo htmlspecialchars($event->getEventFormat() === 'online' ? 'Online Event' : $event->getLocation()); ?>"
                                    data-event-format="<?php echo $event->getEventFormat() === 'online' ? 'Online' : 'In-Person'; ?>"
                                    data-event-capacity="<?php 
                                        $totalReservations = isset($totalReservations) ? $totalReservations : 0;
                                        $availableSeats = isset($availableSeats) ? $availableSeats : 0;
                                        echo $totalReservations . ' Attendees | ' . $availableSeats . ' Available';
                                    ?>">
                                <i class="fas fa-qrcode"></i> QR
                            </button>
                            <button class="btn btn-warning btn-sm generate-promo-btn" 
                                    data-event-id="<?php echo $event->getEventId(); ?>"
                                    title="Generate Promotional Content">
                                <i class="fas fa-magic"></i> AI Promo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Registered Events Section -->
        <?php
        // Get user's email from session or wherever it's stored
        $userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
        
        // Debug information
        echo "<!-- Debug Info:";
        echo "\nUser Email: " . ($userEmail ? $userEmail : 'Not set');
        if ($userEmail) {
            $registeredEvents = $eventController->getRegisteredEvents($userEmail);
            echo "\nNumber of Registered Events: " . count($registeredEvents);
            echo "\nRegistered Events:";
            print_r($registeredEvents);
        }
        echo "\n-->";
        
        if ($userEmail) {
            $registeredEvents = $eventController->getRegisteredEvents($userEmail);
        ?>
        <div class="registered-events">
            <div class="registered-events-header">
                <h2 class="registered-events-title">My Registered Events</h2>
                <span class="registered-events-count"><?php echo count($registeredEvents); ?> Events</span>
            </div>
            
            <?php if (empty($registeredEvents)): ?>
            <div class="no-registered-events">
                <i class="fas fa-calendar-check"></i>
                <p>You haven't registered for any events yet.</p>
               
            </div>
            <?php else: ?>
                <?php foreach ($registeredEvents as $event): ?>
                <div class="registered-event-card">
                    <div class="registered-event-header">
                        <h3 class="registered-event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                        <div class="registered-event-date">
                            <i class="far fa-calendar"></i>
                            <?php echo date("j F Y", strtotime($event['start_date'])); ?> at <?php echo $event['start_time']; ?>
                        </div>
                    </div>
                    
                    <div class="registered-event-details">
                        <div class="registered-event-detail">
                            <i class="fas fa-users"></i>
                            <span><?php echo $event['seats_reserved']; ?> Seats Reserved</span>
                        </div>
                        <div class="registered-event-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo $event['event_format'] === 'online' ? 'Online Event' : htmlspecialchars($event['location']); ?></span>
                        </div>
                        <div class="registered-event-detail">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Reserved on <?php echo date("j F Y", strtotime($event['reservation_date'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="registered-event-actions">
                        <a href="makeReservation.php?event_id=<?php echo $event['event_id']; ?>" class="btn-view-details">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php } ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>Events</h3>
                    <p>Connect with campus events. Find, book, and attend your favorite educational activities all in one place.</p>
                </div>
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="#">Event Planning Guide</a></li>
                        <li><a href="#">Campus Map</a></li>
                        <li><a href="#">Student Engagement</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>About</h4>
                    <ul>
                        <li><a href="#">Student Union</a></li>
                        <li><a href="#">Events Policy</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Connect</h4>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Student Events Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Add this modal for displaying AI-generated content -->
    <div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="promoModalLabel">AI-Generated Promotional Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="promoContent" class="promo-content">
                        <!-- AI-generated content will appear here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="copyPromo">Copy to Clipboard</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Filter functionality
        const searchInput = document.getElementById('searchEvents');
        const filterSelect = document.getElementById('filterType');
        const filterBtn = document.getElementById('filterBtn');
        const eventCards = document.querySelectorAll('.event-card');
        
        function filterEvents() {
            const searchTerm = searchInput.value.toLowerCase();
            const filterType = filterSelect.value.toLowerCase();
            
            eventCards.forEach(card => {
                const title = card.querySelector('.event-title').textContent.toLowerCase();
                const tags = Array.from(card.querySelectorAll('.tag')).map(tag => tag.textContent.toLowerCase());
                
                const matchesSearch = title.includes(searchTerm);
                const matchesFilter = filterType === '' || tags.some(tag => tag.includes(filterType));
                
                if (matchesSearch && matchesFilter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        searchInput.addEventListener('input', filterEvents);
        filterSelect.addEventListener('change', filterEvents);
        filterBtn.addEventListener('click', filterEvents);
        
        // Handle seat modification
        document.querySelectorAll('.decrease-seats').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.nextElementSibling;
                if (parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1;
                    this.nextElementSibling.nextElementSibling.disabled = false;
                    if (parseInt(input.value) <= 1) {
                        this.disabled = true;
                    }
                }
            });
        });
        
        document.querySelectorAll('.increase-seats').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (parseInt(input.value) < 10) {
                    input.value = parseInt(input.value) + 1;
                    this.previousElementSibling.previousElementSibling.disabled = false;
                    if (parseInt(input.value) >= 10) {
                        this.disabled = true;
                    }
                }
            });
        });
        
        // Prevent manual input of invalid values
        document.querySelectorAll('.seat-input').forEach(input => {
            input.addEventListener('change', function() {
                let value = parseInt(this.value);
                if (isNaN(value) || value < 1) value = 1;
                if (value > 10) value = 10;
                this.value = value;
                
                // Update button states
                const decreaseBtn = this.previousElementSibling;
                const increaseBtn = this.nextElementSibling;
                decreaseBtn.disabled = value <= 1;
                increaseBtn.disabled = value >= 10;
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap modal
        const promoModal = document.getElementById('promoModal');
        if (promoModal) {
            const modal = new bootstrap.Modal(promoModal);
            
            // Add click handlers for promo buttons
            document.querySelectorAll('.generate-promo-btn').forEach(button => {
                button.addEventListener('click', async function() {
                    const eventCard = this.closest('.event-card');
                    const eventId = this.getAttribute('data-event-id');
                    
                    // Show loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
                    this.disabled = true;
                    
                    try {
                        // Generate promotional content
                        const promoContent = await generateEventPromo(eventCard);
                        
                        if (promoContent) {
                            // Display in modal
                            document.getElementById('promoContent').textContent = promoContent;
                            modal.show();
                        }
                    } catch (error) {
                        console.error('Error generating promo:', error);
                        alert('Error generating promotional content. Please try again.');
                    } finally {
                        // Reset button state
                        this.innerHTML = '<i class="fas fa-magic"></i> AI Promo';
                        this.disabled = false;
                    }
                });
            });
            
            // Copy to clipboard functionality
            const copyButton = document.getElementById('copyPromo');
            if (copyButton) {
                copyButton.addEventListener('click', function() {
                    const content = document.getElementById('promoContent').textContent;
                    navigator.clipboard.writeText(content).then(() => {
                        this.textContent = 'Copied!';
                        setTimeout(() => {
                            this.textContent = 'Copy ';
                        }, 2000);
                    });
                });
            }
        }
    });
    </script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} catch (Exception $e) {
    // Handle unexpected errors
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="../stylse.css">
</head>
<body>
    <div class="container">
        <div class="alert alert-danger">
            <h1>Error</h1>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <a href="events.php" class="btn btn-primary">Back to Events</a>
        </div>
    </div>
</body>
</html>';
}
?> 