document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('project-contribution-form');
    
    // Validation des champs requis
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Validation du prénom
        const firstName = document.getElementById('firstName');
        if (!firstName.value.trim()) {
            showError(firstName, 'Le prénom est requis');
            return;
        }
        if (firstName.value.length < 2) {
            showError(firstName, 'Le prénom doit contenir au moins 2 caractères');
            return;
        }

        // Validation du nom
        const lastName = document.getElementById('lastName');
        if (!lastName.value.trim()) {
            showError(lastName, 'Le nom est requis');
            return;
        }
        if (lastName.value.length < 2) {
            showError(lastName, 'Le nom doit contenir au moins 2 caractères');
            return;
        }

        // Validation de l'email
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim()) {
            showError(email, 'L\'email est requis');
            return;
        }
        if (!emailRegex.test(email.value)) {
            showError(email, 'Veuillez entrer une adresse email valide');
            return;
        }

        // Validation du numéro de téléphone
        const phoneNumber = document.getElementById('phone-number');
        const phoneRegex = /^[0-9]{10}$/;
        if (phoneNumber.value && !phoneRegex.test(phoneNumber.value.replace(/\s/g, ''))) {
            showError(phoneNumber, 'Veuillez entrer un numéro de téléphone valide (10 chiffres)');
            return;
        }

        // Validation de la ville
        const city = document.getElementById('city');
        if (city.value.trim() && city.value.length < 2) {
            showError(city, 'Le nom de la ville doit contenir au moins 2 caractères');
            return;
        }

        // Validation du groupe d'âge
        const ageGroup = document.getElementById('age-group');
        if (!ageGroup.value) {
            showError(ageGroup, 'Veuillez sélectionner votre groupe d\'âge');
            return;
        }

        // Validation des préférences de projet
        const preferredProjects = document.getElementById('preferred-projects');
        if (!preferredProjects.value) {
            showError(preferredProjects, 'Veuillez sélectionner vos préférences de projet');
            return;
        }

        // Validation du type de contribution
        const contributionType = document.getElementById('contributionType');
        if (contributionType.value === '') {
            showError(contributionType, 'Veuillez sélectionner un type de contribution');
            return;
        }

        // Validation du message
        const message = document.getElementById('message');
        if (!message.value.trim()) {
            showError(message, 'Veuillez décrire votre contribution');
            return;
        }
        if (message.value.length < 10) {
            showError(message, 'La description doit contenir au moins 10 caractères');
            return;
        }

        // Validation des conditions d'utilisation
        const terms = document.querySelector('input[name="terms"]');
        if (!terms.checked) {
            showError(terms, 'Vous devez accepter les conditions d\'utilisation');
            return;
        }

        // Validation du fichier uploadé
        const fileUpload = document.getElementById('fileUpload');
        if (fileUpload.files.length > 0) {
            const file = fileUpload.files[0];
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!allowedTypes.includes(file.type)) {
                showError(fileUpload, 'Format de fichier non supporté. Utilisez PDF, JPEG ou PNG');
                return;
            }

            if (file.size > maxSize) {
                showError(fileUpload, 'Le fichier ne doit pas dépasser 5MB');
                return;
            }
        }

        // Si toutes les validations passent, soumettre le formulaire
        form.submit();
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

    // Validation en temps réel du numéro de téléphone
    const phoneNumber = document.getElementById('phone-number');
    phoneNumber.addEventListener('input', function(e) {
        // Nettoyer le numéro de téléphone
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) {
            value = value.slice(0, 10);
        }
        e.target.value = value;
    });

    // Validation en temps réel de l'email
    const email = document.getElementById('email');
    email.addEventListener('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
            showError(this, 'Veuillez entrer une adresse email valide');
        }
    });

    // Validation en temps réel du message
    const message = document.getElementById('message');
    message.addEventListener('input', function() {
        if (this.value.length < 10 && this.value.length > 0) {
            showError(this, 'La description doit contenir au moins 10 caractères');
        }
    });

    // Validation en temps réel du prénom et nom
    const nameFields = [document.getElementById('firstName'), document.getElementById('lastName')];
    nameFields.forEach(field => {
        field.addEventListener('input', function() {
            if (this.value.length < 2 && this.value.length > 0) {
                showError(this, 'Ce champ doit contenir au moins 2 caractères');
            }
        });
    });

    // Validation en temps réel de la ville
    const city = document.getElementById('city');
    city.addEventListener('input', function() {
        if (this.value.length < 2 && this.value.length > 0) {
            showError(this, 'Le nom de la ville doit contenir au moins 2 caractères');
        }
    });
}); 