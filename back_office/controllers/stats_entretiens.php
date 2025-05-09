<?php
require_once '../model/config.php';

// Set page header and meta information
$pageTitle = "Statistiques des Candidatures";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CityPulse</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo $pageTitle; ?></h1>
                    <a href="../view/entretiens.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        <i class="fas fa-arrow-left mr-2"></i> Retour aux Candidatures
                    </a>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-grow">
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <div class="px-4 py-6 sm:px-0">
                    <!-- Single centered chart card -->
                    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
                        <h2 class="text-xl font-semibold mb-4 text-center">Répartition des candidatures par statut</h2>
                        <div class="h-80">
                            <canvas id="applicationsByStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-gray-500">© <?php echo date('Y'); ?> CityPulse - Tous droits réservés</p>
            </div>
        </footer>
    </div>

    <script>
        // Generate colors for charts
        function generateColors(count) {
            const colors = [
                '#6944ff', // Purple (primary color from your site)
                '#48BB78', // Green (for accepted)
                '#F56565', // Red (for rejected)
                '#ECC94B', // Yellow
                '#4299E1', // Blue
                '#ED64A6', // Pink
                '#9F7AEA', // Lavender
                '#ED8936', // Orange
                '#38B2AC', // Teal
                '#667EEA'  // Indigo
            ];
            
            // If we need more colors than available, repeat them
            const result = [];
            for (let i = 0; i < count; i++) {
                result.push(colors[i % colors.length]);
            }
            
            return result;
        }

        // Create Pie Chart
        function createPieChart(canvasId, title, labels, data) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            
            // Generate colors
            const colors = generateColors(labels.length);
            
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        title: {
                            display: true,
                            text: title
                        }
                    }
                }
            });
        }

        // Main function to initialize charts
        async function initCharts() {
            try {
                // Get application data directly from PHP
                let appData = <?php
                    // Include database configuration
                    require_once '../model/config.php';
                    
                    // Get real status counts directly from the database
                    function getStatusCounts() {
                        global $servername, $username, $password, $dbname;
                        
                        try {
                            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            
                            // Count applications by status
                            $query = "SELECT 
                                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                                        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
                                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
                                     FROM entretiens";
                            
                            $stmt = $pdo->prepare($query);
                            $stmt->execute();
                            
                            return $stmt->fetch(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            error_log("Database error in getStatusCounts: " . $e->getMessage());
                            return ['pending_count' => 0, 'accepted_count' => 0, 'rejected_count' => 0];
                        }
                    }
                    
                    // Get status counts
                    $statusCounts = getStatusCounts();
                    
                    // Create data for the frontend
                    $data = [
                        'statusCounts' => $statusCounts
                    ];
                    
                    echo json_encode($data);
                ?> || { statusCounts: {pending_count: 0, accepted_count: 0, rejected_count: 0} };

                // Extract the status counts
                const { statusCounts } = appData;
                
                // Create data for the status pie chart directly from counts
                const statusData = {
                    labels: ['En attente', 'Accepté', 'Rejeté'],
                    values: [
                        parseInt(statusCounts.pending_count) || 0,
                        parseInt(statusCounts.accepted_count) || 0, 
                        parseInt(statusCounts.rejected_count) || 0
                    ]
                };
                
                // Filter out zero values and their labels
                const filteredLabels = [];
                const filteredValues = [];
                statusData.labels.forEach((label, index) => {
                    if (statusData.values[index] > 0) {
                        filteredLabels.push(label);
                        filteredValues.push(statusData.values[index]);
                    }
                });
                
                // If no data, show message
                if (filteredValues.length === 0) {
                    document.getElementById('applicationsByStatusChart').parentNode.innerHTML = 
                        '<div class="flex items-center justify-center h-full"><p class="text-gray-500"><i class="fas fa-info-circle mr-2"></i>Aucune candidature trouvée</p></div>';
                } else {
                    // Create status pie chart with filtered data
                    createPieChart('applicationsByStatusChart', 'Répartition par statut', filteredLabels, filteredValues);
                }
            } catch (error) {
                console.error('Error initializing chart:', error);
                document.querySelector('.h-80').innerHTML = 
                    '<div class="flex items-center justify-center h-full"><p class="text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Erreur lors du chargement du graphique</p></div>';
            }
        }

        // Initialize charts when the page loads
        document.addEventListener('DOMContentLoaded', initCharts);
    </script>
</body>
</html>