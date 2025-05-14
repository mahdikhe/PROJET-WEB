<?php
session_start();
$pageTitle = "Welcome to CityPulse";

// Include database connection
require_once 'user/config.php';

// Get upcoming events
try {
    $pdo = Config::getConnexion();
    $stmt = $pdo->query("
        SELECT * FROM events 
        WHERE start_date >= CURDATE() 
        ORDER BY start_date ASC 
        LIMIT 4
    ");
    $upcomingEvents = $stmt->fetchAll();
} catch (Exception $e) {
    $upcomingEvents = [];
}

// Get user stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events");
    $eventCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
    $reservationCount = $stmt->fetch()['count'];
} catch (Exception $e) {
    $userCount = 0;
    $eventCount = 0;
    $reservationCount = 0;
}

// Include header
include_once 'user/views/includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="/test1/user/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #00b8a9;
            --primary-light: #7fdfd6;
            --secondary: #6c63ff;
            --secondary-light: #9c96ff;
            --accent: #d295ff;
            --text-dark: #1d1d3d;
            --text-medium: #505565;
            --text-light: #8a8c99;
            --white: #ffffff;
            --light-bg: #f5f7fa;
            --border-color: #e0e3e8;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: var(--text-dark);
            background: var(--light-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        main {
            flex: 1;
            width: 100%;
        }
        .hero-section {
            background: var(--white);
            padding: 3.5rem 0 2.5rem 0;
        }
        .hero-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2.5rem;
        }
        .hero-content {
            flex: 1;
        }
        .hero-badge {
            display: inline-block;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 1rem;
            padding: 0.3rem 1.1rem;
            margin-bottom: 1.2rem;
        }
        .hero-title {
            font-size: 2.7rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.1rem;
        }
        .hero-title .accent {
            color: var(--primary);
        }
        .hero-desc {
            color: var(--text-medium);
            font-size: 1.15rem;
            margin-bottom: 2rem;
            max-width: 500px;
        }
        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .btn, .btn-outline {
            padding: 0.5rem 1.2rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .btn {
            background: var(--primary);
            color: #fff;
            border: none;
        }
        .btn:hover {
            background: var(--primary-light);
        }
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        .btn-outline:hover {
            background: var(--primary);
            color: #fff;
        }
        .hero-visual {
            flex: 1;
            display: flex;
            justify-content: flex-end;
        }
        .decorative-grid {
            display: grid;
            grid-template-columns: repeat(5, 38px);
            grid-template-rows: repeat(4, 38px);
            gap: 12px;
        }
        .decorative-square {
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary) 60%, var(--secondary) 100%);
            opacity: 0.85;
            transition: transform 0.2s;
        }
        .decorative-square.alt {
            background: linear-gradient(135deg, var(--secondary) 60%, var(--primary) 100%);
        }
        .decorative-square.light {
            background: var(--primary-light);
        }
        .core-features-section {
            background: var(--white);
            padding: 3rem 0 2.5rem 0;
            text-align: center;
        }
        .core-features-title {
            font-size: 2.1rem;
            font-weight: 800;
            margin-bottom: 0.7rem;
        }
        .core-features-desc {
            color: var(--text-medium);
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2.2rem;
            max-width: 950px;
            margin: 0 auto;
        }
        .feature-card {
            background: var(--light-bg);
            border-radius: 1.1rem;
            padding: 2.2rem 1.2rem 1.5rem 1.2rem;
            box-shadow: 0 2px 8px 0 rgba(36, 60, 120, 0.06);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .feature-icon {
            font-size: 2.2rem;
            margin-bottom: 1.1rem;
        }
        .feature-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .feature-desc {
            color: var(--text-medium);
            font-size: 1rem;
        }
        @media (max-width: 900px) {
            .hero-flex { flex-direction: column; text-align: center; }
            .hero-visual { justify-content: center; margin-top: 2rem; }
        }
    </style>
</head>
<body>
<main>
    <section class="hero-section">
        <div class="container hero-flex">
            <div class="hero-content">
                <span class="hero-badge">The Urban Pulse of Tomorrow</span>
                <div class="hero-title">Shape the Future <br> <span class="accent">of Our Cities</span></div>
                <div class="hero-desc">Connect with urban planners, architects, and citizens to collaborate on innovative projects that transform our urban landscapes and create more sustainable, livable communities.</div>
                <div class="hero-buttons">
                    <a href="#" class="btn">Join the Community</a>
                    <a href="#" class="btn-outline">Explore Projects</a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="decorative-grid">
                    <div class="decorative-square"></div>
                    <div class="decorative-square alt"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square light"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square alt"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square alt"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square light"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square alt"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square alt"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square light"></div>
                    <div class="decorative-square"></div>
                    <div class="decorative-square alt"></div>
                </div>
            </div>
        </div>
    </section>
    <section class="core-features-section">
        <div class="container">
            <div class="core-features-title">Core Features</div>
            <div class="core-features-desc">Everything you need to collaborate on urban projects and connect with like-minded individuals</div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon" style="color:var(--primary);"><i class="fas fa-project-diagram"></i></div>
                    <div class="feature-title">Project Management</div>
                    <div class="feature-desc">Collaborate on urban projects with visual progress tracking and resource management tools.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="color:var(--secondary);"><i class="fas fa-calendar-alt"></i></div>
                    <div class="feature-title">Event Management</div>
                    <div class="feature-desc">Organize and discover online and offline events with RSVP functionality and reminders.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="color:var(--primary);"><i class="fas fa-comments"></i></div>
                    <div class="feature-title">Forum Management</div>
                    <div class="feature-desc">Engage in specialized discussion boards with threaded conversations and powerful search.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="color:var(--accent);"><i class="fas fa-users"></i></div>
                    <div class="feature-title">Group Management</div>
                    <div class="feature-desc">Create and join groups based on interests, projects, or locations to foster community.</div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php include_once 'user/views/includes/footer.php'; ?>
</body>
</html> 