<?php
include('create project/db.php'); // Include your database connection file

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
        }
        
        *{
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        }
        
        .container {
            width:100%;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .calendar-view {
            position: relative;
            z-index: 1;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin: 2rem 0;
            transition: transform 0.3s ease;
            width:auto;
        }

        .calendar-view:hover {
            transform: translateY(-5px);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 8px;
            color: white;
        }

        .calendar-header h2 {
            margin: 0;
            font-size: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.75rem;
            width: 100%;
            height: auto;
            min-height: 600px;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 600;
            margin-bottom: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .calendar-weekdays div {
            padding: 0.5rem;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }

        .calendar-weekdays div::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background-color: white;
            transition: width 0.3s ease;
        }

        .calendar-weekdays div:hover::after {
            width: 80%;
            
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.75rem;
            height: auto;
        }

        .calendar-day {
            position: relative;
            min-height: 120px;
            height: auto;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .events-container {
            margin-top: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-grow: 1;
            overflow-y: auto;
            max-height: calc(100% - 2.5rem);
        }

        .day-number {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
            z-index: 1;
        }

        .project-event {
            position: relative;
            padding: 8px;
            margin: 2px 4px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 2;
        }

        .project-event.completed {
            background: linear-gradient(135deg, var(--success) 0%, #27ae60 100%);
            border-left: 4px solid #27ae60;
        }

        .project-event.in-progress {
            background: linear-gradient(135deg, var(--info) 0%, #2980b9 100%);
            border-left: 4px solid #2980b9;
        }

        .project-event.upcoming {
            background: linear-gradient(135deg, var(--purple) 0%, #8e44ad 100%);
            border-left: 4px solid #8e44ad;
        }

        .project-event:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn {
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid white;
            color: white;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-2px);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .back-button {
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
        }

        .back-button:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
        }

        .project-tooltip {
            visibility: hidden;
            position: fixed;
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 250px;
            z-index: 1000;
            pointer-events: none;
        }

        .project-tooltip h3 {
            margin: 0 0 0.5rem 0;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .project-tooltip p {
            margin: 0.5rem 0;
            color: var(--dark);
            font-size: 0.9rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .calendar-day:nth-child(7n+1) { animation-delay: 0.1s; }
        .calendar-day:nth-child(7n+2) { animation-delay: 0.2s; }
        .calendar-day:nth-child(7n+3) { animation-delay: 0.3s; }
        .calendar-day:nth-child(7n+4) { animation-delay: 0.4s; }
        .calendar-day:nth-child(7n+5) { animation-delay: 0.5s; }
        .calendar-day:nth-child(7n+6) { animation-delay: 0.6s; }
        .calendar-day:nth-child(7n+7) { animation-delay: 0.7s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <a href="project.html" class="back-button">
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
                        projectLink.href = `create project/project-details.php?id=${project.id}`;
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
                            window.location.href = `create project/project-details.php?id=${project.id}`;
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