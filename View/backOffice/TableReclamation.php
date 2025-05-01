<?php
require "../../Controller/ReclamationC.php";
$reclamation= new ReclamationC();
$tab=$reclamation->listeReclamation();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="style.css">
    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo">
        
        </div>
        <nav>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link ">
                        <i class="fas fa-home"></i>
                        <span>Overview</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Audience</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Locations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                     <li class="nav-item active">
                        <a href="TableReclamation.php" class="nav-link">
                            <i class="fas fa-exclamation-circle"></i> <!-- or use fa-comment-dots -->
                            <span>Reclamation</span>
                        </a>
                    </li>
                        <li class="nav-item active">
                            <a href="TableReponse.php" class="nav-link">
                                <i class="fas fa-reply"></i> <!-- or use fa-comments -->
                                <span>Reponse</span>
                            </a>
                        </li>

            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <h1 class="header-title">Gestion Reclamation</h1>
            <div class="header-actions">
                <button class="btn btn-outline">
                    <i class="fas fa-download"></i> Export
                </button>
                
            </div>
        </header>

      

        

        <!-- Email List Section -->
        <div class="email-list-section">
            <div class="email-list-header">
                <h2 class="email-list-title">Table Reclamation</h2>
             
            </div>
            <table class="email-table">
                <thead>
                    <tr>
                    <th>Titre Reclamation</th>
                    <th>Raison Reclamation</th>

                        <th>Description Reclamation</th>
                        <th>Date Reclamation</th>
                        <th>Status Reclamation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php
    for ($i = 0; $i < count($tab); $i++) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($tab[$i]['titre_reclamation']) . "</td>";
        echo "<td>" . htmlspecialchars($tab[$i]['raison_reclamation']) . "</td>";

        echo "<td>" . htmlspecialchars($tab[$i]['description_reclamation']) . "</td>";
        echo "<td>" . htmlspecialchars($tab[$i]['date_reclamation']) . "</td>";

        // Display status
        if ($tab[$i]['status_reclamation'] == "En attente") {
            echo "<td><span class='email-status status-active'>En Attente</span></td>";
        } elseif ($tab[$i]['status_reclamation'] == "Résolu") {
            echo "<td><span class='email-status status-pending'>Résolu</span></td>";
        } elseif ($tab[$i]['status_reclamation'] == "Annulé") {
            echo "<td><span class='email-status status-inactive'>Annulé</span></td>";
        }



       echo "<td>

    <form action='forumAjouterReponse.php' method='post' style='padding: 4px 8px; font-size: 12px;'>
        <input type='hidden' name='id_reclamation' value='" . $tab[$i]["id_reclamation"] . "'> <!-- Include the candidate ID -->
        <button type='submit' class='btn btn-outline' style='padding: 4px 8px; font-size: 12px;'>
            <i class='fas fa-edit'></i>
        </button>
    </form>
    <form action='conAnnulerReclamation.php' method='post' style='padding: 4px 8px; font-size: 12px;'>
        <input type='hidden' name='id_reclamation' value='" . $tab[$i]["id_reclamation"] . "'>
        <button type='submit' class='btn btn-outline' style='padding: 4px 8px; font-size: 12px;'>
            <i class='fas fa-trash'></i>
        </button>
    </form>
  </td>";



    }
    ?>
</tbody>

            </table>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <h2 class="chart-title">Évolution des Réclamations</h2>
                <div class="chart-container">
                    <canvas id="reclamationsChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #00b8a9;"></div>
                        <span>En Attente</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #6c63ff;"></div>
                        <span>Résolu</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ff6b6b;"></div>
                        <span>Annulé</span>
                    </div>
                </div>
            </div>
            <div class="chart-card">
                <h2 class="chart-title">Répartition des Statuts</h2>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #00b8a9;"></div>
                        <span>En Attente</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #6c63ff;"></div>
                        <span>Résolu</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ff6b6b;"></div>
                        <span>Annulé</span>
                    </div>
                </div>
            </div>
        </div>

        
    </main>

    <!-- Add Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Update Chart Initialization Script -->
    <script>
        // Prepare data from PHP
        <?php
        $statusCount = array(
            'En attente' => 0,
            'Résolu' => 0,
            'Annulé' => 0
        );
        
        $monthlyData = array();
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[date('M', mktime(0, 0, 0, $i, 1))] = array(
                'En attente' => 0,
                'Résolu' => 0,
                'Annulé' => 0
            );
        }
        
        foreach ($tab as $reclamation) {
            // Count by status
            $status = $reclamation['status_reclamation'];
            if (isset($statusCount[$status])) {
                $statusCount[$status]++;
            }
            
            // Count by month
            $month = date('M', strtotime($reclamation['date_reclamation']));
            if (isset($monthlyData[$month][$status])) {
                $monthlyData[$month][$status]++;
            }
        }
        ?>

        // Initialize Reclamations Evolution Chart
        const reclamationsCtx = document.getElementById('reclamationsChart').getContext('2d');
        new Chart(reclamationsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($monthlyData)); ?>,
                datasets: [{
                    label: 'En Attente',
                    data: <?php echo json_encode(array_map(function($m) { return $m['En attente']; }, $monthlyData)); ?>,
                    borderColor: '#00b8a9',
                    backgroundColor: 'rgba(0, 184, 169, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Résolu',
                    data: <?php echo json_encode(array_map(function($m) { return $m['Résolu']; }, $monthlyData)); ?>,
                    borderColor: '#6c63ff',
                    backgroundColor: 'rgba(108, 99, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Annulé',
                    data: <?php echo json_encode(array_map(function($m) { return $m['Annulé']; }, $monthlyData)); ?>,
                    borderColor: '#ff6b6b',
                    backgroundColor: 'rgba(255, 107, 107, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Initialize Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['En Attente', 'Résolu', 'Annulé'],
                datasets: [{
                    data: [
                        <?php echo $statusCount['En attente']; ?>,
                        <?php echo $statusCount['Résolu']; ?>,
                        <?php echo $statusCount['Annulé']; ?>
                    ],
                    backgroundColor: [
                        '#00b8a9',
                        '#6c63ff',
                        '#ff6b6b'
                    ],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html> 