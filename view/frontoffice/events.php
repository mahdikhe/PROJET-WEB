<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    $eventController = new EventController();
    
    // Get all events
    $events = $eventController->listEvents();
    
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
    <link rel="stylesheet" href="../stylse.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .main-nav {
            display: flex;
            gap: 30px;
        }
        
        .main-nav a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
        }
        
        .main-nav a.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .no-events {
            text-align: center;
            padding: 50px 0;
            color: var(--text-medium);
        }
        
        .no-events i {
            font-size: 48px;
            margin-bottom: 20px;
            color: var(--border-color);
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .event-cover {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
            background-color: var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-medium);
            font-size: 48px;
        }
        
        .event-details {
            padding: 20px;
        }
        
        .event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }
        
        .attendees {
            color: var(--text-medium);
            font-size: 14px;
        }
        
        .footer {
            background-color: var(--background);
            padding: 60px 0 30px;
            margin-top: 60px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section h3 {
            color: var(--text-dark);
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .footer-section h4 {
            color: var(--text-dark);
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .footer-section p {
            color: var(--text-medium);
            line-height: 1.6;
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-section ul li {
            margin-bottom: 10px;
        }
        
        .footer-section ul li a {
            color: var(--text-medium);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-section ul li a:hover {
            color: var(--primary);
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
        }
        
        .social-icons a {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            transition: all 0.2s;
        }
        
        .social-icons a:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            color: var(--text-medium);
            font-size: 14px;
        }

        .event-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .event-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 10px 0;
            color: var(--text-dark);
        }

        .event-date, .event-location {
            color: var(--text-medium);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .event-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 12px 0;
        }

        .tag {
            background-color: var(--background);
            color: var(--text-medium);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .btn-register {
            background-color: var(--primary);
            color: white;
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .main-nav, .auth-buttons {
                width: 100%;
                justify-content: space-between;
            }
        }

        .tab-container {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-medium);
            transition: all 0.2s;
        }

        .tab-button.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Header/Navigation -->
    <header>
        <div class="container header-container">
            <a href="#" class="logo">
                <h2>Student Events Portal</h2>
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
            <div class="card event-card">
                <div class="event-cover">
                    <i class="fas <?php echo $iconClass; ?>"></i>
                </div>
                <div class="event-details">
                    <div class="event-date">
                        <i class="far fa-calendar"></i> <?php echo $startDate; ?> at <?php echo $event->getStartTime(); ?>
                    </div>
                    <div class="event-title"><?php echo htmlspecialchars($event->getEventTitle()); ?></div>
                    <div class="event-location">
                        <i class="fas <?php echo $iconClass; ?>"></i> 
                        <?php echo htmlspecialchars($event->getEventFormat() === 'online' ? 'Online Event' : $event->getLocation()); ?>
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
                            <i class="fas fa-user"></i> <?php echo $totalReservations; ?> Attendees |
                            <i class="fas fa-chair"></i> <?php echo $availableSeats; ?> Available
                        </div>
                        <a href="makeReservation.php?event_id=<?php echo $event->getEventId(); ?>" class="btn btn-primary btn-sm btn-register">
                            Register
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>Student Events Portal</h3>
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
    });
    </script>
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