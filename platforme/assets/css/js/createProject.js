document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.project-form');
    
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Disable the submit button to prevent multiple submissions
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';
        
        // Validation du nom du projet
        const projectName = document.getElementById('projectName');
        if (!projectName.value.trim()) {
            showError(projectName, 'Le nom du projet est requis');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }
        if (projectName.value.length < 3) {
            showError(projectName, 'Le nom du projet doit contenir au moins 3 caractères');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation de la description
        const projectDescription = document.getElementById('projectDescription');
        if (!projectDescription.value.trim()) {
            showError(projectDescription, 'La description du projet est requise');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }
        if (projectDescription.value.length < 20) {
            showError(projectDescription, 'La description doit contenir au moins 20 caractères');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation des dates
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        const today = new Date().toISOString().split('T')[0];
        
        if (!startDate.value) {
            showError(startDate, 'La date de début est requise');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }
        if (startDate.value < today) {
            showError(startDate, 'La date de début ne peut pas être dans le passé');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }
        if (!endDate.value) {
            showError(endDate, 'La date de fin est requise');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }
        if (endDate.value < startDate.value) {
            showError(endDate, 'La date de fin doit être après la date de début');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation de la localisation
        const projectLocation = document.getElementById('projectLocation');
        if (!projectLocation.value.trim()) {
            showError(projectLocation, 'La localisation est requise');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation de la catégorie
        const projectCategory = document.getElementById('projectCategory');
        if (!projectCategory.value) {
            showError(projectCategory, 'Veuillez sélectionner une catégorie');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation des tags
        const projectTags = document.getElementById('projectTags');
        if (!projectTags.value.trim()) {
            showError(projectTags, 'Veuillez ajouter au moins un tag');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation du budget
        const projectBudget = document.getElementById('projectBudget');
        if (!projectBudget.value || projectBudget.value < 100) {
            showError(projectBudget, 'Le budget minimum est de 100$');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation de la taille de l'équipe
        const teamSize = document.getElementById('teamSize');
        if (!teamSize.value) {
            showError(teamSize, 'Veuillez sélectionner la taille de l\'équipe');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation des compétences requises
        const skills = document.querySelectorAll('input[name="skills[]"]');
        let hasSelectedSkill = false;
        skills.forEach(skill => {
            if (skill.checked) hasSelectedSkill = true;
        });
        if (!hasSelectedSkill) {
            showError(skills[0].parentElement, 'Veuillez sélectionner au moins une compétence requise');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation de la visibilité
        const projectVisibility = document.getElementById('projectVisibility');
        if (!projectVisibility.value) {
            showError(projectVisibility, 'Veuillez sélectionner la visibilité du projet');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation de l'image principale
        const projectImage = document.getElementById('projectImage');
        if (!projectImage.files.length) {
            showError(projectImage, 'Une image principale est requise');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }
        const file = projectImage.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            showError(projectImage, 'Format d\'image non supporté. Utilisez JPEG, PNG ou GIF');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }
        if (file.size > maxSize) {
            showError(projectImage, 'L\'image ne doit pas dépasser 5MB');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation des fichiers supplémentaires
        const additionalFiles = document.getElementById('additionalFiles');
        if (additionalFiles.files.length > 0) {
            const allowedFileTypes = ['.pdf', '.doc', '.docx', '.ppt', '.pptx'];
            const maxFileSize = 10 * 1024 * 1024; // 10MB

            for (let file of additionalFiles.files) {
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                if (!allowedFileTypes.includes(fileExtension)) {
                    showError(additionalFiles, 'Format de fichier non supporté. Utilisez PDF, DOC, DOCX, PPT ou PPTX');
                    submitButton.disabled = false;
                    submitButton.textContent = '🚀 Launch Project';
                    return;
                }
                if (file.size > maxFileSize) {
                    showError(additionalFiles, 'Les fichiers ne doivent pas dépasser 10MB chacun');
                    submitButton.disabled = false;
                    submitButton.textContent = '🚀 Launch Project';
                    return;
                }
            }
        }

        // Validation du site web (si fourni)
        const projectWebsite = document.getElementById('projectWebsite');
        if (projectWebsite.value && !isValidUrl(projectWebsite.value)) {
            showError(projectWebsite, 'Veuillez entrer une URL valide');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Validation des conditions d'utilisation
        const terms = document.querySelector('input[name="terms"]');
        if (!terms.checked) {
            showError(terms, 'Vous devez accepter les conditions d\'utilisation');
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
            return;
        }

        // Si toutes les validations passent, soumettre le formulaire
        const formData = new FormData(form);
        
        fetch('save_project.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                // Redirect to success page
                window.location.href = result.data.redirect;
            } else {
                throw new Error(result.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
            submitButton.disabled = false;
            submitButton.textContent = '🚀 Launch Project';
        });
    });
    
    // Fonction pour afficher les erreurs
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        let errorElement = formGroup.querySelector('.error-message');
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            formGroup.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        input.classList.add('error');
        
        // Supprimer l'erreur lors de la saisie
        input.addEventListener('input', function() {
            errorElement.textContent = '';
            input.classList.remove('error');
        }, { once: true });
    }

    // Fonction pour valider les URLs
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    // Validation en temps réel du nom du projet
    const projectName = document.getElementById('projectName');
    projectName.addEventListener('input', function() {
        if (this.value.length < 3 && this.value.length > 0) {
            showError(this, 'Le nom du projet doit contenir au moins 3 caractères');
        }
    });

    // Validation en temps réel de la description
    const projectDescription = document.getElementById('projectDescription');
    projectDescription.addEventListener('input', function() {
        if (this.value.length < 20 && this.value.length > 0) {
            showError(this, 'La description doit contenir au moins 20 caractères');
        }
    });

    // Validation en temps réel du budget
    const projectBudget = document.getElementById('projectBudget');
    projectBudget.addEventListener('input', function() {
        if (this.value && this.value < 100) {
            showError(this, 'Le budget minimum est de 100$');
        }
    });

    // Validation en temps réel de l'URL du site web
    const projectWebsite = document.getElementById('projectWebsite');
    projectWebsite.addEventListener('blur', function() {
        if (this.value && !isValidUrl(this.value)) {
            showError(this, 'Veuillez entrer une URL valide');
        }
    });

    // Prévisualisation de l'image
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
});