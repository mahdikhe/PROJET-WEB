<?php
// Include database configuration
require_once 'config.php';

/**
 * Get offer details by ID
 * 
 * @param int $id The offer ID
 * @return array|null The offer data or null if not found
 */
function getOfferById($id) {
    global $host, $dbname, $username, $password;
    
    try {
        // Create a new PDO instance
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prepare the SQL query
        $stmt = $pdo->prepare("SELECT * FROM offres WHERE id = :id LIMIT 1");
        
        // Bind the ID parameter
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Fetch the offer
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return the offer or null if not found
        return $offer ? $offer : null;
        
    } catch (PDOException $e) {
        // Log the error (in a production environment)
        // error_log("Database error in getOfferById: " . $e->getMessage());
        
        // Return null on error
        return null;
    }
}

// If this file is called directly with an ID parameter, return JSON response
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $offer = getOfferById($id);
    
    // Set content type to JSON
    header('Content-Type: application/json');
    
    if ($offer) {
        echo json_encode(['success' => true, 'offer' => $offer]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Offre non trouvée']);
    }
    
    exit;
}
?>