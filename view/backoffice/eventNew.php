<<<<<<< HEAD
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
=======
<?php
include "../../config.php";
include "../../model/model.php";
include "../../controller/conttroler.php";

try {
    $eventController = new EventController();
    
    // Handle form submission
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
                (float)($_POST['price'] ?? 0)
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
            
            // Add the event
            $eventController->addEvent($event);
            
            // Redirect with success message
            header("Location: read.php?add_success=true");
            exit();
        } catch (Exception $e) {
            // Store the error message to display on the form
            $error = $e->getMessage();
        }
    }

    if (isset($_GET['id']) && !empty($_GET['id'])) {
        try {
            $eventId = $_GET['id'];
            $eventData = $eventController->showEvent($eventId);
            
            if ($eventData) {
                
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
                    $eventData['online_url'],
                    (int)$eventData['capacity'],
                    $eventData['ticket_type'],
                    (float)$eventData['price'],
                    (int)$eventData['event_id']
                );
                
                // If this is a GET request and we're editing, we would display the form
                // filled with the event data here
            } else {
                throw new Exception("Event not found");
            }
        } catch (Exception $e) {
            $error = urlencode($e->getMessage());
            header("Location: event.html?error=" . $error);
            exit();
        }
    }
} catch (Exception $e) {
    $error = urlencode($e->getMessage());
    header("Location: newEvent.html?error=" . $error);
    exit();
}
>>>>>>> bb0192a77f41df7c722502d7c9fbaadb5c90f577
?>