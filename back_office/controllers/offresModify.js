document.addEventListener('DOMContentLoaded', function() {
    // Get all edit buttons
    const editButtons = document.querySelectorAll('.edit-btn');
    
    // Get modal elements
    const modal = document.getElementById('editOfferModal');
    const closeModalBtn = document.getElementById('closeEditModal');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const form = document.getElementById('editOfferForm');

    // Get form input elements
    const idInput = document.getElementById('edit_id');
    const titreInput = document.getElementById('edit_titre');
    const entrepriseInput = document.getElementById('edit_entreprise');
    const emplacementSelect = document.getElementById('edit_emplacement');
    const descriptionInput = document.getElementById('edit_description');
    const dateInput = document.getElementById('edit_date');
    const typeInputs = document.querySelectorAll('input[name="edit_type"]');

    // Tunisian cities array (same as in offresadd.js)
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

    // Initialize datepicker for edit form
    const datepicker = flatpickr("#edit_date", {
        dateFormat: "Y-m-d",
        minDate: "today",
    });

    // Attach event listener to all edit buttons
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const offerId = this.dataset.id;
            fetchOfferData(offerId);
        });
    });

    // Function to fetch offer data and populate the form
    function fetchOfferData(id) {
        // Show loading state
        showLoading(true);
        
        // Fetch the offer data
        fetch(`../model/getOfferById.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.offer) {
                    // Populate form with offer data
                    populateForm(data.offer);
                    // Show the modal
                    modal.style.display = 'block';
                } else {
                    showNotification('Offre non trouvée', 'error');
                }
            })
            .catch(error => {
                showNotification('Erreur lors de la récupération des données: ' + error.message, 'error');
            })
            .finally(() => {
                showLoading(false);
            });
    }

    // Function to show loading state
    function showLoading(isLoading) {
        // Implementation depends on your UI
        // For example, show/hide a spinner or disable buttons
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = isLoading ? 'flex' : 'none';
        }
    }

    // Function to populate the form with offer data
    function populateForm(offer) {
        // Set values in form
        idInput.value = offer.id;
        titreInput.value = offer.titre;
        entrepriseInput.value = offer.entreprise;
        emplacementSelect.value = offer.emplacement;
        descriptionInput.value = offer.description;
        
        // Set date
        datepicker.setDate(offer.date);
        
        // Set type radio button
        const typeRadio = document.querySelector(`input[name="edit_type"][value="${offer.type}"]`);
        if (typeRadio) {
            typeRadio.checked = true;
        }
        
        // Update word count for description
        const wordCount = offer.description.trim() ? offer.description.trim().split(/\s+/).length : 0;
        document.getElementById('editDescriptionWordCount').textContent = `${wordCount} mots`;
        
        // Clear any previous error states
        clearErrors();
        resetInputStyles();
        
        // Enable the submit button
        updateSubmitButtonState();
    }

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
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
    }

    // Clear specific error message
    function clearError(inputId) {
        const errorElement = document.getElementById(inputId + 'Error');
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    }
    
    // Validate title during input - allow any input but provide feedback
    titreInput.addEventListener('input', function() {
        let value = titreInput.value.trim();
        
        if (value === '') {
            showError('edit_titre', 'Le titre est requis');
            titreInput.classList.add('border-red-500');
            titreInput.classList.remove('border-green-500', 'border-gray-300');
        } else if (!/^[a-zA-ZÀ-ÿ\s\-]+$/.test(value)) {
            showError('edit_titre', 'Le titre ne doit contenir que des lettres, espaces et tirets');
            titreInput.classList.add('border-red-500');
            titreInput.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('edit_titre');
            titreInput.classList.remove('border-red-500', 'border-gray-300');
            titreInput.classList.add('border-green-500');
        }
        
        updateSubmitButtonState();
    });
    
    // Validate company name during input - allow any input but provide feedback
    entrepriseInput.addEventListener('input', function() {
        let value = entrepriseInput.value.trim();
        
        if (value === '') {
            showError('edit_entreprise', 'Le nom de l\'entreprise est requis');
            entrepriseInput.classList.add('border-red-500');
            entrepriseInput.classList.remove('border-green-500', 'border-gray-300');
        } else if (!/^[a-zA-ZÀ-ÿ\s\-&.]+$/.test(value)) {
            showError('edit_entreprise', 'Le nom de l\'entreprise ne doit contenir que des lettres, espaces et certains caractères spéciaux');
            entrepriseInput.classList.add('border-red-500');
            entrepriseInput.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('edit_entreprise');
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
        document.getElementById('editDescriptionWordCount').textContent = `${wordCount} mots`;
        
        if (value === '') {
            showError('edit_description', 'La description est requise');
            descriptionInput.classList.add('border-red-500');
            descriptionInput.classList.remove('border-green-500', 'border-gray-300');
        } else if (wordCount < 5) {
            showError('edit_description', 'La description doit contenir au moins 5 mots');
            descriptionInput.classList.add('border-red-500');
            descriptionInput.classList.remove('border-green-500', 'border-gray-300');
        } else if (wordCount > 255) {
            showError('edit_description', 'La description ne doit pas dépasser 255 mots');
            descriptionInput.classList.add('border-red-500');
            descriptionInput.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('edit_description');
            descriptionInput.classList.remove('border-red-500', 'border-gray-300');
            descriptionInput.classList.add('border-green-500');
        }
        
        updateSubmitButtonState();
    });
    
    // Validate emplacement on change
    emplacementSelect.addEventListener('change', function() {
        const value = emplacementSelect.value;
        
        if (!value) {
            showError('edit_emplacement', 'L\'emplacement est requis');
            emplacementSelect.classList.add('border-red-500');
            emplacementSelect.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('edit_emplacement');
            emplacementSelect.classList.remove('border-red-500', 'border-gray-300');
            emplacementSelect.classList.add('border-green-500');
        }
        
        updateSubmitButtonState();
    });
    
    // Validate date when selected
    dateInput.addEventListener('change', function() {
        const value = dateInput.value;
        
        if (!value) {
            showError('edit_date', 'La date est requise');
            dateInput.classList.add('border-red-500');
            dateInput.classList.remove('border-green-500', 'border-gray-300');
        } else {
            clearError('edit_date');
            dateInput.classList.remove('border-red-500', 'border-gray-300');
            dateInput.classList.add('border-green-500');
        }
        
        updateSubmitButtonState();
    });
    
    // Validate type on selection
    typeInputs.forEach(input => {
        input.addEventListener('change', function() {
            clearError('edit_type');
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
        const isTypeValid = document.querySelector('input[name="edit_type"]:checked') !== null;
        
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
        const isTypeValid = document.querySelector('input[name="edit_type"]:checked') !== null;
        
        const isFormValid = isTitreValid && isEntrepriseValid && isEmplacementValid && 
                          isDescriptionValid && isDateValid && isTypeValid;
        
        if (isFormValid) {
            // We need to map edit_type radio button values to type field for the server
            const typeValue = document.querySelector('input[name="edit_type"]:checked').value;
            const hiddenTypeInput = document.createElement('input');
            hiddenTypeInput.type = 'hidden';
            hiddenTypeInput.name = 'type';
            hiddenTypeInput.value = typeValue;
            form.appendChild(hiddenTypeInput);
            
            form.submit();
        }
    });
    
    // Function to show notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-5 right-5 p-4 rounded-md shadow-md ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="mr-3">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                </div>
                <p>${message}</p>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Remove notification after some time
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
});