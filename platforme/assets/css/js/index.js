// Inject enhanced styles dynamically
const style = document.createElement('style');
style.textContent = `
  .project-stats {
    margin: 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .contributor-count {
    background: linear-gradient(135deg, #f0f4f8 0%, #e3ebf3 100%);
    padding: 8px 14px;
    border-radius: 24px;
    font-size: 0.85rem;
    color: #2d3748;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(203, 213, 225, 0.3);
    position: relative;
    overflow: hidden;
  }

  .contributor-count::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(66, 153, 225, 0.1) 0%, rgba(66, 153, 225, 0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .contributor-count:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    color: #1a365d;
  }

  .contributor-count:hover::before {
    opacity: 1;
  }

  .contributor-count i {
    color: #4299e1;
    font-size: 0.9rem;
    transition: transform 0.3s ease;
  }

  .contributor-count:hover i {
    transform: scale(1.1);
  }

  .count-number {
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    display: inline-block;
  }

  @keyframes floatPulse {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-3px) scale(1.05); }
  }

  @keyframes countIncrement {
    0% { 
      transform: translateY(0) scale(1);
      opacity: 1;
    }
    50% {
      transform: translateY(-10px) scale(1.2);
      opacity: 0.8;
    }
    100% {
      transform: translateY(0) scale(1);
      opacity: 1;
    }
  }

  @keyframes ripple {
    0% {
      transform: scale(0.8);
      opacity: 0.6;
    }
    100% {
      transform: scale(1.4);
      opacity: 0;
    }
  }

  .count-updated {
    animation: countIncrement 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    color: #2b6cb0;
  }

  .count-updated::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 30px;
    height: 30px;
    background: rgba(66, 153, 225, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    animation: ripple 0.6s ease-out forwards;
  }

  .contributor-count.pulse {
    animation: floatPulse 2s ease-in-out infinite;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .contributor-count {
      padding: 6px 12px;
      font-size: 0.8rem;
    }
  }

  .share-btn {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    color: #0369a1;
    border: none;
    padding: 8px 15px;
    border-radius: 24px;
    cursor: pointer;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    position: relative;
    overflow: hidden;
}

.share-btn:hover {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.share-btn i {
    transition: transform 0.3s ease;
}

.share-btn:hover i {
    transform: rotate(15deg) scale(1.1);
}

.share-btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(2, 132, 199, 0.4);
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    transition: transform 0.6s ease-out, opacity 0.6s ease-out;
    opacity: 0;
}

.share-btn:active::after {
    transform: translate(-50%, -50%) scale(15);
    opacity: 1;
}

/* Share modal styles */
.share-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.share-modal.active {
    opacity: 1;
    visibility: visible;
}

.share-modal-content {
    background: white;
    padding: 25px;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.share-modal.active .share-modal-content {
    transform: translateY(0);
}

.share-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.share-modal-title {
    font-size: 1.2rem;
    color: #1e293b;
    margin: 0;
}

.share-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
    transition: color 0.2s ease;
}

.share-modal-close:hover {
    color: #475569;
}

.share-options {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 20px;
}

.share-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.share-option:hover {
    background: #f1f5f9;
    transform: translateY(-3px);
}

.share-option i {
    font-size: 1.8rem;
    margin-bottom: 5px;
}

.share-option.facebook i {
    color: #1877f2;
}

.share-option.twitter i {
    color: #1da1f2;
}

.share-option.whatsapp i {
    color: #25d366;
}

.share-option.link i {
    color: #64748b;
}

.share-option-label {
    font-size: 0.75rem;
    color: #475569;
}

.share-link-container {
    display: flex;
    margin-top: 15px;
}

.share-link-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px 0 0 6px;
    font-size: 0.9rem;
}

.share-link-copy {
    background: #0ea5e9;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 0 6px 6px 0;
    cursor: pointer;
    transition: background 0.2s ease;
}

.share-link-copy:hover {
    background: #0284c7;
}

@media (max-width: 480px) {
    .share-options {
        grid-template-columns: repeat(2, 1fr);
    }
}
`;
document.head.appendChild(style);







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
    const url = showAll ? 'createProject/get_projects.php?all=true' : 'createProject/get_projects.php';
    
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
                    : 'createProject/' + project.projectImage;
                
                img.src = imagePath;
                
                img.onerror = function() {
                    this.src = 'createProject/default-project-image.jpg';
                };
                
                // In your loadProjects() function
                projectCard.innerHTML = `
                <div class="project-image-container">
                    ${img.outerHTML}
                </div>
                <div class="project-info">
                    <div class="project-name">${project.projectName}</div>
                    <div class="project-location">
                        <i class="fas fa-map-marker-alt"></i> ${project.projectLocation}
                    </div>
                    <div class="project-stats">
                        <span class="contributor-count">
                            <i class="fas fa-users"></i> ${project.contributor_count || 0} Contributors
                        </span>
                    </div>
                    <a href="../../views/frontoffice/createProject/project-details.php?id=${project.id}" class="view-details-btn">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    <div class="project-buttons">
                        <a href="contribute.html?project_id=${project.id}" class="contribute-btn">
                            <i class="fas fa-hand-holding-heart"></i> Contribute
                        </a>
                        <a href="tasks.php?project_id=${project.id}" class="task-btn">
                            <i class="fas fa-tasks"></i> Manage Tasks
                        </a>
                        <button class="share-btn" data-project-id="${project.id}" data-project-name="${project.projectName}">
                            <i class="fas fa-share-alt"></i>Share
                        </button>
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



// Time spent tracking - session-based version
let timeSpentSeconds = 0;
let timeSpentInterval;

function updateTimeSpent() {
  timeSpentSeconds++;
  const hours = Math.floor(timeSpentSeconds / 3600);
  const minutes = Math.floor((timeSpentSeconds % 3600) / 60);
  const seconds = timeSpentSeconds % 60;
  
  document.getElementById('timeDisplay').textContent = 
    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
  
  // Save to sessionStorage on each update
  sessionStorage.setItem('sessionTimeSpent', timeSpentSeconds);
}

// Initialize on page load
window.onload = function() {
  // Get time from sessionStorage if exists (will be null on new tab/window)
  const savedTime = sessionStorage.getItem('sessionTimeSpent');
  timeSpentSeconds = savedTime ? parseInt(savedTime) : 0;
  
  document.getElementById('timeDisplay').textContent = 
    `${Math.floor(timeSpentSeconds / 3600).toString().padStart(2, '0')}:` +
    `${Math.floor((timeSpentSeconds % 3600) / 60).toString().padStart(2, '0')}:` +
    `${(timeSpentSeconds % 60).toString().padStart(2, '0')}`;
  
  timeSpentInterval = setInterval(updateTimeSpent, 1000);
};

// Clean up
window.addEventListener('beforeunload', function() {
  clearInterval(timeSpentInterval);
});





// Share functionality
// Share functionality
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('share-btn') || e.target.closest('.share-btn')) {
        e.preventDefault();
        const shareBtn = e.target.classList.contains('share-btn') ? e.target : e.target.closest('.share-btn');
        const projectId = shareBtn.dataset.projectId;
        const projectName = shareBtn.dataset.projectName;
        
        showShareModal(projectId, projectName);
    }
    
    // Handle share option clicks
    if (e.target.closest('.share-option')) {
        e.preventDefault();
        const option = e.target.closest('.share-option');
        const platform = option.getAttribute('data-share');
        const projectUrl = document.querySelector('.share-link-input').value;
        const projectName = option.closest('.share-modal-content').querySelector('.share-modal-title').textContent.replace('Partager ce projet ', '');
        const shareText = `Je soutiens le projet "${projectName}" sur EcoFund. Rejoignez-moi! ${projectUrl}`;

        switch(platform) {
            case 'facebook':
                shareOnFacebook(shareText);
                break;
            case 'twitter':
                shareOnTwitter(shareText);
                break;
            case 'whatsapp':
                shareOnWhatsApp(shareText);
                break;
            case 'link':
                copyProjectLink(projectUrl);
                break;
        }
    }
    
    // Close modal when clicking close button or outside
    if (e.target.classList.contains('share-modal-close') || 
        e.target.classList.contains('share-modal')) {
        hideShareModal();
    }
    
    // Copy link functionality
    if (e.target.classList.contains('share-link-copy')) {
        e.preventDefault();
        const input = document.querySelector('.share-link-input');
        input.select();
        document.execCommand('copy');
        
        // Show feedback
        const copyBtn = e.target;
        copyBtn.textContent = 'Copié!';
        setTimeout(() => {
            copyBtn.textContent = 'Copier';
        }, 2000);
    }
});

function showShareModal(projectId, projectName) {
    const modal = document.createElement('div');
    modal.className = 'share-modal active';
    
    const projectUrl = `${window.location.origin}/views/frontoffice/createProject/project-details.php?id=${projectId}`;
    
    modal.innerHTML = `
        <div class="share-modal-content">
            <div class="share-modal-header">
                <h3 class="share-modal-title">Partager ce projet</h3>
                <button class="share-modal-close">&times;</button>
            </div>
            
            <div class="share-options">
                <div class="share-option facebook" data-share="facebook">
                    <i class="fab fa-facebook-f"></i>
                    <span class="share-option-label">Facebook</span>
                </div>
                
                <div class="share-option twitter" data-share="twitter">
                    <i class="fab fa-twitter"></i>
                    <span class="share-option-label">Twitter</span>
                </div>
                
                <div class="share-option whatsapp" data-share="whatsapp">
                    <i class="fab fa-whatsapp"></i>
                    <span class="share-option-label">WhatsApp</span>
                </div>
                
                <div class="share-option link" data-share="link">
                    <i class="fas fa-link"></i>
                    <span class="share-option-label">Lien</span>
                </div>
            </div>
            
            <div class="share-link-container">
                <input type="text" class="share-link-input" value="${projectUrl}" readonly>
                <button class="share-link-copy">Copier</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
}

// Share functions
function shareOnFacebook(message) {
    const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(message.split(' ').pop())}&quote=${encodeURIComponent(message)}`;
    window.open(url, '_blank', 'width=600,height=400');
}

function shareOnTwitter(message) {
    const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank', 'width=600,height=400');
}

function shareOnWhatsApp(message) {
    const url = `https://wa.me/?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank', 'width=600,height=600');
}

function copyProjectLink(link) {
    const input = document.createElement('input');
    input.value = link;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    
    // Show feedback
    const linkOption = document.querySelector('.share-option.link');
    if (linkOption) {
        const originalLabel = linkOption.querySelector('.share-option-label');
        originalLabel.textContent = 'Lien copié!';
        setTimeout(() => {
            originalLabel.textContent = 'Lien';
        }, 2000);
    }
}

function hideShareModal() {
    const modal = document.querySelector('.share-modal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = '';
        }, 300);
    }
}