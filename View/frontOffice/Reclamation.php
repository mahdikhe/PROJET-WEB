<?php
require "../../Controller/ReponseC.php";

$reclamation = new ReclamationC();
$id_user = 1;
$tab = $reclamation->listeReclamationByUser($id_user);

// Get the latest reclamation if it's new
$latestReclamation = null;
if (isset($_GET['new']) && $_GET['new'] === 'true' && !empty($tab)) {
    $latestReclamation = end($tab); // Get the most recent reclamation
}

// Check if there's a new reclamation
$hasNewReclamation = isset($_GET['new']) && $_GET['new'] === 'true';

// List of bad words to filter
$badWords = array(
    'hate', 'disgusting', 'stupid', 'idiot', 'fool','ahmed',
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['Titre'] ?? '';
    $description = $_POST['Description'] ?? '';
    
    if (containsBadWords($title, $badWords) || containsBadWords($description, $badWords)) {
        echo "<script>alert('Votre message contient des mots inappropriés. Veuillez modifier votre texte.');</script>";
    }
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

        /* Notification Styles */
        .notification-badge {
            position: relative;
            display: inline-block;
        }

        .notification-badge[data-count]:after {
            content: attr(data-count);
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ff4757;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            min-width: 18px;
            text-align: center;
        }

        .notification-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #2ed573;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
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
            <h1>Envoyer Reclamation</h1>
        </div>

        <div class="card">
            <form id="new-discussion-form" class="form-section" method="post" action="../backOffice/conEnvoyerReclamation.php" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="Titre">Titre</label>
                    <input id="Titre" name="Titre" class="form-control" placeholder="Entrez un titre"/>
                    <div id="titleError" class="error-message">Le titre contient des mots inappropriés.</div>

                    <label for="Raison">Raison</label>
                    <select id="Raison" name="Raison" class="form-control">
                        <option value="">-- Sélectionnez une raison --</option>
                        <option value="technique">Problème technique</option>
                        <option value="facturation">Erreur de facturation</option>
                        <option value="delai">Retard de livraison</option>
                        <option value="qualite">Problème de qualité</option>
                        <option value="autre">Autre</option>
                    </select>
                    <div id="raisonError" class="error-message">Veuillez sélectionner une raison</div>

                    <label for="Description">Description</label>
                    <textarea id="Description" name="Description" class="form-control" rows="6" placeholder="Share your thoughts, ideas, or questions..."></textarea>
                    <div id="descriptionError" class="error-message">La description contient des mots inappropriés.</div>
                </div>              
                <div class="form-actions">
                    <a href="forums.html" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </div>
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

<!-- CSS to make modal scrollable and style buttons -->
<style>
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.message {
    border-bottom: 1px solid #ddd;
    padding: 10px 0;
}

.actions {
    margin-top: 10px;
}

.edit-btn, .delete-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

.edit-btn {
    background-color: #4CAF50;
    color: white;
}

.delete-btn {
    background-color: #f44336;
    color: white;
}
</style>




    <script>
        // JavaScript to handle modal display
        var modal = document.getElementById("notificationModal");
        var btn = document.getElementById("notificationButton");
        var span = document.getElementById("closeModal");

        btn.onclick = function() {
            modal.style.display = "flex"; // Show the modal
        }

        span.onclick = function() {
            modal.style.display = "none"; // Close the modal
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none"; // Close modal if clicked outside
            }
        }

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
            let errorMessage = '';

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
            }

            // Check for bad words
            if (containsBadWords(titre) || containsBadWords(description)) {
                isValid = false;
            }

            if (!isValid) {
                errorMessage = 'Veuillez corriger les erreurs dans le formulaire';
                alert(errorMessage);
            }

            return isValid;
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

        // Notification System
        function showNotification(message, details = null) {
            console.log("Attempting to show notification:", message, details); // Debug log

            // Check if browser supports notifications
            if (!("Notification" in window)) {
                console.log("This browser does not support desktop notifications");
                return;
            }

            // Request permission if not already granted
            if (Notification.permission !== "granted") {
                Notification.requestPermission().then(function(permission) {
                    if (permission === "granted") {
                        showNotificationContent(message, details);
                    }
                });
            } else {
                showNotificationContent(message, details);
            }
        }

        function showNotificationContent(message, details) {
            console.log("Showing notification content:", message, details); // Debug log

            // Show browser notification
            try {
                const notification = new Notification("Nouvelle Réclamation", {
                    body: details ? `${message}\n\nTitre: ${details.titre}\nRaison: ${details.raison}\nDate: ${details.date}` : message,
                    icon: "assets/logo.png",
                    badge: "assets/logo.png",
                    tag: "reclamation-notification"
                });

                // Close notification after 5 seconds
                setTimeout(() => notification.close(), 5000);
            } catch (error) {
                console.error("Error showing browser notification:", error);
            }

            // Show toast notification
            const toast = document.getElementById('notificationToast');
            if (toast) {
                toast.style.display = 'block';
                
                // Create detailed toast content
                let toastContent = message;
                if (details) {
                    toastContent = `
                        <div style="text-align: left;">
                            <div style="font-weight: bold; margin-bottom: 8px;">${message}</div>
                            <div style="font-size: 0.9em;">
                                <div><strong>Titre:</strong> ${details.titre}</div>
                                <div><strong>Raison:</strong> ${details.raison}</div>
                                <div><strong>Date:</strong> ${details.date}</div>
                            </div>
                        </div>
                    `;
                }
                toast.innerHTML = toastContent;

                // Hide toast after 5 seconds
                setTimeout(() => {
                    toast.style.animation = 'slideOut 0.5s ease-out';
                    setTimeout(() => {
                        toast.style.display = 'none';
                        toast.style.animation = 'slideIn 0.5s ease-out';
                    }, 500);
                }, 5000);
            }
        }

        // Add notification to form submission
        document.getElementById('new-discussion-form').addEventListener('submit', function(e) {
            if (validateForm()) {
                // Don't show any notification during submission
                return true;
            }
            return false;
        });

        // Check for new reclamation on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM Content Loaded"); // Debug log
            <?php if ($latestReclamation): ?>
            console.log("Latest reclamation found:", <?php echo json_encode($latestReclamation); ?>); // Debug log
            setTimeout(() => {
                showNotification("Une nouvelle réclamation a été ajoutée!", {
                    titre: "<?php echo htmlspecialchars($latestReclamation['titre_reclamation']); ?>",
                    raison: "<?php echo htmlspecialchars($latestReclamation['raison_reclamation']); ?>",
                    date: "<?php echo htmlspecialchars($latestReclamation['date_reclamation']); ?>"
                });
            }, 1000); // Delay notification by 1 second to ensure DOM is ready
            <?php else: ?>
            console.log("No new reclamation found"); // Debug log
            <?php endif; ?>
        });

        // Update notification badge count
        function updateNotificationBadge() {
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                const count = <?php echo count($tab); ?>;
                badge.setAttribute('data-count', count);
            }
        }

        // Call update function on page load
        updateNotificationBadge();
    </script>
    <script src="js/ControleSaisie.js"> </script>
</body>
</html>