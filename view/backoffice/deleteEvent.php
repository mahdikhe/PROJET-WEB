<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: read.php');
        exit;
    }
    
    // Check if event_id is provided
    if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
        throw new Exception("Event ID is required");
    }
    
    $eventId = (int)$_POST['event_id'];
    
    // Create controller instance
    $eventController = new EventController();
    
    // Check if the event exists
    $event = $eventController->getEvent($eventId);
    if (!$event) {
        throw new Exception("Event not found");
    }
    
    // Delete the event
    $success = $eventController->deleteEvent($eventId);
    
    if ($success) {
        // Redirect to events list with success message
        header('Location: read.php?delete_success=true');
        exit;
    } else {
        throw new Exception("Failed to delete event. Please try again.");
    }
} catch (Exception $e) {
    // Redirect with error message
    header('Location: read.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>
