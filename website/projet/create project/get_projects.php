<?php
require_once('db.php');

header('Content-Type: application/json');

try {
    // Get the total number of projects
    $countSql = "SELECT COUNT(*) as total FROM projects";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();
    $totalProjects = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get the latest projects with their IDs and images
    $sql = "SELECT id, projectName, projectLocation, projectImage FROM projects ORDER BY created_at DESC";
    
    // Check if we're getting all projects or just the first 2
    if (!isset($_GET['all']) || $_GET['all'] !== 'true') {
        $sql .= " LIMIT 2";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process image paths
    foreach ($projects as &$project) {
        if (!empty($project['projectImage'])) {
            // Remove any leading slashes or dots
            $project['projectImage'] = ltrim($project['projectImage'], './');
            
            // If the path doesn't start with 'uploads/', add it
            if (strpos($project['projectImage'], 'uploads/') !== 0) {
                $project['projectImage'] = 'uploads/' . $project['projectImage'];
            }
            
            // Check if file exists in the correct directory
            $fullPath = __DIR__ . '/' . $project['projectImage'];
            if (!file_exists($fullPath)) {
                $project['projectImage'] = 'default-project-image.jpg';
            }
        } else {
            $project['projectImage'] = 'default-project-image.jpg';
        }
    }
    
    echo json_encode([
        'success' => true,
        'projects' => $projects,
        'totalProjects' => $totalProjects
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 