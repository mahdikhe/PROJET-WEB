<<<<<<< HEAD
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
=======
<?php
include "../../config.php";
include "../../model/model.php";
include "../../controller/conttroler.php";

// Handle event deletion if form is submitted
if (isset($_POST['event_id'])) { 
    $eventId = $_POST['event_id'];

    try {
        $eventController = new EventController();
        
        // First, check if the event exists
        $event = $eventController->getEvent($eventId);
        
        if (!$event) {
            $deleteError = "Event not found.";
        } else {
            // Delete the event
            $eventController->deleteEvent($eventId);
            
            // Redirect to prevent form resubmission
            header("Location: read.php?delete_success=true");
            exit();
        }
    } catch (Exception $e) {
        $deleteError = $e->getMessage();
    }
}

// If there's an error, display it
if (isset($deleteError)) {
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
            <p>' . htmlspecialchars($deleteError) . '</p>
        </div>
    </div>
</body>
</html>';
}
?>
>>>>>>> bb0192a77f41df7c722502d7c9fbaadb5c90f577
