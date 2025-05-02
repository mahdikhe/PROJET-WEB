<?php
require_once dirname(__DIR__, 2) . '/controller/Controller.php';
require_once dirname(__DIR__, 2) . '/model/model.php';

$postController = new PostController();
$totalPosts = $postController->getTotalPosts();
$recentPosts = $postController->getRecentPosts();
$posts = $postController->listPosts();
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
    <link rel="stylesheet" href="../style.css">
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
                    <a href="#" class="nav-link active">
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
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <h1 class="header-title">Social Media Analytics</h1>
            <div class="header-actions">
                <button class="btn btn-outline">
                    <i class="fas fa-download"></i> Export
                </button>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Report
                </button>
            </div>
        </header>

        <!-- Post Management Section -->
        <div class="card" style="margin-bottom: 20px;">
            <h2 class="card-title">Post Management</h2>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <div style="display: flex; gap: 10px;">
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i> <a href="add.php" style="color: white; text-decoration: none;">Add Post</a>
                    </button>
                </div>
                <div style="margin-left: auto;">
                    <button class="btn btn-outline">
                        <i class="fas fa-eye"></i> <a href="../frontoffice/cont.php" style="color: var(--primary); text-decoration: none;">See Posts</a>
                    </button>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <div class="metric-card">
                <h3 class="metric-title">Total Posts</h3>
                <div class="metric-value"><?= $totalPosts ?></div>
                <div class="metric-change">Total number of posts</div>
            </div>
            <div class="metric-card">
                <h3 class="metric-title">Recent Posts</h3>
                <div class="metric-value"><?= $recentPosts ?></div>
                <div class="metric-change">Posts in the last 7 days</div>
            </div>
            <div class="metric-card">
                <h3 class="metric-title">Active Authors</h3>
                <div class="metric-value"><?= count(array_unique(array_column($posts, 'author'))) ?></div>
                <div class="metric-change">Unique authors</div>
            </div>
            <div class="metric-card">
                <h3 class="metric-title">Average Posts</h3>
                <div class="metric-value"><?= $totalPosts > 0 ? round($totalPosts / count(array_unique(array_column($posts, 'author'))), 1) : 0 ?></div>
                <div class="metric-change">Posts per author</div>
            </div>
        </div>

        <!-- Recent Posts Section -->
        <div class="recent-posts-section">
            <h2 class="section-title">Recent Posts</h2>
            <div class="posts-grid">
                <?php foreach (array_slice($posts, 0, 4) as $post): ?>
                    <div class="post-card">
                        <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <p class="post-author">By <?= htmlspecialchars($post['author']) ?></p>
                        <p class="post-date"><?= date('F j, Y', strtotime($post['created_at'])) ?></p>
                        <div class="post-actions">
                            <a href="modifypost.php?id=<?= $post['post_id'] ?>" class="btn btn-outline" style="font-size: 12px;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete.php?id=<?= $post['post_id'] ?>" class="btn btn-outline" style="font-size: 12px; color: #ff4757; border-color: #ff4757;"
                               onclick="return confirm('Are you sure you want to delete this post?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <h2 class="chart-title">Post Activity</h2>
                <div class="chart-container">
                    <canvas id="postActivityChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h2 class="chart-title">Author Distribution</h2>
                <div class="chart-container">
                    <canvas id="authorDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Update Chart Initialization Script -->
    <script>
        // Initialize Post Activity Chart
        const postActivityCtx = document.getElementById('postActivityChart').getContext('2d');
        new Chart(postActivityCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Posts',
                    data: [12, 19, 15, 25, 22, 30, 28, 35, 32, 40, 38, 45],
                    borderColor: '#00b8a9',
                    backgroundColor: 'rgba(0, 184, 169, 0.1)',
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
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
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

        // Initialize Author Distribution Chart
        const authorDistributionCtx = document.getElementById('authorDistributionChart').getContext('2d');
        new Chart(authorDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Author 1', 'Author 2', 'Author 3', 'Author 4', 'Author 5'],
                datasets: [{
                    data: [25, 35, 20, 12, 8],
                    backgroundColor: [
                        '#00b8a9',
                        '#6c63ff',
                        '#d295ff',
                        '#ff6b6b',
                        '#ffd166'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html> 