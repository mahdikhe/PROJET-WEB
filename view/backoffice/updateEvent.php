<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
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
        if ($eventId <= 0) {
            throw new Exception("Invalid event ID");
        }
        
        if (empty($eventTitle) || empty($eventType) || empty($description) || 
            empty($startDate) || empty($startTime) || empty($endDate) || 
            empty($endTime) || empty($eventFormat) || empty($ticketType) || $capacity <= 0) {
                
            header("Location: editEvent.php?event_id=$eventId&error=" . urlencode('All required fields must be filled in'));
            exit;
        }
        
        // Validate format-specific fields
        if ($eventFormat === 'inPerson' && empty($location)) {
            header("Location: editEvent.php?event_id=$eventId&error=" . urlencode('Location is required for in-person events'));
            exit;
        }
        
        if ($eventFormat === 'online' && empty($onlineUrl)) {
            header("Location: editEvent.php?event_id=$eventId&error=" . urlencode('Online URL is required for online events'));
            exit;
        }
        
        // Validate dates
        $startDateTime = new DateTime("$startDate $startTime");
        $endDateTime = new DateTime("$endDate $endTime");
        
        if ($endDateTime <= $startDateTime) {
            header("Location: editEvent.php?event_id=$eventId&error=" . urlencode('End date/time must be after start date/time'));
            exit;
        }
        
        // Validate price
        if ($ticketType === 'paid' && $price <= 0) {
            header("Location: editEvent.php?event_id=$eventId&error=" . urlencode('Price must be greater than zero for paid events'));
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
            $price,
            $eventId
        );
        
        // Update event in database
        $eventController = new EventController();
        $result = $eventController->updateEvent($event);
        
        if ($result) {
            // Success - redirect to event list
            header('Location: read.php?edit_success=true');
            exit;
        } else {
            // Error - redirect back to form with error message
            header("Location: editEvent.php?event_id=$eventId&error=" . urlencode('Failed to update event. Please try again.'));
            exit;
        }
    } else {
        // If not a POST request, redirect to the event list
        header('Location: read.php');
        exit;
    }
} catch (Exception $e) {
    // Handle unexpected errors
    header('Location: read.php?error=' . urlencode($e->getMessage()));
    exit;
}
?> 