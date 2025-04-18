<?php
include "../../config.php";
include "../../model/model.php";
include "../../controller/conttroler.php";

try {
    $eventController = new EventController();

    // Get all events
    $events = $eventController->listEvents();

    // Calculate statistics
    $totalEvents = 0;
    $totalDuration = 0;
    $onlineCount = 0;
    $offlineCount = 0;
    $totalRevenue = 0;

    if ($events) {
        $totalEvents = count($events);
        foreach ($events as $eventData) {
            // Create Event object for calculations
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
                (float)$eventData['price']
            );

            // Calculate duration
            $startDateTime = new DateTime($event->getStartDate() . ' ' . $event->getStartTime());
            $endDateTime = new DateTime($event->getEndDate() . ' ' . $event->getEndTime());
            $duration = $endDateTime->diff($startDateTime);
            $totalDuration += $duration->h + ($duration->days * 24);

            // Count event formats
            if ($event->getEventFormat() === 'online') {
                $onlineCount++;
            } else {
                $offlineCount++;
            }

            // Calculate revenue
            if ($event->getTicketType() === 'paid') {
                $totalRevenue += $event->getPrice();
            }
        }
    }

    $avgDuration = $totalEvents > 0 ? round($totalDuration / $totalEvents, 1) : 0;

    // Start the HTML output
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event List</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="stylse.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Modal styles */
        .modal {
            display: none; 
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            width: 80%;
            max-width: 700px;
            position: relative;
            animation: modalopen 0.4s;
        }
        
        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-60px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 24px;
            font-weight: bold;
            color: #6c757d;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #343a40;
        }
        
        .modal-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .modal-title {
            margin: 0;
            color: var(--text-dark);
            font-size: 1.5rem;
        }
        
        .event-detail-row {
            margin-bottom: 15px;
            display: flex;
        }
        
        .event-detail-label {
            font-weight: 600;
            width: 140px;
            color: var(--text-medium);
        }
        
        .event-detail-value {
            flex: 1;
            color: var(--text-dark);
        }
        
        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        
        /* New styles for edit button */
        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<button class="sidebar-toggle">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar">
    <div class="sidebar-logo">
        <h2>Event Manager</h2>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="events.php">
                <i class="fas fa-calendar-check"></i> Events
            </a>
        </li>
        <li>
            <a href="newEvent.html">
                <i class="fas fa-plus-circle"></i> Create Event
            </a>
        </li>
        <li>
            <a href="reports.php">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </li>
        <li>
            <a href="attendees.php">
                <i class="fas fa-users"></i> Attendees
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
    <div class="container">'; 

    // Display success messages if exists
    if (isset($_GET['delete_success']) && $_GET['delete_success'] == "true") {
        echo '<div class="alert alert-success">Event deleted successfully.</div>';
    }
    if (isset($_GET['edit_success']) && $_GET['edit_success'] == "true") {
        echo '<div class="alert alert-success">Event updated successfully.</div>';
    }

    echo '<h1 class="page-header">Event List</h1>

        <!-- Dashboard Grid -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value">' . $totalEvents . '</div>
                <div class="stat-label">Total Events</div>
                <div class="stat-growth">All time events</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $avgDuration . 'h</div>
                <div class="stat-label">Average Duration</div>
                <div class="stat-growth">Per event</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">$' . number_format($totalRevenue, 2) . '</div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-growth">From paid events</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $onlineCount . '/' . $offlineCount . '</div>
                <div class="stat-label">Online/Offline Events</div>
                <div class="stat-growth">Current ratio</div>
            </div>
        </div>

        <!-- Event Format Distribution -->
        <div class="chart-container">
            <h2 class="card-title">Event Format Distribution</h2>
            <div class="donut-chart">
                <div class="chart-wrapper">
                    <canvas id="formatChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: var(--primary);"></div>
                        <span>Online Events (' . $onlineCount . ')</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: var(--secondary);"></div>
                        <span>In-Person Events (' . $offlineCount . ')</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event List -->
        <div class="events-list">';

    // Check if there are any events
    if (!$events) {
        echo '<p class="no-events">No events found.</p>';
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
                            <a href="editEvent.php?event_id=' . $event->getEventId() . '" class="btn btn-edit btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="deleteEvent.php" method="post" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this event?\');">
                                <input type="hidden" name="event_id" value="' . $event->getEventId() . '">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
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
                    <a href="editEvent.php?event_id=' . $event->getEventId() . '" class="btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button class="btn btn-primary close-btn" data-event-id="' . $event->getEventId() . '">Close</button>
                  </div>
                </div>
            </div>';
        }
    }

    echo '</div>';

    echo '</div>

    <script>
    // Event Format Distribution Chart
    const formatCtx = document.getElementById("formatChart").getContext("2d");
    new Chart(formatCtx, {
        type: "doughnut",
        data: {
            labels: ["Online Events", "In-Person Events"],
            datasets: [{
                data: [' . $onlineCount . ', ' . $offlineCount . '],
                backgroundColor: [
                    "var(--primary)",
                    "var(--secondary)"
                ],
                borderWidth: 0,
                hoverOffset: 15,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: "rgba(255, 255, 255, 0.9)",
                    titleColor: "var(--text-dark)",
                    bodyColor: "var(--text-medium)",
                    borderColor: "var(--border-color)",
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || "";
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: "70%",
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
    
    // Modal Scripts
    document.addEventListener("DOMContentLoaded", function() {
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
    });
    document.addEventListener("DOMContentLoaded", function() {
    // Get all alert messages
    const alerts = document.querySelectorAll(".alert");
    
    // If there are any alerts present, set a timeout to remove them after 3 seconds
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(function(alert) {
                // Add fade-out animation
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                
                // Remove the element after the fade-out animation completes
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 3000); // 3 seconds
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
    <link rel="stylesheet" href="stylse.css">
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