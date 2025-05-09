<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    $eventController = new EventController();
    
    // Get event_id from URL
    $eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
    
    if ($eventId <= 0) {
        throw new Exception("Invalid event ID.");
    }
    
    // Get event details
    $eventDetails = $eventController->getEvent($eventId);
    
    if (!$eventDetails) {
        throw new Exception("Event not found.");
    }
    
    // Convert to Event object
    $event = new Event(
        $eventDetails['event_title'],
        $eventDetails['event_type'],
        $eventDetails['description'],
        $eventDetails['start_date'],
        $eventDetails['start_time'],
        $eventDetails['end_date'],
        $eventDetails['end_time'],
        $eventDetails['event_format'],
        $eventDetails['location'] ?? '',
        $eventDetails['online_url'] ?? '',
        (int)$eventDetails['capacity'],
        $eventDetails['ticket_type'],
        (float)$eventDetails['price'],
        (int)$eventDetails['event_id']
    );
    
    // Check available seats
    $availableSeats = $eventController->getEventAvailableSeats($eventId);
    
    if ($availableSeats <= 0) {
        throw new Exception("Sorry, this event is fully booked.");
    }
    
    // Get the errors from query string if there are any
    $errors = [];
    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'seats':
                $errors[] = 'The number of seats requested is not available.';
                break;
            case 'email':
                $errors[] = 'Please provide a valid email address.';
                break;
            case 'exists':
                $errors[] = 'You already have a reservation for this event.';
                break;
            default:
                $errors[] = 'An error occurred while processing your reservation.';
        }
    }
    
    // Get success message if exists
    $success = isset($_GET['success']) && $_GET['success'] == 1;
    
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Reservation - ' . htmlspecialchars($event->getEventTitle()) . '</title>
    <link rel="stylesheet" href="../stylse.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .reservation-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .event-details {
            flex: 1;
            min-width: 300px;
        }
        
        .reservation-form {
            flex: 1;
            min-width: 300px;
            background-color: var(--white);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .event-image {
            width: 100%;
            aspect-ratio: 16/9;
            background-color: var(--border-color);
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-medium);
            overflow: hidden;
        }
        
        .event-image i {
            font-size: 48px;
        }
        
        .detail-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .detail-label {
            flex: 1;
            color: var(--text-medium);
        }
        
        .detail-value {
            flex: 2;
            font-weight: 500;
        }
        
        .seats-input {
            display: flex;
            align-items: center;
            max-width: 120px;
        }
        
        .seats-input button {
            width: 32px;
            height: 32px;
            border: 1px solid var(--border-color);
            background: var(--background);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
        }
        
        .seats-input input {
            width: 56px;
            height: 32px;
            text-align: center;
            border: 1px solid var(--border-color);
            border-left: none;
            border-right: none;
        }
        
        .price-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: var(--background);
            border-radius: 8px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
            font-weight: 600;
            font-size: 1.1em;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">
        <h2>Event Manager</h2>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="../backoffice/dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="../backoffice/read.php">
                <i class="fas fa-calendar-check"></i> Admin Events
            </a>
        </li>
        <li>
            <a href="reservations.php">
                <i class="fas fa-ticket-alt"></i> My Reservations
            </a>
        </li>
        <li>
            <a href="events.php">
                <i class="fas fa-calendar-alt"></i> Public Events
            </a>
        </li>
    </ul>
    
    <div class="sidebar-user">
        <div class="user-avatar">U</div>
        <div class="user-details">
            <p class="user-name">Public User</p>
            <p class="user-role">Guest</p>
        </div>
    </div>
</div>

