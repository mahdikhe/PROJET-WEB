
<?php
require_once('db.php');

// Démarrer la session pour stocker le panier
session_start();

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Gestion des actions sur le panier
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
    
    switch ($action) {
        case 'add':
            // Vérifier si le projet existe et est payant
            if ($projectId > 0) {
                $stmt = $conn->prepare("SELECT id, projectName, ticket_price, is_paid FROM projects WHERE id = ? AND is_paid = 1");
                $stmt->execute([$projectId]);
                $project = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($project) {
                    // Ajouter au panier
                    $_SESSION['cart'][$projectId] = $project;
                    $successMessage = "Le projet '" . htmlspecialchars($project['projectName']) . "' a été ajouté à votre panier.";
                }
            }
            break;
            
        case 'remove':
            // Supprimer du panier
            if (isset($_SESSION['cart'][$projectId])) {
                unset($_SESSION['cart'][$projectId]);
                $successMessage = "Le projet a été retiré de votre panier.";
            }
            break;
            
        case 'clear':
            // Vider le panier
            $_SESSION['cart'] = [];
            $successMessage = "Votre panier a été vidé.";
            break;
    }
}

// Calculer le montant total
$totalAmount = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalAmount += (float)$item['ticket_price'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - CityPulse</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --text: #212529;
            --border: #dee2e6;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: var(--text);
            background-color: var(--light);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .cart-empty {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .cart-table th,
        .cart-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .cart-table th {
            background-color: var(--primary);
            color: white;
        }
        
        .cart-table tr:last-child td {
            border-bottom: none;
        }
        
        .cart-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .button-primary {
            background: var(--primary);
            color: white;
            border: none;
        }
        
        .button-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }
        
        .button-danger {
            background: var(--danger);
            color: white;
            border: none;
        }
        
        .button-success {
            background: var(--success);
            color: white;
            border: none;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .cart-summary {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }
        
        .cart-summary h3 {
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.5rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 1.2rem;
            border-top: 1px solid var(--border);
            padding-top: 0.5rem;
            margin-top: 1rem;
        }
        
        .remove-item {
            color: var(--danger);
            cursor: pointer;
            text-decoration: none;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        @media (max-width: 768px) {
            .cart-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .cart-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Votre Panier</h1>
            <p>Gérez vos tickets pour les projets</p>
        </div>
        
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success">
                <?= $successMessage ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="cart-empty">
                <h2>Votre panier est vide</h2>
                <p>Parcourez nos projets et ajoutez des tickets à votre panier.</p>
                <a href="projects.php" class="button button-primary" style="margin-top: 1rem;">
                    <i class="fas fa-arrow-left"></i> Voir les projets
                </a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Projet</th>
                        <th>Prix</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $projectId => $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['projectName']) ?></td>
                            <td><?= number_format($item['ticket_price'], 2) ?> €</td>
                            <td>
                                <a href="cart.php?action=remove&project_id=<?= $projectId ?>" class="remove-item">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-summary">
                <h3>Résumé de la commande</h3>
                <div class="summary-row">
                    <span>Nombre de tickets:</span>
                    <span><?= count($_SESSION['cart']) ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span><?= number_format($totalAmount, 2) ?> €</span>
                </div>
            </div>
            
            <div class="cart-actions">
                <a href="projects.php" class="button button-outline">
                    <i class="fas fa-arrow-left"></i> Continuer les achats
                </a>
                <a href="cart.php?action=clear" class="button button-danger">
                    <i class="fas fa-trash"></i> Vider le panier
                </a>
                <a href="checkout.php" class="button button-success">
                    <i class="fas fa-credit-card"></i> Procéder au paiement
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 













