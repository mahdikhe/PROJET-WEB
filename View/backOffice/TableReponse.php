<?php
require "../../Controller/ReponseC.php";
$rep= new ReponseC();
$tab=$rep->listeReponse();
// Count reclamation statuses
$enAttenteCount = 0;
$resoluCount = 0;
$annuleCount = 0;

for ($i = 0; $i < count($tab); $i++) {
    if ($tab[$i]['status_reclamation'] == "En attente") {
        $enAttenteCount++;
    } elseif ($tab[$i]['status_reclamation'] == "Résolu") {
        $resoluCount++;
    } elseif ($tab[$i]['status_reclamation'] == "Annulé") {
        $annuleCount++;
    }
}

// Calculate percentages for the chart
$total = count($tab);
$enAttentePercent = $total > 0 ? round(($enAttenteCount / $total) * 100) : 0;
$resoluPercent = $total > 0 ? round(($resoluCount / $total) * 100) : 0;
$annulePercent = $total > 0 ? round(($annuleCount / $total) * 100) : 0;


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
            <h1 class="header-title">Gestion Reponse</h1>
            <div class="header-actions">
                <button class="btn btn-outline">
                    <i class="fas fa-download"></i> Export
                </button>
                
            </div>
        </header>

      

        

        <!-- Email List Section -->
        <div class="email-list-section">
            <div class="email-list-header">
                <h2 class="email-list-title">Table Reponse</h2>
             
            </div>
            <table class="email-table">
                <thead>
                    <tr>
                    <th>Titre Reclamation</th>
                    <th>Raison Reclamation</th>

                        <th>Description Reclamation</th>
                        <th>Date Reclamation</th>
                        <th>Status Reclamation</th>
                        <th>Description Reponse</th>
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
        echo "<td>" . htmlspecialchars($tab[$i]['description_reponse']) . "</td>";



       echo "<td>

    <form action='forumModifierReponse.php' method='post' style='padding: 4px 8px; font-size: 12px;'>
        <input type='hidden' name='id_reponse' value='" . $tab[$i]["id_reponse"] . "'> <!-- Include the candidate ID -->
        <button type='submit' class='btn btn-outline' style='padding: 4px 8px; font-size: 12px;'>
            <i class='fas fa-edit'></i>
        </button>
    </form>
    <form action='conSupprimerReponse.php' method='post' style='padding: 4px 8px; font-size: 12px;'>
        <input type='hidden' name='id_reponse' value='" . $tab[$i]["id_reponse"] . "'>
        <button type='submit' class='btn btn-outline'style='padding: 4px 8px; font-size: 12px;' >
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
                <h2 class="chart-title">Statut des Réclamations</h2>
                <div class="chart-container">
                    <canvas id="engagementChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #2ed573;"></div>
                        <span>En Attente</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ffa502;"></div>
                        <span>Résolu</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ff4757;"></div>
                        <span>Annulé</span>
                    </div>
                </div>
            </div>
            <div class="chart-card">
                <h2 class="chart-title">Répartition des Réclamations</h2>
                <div class="chart-container">
                    <canvas id="demographicsChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #2ed573;"></div>
                        <span>En Attente</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ffa502;"></div>
                        <span>Résolu</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ff4757;"></div>
                        <span>Annulé</span>
                    </div>
                </div>
            </div>
        </div>

        
    </main>

    <!-- Add Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Update Chart and Map Initialization Script -->
    <script>
        // Initialize Engagement Chart with reclamation status data
        const engagementCtx = document.getElementById('engagementChart').getContext('2d');
        new Chart(engagementCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: 'En Attente',
                    data: [<?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>, <?php echo $enAttenteCount; ?>],
                    borderColor: '#2ed573',
                    backgroundColor: 'rgba(46, 213, 115, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Résolu',
                    data: [<?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>, <?php echo $resoluCount; ?>],
                    borderColor: '#ffa502',
                    backgroundColor: 'rgba(255, 165, 2, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Annulé',
                    data: [<?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>, <?php echo $annuleCount; ?>],
                    borderColor: '#ff4757',
                    backgroundColor: 'rgba(255, 71, 87, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: 'var(--text-dark)',
                        bodyColor: 'var(--text-medium)',
                        borderColor: 'var(--border-color)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Initialize Demographics Chart with reclamation status data
        const demographicsCtx = document.getElementById('demographicsChart').getContext('2d');
        new Chart(demographicsCtx, {
            type: 'doughnut',
            data: {
                labels: ['En Attente', 'Résolu', 'Annulé'],
                datasets: [{
                    data: [<?php echo $enAttentePercent; ?>, <?php echo $resoluPercent; ?>, <?php echo $annulePercent; ?>],
                    backgroundColor: [
                        '#2ed573',
                        '#ffa502',
                        '#ff4757'
                    ],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 2000,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });

        // Initialize Map with enhanced features
        const map = L.map('audienceMap').setView([51.505, -0.09], 2);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Enhanced audience locations data with more cities
        const audienceLocations = [
            { lat: 51.505, lng: -0.09, count: 1000, name: 'London', region: 'europe' },
            { lat: 40.7128, lng: -74.0060, count: 2000, name: 'New York', region: 'americas' },
            { lat: 48.8566, lng: 2.3522, count: 1500, name: 'Paris', region: 'europe' },
            { lat: 35.6762, lng: 139.6503, count: 800, name: 'Tokyo', region: 'asia' },
            { lat: -33.8688, lng: 151.2093, count: 500, name: 'Sydney', region: 'asia' },
            { lat: 37.7749, lng: -122.4194, count: 1200, name: 'San Francisco', region: 'americas' },
            { lat: 52.5200, lng: 13.4050, count: 900, name: 'Berlin', region: 'europe' },
            { lat: 55.7558, lng: 37.6173, count: 700, name: 'Moscow', region: 'europe' },
            { lat: 19.4326, lng: -99.1332, count: 600, name: 'Mexico City', region: 'americas' },
            { lat: -23.5505, lng: -46.6333, count: 550, name: 'São Paulo', region: 'americas' },
            { lat: 28.6139, lng: 77.2090, count: 750, name: 'Delhi', region: 'asia' },
            { lat: 30.0444, lng: 31.2357, count: 400, name: 'Cairo', region: 'africa' },
            { lat: -26.2041, lng: 28.0473, count: 350, name: 'Johannesburg', region: 'africa' },
            { lat: 1.3521, lng: 103.8198, count: 450, name: 'Singapore', region: 'asia' }
        ];

        // Add circles with enhanced styling
        const circles = [];
        audienceLocations.forEach((location, index) => {
            setTimeout(() => {
                const circle = L.circle([location.lat, location.lng], {
                    color: '#00b8a9',
                    fillColor: '#00b8a9',
                    fillOpacity: 0.5,
                    radius: Math.sqrt(location.count) * 100
                }).addTo(map);

                circle.bindPopup(`
                    <div style="padding: 10px;">
                        <strong>${location.name}</strong><br>
                        Audience: ${location.count.toLocaleString()}<br>
                        Region: ${location.region.charAt(0).toUpperCase() + location.region.slice(1)}<br>
                        Growth: +${Math.floor(Math.random() * 20) + 5}%
                    </div>
                `);

                circles.push(circle);
            }, index * 200);
        });

        // Add zoom controls
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // Add map filter functionality
        document.getElementById('mapFilter').addEventListener('change', function(e) {
            const selectedRegion = e.target.value;
            circles.forEach(circle => {
                const location = audienceLocations.find(loc => 
                    loc.lat === circle.getLatLng().lat && 
                    loc.lng === circle.getLatLng().lng
                );
                if (selectedRegion === 'all' || location.region === selectedRegion) {
                    circle.addTo(map);
                } else {
                    map.removeLayer(circle);
                }
            });
        });

        // Add heatmap toggle functionality
        let heatmapLayer = null;
        document.getElementById('toggleHeatmap').addEventListener('click', function() {
            if (heatmapLayer) {
                map.removeLayer(heatmapLayer);
                heatmapLayer = null;
            } else {
                const heatmapData = audienceLocations.map(loc => ({
                    lat: loc.lat,
                    lng: loc.lng,
                    value: loc.count
                }));
                
                heatmapLayer = L.heatLayer(heatmapData, {
                    radius: 25,
                    blur: 15,
                    maxZoom: 10,
                    gradient: {
                        0.4: 'blue',
                        0.6: 'cyan',
                        0.7: 'lime',
                        0.8: 'yellow',
                        1.0: 'red'
                    }
                }).addTo(map);
            }
        });
    </script>
</body>
</html> 