<?php
// Include required files
require_once '../../config.php';
require_once '../../model/model.php';
require_once '../../controller/conttroler.php';

try {
    // Check if a POST request with reservation_id was received
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
        $reservationId = $_POST['reservation_id'];
        
        // Create EventController instance
        $eventController = new EventController();
        
        // Get the reservation to find its event_id before deletion
        $reservation = $eventController->getReservation($reservationId);
        
        if ($reservation) {
            $eventId = $reservation['event_id'];
            
            // Delete the reservation
            $deleted = $eventController->deleteReservation($reservationId);
            
            if ($deleted) {
                // Redirect back to dashboard with success message
                header('Location: dashboard.php?delete_success=true');
                exit();
            } else {
                // Redirect back with error
                header('Location: dashboard.php?delete_error=true');
                exit();
            }
        } else {
            // Reservation not found
            header('Location: dashboard.php?delete_error=true&message=Reservation+not+found');
            exit();
        }
    } else {
        // Invalid request
        header('Location: dashboard.php?delete_error=true&message=Invalid+request');
        exit();
    }
} catch (Exception $e) {
    // Log the error
    error_log("Error deleting reservation: " . $e->getMessage());
    
    // Redirect with error
    header('Location: dashboard.php?delete_error=true&message=' . urlencode($e->getMessage()));
    exit();
}
?> 