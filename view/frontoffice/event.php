<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    $eventController = new EventController();
    
    // Get all events
    $events = $eventController->listEvents();
    
    // Check if email is provided for filtering reservations
    $userEmail = isset($_GET['email']) ? $_GET['email'] : '';
    
    // Get user's reservations if email is available
    $userReservations = [];
    if (!empty($userEmail)) {
        $userReservations = $eventController->getReservationsByEmail($userEmail);
    }
    
    // Start the HTML output
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityPulse - Events</title>
    <link rel="stylesheet" href="../stylse.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
<div class="container">';

    echo '<h1 class="page-header">Public Events Portal</h1>';
    
    // Email search form
    echo '<div class="search-form">
        <h2 class="card-title">Find Your Reservations</h2>
        <form action="reservations.php" method="get">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" value="' . htmlspecialchars($userEmail) . '" required>
            </div>
            <button type="submit" class="btn btn-primary">View My Reservations</button>
        </form>
    </div>';

    // Your reserved events section if email is provided
    if (!empty($userEmail) && !empty($userReservations)) {
        echo '<div class="your-events">
            <h2 class="section-header">Your Reserved Events</h2>';
        
        echo '<div class="events-list">';
        foreach ($userReservations as $reservation) {
            // Format the date
            $eventDate = date("j F Y", strtotime($reservation['start_date']));
            
            echo '<div class="event-item">
                <div class="event-content">
                    <div class="event-date">
                        <i class="far fa-calendar"></i> ' . $eventDate . ' at ' . $reservation['start_time'] . '
                    </div>
                    <div class="event-title">' . htmlspecialchars($reservation['event_title']) . '</div>
                    <div class="event-location">
                        <i class="fas ' . ($reservation['event_format'] === 'online' ? 'fa-video' : 'fa-map-marker-alt') . '"></i> ' . htmlspecialchars($reservation['location']) . '
                    </div>
                    <div class="event-stats">
                        <div class="stat">
                            <i class="fas fa-chair"></i> ' . $reservation['seats_reserved'] . ' ' . ($reservation['seats_reserved'] > 1 ? 'seats' : 'seat') . ' reserved
                        </div>
                    </div>
                    <div class="event-actions">
                        <a href="reservations.php?email=' . urlencode($userEmail) . '" class="btn btn-outline btn-sm">
                            <i class="fas fa-ticket-alt"></i> Manage Reservation
                        </a>
                    </div>
                </div>
            </div>';
        }
        echo '</div>';
        
        echo '</div>';
    }

    // Available events section
    echo '<h2 class="section-header">Upcoming Events</h2>
    <div class="tabs">
        <div class="tab active">All Events</div>
        <div class="tab">Workshops</div>
        <div class="tab">Conferences</div>
        <div class="tab">Webinars</div>
    </div>
    
    <div class="events-list">';

    if (!$events) {
        echo '<div class="no-events">
            <i class="fas fa-calendar-times"></i>
            <p>No events available at the moment.</p>
            <p>Please check back later for new events.</p>
        </div>';
    } else {
        foreach ($events as $eventData) {
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
            $location = $event->getEventFormat() === 'online' ? 
                ($event->getOnlineUrl() ? $event->getOnlineUrl() : 'Online') : 
                $event->getLocation();
            $tags = explode(',', $event->getEventType());
            
            // Get available seats
            $availableSeats = $eventController->getEventAvailableSeats($event->getEventId());
            $totalReservations = $eventController->getTotalReservations($event->getEventId());

            echo '<div class="event-item">
                    <div class="event-content">
                        <div class="event-date">
                            <i class="far fa-calendar"></i> ' . $startDate . '
                        </div>
                        <div class="event-title">' . htmlspecialchars($event->getEventTitle()) . '</div>
                        <div class="event-location">
                            <i class="fas ' . ($event->getEventFormat() === 'online' ? 'fa-video' : 'fa-map-marker-alt') . '"></i> ' . htmlspecialchars($location) . '
                        </div>
                        <div class="event-stats">
                            <div class="stat">
                                <i class="fas fa-chair"></i> ' . $availableSeats . ' seats available
                            </div>
                            <div class="stat">
                                <i class="fas fa-users"></i> ' . $totalReservations . ' reservations
                            </div>
                        </div>
                        <div class="event-tags">';

            foreach ($tags as $tag) {
                if (trim($tag) !== '') {
                    echo '<span class="tag">' . trim($tag) . '</span>';
                }
            }

            echo '</div>
                        <div class="event-actions">
                            <button class="btn btn-outline btn-sm view-details" data-event-id="' . $event->getEventId() . '">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <a href="makeReservation.php?event_id=' . $event->getEventId() . '" class="btn btn-primary btn-sm">
                                <i class="fas fa-ticket-alt"></i> Reserve
                            </a>
                        </div>
                    </div>
                </div>';
                
            // Modal for event details
            echo '<div id="eventModal-' . $event->getEventId() . '" class="modal">
                    <div class="modal-content">
                        <span class="close-modal" data-event-id="' . $event->getEventId() . '">&times;</span>
                        <div class="modal-header">
                            <h2 class="modal-title">' . htmlspecialchars($event->getEventTitle()) . '</h2>
                        </div>
                        <div class="event-detail-row">
                            <div class="event-detail-label">Event Type:</div>
                            <div class="event-detail-value">' . htmlspecialchars($event->getEventType()) . '</div>
                        </div>
                        <div class="event-detail-row">
                            <div class="event-detail-label">Description:</div>
                            <div class="event-detail-value">' . nl2br(htmlspecialchars($event->getDescription())) . '</div>
                        </div>
                        <div class="event-detail-row">
                            <div class="event-detail-label">Start Date:</div>
                            <div class="event-detail-value">' . date("j F Y", strtotime($event->getStartDate())) . '</div>
                        </div>
                        <div class="event-detail-row">
                            <div class="event-detail-label">Start Time:</div>
                            <div class="event-detail-value">' . $event->getStartTime() . '</div>
                        </div>
                        <div class="event-detail-row">
                            <div class="event-detail-label">End Date:</div>
                            <div class="event-detail-value">' . date("j F Y", strtotime($event->getEndDate())) . '</div>
                        </div>
                        <div class="event-detail-row">
                            <div class="event-detail-label">End Time:</div>
                            <div class="event-detail-value">' . $event->getEndTime() . '</div>
                        </div>
                        <div class="event-detail-row">
                            <div class="event-detail-label">Format:</div>
                            <div class="event-detail-value">' . ($event->getEventFormat() === 'online' ? 'Online' : 'In Person') . '</div>
                        </div>';
                        
            if ($event->getEventFormat() === 'online' && !empty($event->getOnlineUrl())) {
                echo '<div class="event-detail-row">
                        <div class="event-detail-label">Online URL:</div>
                        <div class="event-detail-value"><a href="' . htmlspecialchars($event->getOnlineUrl()) . '" target="_blank">' . htmlspecialchars($event->getOnlineUrl()) . '</a></div>
                      </div>';
            }

            if ($event->getEventFormat() === 'inPerson' && !empty($event->getLocation())) {
                echo '<div class="event-detail-row">
                        <div class="event-detail-label">Location:</div>
                        <div class="event-detail-value">' . htmlspecialchars($event->getLocation()) . '</div>
                      </div>';
            }
                        
            echo '<div class="event-detail-row">
                    <div class="event-detail-label">Capacity:</div>
                    <div class="event-detail-value">' . $event->getCapacity() . ' people</div>
                  </div>
                  <div class="event-detail-row">
                    <div class="event-detail-label">Available Seats:</div>
                    <div class="event-detail-value">' . $availableSeats . ' seats</div>
                  </div>
                  <div class="event-detail-row">
                    <div class="event-detail-label">Ticket Type:</div>
                    <div class="event-detail-value">' . ucfirst($event->getTicketType()) . '</div>
                  </div>';
                  
            if ($event->getTicketType() === 'paid' && $event->getPrice() > 0) {
                echo '<div class="event-detail-row">
                        <div class="event-detail-label">Price:</div>
                        <div class="event-detail-value">$' . number_format($event->getPrice(), 2) . '</div>
                      </div>';
            }
                        
            echo '<div class="modal-footer">
                    <a href="makeReservation.php?event_id=' . $event->getEventId() . '" class="btn btn-primary">
                        <i class="fas fa-ticket-alt"></i> Reserve Now
                    </a>
                    <button class="btn btn-outline close-btn" data-event-id="' . $event->getEventId() . '">Close</button>
                  </div>
                </div>
            </div>';
        }
    }

    echo '</div>

    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Modal Scripts
        // Open modal when View Details button is clicked
        const viewButtons = document.querySelectorAll(".view-details");
        viewButtons.forEach(button => {
            button.addEventListener("click", function() {
                const eventId = this.getAttribute("data-event-id");
                const modal = document.getElementById("eventModal-" + eventId);
                modal.style.display = "block";
            });
        });
        
        // Close modal when X is clicked
        const closeButtons = document.querySelectorAll(".close-modal");
        closeButtons.forEach(button => {
            button.addEventListener("click", function() {
                const eventId = this.getAttribute("data-event-id");
                const modal = document.getElementById("eventModal-" + eventId);
                modal.style.display = "none";
            });
        });
        
        // Close modal when Close button is clicked
        const closeModalButtons = document.querySelectorAll(".close-btn");
        closeModalButtons.forEach(button => {
            button.addEventListener("click", function() {
                const eventId = this.getAttribute("data-event-id");
                const modal = document.getElementById("eventModal-" + eventId);
                modal.style.display = "none";
            });
        });
        
        // Close modal when clicking outside
        window.addEventListener("click", function(event) {
            if (event.target.classList.contains("modal")) {
                event.target.style.display = "none";
            }
        });

        // Tabs functionality
        const tabs = document.querySelectorAll(".tab");
        tabs.forEach(tab => {
            tab.addEventListener("click", function() {
                tabs.forEach(t => t.classList.remove("active"));
                this.classList.add("active");
                // In a real implementation, you would filter events based on the selected tab
            });
        });

        // Alert Messages
        const alerts = document.querySelectorAll(".alert");
        if (alerts.length > 0) {
            setTimeout(function() {
                alerts.forEach(function(alert) {
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = "0";
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                });
            }, 3000);
        }

        // Sidebar toggle functionality
        const sidebarToggle = document.querySelector(".sidebar-toggle");
        const sidebar = document.querySelector(".sidebar");
        
        sidebarToggle.addEventListener("click", function() {
            sidebar.classList.toggle("active");
        });
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