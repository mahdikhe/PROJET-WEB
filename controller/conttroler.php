<?php
<<<<<<< HEAD
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/model/model.php';
=======
require_once 'C:\xampp\htdocs\test\config.php';
require_once 'C:\xampp\htdocs\test\model\model.php';
>>>>>>> bb0192a77f41df7c722502d7c9fbaadb5c90f577

class EventController {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

<<<<<<< HEAD
    public function getTotalEvents() {
        $sql = "SELECT COUNT(*) as count FROM events";
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error getting total events: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getUpcomingEvents() {
        $today = date('Y-m-d');
        $thirtyDaysLater = date('Y-m-d', strtotime('+30 days'));
        
        $sql = "SELECT COUNT(*) as count FROM events 
                WHERE start_date BETWEEN :today AND :thirtyDaysLater";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'today' => $today,
                'thirtyDaysLater' => $thirtyDaysLater
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error getting upcoming events: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getTotalRevenue() {
        $sql = "SELECT SUM(e.price * r.seats_reserved) as total_revenue 
                FROM reservations r 
                JOIN events e ON r.event_id = e.event_id";
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_revenue'] ?: 0;
        } catch (PDOException $e) {
            error_log("Error calculating total revenue: " . $e->getMessage());
            return 0;
        }
    }

=======
>>>>>>> bb0192a77f41df7c722502d7c9fbaadb5c90f577
    public function listEvents() {
        $sql = "SELECT * FROM events ORDER BY start_date DESC, start_time DESC";
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listing events: " . $e->getMessage());
            return [];
        }
    }

