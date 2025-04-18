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
?>