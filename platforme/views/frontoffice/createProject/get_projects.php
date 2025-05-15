<?php
require_once(__DIR__ . '/../../../config/Database.php');

header('Content-Type: application/json');

try {
    // Initialize database connection
    $db = Database::getInstance()->getConnection();

    // Get the total number of projects
    $countSql = "SELECT COUNT(*) as total FROM projects";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute();
    $totalProjects = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Build the query with contributor count
    $sql = "SELECT p.id, p.projectName, p.projectLocation, p.projectImage, 
                   p.is_paid, p.ticket_price,
                   (SELECT COUNT(*) FROM contributors c WHERE c.project_id = p.id) AS contributor_count
            FROM projects p
            ORDER BY p.created_at DESC";
    
    // Check if we're getting all projects or just a limited number
    if (!isset($_GET['all']) || $_GET['all'] !== 'true') {
        $sql .= " LIMIT 2";
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process image paths and format data
    foreach ($projects as &$project) {
        // Format payment info
        if (isset($project['is_paid'])) {
            $project['payment_info'] = $project['is_paid'] ? 
                number_format($project['ticket_price'], 2) . ' €' : 
                'Free Access';
        }
        
        // Format image path
        if (!empty($project['projectImage'])) {
            // Ensure the image path is correctly formatted for web access
            if (!filter_var($project['projectImage'], FILTER_VALIDATE_URL)) {
                $project['projectImage'] = 'uploads/' . basename($project['projectImage']);
            }
        } else {
            $project['projectImage'] = 'default-project-image.jpg';
        }
        
        // Ensure contributor_count is set (default to 0 if null)
        $project['contributor_count'] = $project['contributor_count'] ?? 0;
    }
    
    echo json_encode([
        'success' => true,
        'projects' => $projects,
        'totalProjects' => $totalProjects
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_projects.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred while fetching projects'
    ]);
} catch (Exception $e) {
    error_log("Error in get_projects.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching projects'
    ]);
}