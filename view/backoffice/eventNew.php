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
?>