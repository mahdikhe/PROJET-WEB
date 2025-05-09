document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const modal = document.getElementById('addOfferModal');
    const addOfferBtn = document.getElementById('addOfferBtn');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const form = document.getElementById('addOfferForm');

    // Get form input elements
    const titreInput = document.getElementById('titre');
    const entrepriseInput = document.getElementById('entreprise');
    const emplacementSelect = document.getElementById('emplacement');
    const descriptionInput = document.getElementById('description');
    const dateInput = document.getElementById('date');
    const typeInputs = document.querySelectorAll('input[name="type"]');

    // Tunisian cities array
    const tunisianCities = [
        'Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte', 'Gabès', 'Ariana', 
        'Gafsa', 'Monastir', 'Ben Arous', 'Kasserine', 'Médenine', 'Nabeul', 
        'Tataouine', 'Béja', 'Kef', 'Mahdia', 'Sidi Bouzid', 'Jendouba', 
        'Tozeur', 'Manouba', 'Siliana', 'Kebili', 'Zaghouan'
    ];

    // Populate the cities dropdown
    tunisianCities.sort().forEach(city => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        emplacementSelect.appendChild(option);
    });

    // Initialize datepicker
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        minDate: "today",
    });

    // Open modal
    addOfferBtn.addEventListener('click', function() {
        modal.style.display = 'block';
        // Reset form and errors
        form.reset();
        clearErrors();
        resetInputStyles();
        // Reset word counter
        document.getElementById('descriptionWordCount').textContent = '0 mots';
    });

    // Close modal
    function closeModal() {
        modal.style.display = 'none';
        clearErrors();
        resetInputStyles();
    }

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Clear error messages
    function clearErrors() {
        const errorElements = document.querySelectorAll('.error-message');
        errorElements.forEach(element => {
            element.textContent = '';
            element.classList.add('hidden');
        });
    }

    // Reset input styles
    function resetInputStyles() {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.classList.remove('border-red-500', 'border-green-500');
            input.classList.add('border-gray-300');
        });
    }

    // Display error message
    function showError(inputId, message) {
        const errorElement = document.getElementById(inputId + 'Error');
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }

    // Clear specific error message
    function clearError(inputId) {
        const errorElement = document.getElementById(inputId + 'Error');
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
    }
    
    // Validate title during input - allow any input but provide feedback
    titreInput.addEventListener('input', function() {
        let value = titreInput.value.trim();
        
        if (value === '') {
            showError('titre', 'Le titre est requis');
            titreInput.classList.add('border-red-500');
            titreInput.classList.remove('border-green-500', 'border-gray-300');
        } else if (!/^[a-zA-ZÀ-ÿ\s\-]+$/.test(value)) {
            showError('titre', 'Le titre ne doit contenir que des lettres, espaces et tirets');
            titreInput.classList.add('border-red-500');
            titreInput.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('titre');
            titreInput.classList.remove('border-red-500', 'border-gray-300');
            titreInput.classList.add('border-green-500');
        }
        
        updateSubmitButtonState();
    });
    
    // Validate company name during input - allow any input but provide feedback
    entrepriseInput.addEventListener('input', function() {
        let value = entrepriseInput.value.trim();
        
        if (value === '') {
            showError('entreprise', 'Le nom de l\'entreprise est requis');
            entrepriseInput.classList.add('border-red-500');
            entrepriseInput.classList.remove('border-green-500', 'border-gray-300');
        } else if (!/^[a-zA-ZÀ-ÿ\s\-&.]+$/.test(value)) {
            showError('entreprise', 'Le nom de l\'entreprise ne doit contenir que des lettres, espaces et certains caractères spéciaux');
            entrepriseInput.classList.add('border-red-500');
            entrepriseInput.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('entreprise');
            entrepriseInput.classList.remove('border-red-500', 'border-gray-300');
            entrepriseInput.classList.add('border-green-500');
        }
        
        updateSubmitButtonState();
    });
    
    // Validate description as user types, checking for word count
    descriptionInput.addEventListener('input', function() {
        let value = descriptionInput.value.trim();
        const wordCount = value ? value.split(/\s+/).length : 0;
        
        // Update word counter
        document.getElementById('descriptionWordCount').textContent = `${wordCount} mots`;
        
        if (value === '') {
            showError('description', 'La description est requise');
            descriptionInput.classList.add('border-red-500');
            descriptionInput.classList.remove('border-green-500', 'border-gray-300');
        } else if (wordCount < 5) {
            showError('description', 'La description doit contenir au moins 5 mots');
            descriptionInput.classList.add('border-red-500');
            descriptionInput.classList.remove('border-green-500', 'border-gray-300');
        } else if (wordCount > 255) {
            showError('description', 'La description ne doit pas dépasser 255 mots');
            descriptionInput.classList.add('border-red-500');
            descriptionInput.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('description');
            descriptionInput.classList.remove('border-red-500', 'border-gray-300');
            descriptionInput.classList.add('border-green-500');
        }
        
        updateSubmitButtonState();
    });
    
    // Validate emplacement on change
    emplacementSelect.addEventListener('change', function() {
        const value = emplacementSelect.value;
        
        if (!value) {
            showError('emplacement', 'L\'emplacement est requis');
            emplacementSelect.classList.add('border-red-500');
            emplacementSelect.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('emplacement');
            emplacementSelect.classList.remove('border-red-500', 'border-gray-300');
            emplacementSelect.classList.add('border-green-500');
        }
        
        updateSubmitButtonState();
    });
    
    // Validate date when selected
    dateInput.addEventListener('change', function() {
        const value = dateInput.value;
        
        if (!value) {
            showError('date', 'La date est requise');
            dateInput.classList.add('border-red-500');
            dateInput.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('date');
            dateInput.classList.remove('border-red-500', 'border-gray-300');
            dateInput.classList.add('border-green-500');
        }
        
        updateSubmitButtonState();
    });
    
    // Validate type on selection
    typeInputs.forEach(input => {
        input.addEventListener('change', function() {
            clearError('type');
            updateSubmitButtonState();
        });
    });
    
    // Check form validity and update submit button state
    function updateSubmitButtonState() {
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Check all validations
        const isTitreValid = titreInput.value.trim() !== '' && /^[a-zA-ZÀ-ÿ\s\-]+$/.test(titreInput.value.trim());
        const isEntrepriseValid = entrepriseInput.value.trim() !== '' && /^[a-zA-ZÀ-ÿ\s\-&.]+$/.test(entrepriseInput.value.trim());
        const isEmplacementValid = emplacementSelect.value !== '';
        
        const descriptionWordCount = descriptionInput.value.trim() ? descriptionInput.value.trim().split(/\s+/).length : 0;
        const isDescriptionValid = descriptionWordCount >= 5 && descriptionWordCount <= 255;
        
        const isDateValid = dateInput.value !== '';
        const isTypeValid = document.querySelector('input[name="type"]:checked') !== null;
        
        const isFormValid = isTitreValid && isEntrepriseValid && isEmplacementValid && 
                            isDescriptionValid && isDateValid && isTypeValid;
        
        // Enable/disable submit button
        if (isFormValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Form submission handler
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // One final check before submitting
        const isTitreValid = titreInput.value.trim() !== '' && /^[a-zA-ZÀ-ÿ\s\-]+$/.test(titreInput.value.trim());
        const isEntrepriseValid = entrepriseInput.value.trim() !== '' && /^[a-zA-ZÀ-ÿ\s\-&.]+$/.test(entrepriseInput.value.trim());
        const isEmplacementValid = emplacementSelect.value !== '';
        
        const descriptionWordCount = descriptionInput.value.trim() ? descriptionInput.value.trim().split(/\s+/).length : 0;
        const isDescriptionValid = descriptionWordCount >= 5 && descriptionWordCount <= 255;
        
        const isDateValid = dateInput.value !== '';
        const isTypeValid = document.querySelector('input[name="type"]:checked') !== null;
        
        const isFormValid = isTitreValid && isEntrepriseValid && isEmplacementValid && 
                          isDescriptionValid && isDateValid && isTypeValid;
        
        if (isFormValid) {
            // Generate a random ID for the offer (will be handled properly on the server side)
            const randomId = '#' + Math.floor(1000 + Math.random() * 9000);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'id';
            hiddenInput.value = randomId;
            form.appendChild(hiddenInput);
            
            form.submit();
        }
    });
});