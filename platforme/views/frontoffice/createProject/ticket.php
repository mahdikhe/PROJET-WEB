<?php
require_once(__DIR__ . '/../../../config/Database.php');

if (isset($_GET['data'])) {
    $ticketData = json_decode(base64_decode($_GET['data']), true);
    
    if ($ticketData) {
        // Verify ticket in database
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM project_tickets WHERE id = ? AND payment_status = 'completed'");
        $stmt->execute([$ticketData['ticket_id']]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ticket) {
            // Valid ticket, display it
            ?>
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Ticket - <?= htmlspecialchars($ticketData['project_name']) ?></title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

:root {
    --primary-color: #2563eb;
    --secondary-color: #1d4ed8;
    --accent-color: #60a5fa;
    --background-color: #f1f5f9;
    --card-background: #ffffff;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --success-color: #10b981;
    
    --glass-background: rgba(255, 255, 255, 0.15);
    --glass-border: rgba(255, 255, 255, 0.3);
    --blur-radius: 12px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Montserrat', sans-serif;
    background: var(--background-color);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: var(--text-primary);
}

.ticket-container {
    max-width: 800px;
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    background: var(--glass-background);
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    backdrop-filter: blur(var(--blur-radius));
    border: 1px solid var(--glass-border);
}

.ticket-left {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    padding: 3rem 2rem;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-right: 1px solid var(--glass-border);
}

.ticket-left::before {
    content: '';
    position: absolute;
    right: -30px;
    height: 100%;
    width: 60px;
    background: var(--card-background);
    top: 0;
    clip-path: polygon(0 0, 100% 50%, 0 100%);
}

.qr-container {
    background: rgba(255, 255, 255, 0.05);
    padding: 2rem;
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    backdrop-filter: blur(var(--blur-radius));
    box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
    animation: glowQR 3s ease-in-out infinite;
}

.qr-code img {
    width: 200px;
    height: 200px;
    border-radius: 8px;
}

@keyframes glowQR {
    0%, 100% {
        box-shadow: 0 0 12px rgba(96, 165, 250, 0.5);
    }
    50% {
        box-shadow: 0 0 24px rgba(96, 165, 250, 0.9);
    }
}

.ticket-status {
    background: rgba(255, 255, 255, 0.15);
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    color: white;
    font-weight: 500;
    margin-top: 1rem;
    backdrop-filter: blur(4px);
    animation: pulseBadge 2s infinite;
}

@keyframes pulseBadge {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.ticket-right {
    padding: 3rem 2rem;
    background: var(--card-background);
}

.ticket-header {
    margin-bottom: 2rem;
}

.ticket-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.ticket-info {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--background-color);
    border-radius: 12px;
    transition: transform 0.2s ease;
}

.info-item:hover {
    transform: translateX(5px);
}

.info-item i {
    font-size: 1.25rem;
    color: var(--primary-color);
    width: 24px;
}

.info-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.info-value {
    font-weight: 600;
    color: var(--text-primary);
}

.validity-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #ecfdf5;
    color: var(--success-color);
    border-radius: 12px;
    font-weight: 600;
    margin-bottom: 2rem;
}

.validity-badge i {
    font-size: 1.25rem;
}

.download-button {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: var(--primary-color);
    color: white;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.download-button:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
}

@media (max-width: 768px) {
    .ticket-container {
        grid-template-columns: 1fr;
        max-width: 400px;
    }

    .ticket-left::before {
        display: none;
    }

    .ticket-left {
        padding: 2rem;
    }

    .ticket-right {
        padding: 2rem;
    }

    .qr-code img {
        width: 150px;
        height: 150px;
    }
}

                </style>
               <script>
    // Use sessionStorage instead of localStorage for tab-specific behavior
    function saveTicketData() {
        const urlParams = new URLSearchParams(window.location.search);
        const ticketData = urlParams.get('data');
        if (ticketData) {
            sessionStorage.setItem('ticketData', ticketData);
        }
    }

    function restoreTicketData() {
        const currentData = new URLSearchParams(window.location.search).get('data');
        if (!currentData) {
            const savedData = sessionStorage.getItem('ticketData');
            if (savedData) {
                window.location.href = window.location.pathname + '?data=' + savedData;
            }
        }
    }

    window.addEventListener('load', saveTicketData);
    document.addEventListener('DOMContentLoaded', restoreTicketData);
</script>
            </head>
            <body>
                <div class="ticket-container">
                    <div class="ticket-left">
                        <div class="qr-container">
                            <div class="qr-code">
                                <img src="<?= htmlspecialchars($ticketData['qr_path'] ?? '') ?>" alt="QR Code du ticket">
                            </div>
                        </div>
                        <div class="ticket-status">
                            <i class="fas fa-check-circle"></i> Ticket Valide
                        </div>
                    </div>
                    
                    <div class="ticket-right">
                        <div class="ticket-header">
                            <h1><?= htmlspecialchars($ticketData['project_name']) ?></h1>
                        </div>
                        
                        <div class="ticket-info">
                            <div class="info-item">
                                <i class="fas fa-ticket-alt"></i>
                                <div>
                                    <div class="info-label">ID du ticket</div>
                                    <div class="info-value"><?= htmlspecialchars($ticketData['ticket_id']) ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="far fa-calendar-alt"></i>
                                <div>
                                    <div class="info-label">Date d'achat</div>
                                    <div class="info-value"><?= date('d/m/Y H:i', $ticketData['timestamp']) ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-tag"></i>
                                <div>
                                    <div class="info-label">Montant</div>
                                    <div class="info-value"><?= number_format($ticketData['amount'], 2) ?> €</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="validity-badge">
                            <i class="fas fa-shield-alt"></i>
                            Ce ticket est valide et sécurisé
                        </div>

                        <a href="<?= htmlspecialchars($ticketData['qr_path'] ?? '') ?>" download class="download-button">
                            <i class="fas fa-download"></i>
                            Télécharger le QR Code
                        </a>
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