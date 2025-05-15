<?php
require_once(__DIR__ . '/../../../../config/Database.php');

// Initialize the database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Task statistics queries (use $conn instead of $pdo)
$totalTasksQuery = "SELECT COUNT(*) as total FROM tasks";
$totalTasksStmt = $conn->query($totalTasksQuery);
$totalTasks = $totalTasksStmt->fetch(PDO::FETCH_ASSOC)['total'];

$completedTasksQuery = "SELECT COUNT(*) as completed FROM tasks WHERE status = 'Done'";
$completedTasksStmt = $conn->query($completedTasksQuery);
$completedTasks = $completedTasksStmt->fetch(PDO::FETCH_ASSOC)['completed'];

$overdueTasksQuery = "SELECT COUNT(*) as overdue FROM tasks WHERE due_date < CURDATE() AND status != 'Done'";
$overdueTasksStmt = $conn->query($overdueTasksQuery);
$overdueTasks = $overdueTasksStmt->fetch(PDO::FETCH_ASSOC)['overdue'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityPulse - Urban Project Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- Add Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
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
            --navbar-height: 0px; /* For offset when scrolling */
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
            scroll-padding-top: 20px; /* Add padding to avoid section being hidden under fixed elements */
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

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .greeting {
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--text-medium);
            font-weight: 400;
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 1.5rem;
            color: var(--primary-color);
            opacity: 0.2;
        }

        .stat-card:nth-child(2) .stat-icon {
            color: var(--secondary-color);
        }

        .stat-card:nth-child(3) .stat-icon {
            color: var(--accent-color);
        }

        .stat-card:nth-child(4) .stat-icon {
            color: var(--light-accent);
        }

        .stat-title {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-medium);
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card:nth-child(1) .stat-value {
            color: var(--primary-color);
        }

        .stat-card:nth-child(2) .stat-value {
            color: var(--secondary-color);
        }

        .stat-card:nth-child(3) .stat-value {
            color: var(--accent-color);
        }

        .stat-card:nth-child(4) .stat-value {
            color: var(--light-accent);
        }

        .stat-change {
            font-size: 0.875rem;
            color: var(--text-medium);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .trend-up {
            color: #4caf50;
        }

        .trend-down {
            color: #f44336;
        }

        /* Chart Sections */
        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            transition: var(--transition);
        }

        .chart-card:hover {
            box-shadow: var(--shadow-md);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .chart-tabs {
            display: flex;
            gap: 12px;
        }

        .chart-tab {
            background: none;
            border: none;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--text-medium);
            cursor: pointer;
            transition: var(--transition);
        }

        .chart-tab.active {
            background-color: rgba(108, 99, 255, 0.1);
            color: var(--primary-color);
            font-weight: 500;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            color: var(--text-medium);
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        /* Projects Table */
        .table-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .projects-table {
            width: 100%;
            border-collapse: collapse;
        }

        .projects-table th {
            text-align: left;
            padding: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-medium);
            border-bottom: 1px solid var(--border-color);
        }

        .projects-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .projects-table tr:last-child td {
            border-bottom: none;
        }

        .project-name {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .project-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
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

        .action-btn {
            background: none;
            border: none;
            font-size: 1rem;
            color: var(--text-medium);
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            color: var(--text-dark);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            
            .logo span {
                display: none;
            }
            
            .nav-link span {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .chart-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        body.font-mono {
            font-family: monospace;
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

        .theme-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .theme-option {
            aspect-ratio: 1/1;
            border-radius: 12px;
            cursor: pointer;
            position: relative;
            transition: var(--transition);
            border: 2px solid transparent;
            overflow: hidden;
        }

        .theme-option.active {
            transform: scale(1.05);
            border-color: var(--primary-color);
        }

        .theme-option:hover {
            transform: translateY(-5px);
        }

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

        /* Animation settings */
        body.reduced-motion *:not(.settings-panel):not(.settings-panel *):not(.settings-overlay):not(.settings-toggle) {
            animation-duration: 0.001ms !important;
            transition-duration: 0.001ms !important;
        }

        .settings-panel {
            transition: right 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        }

        body.compact-ui .stats-grid,
        body.compact-ui .chart-grid {
            gap: 0.75rem;
        }

        body.compact-ui .stat-card,
        body.compact-ui .chart-card,
        body.compact-ui .table-card {
            padding: 1rem;
        }

        /* Font size adjustments */
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
        
        /* PDF Export loading */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            display: none;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Map styles */
        #contributor-map {
            height: 400px;
            width: 100%;
            border-radius: 12px;
            margin-bottom: 1rem;
        }
        
        .map-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .map-legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
            background-color: rgba(0,0,0,0.05);
            border-radius: 20px;
            font-size: 0.875rem;
        }
        
        body.dark-mode .map-legend-item {
            background-color: rgba(255,255,255,0.1);
        }
        
        .map-marker {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .marker-designer { background-color: #6c63ff; }
        .marker-developer { background-color: #00b8a9; }
        .marker-manager { background-color: #ff6b6b; }
        .marker-architect { background-color: #ffd166; }
        .marker-planner { background-color: #4CAF50; }
        
        .map-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .map-tab {
            padding: 8px 16px;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .map-tab.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Contributor list styles */
        .contributor-list {
            max-height: 400px;
            overflow-y: auto;
            display: none;
        }
        
        .contributor-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px;
            border-radius: 8px;
            background-color: var(--card-bg);
            margin-bottom: 10px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        
        .contributor-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .contributor-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .contributor-info {
            flex: 1;
        }
        
        .contributor-name {
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .contributor-meta {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: var(--text-medium);
        }
        
        .contributor-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .contributor-type {
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .type-designer { background-color: rgba(108, 99, 255, 0.1); color: #6c63ff; }
        .type-developer { background-color: rgba(0, 184, 169, 0.1); color: #00b8a9; }
        .type-manager { background-color: rgba(255, 107, 107, 0.1); color: #ff6b6b; }
        .type-architect { background-color: rgba(255, 209, 102, 0.1); color: #ffd166; }
        .type-planner { background-color: rgba(76, 175, 80, 0.1); color: #4CAF50; }

        .map-info-window {
    font-family: 'Roboto', sans-serif;
    max-width: 250px;
}

.map-info-window h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: var(--primary-color);
}

.map-info-window p {
    margin: 5px 0;
    font-size: 14px;
    color: #555;
}

.map-info-window .status-active {
    color: #4CAF50;
    font-weight: bold;
}

.map-info-window .status-pending {
    color: #FF9800;
    font-weight: bold;
}

.map-info-window .status-completed {
    color: #9C27B0;
    font-weight: bold;
}

.map-info-window .view-project-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 5px 10px;
    background-color: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 13px;
}

.map-info-window .view-project-btn:hover {
    background-color: var(--secondary-color);
}
    </style>
    <!-- Add Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>
<body>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="/test1/dashboardgol.php" class="logo">
                <i class="fas fa-city"></i>
                <span>CityPulse</span>
            </a>
        </div>
        <nav>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="creative_dashboard.php" class="nav-link active">
                        <i class="fas fa-columns"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#analytics-section" class="nav-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#projects-category-section" class="nav-link">
                        <i class="fas fa-th-large"></i>
                        <span>Projects by Category</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#recent-projects-section" class="nav-link">
                        <i class="fas fa-project-diagram"></i>
                        <span>Recent Projects</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#timeline-section" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Projects Timeline Analysis</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#task-stats-section" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Tasks</span>
                    </a>
                </li>
                <div class="nav-divider"></div>
                <li class="nav-item">
                    <a href="../../../../views/frontoffice/createProject/createProject.html" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>New Project</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link settings-toggle">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="export-pdf">
                        <i class="fas fa-file-pdf"></i>
                        <span>Export PDF</span>
                    </a>
                </li>
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
        <div id="analytics-section" class="dashboard-header">
            <div>
                <h1 class="greeting">Urban Project Dashboard</h1>
                <p class="subtitle">Monitor and analyze your urbanization projects</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" id="export-btn">
                    <i class="fas fa-download"></i> Export Data
                </button>
                <a href="../../../../views/frontoffice/createProject/createProject.html" class="btn btn-primary">
    <i class="fas fa-plus"></i> New Project
</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <!-- Remplacez le bloc de code du budget par celui-ci -->
<div class="stat-card">
    <i class="fas fa-dollar-sign stat-icon"></i>
    <h3 class="stat-title">Total Projets</h3>
    <div class="stat-value">
    <?php 
                try {
                    $query = $conn->query("SELECT COUNT(*) as count FROM projects");
                    $result = $query->fetch(PDO::FETCH_ASSOC);
                    echo $result['count']; 
                } catch (PDOException $e) {
                    error_log("Error counting projects: " . $e->getMessage());
                    echo "0";
                }
                ?>
    </div>
    <div class="stat-change">
        <i class="fas fa-arrow-up trend-up"></i> 12% increase
    </div>
    <div class="stat-change">
        <?php
        try {
            // Calcul de l'évolution du budget
            $currentMonth = date('m');
            $currentYear = date('Y');
            
            // Budget du mois en cours
            $currentBudgetQuery = $conn->query("
                SELECT COALESCE(SUM(NULLIF(projectBudget, 0)), 0) as total 
                FROM projects 
                WHERE MONTH(created_at) = $currentMonth 
                AND YEAR(created_at) = $currentYear
            ");
            $currentBudget = $currentBudgetQuery->fetch(PDO::FETCH_ASSOC)['total'];

            // Budget du mois précédent
            $lastMonth = $currentMonth - 1;
            $yearForLastMonth = $currentYear;
            if ($lastMonth == 0) {
                $lastMonth = 12;
                $yearForLastMonth--;
            }

            $previousBudgetQuery = $conn->query("
                SELECT COALESCE(SUM(NULLIF(projectBudget, 0)), 0) as total 
                FROM projects 
                WHERE MONTH(created_at) = $lastMonth 
                AND YEAR(created_at) = $yearForLastMonth
            ");
            $previousBudget = $previousBudgetQuery->fetch(PDO::FETCH_ASSOC)['total'];

            // Calcul du pourcentage d'évolution
            if ($previousBudget > 0) {
                $percentChange = (($currentBudget - $previousBudget) / $previousBudget) * 100;
                $trend = $percentChange >= 0 ? 'up' : 'down';
                echo '<i class="fas fa-arrow-' . $trend . ' trend-' . $trend . '"></i> ';
                echo abs(round($percentChange, 1)) . '% ' . ($trend === 'up' ? 'increase' : 'decrease');
            } else {
                echo '<i class="fas fa-minus"></i> Nouveau mois';
            }
        } catch (PDOException $e) {
            error_log("Erreur lors du calcul de l'évolution du budget : " . $e->getMessage());
            echo '<i class="fas fa-minus"></i> Non disponible';
        }
        ?>
    </div>
</div>
<div class="stat-card">
                <i class="fas fa-dollar-sign stat-icon"></i>
                <h3 class="stat-title">Total Budget</h3>
                <div class="stat-value">
                    <?php 
                    $query = $conn->query("SELECT SUM(projectBudget) as total FROM projects");
                    $total = $query->fetch(PDO::FETCH_ASSOC)['total'];
                    echo number_format($total ?? 0); 
                    ?> €
                </div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up trend-up"></i> 8.5% increase
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users stat-icon"></i>
                <h3 class="stat-title">Team Members</h3>
                <div class="stat-value">
                    <?php 
                    $query = $conn->query("SELECT SUM(teamSize) as total FROM projects");
                    echo $query->fetch(PDO::FETCH_ASSOC)['total'] ?? 0; 
                    ?>
                </div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up trend-up"></i> 15% increase
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle stat-icon"></i>
                <h3 class="stat-title">Completed Projects</h3>
                <div class="stat-value">
                    <?php 
                    $query = $conn->query("SELECT COUNT(*) as count FROM projects WHERE endDate < CURDATE()");
                    echo $query->fetch(PDO::FETCH_ASSOC)['count']; 
                    ?>
                </div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up trend-up"></i> 5% increase
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div id="projects-category-section" class="chart-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Projects by Category</h3>
                    <div class="chart-tabs">
                        <button class="chart-tab active">This Year</button>
                        <button class="chart-tab">Last Year</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="projectsChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Budget Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="budgetChart"></canvas>
                </div>
                <div class="chart-legend">
                    <?php 
                    $categoryQuery = $conn->query("SELECT DISTINCT projectCategory FROM projects LIMIT 5");
                    $categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);
                    $colors = ['#6c63ff', '#00b8a9', '#ff6b6b', '#d295ff', '#ffd166'];
                    
                    foreach ($categories as $index => $category) {
                        $color = $colors[$index % count($colors)];
                        echo '<div class="legend-item">';
                        echo '<div class="legend-color" style="background-color: ' . $color . ';"></div>';
                        echo '<span>' . htmlspecialchars($category['projectCategory']) . '</span>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Projects Table -->
        <div id="recent-projects-section" class="table-card">
            <div class="table-header">
                <h3 class="chart-title">Recent Projects</h3>
                <a href="all_projects.php" class="btn btn-outline">View All</a>
            </div>
            <table class="projects-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Budget</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Timeline</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $projectQuery = $conn->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5");
                    $projects = $projectQuery->fetchAll(PDO::FETCH_ASSOC);
                    
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
                                        <div style="font-weight: 500;">' . htmlspecialchars($project['projectName']) . '</div>
                                        <div style="font-size: 0.8rem; color: var(--text-light);">' . htmlspecialchars($project['projectCategory']) . '</div>
                                    </div>
                                </div>
                            </td>';
                        echo '<td>' . number_format($project['projectBudget'] ?? 0) . ' €</td>';
                        echo '<td>' . htmlspecialchars($project['projectLocation']) . '</td>';
                        echo '<td><span class="status-badge status-' . $status . '">' . $statusText . '</span></td>';
                        echo '<td>' . date('M d, Y', strtotime($project['startDate'])) . ' - ' . date('M d, Y', strtotime($project['endDate'])) . '</td>';
                        echo '<td>
                                <button class="action-btn">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Engagement Trend Chart -->
        <div id="task-section" class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Projects Timeline Analysis</h3>
                <div class="chart-tabs">
                    <button class="chart-tab active" data-period="monthly">Monthly</button>
                    <button class="chart-tab" data-period="quarterly">Quarterly</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="engagementChart"></canvas>
            </div>
        </div>


        <!-- Improved Task Statistics Section -->
<section id="task-stats-section" class="task-stats-section" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700; color: var(--primary-color); letter-spacing: 1px;">Task Statistics</h2>
    <div style="display: flex; gap: 2.5rem; flex-wrap: wrap; justify-content: flex-start;">
        <div class="stat-card task-stat-card" style="min-width:220px; background: linear-gradient(135deg, #e0e7ff 60%, #f8fafc 100%); box-shadow: 0 4px 16px rgba(67,97,238,0.08); border-left: 6px solid #6c63ff;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-tasks stat-icon" style="color: #6c63ff; opacity: 1; font-size: 2.2rem; position: static;"></i>
                <div>
                    <div class="stat-title" style="font-size: 1.1rem; color: #6c63ff; font-weight: 600;">Total Tasks</div>
                    <div class="stat-value" style="font-size: 2.3rem; color: #2c3e50; font-weight: 800;"><?php echo $totalTasks; ?></div>
                </div>
            </div>
        </div>
        <div class="stat-card task-stat-card" style="min-width:220px; background: linear-gradient(135deg, #e0ffe7 60%, #f8fafc 100%); box-shadow: 0 4px 16px rgba(67,238,138,0.08); border-left: 6px solid #4caf50;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-check-circle stat-icon" style="color: #4caf50; opacity: 1; font-size: 2.2rem; position: static;"></i>
                <div>
                    <div class="stat-title" style="font-size: 1.1rem; color: #4caf50; font-weight: 600;">Completed Tasks</div>
                    <div class="stat-value" style="font-size: 2.3rem; color: #2c3e50; font-weight: 800;"><?php echo $completedTasks; ?></div>
                </div>
            </div>
        </div>
        <div class="stat-card task-stat-card" style="min-width:220px; background: linear-gradient(135deg, #fff7e7 60%, #f8fafc 100%); box-shadow: 0 4px 16px rgba(255,193,7,0.08); border-left: 6px solid #ff9800;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-exclamation-triangle stat-icon" style="color: #ff9800; opacity: 1; font-size: 2.2rem; position: static;"></i>
                <div>
                    <div class="stat-title" style="font-size: 1.1rem; color: #ff9800; font-weight: 600;">Overdue Tasks</div>
                    <div class="stat-value" style="font-size: 2.3rem; color: #2c3e50; font-weight: 800;"><?php echo $overdueTasks; ?></div>
                </div>
            </div>
        </div>
    </div>
</section>
        
        <!-- Add a new section for contributor locations after the engagement trend chart -->
        <div id="contributor-section" class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Contributor Locations</h3>
                <div class="chart-tabs">
                    <button class="chart-tab active" data-period="all">All Contributors</button>
                    <button class="chart-tab" data-period="recent">Recent Contributors</button>
                </div>
            </div>
            
            <div class="map-tabs">
                <div class="map-tab active" data-view="map">Map View</div>
                <div class="map-tab" data-view="list">List View</div>
            </div>
            
            <div id="contributor-map"></div>
            
            <div id="contributor-list" class="contributor-list">
                <!-- Will be populated via JavaScript -->
            </div>
            
            <div class="map-legend">
                <div class="map-legend-item">
                    <div class="map-marker marker-designer"></div>
                    <span>Designer</span>
                </div>
                <div class="map-legend-item">
                    <div class="map-marker marker-developer"></div>
                    <span>Developer</span>
                </div>
                <div class="map-legend-item">
                    <div class="map-marker marker-manager"></div>
                    <span>Project Manager</span>
                </div>
                <div class="map-legend-item">
                    <div class="map-marker marker-architect"></div>
                    <span>Architect</span>
                </div>
                <div class="map-legend-item">
                    <div class="map-marker marker-planner"></div>
                    <span>Urban Planner</span>
                </div>
            </div>
        </div>
    </main>

    <?php
    // --- Fetch data for charts ---
    $categoryQuery = $conn->query("SELECT projectCategory, COUNT(*) as count FROM projects GROUP BY projectCategory");
    $categoryData = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

    $budgetQuery = $conn->query("SELECT projectCategory, SUM(projectBudget) as total FROM projects GROUP BY projectCategory");
    $budgetData = $budgetQuery->fetchAll(PDO::FETCH_ASSOC);

    // --- Monthly Project Data for Timeline Analysis ---
    $monthlyProjectQuery = $conn->query("
        SELECT 
            MONTH(startDate) as month,
            COUNT(*) as project_count,
            SUM(projectBudget) as total_budget,
            SUM(teamSize) as team_members
        FROM 
            projects 
        WHERE 
            YEAR(startDate) = YEAR(CURDATE())
        GROUP BY 
            MONTH(startDate)
        ORDER BY 
            month ASC
    ");
    $monthlyData = $monthlyProjectQuery->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare data for JS
    $categories = [];
    $counts = [];
    $budgets = [];

    foreach ($categoryData as $row) {
        $categories[] = $row['projectCategory'];
        $counts[] = $row['count'];
    }

    foreach ($budgetData as $row) {
        $budgets[] = $row['total'];
    }
    
    // Prepare monthly data
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $monthlyProjects = array_fill(0, 12, 0);
    $monthlyBudgets = array_fill(0, 12, 0);
    $monthlyTeamSize = array_fill(0, 12, 0);
    
    foreach ($monthlyData as $row) {
        $monthIndex = $row['month'] - 1; // Convert 1-12 to 0-11 for array index
        $monthlyProjects[$monthIndex] = (int)$row['project_count'];
        $monthlyBudgets[$monthIndex] = (float)$row['total_budget'];
        $monthlyTeamSize[$monthIndex] = (int)$row['team_members'];
    }
    ?>

    <!-- Settings Panel -->
    <div class="settings-overlay"></div>
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
    
    <!-- Loading Overlay for PDF Export -->
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
        <p>Generating PDF...</p>
    </div>

    <script>
    // Chart colors
    const colors = {
        primary: '#6c63ff',
        secondary: '#00b8a9',
        accent: '#ff6b6b',
        light: '#d295ff',
        yellow: '#ffd166',
        chartColors: ['#6c63ff', '#00b8a9', '#ff6b6b', '#d295ff', '#ffd166']
    };

    // Gradient for area charts
    function createGradient(ctx, color) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, color + 'CC');  // 80% opacity
        gradient.addColorStop(1, color + '00');  // 0% opacity
        return gradient;
    }
    
    // Projects by Category Chart
    const projectsCtx = document.getElementById('projectsChart').getContext('2d');
    const projectsChart = new Chart(projectsCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($categories); ?>,
            datasets: [{
                label: 'Number of Projects',
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: colors.chartColors,
                borderRadius: 8,
                borderWidth: 0
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
                    backgroundColor: 'white',
                    titleColor: '#2c3e50',
                    bodyColor: '#5a6b7b',
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return `Projects: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
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

    // Budget Distribution Chart
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    const budgetChart = new Chart(budgetCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($categories); ?>,
            datasets: [{
                data: <?php echo json_encode($budgets); ?>,
                backgroundColor: colors.chartColors,
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
                    backgroundColor: 'white',
                    titleColor: '#2c3e50',
                    bodyColor: '#5a6b7b',
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            return `${context.label}: ${new Intl.NumberFormat('fr-FR', {
                                style: 'currency',
                                currency: 'EUR'
                            }).format(value)}`;
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Projects Timeline Analysis Chart with real data
    const engagementCtx = document.getElementById('engagementChart').getContext('2d');
    
    const engagementChart = new Chart(engagementCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Number of Projects',
                data: <?php echo json_encode($monthlyProjects); ?>,
                borderColor: colors.primary,
                backgroundColor: createGradient(engagementCtx, colors.primary),
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: 'white',
                pointBorderColor: colors.primary,
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                yAxisID: 'y'
            }, {
                label: 'Budget Allocation (€)',
                data: <?php echo json_encode($monthlyBudgets); ?>,
                borderColor: colors.secondary,
                backgroundColor: createGradient(engagementCtx, colors.secondary),
                tension: 0.4,
                fill: false,
                pointRadius: 4,
                pointBackgroundColor: 'white',
                pointBorderColor: colors.secondary,
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                yAxisID: 'y1'
            }, {
                label: 'Team Size',
                data: <?php echo json_encode($monthlyTeamSize); ?>,
                borderColor: colors.accent,
                backgroundColor: createGradient(engagementCtx, colors.accent),
                tension: 0.4,
                fill: false,
                pointRadius: 4,
                pointBackgroundColor: 'white',
                pointBorderColor: colors.accent,
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                yAxisID: 'y'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 10,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'white',
                    titleColor: '#2c3e50',
                    bodyColor: '#5a6b7b',
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            
                            if (label === 'Budget Allocation (€)') {
                                return `${label}: ${new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: 'EUR'
                                }).format(value)}`;
                            }
                            return `${label}: ${value}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    title: {
                        display: true,
                        text: 'Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    title: {
                        display: true,
                        text: 'Budget (€)'
                    },
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M €';
                            } else if (value >= 1000) {
                                return (value / 1000).toFixed(0) + 'k €';
                            }
                            return value + ' €';
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

    // Add interactivity to chart tabs
    document.querySelectorAll('.chart-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const parent = this.parentElement;
            parent.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // If this is in the timeline section, handle quarterly/monthly view
            if (this.dataset.period) {
                updateTimelineChart(this.dataset.period);
            }
        });
    });

    // Function to update timeline chart based on period (monthly/quarterly)
    function updateTimelineChart(period) {
        if (!engagementChart) return;
        
        if (period === 'monthly') {
            // Use monthly data (already set up)
            engagementChart.data.labels = <?php echo json_encode($months); ?>;
            engagementChart.data.datasets[0].data = <?php echo json_encode($monthlyProjects); ?>;
            engagementChart.data.datasets[1].data = <?php echo json_encode($monthlyBudgets); ?>;
            engagementChart.data.datasets[2].data = <?php echo json_encode($monthlyTeamSize); ?>;
        } else if (period === 'quarterly') {
            // Calculate quarterly data
            const quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
            const monthlyProjects = <?php echo json_encode($monthlyProjects); ?>;
            const monthlyBudgets = <?php echo json_encode($monthlyBudgets); ?>;
            const monthlyTeamSize = <?php echo json_encode($monthlyTeamSize); ?>;
            
            const quarterlyProjects = [
                monthlyProjects.slice(0, 3).reduce((a, b) => a + b, 0),
                monthlyProjects.slice(3, 6).reduce((a, b) => a + b, 0),
                monthlyProjects.slice(6, 9).reduce((a, b) => a + b, 0),
                monthlyProjects.slice(9, 12).reduce((a, b) => a + b, 0)
            ];
            
            const quarterlyBudgets = [
                monthlyBudgets.slice(0, 3).reduce((a, b) => a + b, 0),
                monthlyBudgets.slice(3, 6).reduce((a, b) => a + b, 0),
                monthlyBudgets.slice(6, 9).reduce((a, b) => a + b, 0),
                monthlyBudgets.slice(9, 12).reduce((a, b) => a + b, 0)
            ];
            
            const quarterlyTeamSize = [
                Math.round(monthlyTeamSize.slice(0, 3).reduce((a, b) => a + b, 0) / 3),
                Math.round(monthlyTeamSize.slice(3, 6).reduce((a, b) => a + b, 0) / 3),
                Math.round(monthlyTeamSize.slice(6, 9).reduce((a, b) => a + b, 0) / 3),
                Math.round(monthlyTeamSize.slice(9, 12).reduce((a, b) => a + b, 0) / 3)
            ];
            
            engagementChart.data.labels = quarters;
            engagementChart.data.datasets[0].data = quarterlyProjects;
            engagementChart.data.datasets[1].data = quarterlyBudgets;
            engagementChart.data.datasets[2].data = quarterlyTeamSize;
        }
        
        engagementChart.update();
    }

    // Add interactivity to sidebar navigation
    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Only apply to links with hash (not external links)
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault(); // Prevent default anchor behavior
                    
                    // Remove active class from all links
                    navLinks.forEach(l => l.classList.remove('active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                    
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        // Smooth scroll to target
                        targetElement.scrollIntoView({ 
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
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
        
        // Update colors object for charts
        colors.primary = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
        colors.secondary = getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim();
        colors.accent = getComputedStyle(document.documentElement).getPropertyValue('--accent-color').trim();
        colors.light = getComputedStyle(document.documentElement).getPropertyValue('--light-accent').trim();
        colors.chartColors = [colors.primary, colors.secondary, colors.accent, colors.light, colors.yellow];
        
        // Update charts
        updateChartColors();
    }
    
    function updateChartColors() {
        // Update projects chart
        projectsChart.data.datasets[0].backgroundColor = colors.chartColors;
        projectsChart.update();
        
        // Update budget chart
        budgetChart.data.datasets[0].backgroundColor = colors.chartColors;
        budgetChart.update();
        
        // Update engagement chart
        engagementChart.data.datasets[0].borderColor = colors.primary;
        engagementChart.data.datasets[0].backgroundColor = createGradient(engagementCtx, colors.primary);
        engagementChart.data.datasets[0].pointBorderColor = colors.primary;
        
        engagementChart.data.datasets[1].borderColor = colors.secondary;
        engagementChart.data.datasets[1].backgroundColor = createGradient(engagementCtx, colors.secondary);
        engagementChart.data.datasets[1].pointBorderColor = colors.secondary;
        
        engagementChart.data.datasets[2].borderColor = colors.accent;
        engagementChart.data.datasets[2].backgroundColor = createGradient(engagementCtx, colors.accent);
        engagementChart.data.datasets[2].pointBorderColor = colors.accent;
        
        engagementChart.update();
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
        
        // Update charts if they exist
        if (typeof updateChartColors === 'function') {
            updateChartColors();
        }
    }

    // PDF Export functionality
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.getElementById('export-btn');
        const exportPdfLink = document.getElementById('export-pdf');
        const loadingOverlay = document.querySelector('.loading-overlay');
        
        function generatePDF() {
            loadingOverlay.classList.add('active');
            
            // Utiliser window.jsPDF car c'est exposé comme ça par le CDN
            const { jsPDF } = window.jspdf;
            
            html2canvas(document.querySelector('.main-content')).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save('urban_projects_dashboard.pdf');
                
                loadingOverlay.classList.remove('active');
                alert('PDF exporté avec succés');
            });
        }
        
        // Add event listeners to both export buttons
        exportBtn.addEventListener('click', generatePDF);
        exportPdfLink.addEventListener('click', function(e) {
            e.preventDefault();
            generatePDF();
        });
    });

    // Language selector
    document.getElementById('language-select')?.addEventListener('change', function() {
        const selectedLanguage = this.value;
        changeLanguage(selectedLanguage);
        saveSettings('language', selectedLanguage);
    });

    // Translations object
    const translations = {
        en: {
            greeting: 'Urban Project Dashboard',
            subtitle: 'Monitor and analyze your urbanization projects',
            exportData: 'Export Data',
            newProject: 'New Project',
            totalProjects: 'Total Projects',
            totalBudget: 'Total Budget',
            teamMembers: 'Team Members',
            completedProjects: 'Completed Projects',
            increase: 'increase',
            projectsByCategory: 'Projects by Category',
            budgetDistribution: 'Budget Distribution',
            thisYear: 'This Year',
            lastYear: 'Last Year',
            recentProjects: 'Recent Projects',
            viewAll: 'View All',
            project: 'Project',
            budget: 'Budget',
            location: 'Location',
            status: 'Status',
            timeline: 'Timeline',
            action: 'Action',
            pending: 'Pending',
            active: 'Active',
            completed: 'Completed',
            timelineAnalysis: 'Projects Timeline Analysis',
            monthly: 'Monthly',
            quarterly: 'Quarterly',
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
            dashboard: 'Dashboard',
            analytics: 'Analytics',
            projectsCategory: 'Projects by Category',
            recentProjectsNav: 'Recent Projects',
            projectsTimeline: 'Projects Timeline Analysis',
            newProjectNav: 'New Project',
            settingsNav: 'Settings',
            exportPDF: 'Export PDF',
            logout: 'Logout'
        },
        fr: {
            greeting: 'Tableau de Bord des Projets Urbains',
            subtitle: 'Surveillez et analysez vos projets d\'urbanisation',
            exportData: 'Exporter les Données',
            newProject: 'Nouveau Projet',
            totalProjects: 'Total des Projets',
            totalBudget: 'Budget Total',
            teamMembers: 'Membres de l\'Équipe',
            completedProjects: 'Projets Terminés',
            increase: 'augmentation',
            projectsByCategory: 'Projets par Catégorie',
            budgetDistribution: 'Répartition du Budget',
            thisYear: 'Cette Année',
            lastYear: 'Année Dernière',
            recentProjects: 'Projets Récents',
            viewAll: 'Voir Tout',
            project: 'Projet',
            budget: 'Budget',
            location: 'Emplacement',
            status: 'Statut',
            timeline: 'Calendrier',
            action: 'Action',
            pending: 'En Attente',
            active: 'Actif',
            completed: 'Terminé',
            timelineAnalysis: 'Analyse Temporelle des Projets',
            monthly: 'Mensuel',
            quarterly: 'Trimestriel',
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
            dashboard: 'Tableau de Bord',
            analytics: 'Analytique',
            projectsCategory: 'Projets par Catégorie',
            recentProjectsNav: 'Projets Récents',
            projectsTimeline: 'Analyse Temporelle',
            newProjectNav: 'Nouveau Projet',
            settingsNav: 'Paramètres',
            exportPDF: 'Exporter PDF',
            logout: 'Déconnexion'
        },
        es: {
            greeting: 'Panel de Proyectos Urbanos',
            subtitle: 'Monitoree y analice sus proyectos de urbanización',
            exportData: 'Exportar Datos',
            newProject: 'Nuevo Proyecto',
            totalProjects: 'Total de Proyectos',
            totalBudget: 'Presupuesto Total',
            teamMembers: 'Miembros del Equipo',
            completedProjects: 'Proyectos Completados',
            increase: 'aumento',
            projectsByCategory: 'Proyectos por Categoría',
            budgetDistribution: 'Distribución del Presupuesto',
            thisYear: 'Este Año',
            lastYear: 'Año Pasado',
            recentProjects: 'Proyectos Recientes',
            viewAll: 'Ver Todo',
            project: 'Proyecto',
            budget: 'Presupuesto',
            location: 'Ubicación',
            status: 'Estado',
            timeline: 'Cronología',
            action: 'Acción',
            pending: 'Pendiente',
            active: 'Activo',
            completed: 'Completado',
            timelineAnalysis: 'Análisis de Cronología de Proyectos',
            monthly: 'Mensual',
            quarterly: 'Trimestral',
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
            dashboard: 'Panel',
            analytics: 'Análisis',
            projectsCategory: 'Proyectos por Categoría',
            recentProjectsNav: 'Proyectos Recientes',
            projectsTimeline: 'Análisis de Cronología',
            newProjectNav: 'Nuevo Proyecto',
            settingsNav: 'Configuración',
            exportPDF: 'Exportar PDF',
            logout: 'Cerrar Sesión'
        },
        de: {
            greeting: 'Städtische Projekt-Dashboard',
            subtitle: 'Überwachen und analysieren Sie Ihre Stadtentwicklungsprojekte',
            exportData: 'Daten Exportieren',
            newProject: 'Neues Projekt',
            totalProjects: 'Gesamtprojekte',
            totalBudget: 'Gesamtbudget',
            teamMembers: 'Teammitglieder',
            completedProjects: 'Abgeschlossene Projekte',
            increase: 'Zunahme',
            projectsByCategory: 'Projekte nach Kategorie',
            budgetDistribution: 'Budgetverteilung',
            thisYear: 'Dieses Jahr',
            lastYear: 'Letztes Jahr',
            recentProjects: 'Aktuelle Projekte',
            viewAll: 'Alle Anzeigen',
            project: 'Projekt',
            budget: 'Budget',
            location: 'Standort',
            status: 'Status',
            timeline: 'Zeitplan',
            action: 'Aktion',
            pending: 'Ausstehend',
            active: 'Aktiv',
            completed: 'Abgeschlossen',
            timelineAnalysis: 'Projekt-Zeitplan-Analyse',
            monthly: 'Monatlich',
            quarterly: 'Vierteljährlich',
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
            dashboard: 'Dashboard',
            analytics: 'Analytik',
            projectsCategory: 'Projekte nach Kategorie',
            recentProjectsNav: 'Aktuelle Projekte',
            projectsTimeline: 'Projekt-Zeitplan-Analyse',
            newProjectNav: 'Neues Projekt',
            settingsNav: 'Einstellungen',
            exportPDF: 'PDF Exportieren',
            logout: 'Abmelden'
        },
        ar: {
            greeting: 'لوحة مشاريع التخطيط العمراني',
            subtitle: 'راقب وحلل مشاريع التطوير الحضري الخاصة بك',
            exportData: 'تصدير البيانات',
            newProject: 'مشروع جديد',
            totalProjects: 'إجمالي المشاريع',
            totalBudget: 'الميزانية الإجمالية',
            teamMembers: 'أعضاء الفريق',
            completedProjects: 'المشاريع المكتملة',
            increase: 'زيادة',
            projectsByCategory: 'المشاريع حسب الفئة',
            budgetDistribution: 'توزيع الميزانية',
            thisYear: 'هذا العام',
            lastYear: 'العام الماضي',
            recentProjects: 'المشاريع الحديثة',
            viewAll: 'عرض الكل',
            project: 'مشروع',
            budget: 'ميزانية',
            location: 'موقع',
            status: 'الحالة',
            timeline: 'الجدول الزمني',
            action: 'إجراء',
            pending: 'قيد الانتظار',
            active: 'نشط',
            completed: 'مكتمل',
            timelineAnalysis: 'تحليل الجدول الزمني للمشاريع',
            monthly: 'شهري',
            quarterly: 'ربع سنوي',
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
            dashboard: 'لوحة المعلومات',
            analytics: 'التحليلات',
            projectsCategory: 'المشاريع حسب الفئة',
            recentProjectsNav: 'المشاريع الحديثة',
            projectsTimeline: 'تحليل الجدول الزمني',
            newProjectNav: 'مشروع جديد',
            settingsNav: 'الإعدادات',
            exportPDF: 'تصدير PDF',
            logout: 'تسجيل الخروج'
        }
    };

    // Function to apply translations
    function changeLanguage(lang) {
        if (!translations[lang]) {
            console.error('Language not supported:', lang);
            return;
        }

        const texts = translations[lang];

        // Update all elements with data-i18n attributes
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            if (texts[key]) {
                element.textContent = texts[key];
            }
        });

        // Update navbar items
        document.querySelector('.nav-link[href="creative_dashboard.php"] span').textContent = texts.dashboard;
        document.querySelector('.nav-link[href="#analytics-section"] span').textContent = texts.analytics;
        document.querySelector('.nav-link[href="#projects-category-section"] span').textContent = texts.projectsCategory;
        document.querySelector('.nav-link[href="#recent-projects-section"] span').textContent = texts.recentProjectsNav;
        document.querySelector('.nav-link[href="#timeline-section"] span').textContent = texts.projectsTimeline;
        document.querySelector('.nav-link[href="../projet/create project/createProject.html"] span').textContent = texts.newProjectNav;
        document.querySelector('.settings-toggle span').textContent = texts.settingsNav;
        document.querySelector('#export-pdf span').textContent = texts.exportPDF;
        document.querySelector('.nav-link[href="#"]:last-child span').textContent = texts.logout;

        // Update header
        document.querySelector('.greeting').textContent = texts.greeting;
        document.querySelector('.subtitle').textContent = texts.subtitle;
        
        // Update buttons
        document.querySelector('#export-btn').innerHTML = `<i class="fas fa-download"></i> ${texts.exportData}`;
        document.querySelector('.header-actions .btn-primary').innerHTML = `<i class="fas fa-plus"></i> ${texts.newProject}`;

        // Update stat cards
        document.querySelectorAll('.stat-title')[0].textContent = texts.totalProjects;
        document.querySelectorAll('.stat-title')[1].textContent = texts.totalBudget;
        document.querySelectorAll('.stat-title')[2].textContent = texts.teamMembers;
        document.querySelectorAll('.stat-title')[3].textContent = texts.completedProjects;
        
        // Update chart titles
        document.querySelectorAll('.chart-title')[0].textContent = texts.projectsByCategory;
        document.querySelectorAll('.chart-title')[1].textContent = texts.budgetDistribution;
        document.querySelectorAll('.chart-title')[2].textContent = texts.recentProjects;
        document.querySelectorAll('.chart-title')[3].textContent = texts.timelineAnalysis;

        // Update chart tabs
        document.querySelectorAll('.chart-tab')[0].textContent = texts.thisYear;
        document.querySelectorAll('.chart-tab')[1].textContent = texts.lastYear;
        document.querySelectorAll('.chart-tab')[2].textContent = texts.monthly;
        document.querySelectorAll('.chart-tab')[3].textContent = texts.quarterly;

        // Update table headers
        const tableHeaders = document.querySelectorAll('.projects-table th');
        tableHeaders[0].textContent = texts.project;
        tableHeaders[1].textContent = texts.budget;
        tableHeaders[2].textContent = texts.location;
        tableHeaders[3].textContent = texts.status;
        tableHeaders[4].textContent = texts.timeline;
        tableHeaders[5].textContent = texts.action;

        // Update status badges
        document.querySelectorAll('.status-pending').forEach(badge => {
            badge.textContent = texts.pending;
        });
        document.querySelectorAll('.status-active').forEach(badge => {
            badge.textContent = texts.active;
        });
        document.querySelectorAll('.status-completed').forEach(badge => {
            badge.textContent = texts.completed;
        });

        // Update settings panel
        document.querySelector('.settings-title').textContent = texts.dashboardSettings;
        document.querySelectorAll('.settings-section-title')[0].textContent = texts.themeColors;
        document.querySelectorAll('.settings-section-title')[1].textContent = texts.displayOptions;
        document.querySelectorAll('.toggle-text')[0].textContent = texts.darkMode;
        document.querySelectorAll('.toggle-text')[1].textContent = texts.compactUI;
        document.querySelectorAll('.toggle-text')[2].textContent = texts.reducedAnimations;
        document.querySelectorAll('.settings-section-title')[2].textContent = texts.textSize;
        document.querySelectorAll('.range-value span')[0].textContent = texts.small;
        document.querySelectorAll('.range-value span')[1].textContent = texts.normal;
        document.querySelectorAll('.range-value span')[2].textContent = texts.large;
        document.querySelectorAll('.settings-section-title')[3].textContent = texts.fontFamily;
        document.querySelectorAll('.settings-section-title')[4].textContent = texts.animationSpeed;
        document.querySelectorAll('.speed-option')[0].textContent = texts.slow;
        document.querySelectorAll('.speed-option')[1].textContent = texts.normal;
        document.querySelectorAll('.speed-option')[2].textContent = texts.fast;
        document.querySelectorAll('.settings-section-title')[5].textContent = texts.language;
        document.querySelector('.reset-button').textContent = texts.resetAllSettings;

        // Update chart labels
        if (projectsChart && budgetChart && engagementChart) {
            // Projects Chart
            projectsChart.options.plugins.tooltip.callbacks.label = function(context) {
                return `${texts.totalProjects}: ${context.raw}`;
            };
            projectsChart.update();

            // Update engagement chart labels
            engagementChart.data.datasets[0].label = texts.totalProjects;
            engagementChart.data.datasets[1].label = `${texts.budget} (€)`;
            engagementChart.data.datasets[2].label = texts.teamMembers;
            engagementChart.update();
        }

        // RTL support for Arabic
        if (lang === 'ar') {
            document.body.setAttribute('dir', 'rtl');
            document.querySelectorAll('.chart-header, .table-header, .header-actions').forEach(el => {
                el.style.flexDirection = 'row-reverse';
            });
        } else {
            document.body.setAttribute('dir', 'ltr');
            document.querySelectorAll('.chart-header, .table-header, .header-actions').forEach(el => {
                el.style.flexDirection = 'row';
            });
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // Add Contributor Map functionality
    // Add Contributor Map functionality with Google Maps
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Google Map
    initGoogleMap();
    
    // Add map/list view toggle functionality
    document.querySelectorAll('.map-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.map-tab').forEach(t => {
                t.classList.remove('active');
            });
            this.classList.add('active');
            
            const view = this.dataset.view;
            if (view === 'map') {
                document.getElementById('contributor-map').style.display = 'block';
                document.getElementById('contributor-list').style.display = 'none';
            } else {
                document.getElementById('contributor-map').style.display = 'none';
                document.getElementById('contributor-list').style.display = 'block';
            }
        });
    });
});

// Initialize Google Map
function initGoogleMap() {
    // Create a script element to load Google Maps API
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyBAnDX1VtLnpPgRgfIIXDlfi7Yxqbjq-TI&callback=initMap`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
    
    // Define the initMap callback function globally
    window.initMap = function() {
        // Default center (Paris)
        const map = new google.maps.Map(document.getElementById('contributor-map'), {
            center: { lat: 48.8566, lng: 2.3522 },
            zoom: 5,
            styles: [
                {
                    "featureType": "administrative",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#444444"
                        }
                    ]
                },
                {
                    "featureType": "landscape",
                    "elementType": "all",
                    "stylers": [
                        {
                            "color": "#f2f2f2"
                        }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "all",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "all",
                    "stylers": [
                        {
                            "saturation": -100
                        },
                        {
                            "lightness": 45
                        }
                    ]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "all",
                    "stylers": [
                        {
                            "visibility": "simplified"
                        }
                    ]
                },
                {
                    "featureType": "road.arterial",
                    "elementType": "labels.icon",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "transit",
                    "elementType": "all",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "all",
                    "stylers": [
                        {
                            "color": "#d6e9ff"
                        },
                        {
                            "visibility": "on"
                        }
                    ]
                }
            ]
        });
        
        // Store the map object globally for later use
        window.projectMap = map;
        
        // Load contributor and project data
        loadContributorData(map);
        loadProjectLocations(map);
    };
}

// Function to load project locations from your database
function loadProjectLocations(map) {
    // Send AJAX request to get project locations
    fetch('get_project_locations.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayProjectsOnMap(map, data.data);
        } else {
            console.error('Error loading project data:', data.message);
            // Add demo project data if we can't load real data
            addDemoProjects(map);
        }
    })
    .catch(error => {
        console.error('Error fetching project data:', error);
        // Add demo project data if fetch fails
        addDemoProjects(map);
    });
}

// Function to display projects on the map
function displayProjectsOnMap(map, projects) {
    if (!projects || projects.length === 0) {
        addDemoProjects(map);
        return;
    }
    
    const bounds = new google.maps.LatLngBounds();
    const infoWindow = new google.maps.InfoWindow();
    
    projects.forEach(project => {
        if (project.latitude && project.longitude) {
            // Determine marker color based on project category
            const markerColor = getProjectMarkerColor(project.category);
            
            // Create marker
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(project.latitude), lng: parseFloat(project.longitude) },
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: markerColor,
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                    scale: 8
                },
                title: project.name
            });
            
            // Create content for info window
            const content = `
                <div class="map-info-window">
                    <h3>${project.name}</h3>
                    <p><strong>Category:</strong> ${project.category}</p>
                    <p><strong>Status:</strong> <span class="status-${project.status.toLowerCase()}">${project.status}</span></p>
                    <p><strong>Budget:</strong> ${new Intl.NumberFormat('fr-FR', {
                        style: 'currency',
                        currency: 'EUR'
                    }).format(project.budget)}</p>
                    ${project.description ? `<p>${project.description}</p>` : ''}
                    <a href="project_details.php?id=${project.id}" class="view-project-btn">View Project</a>
                </div>
            `;
            
            // Add click listener to show info window
            marker.addListener('click', () => {
                infoWindow.setContent(content);
                infoWindow.open(map, marker);
            });
            
            // Extend bounds to include this marker
            bounds.extend(marker.getPosition());
        }
    });
    
    // Fit map to bounds if there are any markers
    if (!bounds.isEmpty()) {
        map.fitBounds(bounds);
    }
}

// Function to determine marker color based on project category
function getProjectMarkerColor(category) {
    if (!category) return '#6c63ff'; // Default color
    
    category = category.toLowerCase();
    
    if (category.includes('residential')) {
        return '#00b8a9'; // Teal
    } else if (category.includes('commercial')) {
        return '#ff6b6b'; // Red
    } else if (category.includes('infrastructure')) {
        return '#ffd166'; // Yellow
    } else if (category.includes('green')) {
        return '#4CAF50'; // Green
    } else if (category.includes('public')) {
        return '#9c27b0'; // Purple
    } else {
        return '#6c63ff'; // Default
    }
}

// Function to add demo projects (fallback)
function addDemoProjects(map) {
    const demoProjects = getDemoProjects();
    displayProjectsOnMap(map, demoProjects);
}

// Function to get demo projects data
function getDemoProjects() {
    return [
        {
            id: 1,
            name: 'Urban Garden Project',
            category: 'Green Space',
            status: 'Active',
            budget: 1250000,
            description: 'Development of urban gardens in the city center',
            latitude: 48.8566, 
            longitude: 2.3522
        },
        {
            id: 2,
            name: 'Smart Traffic System',
            category: 'Infrastructure',
            status: 'Planning',
            budget: 3500000,
            description: 'Implementation of AI-powered traffic management',
            latitude: 45.7640, 
            longitude: 4.8357
        },
        {
            id: 3,
            name: 'Riverside Apartments',
            category: 'Residential',
            status: 'Completed',
            budget: 4200000,
            description: 'Luxury residential complex by the river',
            latitude: 43.2965, 
            longitude: 5.3698
        },
        {
            id: 4,
            name: 'Downtown Mall',
            category: 'Commercial',
            status: 'Active',
            budget: 2800000,
            description: 'Mixed-use commercial development',
            latitude: 36.8065, 
            longitude: 10.1815
        },
        {
            id: 5,
            name: 'Public Library',
            category: 'Public',
            status: 'Planning',
            budget: 1750000,
            description: 'New central library with community spaces',
            latitude: 41.3851, 
            longitude: 2.1734
        }
    ];
}

// Modify the existing loadContributorData function to use Google Maps
function loadContributorData(map) {
    // Send AJAX request to get contributor locations
    fetch('get_contributor_locations.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayContributorsOnGoogleMap(map, data.data);
            displayContributorsList(data.data);
        } else {
            console.error('Error loading contributor data:', data.message);
            // Add demo data if we can't load real data
            addDemoContributors(map);
        }
    })
    .catch(error => {
        console.error('Error fetching contributor data:', error);
        // Add demo data if fetch fails
        addDemoContributors(map);
    });
}

// Function to display contributors on Google Maps
function displayContributorsOnGoogleMap(map, contributors) {
    if (!contributors || contributors.length === 0) {
        addDemoContributors(map);
        return;
    }
    
    const bounds = new google.maps.LatLngBounds();
    const infoWindow = new google.maps.InfoWindow();
    
    contributors.forEach(contributor => {
        if (contributor.latitude && contributor.longitude) {
            // Determine marker color based on contribution type
            const markerColor = getMarkerColor(contributor.contribution_type);
            
            // Create marker
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(contributor.latitude), lng: parseFloat(contributor.longitude) },
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: markerColor,
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                    scale: 8
                },
                title: `${contributor.first_name} ${contributor.last_name}`
            });
            
            // Create content for info window
            const content = `
                <div class="map-info-window">
                    <h3>${contributor.first_name} ${contributor.last_name}</h3>
                    <p><strong>Role:</strong> ${contributor.contribution_type || 'Contributor'}</p>
                    <p><strong>Location:</strong> ${contributor.city || 'Unknown'}</p>
                    ${contributor.project_name ? `<p><strong>Project:</strong> ${contributor.project_name}</p>` : ''}
                </div>
            `;
            
            // Add click listener to show info window
            marker.addListener('click', () => {
                infoWindow.setContent(content);
                infoWindow.open(map, marker);
            });
            
            // Extend bounds to include this marker
            bounds.extend(marker.getPosition());
        }
    });
    
    // Fit map to bounds if there are any markers
    if (!bounds.isEmpty()) {
        map.fitBounds(bounds);
    }
}
    </script>
</body>
</html>