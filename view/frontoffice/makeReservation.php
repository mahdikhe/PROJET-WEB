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
    
    $html = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Make Reservation - ' . htmlspecialchars($event->getEventTitle()) . '</title>
        <link rel="stylesheet" href="../stylse.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="../functions.js"></script>
        <style>
            .reservation-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                margin-top: 30px;
            }
            
            .event-details {
                background: linear-gradient(135deg, var(--background) 0%, var(--white) 100%);
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            
            .event-image {
                width: 100%;
                height: 200px;
                background-color: var(--primary-light);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 20px;
            }
            
            .event-image i {
                font-size: 4rem;
                color: var(--primary);
            }
            
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 15px 0;
                border-bottom: 1px solid var(--border-color);
            }
            
            .detail-row:last-child {
                border-bottom: none;
            }
            
            .detail-label {
                color: var(--text-medium);
                font-weight: 500;
            }
            
            .detail-value {
                color: var(--text-dark);
                font-weight: 600;
            }
            
            .reservation-form {
                background: var(--white);
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                color: var(--text-dark);
                font-weight: 500;
            }
            
            .form-control {
                width: 100%;
                padding: 12px;
                border: 2px solid var(--border-color);
                border-radius: 8px;
                font-size: 1rem;
                transition: all 0.3s ease;
            }
            
            .form-control:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
                outline: none;
            }
            
            .form-control.is-invalid {
                border-color: var(--danger);
            }
            
            .invalid-feedback {
                color: var(--danger);
                font-size: 0.875rem;
                margin-top: 5px;
                display: none;
            }
            
            .seats-input {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .seats-input button {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                padding: 0;
            }
            
            .seats-input input {
                width: 60px;
                text-align: center;
                font-weight: 600;
            }
            
            .price-summary {
                background: var(--background);
                padding: 20px;
                border-radius: 10px;
                margin: 20px 0;
            }
            
            .price-row, .total-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
            }
            
            .total-row {
                font-weight: 600;
                font-size: 1.1rem;
                color: var(--primary);
                padding-top: 10px;
                border-top: 1px solid var(--border-color);
            }
            
            .btn-block {
                width: 100%;
                padding: 15px;
                font-size: 1.1rem;
                font-weight: 600;
            }
            
            .alert {
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 15px;
            }
            
            .alert i {
                font-size: 1.5rem;
            }
            
            .alert-danger {
                background-color: var(--danger-light);
                color: var(--danger);
                border: 1px solid var(--danger);
            }
            
            .alert-success {
                background-color: var(--success-light);
                color: var(--success);
                border: 1px solid var(--success);
            }
            
            .page-header-with-actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
            }
            
            @media (max-width: 768px) {
                .reservation-container {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="page-header-with-actions">
                <h1 class="page-header">Reserve Event</h1>
                <div class="header-actions">
                    <a href="events.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Events
                    </a>
                </div>
            </div>';

    if (!empty($errors)) {
        foreach ($errors as $error) {
            $html .= '<div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                ' . htmlspecialchars($error) . '
            </div>';
        }
    }

    if ($success) {
        $html .= '<div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <h4>Reservation Successful!</h4>
            <p>We\'ve sent the details to your email. You can also view your reservations on the <a href="reservations.php">Reservations</a> page.</p>
        </div>';
    }

    $html .= '<div class="reservation-container">
        <div class="event-details card">
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
        $html .= '<div class="detail-row">
            <div class="detail-label">Online URL</div>
            <div class="detail-value">' . htmlspecialchars($event->getOnlineUrl()) . '</div>
        </div>';
    }

    if ($event->getEventFormat() === 'inPerson') {
        $html .= '<div class="detail-row">
            <div class="detail-label">Location</div>
            <div class="detail-value">' . htmlspecialchars($event->getLocation()) . '</div>
        </div>';
    }

    $html .= '<div class="detail-row">
            <div class="detail-label">Available Seats</div>
            <div class="detail-value">' . $availableSeats . '</div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Ticket Type</div>
            <div class="detail-value">' . ucfirst($event->getTicketType()) . '</div>
        </div>';

    if ($event->getTicketType() === 'paid') {
        $html .= '<div class="detail-row">
            <div class="detail-label">Price</div>
            <div class="detail-value">$' . number_format($event->getPrice(), 2) . ' per seat</div>
        </div>';
    }

    $html .= '</div>
        
        <div class="reservation-form card">
            <h2>Make Your Reservation</h2>
            <p>Fill in the form below to reserve your spot for this event.</p>
            
            <form id="reservationForm" action="processReservation.php" method="post">
                <input type="hidden" name="event_id" value="' . $eventId . '">
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name">
                    <div class="invalid-feedback">Please enter your name</div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address">
                    <div class="invalid-feedback">Please enter a valid email address</div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number (optional)</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter your phone number">
                    <div class="invalid-feedback">Please enter a valid phone number</div>
                </div>
                
                <div class="form-group">
                    <label for="seats">Number of Seats</label>
                    <div class="seats-input">
                        <button type="button" id="decreaseSeats" class="btn btn-outline">-</button>
                        <input type="number" id="seats" name="seats" value="1" min="1" max="' . $availableSeats . '" readonly>
                        <button type="button" id="increaseSeats" class="btn btn-outline">+</button>
                    </div>
                    <div class="invalid-feedback">Please select a valid number of seats</div>
                </div>';

    if ($event->getTicketType() === 'paid') {
        $html .= '<div class="price-summary">
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

    $html .= '<div class="form-group">
            <label for="special_requests">Special Requests (optional)</label>
            <textarea id="special_requests" name="special_requests" class="form-control" rows="3" placeholder="Any special requirements or requests?"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-ticket-alt"></i> Complete Reservation
        </button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize seat control
    setupSeatControl("seats", "decreaseSeats", "increaseSeats", ' . $availableSeats . ', ' . $event->getPrice() . ');
    
    // Setup form validation
    setupFormValidation("reservationForm", {
        "name": {
            type: "name"
        },
        "email": {
            type: "email"
        },
        "phone": {
            type: "phone"
        },
        "seats": {
            type: "seats",
            options: {
                maxSeats: ' . $availableSeats . '
            }
        }
    });
});
</script>
</body>
</html>';

    echo $html;
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
                <a href="events.php" class="btn btn-primary">Back to Events</a>
            </div>
        </div>
    </body>
    </html>';
}
?>