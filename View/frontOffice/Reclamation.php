<?php
require "../../Controller/ReponseC.php";

$reclamation = new ReclamationC();
$id_user = 1;
$tab = $reclamation->listeReclamationByUser($id_user);
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
            <form id="new-discussion-form" class="form-section" method="post" action="../backOffice/conEnvoyerReclamation.php" onsubmit="return validerDescription()">
                <div class="form-group">
                     <label for="Titre">Titre</label>
                    <input id="Titre" name="Titre" class="form-control"  
                              placeholder="Entrez un titre"/>
                              <label for="Raison">Raison</label>

                            <select id="Raison" name="Raison" class="form-control">
                            <option value="">-- Sélectionnez une raison --</option>
                            <option value="technique">Problème technique</option>
                            <option value="facturation">Erreur de facturation</option>
                            <option value="delai">Retard de livraison</option>
                            <option value="qualite">Problème de qualité</option>
                            <option value="autre">Autre</option>
                            </select>

                    <label for="Description">Description</label>
                    <textarea id="Description" name="Description" class="form-control" rows="6"  
                              placeholder="Share your thoughts, ideas, or questions..."></textarea>
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
    </script>
    <script src="js/ControleSaisie.js"> </script>
</body>
</html>