<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CityPulse - Urban Planning Platform</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style1.css">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
  <header>
    <div class="container header-container">
      <a href="front/landingPage.html" class="logo">
        <img src="assets/logo.png" alt="CityPulse Logo" style="height: 35px; margin-right: 10px;">
        CityPulse
      </a>
      <nav class="main-nav">
        <a href="cont.php" class="active">post</a>
        <a href="event.html">Events</a>
        <a href="forums.html">Forums</a>
      </nav>
      <div class="auth-buttons">
        <a href="login.html" style="text-decoration: none; color: var(--text-medium); margin-right: 10px;">Log In</a>
        <button class="btn btn-primary">Sign Up</button>
      </div>
    </div>
  </header>

  <main class="container">
    <div class="row" style="display: flex; gap: 20px; margin-top: 20px;">
      <!-- Left Column - Profile Section -->
      <div class="card" style="width: 25%;">
        <div style="text-align: center;">
          <h3>Profil</h3>
          <div class="profile-avatar"></div>
          <h4 class="profile-name">Sophie Durand</h4>
          <p class="profile-title">Architecte Urbaniste</p>
          <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 10px;">
            <button class="btn btn-primary" style="font-size: 12px; padding: 6px 12px;">Modifier</button>
            <button class="btn btn-outline" style="font-size: 12px; padding: 6px 12px;">Messages</button>
          </div>
        </div>

        <div style="margin-top: 20px;">
          <h4>Mes Groupes</h4>
          <div class="group-item">
            <div class="group-avatar"></div>
            <div class="group-info">
              <p class="group-name">Urbanisme durable</p>
              <p class="group-meta">67 membres</p>
            </div>
          </div>
          <div class="group-item">
            <div class="group-avatar"></div>
            <div class="group-info">
              <p class="group-name">Mobilité urbaine</p>
              <p class="group-meta">45 800 membres</p>
            </div>
          </div>
          <div class="group-item">
            <div class="group-avatar"></div>
            <div class="group-info">
              <p class="group-name">Économie solidaire</p>
              <p class="group-meta">12 400 membres</p>
            </div>
          </div>
        </div>

        <div style="margin-top: 20px;">
          <h4>Événements à venir</h4>
          <div class="event-item">
            <p class="event-date"><i class="far fa-calendar"></i> 15 mars 2025</p>
            <p class="event-title">Forum de l'urbanisme durable</p>
            <p class="event-location">Paris, France</p>
          </div>
          <div class="event-item">
            <p class="event-date"><i class="far fa-calendar"></i> 28 mai 2025</p>
            <p class="event-title">Web-salon: Villes intelligentes</p>
            <p class="event-location">En ligne</p>
          </div>
        </div>
      </div>

      <!-- Middle Column - Feed -->
      <div style="width: 50%;">
        <!-- Post creation -->
        <div class="card">
          <div style="display: flex; align-items: center; gap: 15px;">
            <div class="profile-avatar" style="width: 40px; height: 40px;"></div>
            <div style="flex: 1;">
              <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button class="btn btn-primary">
                  <a href="addpost.html" style="color: white; text-decoration: none;">Submit Post</a>
                </button>
                <button class="btn btn-outline">
                  <a href="modify.html" style="color: var(--primary); text-decoration: none;">Modify Post</a>
                </button>
                <button class="btn btn-outline" style="color: #ff4757; border-color: #ff4757;">
                  <a href="delete.html" style="color: #ff4757; text-decoration: none;">Delete Post</a>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Feed filters -->
        <div class="tabs" style="margin: 15px 0;">
          <button class="tab active">Populaire</button>
          <button class="tab">Récent</button>
          <button class="tab">Suivi</button>
        </div>

        <?php
        require 'C:\xampp\htdocs\blog\config.php';

        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->query("SELECT * FROM post ORDER BY created_at DESC");
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
        ?>

        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-header">
                    <div class="post-avatar"></div>
                    <div class="post-meta">
                        <h4 class="post-author"><?= htmlspecialchars($post['author']) ?></h4>
                        <p class="post-time"><?= $post['created_at'] ?></p>
                    </div>
                </div>
                <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                <p class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                <div class="post-actions">
                    <div class="action-buttons">
                        <button class="action-button"><i class="far fa-comment"></i> 0</button>
                        <button class="action-button"><i class="far fa-bookmark"></i></button>
                        <button class="action-button">Partager</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
      </div>

      <!-- Right Column - Forums & Resources -->
      <div style="width: 25%;">
        <!-- Popular Forums -->
        <div class="card">
          <h3>Forums populaires</h3>
          <div class="group-item">
            <div class="group-avatar" style="background-color: var(--primary-light);"></div>
            <div class="group-info">
              <p class="group-name">Urbanisme tactique</p>
              <p class="group-meta">37 discussions actives</p>
            </div>
          </div>
          <div class="group-item">
            <div class="group-avatar" style="background-color: var(--primary-light);"></div>
            <div class="group-info">
              <p class="group-name">Rénovation énergétique</p>
              <p class="group-meta">28 discussions actives</p>
            </div>
          </div>
          <div class="group-item">
            <div class="group-avatar" style="background-color: var(--primary-light);"></div>
            <div class="group-info">
              <p class="group-name">Participation citoyenne</p>
              <p class="group-meta">29 discussions actives</p>
            </div>
          </div>
        </div>

        <!-- Suggestions -->
        <div class="card">
          <h3>Suggestions</h3>
          <p style="color: var(--text-light); font-size: 12px;">Personnes à suivre</p>
          
          <div style="display: flex; align-items: center; justify-content: space-between; margin: 15px 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <div class="profile-avatar" style="width: 40px; height: 40px;"></div>
              <div>
                <p style="font-weight: 500; margin: 0;">Marie Lambert</p>
                <p style="color: var(--text-light); font-size: 12px; margin: 0;">Paysagiste</p>
              </div>
            </div>
            <button class="btn btn-outline" style="font-size: 12px; padding: 4px 12px;">Suivre</button>
          </div>
          
          <div style="display: flex; align-items: center; justify-content: space-between; margin: 15px 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <div class="profile-avatar" style="width: 40px; height: 40px;"></div>
              <div>
                <p style="font-weight: 500; margin: 0;">Lucas Renaud</p>
                <p style="color: var(--text-light); font-size: 12px; margin: 0;">Paysagiste</p>
              </div>
            </div>
            <button class="btn btn-outline" style="font-size: 12px; padding: 4px 12px;">Suivre</button>
          </div>
        </div>

        <!-- Resources -->
        <div class="card">
          <h3>Ressources</h3>
          <div class="group-item">
            <div class="group-avatar" style="background-color: #f1f1f1; display: flex; align-items: center; justify-content: center;">
              <span style="font-size: 10px; color: var(--text-medium);">PDF</span>
            </div>
            <div class="group-info">
              <p class="group-name">Bibliothèque de modèles 3D</p>
              <p class="group-meta">20 éléments</p>
            </div>
          </div>
          <div class="group-item">
            <div class="group-avatar" style="background-color: #f1f1f1; display: flex; align-items: center; justify-content: center;">
              <span style="font-size: 10px; color: var(--text-medium);">PDF</span>
            </div>
            <div class="group-info">
              <p class="group-name">Réglementations urbanistiques</p>
              <p class="group-meta"></p>
            </div>
          </div>
        </div>
      </div>
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
</body>
</html>