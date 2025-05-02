<?php
header('Content-Type: application/json');

// Database connection
include 'C:/Users/Abderrahmen/Desktop/aa/platforme/config/Database.php';

try {
    $query = "SELECT 
                p.id, 
                p.name, 
                p.category, 
                p.status, 
                p.budget, 
                p.description, 
                l.latitude, 
                l.longitude 
              FROM projects p
              JOIN project_locations l ON p.id = l.project_id
              WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $projects
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>