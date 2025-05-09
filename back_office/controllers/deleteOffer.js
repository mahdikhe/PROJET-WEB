document.addEventListener('DOMContentLoaded', function() {
    // Get all delete buttons
    const deleteButtons = document.querySelectorAll('.delete-btn');

    // Create the confirmation modal dynamically
    const confirmationModal = document.createElement('div');
    confirmationModal.id = 'deleteConfirmModal';
    confirmationModal.className = 'modal';
    confirmationModal.innerHTML = `
        <div class="modal-content" style="width: 400px;">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Confirmation de suppression</h2>
                <button id="closeDeleteModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="mb-6">Voulez-vous supprimer l'offre <span id="offerIdToDelete"></span> ?</p>
            <div class="flex justify-end gap-3">
                <button id="cancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Annuler</button>
                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Confirmer</button>
            </div>
        </div>
    `;
    document.body.appendChild(confirmationModal);

    // Get modal elements
    const modal = document.getElementById('deleteConfirmModal');
    const closeModalBtn = document.getElementById('closeDeleteModal');
    const cancelBtn = document.getElementById('cancelDeleteBtn');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const offerIdSpan = document.getElementById('offerIdToDelete');

    // Variable to store current offer ID to delete
    let currentOfferId = null;

    // Attach click event to all delete buttons
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            // Get the offer ID from data-id attribute
            currentOfferId = this.getAttribute('data-id');
            // Display the offer ID in the confirmation modal
            offerIdSpan.textContent = currentOfferId;
            // Show the modal
            modal.style.display = 'block';
        });
    });

    // Close modal
    function closeModal() {
        modal.style.display = 'none';
    }

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Handle delete confirmation
    confirmBtn.addEventListener('click', function() {
        if (currentOfferId) {
            // Create form data
            const formData = new FormData();
            formData.append('id', currentOfferId);

            // Show loading state
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            confirmBtn.disabled = true;

            // Send AJAX request
            fetch('../model/deleteOffer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.text();
            })
            .then(text => {
                // Try to parse as JSON, if it fails, we have a non-JSON response
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Réponse invalide du serveur');
                }
                
                closeModal();
                
                if (data.success) {
                    // Show success notification
                    showNotification(data.message, 'success');
                    
                    // Remove the row from the table
                    const row = document.querySelector(`button[data-id="${currentOfferId}"]`).closest('tr');
                    if (row) {
                        row.remove();
                    }
                } else {
                    // Show error notification
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                closeModal();
                showNotification('Une erreur est survenue: ' + error.message, 'error');
                console.error('Error details:', error);
            })
            .finally(() => {
                // Reset button state
                confirmBtn.innerHTML = 'Confirmer';
                confirmBtn.disabled = false;
            });
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