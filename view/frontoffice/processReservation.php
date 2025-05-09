<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // Redirect back to events page if not a POST request
        header("Location: events.php");
        exit;
    }
    
    // Get form data
    $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $seats = isset($_POST['seats']) ? (int)$_POST['seats'] : 1;
    $specialRequests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';
    
    // Validate data
    if ($eventId <= 0) {
        throw new Exception("Invalid event ID.");
    }
    
    if (empty($name) || empty($email)) {
        header("Location: makeReservation.php?event_id=$eventId&error=fields");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: makeReservation.php?event_id=$eventId&error=email");
        exit;
    }
    
    if ($seats <= 0) {
        header("Location: makeReservation.php?event_id=$eventId&error=seats");
        exit;
    }
    
    // Create controller instance
    $eventController = new EventController();
    
    // Check if event exists
    $event = $eventController->getEvent($eventId);
    if (!$event) {
        throw new Exception("Event not found.");
    }
    
    // Check if there are enough available seats
    $availableSeats = $eventController->getEventAvailableSeats($eventId);
    if ($seats > $availableSeats) {
        header("Location: makeReservation.php?event_id=$eventId&error=seats");
        exit;
    }
    
    // Check if user already has a reservation for this event
    $userReservations = $eventController->getReservationsByEmail($email);
    foreach ($userReservations as $reservation) {
        if ($reservation['event_id'] == $eventId) {
            header("Location: makeReservation.php?event_id=$eventId&error=exists");
            exit;
        }
    }
    
    // Create the reservation
    $reservationId = $eventController->addReservation($eventId, $name, $email, $seats);
    
    if ($reservationId) {
        // Success - redirect to the reservation page with success message
        header("Location: reservations.php?email=$email&success=reserve");
        exit;
    } else {
        // Error - redirect back to the form
        header("Location: makeReservation.php?event_id=$eventId&error=failed");
        exit;
    }
    
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