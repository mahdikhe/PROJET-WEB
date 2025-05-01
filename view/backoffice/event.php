<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    $eventController = new EventController();
    
    // Get all events
    $events = $eventController->listEvents();
    
    // Start the HTML output
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../stylse.css">
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">
        <h2>Event Manager</h2>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="read.php">
                <i class="fas fa-calendar-check"></i> Events
            </a>
        </li>
        <li>
            <a href="newEvent.html">
                <i class="fas fa-plus-circle"></i> Create Event
            </a>
        </li>
        <li>
            <a href="../frontoffice/reservations.php">
                <i class="fas fa-ticket-alt"></i> Reservations
            </a>
        </li>
        <li>
            <a href="event.php" class="active">
                <i class="fas fa-calendar-alt"></i> Events List
            </a>
        </li>
        <li>
            <a href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
    </ul>
    
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

<!-- Main content container -->
<div class="container">
    <h1 class="page-header">Events Management</h1>
    
    <div class="alert alert-info">
        <p><i class="fas fa-info-circle"></i> <strong>Note:</strong> Reservation functionality has been moved to the frontoffice. <a href="../frontoffice/event.php">Click here</a> to access the public event view with reservation capabilities.</p>
    </div>
    
    <div class="event-filters">
        <div class="filter-search">
            <input type="text" class="search-input" id="searchEvents" placeholder="Search events...">
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
        <div class="view-options">
            <a href="read.php" class="btn btn-outline">
                <i class="fas fa-th-list"></i> Admin View
            </a>
        </div>
    </div>';
    
    // Display events
    if (!$events) {
        echo '<p class="no-events">No events found.</p>';
    } else {
        echo '<div class="events-list">';
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
                $eventData['location'],
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
            
            echo '<div class="event-item">
                <div class="event-content">
                    <div class="event-date">
                        <i class="far fa-calendar"></i> ' . $startDate . '
                    </div>
                    <div class="event-title">' . htmlspecialchars($event->getEventTitle()) . '</div>
                    <div class="event-location">
                        <i class="fas ' . ($event->getEventFormat() === 'online' ? 'fa-video' : 'fa-map-marker-alt') . '"></i> ' . htmlspecialchars($location) . '
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
                        <a href="editEvent.php?event_id=' . $event->getEventId() . '" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
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
                <div class="event-detail-value">' . $availableSeats . '</div>
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
                <a href="editEvent.php?event_id=' . $event->getEventId() . '" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Event
                </a>
                <button class="btn btn-outline close-btn" data-event-id="' . $event->getEventId() . '">Close</button>
            </div>
            </div>
        </div>';
        }
        echo '</div>';
    }
    
echo '</div>

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
    
    // Filter functionality
    const searchInput = document.getElementById("searchEvents");
    const filterType = document.getElementById("filterType");
    const filterBtn = document.getElementById("filterBtn");
    const eventItems = document.querySelectorAll(".event-item");
    
    function filterEvents() {
        const searchValue = searchInput.value.toLowerCase();
        const typeValue = filterType.value.toLowerCase();
        
        eventItems.forEach(item => {
            const title = item.querySelector(".event-title").textContent.toLowerCase();
            const tags = Array.from(item.querySelectorAll(".tag")).map(tag => tag.textContent.toLowerCase());
            
            const matchesSearch = title.includes(searchValue);
            const matchesType = typeValue === "" || tags.some(tag => tag.includes(typeValue));
            
            if (matchesSearch && matchesType) {
                item.style.display = "block";
            } else {
                item.style.display = "none";
            }
        });
    }
    
    searchInput.addEventListener("input", filterEvents);
    filterType.addEventListener("change", filterEvents);
    filterBtn.addEventListener("click", filterEvents);
    
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
        </div>
    </div>
</body>
</html>';
}
?> 