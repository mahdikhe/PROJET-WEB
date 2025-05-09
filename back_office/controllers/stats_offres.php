<?php
require_once '../model/config.php';

// Set page header and meta information
$pageTitle = "Statistiques des Offres";
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
                    <a href="../view/offres.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        <i class="fas fa-arrow-left mr-2"></i> Retour aux Offres
                    </a>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-grow">
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <div class="px-4 py-6 sm:px-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Chart 1: Offers by Type -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h2 class="text-xl font-semibold mb-4">Répartition des offres par type</h2>
                            <div class="h-64">
                                <canvas id="offersByTypeChart"></canvas>
                            </div>
                        </div>

                        <!-- Chart 2: Offers by Location -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h2 class="text-xl font-semibold mb-4">Répartition des offres par emplacement</h2>
                            <div class="h-64">
                                <canvas id="offersByLocationChart"></canvas>
                            </div>
                        </div>

                        <!-- Chart 3: Offers by Month -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h2 class="text-xl font-semibold mb-4">Évolution des offres par mois</h2>
                            <div class="h-64">
                                <canvas id="offersByMonthChart"></canvas>
                            </div>
                        </div>

                        <!-- Chart 4: Top Employers -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h2 class="text-xl font-semibold mb-4">Top 5 des entreprises</h2>
                            <div class="h-64">
                                <canvas id="topEmployersChart"></canvas>
                            </div>
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
        // Fetch the data from the server
        fetch('../model/getAllOffers.php')
            .then(response => response.json())
            .then(offers => {
                // Process data for Charts
                const typeData = processOffersByType(offers);
                const locationData = processOffersByLocation(offers);
                const monthData = processOffersByMonth(offers);
                const employerData = processTopEmployers(offers);

                // Create Charts
                createPieChart('offersByTypeChart', 'Répartition par type', typeData.labels, typeData.values);
                createPieChart('offersByLocationChart', 'Répartition par emplacement', locationData.labels, locationData.values);
                createLineChart('offersByMonthChart', 'Évolution par mois', monthData.labels, monthData.values);
                createBarChart('topEmployersChart', 'Top 5 des entreprises', employerData.labels, employerData.values);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                // Display error message on the page
                document.querySelectorAll('.h-64').forEach(container => {
                    container.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Erreur lors du chargement des données</p></div>';
                });
            });

        // Process offers by type
        function processOffersByType(offers) {
            const typeCounts = {};

            offers.forEach(offer => {
                const type = offer.type || 'Non spécifié';
                typeCounts[type] = (typeCounts[type] || 0) + 1;
            });

            return {
                labels: Object.keys(typeCounts),
                values: Object.values(typeCounts)
            };
        }

        // Process offers by location
        function processOffersByLocation(offers) {
            const locationCounts = {};

            offers.forEach(offer => {
                const location = offer.emplacement || 'Non spécifié';
                locationCounts[location] = (locationCounts[location] || 0) + 1;
            });

            // Sort by count and take top 5
            const sortedLocations = Object.entries(locationCounts)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 5);

            return {
                labels: sortedLocations.map(item => item[0]),
                values: sortedLocations.map(item => item[1])
            };
        }

        // Process offers by month
        function processOffersByMonth(offers) {
            const monthCounts = {};
            const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

            // Initialize all months with 0
            months.forEach((month, index) => {
                monthCounts[index] = 0;
            });

            offers.forEach(offer => {
                if (offer.date) {
                    const date = new Date(offer.date);
                    const month = date.getMonth();
                    monthCounts[month]++;
                }
            });

            return {
                labels: months,
                values: Object.values(monthCounts)
            };
        }

        // Process top employers
        function processTopEmployers(offers) {
            const employerCounts = {};

            offers.forEach(offer => {
                const employer = offer.entreprise || 'Non spécifié';
                employerCounts[employer] = (employerCounts[employer] || 0) + 1;
            });

            // Sort by count and take top 5
            const sortedEmployers = Object.entries(employerCounts)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 5);

            return {
                labels: sortedEmployers.map(item => item[0]),
                values: sortedEmployers.map(item => item[1])
            };
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

        // Create Line Chart
        function createLineChart(canvasId, title, labels, data) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Nombre d\'offres',
                        data: data,
                        borderColor: '#4C51BF',
                        backgroundColor: 'rgba(76, 81, 191, 0.1)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: title
                        }
                    }
                }
            });
        }

        // Create Bar Chart
        function createBarChart(canvasId, title, labels, data) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            
            // Generate colors
            const colors = generateColors(labels.length);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Nombre d\'offres',
                        data: data,
                        backgroundColor: colors,
                        borderColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: title
                        }
                    }
                }
            });
        }

        // Generate colors for charts
        function generateColors(count) {
            const colors = [
                '#4C51BF', '#ED8936', '#38B2AC', '#F56565', '#9F7AEA',
                '#667EEA', '#48BB78', '#4299E1', '#ED64A6', '#ECC94B'
            ];
            
            // If we need more colors than available, repeat them
            const result = [];
            for (let i = 0; i < count; i++) {
                result.push(colors[i % colors.length]);
            }
            
            return result;
        }
    </script>
</body>
</html>