<?php
// Include database configuration
require_once '../../back_office/model/config.php';

/**
 * Get all job offers from the database
 * 
 * @param array $filters Optional filters to apply
 * @param int $limit Number of offers to return
 * @param int $offset Pagination offset
 * @return array Array of offers
 */
function getFrontOfficeOffers($filters = [], $limit = 12, $offset = 0) {
    global $servername, $username, $password, $dbname;
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Start building the query
        $query = "SELECT * FROM offres";
        $params = [];
        
        // Apply filters if any
        if (!empty($filters)) {
            $whereConditions = [];
            
            // Filter by title or company
            if (!empty($filters['search'])) {
                $whereConditions[] = "(titre LIKE :search OR entreprise LIKE :search OR description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            // Filter by type
            if (!empty($filters['type'])) {
                $whereConditions[] = "type = :type";
                $params[':type'] = $filters['type'];
            }
            
            // Filter by location
            if (!empty($filters['emplacement'])) {
                $whereConditions[] = "emplacement = :emplacement";
                $params[':emplacement'] = $filters['emplacement'];
            }
            
            // Add WHERE clause if there are conditions
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(" AND ", $whereConditions);
            }
        }
        
        // Order by date (newest first)
        $query .= " ORDER BY date DESC";
        
        // Add pagination
        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        // Prepare and execute the query
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            if ($key == ':limit' || $key == ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        
        // Fetch all offers
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $offers;
    } catch (PDOException $e) {
        // Log error or handle exception
        error_log("Database error in getFrontOfficeOffers: " . $e->getMessage());
        return [];
    }
}

/**
 * Count the total number of job offers based on filters
 * 
 * @param array $filters Optional filters to apply
 * @return int Total count
 */
function countOffers($filters = []) {
    global $servername, $username, $password, $dbname;
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Start building the count query
        $query = "SELECT COUNT(*) as total FROM offres";
        $params = [];
        
        // Apply filters if any
        if (!empty($filters)) {
            $whereConditions = [];
            
            // Filter by title or company
            if (!empty($filters['search'])) {
                $whereConditions[] = "(titre LIKE :search OR entreprise LIKE :search OR description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            // Filter by type
            if (!empty($filters['type'])) {
                $whereConditions[] = "type = :type";
                $params[':type'] = $filters['type'];
            }
            
            // Filter by location
            if (!empty($filters['emplacement'])) {
                $whereConditions[] = "emplacement = :emplacement";
                $params[':emplacement'] = $filters['emplacement'];
            }
            
            // Add WHERE clause if there are conditions
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(" AND ", $whereConditions);
            }
        }
        
        // Prepare and execute the query
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();
        
        // Fetch the count
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int) $result['total'];
    } catch (PDOException $e) {
        // Log error or handle exception
        error_log("Database error in countOffers: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get all distinct locations for filter options
 * 
 * @return array Array of locations
 */
function getLocations() {
    global $servername, $username, $password, $dbname;
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get distinct locations
        $query = "SELECT DISTINCT emplacement FROM offres ORDER BY emplacement";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        // Fetch all locations
        $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $locations;
    } catch (PDOException $e) {
        // Log error or handle exception
        error_log("Database error in getLocations: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all distinct job types for filter options
 * 
 * @return array Array of types
 */
function getJobTypes() {
    global $servername, $username, $password, $dbname;
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get distinct types
        $query = "SELECT DISTINCT type FROM offres ORDER BY type";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        // Fetch all types
        $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $types;
    } catch (PDOException $e) {
        // Log error or handle exception
        error_log("Database error in getJobTypes: " . $e->getMessage());
        return [];
    }
}

/**
 * Format date for display
 * 
 * @param string $dateString Date string from database
 * @return string Formatted date
 */
function formatOfferDate($dateString) {
    // Create DateTime object from the date string
    $date = new DateTime($dateString);
    
    // Set locale for month names in French
    setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
    
    // Format the date as "12 juin 2023"
    $formattedDate = $date->format('j') . ' ' . strftime('%B', $date->getTimestamp()) . ' ' . $date->format('Y');
    
    return $formattedDate;
}
?>