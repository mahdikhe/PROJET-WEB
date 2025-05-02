<?php
header('Content-Type: application/json');

// Connexion à la base de données
$host = 'localhost';
$dbname = 'citypulse';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les paramètres de pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 2;
    $offset = ($page - 1) * $limit;
    
    // Récupérer le nombre total de projets
    $countQuery = "SELECT COUNT(*) as total FROM projects";
    $countStmt = $pdo->query($countQuery);
    $totalProjects = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Récupérer les projets paginés
    $query = "SELECT * FROM projects ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Préparer la réponse
    $response = [
        'success' => true,
        'projects' => $projects,
        'totalProjects' => $totalProjects,
        'currentPage' => $page,
        'totalPages' => ceil($totalProjects / $limit)
    ];
    
} catch(PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ];
}

// Envoyer la réponse
echo json_encode($response);
?>