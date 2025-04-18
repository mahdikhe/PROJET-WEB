<?php
require_once 'C:\xampp\htdocs\test\config.php';
require_once 'C:\xampp\htdocs\test\model\model.php';

class EventController {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

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
}
?>
