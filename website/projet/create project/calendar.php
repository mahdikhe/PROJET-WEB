<?php
include('db.php');
session_start();

// Get current month and year
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month and year
if ($month < 1 || $month > 12) $month = date('n');
if ($year < 2000 || $year > 2100) $year = date('Y');

// Get first day of the month
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$numberDays = date('t', $firstDay);
$dateComponents = getdate($firstDay);
$monthName = $dateComponents['month'];
$dayOfWeek = $dateComponents['wday'];

// Get projects for the current month
$startDate = date('Y-m-01', $firstDay);
$endDate = date('Y-m-t', $firstDay);

$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM project_supporters WHERE project_id = p.id) AS supporters_count,
          EXISTS(SELECT 1 FROM project_supporters ps WHERE ps.project_id = p.id AND ps.user_id = :userId) AS is_supported
          FROM projects p 
          WHERE p.startDate BETWEEN :startDate AND :endDate
          ORDER BY p.startDate ASC";

try {
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':userId', $_SESSION['user_id'] ?? 0, PDO::PARAM_INT);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching projects: " . $e->getMessage();
    $projects = [];
}

// Group projects by day
$projectsByDay = [];
foreach ($projects as $project) {
    $day = date('j', strtotime($project['startDate']));
    if (!isset($projectsByDay[$day])) {
        $projectsByDay[$day] = [];
    }
    $projectsByDay[$day][] = $project;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Calendar - CityPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #4cc9f0;
            --dark: #212529;
            --light: #f8f9fa;
            --border: #dee2e6;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .calendar-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin: 0;
        }
        
        .calendar-nav {
            display: flex;
            gap: 1rem;
        }
        
        .calendar-nav button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .calendar-nav button:hover {
            background: var(--secondary);
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1rem;
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .calendar-day-header {
            text-align: center;
            font-weight: 600;
            padding: 0.5rem;
            color: var(--primary);
        }
        
        .calendar-day {
            min-height: 120px;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
        }
        
        .calendar-day.empty {
            background: #f8f9fa;
        }
        
        .day-number {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .project-event {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.25rem;
            font-size: 0.85rem;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .project-event:hover {
            transform: translateY(-2px);
        }
        
        .project-event.completed {
            background: #28a745;
        }
        
        .project-event.ongoing {
            background: #ffc107;
            color: var(--dark);
        }
        
        .project-event.upcoming {
            background: #dc3545;
        }
        
        .month-selector {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .month-selector select {
            padding: 0.5rem;
            border-radius: 8px;
            border: 1px solid var(--border);
        }
        
        .today {
            background: #e3f2fd !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="calendar-header">
            <h1 class="calendar-title">Project Calendar</h1>
            <div class="calendar-nav">
                <a href="?month=<?= $month-1 ?>&year=<?= $year ?>" class="button">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
                <div class="month-selector">
                    <select onchange="window.location.href='?month=' + this.value + '&year=<?= $year ?>'">
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select onchange="window.location.href='?month=<?= $month ?>&year=' + this.value">
                        <?php for($y = 2020; $y <= 2030; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <a href="?month=<?= $month+1 ?>&year=<?= $year ?>" class="button">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
                <a href="?month=<?= date('n') ?>&year=<?= date('Y') ?>" class="button">
                    Today
                </a>
            </div>
        </div>
        
        <div class="calendar-grid">
            <?php
            // Display day names
            $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            foreach ($dayNames as $dayName) {
                echo "<div class='calendar-day-header'>$dayName</div>";
            }
            
            // Display empty cells for days before the first day of the month
            for ($i = 0; $i < $dayOfWeek; $i++) {
                echo "<div class='calendar-day empty'></div>";
            }
            
            // Display days of the month
            for ($day = 1; $day <= $numberDays; $day++) {
                $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                echo "<div class='calendar-day" . ($isToday ? " today" : "") . "'>";
                echo "<div class='day-number'>$day</div>";
                
                if (isset($projectsByDay[$day])) {
                    foreach ($projectsByDay[$day] as $project) {
                        $status = 'upcoming';
                        $startDate = strtotime($project['startDate']);
                        $endDate = strtotime($project['endDate'] ?? 'now');
                        $now = time();
                        
                        if ($now > $endDate) {
                            $status = 'completed';
                        } else if ($now >= $startDate && $now <= $endDate) {
                            $status = 'ongoing';
                        }
                        
                        echo "<div class='project-event $status' onclick=\"window.location.href='project-details.php?id={$project['id']}'\">";
                        echo htmlspecialchars($project['projectName']);
                        echo "</div>";
                    }
                }
                
                echo "</div>";
            }
            ?>
        </div>
    </div>
</body>
</html> 