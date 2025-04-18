<?php
// Include database connection for contributors
include_once 'db_contributors.php';

// Set header to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'data' => [],
    'message' => ''
];

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

try {
    // Check if we need to run geocoding first
    $checkStmt = $contrib_conn->prepare("SELECT COUNT(*) FROM contributors WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    $checkStmt->execute();
    $coordinatesExist = $checkStmt->fetchColumn() > 0;
    
    if (!$coordinatesExist) {
        // No coordinates exist yet - redirect to update script
        $response['message'] = 'No coordinates available. Please run the coordinate update script first.';
        $response['redirect'] = '../projet/update_coordinates.php';
        echo json_encode($response);
        exit;
    }
    
    // Fetch all contributors with coordinates
    $sql = "SELECT id, first_name, last_name, city, country, profile_image, contribution_type,
            latitude, longitude FROM contributors 
            WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
    $stmt = $contrib_conn->prepare($sql);
    $stmt->execute();
    
    $contributors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($contributors) > 0) {
        foreach ($contributors as &$contributor) {
            // Add a default image if profile_image is null
            if (empty($contributor['profile_image'])) {
                $contributor['profile_image'] = '../assets/img/default-avatar.jpg';
            }
            
            // Format name for display
            $contributor['name'] = $contributor['first_name'] . ' ' . $contributor['last_name'];
            
            // Format location text
            $location = $contributor['city'];
            if (!empty($contributor['country'])) {
                $location .= ', ' . $contributor['country'];
            }
            $contributor['location'] = $location;
        }
        
        $response['success'] = true;
        $response['data'] = $contributors;
    } else {
        $response['message'] = 'No contributors with valid coordinates found.';
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

// Return the JSON response
echo json_encode($response);

/**
 * Get coordinates for a given city name 
 * 
 * @param string $city The city name
 * @return array|null Coordinates as [lat, lng] or null if not found
 */
function getCityCoordinates($city) {
    // Simplified mapping of cities to coordinates
    $cityMap = [
        'paris' => ['lat' => 48.8566, 'lng' => 2.3522],
        'lyon' => ['lat' => 45.7640, 'lng' => 4.8357],
        'marseille' => ['lat' => 43.2965, 'lng' => 5.3698],
        'toulouse' => ['lat' => 43.6047, 'lng' => 1.4442],
        'nice' => ['lat' => 43.7102, 'lng' => 7.2620],
        'nantes' => ['lat' => 47.2184, 'lng' => -1.5536],
        'strasbourg' => ['lat' => 48.5734, 'lng' => 7.7521],
        'montpellier' => ['lat' => 43.6112, 'lng' => 3.8767],
        'bordeaux' => ['lat' => 44.8378, 'lng' => -0.5792],
        'lille' => ['lat' => 50.6292, 'lng' => 3.0573],
        'rennes' => ['lat' => 48.1173, 'lng' => -1.6778],
        'london' => ['lat' => 51.5074, 'lng' => -0.1278],
        'berlin' => ['lat' => 52.5200, 'lng' => 13.4050],
        'madrid' => ['lat' => 40.4168, 'lng' => -3.7038],
        'rome' => ['lat' => 41.9028, 'lng' => 12.4964],
        'amsterdam' => ['lat' => 52.3676, 'lng' => 4.9041],
        'brussels' => ['lat' => 50.8503, 'lng' => 4.3517],
        'lisbon' => ['lat' => 38.7223, 'lng' => -9.1393],
        'vienna' => ['lat' => 48.2082, 'lng' => 16.3738],
        'barcelona' => ['lat' => 41.3851, 'lng' => 2.1734],
        'munich' => ['lat' => 48.1351, 'lng' => 11.5820],
        'prague' => ['lat' => 50.0755, 'lng' => 14.4378],
        'milan' => ['lat' => 45.4642, 'lng' => 9.1900],
        'manchester' => ['lat' => 53.4808, 'lng' => -2.2426],
        'tunis' => ['lat' => 36.8065, 'lng' => 10.1815],
        // Add more cities as needed
        'algiers' => ['lat' => 36.7538, 'lng' => 3.0588],
        'rabat' => ['lat' => 34.0209, 'lng' => -6.8416],
        'cairo' => ['lat' => 30.0444, 'lng' => 31.2357],
        'casablanca' => ['lat' => 33.5731, 'lng' => -7.5898],
        'athens' => ['lat' => 37.9838, 'lng' => 23.7275],
        'istanbul' => ['lat' => 41.0082, 'lng' => 28.9784],
        'dubai' => ['lat' => 25.2048, 'lng' => 55.2708]
    ];
    
    // Normalize city name for lookup
    $normalizedCity = strtolower(trim($city));
    
    // Try exact match first
    if (isset($cityMap[$normalizedCity])) {
        return $cityMap[$normalizedCity];
    }
    
    // Try partial match
    foreach ($cityMap as $knownCity => $coordinates) {
        if (strpos($normalizedCity, $knownCity) !== false || 
            strpos($knownCity, $normalizedCity) !== false) {
            return $coordinates;
        }
    }
    
    return null;
} 