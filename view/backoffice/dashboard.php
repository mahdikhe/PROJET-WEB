<?php
// Include required files
require_once '../../config.php';
require_once '../../model/model.php';
require_once '../../controller/conttroler.php';

try {
    $eventController = new EventController();
    $totalEvents = $eventController->getTotalEvents();
    $upcomingEvents = $eventController->getUpcomingEvents();
    $totalReservations = $eventController->getTotalReservations();
    $totalRevenue = $eventController->getTotalRevenue();
    
    // Get all events to calculate format distribution and reservations
    $events = $eventController->listEvents();
    
    // Initialize counters
    $onlineCount = 0;
    $offlineCount = 0;
    $reservationsByEvent = [];
    $eventNames = [];
    $eventReservationCounts = [];
    
    // Calculate event format distribution and get reservations
    if ($events) {
        foreach ($events as $eventData) {
            // Count event formats
            if ($eventData['event_format'] === 'online') {
                $onlineCount++;
            } else {
                $offlineCount++;
            }
            
            // Get reservations for this event
            $eventId = $eventData['event_id'];
            $eventReservations = $eventController->getReservationsByEvent($eventId);
            $reservationsByEvent[$eventId] = $eventReservations;
            
            // Store event name and reservation count for chart
            $eventNames[] = $eventData['event_title'];
            $eventReservationCounts[] = count($eventReservations);
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="ui.css">
    <style>
        .reservation-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .reservation-table th, 
        .reservation-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .reservation-table th {
            font-weight: 600;
            color: var(--text-dark);
            background-color: var(--light-bg);
        }
        
        .reservation-table tr:hover {
            background-color: var(--light-bg);
        }
        
        .chart-row {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-col {
            flex: 1;
            min-width: 300px;
        }
        
        @media (max-width: 992px) {
            .chart-row {
                flex-direction: column;
            }
        }
        
        .no-reservations {
            text-align: center;
            padding: 30px;
            color: var(--text-medium);
        }
        
        .event-header {
            background-color: var(--primary);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            margin-top: 20px;
        }
        
        .event-header h3 {
            margin: 0;
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
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="read.php">
                    <i class="fas fa-calendar-check"></i> Events
                </a>
            </li>
            <li>
                <a href="newEvent.php">
                    <i class="fas fa-plus-circle"></i> Create Event
                </a>
            </li>
            <li>
                <a href="../frontoffice/events.php#reservations" target="_blank">
                    <i class="fas fa-ticket-alt"></i> View Reservations
                </a>
            </li>
            <li>
                <a href="../frontoffice/events.php" target="_blank">
                    <i class="fas fa-globe"></i> Public Portal
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
        <h1 class="page-header">Dashboard</h1>

        <?php
        // Display success or error messages if they exist
        if (isset($_GET['delete_success']) && $_GET['delete_success'] == 'true') {
            echo '<div class="alert alert-success">Reservation deleted successfully.</div>';
        }
        if (isset($_GET['delete_error']) && $_GET['delete_error'] == 'true') {
            $errorMessage = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'An error occurred while deleting the reservation.';
            echo '<div class="alert alert-danger">' . $errorMessage . '</div>';
        }
        ?>

        <!-- Statistics Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalEvents; ?></div>
                <div class="stat-label">Total Events</div>
                <div class="stat-growth">All time events</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?php echo $upcomingEvents; ?></div>
                <div class="stat-label">Upcoming Events</div>
                <div class="stat-growth">Next 30 days</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?php echo $totalReservations; ?></div>
                <div class="stat-label">Total Reservations</div>
                <div class="stat-growth">Across all events</div>
            </div>

            <div class="stat-card">
                <div class="stat-value">$<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-growth">From all tickets</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="chart-row">
            <!-- Event Format Distribution Chart -->
            <div class="chart-col">
                <div class="chart-container">
                    <h2 class="card-title">Event Format Distribution</h2>
                    <div class="donut-chart">
                        <div class="chart-wrapper">
                            <canvas id="formatChart"></canvas>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: var(--primary);"></div>
                                <span>Online Events (<?php echo $onlineCount; ?>)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: var(--secondary);"></div>
                                <span>In-Person Events (<?php echo $offlineCount; ?>)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reservations Chart -->
            <div class="chart-col">
                <div class="chart-container">
                    <h2 class="card-title">Event Reservations</h2>
                    <div class="chart-wrapper">
                        <canvas id="reservationsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Quick Actions</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <a href="newEvent.php" class="btn btn-primary btn-block">
                            <i class="fas fa-plus-circle"></i> Add New Event
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="read.php" class="btn btn-success btn-block">
                            <i class="fas fa-list"></i> View All Events
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="../frontoffice/events.php#reservations" target="_blank" class="btn btn-info btn-block">
                            <i class="fas fa-ticket-alt"></i> Manage Reservations
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="../frontoffice/events.php" target="_blank" class="btn btn-secondary btn-block">
                            <i class="fas fa-globe"></i> View Public Portal
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservations List -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">List of Reservations</h2>
            </div>
            <div class="card-body">
                <?php 
                $hasReservations = false;
                
                foreach ($events as $eventData):
                    $eventId = $eventData['event_id'];
                    $eventReservations = $reservationsByEvent[$eventId] ?? [];
                    
                    if (!empty($eventReservations)):
                        $hasReservations = true;
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
                ?>
                <div class="event-header">
                    <h3><?php echo htmlspecialchars($event->getEventTitle()); ?></h3>
                    <p style="margin: 5px 0 0;">Date: <?php echo date("j F Y", strtotime($event->getStartDate())); ?> at <?php echo $event->getStartTime(); ?></p>
                </div>
                
                <table class="reservation-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Seats</th>
                            <th>Reservation Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventReservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['guest_name']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['guest_email']); ?></td>
                            <td><?php echo $reservation['seats_reserved']; ?></td>
                            <td><?php echo date("j M Y, H:i", strtotime($reservation['reservation_date'])); ?></td>
                            <td>
                                <form action="deleteReservation.php" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this reservation?');">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php 
                    endif;
                endforeach;
                
                if (!$hasReservations):
                ?>
                <div class="no-reservations">
                    <i class="fas fa-ticket-alt" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                    <p>No reservations have been made for any events yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Event Format Distribution Chart
        const formatCtx = document.getElementById("formatChart").getContext("2d");
        new Chart(formatCtx, {
            type: "doughnut",
            data: {
                labels: ["Online Events", "In-Person Events"],
                datasets: [{
                    data: [<?php echo $onlineCount; ?>, <?php echo $offlineCount; ?>],
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

        // Reservations Chart
        const reservationsCtx = document.getElementById("reservationsChart").getContext("2d");
        new Chart(reservationsCtx, {
            type: "bar",
            data: {
                labels: <?php echo json_encode(array_map(function($name) { 
                    // Shorten long event names for better display
                    return (strlen($name) > 20) ? substr($name, 0, 20) . '...' : $name;
                }, $eventNames)); ?>,
                datasets: [{
                    label: "Reservations",
                    data: <?php echo json_encode($eventReservationCounts); ?>,
                    backgroundColor: "var(--primary)",
                    borderWidth: 0,
                    borderRadius: 5,
                    barThickness: 20,
                    maxBarThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            display: true,
                            color: "rgba(0, 0, 0, 0.05)"
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
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
                        displayColors: false
                    }
                }
            }
        });

        // Sidebar toggle functionality
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
<?php
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?> 