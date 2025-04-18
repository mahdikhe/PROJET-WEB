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
