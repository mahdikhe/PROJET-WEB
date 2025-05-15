<?php
require_once(__DIR__ . '/../../config/Database.php');

// Initialize database connection
$database = Database::getInstance();
$conn = $database->getConnection();

// Fetch all projects with their dates
try {
    $query = "SELECT id, projectName, startDate, endDate FROM projects ORDER BY startDate";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching projects: " . $e->getMessage();
    $projects = [];
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
    <link rel="stylesheet" href="../../assets/css/style1.css" />
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #4cc9f0;
            --dark: #212529;
            --light: #f8f9fa;
            --border: #dee2e6;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --info: #3498db;
            --purple: #9b59b6;
            --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f6f9fc 0%, #e9ecef 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .calendar-view {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .calendar-view:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.15);
        }

        .calendar-header {
            background: var(--gradient-primary);
            margin: -2rem -2rem 2rem -2rem;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .calendar-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            letter-spacing: 0.5px;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem 0;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
        }

        .calendar-weekdays div {
            font-weight: 600;
            color: var(--primary);
            text-align: center;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1.5px;
            position: relative;
            padding: 0.5rem;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1rem;
            animation: fadeInCalendar 0.6s ease-out;
        }

        .calendar-day {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            min-height: 140px;
            border: 1px solid rgba(67, 97, 238, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .calendar-day:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 24px rgba(67, 97, 238, 0.15);
            border-color: var(--primary);
        }

        .day-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            position: relative;
        }

        .calendar-day.today {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(76, 201, 240, 0.1) 100%);
            border: 2px solid var(--primary);
        }

        .calendar-day.today .day-number::after {
            content: 'Today';
            font-size: 0.7rem;
            background: var(--primary);
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            position: absolute;
            right: -10px;
            top: -5px;
        }

        .project-event {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .project-event::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .project-event:hover::before {
            opacity: 1;
        }

        .project-event.completed {
            background: linear-gradient(135deg, var(--success) 0%, #27ae60 100%);
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
        }

        .project-event.in-progress {
            background: linear-gradient(135deg, var(--info) 0%, #2980b9 100%);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }

        .project-event.upcoming {
            background: linear-gradient(135deg, var(--purple) 0%, #8e44ad 100%);
            box-shadow: 0 4px 12px rgba(155, 89, 182, 0.2);
        }

        .project-tooltip {
            background: white;
            border-radius: 15px;
            padding: 1.25rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            border: 1px solid rgba(67, 97, 238, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .project-tooltip h3 {
            color: var(--primary);
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
            padding-bottom: 0.5rem;
        }

        .project-tooltip p {
            margin: 0.5rem 0;
            color: var(--dark);
            font-size: 0.9rem;
        }

        @keyframes fadeInCalendar {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulseToday {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .calendar-day.today {
            animation: pulseToday 2s infinite;
        }

        .events-container {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-height: calc(100% - 2rem);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) transparent;
        }

        .events-container::-webkit-scrollbar {
            width: 6px;
        }

        .events-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .events-container::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 20px;
        }

        .back-button {
    position: fixed;               /* Keep it in view even on scroll */
    top: 180px;                     /* Distance from top */
    right: 20px;                    /* Distance from left */
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: var(--primary);
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    background: var(--light);
    transition: all 0.3s ease;
    z-index: 1000;   
    font-size: 20px;              /* Ensure it's above other elements */
}


        .back-button:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
        }
    </style>
</head>
<header>
    <div class="container header-container">
      <a href="../backoffice/dashboard/dashboard/creative_dashboard.php" class="logo">
          <img src="logo.png" alt="CityPulse Logo" style="height: 35px; margin-right: 10px;">
          
      </a>
      <nav class="main-nav">
        <a href="cont.html">Post</a>
        <a href="project.html" class="active">Projects</a>
        <a href="tasks.php">Tasks</a>
        <a href="event.html">Events</a>
        <a href="forums.html">Forums</a>
        <a href="#">Offre d'emploi</a>
      </nav>
      <div class="auth-buttons">
        <a href="login.html" class="btn btn-outline">Log In</a>
        <a href="signup.html" class="btn btn-primary">Sign Up</a>
      </div>

     
  
    </div>
  

    
  </header>
<body>


    <div class="container">
        <div class="page-header">
            <a href="project.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Projects
            </a>
            <h1>Project Calendar</h1>
        </div>

        <div class="calendar-view">
            <div class="calendar-header">
                <button class="btn btn-outline" onclick="previousMonth()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 id="currentMonth">January 2024</h2>
                <button class="btn btn-outline" onclick="nextMonth()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="calendar-grid">
                <div class="calendar-weekdays">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>
                <div class="calendar-days" id="calendarDays">
                    <!-- Calendar days will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentDate = new Date();
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        // Projects data from PHP
        const projects = <?php echo json_encode($projects); ?>;

        function getProjectStatus(startDate, endDate) {
            const today = new Date();
            startDate = new Date(startDate);
            endDate = new Date(endDate);

            if (endDate < today) {
                return 'completed';
            } else if (startDate <= today && endDate >= today) {
                return 'in-progress';
            } else {
                return 'upcoming';
            }
        }

        function updateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            document.getElementById('currentMonth').textContent = `${months[month]} ${year}`;
            
            const firstDay = new Date(year, month, 1);
            const startingDay = firstDay.getDay();
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const prevLastDay = new Date(year, month, 0);
            const daysInPrevMonth = prevLastDay.getDate();
            
            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';
            
            // Previous month days
            for (let i = startingDay - 1; i >= 0; i--) {
                const day = document.createElement('div');
                day.className = 'calendar-day other-month';
                day.innerHTML = `<div class="day-number">${daysInPrevMonth - i}</div>`;
                calendarDays.appendChild(day);
            }
            
            // Current month days
            const today = new Date();
            for (let i = 1; i <= daysInMonth; i++) {
                const day = document.createElement('div');
                day.className = 'calendar-day';
                
                if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                    day.classList.add('today');
                }
                
                day.innerHTML = `<div class="day-number">${i}</div>`;
                const eventsContainer = document.createElement('div');
                eventsContainer.className = 'events-container';
                
                // Add projects for this day
                const currentDate = new Date(year, month, i);
                let projectCount = 0;
                
                projects.forEach(project => {
                    const startDate = new Date(project.startDate);
                    const endDate = new Date(project.endDate);
                    
                    if (currentDate >= startDate && currentDate <= endDate) {
                        projectCount++;
                        const projectLink = document.createElement('a');
                        projectLink.href = `createProject/project-details.php?id=${project.id}`;
                        projectLink.className = `project-event ${getProjectStatus(project.startDate, project.endDate)}`;
                        projectLink.textContent = project.projectName;
                        
                        // Create tooltip
                        const tooltip = document.createElement('div');
                        tooltip.className = 'project-tooltip';
                        tooltip.innerHTML = `
                            <h3>${project.projectName}</h3>
                            <p>Start Date: ${startDate.toLocaleDateString()}</p>
                            <p>End Date: ${endDate.toLocaleDateString()}</p>
                        `;
                        
                        document.body.appendChild(tooltip);
                        
                        // Show tooltip on hover with position calculation
                        projectLink.addEventListener('mouseenter', (e) => {
                            tooltip.style.visibility = 'visible';
                            
                            // Calculate position
                            const rect = e.target.getBoundingClientRect();
                            const tooltipHeight = tooltip.offsetHeight;
                            const tooltipWidth = tooltip.offsetWidth;
                            
                            // Check if tooltip would go off screen to the right
                            let left = rect.right + 10;
                            if (left + tooltipWidth > window.innerWidth) {
                                left = rect.left - tooltipWidth - 10;
                            }
                            
                            // Check if tooltip would go off screen at the bottom
                            let top = rect.top;
                            if (top + tooltipHeight > window.innerHeight) {
                                top = window.innerHeight - tooltipHeight - 10;
                            }
                            
                            tooltip.style.left = `${left}px`;
                            tooltip.style.top = `${top}px`;
                        });
                        
                        projectLink.addEventListener('mouseleave', () => {
                            tooltip.style.visibility = 'hidden';
                        });
                        
                        // Update tooltip position on mouse move
                        projectLink.addEventListener('mousemove', (e) => {
                            const rect = e.target.getBoundingClientRect();
                            const tooltipHeight = tooltip.offsetHeight;
                            const tooltipWidth = tooltip.offsetWidth;
                            
                            let left = rect.right + 10;
                            if (left + tooltipWidth > window.innerWidth) {
                                left = rect.left - tooltipWidth - 10;
                            }
                            
                            let top = rect.top;
                            if (top + tooltipHeight > window.innerHeight) {
                                top = window.innerHeight - tooltipHeight - 10;
                            }
                            
                            tooltip.style.left = `${left}px`;
                            tooltip.style.top = `${top}px`;
                        });
                        
                        projectLink.addEventListener('click', (e) => {
                            e.stopPropagation();
                            window.location.href = `createProject/project-details.php?id=${project.id}`;
                        });
                        
                        eventsContainer.appendChild(projectLink);
                    }
                });
                
                // Adjust day height based on project count
                if (projectCount > 0) {
                    const baseHeight = 120; // Base height for days with projects
                    const projectHeight = 40; // Height per project
                    const newHeight = Math.max(baseHeight, baseHeight + (projectCount - 1) * projectHeight);
                    day.style.height = `${newHeight}px`;
                }
                
                day.appendChild(eventsContainer);
                calendarDays.appendChild(day);
            }
            
            // Next month days
            const remainingDays = 42 - (startingDay + daysInMonth);
            for (let i = 1; i <= remainingDays; i++) {
                const day = document.createElement('div');
                day.className = 'calendar-day other-month';
                day.innerHTML = `<div class="day-number">${i}</div>`;
                calendarDays.appendChild(day);
            }
            
            // Adjust grid container height after all days are populated
            const gridContainer = document.querySelector('.calendar-grid');
            const maxDayHeight = Math.max(...Array.from(document.querySelectorAll('.calendar-day')).map(day => day.offsetHeight));
            gridContainer.style.height = `${maxDayHeight * 6}px`; // 6 rows in the calendar
        }

        function previousMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            updateCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            updateCalendar();
        }

        // Initialize calendar
        updateCalendar();
    </script>
</body>
</html>