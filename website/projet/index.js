const steps = document.querySelectorAll(".timeline-step");
let currentStep = Array.from(steps).findIndex(step => step.classList.contains("current"));

function goToNextStep() {
  if (currentStep < 0 && steps.length > 0) {
    steps[0].classList.add("current");
    currentStep = 0;
  } else if (currentStep < steps.length - 1) {
    steps[currentStep].classList.remove("current");
    steps[currentStep].classList.add("completed");
    currentStep++;
    steps[currentStep].classList.add("current");
  } else {
    alert("Project is complete 🎉");
  }
}

// Function to load projects
function loadProjects(showAll = false) {
    const url = showAll ? 'create project/get_projects.php?all=true' : 'create project/get_projects.php';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const projectList = document.getElementById('projectList');
            const viewMoreContainer = document.getElementById('viewMoreContainer');
            
            // Clear existing projects
            projectList.innerHTML = '';
            
            // Display projects
            data.projects.forEach(project => {
                const projectCard = document.createElement('div');
                projectCard.className = 'project-card';
                
                // Create image element with error handling
                const img = document.createElement('img');
                img.className = 'project-image';
                img.alt = project.projectName;
                
                // Set the image source with proper path
                const imagePath = project.projectImage.startsWith('http') 
                    ? project.projectImage 
                    : 'create project/' + project.projectImage;
                
                img.src = imagePath;
                
                img.onerror = function() {
                    this.src = 'create project/default-project-image.jpg';
                };
                
                projectCard.innerHTML = `
                    <div class="project-image-container">
                        ${img.outerHTML}
                    </div>
                    <div class="project-info">
                        <div class="project-name">${project.projectName}</div>
                        <div class="project-location">
                            <i class="fas fa-map-marker-alt"></i> ${project.projectLocation}
                        </div>
                        <a href="create project/project-details.php?id=${project.id}" class="view-details-btn">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <div class="project-buttons">
                            <a href="contribute.html" class="contribute-btn">
                                <i class="fas fa-hand-holding-heart"></i> Contribute
                            </a>
                            <a href="tasks.php?project_id=${project.id}" class="task-btn">
                                <i class="fas fa-tasks"></i> Manage Tasks
                            </a>
                        </div>
                    </div>
                `;
                projectList.appendChild(projectCard);
            });
            
            // Show View More button if there are more than 2 projects and we're not showing all
            if (!showAll && data.totalProjects > 2) {
                viewMoreContainer.style.display = 'block';
                viewMoreContainer.innerHTML = `
                    <button class="view-more-btn" onclick="loadProjects(true)">
                        <i class="fas fa-chevron-down"></i> View All Projects (${data.totalProjects})
                    </button>
                `;
            } else {
                viewMoreContainer.style.display = 'none';
            }
        })
        .catch(error => console.error('Error loading projects:', error));
}

document.addEventListener('DOMContentLoaded', function() {
    // Load initial projects (first 2)
    loadProjects(false);
    
    // Refresh projects every 30 seconds
    setInterval(() => loadProjects(false), 30000);
});