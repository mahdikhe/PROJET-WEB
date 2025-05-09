<?php

$id=$_POST['id_reclamation'];

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="style.css">
    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Add SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
</head>
<body>
  

 <!-- Sidebar -->
 <aside class="sidebar">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo">
        
        </div>
        <nav>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link ">
                        <i class="fas fa-home"></i>
                        <span>Overview</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Audience</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Locations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                     <li class="nav-item active">
                        <a href="TableReclamation.php" class="nav-link">
                            <i class="fas fa-exclamation-circle"></i> <!-- or use fa-comment-dots -->
                            <span>Reclamation</span>
                        </a>
                    </li>
                        <li class="nav-item active">
                            <a href="TableReponse.php" class="nav-link">
                                <i class="fas fa-reply"></i> <!-- or use fa-comments -->
                                <span>Reponse</span>
                            </a>
                        </li>

            </ul>
        </nav>
    </aside>








    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <h1 class="header-title">Ajouter Reponse</h1>
            <div class="header-actions">
             <a href="TableReclamation.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Retourner
             </a>
            </div>
        </header>

      

        

        <!-- Email List Section -->
        <main class="container">
        <div class="page-header" style="margin: 24px 0;">
            <h1>Repondre ici</h1>
        </div>

        <div class="card">
            <form id="new-discussion-form" class="form-section" method="post" action="conAjouterReponse.php" onsubmit="return confirmSubmit(event)">
              

                <div class="form-group">
                    <label for="discussionContent">Description</label>
                    <textarea id="Description" name="Description" class="form-control" rows="6" required 
                              placeholder="Share your thoughts, ideas, or questions..."></textarea>
                </div>              
                <input type='hidden' name='id_reclamation' value=<?php echo ($id) ?>>

                <div class="form-actions">
                    <a href="TableReclamation.php" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </main>

        
               

           

        <!-- Charts Section -->
       

        
    </main>

    <script>
        function confirmSubmit(event) {
            event.preventDefault();
            
            const description = document.getElementById('Description').value.trim();
            
            if (!description) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Veuillez remplir la description',
                    confirmButtonColor: '#2ed573'
                });
                return false;
            }

            Swal.fire({
                title: 'Confirmer la réponse',
                text: "Êtes-vous sûr de vouloir enregistrer cette réponse ?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2ed573',
                cancelButtonColor: '#ff4757',
                confirmButtonText: 'Oui, enregistrer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Enregistrement en cours...',
                        text: 'Veuillez patienter',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit the form
                    document.getElementById('new-discussion-form').submit();
                }
            });

            return false;
        }

        // Show success message if URL has success parameter
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                Swal.fire({
                    icon: 'success',
                    title: 'Succès!',
                    text: 'La réponse a été enregistrée avec succès',
                    confirmButtonColor: '#2ed573'
                }).then(() => {
                    window.location.href = 'TableReponse.php';
                });
            }
        }
    </script>

    <script src="../frontOffice/js/ReclamationJS.js"> </script>

</body>
</html> 