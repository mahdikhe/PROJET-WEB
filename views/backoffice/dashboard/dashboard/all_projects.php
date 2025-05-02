<?php include 'C:/Users/Abderrahmen/Desktop/2A40/cursor/website/projet/create project/db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityPulse - All Projects</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #00b8a9;
            --accent-color: #ff6b6b;
            --light-accent: #d295ff;
            --background-color: #f8f9fc;
            --card-bg: #ffffff;
            --text-dark: #2c3e50;
            --text-medium: #5a6b7b;
            --text-light: #a3aebf;
            --border-color: rgba(0, 0, 0, 0.1);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
            --sidebar-bg-start: #2c3e50;
            --sidebar-bg-end: #1a252f;
        }

        /* Dark mode variables */
        body.dark-mode {
            --background-color: #121212;
            --card-bg: #1e1e1e;
            --text-dark: #ffffff;
            --text-medium: #b0b0b0;
            --text-light: #777777;
            --border-color: rgba(255, 255, 255, 0.1);
            --sidebar-bg-start: #000000;
            --sidebar-bg-end: #121212;
        }

        html {
            scroll-behavior: smooth;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-dark);
            line-height: 1.6;
            display: flex;
            overflow-x: hidden;
            margin-left: 10px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg-start), var(--sidebar-bg-end));
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding: 2rem 0;
            transition: var(--transition);
            z-index: 100;
            box-shadow: var(--shadow-lg);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 1.8rem;
            color: var(--secondary-color);
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--secondary-color);
            color: white;
        }

        .nav-link i {
            font-size: 1.2rem;
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .nav-divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.1);
            margin: 1.5rem 1.5rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .page-subtitle {
            color: var(--text-medium);
            font-size: 1.1rem;
            font-weight: 400;
            margin-top: 0.5rem;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            border: none;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #5a52e3;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-medium);
        }

        .btn-outline:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--text-dark);
        }

        /* Filter Section */
        .filter-section {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-medium);
        }

        .filter-input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--card-bg);
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }

        /* Projects Table */
        .projects-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .projects-table {
            width: 100%;
            border-collapse: collapse;
        }

        .projects-table th {
            text-align: left;
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-medium);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            background-color: var(--card-bg);
            z-index: 10;
        }

        .projects-table td {
            padding: 1.2rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .projects-table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .project-name {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .project-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .status-pending {
            background-color: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .status-completed {
            background-color: rgba(108, 99, 255, 0.1);
            color: #6c63ff;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-medium);
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .page-info {
            color: var(--text-medium);
            font-size: 0.9rem;
        }

        .page-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .page-btn {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background-color: transparent;
            color: var(--text-medium);
            cursor: pointer;
            transition: var(--transition);
        }

        .page-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .page-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Settings Panel Styles */
        .settings-panel {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background-color: var(--card-bg);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            transition: right 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            padding: 2rem;
            overflow-y: auto;
        }

        .settings-panel.active {
            right: 0;
        }

        .settings-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .settings-overlay.active {
            display: block;
        }

        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .settings-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .settings-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-medium);
            cursor: pointer;
            transition: var(--transition);
        }

        .settings-close:hover {
            color: var(--text-dark);
        }

        .settings-section {
            margin-bottom: 2rem;
        }

        .settings-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .toggle-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: var(--transition);
        }

        body.dark-mode .toggle-option {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .toggle-text {
            font-weight: 500;
            color: var(--text-dark);
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: var(--primary-color);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        .range-slider {
            width: 100%;
            margin: 1rem 0;
        }

        .range-value {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--text-medium);
        }

        .animation-speed {
            display: flex;
            gap: 0.5rem;
        }

        .speed-option {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.85rem;
            color: var(--text-medium);
        }

        .speed-option.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .language-selector {
            position: relative;
            margin-top: 1rem;
        }

        .language-selector select {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--card-bg);
            color: var(--text-dark);
            appearance: none;
            font-size: 1rem;
            cursor: pointer;
        }

        .language-selector::after {
            content: '\f078';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--text-medium);
        }

        .font-selector {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .font-option {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .font-option.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .font-option-sans {
            font-family: 'Inter', sans-serif;
        }

        .font-option-serif {
            font-family: Georgia, serif;
        }

        .font-option-mono {
            font-family: monospace;
        }

        .reset-button {
            width: 100%;
            padding: 0.75rem;
            margin-top: 1rem;
            background-color: rgba(0, 0, 0, 0.05);
            border: none;
            border-radius: 8px;
            color: var(--text-dark);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .reset-button:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        body.dark-mode .reset-button {
            background-color: rgba(255, 255, 255, 0.05);
        }

        body.dark-mode .reset-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Theme colors */
        .theme-purple {
            background: linear-gradient(45deg, #6c63ff, #d295ff);
        }

        .theme-teal {
            background: linear-gradient(45deg, #00b8a9, #8ed1cc);
        }

        .theme-blue {
            background: linear-gradient(45deg, #1877f2, #56a8f7);
        }

        .theme-green {
            background: linear-gradient(45deg, #4caf50, #8bc34a);
        }

        .theme-orange {
            background: linear-gradient(45deg, #ff9800, #ffeb3b);
        }

        .theme-red {
            background: linear-gradient(45deg, #ff5252, #ff9e80);
        }

        /* Additional styles for font sizes */
        body.font-size-small {
            font-size: 0.875rem;
        }

        body.font-size-large {
            font-size: 1.125rem;
        }

        body.font-sans {
            font-family: 'Inter', sans-serif;
        }

        body.font-serif {
            font-family: Georgia, serif;
        }

        body.font-mono {
            font-family: monospace;
        }

        /* Animation settings */
        body.reduced-motion *:not(.settings-panel):not(.settings-panel *):not(.settings-overlay):not(.settings-toggle) {
            animation-duration: 0.001ms !important;
            transition-duration: 0.001ms !important;
        }

        /* Compact UI settings */
        body.compact-ui .filter-section,
        body.compact-ui .projects-container {
            gap: 0.75rem;
        }

        body.compact-ui .filter-row,
        body.compact-ui .filter-group,
        body.compact-ui .filter-actions {
            margin-bottom: 0.5rem;
        }

        body.compact-ui .filter-section,
        body.compact-ui .page-header {
            margin-bottom: 1rem;
        }

        /* Alert messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border-left: 4px solid #4caf50;
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border-left: 4px solid #f44336;
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s ease;
            margin-left: 1rem;
            color: inherit;
        }

        .alert-close:hover {
            opacity: 1;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="logo">
                <i class="fas fa-city"></i>
                <span>CityPulse</span>
            </a>
        </div>
        <nav>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="creative_dashboard.php" class="nav-link">
                        <i class="fas fa-columns"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="all_projects.php" class="nav-link active">
                        <i class="fas fa-project-diagram"></i>
                        <span>All Projects</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <div class="nav-divider"></div>
                <li class="nav-item">
                    <a href="../projet/create project/createProject.html" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>New Project</span>
                    </a>
                </li>
    <!--<li class="nav-item">
                    <a href="#" class="nav-link settings-toggle">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>-->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">All Projects</h1>
                <p class="page-subtitle">Comprehensive list of all urban development projects</p>
            </div>
            <div class="header-actions">
                <a href="../projet/create project/createProject.html" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Project
                </a>
            </div>
        </div>

        <?php if (isset($_GET['message']) && $_GET['message'] === 'deleted'): ?>
        <div class="alert alert-success">
            Project was successfully deleted.
            <button class="alert-close">&times;</button>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <?php
            switch ($_GET['error']) {
                case 'not_found':
                    echo 'Project not found.';
                    break;
                case 'invalid_id':
                    echo 'Invalid project ID.';
                    break;
                case 'database':
                    echo 'Database error: ' . (isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Unknown error');
                    break;
                default:
                    echo 'An error occurred.';
            }
            ?>
            <button class="alert-close">&times;</button>
        </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <form action="all_projects.php" method="GET">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search" class="filter-label">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search projects..." class="filter-input" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>
                    <div class="filter-group">
                        <label for="category" class="filter-label">Category</label>
                        <select id="category" name="category" class="filter-input">
                            <option value="">All Categories</option>
                            <?php
                            $categoryQuery = $conn->query("SELECT DISTINCT projectCategory FROM projects ORDER BY projectCategory");
                            while($category = $categoryQuery->fetch(PDO::FETCH_ASSOC)) {
                                $selected = (isset($_GET['category']) && $_GET['category'] == $category['projectCategory']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($category['projectCategory']) . '" ' . $selected . '>' . htmlspecialchars($category['projectCategory']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="status" class="filter-label">Status</label>
                        <select id="status" name="status" class="filter-input">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="active" <?= (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="completed" <?= (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                </div>
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="location" class="filter-label">Location</label>
                        <select id="location" name="location" class="filter-input">
                            <option value="">All Locations</option>
                            <?php
                            $locationQuery = $conn->query("SELECT DISTINCT projectLocation FROM projects ORDER BY projectLocation");
                            while($location = $locationQuery->fetch(PDO::FETCH_ASSOC)) {
                                $selected = (isset($_GET['location']) && $_GET['location'] == $location['projectLocation']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($location['projectLocation']) . '" ' . $selected . '>' . htmlspecialchars($location['projectLocation']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date_from" class="filter-label">Start Date</label>
                        <input type="date" id="date_from" name="date_from" class="filter-input" value="<?= isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : '' ?>">
                    </div>
                    <div class="filter-group">
                        <label for="date_to" class="filter-label">End Date</label>
                        <input type="date" id="date_to" name="date_to" class="filter-input" value="<?= isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : '' ?>">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="reset" class="btn btn-outline">Reset</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Projects Table -->
        <div class="projects-container">
            <table class="projects-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Budget</th>
                        <th>Location</th>
                        <th>Team Size</th>
                        <th>Status</th>
                        <th>Timeline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build the SQL query with filters
                    $sql = "SELECT * FROM projects WHERE 1=1";
                    $params = [];

                    if(isset($_GET['search']) && $_GET['search'] != '') {
                        $sql .= " AND (projectName LIKE ? OR projectDescription LIKE ?)";
                        $searchTerm = '%' . $_GET['search'] . '%';
                        $params[] = $searchTerm;
                        $params[] = $searchTerm;
                    }

                    if(isset($_GET['category']) && $_GET['category'] != '') {
                        $sql .= " AND projectCategory = ?";
                        $params[] = $_GET['category'];
                    }

                    if(isset($_GET['location']) && $_GET['location'] != '') {
                        $sql .= " AND projectLocation = ?";
                        $params[] = $_GET['location'];
                    }

                    if(isset($_GET['date_from']) && $_GET['date_from'] != '') {
                        $sql .= " AND startDate >= ?";
                        $params[] = $_GET['date_from'];
                    }

                    if(isset($_GET['date_to']) && $_GET['date_to'] != '') {
                        $sql .= " AND endDate <= ?";
                        $params[] = $_GET['date_to'];
                    }

                    if(isset($_GET['status']) && $_GET['status'] != '') {
                        $now = date('Y-m-d');
                        if($_GET['status'] == 'pending') {
                            $sql .= " AND startDate > ?";
                            $params[] = $now;
                        } else if($_GET['status'] == 'active') {
                            $sql .= " AND startDate <= ? AND endDate >= ?";
                            $params[] = $now;
                            $params[] = $now;
                        } else if($_GET['status'] == 'completed') {
                            $sql .= " AND endDate < ?";
                            $params[] = $now;
                        }
                    }

                    // Pagination
                    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                    $perPage = 10;
                    $offset = ($page - 1) * $perPage;

                    // Get total count for pagination
                    $countQuery = $conn->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $sql));
                    $countQuery->execute($params);
                    $totalProjects = $countQuery->fetchColumn();
                    $totalPages = ceil($totalProjects / $perPage);

                    // Add sorting and pagination
                    $sql .= " ORDER BY startDate DESC LIMIT $perPage OFFSET $offset";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $categoryColors = [
                        'Residential' => '#6c63ff',
                        'Commercial' => '#00b8a9',
                        'Infrastructure' => '#ff6b6b',
                        'Public' => '#d295ff',
                        'Green Space' => '#4caf50'
                    ];
                    
                    foreach ($projects as $project) {
                        $now = new DateTime();
                        $start = new DateTime($project['startDate']);
                        $end = new DateTime($project['endDate']);
                        
                        if ($now < $start) {
                            $status = 'pending';
                            $statusText = 'Pending';
                        } elseif ($now > $end) {
                            $status = 'completed';
                            $statusText = 'Completed';
                        } else {
                            $status = 'active';
                            $statusText = 'Active';
                        }
                        
                        $category = $project['projectCategory'];
                        $backgroundColor = $categoryColors[$category] ?? '#6c63ff';
                        $icon = 'building';
                        
                        if (stripos($category, 'residential') !== false) {
                            $icon = 'home';
                        } elseif (stripos($category, 'commercial') !== false) {
                            $icon = 'store';
                        } elseif (stripos($category, 'infrastructure') !== false) {
                            $icon = 'road';
                        } elseif (stripos($category, 'public') !== false) {
                            $icon = 'landmark';
                        } elseif (stripos($category, 'green') !== false) {
                            $icon = 'tree';
                        }
                        
                        echo '<tr>';
                        echo '<td>
                                <div class="project-name">
                                    <div class="project-icon" style="background-color: ' . $backgroundColor . ';">
                                        <i class="fas fa-' . $icon . '"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;">' . htmlspecialchars($project['projectName']) . '</div>
                                        <div style="font-size: 0.8rem; color: var(--text-medium);">' . htmlspecialchars($project['projectCategory']) . '</div>
                                    </div>
                                </div>
                            </td>';
                        echo '<td>' . number_format($project['projectBudget'] ?? 0) . ' €</td>';
                        echo '<td>' . htmlspecialchars($project['projectLocation']) . '</td>';
                        echo '<td>' . $project['teamSize'] . ' members</td>';
                        echo '<td><span class="status-badge status-' . $status . '">' . $statusText . '</span></td>';
                        echo '<td>' . date('M d, Y', strtotime($project['startDate'])) . ' - ' . date('M d, Y', strtotime($project['endDate'])) . '</td>';
                        echo '<td>
                                <div class="action-buttons">
                                    <a href="view_project.php?id=' . $project['id'] . '" class="action-btn" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="../projet/create project/edit-project.php?id=' . $project['id'] . '" class="action-btn" title="Edit Project">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="action-btn delete-project" data-id="' . $project['id'] . '" data-name="' . htmlspecialchars($project['projectName']) . '" title="Delete Project">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>';
                        echo '</tr>';
                    }

                    if (count($projects) == 0) {
                        echo '<tr><td colspan="7" style="text-align: center; padding: 2rem;">No projects found. Try adjusting your filters.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <div class="page-info">
                    Showing <?= min(($page-1)*$perPage+1, $totalProjects) ?> to <?= min($page*$perPage, $totalProjects) ?> of <?= $totalProjects ?> projects
                </div>
                <div class="page-buttons">
                    <?php
                    // Previous page button
                    $prevDisabled = ($page <= 1) ? 'disabled' : '';
                    $prevLink = ($page > 1) ? '?' . http_build_query(array_merge($_GET, ['page' => $page - 1])) : '#';
                    echo '<a href="' . $prevLink . '" class="page-btn ' . $prevDisabled . '"><i class="fas fa-chevron-left"></i></a>';

                    // Page numbers
                    $startPage = max(1, min($page - 2, $totalPages - 4));
                    $endPage = min($totalPages, max($page + 2, 5));
                    
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $isActive = ($i == $page) ? 'active' : '';
                        $pageLink = '?' . http_build_query(array_merge($_GET, ['page' => $i]));
                        echo '<a href="' . $pageLink . '" class="page-btn ' . $isActive . '">' . $i . '</a>';
                    }

                    // Next page button
                    $nextDisabled = ($page >= $totalPages) ? 'disabled' : '';
                    $nextLink = ($page < $totalPages) ? '?' . http_build_query(array_merge($_GET, ['page' => $page + 1])) : '#';
                    echo '<a href="' . $nextLink . '" class="page-btn ' . $nextDisabled . '"><i class="fas fa-chevron-right"></i></a>';
                    ?>
                </div>
            </div>
        </div>
    </main>

    <div class="settings-panel">
        <div class="settings-header">
            <h2 class="settings-title">Dashboard Settings</h2>
            <button class="settings-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="settings-section">
            <h3 class="settings-section-title">Theme Colors</h3>
            <div class="theme-options">
                <div class="theme-option theme-purple active" data-theme="purple"></div>
                <div class="theme-option theme-teal" data-theme="teal"></div>
                <div class="theme-option theme-blue" data-theme="blue"></div>
                <div class="theme-option theme-green" data-theme="green"></div>
                <div class="theme-option theme-orange" data-theme="orange"></div>
                <div class="theme-option theme-red" data-theme="red"></div>
            </div>
        </div>
        
        <div class="settings-section">
            <h3 class="settings-section-title">Display Options</h3>
            
            <div class="toggle-option">
                <span class="toggle-text">Dark Mode</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="dark-mode-toggle">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="toggle-option">
                <span class="toggle-text">Compact UI</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="compact-ui-toggle">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="toggle-option">
                <span class="toggle-text">Reduced Animations</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="reduced-motion-toggle">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
        
        <div class="settings-section">
            <h3 class="settings-section-title">Text Size</h3>
            <input type="range" min="1" max="3" value="2" class="range-slider" id="font-size-slider">
            <div class="range-value">
                <span>Small</span>
                <span>Normal</span>
                <span>Large</span>
            </div>
        </div>
        
        <div class="settings-section">
            <h3 class="settings-section-title">Font Family</h3>
            <div class="font-selector">
                <div class="font-option font-option-sans active" data-font="sans">Sans</div>
                <div class="font-option font-option-serif" data-font="serif">Serif</div>
                <div class="font-option font-option-mono" data-font="mono">Mono</div>
            </div>
        </div>
        
        <div class="settings-section">
            <h3 class="settings-section-title">Animation Speed</h3>
            <div class="animation-speed">
                <div class="speed-option" data-speed="slow">Slow</div>
                <div class="speed-option active" data-speed="normal">Normal</div>
                <div class="speed-option" data-speed="fast">Fast</div>
            </div>
        </div>
        
        <div class="settings-section">
            <h3 class="settings-section-title">Language</h3>
            <div class="language-selector">
                <select id="language-select">
                    <option value="en">English</option>
                    <option value="fr">Français</option>
                    <option value="es">Español</option>
                    <option value="de">Deutsch</option>
                    <option value="ar">العربية</option>
                </select>
            </div>
        </div>
        
        <button class="reset-button">Reset All Settings</button>
    </div>

    <script>
        // Form reset handler to redirect to the page without query parameters
        document.querySelector('form button[type="reset"]').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'all_projects.php';
        });

        // Delete project functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to delete buttons
            document.querySelectorAll('.delete-project').forEach(button => {
                button.addEventListener('click', function() {
                    const projectId = this.getAttribute('data-id');
                    const projectName = this.getAttribute('data-name');
                    
                    if (confirm(`Are you sure you want to delete the project "${projectName}"? This action cannot be undone.`)) {
                        // Create and submit a form to delete the project
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'delete_project.php';
                        
                        const idField = document.createElement('input');
                        idField.type = 'hidden';
                        idField.name = 'project_id';
                        idField.value = projectId;
                        
                        form.appendChild(idField);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // Add event listeners to alert close buttons
            document.querySelectorAll('.alert-close').forEach(button => {
                button.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                });
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                });
            }, 5000);
        });

        // Settings panel functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize settings panel
            initSettingsPanel();
            
            // Load saved settings
            loadSettings();
        });

        function initSettingsPanel() {
            // Get all settings panel elements
            const settingsToggle = document.querySelector('.settings-toggle');
            const settingsPanel = document.querySelector('.settings-panel');
            const settingsOverlay = document.querySelector('.settings-overlay');
            const settingsClose = document.querySelector('.settings-close');
            
            // Debug check
            if (!settingsToggle || !settingsPanel || !settingsOverlay || !settingsClose) {
                console.error('Missing settings panel elements');
                return;
            }

            // Toggle settings panel
            settingsToggle.addEventListener('click', function(e) {
                e.preventDefault();
                settingsPanel.classList.add('active');
                settingsOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });

            // Close settings panel
            function closeSettings() {
                settingsPanel.classList.remove('active');
                settingsOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            settingsClose.addEventListener('click', closeSettings);
            settingsOverlay.addEventListener('click', closeSettings);

            // Theme color options
            const themeOptions = document.querySelectorAll('.theme-option');
            themeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    themeOptions.forEach(o => o.classList.remove('active'));
                    this.classList.add('active');
                    applyTheme(this.dataset.theme);
                    saveSettings('theme', this.dataset.theme);
                });
            });

            // Toggle switches
            document.getElementById('dark-mode-toggle')?.addEventListener('change', function() {
                document.body.classList.toggle('dark-mode', this.checked);
                saveSettings('darkMode', this.checked);
            });

            document.getElementById('compact-ui-toggle')?.addEventListener('change', function() {
                document.body.classList.toggle('compact-ui', this.checked);
                saveSettings('compactUI', this.checked);
            });

            document.getElementById('reduced-motion-toggle')?.addEventListener('change', function() {
                document.body.classList.toggle('reduced-motion', this.checked);
                saveSettings('reducedMotion', this.checked);
            });

            // Font size slider
            const fontSizeSlider = document.getElementById('font-size-slider');
            if (fontSizeSlider) {
                fontSizeSlider.addEventListener('input', function() {
                    document.body.classList.remove('font-size-small', 'font-size-large');
                    if (this.value == 1) document.body.classList.add('font-size-small');
                    if (this.value == 3) document.body.classList.add('font-size-large');
                    saveSettings('fontSize', this.value);
                });
            }

            // Font family options
            const fontOptions = document.querySelectorAll('.font-option');
            fontOptions.forEach(option => {
                option.addEventListener('click', function() {
                    fontOptions.forEach(o => o.classList.remove('active'));
                    this.classList.add('active');
                    document.body.classList.remove('font-sans', 'font-serif', 'font-mono');
                    document.body.classList.add(`font-${this.dataset.font}`);
                    saveSettings('fontFamily', this.dataset.font);
                });
            });

            // Animation speed options
            const speedOptions = document.querySelectorAll('.speed-option');
            speedOptions.forEach(option => {
                option.addEventListener('click', function() {
                    speedOptions.forEach(o => o.classList.remove('active'));
                    this.classList.add('active');
                    const speed = this.dataset.speed;
                    const transitionTime = speed === 'slow' ? '0.6s' : speed === 'fast' ? '0.15s' : '0.3s';
                    document.documentElement.style.setProperty('--transition', `all ${transitionTime} ease`);
                    saveSettings('animationSpeed', speed);
                });
            });

            // Language selector
            document.getElementById('language-select')?.addEventListener('change', function() {
                const selectedLanguage = this.value;
                changeLanguage(selectedLanguage);
                saveSettings('language', selectedLanguage);
            });

            // Reset button
            document.querySelector('.reset-button')?.addEventListener('click', resetSettings);
        }

        // Helper functions
        function applyTheme(theme) {
            switch(theme) {
                case 'purple':
                    document.documentElement.style.setProperty('--primary-color', '#6c63ff');
                    document.documentElement.style.setProperty('--secondary-color', '#00b8a9');
                    document.documentElement.style.setProperty('--accent-color', '#ff6b6b');
                    document.documentElement.style.setProperty('--light-accent', '#d295ff');
                    break;
                case 'teal':
                    document.documentElement.style.setProperty('--primary-color', '#00b8a9');
                    document.documentElement.style.setProperty('--secondary-color', '#6c63ff');
                    document.documentElement.style.setProperty('--accent-color', '#ff9800');
                    document.documentElement.style.setProperty('--light-accent', '#8ed1cc');
                    break;
                case 'blue':
                    document.documentElement.style.setProperty('--primary-color', '#1877f2');
                    document.documentElement.style.setProperty('--secondary-color', '#56a8f7');
                    document.documentElement.style.setProperty('--accent-color', '#ff6b6b');
                    document.documentElement.style.setProperty('--light-accent', '#98c1f5');
                    break;
                case 'green':
                    document.documentElement.style.setProperty('--primary-color', '#4caf50');
                    document.documentElement.style.setProperty('--secondary-color', '#8bc34a');
                    document.documentElement.style.setProperty('--accent-color', '#ff9800');
                    document.documentElement.style.setProperty('--light-accent', '#a5d6a7');
                    break;
                case 'orange':
                    document.documentElement.style.setProperty('--primary-color', '#ff9800');
                    document.documentElement.style.setProperty('--secondary-color', '#ffeb3b');
                    document.documentElement.style.setProperty('--accent-color', '#ff5252');
                    document.documentElement.style.setProperty('--light-accent', '#ffcc80');
                    break;
                case 'red':
                    document.documentElement.style.setProperty('--primary-color', '#ff5252');
                    document.documentElement.style.setProperty('--secondary-color', '#ff9e80');
                    document.documentElement.style.setProperty('--accent-color', '#ffeb3b');
                    document.documentElement.style.setProperty('--light-accent', '#ff8a80');
                    break;
            }
        }

        function saveSettings(key, value) {
            let settings = JSON.parse(localStorage.getItem('dashboardSettings') || '{}');
            settings[key] = value;
            localStorage.setItem('dashboardSettings', JSON.stringify(settings));
        }

        function loadSettings() {
            const settings = JSON.parse(localStorage.getItem('dashboardSettings') || '{}');
            
            // Apply theme
            if (settings.theme) {
                const themeOption = document.querySelector(`[data-theme="${settings.theme}"]`);
                if (themeOption) {
                    document.querySelectorAll('.theme-option').forEach(o => o.classList.remove('active'));
                    themeOption.classList.add('active');
                    applyTheme(settings.theme);
                }
            }
            
            // Apply dark mode
            if (settings.darkMode) {
                const darkModeToggle = document.getElementById('dark-mode-toggle');
                if (darkModeToggle) {
                    darkModeToggle.checked = true;
                    document.body.classList.add('dark-mode');
                }
            }
            
            // Apply compact UI
            if (settings.compactUI) {
                const compactUIToggle = document.getElementById('compact-ui-toggle');
                if (compactUIToggle) {
                    compactUIToggle.checked = true;
                    document.body.classList.add('compact-ui');
                }
            }
            
            // Apply reduced motion
            if (settings.reducedMotion) {
                const reducedMotionToggle = document.getElementById('reduced-motion-toggle');
                if (reducedMotionToggle) {
                    reducedMotionToggle.checked = true;
                    document.body.classList.add('reduced-motion');
                }
            }
            
            // Apply font size
            if (settings.fontSize) {
                const fontSizeSlider = document.getElementById('font-size-slider');
                if (fontSizeSlider) {
                    fontSizeSlider.value = settings.fontSize;
                    if (settings.fontSize == 1) {
                        document.body.classList.add('font-size-small');
                    } else if (settings.fontSize == 3) {
                        document.body.classList.add('font-size-large');
                    }
                }
            }
            
            // Apply font family
            if (settings.fontFamily) {
                const fontOption = document.querySelector(`[data-font="${settings.fontFamily}"]`);
                if (fontOption) {
                    document.querySelectorAll('.font-option').forEach(o => o.classList.remove('active'));
                    fontOption.classList.add('active');
                    document.body.classList.add('font-' + settings.fontFamily);
                }
            } else {
                document.body.classList.add('font-sans');
            }
            
            // Apply animation speed
            if (settings.animationSpeed) {
                const speedOption = document.querySelector(`[data-speed="${settings.animationSpeed}"]`);
                if (speedOption) {
                    document.querySelectorAll('.speed-option').forEach(o => o.classList.remove('active'));
                    speedOption.classList.add('active');
                    document.documentElement.style.setProperty('--transition', 
                        settings.animationSpeed === 'slow' ? 'all 0.6s ease' : 
                        settings.animationSpeed === 'fast' ? 'all 0.15s ease' : 
                        'all 0.3s ease'
                    );
                }
            }
            
            // Apply language
            if (settings.language) {
                const languageSelect = document.getElementById('language-select');
                if (languageSelect) {
                    languageSelect.value = settings.language;
                    // Apply translations for saved language
                    changeLanguage(settings.language);
                }
            }
        }
        
        function resetSettings() {
            // Clear saved settings
            localStorage.removeItem('dashboardSettings');
            
            // Reset UI to defaults
            document.body.classList.remove('dark-mode', 'compact-ui', 'reduced-motion');
            document.body.classList.remove('font-size-small', 'font-size-large');
            document.body.classList.remove('font-sans', 'font-serif', 'font-mono');
            document.body.classList.add('font-sans');
            document.documentElement.style.setProperty('--transition', 'all 0.3s ease');
            document.documentElement.style.setProperty('--primary-color', '#6c63ff');
            document.documentElement.style.setProperty('--secondary-color', '#00b8a9');
            document.documentElement.style.setProperty('--accent-color', '#ff6b6b');
            document.documentElement.style.setProperty('--light-accent', '#d295ff');
            
            // Reset form controls
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            const compactUIToggle = document.getElementById('compact-ui-toggle');
            const reducedMotionToggle = document.getElementById('reduced-motion-toggle');
            const fontSizeSlider = document.getElementById('font-size-slider');
            const languageSelect = document.getElementById('language-select');
            
            if (darkModeToggle) darkModeToggle.checked = false;
            if (compactUIToggle) compactUIToggle.checked = false;
            if (reducedMotionToggle) reducedMotionToggle.checked = false;
            if (fontSizeSlider) fontSizeSlider.value = 2;
            if (languageSelect) languageSelect.value = 'en';
            
            document.querySelectorAll('.theme-option').forEach(o => o.classList.remove('active'));
            const purpleTheme = document.querySelector('.theme-purple');
            if (purpleTheme) purpleTheme.classList.add('active');
            
            document.querySelectorAll('.font-option').forEach(o => o.classList.remove('active'));
            const sansFont = document.querySelector('.font-option-sans');
            if (sansFont) sansFont.classList.add('active');
            
            document.querySelectorAll('.speed-option').forEach(o => o.classList.remove('active'));
            const normalSpeed = document.querySelector('[data-speed="normal"]');
            if (normalSpeed) normalSpeed.classList.add('active');
        }

        // Translations object
        const translations = {
            en: {
                allProjects: 'All Projects',
                projectsSubtitle: 'Comprehensive list of all urban development projects',
                search: 'Search',
                searchPlaceholder: 'Search projects...',
                category: 'Category',
                allCategories: 'All Categories',
                status: 'Status',
                allStatuses: 'All Statuses',
                pending: 'Pending',
                active: 'Active',
                completed: 'Completed',
                location: 'Location',
                allLocations: 'All Locations',
                startDate: 'Start Date',
                endDate: 'End Date',
                reset: 'Reset',
                applyFilters: 'Apply Filters',
                project: 'Project',
                budget: 'Budget',
                teamSize: 'Team Size',
                timeline: 'Timeline',
                actions: 'Actions',
                members: 'members',
                noProjects: 'No projects found. Try adjusting your filters.',
                showing: 'Showing',
                to: 'to',
                of: 'of',
                projects: 'projects',
                settings: 'Settings',
                dashboardSettings: 'Dashboard Settings',
                themeColors: 'Theme Colors',
                displayOptions: 'Display Options',
                darkMode: 'Dark Mode',
                compactUI: 'Compact UI',
                reducedAnimations: 'Reduced Animations',
                textSize: 'Text Size',
                small: 'Small',
                normal: 'Normal',
                large: 'Large',
                fontFamily: 'Font Family',
                animationSpeed: 'Animation Speed',
                slow: 'Slow',
                fast: 'Fast',
                language: 'Language',
                resetAllSettings: 'Reset All Settings',
                newProject: 'New Project'
            },
            fr: {
                allProjects: 'Tous les Projets',
                projectsSubtitle: 'Liste complète de tous les projets de développement urbain',
                search: 'Recherche',
                searchPlaceholder: 'Rechercher des projets...',
                category: 'Catégorie',
                allCategories: 'Toutes les Catégories',
                status: 'Statut',
                allStatuses: 'Tous les Statuts',
                pending: 'En Attente',
                active: 'Actif',
                completed: 'Terminé',
                location: 'Emplacement',
                allLocations: 'Tous les Emplacements',
                startDate: 'Date de Début',
                endDate: 'Date de Fin',
                reset: 'Réinitialiser',
                applyFilters: 'Appliquer les Filtres',
                project: 'Projet',
                budget: 'Budget',
                teamSize: 'Taille de l\'Équipe',
                timeline: 'Calendrier',
                actions: 'Actions',
                members: 'membres',
                noProjects: 'Aucun projet trouvé. Essayez d\'ajuster vos filtres.',
                showing: 'Affichage de',
                to: 'à',
                of: 'sur',
                projects: 'projets',
                settings: 'Paramètres',
                dashboardSettings: 'Paramètres du Tableau de Bord',
                themeColors: 'Couleurs du Thème',
                displayOptions: 'Options d\'Affichage',
                darkMode: 'Mode Sombre',
                compactUI: 'Interface Compacte',
                reducedAnimations: 'Animations Réduites',
                textSize: 'Taille du Texte',
                small: 'Petit',
                normal: 'Normal',
                large: 'Grand',
                fontFamily: 'Police de Caractères',
                animationSpeed: 'Vitesse d\'Animation',
                slow: 'Lente',
                fast: 'Rapide',
                language: 'Langue',
                resetAllSettings: 'Réinitialiser Tous les Paramètres',
                newProject: 'Nouveau Projet'
            },
            es: {
                allProjects: 'Todos los Proyectos',
                projectsSubtitle: 'Lista completa de todos los proyectos de desarrollo urbano',
                search: 'Buscar',
                searchPlaceholder: 'Buscar proyectos...',
                category: 'Categoría',
                allCategories: 'Todas las Categorías',
                status: 'Estado',
                allStatuses: 'Todos los Estados',
                pending: 'Pendiente',
                active: 'Activo',
                completed: 'Completado',
                location: 'Ubicación',
                allLocations: 'Todas las Ubicaciones',
                startDate: 'Fecha de Inicio',
                endDate: 'Fecha de Finalización',
                reset: 'Restablecer',
                applyFilters: 'Aplicar Filtros',
                project: 'Proyecto',
                budget: 'Presupuesto',
                teamSize: 'Tamaño del Equipo',
                timeline: 'Cronología',
                actions: 'Acciones',
                members: 'miembros',
                noProjects: 'No se encontraron proyectos. Intente ajustar sus filtros.',
                showing: 'Mostrando',
                to: 'a',
                of: 'de',
                projects: 'proyectos',
                settings: 'Configuración',
                dashboardSettings: 'Configuración del Panel',
                themeColors: 'Colores del Tema',
                displayOptions: 'Opciones de Visualización',
                darkMode: 'Modo Oscuro',
                compactUI: 'Interfaz Compacta',
                reducedAnimations: 'Animaciones Reducidas',
                textSize: 'Tamaño del Texto',
                small: 'Pequeño',
                normal: 'Normal',
                large: 'Grande',
                fontFamily: 'Tipo de Letra',
                animationSpeed: 'Velocidad de Animación',
                slow: 'Lenta',
                fast: 'Rápida',
                language: 'Idioma',
                resetAllSettings: 'Restablecer Configuración',
                newProject: 'Nuevo Proyecto'
            },
            de: {
                allProjects: 'Alle Projekte',
                projectsSubtitle: 'Umfassende Liste aller Stadtentwicklungsprojekte',
                search: 'Suche',
                searchPlaceholder: 'Projekte suchen...',
                category: 'Kategorie',
                allCategories: 'Alle Kategorien',
                status: 'Status',
                allStatuses: 'Alle Status',
                pending: 'Ausstehend',
                active: 'Aktiv',
                completed: 'Abgeschlossen',
                location: 'Standort',
                allLocations: 'Alle Standorte',
                startDate: 'Startdatum',
                endDate: 'Enddatum',
                reset: 'Zurücksetzen',
                applyFilters: 'Filter anwenden',
                project: 'Projekt',
                budget: 'Budget',
                teamSize: 'Teamgröße',
                timeline: 'Zeitplan',
                actions: 'Aktionen',
                members: 'Mitglieder',
                noProjects: 'Keine Projekte gefunden. Versuchen Sie, Ihre Filter anzupassen.',
                showing: 'Anzeige',
                to: 'bis',
                of: 'von',
                projects: 'Projekten',
                settings: 'Einstellungen',
                dashboardSettings: 'Dashboard-Einstellungen',
                themeColors: 'Themenfarben',
                displayOptions: 'Anzeigeoptionen',
                darkMode: 'Dunkler Modus',
                compactUI: 'Kompakte Benutzeroberfläche',
                reducedAnimations: 'Reduzierte Animationen',
                textSize: 'Textgröße',
                small: 'Klein',
                normal: 'Normal',
                large: 'Groß',
                fontFamily: 'Schriftart',
                animationSpeed: 'Animationsgeschwindigkeit',
                slow: 'Langsam',
                fast: 'Schnell',
                language: 'Sprache',
                resetAllSettings: 'Alle Einstellungen Zurücksetzen',
                newProject: 'Neues Projekt'
            },
            ar: {
                allProjects: 'جميع المشاريع',
                projectsSubtitle: 'قائمة شاملة بجميع مشاريع التطوير الحضري',
                search: 'بحث',
                searchPlaceholder: 'البحث عن المشاريع...',
                category: 'فئة',
                allCategories: 'جميع الفئات',
                status: 'الحالة',
                allStatuses: 'جميع الحالات',
                pending: 'قيد الانتظار',
                active: 'نشط',
                completed: 'مكتمل',
                location: 'موقع',
                allLocations: 'جميع المواقع',
                startDate: 'تاريخ البدء',
                endDate: 'تاريخ الانتهاء',
                reset: 'إعادة تعيين',
                applyFilters: 'تطبيق المرشحات',
                project: 'مشروع',
                budget: 'ميزانية',
                teamSize: 'حجم الفريق',
                timeline: 'الجدول الزمني',
                actions: 'إجراءات',
                members: 'أعضاء',
                noProjects: 'لم يتم العثور على مشاريع. حاول ضبط المرشحات الخاصة بك.',
                showing: 'عرض',
                to: 'إلى',
                of: 'من',
                projects: 'المشاريع',
                settings: 'الإعدادات',
                dashboardSettings: 'إعدادات لوحة المعلومات',
                themeColors: 'ألوان السمة',
                displayOptions: 'خيارات العرض',
                darkMode: 'الوضع المظلم',
                compactUI: 'واجهة مدمجة',
                reducedAnimations: 'تقليل الرسوم المتحركة',
                textSize: 'حجم النص',
                small: 'صغير',
                normal: 'عادي',
                large: 'كبير',
                fontFamily: 'نوع الخط',
                animationSpeed: 'سرعة الرسوم المتحركة',
                slow: 'بطيء',
                fast: 'سريع',
                language: 'اللغة',
                resetAllSettings: 'إعادة تعيين جميع الإعدادات',
                newProject: 'مشروع جديد'
            }
        };

        // Function to apply translations
        function changeLanguage(lang) {
            if (!translations[lang]) {
                console.error('Language not supported:', lang);
                return;
            }

            const texts = translations[lang];

            // Update page title and subtitle
            const pageTitle = document.querySelector('.page-title');
            const pageSubtitle = document.querySelector('.page-subtitle');
            if (pageTitle) pageTitle.textContent = texts.allProjects;
            if (pageSubtitle) pageSubtitle.textContent = texts.projectsSubtitle;

            // Update filter labels
            const filterLabels = document.querySelectorAll('.filter-label');
            filterLabels.forEach(label => {
                const forAttr = label.getAttribute('for');
                if (forAttr === 'search') label.textContent = texts.search;
                if (forAttr === 'category') label.textContent = texts.category;
                if (forAttr === 'status') label.textContent = texts.status;
                if (forAttr === 'location') label.textContent = texts.location;
                if (forAttr === 'date_from') label.textContent = texts.startDate;
                if (forAttr === 'date_to') label.textContent = texts.endDate;
            });

            // Update filter placeholders
            document.querySelector('#search').placeholder = texts.searchPlaceholder;

            // Update select option texts
            const categorySelect = document.querySelector('#category');
            if (categorySelect) {
                categorySelect.options[0].text = texts.allCategories;
            }

            const statusSelect = document.querySelector('#status');
            if (statusSelect) {
                statusSelect.options[0].text = texts.allStatuses;
                statusSelect.options[1].text = texts.pending;
                statusSelect.options[2].text = texts.active;
                statusSelect.options[3].text = texts.completed;
            }

            const locationSelect = document.querySelector('#location');
            if (locationSelect) {
                locationSelect.options[0].text = texts.allLocations;
            }

            // Update filter buttons
            const resetBtn = document.querySelector('form button[type="reset"]');
            const applyBtn = document.querySelector('form button[type="submit"]');
            if (resetBtn) resetBtn.textContent = texts.reset;
            if (applyBtn) applyBtn.textContent = texts.applyFilters;

            // Update table headers
            const tableHeaders = document.querySelectorAll('.projects-table th');
            if (tableHeaders.length > 0) {
                tableHeaders[0].textContent = texts.project;
                tableHeaders[1].textContent = texts.budget;
                tableHeaders[2].textContent = texts.location;
                tableHeaders[3].textContent = texts.teamSize;
                tableHeaders[4].textContent = texts.status;
                tableHeaders[5].textContent = texts.timeline;
                tableHeaders[6].textContent = texts.actions;
            }

            // Update new project button
            const newProjectBtn = document.querySelector('.header-actions .btn-primary');
            if (newProjectBtn) {
                newProjectBtn.innerHTML = `<i class="fas fa-plus"></i> ${texts.newProject}`;
            }

            // Update settings panel
            const settingsTitle = document.querySelector('.settings-title');
            if (settingsTitle) settingsTitle.textContent = texts.dashboardSettings;

            const settingsSectionTitles = document.querySelectorAll('.settings-section-title');
            if (settingsSectionTitles.length >= 6) {
                settingsSectionTitles[0].textContent = texts.themeColors;
                settingsSectionTitles[1].textContent = texts.displayOptions;
                settingsSectionTitles[2].textContent = texts.textSize;
                settingsSectionTitles[3].textContent = texts.fontFamily;
                settingsSectionTitles[4].textContent = texts.animationSpeed;
                settingsSectionTitles[5].textContent = texts.language;
            }

            const toggleTexts = document.querySelectorAll('.toggle-text');
            if (toggleTexts.length >= 3) {
                toggleTexts[0].textContent = texts.darkMode;
                toggleTexts[1].textContent = texts.compactUI;
                toggleTexts[2].textContent = texts.reducedAnimations;
            }

            const rangeValueSpans = document.querySelectorAll('.range-value span');
            if (rangeValueSpans.length >= 3) {
                rangeValueSpans[0].textContent = texts.small;
                rangeValueSpans[1].textContent = texts.normal;
                rangeValueSpans[2].textContent = texts.large;
            }

            const speedOptions = document.querySelectorAll('.speed-option');
            if (speedOptions.length >= 3) {
                speedOptions[0].textContent = texts.slow;
                speedOptions[1].textContent = texts.normal;
                speedOptions[2].textContent = texts.fast;
            }

            const resetButton = document.querySelector('.reset-button');
            if (resetButton) resetButton.textContent = texts.resetAllSettings;

            // RTL support for Arabic
            if (lang === 'ar') {
                document.body.setAttribute('dir', 'rtl');
                document.querySelectorAll('.filter-row, .header-actions').forEach(el => {
                    el.style.flexDirection = 'row-reverse';
                });
            } else {
                document.body.setAttribute('dir', 'ltr');
                document.querySelectorAll('.filter-row, .header-actions').forEach(el => {
                    el.style.flexDirection = 'row';
                });
            }
        }
    </script>
</body>
</html> 