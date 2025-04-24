<<<<<<< HEAD
<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    $eventController = new EventController();
    
    // Get event_id from URL
    $eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
    
    if ($eventId <= 0) {
        throw new Exception("Invalid event ID");
    }
    
    // Get event details
    $eventData = $eventController->getEvent($eventId);
    
    if (!$eventData) {
        throw new Exception("Event not found");
    }
    
    // Create Event object
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - <?php echo htmlspecialchars($event->getEventTitle()); ?></title>
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
                <a href="newEvent.php">
                    <i class="fas fa-plus-circle"></i> Create Event
                </a>
            </li>
            <li>
                <a href="../frontoffice/events.php#reservations">
                    <i class="fas fa-ticket-alt"></i> Reservations
                </a>
            </li>
            <li>
                <a href="../frontoffice/events.php">
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
            <h1>Edit Event</h1>
            <p>Update event details for "<?php echo htmlspecialchars($event->getEventTitle()); ?>"</p>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Error message display -->
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <p><?php echo htmlspecialchars($_GET['error']); ?></p>
                </div>
                <?php endif; ?>
                
                <form id="edit-event-form" action="updateEvent.php" method="POST">
                    <input type="hidden" name="event_id" value="<?php echo $event->getEventId(); ?>">
                    
                    <!-- Event Basic Information -->
                    <div class="form-section">
                        <h2 class="form-section-title">Event Information</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="eventTitle">Event Title*</label>
                                <input type="text" id="eventTitle" name="eventTitle" class="form-control" value="<?php echo htmlspecialchars($event->getEventTitle()); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="eventType">Event Type*</label>
                                <select id="eventType" name="eventType" class="form-control" required>
                                    <option value="">-- Select event type --</option>
                                    <option value="workshop" <?php echo $event->getEventType() === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                                    <option value="conference" <?php echo $event->getEventType() === 'conference' ? 'selected' : ''; ?>>Conference</option>
                                    <option value="seminar" <?php echo $event->getEventType() === 'seminar' ? 'selected' : ''; ?>>Seminar</option>
                                    <option value="networking" <?php echo $event->getEventType() === 'networking' ? 'selected' : ''; ?>>Networking Event</option>
                                    <option value="forum" <?php echo $event->getEventType() === 'forum' ? 'selected' : ''; ?>>Forum</option>
                                    <option value="other" <?php echo $event->getEventType() === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Event Description*</label>
                            <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($event->getDescription()); ?></textarea>
                        </div>
                    </div>

                    <!-- Date and Time -->
                    <div class="form-section">
                        <h2 class="form-section-title">Date and Time</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="startDate">Start Date*</label>
                                <input type="date" id="startDate" name="startDate" class="form-control" value="<?php echo $event->getStartDate(); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="startTime">Start Time*</label>
                                <input type="time" id="startTime" name="startTime" class="form-control" value="<?php echo $event->getStartTime(); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="endDate">End Date*</label>
                                <input type="date" id="endDate" name="endDate" class="form-control" value="<?php echo $event->getEndDate(); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="endTime">End Time*</label>
                                <input type="time" id="endTime" name="endTime" class="form-control" value="<?php echo $event->getEndTime(); ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Location and Capacity -->
                    <div class="form-section">
                        <h2 class="form-section-title">Location and Attendance</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="eventFormat">Event Format*</label>
                                <select id="eventFormat" name="eventFormat" class="form-control" required onchange="toggleLocationFields()">
                                    <option value="">-- Select format --</option>
                                    <option value="inPerson" <?php echo $event->getEventFormat() === 'inPerson' ? 'selected' : ''; ?>>In Person</option>
                                    <option value="online" <?php echo $event->getEventFormat() === 'online' ? 'selected' : ''; ?>>Online</option>
                                </select>
                            </div>
                            <div class="form-group" id="locationGroup" <?php echo $event->getEventFormat() === 'online' ? 'style="display: none;"' : ''; ?>>
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($event->getLocation()); ?>">
                            </div>
                            <div class="form-group" id="onlineUrlGroup" <?php echo $event->getEventFormat() !== 'online' ? 'style="display: none;"' : ''; ?>>
                                <label for="onlineUrl">Online Meeting URL</label>
                                <input type="url" id="onlineUrl" name="onlineUrl" class="form-control" value="<?php echo htmlspecialchars($event->getOnlineUrl()); ?>">
                            </div>
                            <div class="form-group">
                                <label for="capacity">Maximum Capacity*</label>
                                <input type="number" id="capacity" name="capacity" class="form-control" value="<?php echo $event->getCapacity(); ?>" required min="1">
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="form-section">
                        <h2 class="form-section-title">Ticket Information</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="ticketType">Ticket Type*</label>
                                <select id="ticketType" name="ticketType" class="form-control" required onchange="togglePriceField()">
                                    <option value="">-- Select ticket type --</option>
                                    <option value="free" <?php echo $event->getTicketType() === 'free' ? 'selected' : ''; ?>>Free</option>
                                    <option value="paid" <?php echo $event->getTicketType() === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                </select>
                            </div>
                            <div class="form-group" id="priceGroup" <?php echo $event->getTicketType() !== 'paid' ? 'style="display: none;"' : ''; ?>>
                                <label for="price">Ticket Price ($)</label>
                                <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" value="<?php echo $event->getPrice(); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="read.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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
            } else {
                locationGroup.style.display = 'block';
                onlineUrlGroup.style.display = 'block';
                locationInput.required = true;
                onlineUrlInput.required = true;
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
        document.getElementById('edit-event-form').addEventListener('submit', function(e) {
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
=======
<?php
include "../../config.php";
include "../../model/model.php";
include "../../controller/conttroler.php";

try {
    $eventController = new EventController();
    
    // Check if event_id is provided
    if (!isset($_GET['event_id']) && !isset($_POST['event_id'])) {
        throw new Exception("Event ID is required");
    }
    
    $event_id = isset($_GET['event_id']) ? $_GET['event_id'] : $_POST['event_id'];
    
    // Handle form submission for updating
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            // Validate required fields
            $required_fields = ['eventTitle', 'eventType', 'description', 'startDate', 'startTime',
                               'endDate', 'endTime', 'eventFormat', 'capacity', 'ticketType'];
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field is required");
                }
            }
            
            // Create Event object from form data
            $event = new Event(
                $_POST['eventTitle'],
                $_POST['eventType'],
                $_POST['description'],
                $_POST['startDate'],
                $_POST['startTime'],
                $_POST['endDate'],
                $_POST['endTime'],
                $_POST['eventFormat'],
                $_POST['location'] ?? '',
                $_POST['onlineUrl'] ?? '',
                (int)$_POST['capacity'],
                $_POST['ticketType'],
                (float)($_POST['price'] ?? 0),
                (int)$event_id
            );
            
            // Additional validation
            if ($event->getTicketType() === 'paid' && $event->getPrice() <= 0) {
                throw new Exception("Price is required for paid events");
            }
            
            if ($event->getEventFormat() === 'online' && empty($event->getOnlineUrl())) {
                throw new Exception("Online URL is required for online events");
            }
            
            if ($event->getEventFormat() === 'inPerson' && empty($event->getLocation())) {
                throw new Exception("Location is required for in-person events");
            }
            
            // Update the event
            $eventController->updateEvent($event);
            
            // Redirect with success message
            header("Location: read.php?edit_success=true");
            exit();
        } catch (Exception $e) {
            // Store the error message to display on the form
            $error = $e->getMessage();
        }
    }
    
    // Fetch event data for editing
    $eventData = $eventController->getEvent($event_id);
    
    if (!$eventData) {
        throw new Exception("Event not found");
    }
    
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
    
    // HTML for the edit form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Event</title>
        <link rel="stylesheet" href="stylse.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .alert-danger {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .form-row {
                margin-bottom: 20px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
            }
            input[type="text"], 
            input[type="date"],
            input[type="time"],
            input[type="number"],
            input[type="url"],
            textarea,
            select {
                width: 100%;
                padding: 10px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                font-size: 16px;
            }
            textarea {
                min-height: 100px;
            }
            .conditional-field {
                display: none;
            }
            .btn-container {
                display: flex;
                justify-content: space-between;
                margin-top: 30px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="page-header">Edit Event</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="editEvent.php">
                <input type="hidden" name="event_id" value="<?php echo $event->getEventId(); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="eventTitle">Event Title *</label>
                        <input type="text" id="eventTitle" name="eventTitle" value="<?php echo htmlspecialchars($event->getEventTitle()); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="eventType">Event Type *</label>
                        <input type="text" id="eventType" name="eventType" value="<?php echo htmlspecialchars($event->getEventType()); ?>" placeholder="e.g. Conference, Workshop, Concert" required>
                        <small>You can use comma-separated values for multiple types</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required><?php echo htmlspecialchars($event->getDescription()); ?></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="startDate">Start Date *</label>
                        <input type="date" id="startDate" name="startDate" value="<?php echo $event->getStartDate(); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="startTime">Start Time *</label>
                        <input type="time" id="startTime" name="startTime" value="<?php echo $event->getStartTime(); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="endDate">End Date *</label>
                        <input type="date" id="endDate" name="endDate" value="<?php echo $event->getEndDate(); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="endTime">End Time *</label>
                        <input type="time" id="endTime" name="endTime" value="<?php echo $event->getEndTime(); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="eventFormat">Event Format *</label>
                        <select id="eventFormat" name="eventFormat" required>
                            <option value="online" <?php echo ($event->getEventFormat() === 'online') ? 'selected' : ''; ?>>Online</option>
                            <option value="inPerson" <?php echo ($event->getEventFormat() === 'inPerson') ? 'selected' : ''; ?>>In Person</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row" id="locationField" <?php echo ($event->getEventFormat() === 'inPerson') ? '' : 'style="display:none;"'; ?>>
                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($event->getLocation()); ?>">
                    </div>
                </div>
                
                <div class="form-row" id="onlineUrlField" <?php echo ($event->getEventFormat() === 'online') ? '' : 'style="display:none;"'; ?>>
                    <div class="form-group">
                        <label for="onlineUrl">Online URL *</label>
                        <input type="url" id="onlineUrl" name="onlineUrl" value="<?php echo htmlspecialchars($event->getOnlineUrl()); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="capacity">Capacity *</label>
                        <input type="number" id="capacity" name="capacity" value="<?php echo $event->getCapacity(); ?>" min="1" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ticketType">Ticket Type *</label>
                        <select id="ticketType" name="ticketType" required>
                            <option value="free" <?php echo ($event->getTicketType() === 'free') ? 'selected' : ''; ?>>Free</option>
                            <option value="paid" <?php echo ($event->getTicketType() === 'paid') ? 'selected' : ''; ?>>Paid</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row" id="priceField" <?php echo ($event->getTicketType() === 'paid') ? '' : 'style="display:none;"'; ?>>
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" value="<?php echo $event->getPrice(); ?>" min="0" step="0.01">
                    </div>
                </div>
                
                <div class="btn-container">
                    <a href="read.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
        
        <script>
            // Show/hide conditional fields based on selections
            document.addEventListener('DOMContentLoaded', function() {
                const eventFormatSelect = document.getElementById('eventFormat');
                const locationField = document.getElementById('locationField');
                const onlineUrlField = document.getElementById('onlineUrlField');
                
                eventFormatSelect.addEventListener('change', function() {
                    if (this.value === 'inPerson') {
                        locationField.style.display = 'block';
                        onlineUrlField.style.display = 'none';
                    } else {
                        locationField.style.display = 'none';
                        onlineUrlField.style.display = 'block';
                    }
                });
                
                const ticketTypeSelect = document.getElementById('ticketType');
                const priceField = document.getElementById('priceField');
                
                ticketTypeSelect.addEventListener('change', function() {
                    if (this.value === 'paid') {
                        priceField.style.display = 'block';
                    } else {
                        priceField.style.display = 'none';
                    }
                });
                
                // Validate end date/time is after start date/time
                const form = document.querySelector('form');
                form.addEventListener('submit', function(event) {
                    const startDate = document.getElementById('startDate').value;
                    const startTime = document.getElementById('startTime').value;
                    const endDate = document.getElementById('endDate').value;
                    const endTime = document.getElementById('endTime').value;
                    
                    const startDateTime = new Date(`${startDate}T${startTime}`);
                    const endDateTime = new Date(`${endDate}T${endTime}`);
                    
                    if (endDateTime <= startDateTime) {
                        alert('End date and time must be after start date and time');
                        event.preventDefault();
                        return false;
                    }
                    
                    // Validate other conditional fields
                    const eventFormat = document.getElementById('eventFormat').value;
                    if (eventFormat === 'online') {
                        const onlineUrl = document.getElementById('onlineUrl').value;
                        if (!onlineUrl) {
                            alert('Online URL is required for online events');
                            event.preventDefault();
                            return false;
                        }
                    } else {
                        const location = document.getElementById('location').value;
                        if (!location) {
                            alert('Location is required for in-person events');
                            event.preventDefault();
                            return false;
                        }
                    }
                    
                    const ticketType = document.getElementById('ticketType').value;
                    if (ticketType === 'paid') {
                        const price = document.getElementById('price').value;
                        if (!price || price <= 0) {
                            alert('Price must be greater than 0 for paid events');
                            event.preventDefault();
                            return false;
                        }
                    }
                });
            });
        </script>
    </body>
    </html>
<?php
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
                <a href="read.php" class="btn btn-primary">Back to Event List</a>
            </div>
        </div>
    </body>
    </html>';
}
>>>>>>> bb0192a77f41df7c722502d7c9fbaadb5c90f577
?>