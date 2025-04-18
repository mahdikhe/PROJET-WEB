



<?php
require_once('db.php');

// Démarrer la session pour récupérer le panier
session_start();

// Vérifier si le panier est vide
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Calculer le montant total
$totalAmount = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalAmount += (float)$item['ticket_price'];
}

// Traitement du formulaire de paiement
$paymentComplete = false;
$paymentError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valider les champs du formulaire
    $requiredFields = ['cardName', 'cardNumber', 'cardExpiry', 'cardCvv', 'billingName', 'billingEmail', 'billingAddress'];
    $isValid = true;
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $isValid = false;
            $paymentError = "Tous les champs sont obligatoires.";
            break;
        }
    }
    
    // Valider le numéro de carte (simpliste pour l'exemple)
    if ($isValid && !preg_match('/^\d{16}$/', str_replace(' ', '', $_POST['cardNumber']))) {
        $isValid = false;
        $paymentError = "Le numéro de carte est invalide.";
    }
    
    // Valider le CVV (simpliste pour l'exemple)
    if ($isValid && !preg_match('/^\d{3,4}$/', $_POST['cardCvv'])) {
        $isValid = false;
        $paymentError = "Le code CVV est invalide.";
    }
    
    // Traiter le paiement si tout est valide
    if ($isValid) {
        try {
            // Début de la transaction
            $conn->beginTransaction();
            
            // Utilisateur factice pour la démo
            $userId = 1; // Dans un système réel, ce serait l'ID de l'utilisateur connecté
            
            // Insertion de chaque ticket acheté
            foreach ($_SESSION['cart'] as $projectId => $item) {
                $stmt = $conn->prepare("INSERT INTO project_tickets (user_id, project_id, amount, payment_status) VALUES (?, ?, ?, 'completed')");
                $stmt->execute([$userId, $projectId, $item['ticket_price']]);
            }
            
            // Confirmer la transaction
            $conn->commit();
            
            // Vider le panier après un paiement réussi
            $_SESSION['cart'] = [];
            
            // Marquer le paiement comme complet
            $paymentComplete = true;
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $conn->rollBack();
            $paymentError = "Erreur lors du traitement du paiement: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - CityPulse</title>
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
        
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        
        .payment-form,
        .order-summary {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .form-section {
            margin-bottom: 1.5rem;
        }
        
        .form-section h2 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 1rem;
        }
        
        .button-primary {
            background: var(--primary);
            color: white;
        }
        
        .button-success {
            background: var(--success);
            color: white;
        }
        
        .button-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
        
        .order-items {
            margin-bottom: 1.5rem;
        }
        
        .order-item {
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .payment-success {
            text-align: center;
            padding: 3rem;
        }
        
        .payment-success i {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Paiement</h1>
            <p>Finalisez votre achat de tickets</p>
        </div>
        
        <?php if ($paymentComplete): ?>
            <!-- Affichage du message de succès -->
            <div class="payment-success">
                <i class="fas fa-check-circle"></i>
                <h2>Paiement réussi !</h2>
                <p>Votre achat a été traité avec succès. Vous pouvez maintenant accéder à tous les projets que vous avez achetés.</p>
                <a href="projects.php" class="button button-primary" style="margin-top: 1rem;">
                    <i class="fas fa-arrow-left"></i> Retour aux projets
                </a>
            </div>
        <?php else: ?>
            <!-- Affichage du formulaire de paiement -->
            <?php if ($paymentError): ?>
                <div class="alert alert-danger">
                    <?= $paymentError ?>
                </div>
            <?php endif; ?>
            
            <div class="checkout-container">
                <div class="payment-form">
                    <form method="POST" action="checkout.php">
                        <div class="form-section">
                            <h2>Informations de paiement</h2>
                            <div class="form-group">
                                <label for="cardName">Nom sur la carte</label>
                                <input type="text" id="cardName" name="cardName" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="cardNumber">Numéro de carte</label>
                                <input type="text" id="cardNumber" name="cardNumber" class="form-control" placeholder="1234 5678 9012 3456" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cardExpiry">Date d'expiration</label>
                                    <input type="text" id="cardExpiry" name="cardExpiry" class="form-control" placeholder="MM/AA" required>
                                </div>
                                <div class="form-group">
                                    <label for="cardCvv">CVV</label>
                                    <input type="text" id="cardCvv" name="cardCvv" class="form-control" placeholder="123" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h2>Informations de facturation</h2>
                            <div class="form-group">
                                <label for="billingName">Nom complet</label>
                                <input type="text" id="billingName" name="billingName" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="billingEmail">Email</label>
                                <input type="email" id="billingEmail" name="billingEmail" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="billingAddress">Adresse</label>
                                <textarea id="billingAddress" name="billingAddress" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="button button-success" style="width: 100%;">
                            <i class="fas fa-lock"></i> Payer <?= number_format($totalAmount, 2) ?> €
                        </button>
                    </form>
                </div>
                
                <div class="order-summary">
                    <h2>Résumé de la commande</h2>
                    <div class="order-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="order-item">
                                <div class="summary-row">
                                    <span><?= htmlspecialchars($item['projectName']) ?></span>
                                    <span><?= number_format($item['ticket_price'], 2) ?> €</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-row">
                        <span>Sous-total:</span>
                        <span><?= number_format($totalAmount, 2) ?> €</span>
                    </div>
                    <div class="summary-row">
                        <span>TVA (20%):</span>
                        <span><?= number_format($totalAmount * 0.2, 2) ?> €</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span><?= number_format($totalAmount * 1.2, 2) ?> €</span>
                    </div>
                    <a href="cart.php" class="button button-outline" style="width: 100%; margin-top: 1rem; text-align: center;">
                        <i class="fas fa-arrow-left"></i> Retour au panier
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Formatting for credit card number
        document.getElementById('cardNumber').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 16) {
                value = value.substring(0, 16);
            }
            
            // Format with spaces every 4 digits
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            e.target.value = formattedValue;
        });
        
        // Formatting for expiry date
        document.getElementById('cardExpiry').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.substring(0, 4);
            }
            
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            
            e.target.value = value;
        });
        
        // Formatting for CVV
        document.getElementById('cardCvv').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.substring(0, 4);
            }
            e.target.value = value;
        });
    </script>
</body>
</html> 