<?php
require_once(__DIR__ . '/../../../config/Database.php');

$db = Database::getInstance();
$conn = $db->getConnection();

if (isset($_GET['ticket_data'])) {
    $ticketData = json_decode(base64_decode($_GET['ticket_data']), true);
    
    if ($ticketData) {
        // Verify ticket in database
        $stmt = $conn->prepare("SELECT * FROM project_tickets WHERE id = ? AND payment_status = 'completed'");
        $stmt->execute([$ticketData['ticket_id']]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ticket) {
            // Valid ticket, show the ticket details
            ?>
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Ticket - <?= htmlspecialchars($ticketData['project_name']) ?></title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
                <style>
                    :root {
                        --primary: #4361ee;
                        --secondary: #3f37c9;
                        --accent: #00a854;
                        --background: #f5f7fa;
                        --text: #2d3748;
                        --gradient: linear-gradient(135deg, #4361ee, #3f37c9);
                    }

                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Poppins', sans-serif !important;
                        background: #f5f7fa !important;
                        min-height: 100vh;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 20px;
                        perspective: 1000px;
                    }

                    .ticket-container {
                        max-width: 600px;
                        width: 100%;
                        background: #ffffff;
                        border-radius: 20px;
                        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
                        overflow: hidden;
                        transform-style: preserve-3d;
                        animation: ticketAppear 0.6s ease-out;
                        position: relative;
                        z-index: 1;
                    }

                    @keyframes ticketAppear {
                        0% {
                            opacity: 0;
                            transform: translateY(30px) rotateX(10deg);
                        }
                        100% {
                            opacity: 1;
                            transform: translateY(0) rotateX(0);
                        }
                    }

                    .ticket-header {
                        background: var(--gradient);
                        padding: 2rem;
                        text-align: center;
                        position: relative;
                        overflow: hidden;
                    }

                    .ticket-header::before {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: -50%;
                        width: 200%;
                        height: 100%;
                        background: rgba(255,255,255,0.1);
                        transform: skewX(-30deg);
                        animation: shine 3s infinite;
                    }

                    @keyframes shine {
                        0% { transform: translateX(-100%) skewX(-30deg); }
                        100% { transform: translateX(100%) skewX(-30deg); }
                    }

                    .ticket-header h1 {
                        color: white;
                        font-size: 1.8rem;
                        margin-bottom: 0.5rem;
                        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }

                    .ticket-header p {
                        color: rgba(255,255,255,0.9);
                        font-size: 1rem;
                    }

                    .ticket-body {
                        padding: 2rem;
                        position: relative;
                    }

                    .ticket-info {
                        background: rgba(67, 97, 238, 0.05);
                        padding: 1.5rem;
                        border-radius: 15px;
                        margin: 1.5rem 0;
                        transform: translateZ(20px);
                    }

                    .ticket-info p {
                        margin: 0.8rem 0;
                        color: var(--text);
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        animation: slideIn 0.5s ease-out backwards;
                    }

                    @keyframes slideIn {
                        from {
                            opacity: 0;
                            transform: translateX(-20px);
                        }
                        to {
                            opacity: 1;
                            transform: translateX(0);
                        }
                    }

                    .ticket-info p:nth-child(1) { animation-delay: 0.1s; }
                    .ticket-info p:nth-child(2) { animation-delay: 0.2s; }
                    .ticket-info p:nth-child(3) { animation-delay: 0.3s; }

                    .ticket-info i {
                        color: var(--primary);
                        font-size: 1.2rem;
                    }

                    .ticket-info strong {
                        color: var(--primary);
                        font-weight: 600;
                        margin-right: 5px;
                    }

                    .ticket-validity {
                        text-align: center;
                        padding: 1.5rem;
                        background: linear-gradient(135deg, #e3fcef 0%, #d0f7e6 100%);
                        color: var(--accent);
                        border-radius: 15px;
                        margin-top: 2rem;
                        position: relative;
                        overflow: hidden;
                        animation: validityPulse 2s infinite;
                    }

                    @keyframes validityPulse {
                        0%, 100% { transform: scale(1); }
                        50% { transform: scale(1.02); }
                    }

                    .ticket-validity i {
                        font-size: 2.5rem;
                        margin-bottom: 1rem;
                        display: block;
                        animation: checkmark 0.5s ease-out 0.5s backwards;
                    }

                    @keyframes checkmark {
                        0% {
                            opacity: 0;
                            transform: scale(0.5) rotate(-45deg);
                        }
                        100% {
                            opacity: 1;
                            transform: scale(1) rotate(0);
                        }
                    }

                    .ticket-validity p {
                        font-weight: 500;
                    }

                    @media (max-width: 480px) {
                        .ticket-container {
                            margin: 10px;
                        }

                        .ticket-header h1 {
                            font-size: 1.5rem;
                        }

                        .ticket-info {
                            padding: 1rem;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="ticket-container">
                    <div class="ticket-header">
                        <h1><?= htmlspecialchars($ticketData['project_name']) ?></h1>
                        <p>Ticket valide</p>
                    </div>
                    
                    <div class="ticket-body">
                        <div class="ticket-info">
                            <p>
                                <i class="fas fa-ticket-alt"></i>
                                <strong>ID du ticket:</strong> 
                                <?= htmlspecialchars($ticketData['ticket_id']) ?>
                            </p>
                            <p>
                                <i class="far fa-calendar-alt"></i>
                                <strong>Date d'achat:</strong> 
                                <?= date('d/m/Y H:i', $ticketData['timestamp']) ?>
                            </p>
                            <p>
                                <i class="fas fa-tag"></i>
                                <strong>Montant:</strong> 
                                <?= number_format($ticketData['amount'], 2) ?> €
                            </p>
                        </div>
                        
                        <div class="ticket-validity">
                            <i class="fas fa-check-circle"></i>
                            <p>Ce ticket est valide et peut être utilisé</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
        } else {
            echo "<div style='text-align: center; padding: 50px; color: #dc3545;'>
                    <h2>Ticket invalide ou expiré</h2>
                  </div>";
        }
    } else {
        echo "<div style='text-align: center; padding: 50px; color: #dc3545;'>
                <h2>Données de ticket invalides</h2>
              </div>";
    }
} else {
    echo "<div style='text-align: center; padding: 50px; color: #dc3545;'>
            <h2>Aucune donnée de ticket fournie</h2>
          </div>";
}
?>