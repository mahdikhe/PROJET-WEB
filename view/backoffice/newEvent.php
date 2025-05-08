<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    $eventController = new EventController();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        $result = $eventController->createEvent($eventData);
        
        if ($result) {
            header('Location: read.php?success=1');
            exit;
        } else {
            header('Location: newEvent.php?error=Failed to create event');
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Event</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="ui.css">
    <style>
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-section-title {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--text-dark);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        /* Date/time fields in Firefox fix */
        input[type="date"], 
        input[type="time"] {
            padding: 0.48rem 0.75rem;
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
                <a href="newEvent.php" class="active">
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
        <div class="page-header">
            <h1>Create New Event</h1>
            <p>Fill in the details below to create a new event</p>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Error message display -->
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <p><?php echo htmlspecialchars($_GET['error']); ?></p>
                </div>
                <?php endif; ?>
                
                <form id="create-event-form" action="newEvent.php" method="POST">
                    
                    <!-- Event Basic Information -->
                    <div class="form-section">
                        <h2 class="form-section-title">Event Information</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="eventTitle">Event Title*</label>
                                <input type="text" id="eventTitle" name="eventTitle" class="form-control" placeholder="Enter a descriptive title">
                            </div>
                            <div class="form-group">
                                <label for="eventType">Event Type*</label>
                                <select id="eventType" name="eventType" class="form-control">
                                    <option value="">-- Select event type --</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="conference">Conference</option>
                                    <option value="seminar">Seminar</option>
                                    <option value="networking">Networking Event</option>
                                    <option value="forum">Forum</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Event Description*</label>
                            <textarea id="description" name="description" class="form-control" rows="4" placeholder="Describe your event in detail"></textarea>
                        </div>
                    </div>

                    <!-- Date and Time -->
                    <div class="form-section">
                        <h2 class="form-section-title">Date and Time</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="startDate">Start Date*</label>
                                <input type="date" id="startDate" name="startDate" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="startTime">Start Time*</label>
                                <input type="time" id="startTime" name="startTime" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="endDate">End Date*</label>
                                <input type="date" id="endDate" name="endDate" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="endTime">End Time*</label>
                                <input type="time" id="endTime" name="endTime" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Location and Capacity -->
                    <div class="form-section">
                        <h2 class="form-section-title">Location and Attendance</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="eventFormat">Event Format*</label>
                                <select id="eventFormat" name="eventFormat" class="form-control" onchange="toggleLocationFields()">
                                    <option value="">-- Select format --</option>
                                    <option value="inPerson">In Person</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div class="form-group" id="locationGroup">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" class="form-control" placeholder="Enter venue address">
                            </div>
                            <div class="form-group" id="onlineUrlGroup" style="display: none;">
                                <label for="onlineUrl">Online Meeting URL</label>
                                <input type="url" id="onlineUrl" name="onlineUrl" class="form-control" placeholder="Enter meeting link">
                            </div>
                            <div class="form-group">
                                <label for="capacity">Maximum Capacity*</label>
                                <input type="number" id="capacity" name="capacity" class="form-control" min="1" placeholder="Number of attendees">
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="form-section">
                        <h2 class="form-section-title">Ticket Information</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="ticketType">Ticket Type*</label>
                                <select id="ticketType" name="ticketType" class="form-control" onchange="togglePriceField()">
                                    <option value="">-- Select ticket type --</option>
                                    <option value="free">Free</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                            <div class="form-group" id="priceGroup" style="display: none;">
                                <label for="price">Ticket Price ($)</label>
                                <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" value="0" placeholder="Enter ticket price">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="read.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../functions.js"></script>
    <script>
        // Initialize form validation
        document.addEventListener('DOMContentLoaded', function() {
            setupFormValidation('create-event-form', {
                'eventTitle': { type: 'text', options: { minLength: 3 } },
                'eventType': { type: 'text' },
                'description': { type: 'text', options: { minLength: 10 } },
                'startDate': { type: 'date' },
                'startTime': { type: 'time' },
                'endDate': { type: 'date' },
                'endTime': { type: 'time' },
                'eventFormat': { type: 'text' },
                'location': { type: 'text' },
                'onlineUrl': { type: 'url' },
                'capacity': { type: 'number', options: { min: 1 } },
                'ticketType': { type: 'text' },
                'price': { type: 'number', options: { min: 0 } }
            });
            
            // Setup date/time validation
            setupDateTimeValidation('startDate', 'startTime');
            setupDateTimeValidation('endDate', 'endTime');
        });

        // Toggle location fields based on event format
        function toggleLocationFields() {
            const eventFormat = document.getElementById('eventFormat').value;
            const locationGroup = document.getElementById('locationGroup');
            const onlineUrlGroup = document.getElementById('onlineUrlGroup');
            const locationInput = document.getElementById('location');
            const onlineUrlInput = document.getElementById('onlineUrl');
            
            if (eventFormat === 'online') {
                locationGroup.style.display = 'none';
                onlineUrlGroup.style.display = 'block';
                locationInput.required = false;
                onlineUrlInput.required = true;
            } else if (eventFormat === 'inPerson') {
                locationGroup.style.display = 'block';
                onlineUrlGroup.style.display = 'none';
                locationInput.required = true;
                onlineUrlInput.required = false;
            }
        }
        
        // Toggle price field based on ticket type
        function togglePriceField() {
            const ticketType = document.getElementById('ticketType').value;
            const priceGroup = document.getElementById('priceGroup');
            const priceInput = document.getElementById('price');
            
            if (ticketType === 'paid') {
                priceGroup.style.display = 'block';
                priceInput.required = true;
            } else {
                priceGroup.style.display = 'none';
                priceInput.required = false;
                priceInput.value = 0;
            }
        }
        
        // Validate end date/time is after start date/time
        document.getElementById('create-event-form').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('startDate').value + ' ' + document.getElementById('startTime').value);
            const endDate = new Date(document.getElementById('endDate').value + ' ' + document.getElementById('endTime').value);
            
            if (endDate <= startDate) {
                e.preventDefault();
                alert('End date and time must be after start date and time');
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
    // Handle unexpected errors
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="ui.css">
</head>
<body>
    <div class="container" style="margin-left: 0;">
        <div class="alert alert-danger">
            <h1>Error</h1>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <a href="read.php" class="btn btn-primary">Back to Events</a>
        </div>
    </div>
</body>
</html>';
}
?>