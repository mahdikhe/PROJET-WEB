<?php
require "../../Controller/ReponseC.php";
$id=$_POST['id_reponse'];
$rep=new ReponseC();

$desc=$rep->getDescriptionById($id);


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
   
</head>
<body>
  

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <h1 class="header-title">Modifier Reponse</h1>
            <div class="header-actions">
             <a href="TableReclamation.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Retourner
             </a>
            </div>
        </header>

      

        

        <!-- Email List Section -->
        <main class="container">
        <div class="page-header" style="margin: 24px 0;">
            <h1>Modifier ici</h1>
        </div>

        <div class="card">
        <form id="new-discussion-form" class="form-section" method="post" action="conModifierReponse.php" onsubmit="return validerDescription()">
    <div class="form-group">
        <label for="discussionContent">Description</label>
        <textarea id="Description" name="Description" class="form-control" rows="6" required 
                  placeholder="Share your thoughts, ideas, or questions..."><?php echo htmlspecialchars(is_array($desc) ? implode(', ', $desc) : $desc); ?></textarea>
    </div>              
    <input type='hidden' name='id_reponse' value="<?php echo htmlspecialchars($id); ?>">

    <div class="form-actions">
        <a href="forums.html" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
        </div>
    </main>

        
               

           

        <!-- Charts Section -->
       

        
    </main>

    <script src="../frontOffice/js/ReclamationJS.js"> </script>

</body>
</html> 