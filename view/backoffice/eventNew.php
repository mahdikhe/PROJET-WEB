<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $eventTitle = isset($_POST['eventTitle']) ? trim($_POST['eventTitle']) : '';
        $eventType = isset($_POST['eventType']) ? trim($_POST['eventType']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $startDate = isset($_POST['startDate']) ? trim($_POST['startDate']) : '';
        $startTime = isset($_POST['startTime']) ? trim($_POST['startTime']) : '';
        $endDate = isset($_POST['endDate']) ? trim($_POST['endDate']) : '';
        $endTime = isset($_POST['endTime']) ? trim($_POST['endTime']) : '';
        $eventFormat = isset($_POST['eventFormat']) ? trim($_POST['eventFormat']) : '';
        $location = isset($_POST['location']) ? trim($_POST['location']) : '';
        $onlineUrl = isset($_POST['onlineUrl']) ? trim($_POST['onlineUrl']) : '';
        $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : 0;
        $ticketType = isset($_POST['ticketType']) ? trim($_POST['ticketType']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        
        // Validate required fields
        if (empty($eventTitle) || empty($eventType) || empty($description) || 
            empty($startDate) || empty($startTime) || empty($endDate) || 
            empty($endTime) || empty($eventFormat) || empty($ticketType) || $capacity <= 0) {
                
            header('Location: newEvent.php?error=' . urlencode('All required fields must be filled in'));
            exit;
        }
        
        // Validate format-specific fields
        if ($eventFormat === 'inPerson' && empty($location)) {
            header('Location: newEvent.php?error=' . urlencode('Location is required for in-person events'));
            exit;
        }
        
        if ($eventFormat === 'online' && empty($onlineUrl)) {
            header('Location: newEvent.php?error=' . urlencode('Online URL is required for online events'));
            exit;
        }
        
        // Validate dates
        $startDateTime = new DateTime("$startDate $startTime");
        $endDateTime = new DateTime("$endDate $endTime");
        
        if ($endDateTime <= $startDateTime) {
            header('Location: newEvent.php?error=' . urlencode('End date/time must be after start date/time'));
            exit;
        }
        
        // Validate price
        if ($ticketType === 'paid' && $price <= 0) {
            header('Location: newEvent.php?error=' . urlencode('Price must be greater than zero for paid events'));
            exit;
        }
        
        // Create Event object
        $event = new Event(
            $eventTitle,
            $eventType,
            $description,
            $startDate,
            $startTime,
            $endDate,
            $endTime,
            $eventFormat,
            $location,
            $onlineUrl,
            $capacity,
            $ticketType,
            $price
        );
        
        // Save event to database
        $eventController = new EventController();
        $result = $eventController->addEvent($event);
        
        if ($result) {
            // Success - redirect to event list
            header('Location: read.php?success=create');
            exit;
        } else {
            // Error - redirect back to form with error message
            header('Location: newEvent.php?error=' . urlencode('Failed to create event. Please try again.'));
            exit;
        }
    } else {
        // If not a POST request, redirect to the form
        header('Location: newEvent.php');
        exit;
    }
} catch (Exception $e) {
    // Handle unexpected errors
    header('Location: newEvent.php?error=' . urlencode($e->getMessage()));
    exit;
}

