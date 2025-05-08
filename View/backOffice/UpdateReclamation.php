<?php
require "../../Controller/ReponseC.php";

// Assuming this is the user ID. You might need to retrieve it dynamically.
$id_user = 1;

// Fetch all reclamations by the user
$reclamation = new ReclamationC();
$tab = $reclamation->listeReclamationByUser($id_user);

// List of bad words to filter
$badWords = array(
    'hate', 'disgusting', 'stupid', 'idiot', 'fool',
    'loser', 'jerk', 'moron', 'dumb', 'creep',
    'scumbag', 'pathetic', 'ignorant', 'foolish', 'useless',
    'shameful', 'worthless', 'annoying', 'ridiculous', 'coward',
    'selfish', 'arrogant', 'lazy', 'hypocrite', 'manipulative',
    'deceitful', 'toxic', 'nasty', 'vile', 'cruel',
    'mean', 'abusive', 'offensive', 'disrespectful', 'malicious'
);

// Function to check for bad words
function containsBadWords($text, $badWords) {
    $text = strtolower($text);
    foreach ($badWords as $word) {
        if (strpos($text, strtolower($word)) !== false) {
            return true;
        }
    }
    return false;
}

// Check if 'id_reclamation' is set in POST before using it
if (isset($_POST['id_reclamation'])) {
    $id_reclamation = $_POST['id_reclamation'];
    $id = $id_reclamation; // Pass to another variable if needed

    // Fetch description by id
    $result= $reclamation->getDescriptionById($id_reclamation);
    $description=$result['description_reclamation'];
    $titre=$result['titre_reclamation'];
    $raison=$result['raison_reclamation'];

} else {
    // Handle the case where 'id_reclamation' is not set in POST
    echo "ID Reclamation not found!";
    exit; // Optionally exit or redirect
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityPulse - Forums</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
            justify-content: center; /* Center modal horizontally */
            align-items: center; /* Center modal vertically */
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .form-control.error {
            border-color: #dc3545;
        }

        .form-control.error:focus {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .form-control.error:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.html" class="logo">
                <img src="imagelogo" alt="CityPulse Logo">
                CityPulse
            </a>
            <nav class="main-nav">
                <a href="projects.html">Projects</a>
                <a href="events.html">Events</a>
                <a href="Reclamation.php" class="active">Reclamation</a>
                <a href="groups.html">Groups</a>
            </nav>
            <div class="auth-buttons">
                <a href="login.html" class="btn btn-outline">Log In</a>
                <a href="signup.html" class="btn btn-primary">Sign Up</a>
                <a href="#" class="btn btn-primary" id="notificationButton">
                    <i class="fas fa-bell"></i> Notification
                </a>
            </div>
        </div>
    </header>
    
    <main class="container">
        <div class="page-header" style="margin: 24px 0;">
            <h1>Modifier Reclamation</h1>
        </div>

        <div class="card">
            <form id="new-discussion-form" class="form-section" method="post" action="conModifierReclamation.php" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="Titre">Titre</label>
                    <input id="Titre" name="Titre" class="form-control" placeholder="Entrez un titre" value="<?php echo htmlspecialchars(is_array($titre) ? implode(', ', $titre) : $titre); ?>"/>
                    <div id="titleError" class="error-message">Le titre contient des mots inappropriés.</div>

                    <label for="Raison">Raison</label>
                    <select id="Raison" name="Raison" class="form-control">
                        <option value="">-- Sélectionnez une raison --</option>
                        <option value="technique" <?php if ($raison == 'technique') echo 'selected'; ?>>Problème technique</option>
                        <option value="facturation" <?php if ($raison == 'facturation') echo 'selected'; ?>>Erreur de facturation</option>
                        <option value="delai" <?php if ($raison == 'delai') echo 'selected'; ?>>Retard de livraison</option>
                        <option value="qualite" <?php if ($raison == 'qualite') echo 'selected'; ?>>Problème de qualité</option>
                        <option value="autre" <?php if ($raison == 'autre') echo 'selected'; ?>>Autre</option>
                    </select>
                    <div id="raisonError" class="error-message">Veuillez sélectionner une raison</div>

                    <label for="Description">Description</label>
                    <textarea id="Description" name="Description" class="form-control" rows="6" placeholder="Share your thoughts, ideas, or questions..."><?php echo htmlspecialchars(is_array($description) ? implode(', ', $description) : $description); ?></textarea>
                    <div id="descriptionError" class="error-message">La description contient des mots inappropriés.</div>
                </div>              
                <div class="form-actions">
                    <a href="Reclamation.php" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>

                <input type="hidden" name="id_reclamation" value="<?php echo htmlspecialchars($id); ?>">
            </form>
        </div>
    </main>

    <footer style="background-color: var(--text-dark); color: white; padding: 48px 0; margin-top: 48px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px;">
                <div>
                    <h3>CityPulse</h3>
                    <p>Connect with urban planners, architects, and citizens to collaborate on innovative projects.</p>
                </div>
                <div>
                    <h4>Resources</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="#" style="color: white; text-decoration: none;">Urban Planning Guide</a></li>
                        <li><a href="#" style="color: white; text-decoration: none;">Sustainable City Toolkit</a></li>
                        <li><a href="#" style="color: white; text-decoration: none;">Community Engagement</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Company</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="#" style="color: white; text-decoration: none;">About Us</a></li>
                        <li><a href="#" style="color: white; text-decoration: none;">Blog</a></li>
                        <li><a href="#" style="color: white; text-decoration: none;">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Connect</h4>
                    <div style="display: flex; gap: 16px; margin-top: 8px;">
                        <a href="#" style="color: white;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: white;"><i class="fab fa-linkedin"></i></a>
                        <a href="#" style="color: white;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: white;"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 32px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.1);">
                <p>&copy; 2025 CityPulse. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Modal -->
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h1>Reclamation Messages</h1>
            <?php
            for ($i = 0; $i < count($tab); $i++) {
                echo "<div class='message'>";
                echo "<p><strong>Titre:</strong> " . $tab[$i]['titre_reclamation'] . "</p>";
                echo "<p><strong>Raison:</strong> " . $tab[$i]['raison_reclamation'] . "</p>";
                echo "<p><strong>Description:</strong> " . $tab[$i]['description_reclamation'] . " </p>";
                echo "<p><strong>Date:</strong> " . $tab[$i]['date_reclamation'] . "</p>";
                echo "<p><strong>Status:</strong> " . $tab[$i]['status_reclamation'] . "</p>";

                // Show buttons if status is 'En attente'
                if ($tab[$i]['status_reclamation'] === 'En attente') {
                    echo "<div class='actions'>";

                    // Edit Button Form
                    echo "<form method='post' action='UpdateReclamation.php' style='display:inline-block;'>";
                    echo "<input type='hidden' name='id_reclamation' value='" . $tab[$i]['id_reclamation'] . "'>";
                    echo "<button type='submit' class='edit-btn'>Edit</button>";
                    echo "</form>";

                    // Delete Button Form
                    echo "<form method='post' action='delete_reclamation.php' style='display:inline-block; margin-left:10px;'>";
                    echo "<input type='hidden' name='id_reclamation' value='" . $tab[$i]['id_reclamation'] . "'>";
                    echo "<button type='submit' class='delete-btn'>Delete</button>";
                    echo "</form>";

                    echo "</div>";
                }

                echo "<br>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <script>
        // Bad words list
        const badWords = <?php echo json_encode($badWords); ?>;

        // Function to check for bad words
        function containsBadWords(text) {
            text = text.toLowerCase();
            return badWords.some(word => text.includes(word.toLowerCase()));
        }

        // Input validation function
        function validateForm() {
            const titre = document.getElementById('Titre').value.trim();
            const raison = document.getElementById('Raison').value;
            const description = document.getElementById('Description').value.trim();
            let isValid = true;

            // Reset previous error states
            document.getElementById('Titre').classList.remove('error');
            document.getElementById('Raison').classList.remove('error');
            document.getElementById('Description').classList.remove('error');
            document.getElementById('titleError').style.display = 'none';
            document.getElementById('raisonError').style.display = 'none';
            document.getElementById('descriptionError').style.display = 'none';

            // Validate title
            if (titre === '') {
                document.getElementById('Titre').classList.add('error');
                document.getElementById('titleError').textContent = 'Le titre est requis';
                document.getElementById('titleError').style.display = 'block';
                isValid = false;
            } else if (titre.length < 5) {
                document.getElementById('Titre').classList.add('error');
                document.getElementById('titleError').textContent = 'Le titre doit contenir au moins 5 caractères';
                document.getElementById('titleError').style.display = 'block';
                isValid = false;
            } else if (titre.length > 100) {
                document.getElementById('Titre').classList.add('error');
                document.getElementById('titleError').textContent = 'Le titre ne doit pas dépasser 100 caractères';
                document.getElementById('titleError').style.display = 'block';
                isValid = false;
            } else if (containsBadWords(titre)) {
                document.getElementById('Titre').classList.add('error');
                document.getElementById('titleError').textContent = 'Le titre contient des mots inappropriés';
                document.getElementById('titleError').style.display = 'block';
                isValid = false;
            }

            // Validate reason
            if (raison === '') {
                document.getElementById('Raison').classList.add('error');
                document.getElementById('raisonError').textContent = 'Veuillez sélectionner une raison';
                document.getElementById('raisonError').style.display = 'block';
                isValid = false;
            }

            // Validate description
            if (description === '') {
                document.getElementById('Description').classList.add('error');
                document.getElementById('descriptionError').textContent = 'La description est requise';
                document.getElementById('descriptionError').style.display = 'block';
                isValid = false;
            } else if (description.length < 20) {
                document.getElementById('Description').classList.add('error');
                document.getElementById('descriptionError').textContent = 'La description doit contenir au moins 20 caractères';
                document.getElementById('descriptionError').style.display = 'block';
                isValid = false;
            } else if (description.length > 1000) {
                document.getElementById('Description').classList.add('error');
                document.getElementById('descriptionError').textContent = 'La description ne doit pas dépasser 1000 caractères';
                document.getElementById('descriptionError').style.display = 'block';
                isValid = false;
            } else if (containsBadWords(description)) {
                document.getElementById('Description').classList.add('error');
                document.getElementById('descriptionError').textContent = 'La description contient des mots inappropriés';
                document.getElementById('descriptionError').style.display = 'block';
                isValid = false;
            }

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de validation',
                    text: 'Veuillez corriger les erreurs dans le formulaire',
                    confirmButtonColor: '#2ed573'
                });
                return false;
            }

            // Show confirmation dialog
            Swal.fire({
                title: 'Confirmer la modification',
                text: "Êtes-vous sûr de vouloir modifier cette réclamation ?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2ed573',
                cancelButtonColor: '#ff4757',
                confirmButtonText: 'Oui, modifier',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Modification en cours...',
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

        // Add real-time validation
        document.getElementById('Titre').addEventListener('input', function() {
            const titre = this.value.trim();
            const errorElement = document.getElementById('titleError');
            
            if (titre === '') {
                this.classList.add('error');
                errorElement.textContent = 'Le titre est requis';
                errorElement.style.display = 'block';
            } else if (titre.length < 5) {
                this.classList.add('error');
                errorElement.textContent = 'Le titre doit contenir au moins 5 caractères';
                errorElement.style.display = 'block';
            } else if (titre.length > 100) {
                this.classList.add('error');
                errorElement.textContent = 'Le titre ne doit pas dépasser 100 caractères';
                errorElement.style.display = 'block';
            } else if (containsBadWords(titre)) {
                this.classList.add('error');
                errorElement.textContent = 'Le titre contient des mots inappropriés';
                errorElement.style.display = 'block';
            } else {
                this.classList.remove('error');
                errorElement.style.display = 'none';
            }
        });

        document.getElementById('Raison').addEventListener('change', function() {
            const raison = this.value;
            const errorElement = document.getElementById('raisonError');
            
            if (raison === '') {
                this.classList.add('error');
                errorElement.textContent = 'Veuillez sélectionner une raison';
                errorElement.style.display = 'block';
            } else {
                this.classList.remove('error');
                errorElement.style.display = 'none';
            }
        });

        document.getElementById('Description').addEventListener('input', function() {
            const description = this.value.trim();
            const errorElement = document.getElementById('descriptionError');
            
            if (description === '') {
                this.classList.add('error');
                errorElement.textContent = 'La description est requise';
                errorElement.style.display = 'block';
            } else if (description.length < 20) {
                this.classList.add('error');
                errorElement.textContent = 'La description doit contenir au moins 20 caractères';
                errorElement.style.display = 'block';
            } else if (description.length > 1000) {
                this.classList.add('error');
                errorElement.textContent = 'La description ne doit pas dépasser 1000 caractères';
                errorElement.style.display = 'block';
            } else if (containsBadWords(description)) {
                this.classList.add('error');
                errorElement.textContent = 'La description contient des mots inappropriés';
                errorElement.style.display = 'block';
            } else {
                this.classList.remove('error');
                errorElement.style.display = 'none';
            }
        });

        // Show success message if URL has success parameter
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                Swal.fire({
                    icon: 'success',
                    title: 'Succès!',
                    text: 'La réclamation a été modifiée avec succès',
                    confirmButtonColor: '#2ed573'
                }).then(() => {
                    window.location.href = 'Reclamation.php';
                });
            }
        }
    </script>
</body>
</html>
