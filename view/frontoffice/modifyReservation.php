<?php
include_once dirname(dirname(__DIR__)) . "/config.php";
include_once dirname(dirname(__DIR__)) . "/model/model.php";
include_once dirname(dirname(__DIR__)) . "/controller/conttroler.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $eventController = new EventController();
    
    // Check if all required parameters are present
    if (!isset($_POST['reservation_id']) || !isset($_POST['event_id']) || !isset($_POST['action'])) {
        throw new Exception("Missing required parameters");
    }
    
    $reservation_id = $_POST['reservation_id'];
    $event_id = $_POST['event_id'];
    $action = $_POST['action'];
    
    // Get the event details
    $event = $eventController->getEvent($event_id);
    if (!$event) {
        throw new Exception("Event not found");
    }
    
    // Check if modification is allowed (24 hours before event)
    $eventDateTime = new DateTime($event['start_date'] . ' ' . $event['start_time']);
    $currentDateTime = new DateTime();
    $interval = $currentDateTime->diff($eventDateTime);
    $canModify = $interval->days >= 1 || ($interval->days == 0 && $interval->h >= 24);
    
    if (!$canModify) {
        throw new Exception("Reservation cannot be modified less than 24 hours before the event");
    }
    
    // Handle different actions
    switch ($action) {
        case 'update':
            if (!isset($_POST['new_seats'])) {
                throw new Exception("Missing number of seats");
            }
            
            $new_seats = intval($_POST['new_seats']);
            if ($new_seats < 1 || $new_seats > 10) {
                throw new Exception("Invalid number of seats");
            }
            
            // Get current reservation
            $reservation = $eventController->getReservation($reservation_id);
            if (!$reservation) {
                throw new Exception("Reservation not found");
            }
            
            // Check if there are enough available seats
            $availableSeats = $eventController->getEventAvailableSeats($event_id);
            $seatsToAdd = $new_seats - $reservation['seats_reserved'];
            
            if ($seatsToAdd > $availableSeats) {
                throw new Exception("Not enough available seats");
            }
            
            // Update the reservation
            $success = $eventController->updateReservation($reservation_id, $new_seats);
            if (!$success) {
                throw new Exception("Failed to update reservation");
            }
            
            $_SESSION['success_message'] = "Reservation updated successfully";
            break;
            
        case 'cancel':
            // Delete the reservation
            $success = $eventController->deleteReservation($reservation_id);
            if (!$success) {
                throw new Exception("Failed to cancel reservation");
            }
            
            $_SESSION['success_message'] = "Reservation cancelled successfully";
            break;
            
        default:
            throw new Exception("Invalid action");
    }
    
    // Redirect back to events page
    header("Location: events.php");
    exit();
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: events.php");
    exit();
}
?> 