    public function getEvent($event_id) {
        $sql = "SELECT * FROM events WHERE event_id = :event_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['event_id' => $event_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting event: " . $e->getMessage());
            return null;
        }
    }

    public function addEvent(Event $event) {
        $sql = "INSERT INTO events (
            event_title, event_type, description, start_date, start_time,
            end_date, end_time, event_format, location, online_url,
            capacity, ticket_type, price
        ) VALUES (
            :event_title, :event_type, :description, :start_date, :start_time,
            :end_date, :end_time, :event_format, :location, :online_url,
            :capacity, :ticket_type, :price
        )";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'event_title' => $event->getEventTitle(),
                'event_type' => $event->getEventType(),
                'description' => $event->getDescription(),
                'start_date' => $event->getStartDate(),
                'start_time' => $event->getStartTime(),
                'end_date' => $event->getEndDate(),
                'end_time' => $event->getEndTime(),
                'event_format' => $event->getEventFormat(),
                'location' => $event->getLocation(),
                'online_url' => $event->getOnlineUrl(),
                'capacity' => $event->getCapacity(),
                'ticket_type' => $event->getTicketType(),
                'price' => $event->getPrice()
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding event: " . $e->getMessage());
            return false;
        }
    }

    public function updateEvent(Event $event) {
        $sql = "UPDATE events SET 
            event_title = :event_title,
            event_type = :event_type,
            description = :description,
            start_date = :start_date,
            start_time = :start_time,
            end_date = :end_date,
            end_time = :end_time,
            event_format = :event_format,
            location = :location,
            online_url = :online_url,
            capacity = :capacity,
            ticket_type = :ticket_type,
            price = :price
        WHERE event_id = :event_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'event_id' => $event->getEventId(),
                'event_title' => $event->getEventTitle(),
                'event_type' => $event->getEventType(),
                'description' => $event->getDescription(),
                'start_date' => $event->getStartDate(),
                'start_time' => $event->getStartTime(),
                'end_date' => $event->getEndDate(),
                'end_time' => $event->getEndTime(),
                'event_format' => $event->getEventFormat(),
                'location' => $event->getLocation(),
                'online_url' => $event->getOnlineUrl(),
                'capacity' => $event->getCapacity(),
                'ticket_type' => $event->getTicketType(),
                'price' => $event->getPrice()
            ]);
        } catch (PDOException $e) {
            error_log("Error updating event: " . $e->getMessage());
            return false;
        }
    }

    public function deleteEvent($event_id) {
<<<<<<< HEAD
        // First delete all reservations for this event
        $this->deleteReservationsByEventId($event_id);
        
        // Then delete the event
=======
>>>>>>> bb0192a77f41df7c722502d7c9fbaadb5c90f577
        $sql = "DELETE FROM events WHERE event_id = :event_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['event_id' => $event_id]);
        } catch (PDOException $e) {
            error_log("Error deleting event: " . $e->getMessage());
            return false;
        }
    }

    public function sortEvents($order = 'ASC') {
        $sql = "SELECT * FROM events ORDER BY event_title $order";
        $db = Config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
<<<<<<< HEAD
    
    /* Reservation Management Methods */
    
    public function addReservation($event_id, $guest_name, $guest_email, $seats_reserved = 1) {
        $sql = "INSERT INTO reservations (
            event_id, guest_name, guest_email, seats_reserved
        ) VALUES (
            :event_id, :guest_name, :guest_email, :seats_reserved
        )";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'event_id' => $event_id,
                'guest_name' => $guest_name,
                'guest_email' => $guest_email,
                'seats_reserved' => $seats_reserved
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding reservation: " . $e->getMessage());
            return false;
        }
    }
    
    public function getReservation($reservation_id) {
        $sql = "SELECT * FROM reservations WHERE reservation_id = :reservation_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['reservation_id' => $reservation_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting reservation: " . $e->getMessage());
            return null;
        }
    }
    
    public function getReservationsByEvent($event_id) {
        $sql = "SELECT * FROM reservations WHERE event_id = :event_id ORDER BY reservation_date DESC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['event_id' => $event_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting reservations by event: " . $e->getMessage());
            return [];
        }
    }
    
    public function getReservationsByEmail($email) {
        $sql = "SELECT r.*, e.event_title, e.start_date, e.start_time, e.event_format, e.location 
                FROM reservations r 
                JOIN events e ON r.event_id = e.event_id 
                WHERE r.guest_email = :email 
                ORDER BY r.reservation_date DESC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting reservations by email: " . $e->getMessage());
            return [];
        }
    }
    
    public function deleteReservation($reservation_id) {
        $sql = "DELETE FROM reservations WHERE reservation_id = :reservation_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['reservation_id' => $reservation_id]);
        } catch (PDOException $e) {
            error_log("Error deleting reservation: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteReservationsByEventId($event_id) {
        $sql = "DELETE FROM reservations WHERE event_id = :event_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['event_id' => $event_id]);
        } catch (PDOException $e) {
            error_log("Error deleting reservations by event ID: " . $e->getMessage());
            return false;
        }
    }
    
    public function getEventAvailableSeats($event_id) {
        try {
            // Get event capacity
            $eventSql = "SELECT capacity FROM events WHERE event_id = :event_id";
            $eventStmt = $this->pdo->prepare($eventSql);
            $eventStmt->execute(['event_id' => $event_id]);
            $event = $eventStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event) {
                return 0;
            }
            
            $capacity = $event['capacity'];
            
            // Get total reserved seats
            $reservationSql = "SELECT SUM(seats_reserved) as total_reserved FROM reservations WHERE event_id = :event_id";
            $reservationStmt = $this->pdo->prepare($reservationSql);
            $reservationStmt->execute(['event_id' => $event_id]);
            $reservation = $reservationStmt->fetch(PDO::FETCH_ASSOC);
            
            $reserved = $reservation['total_reserved'] ?: 0;
            
            // Return available seats
            return $capacity - $reserved;
        } catch (PDOException $e) {
            error_log("Error calculating available seats: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getTotalReservations($event_id = null) {
        if ($event_id !== null) {
            $sql = "SELECT COUNT(*) as count FROM reservations WHERE event_id = :event_id";
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['event_id' => $event_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['count'];
            } catch (PDOException $e) {
                error_log("Error getting total reservations: " . $e->getMessage());
                return 0;
            }
        } else {
            $sql = "SELECT COUNT(*) as count FROM reservations";
            try {
                $stmt = $this->pdo->query($sql);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['count'];
            } catch (PDOException $e) {
                error_log("Error getting total reservations: " . $e->getMessage());
                return 0;
            }
        }
    }
=======
>>>>>>> bb0192a77f41df7c722502d7c9fbaadb5c90f577
}
?>