$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Event</title>
    <link rel="stylesheet" href="../stylse.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../functions.js"></script>
    <style>
        .event-form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
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
        
        .format-options {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .format-option {
            flex: 1;
            padding: 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .format-option:hover {
            border-color: var(--primary);
        }
        
        .format-option.selected {
            border-color: var(--primary);
            background-color: rgba(var(--primary-rgb), 0.1);
        }
        
        .format-option i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .ticket-type-options {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .ticket-type-option {
            flex: 1;
            padding: 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .ticket-type-option:hover {
            border-color: var(--primary);
        }
        
        .ticket-type-option.selected {
            border-color: var(--primary);
            background-color: rgba(var(--primary-rgb), 0.1);
        }
        
        .ticket-type-option i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .btn-block {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header-with-actions">
            <h1 class="page-header">Create New Event</h1>
            <div class="header-actions">
                <a href="read.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Events
                </a>
            </div>
        </div>';

    if (isset($_GET['error'])) {
        $html .= '<div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            ' . htmlspecialchars($_GET['error']) . '
        </div>';
    }

    $html .= '<div class="event-form-container">
        <form id="eventForm" action="eventNew.php" method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="eventTitle">Event Title</label>
                    <input type="text" id="eventTitle" name="eventTitle" class="form-control" required>
                    <div class="invalid-feedback">Please enter an event title</div>
                </div>
                
                <div class="form-group">
                    <label for="eventType">Event Type</label>
                    <input type="text" id="eventType" name="eventType" class="form-control" required>
                    <div class="invalid-feedback">Please enter an event type</div>
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                    <div class="invalid-feedback">Please enter a description</div>
                </div>
                
                <div class="form-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" name="startDate" class="form-control" required>
                    <div class="invalid-feedback">Please select a start date</div>
                </div>
                
                <div class="form-group">
                    <label for="startTime">Start Time</label>
                    <input type="time" id="startTime" name="startTime" class="form-control" required>
                    <div class="invalid-feedback">Please select a start time</div>
                </div>
                
                <div class="form-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" name="endDate" class="form-control" required>
                    <div class="invalid-feedback">Please select an end date</div>
                </div>
                
                <div class="form-group">
                    <label for="endTime">End Time</label>
                    <input type="time" id="endTime" name="endTime" class="form-control" required>
                    <div class="invalid-feedback">Please select an end time</div>
                </div>
                
                <div class="form-group full-width">
                    <label>Event Format</label>
                    <div class="format-options">
                        <div class="format-option" data-format="inPerson">
                            <i class="fas fa-building"></i>
                            <h4>In Person</h4>
                            <p>Physical location event</p>
                        </div>
                        <div class="format-option" data-format="online">
                            <i class="fas fa-laptop"></i>
                            <h4>Online</h4>
                            <p>Virtual event</p>
                        </div>
                    </div>
                    <input type="hidden" id="eventFormat" name="eventFormat" required>
                    <div class="invalid-feedback">Please select an event format</div>
                </div>
                
                <div class="form-group" id="locationGroup">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" class="form-control">
                    <div class="invalid-feedback">Please enter a location</div>
                </div>
                
                <div class="form-group" id="onlineUrlGroup" style="display: none;">
                    <label for="onlineUrl">Online URL</label>
                    <input type="url" id="onlineUrl" name="onlineUrl" class="form-control">
                    <div class="invalid-feedback">Please enter a valid URL</div>
                </div>
                
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" class="form-control" min="1" required>
                    <div class="invalid-feedback">Please enter a valid capacity</div>
                </div>
                
                <div class="form-group full-width">
                    <label>Ticket Type</label>
                    <div class="ticket-type-options">
                        <div class="ticket-type-option" data-type="free">
                            <i class="fas fa-ticket-alt"></i>
                            <h4>Free</h4>
                            <p>No charge for tickets</p>
                        </div>
                        <div class="ticket-type-option" data-type="paid">
                            <i class="fas fa-dollar-sign"></i>
                            <h4>Paid</h4>
                            <p>Charge for tickets</p>
                        </div>
                    </div>
                    <input type="hidden" id="ticketType" name="ticketType" required>
                    <div class="invalid-feedback">Please select a ticket type</div>
                </div>
                
                <div class="form-group" id="priceGroup" style="display: none;">
                    <label for="price">Price per Ticket ($)</label>
                    <input type="number" id="price" name="price" class="form-control" min="0" step="0.01">
                    <div class="invalid-feedback">Please enter a valid price</div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-plus"></i> Create Event
            </button>
        </form>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize form validation
    setupFormValidation("eventForm", {
        eventTitle: { type: "text", options: { minLength: 3 } },
        eventType: { type: "text", options: { minLength: 2 } },
        description: { type: "text", options: { minLength: 10 } },
        startDate: { type: "date" },
        startTime: { type: "time" },
        endDate: { type: "date" },
        endTime: { type: "time" },
        eventFormat: { type: "text" },
        location: { type: "text", options: { minLength: 3 } },
        onlineUrl: { type: "url" },
        capacity: { type: "number", options: { min: 1 } },
        ticketType: { type: "text" },
        price: { type: "number", options: { min: 0 } }
    });
    
    // Format selection
    const formatOptions = document.querySelectorAll(".format-option");
    const eventFormatInput = document.getElementById("eventFormat");
    const locationGroup = document.getElementById("locationGroup");
    const onlineUrlGroup = document.getElementById("onlineUrlGroup");
    
    formatOptions.forEach(option => {
        option.addEventListener("click", function() {
            formatOptions.forEach(opt => opt.classList.remove("selected"));
            this.classList.add("selected");
            const format = this.dataset.format;
            eventFormatInput.value = format;
            
            if (format === "inPerson") {
                locationGroup.style.display = "block";
                onlineUrlGroup.style.display = "none";
                document.getElementById("location").required = true;
                document.getElementById("onlineUrl").required = false;
            } else {
                locationGroup.style.display = "none";
                onlineUrlGroup.style.display = "block";
                document.getElementById("location").required = false;
                document.getElementById("onlineUrl").required = true;
            }
        });
    });
    
    // Ticket type selection
    const ticketTypeOptions = document.querySelectorAll(".ticket-type-option");
    const ticketTypeInput = document.getElementById("ticketType");
    const priceGroup = document.getElementById("priceGroup");
    
    ticketTypeOptions.forEach(option => {
        option.addEventListener("click", function() {
            ticketTypeOptions.forEach(opt => opt.classList.remove("selected"));
            this.classList.add("selected");
            const type = this.dataset.type;
            ticketTypeInput.value = type;
            
            if (type === "paid") {
                priceGroup.style.display = "block";
                document.getElementById("price").required = true;
            } else {
                priceGroup.style.display = "none";
                document.getElementById("price").required = false;
            }
        });
    });
    
    // Date validation
    setupDateTimeValidation("startDate", "startTime");
    setupDateTimeValidation("endDate", "endTime");
});
</script>
</body>
</html>';

echo $html;
?>