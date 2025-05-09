<?php
// Include database connection
require_once 'config.php';

/**
 * Function to get all job offers from the database
 * @return array Array of job offers
 */
function getAllOffers() {
    global $conn;
    
    try {
        // Prepare SQL query to select all offers
        $stmt = $conn->prepare("SELECT * FROM offres ORDER BY id DESC");
        $stmt->execute();
        
        // Return all offers as an associative array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        // Return empty array on error
        error_log("Error fetching offers: " . $e->getMessage());
        return [];
    }
}

// If this file is requested directly (via AJAX for example), return JSON data
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('Content-Type: application/json');
    echo json_encode(getAllOffers());
    exit;
}
?>