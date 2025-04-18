<?php

class EventManager {
    private $pdo;
    private $lastError;
    private $lastInsertId;
    private $eventData;
    
    /**
     * Constructor - initializes database connection
     */
    public function __construct() {
        $this->pdo = Config::getConnexion();
        $this->lastError = null;
        $this->lastInsertId = null;
        $this->eventData = [];
    }
    
    /**
     * Set event data from form or other source
     * 
     * @param array $data Array of event data
     * @return EventManager Instance for method chaining
     */
    public function setEventData($data) {
        $this->eventData = $data;
        return $this;
    }
    
    /**
     * Get current event data
     * 
     * @return array Current event data
     */
    public function getEventData() {
        return $this->eventData;
    }
    
    /**
     * Set a specific field in event data
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @return EventManager Instance for method chaining
     */
    public function setField($field, $value) {
        $this->eventData[$field] = $value;
        return $this;
    }
    
    /**
     * Get a specific field from event data
     * 
     * @param string $field Field name
     * @return mixed Field value or null if not set
     */
    public function getField($field) {
        return $this->eventData[$field] ?? null;
    }
    
    /**
     * Get the last error message
     * 
     * @return string|null Last error message or null if no error
     */
    public function getLastError() {
        return $this->lastError;
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return int|null Last inserted ID or null if no insert
     */
    public function getLastInsertId() {
        return $this->lastInsertId;
    }
}

class Event {
    private ?int $event_id;
    private string $event_title;
    private string $event_type;
    private string $description;
    private string $start_date;
    private string $start_time;
    private string $end_date;
    private string $end_time;
    private string $event_format;
    private string $location;
    private string $online_url;
    private int $capacity;
    private string $ticket_type;
    private float $price;

    public function __construct(
        string $event_title,
        string $event_type,
        string $description,
        string $start_date,
        string $start_time,
        string $end_date,
        string $end_time,
        string $event_format,
        string $location,
        string $online_url = '',
        int $capacity,
        string $ticket_type,
        float $price,
        ?int $event_id = null
    ) {
        $this->event_id = $event_id;
        $this->event_title = $event_title;
        $this->event_type = $event_type;
        $this->description = $description;
        $this->start_date = $start_date;
        $this->start_time = $start_time;
        $this->end_date = $end_date;
        $this->end_time = $end_time;
        $this->event_format = $event_format;
        $this->location = $location;
        $this->online_url = $online_url;
        $this->capacity = $capacity;
        $this->ticket_type = $ticket_type;
        $this->price = $price;
    }

    // Getters
    public function getEventId(): ?int { return $this->event_id; }
    public function getEventTitle(): string { return $this->event_title; }
    public function getEventType(): string { return $this->event_type; }
    public function getDescription(): string { return $this->description; }
    public function getStartDate(): string { return $this->start_date; }
    public function getStartTime(): string { return $this->start_time; }
    public function getEndDate(): string { return $this->end_date; }
    public function getEndTime(): string { return $this->end_time; }
    public function getEventFormat(): string { return $this->event_format; }
    public function getLocation(): string { return $this->location; }
    public function getOnlineUrl(): string { return $this->online_url; }
    public function getCapacity(): int { return $this->capacity; }
    public function getTicketType(): string { return $this->ticket_type; }
    public function getPrice(): float { return $this->price; }

    // Setters
    public function setEventId(?int $event_id): self { 
        $this->event_id = $event_id; 
        return $this; 
    }
    public function setEventTitle(string $event_title): self { 
        $this->event_title = $event_title; 
        return $this; 
    }
    public function setEventType(string $event_type): self { 
        $this->event_type = $event_type; 
        return $this; 
    }
    public function setDescription(string $description): self { 
        $this->description = $description; 
        return $this; 
    }
    public function setStartDate(string $start_date): self { 
        $this->start_date = $start_date; 
        return $this; 
    }
    public function setStartTime(string $start_time): self { 
        $this->start_time = $start_time; 
        return $this; 
    }
    public function setEndDate(string $end_date): self { 
        $this->end_date = $end_date; 
        return $this; 
    }
    public function setEndTime(string $end_time): self { 
        $this->end_time = $end_time; 
        return $this; 
    }
    public function setEventFormat(string $event_format): self { 
        $this->event_format = $event_format; 
        return $this; 
    }
    public function setLocation(string $location): self { 
        $this->location = $location; 
        return $this; 
    }
    public function setOnlineUrl(string $online_url): self { 
        $this->online_url = $online_url; 
        return $this; 
    }
    public function setCapacity(int $capacity): self { 
        $this->capacity = $capacity; 
        return $this; 
    }
    public function setTicketType(string $ticket_type): self { 
        $this->ticket_type = $ticket_type; 
        return $this; 
    }
    public function setPrice(float $price): self { 
        $this->price = $price; 
        return $this; 
    }
}
?>