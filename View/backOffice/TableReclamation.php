<?php
require "../../Controller/ReclamationC.php";
$reclamation = new ReclamationC();

// Get all reclamations from database for the table
$tab = $reclamation->listeReclamation();

// Get statistics from database
$stats = $reclamation->getReclamationStats();

// Convert PHP data to JSON for JavaScript
$jsonData = json_encode($tab, JSON_UNESCAPED_UNICODE);

// Get monthly data from database
$monthlyData = $reclamation->getMonthlyStats();

// Calculate percentages from database stats
$total = $stats['total'];
$enAttenteCount = $stats['en_attente'];
$resoluCount = $stats['resolu'];
$annuleCount = $stats['annule'];

$enAttentePercent = $total > 0 ? round(($enAttenteCount / $total) * 100) : 0;
$resoluPercent = $total > 0 ? round(($resoluCount / $total) * 100) : 0;
$annulePercent = $total > 0 ? round(($annuleCount / $total) * 100) : 0;

// Prepare months array
$months = array('Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc');

// Initialize monthly data array
$monthlyData = array();
for ($i = 1; $i <= 12; $i++) {
    $monthlyData[date('M', mktime(0, 0, 0, $i, 1))] = array(
        'En attente' => 0,
        'Résolu' => 0,
        'Annulé' => 0
    );
}

// Count monthly data only
foreach ($tab as $reclamation) {
    $status = $reclamation['status_reclamation'];
    $month = date('M', strtotime($reclamation['date_reclamation']));
    
    // Count by month
    if (isset($monthlyData[$month][$status])) {
        $monthlyData[$month][$status]++;
    }
}

// Calculate percentages
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
   

          <!--init data table!-->
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
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
                <button class="btn btn-outline export-btn">
                    <i class="fas fa-download"></i> Export
                </button>
                
            </div>
        </header>

      

        

        <!-- Email List Section -->
        <div class="email-list-section">
            <div class="email-list-header">
                <h2 class="email-list-title">Table Reclamation</h2>
                <div class="table-controls">
                    <a href="TableReponse.php" class="btn btn-primary" style="margin-right: 10px;">
                        <i class="fas fa-reply"></i> Voir les Réponses
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
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Résolu',
                    data: <?php echo json_encode(array_map(function($m) { return $m['Résolu']; }, $monthlyData)); ?>,
                    borderColor: '#6c63ff',
                    backgroundColor: 'rgba(108, 99, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Annulé',
                    data: <?php echo json_encode(array_map(function($m) { return $m['Annulé']; }, $monthlyData)); ?>,
                    borderColor: '#ff6b6b',
                    backgroundColor: 'rgba(255, 107, 107, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#333',
                        bodyColor: '#666',
                        borderColor: '#ddd',
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
                            stepSize: 1
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

        // Initialize Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['En Attente', 'Résolu', 'Annulé'],
                datasets: [{
                    data: [
                        <?php echo $enAttenteCount; ?>,
                        <?php echo $resoluCount; ?>,
                        <?php echo $annuleCount; ?>
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
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
                            <td>
                                <form action='forumAjouterReponse.php' method='post' style='padding: 4px 8px; font-size: 12px;'>
                                    <input type='hidden' name='id_reclamation' value='${item.id_reclamation}'>
                                    <button type='submit' class='btn btn-outline' style='padding: 4px 8px; font-size: 12px;'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                </form>
                                <form action='conAnnulerReclamation.php' method='post' style='padding: 4px 8px; font-size: 12px;'>
                                    <input type='hidden' name='id_reclamation' value='${item.id_reclamation}'>
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
                    item.status_reclamation.toLowerCase().includes(searchText)
                );
                currentPage = 1;
                renderTable();
            });

            // Sort functionality
            $('#sortSelect').on('change', function() {
                const sortBy = $(this).val();
                if (sortBy) {
                    filteredData.sort((a, b) => {
                        let valueA = a[sortBy + '_reclamation'] || a[sortBy];
                        let valueB = b[sortBy + '_reclamation'] || b[sortBy];
                        
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

            // Function to export table to PDF
            function exportToPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                // Add title
                doc.setFontSize(16);
                doc.text('Liste des Réclamations', 14, 15);
                
                // Add date
                doc.setFontSize(10);
                doc.text('Date d\'export: ' + new Date().toLocaleDateString(), 14, 22);
                
                // Prepare table data
                const tableData = filteredData.map(item => [
                    item.titre_reclamation,
                    item.raison_reclamation,
                    item.description_reclamation,
                    item.date_reclamation,
                    item.status_reclamation
                ]);
                
                // Add table
                doc.autoTable({
                    head: [['Titre', 'Raison', 'Description', 'Date', 'Status']],
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
                        0: { cellWidth: 40 },
                        1: { cellWidth: 40 },
                        2: { cellWidth: 60 },
                        3: { cellWidth: 30 },
                        4: { cellWidth: 20 }
                    }
                });
                
                // Save the PDF
                doc.save('reclamations_' + new Date().toISOString().slice(0,10) + '.pdf');
            }

            // Add click handler for export button only
            $('.export-btn').click(function(e) {
                e.preventDefault();
                exportToPDF();
            });

            // Initial render
            renderTable();
        });
    </script>

</body>
</html> 