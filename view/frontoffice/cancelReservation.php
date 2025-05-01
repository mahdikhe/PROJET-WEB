<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

try {
    // Check if reservation ID is provided
    if (!isset($_POST['reservation_id']) || empty($_POST['reservation_id'])) {
        throw new Exception("Reservation ID is required");
    }
    
    $reservationId = $_POST['reservation_id'];
    $eventController = new EventController();
    
    // Delete the reservation
    $success = $eventController->deleteReservation($reservationId);
    
    if ($success) {
        // Redirect to events page with success message
        header("Location: events.php?success=delete#reservations");
        exit;
    } else {
        throw new Exception("Failed to cancel reservation");
    }
} catch (Exception $e) {
    // Redirect with error message
    header("Location: events.php?error=" . urlencode($e->getMessage()) . "#reservations");
    exit;
}
?> 