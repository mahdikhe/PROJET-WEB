<?php
require "../../Controller/ReponseC.php";
$rep = new ReponseC();
$rec = new ReclamationC();

// Get all responses from database
$tab = $rep->listeReponse();

// Get all reclamations from database
$allReclamations = $rec->listeReclamation();

// Get statistics from database
$stats = $rep->getReponseStats();

// Convert PHP data to JSON for JavaScript
$jsonData = json_encode($tab, JSON_UNESCAPED_UNICODE);

// Calculate response metrics
$totalResponses = count($tab); // Count total responses
$totalReclamations = count($allReclamations); // Count all reclamations
$responseRate = $totalReclamations > 0 ? round(($totalResponses / $totalReclamations) * 100) : 0;
$pendingReclamations = max(0, $totalReclamations - $totalResponses); // Ensure non-negative value

// Get monthly data from database
$monthlyData = $rep->getMonthlyStats();

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

       <!--init data table!-->
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


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
                <button class="btn btn-outline export-btn">
                    <i class="fas fa-download"></i> Export
                </button>
                
            </div>
        </header>

      

        

        <!-- Email List Section -->
        <div class="email-list-section">
            <div class="email-list-header">
                <h2 class="email-list-title">Table Reponse</h2>
                <div class="table-controls">
                    <a href="TableReclamation.php" class="btn btn-primary" style="margin-right: 10px;">
                        <i class="fas fa-exclamation-circle"></i> Voir les Réclamations
                    </a>
                    <input type="text" id="searchInput" placeholder="Rechercher..." class="search-input">
                    <select id="sortSelect" class="sort-select">
                        <option value="">Trier par...</option>
                        <option value="titre">Titre</option>
                        <option value="raison">Raison</option>
                        <option value="date">Date</option>
                        <option value="status">Status</option>
                        <option value="description">Description</option>
                    </select>
                </div>
            </div>
            <table id="myTable" class="email-table">
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
                </tbody>
            </table>
            <div class="pagination"></div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <h2 class="chart-title">Taux de Réponse</h2>
                <div class="chart-container">
                    <canvas id="responseRateChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #2ed573;"></div>
                        <span>Réponses (<?php echo $totalResponses; ?>)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ff4757;"></div>
                        <span>Réclamations (<?php echo $totalReclamations; ?>)</span>
                    </div>
                </div>
                <div class="stats-summary">
                    <p>Taux de réponse: <?php echo $responseRate; ?>%</p>
                    <p>Réclamations sans réponse: <?php echo $pendingReclamations; ?></p>
                </div>
            </div>
        </div>

        
    </main>

    <!-- Add Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Update Chart and Map Initialization Script -->
    <script>
        // Initialize Response Rate Chart
        const responseRateCtx = document.getElementById('responseRateChart').getContext('2d');
        new Chart(responseRateCtx, {
            type: 'bar',
            data: {
                labels: ['Réclamations vs Réponses'],
                datasets: [
                    {
                        label: 'Réponses',
                        data: [<?php echo $totalResponses; ?>],
                        backgroundColor: '#2ed573',
                        borderColor: '#2ed573',
                        borderWidth: 1
                    },
                    {
                        label: 'Réclamations',
                        data: [<?php echo $totalReclamations; ?>],
                        backgroundColor: '#ff4757',
                        borderColor: '#ff4757',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label;
                                const value = context.raw;
                                const total = context.dataset.label === 'Réponses' ? <?php echo $totalReclamations; ?> : <?php echo $totalReclamations; ?>;
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        title: {
                            display: true,
                            text: 'Nombre'
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

    <script>
        $(document).ready(function() {
            let currentPage = 1;
            const itemsPerPage = 10;
            let allData = <?php echo $jsonData; ?>;
            let filteredData = [...allData];

            // Function to render table with pagination
            function renderTable() {
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const paginatedData = filteredData.slice(startIndex, endIndex);
                
                let tableHtml = '';
                
                paginatedData.forEach(function(item) {
                    let statusClass = '';
                    if (item.status_reclamation === 'En attente') {
                        statusClass = 'status-active';
                    } else if (item.status_reclamation === 'Résolu') {
                        statusClass = 'status-pending';
                    } else if (item.status_reclamation === 'Annulé') {
                        statusClass = 'status-inactive';
                    }

                    tableHtml += `
                        <tr>
                            <td>${item.titre_reclamation}</td>
                            <td>${item.raison_reclamation}</td>
                            <td>${item.description_reclamation}</td>
                            <td>${item.date_reclamation}</td>
                            <td><span class='email-status ${statusClass}'>${item.status_reclamation}</span></td>
                            <td>${item.description_reponse}</td>
                            <td>
                                <form action='forumModifierReponse.php' method='post' style='padding: 4px 8px; font-size: 12px;'>
                                    <input type='hidden' name='id_reponse' value='${item.id_reponse}'>
                                    <button type='submit' class='btn btn-outline' style='padding: 4px 8px; font-size: 12px;'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                </form>
                                <form action='conSupprimerReponse.php' method='post' style='padding: 4px 8px; font-size: 12px;'>
                                    <input type='hidden' name='id_reponse' value='${item.id_reponse}'>
                                    <button type='submit' class='btn btn-outline' style='padding: 4px 8px; font-size: 12px;'>
                                        <i class='fas fa-trash'></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    `;
                });
                
                $('#myTable tbody').html(tableHtml);
                renderPagination();
            }

            // Function to render pagination
            function renderPagination() {
                const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                let paginationHtml = '';
                
                // Previous button
                paginationHtml += `
                    <button class="prev-page" ${currentPage === 1 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                `;
                
                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    paginationHtml += `
                        <button class="page-number ${i === currentPage ? 'active' : ''}" data-page="${i}">
                            ${i}
                        </button>
                    `;
                }
                
                // Next button
                paginationHtml += `
                    <button class="next-page" ${currentPage === totalPages ? 'disabled' : ''}>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                `;
                
                $('.pagination').html(paginationHtml);
            }

            // Search functionality
            $('#searchInput').on('keyup', function() {
                const searchText = $(this).val().toLowerCase();
                filteredData = allData.filter(item => 
                    item.titre_reclamation.toLowerCase().includes(searchText) ||
                    item.raison_reclamation.toLowerCase().includes(searchText) ||
                    item.description_reclamation.toLowerCase().includes(searchText) ||
                    item.status_reclamation.toLowerCase().includes(searchText) ||
                    item.description_reponse.toLowerCase().includes(searchText)
                );
                currentPage = 1;
                renderTable();
            });

            // Sort functionality
            $('#sortSelect').on('change', function() {
                const sortBy = $(this).val();
                if (sortBy) {
                    filteredData.sort((a, b) => {
                        let valueA = a[sortBy + '_reclamation'] || a[sortBy + '_reponse'] || a[sortBy];
                        let valueB = b[sortBy + '_reclamation'] || b[sortBy + '_reponse'] || b[sortBy];
                        
                        if (sortBy === 'date') {
                            return new Date(valueB) - new Date(valueA);
                        }
                        return valueA.localeCompare(valueB);
                    });
                    renderTable();
                }
            });

            // Pagination click handlers
            $(document).on('click', '.page-number', function() {
                currentPage = parseInt($(this).data('page'));
                renderTable();
            });

            $(document).on('click', '.prev-page', function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            });

            $(document).on('click', '.next-page', function() {
                const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            });

            // Initial render
            renderTable();
        });
    </script>

    <!-- Add jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <script>
        $(document).ready(function() {
            let currentPage = 1;
            const itemsPerPage = 10;
            let allData = <?php echo $jsonData; ?>;
            let filteredData = [...allData];

            // Function to export table to PDF
            function exportToPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                // Add title
                doc.setFontSize(16);
                doc.text('Liste des Réponses', 14, 15);
                
                // Add date
                doc.setFontSize(10);
                doc.text('Date d\'export: ' + new Date().toLocaleDateString(), 14, 22);
                
                // Prepare table data
                const tableData = filteredData.map(item => [
                    item.titre_reclamation,
                    item.raison_reclamation,
                    item.description_reclamation,
                    item.date_reclamation,
                    item.status_reclamation,
                    item.description_reponse
                ]);
                
                // Add table
                doc.autoTable({
                    head: [['Titre', 'Raison', 'Description', 'Date', 'Status', 'Réponse']],
                    body: tableData,
                    startY: 30,
                    theme: 'grid',
                    styles: {
                        fontSize: 8,
                        cellPadding: 2
                    },
                    headStyles: {
                        fillColor: [0, 184, 169],
                        textColor: 255
                    },
                    columnStyles: {
                        0: { cellWidth: 35 },
                        1: { cellWidth: 35 },
                        2: { cellWidth: 50 },
                        3: { cellWidth: 25 },
                        4: { cellWidth: 20 },
                        5: { cellWidth: 50 }
                    }
                });
                
                // Save the PDF
                doc.save('reponses_' + new Date().toISOString().slice(0,10) + '.pdf');
            }

            // Add click handler for export button only
            $('.export-btn').click(function(e) {
                e.preventDefault();
                exportToPDF();
            });

            // ... rest of your existing code ...
        });
    </script>

    <style>
        .stats-summary {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        .stats-summary p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .stats-summary p:first-child {
            font-weight: bold;
            color: #333;
            font-size: 16px;
        }
        .table-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-input {
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            width: 250px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .search-input:focus {
            outline: none;
            border-color: #2ed573;
            box-shadow: 0 2px 8px rgba(46,213,115,0.1);
        }

        .search-input::placeholder {
            color: #999;
        }

        .sort-select {
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            min-width: 150px;
        }

        .sort-select:focus {
            outline: none;
            border-color: #2ed573;
            box-shadow: 0 2px 8px rgba(46,213,115,0.1);
        }

        .sort-select option {
            padding: 10px;
            font-size: 14px;
        }

        .email-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .email-list-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        @media (max-width: 768px) {
            .table-controls {
                flex-direction: column;
                width: 100%;
            }

            .search-input, .sort-select {
                width: 100%;
            }
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            background-color: #fff;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover:not(:disabled) {
            background-color: #f8f9fa;
            border-color: #2ed573;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination .page-number {
            min-width: 35px;
            text-align: center;
        }

        .pagination .page-number.active {
            background-color: #2ed573;
            color: white;
            border-color: #2ed573;
        }

        .pagination .prev-page,
        .pagination .next-page {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

</body>
</html> 