<!-- Mobile toggle button -->
<button class="sidebar-toggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Main content container -->
<div class="container">
    <div class="page-header-with-actions">
        <h1 class="page-header">Reserve Event</h1>
        <div class="header-actions">
            <a href="events.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
        </div>
    </div>';
    
    // Display errors if any
    if (!empty($errors)) {
        echo '<div class="alert alert-danger">';
        foreach ($errors as $error) {
            echo '<p>' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
    }
    
    // Display success message
    if ($success) {
        echo '<div class="alert alert-success">
            <h3><i class="fas fa-check-circle"></i> Reservation Successful!</h3>
            <p>Your reservation for "' . htmlspecialchars($event->getEventTitle()) . '" has been confirmed.</p>
            <p>We\'ve sent the details to your email. You can also view your reservations on the <a href="reservations.php">Reservations</a> page.</p>
        </div>';
    }
    
    echo '<div class="reservation-container">
        <div class="event-details">
            <h2>Event Details</h2>
            <div class="event-image">
                <i class="fas ' . ($event->getEventFormat() === 'online' ? 'fa-laptop' : 'fa-calendar-day') . '"></i>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Event Title</div>
                <div class="detail-value">' . htmlspecialchars($event->getEventTitle()) . '</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Date</div>
                <div class="detail-value">' . date("j F Y", strtotime($event->getStartDate())) . '</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Time</div>
                <div class="detail-value">' . $event->getStartTime() . ' - ' . $event->getEndTime() . '</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Event Type</div>
                <div class="detail-value">' . htmlspecialchars($event->getEventType()) . '</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Format</div>
                <div class="detail-value">' . ($event->getEventFormat() === 'online' ? 'Online' : 'In Person') . '</div>
            </div>';
            
    if ($event->getEventFormat() === 'online' && !empty($event->getOnlineUrl())) {
        echo '<div class="detail-row">
                <div class="detail-label">Online URL</div>
                <div class="detail-value">' . htmlspecialchars($event->getOnlineUrl()) . '</div>
              </div>';
    }
            
    if ($event->getEventFormat() === 'inPerson') {
        echo '<div class="detail-row">
                <div class="detail-label">Location</div>
                <div class="detail-value">' . htmlspecialchars($event->getLocation()) . '</div>
              </div>';
    }
            
    echo '<div class="detail-row">
                <div class="detail-label">Available Seats</div>
                <div class="detail-value">' . $availableSeats . '</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Ticket Type</div>
                <div class="detail-value">' . ucfirst($event->getTicketType()) . '</div>
            </div>';
            
    if ($event->getTicketType() === 'paid') {
        echo '<div class="detail-row">
                <div class="detail-label">Price</div>
                <div class="detail-value">$' . number_format($event->getPrice(), 2) . ' per seat</div>
              </div>';
    }
            
    echo '</div>
        
        <div class="reservation-form">
            <h2>Make Your Reservation</h2>
            <p>Fill in the form below to reserve your spot for this event.</p>
            
            <form action="processReservation.php" method="post">
                <input type="hidden" name="event_id" value="' . $eventId . '">
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number (optional)</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter your phone number">
                </div>
                
                <div class="form-group">
                    <label for="seats">Number of Seats</label>
                    <div class="seats-input">
                        <button type="button" id="decreaseSeats">-</button>
                        <input type="number" id="seats" name="seats" value="1" min="1" max="' . $availableSeats . '" readonly>
                        <button type="button" id="increaseSeats">+</button>
                    </div>
                </div>';
                
    if ($event->getTicketType() === 'paid') {
        echo '<div class="price-summary">
                <div class="price-row">
                    <div>Price per seat:</div>
                    <div>$' . number_format($event->getPrice(), 2) . '</div>
                </div>
                
                <div class="price-row">
                    <div>Number of seats:</div>
                    <div id="seatCount">1</div>
                </div>
                
                <div class="total-row">
                    <div>Total:</div>
                    <div id="totalPrice">$' . number_format($event->getPrice(), 2) . '</div>
                </div>
              </div>';
    }
                
    echo '<div class="form-group">
                <label for="special_requests">Special Requests (optional)</label>
                <textarea id="special_requests" name="special_requests" class="form-control" rows="3" placeholder="Any special requirements or requests?"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-ticket-alt"></i> Complete Reservation
            </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Handle seat increment/decrement
    const decreaseBtn = document.getElementById("decreaseSeats");
    const increaseBtn = document.getElementById("increaseSeats");
    const seatsInput = document.getElementById("seats");
    const seatCount = document.getElementById("seatCount");
    const totalPrice = document.getElementById("totalPrice");
    const maxSeats = ' . $availableSeats . ';
    const pricePerSeat = ' . $event->getPrice() . ';
    
    decreaseBtn.addEventListener("click", function() {
        let currentVal = parseInt(seatsInput.value);
        if (currentVal > 1) {
            seatsInput.value = currentVal - 1;
            updatePriceSummary();
        }
    });
    
    increaseBtn.addEventListener("click", function() {
        let currentVal = parseInt(seatsInput.value);
        if (currentVal < maxSeats) {
            seatsInput.value = currentVal + 1;
            updatePriceSummary();
        }
    });
    
    function updatePriceSummary() {
        if (seatCount && totalPrice) {
            const seats = parseInt(seatsInput.value);
            seatCount.textContent = seats;
            totalPrice.textContent = "$" + (seats * pricePerSeat).toFixed(2);
        }
    }
    
    // Sidebar toggle functionality
    const sidebarToggle = document.querySelector(".sidebar-toggle");
    const sidebar = document.querySelector(".sidebar");
    
    sidebarToggle.addEventListener("click", function() {
        sidebar.classList.toggle("active");
    });
    
    // Alert Messages
    const alerts = document.querySelectorAll(".alert");
    
    if (alerts.length > 0 && !alerts[0].classList.contains("alert-success")) {
        setTimeout(function() {
            alerts.forEach(function(alert) {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    }
});
</script>
</body>
</html>';
} catch (Exception $e) {
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
            <a href="event.php" class="btn btn-primary">Back to Events</a>
        </div>
    </div>
</body>
</html>';
}
?> 