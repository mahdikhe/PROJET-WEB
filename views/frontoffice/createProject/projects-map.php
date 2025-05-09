<?php
require_once(__DIR__ . '/../../../config/Database.php');
session_start();

// Get all projects with location data
$db = Database::getInstance()->getConnection();
$query = "SELECT id, projectName, projectLocation, latitude, longitude, projectCategory FROM projects 
          WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
$projects = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Define color mapping for categories
$categoryColors = [
    'Environment' => '#2ecc71',
    'Urban Developement' => '#3498db',
    'Transportation' => '#e74c3c',
    'Smart Technology' => '#9b59b6',
    'Community Services' => '#f39c12',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Map - CityPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/css/style1.css" />
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <!-- MarkerCluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }
        #map {
            height: 600px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            text-decoration: none;
            color: #4361ee;
            font-weight: 600;
        }
        .legend {
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .legend h3 {
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="projects.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Projects List
        </a>
        
        <div class="page-header">
            <h1>Projects Map</h1>
            <p>View all project locations on the map</p>
        </div>
        
        <div id="map"></div>
        
        <div class="legend">
            <h3>Project Categories</h3>
            <div class="legend-items">
                <?php foreach ($categoryColors as $category => $color): ?>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: <?= $color ?>;"></div>
                        <span><?= $category ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <!-- MarkerCluster JS -->
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script>
        // Define category colors
        const categoryColors = {
        'Environment': '#2ecc71',
        'Urban Developement': '#3498db',
        'Transportation': '#e74c3c',
        'Smart Technology': '#9b59b6',
        'Community Services': '#f39c12'
    };

        // Initialize the map
        const map = L.map('map').setView([48.8566, 2.3522], 12); // Default to Paris coordinates

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Initialize marker cluster group
        const markers = L.markerClusterGroup({
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            maxClusterRadius: 40
        });

        // Add project markers to the cluster group
        <?php foreach ($projects as $project): ?>
            <?php if (!empty($project['latitude']) && !empty($project['longitude'])): ?>
                const marker<?= $project['id'] ?> = L.circleMarker(
                    [<?= $project['latitude'] ?>, <?= $project['longitude'] ?>],
                    {
                        radius: 8,
                        fillColor: categoryColors['<?= $project['projectCategory'] ?>'] || '#555555',
                        color: '#fff',
                        weight: 1,
                        opacity: 1,
                        fillOpacity: 0.8
                    }
                ).bindPopup(`
                    <b><?= addslashes($project['projectName']) ?></b><br>
                    <span style="color: ${categoryColors['<?= $project['projectCategory'] ?>'] || '#555555'}; font-weight:600;">
                        <?= $project['projectCategory'] ?>
                    </span><br>
                    <?= addslashes($project['projectLocation']) ?><br>
                    <a href="project-details.php?id=<?= $project['id'] ?>">View Details</a>
                `);
                
                markers.addLayer(marker<?= $project['id'] ?>);
            <?php endif; ?>
        <?php endforeach; ?>

        // Add markers to the map
        map.addLayer(markers);

        // Fit map to markers if there are any
        <?php if (!empty($projects)): ?>
            const markerLocations = [];
            <?php foreach ($projects as $project): ?>
                <?php if (!empty($project['latitude']) && !empty($project['longitude'])): ?>
                    markerLocations.push([<?= $project['latitude'] ?>, <?= $project['longitude'] ?>]);
                <?php endif; ?>
            <?php endforeach; ?>
            
            if (markerLocations.length > 0) {
                const bounds = L.latLngBounds(markerLocations);
                map.fitBounds(bounds, { padding: [50, 50] });
                
                // If only one marker, set a reasonable zoom level
                if (markerLocations.length === 1) {
                    map.setZoom(14);
                }
            }
        <?php endif; ?>
    </script>
</body>
</html>