<?php
// Include database connection for contributors - determine the path based on the current file location
$base_path = __DIR__;
$db_file = $base_path . '/db_contributors.php';

// Check if the file exists at the direct path
if (!file_exists($db_file)) {
    // Try finding it in the dashboard folder
    $db_file = dirname($base_path) . '/dashboard/db_contributors.php';
    if (!file_exists($db_file)) {
        die(json_encode([
            'success' => false,
            'message' => 'Database connection file not found'
        ]));
    }
}
include_once $db_file;

// Set header to JSON if called as API
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
          
if ($isAjax) {
    header('Content-Type: application/json');
}

$response = [
    'success' => false,
    'updated' => 0,
    'total' => 0,
    'failed' => 0,
    'message' => ''
];

try {
    // Get all contributors without coordinates
    $sql = "SELECT id, city FROM contributors WHERE (latitude IS NULL OR longitude IS NULL) AND city IS NOT NULL";
    $stmt = $contrib_conn->prepare($sql);
    $stmt->execute();
    $contributors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['total'] = count($contributors);
    $updatedCount = 0;
    $failedCount = 0;
    
    foreach ($contributors as $contributor) {
        if (empty($contributor['city'])) {
            $failedCount++;
            continue;
        }
        
        $cityCoordinates = getCityCoordinates($contributor['city']);
        
        if ($cityCoordinates) {
            // Add small random offset
            $latOffset = (rand(-10, 10) / 300);
            $lngOffset = (rand(-10, 10) / 300);
            
            $latitude = $cityCoordinates['lat'] + $latOffset;
            $longitude = $cityCoordinates['lng'] + $lngOffset;
            
            // Update the database
            $updateStmt = $contrib_conn->prepare("UPDATE contributors SET latitude = :lat, longitude = :lng WHERE id = :id");
            $updateStmt->bindParam(':lat', $latitude);
            $updateStmt->bindParam(':lng', $longitude);
            $updateStmt->bindParam(':id', $contributor['id']);
            
            if ($updateStmt->execute()) {
                $updatedCount++;
                
                if (!$isAjax) {
                    echo "Updated coordinates for contributor ID {$contributor['id']} (City: {$contributor['city']}).<br>";
                }
            } else {
                $failedCount++;
            }
        } else {
            $failedCount++;
            
            if (!$isAjax) {
                echo "Could not geocode city: {$contributor['city']} for contributor ID {$contributor['id']}.<br>";
            }
        }
    }
    
    $response['success'] = true;
    $response['updated'] = $updatedCount;
    $response['failed'] = $failedCount;
    $response['message'] = "Successfully updated coordinates for $updatedCount contributors.";
    
    if (!$isAjax) {
        echo "<h2>Update Complete</h2>";
        echo "Total contributors without coordinates: {$response['total']}<br>";
        echo "Successfully updated: $updatedCount<br>";
        echo "Failed to update: $failedCount<br>";
        
        echo "<p><a href='dashboard/creative_dashboard.php'>Return to Dashboard</a></p>";
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    
    if (!$isAjax) {
        echo "<h2>Error</h2>";
        echo "<p>{$response['message']}</p>";
    }
}

// Output JSON response if AJAX request
if ($isAjax) {
    echo json_encode($response);
}

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
        // North African cities
        'algiers' => ['lat' => 36.7538, 'lng' => 3.0588],
        'rabat' => ['lat' => 34.0209, 'lng' => -6.8416],
        'cairo' => ['lat' => 30.0444, 'lng' => 31.2357],
        'casablanca' => ['lat' => 33.5731, 'lng' => -7.5898],
        'marrakech' => ['lat' => 31.6295, 'lng' => -7.9811],
        'sfax' => ['lat' => 34.7478, 'lng' => 10.7661],
        'tangier' => ['lat' => 35.7673, 'lng' => -5.7974],
        'alexandria' => ['lat' => 31.2001, 'lng' => 29.9187],
        'annaba' => ['lat' => 36.9000, 'lng' => 7.7660],
        'tripoli' => ['lat' => 32.8872, 'lng' => 13.1913]
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
?> 