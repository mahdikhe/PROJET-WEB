<?php
require_once dirname(__DIR__, 2) . '/controller/Controller.php';
require_once dirname(__DIR__, 2) . '/model/model.php';
require_once dirname(__DIR__, 2) . '/model/translate.php';

$postController = new PostController();
$translator = new Translator();
$posts = [];
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle translation requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'translate') {
    $text = $_POST['text'] ?? '';
    if (!empty($text)) {
        $result = $translator->translateToFrench($text);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
}

// Handle like/dislike actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['post_id'])) {
    $user_id = 'anonymous'; // In a real application, this would be the logged-in user's ID
    $post_id = $_POST['post_id'];
    
    if ($_POST['action'] === 'like' || $_POST['action'] === 'dislike') {
        $type = $_POST['action'];
        if ($postController->toggleLike($post_id, $user_id, $type)) {
            header("Location: cont.php" . (!empty($search_query) ? "?search=" . urlencode($search_query) : ""));
            exit();
        }
    } elseif ($_POST['action'] === 'save') {
        if ($postController->toggleSavePost($post_id, $user_id)) {
            header("Location: cont.php" . (!empty($search_query) ? "?search=" . urlencode($search_query) : ""));
            exit();
        }
    }
}

try {
    if (!empty($search_query)) {
        $posts = $postController->searchPosts($search_query);
    } else {
        $posts = $postController->listPosts();
    }
} catch (Exception $e) {
    error_log("Error fetching posts: " . $e->getMessage());
    $error = "Error loading posts. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityPulse - Urban Planning Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="cont.php" class="logo">
                <img src="../../assets/logo.png" alt="CityPulse Logo" style="height: 35px; margin-right: 10px;">
                CityPulse
            </a>
            <nav class="main-nav">
                <a href="cont.php" class="active">Posts</a>
                <a href="saved.php"><i class="far fa-bookmark"></i> Saved</a>
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
                    <h3>Profile</h3>
                    <div class="profile-avatar"></div>
                    <h4 class="profile-name">Sophie Durand</h4>
                    <p class="profile-title">Urban Architect</p>
                    <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 10px;">
                        <button class="btn btn-primary" style="font-size: 12px; padding: 6px 12px;">Edit</button>
                        <button class="btn btn-outline" style="font-size: 12px; padding: 6px 12px;">Messages</button>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <h4>My Groups</h4>
                    <div class="group-item">
                        <div class="group-avatar"></div>
                        <div class="group-info">
                            <p class="group-name">Sustainable Urbanism</p>
                            <p class="group-meta">67 members</p>
                        </div>
                    </div>
                    <div class="group-item">
                        <div class="group-avatar"></div>
                        <div class="group-info">
                            <p class="group-name">Urban Mobility</p>
                            <p class="group-meta">45,800 members</p>
                        </div>
                    </div>
                    <div class="group-item">
                        <div class="group-avatar"></div>
                        <div class="group-info">
                            <p class="group-name">Solidarity Economy</p>
                            <p class="group-meta">12,400 members</p>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <h4>Upcoming Events</h4>
                    <div class="event-item">
                        <p class="event-date"><i class="far fa-calendar"></i> March 15, 2025</p>
                        <p class="event-title">Sustainable Urbanism Forum</p>
                        <p class="event-location">Paris, France</p>
                    </div>
                    <div class="event-item">
                        <p class="event-date"><i class="far fa-calendar"></i> May 28, 2025</p>
                        <p class="event-title">Webinar: Smart Cities</p>
                        <p class="event-location">Online</p>
                    </div>
                </div>
            </div>

            <!-- Middle Column - Feed -->
            <div style="width: 50%;">
                <!-- Search Bar -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form action="cont.php" method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search posts by title..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if (!empty($search_query)): ?>
                                <a href="cont.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Post creation -->
                <div class="card">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="profile-avatar" style="width: 40px; height: 40px;"></div>
                        <div style="flex: 1;">
                            <div style="display: flex; gap: 10px; margin-top: 10px;">
                                <button class="btn btn-primary">
                                    <a href="add.php" style="color: white; text-decoration: none;">Add Post</a>
                                </button>
                                <button class="btn btn-outline" onclick="document.getElementById('image-upload').click()">
                                    <i class="fas fa-image"></i> Upload Image
                                </button>
                                <input type="file" id="image-upload" accept="image/*" style="display: none;" onchange="handleImageUpload(this)">
                            </div>
                            <div id="image-preview" style="margin-top: 10px; display: none;">
                                <div style="position: relative; display: inline-block;">
                                    <img id="preview" src="#" alt="Preview" style="max-width: 300px; max-height: 200px; border-radius: 4px;">
                                    <button type="button" class="btn btn-outline btn-danger" style="position: absolute; top: 5px; right: 5px;" onclick="removeImage()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <p style="margin-top: 5px; font-size: 12px; color: var(--text-light);">
                                    Image will be attached to your next post
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function handleImageUpload(input) {
                    const preview = document.getElementById('preview');
                    const imagePreview = document.getElementById('image-preview');
                    
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            imagePreview.style.display = 'block';
                            
                            // Upload the image
                            const formData = new FormData();
                            formData.append('image', input.files[0]);
                            
                            // Show loading state
                            const uploadButton = document.querySelector('.btn-outline i.fa-image').parentElement;
                            const originalText = uploadButton.innerHTML;
                            uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                            uploadButton.disabled = true;
                            
                            fetch('upload_image.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('Image uploaded successfully:', data.path);
                                    // Store the image path for later use
                                    window.uploadedImagePath = data.path;
                                    alert('Image uploaded successfully! It will be attached to your next post.');
                                } else {
                                    console.error('Upload failed:', data.error);
                                    alert('Failed to upload image: ' + data.error);
                                    removeImage();
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error uploading image. Please try again.');
                                removeImage();
                            })
                            .finally(() => {
                                // Reset button state
                                uploadButton.innerHTML = originalText;
                                uploadButton.disabled = false;
                            });
                        }
                        
                        reader.readAsDataURL(input.files[0]);
                    }
                }

                function removeImage() {
                    const imageInput = document.getElementById('image-upload');
                    const imagePreview = document.getElementById('image-preview');
                    const preview = document.getElementById('preview');
                    
                    imageInput.value = '';
                    preview.src = '#';
                    imagePreview.style.display = 'none';
                    window.uploadedImagePath = null;
                }
                </script>

                <!-- Feed filters -->
                <div class="tabs" style="margin: 15px 0;">
                    <button class="tab active">Popular</button>
                    <button class="tab">Recent</button>
                    <button class="tab">Following</button>
                </div>

                <?php if (isset($error)): ?>
                    <div class="error-message" style="color: red; background-color: #ffebee; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($posts)): ?>
                    <div class="card">
                        <p style="text-align: center; color: var(--text-light);">No posts available. Be the first to create one!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post">
                            <div class="post-header">
                                <div class="post-avatar"></div>
                                <div class="post-meta">
                                    <h4 class="post-author"><?= htmlspecialchars($post['author']) ?></h4>
                                    <p class="post-time"><?= date('F j, Y g:i a', strtotime($post['created_at'])) ?></p>
                                </div>
                                <div class="post-actions" style="margin-left: auto; display: flex; gap: 10px;">
                                    <a href="edit.php?id=<?= htmlspecialchars($post['post_id']) ?>" 
                                       class="btn btn-outline" style="font-size: 12px; padding: 4px 8px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete.php?id=<?= htmlspecialchars($post['post_id']) ?>" 
                                       class="btn btn-outline" style="font-size: 12px; padding: 4px 8px; color: #ff4757; border-color: #ff4757;"
                                       onclick="return confirm('Are you sure you want to delete this post?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                            <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                            <p class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                            <button class="btn btn-outline translate-btn" 
                                    data-text="<?= htmlspecialchars($post['content']) ?>"
                                    style="font-size: 12px; padding: 4px 8px; margin-top: 5px;">
                                <i class="fas fa-language"></i> Translate to French
                            </button>
                            <div class="translation-result" style="display: none; margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;"></div>
                            <?php if (!empty($post['image_path'])): ?>
                                <div class="post-image" style="margin: 15px 0;">
                                    <?php
                                    $image_path = '../../' . $post['image_path'];
                                    error_log("Attempting to display image:");
                                    error_log("Image path from DB: " . $post['image_path']);
                                    error_log("Full image path: " . $image_path);
                                    error_log("File exists: " . (file_exists($image_path) ? 'yes' : 'no'));
                                    error_log("File readable: " . (is_readable($image_path) ? 'yes' : 'no'));
                                    
                                    if (file_exists($image_path)) {
                                        echo '<img src="' . htmlspecialchars($image_path) . '" 
                                             alt="Post image" 
                                             style="max-width: 100%; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                                             onerror="this.onerror=null; this.src=\'../../assets/placeholder.png\'; console.log(\'Image failed to load: \' + this.src);">';
                                    } else {
                                        echo '<div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 8px;">
                                                <i class="fas fa-image" style="font-size: 24px; color: #6c757d;"></i>
                                                <p style="margin-top: 10px; color: #6c757d;">Image not found</p>
                                                <p style="font-size: 12px; color: #999;">Path: ' . htmlspecialchars($image_path) . '</p>
                                              </div>';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                            <div class="post-actions">
                                <div class="action-buttons">
                                    <?php
                                    $reactions = $postController->getPostReactions($post['post_id']);
                                    $userReaction = $postController->getUserReaction($post['post_id'], 'anonymous');
                                    $isSaved = $postController->isPostSaved($post['post_id'], 'anonymous');
                                    ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['post_id']) ?>">
                                        <button type="submit" name="action" value="like" 
                                                class="action-button <?= $userReaction === 'like' ? 'active' : '' ?>"
                                                style="color: <?= $userReaction === 'like' ? 'var(--primary)' : 'var(--text-light)' ?>">
                                            <i class="fas fa-thumbs-up"></i> 
                                            <span><?= $reactions['likes'] ?? 0 ?></span>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['post_id']) ?>">
                                        <button type="submit" name="action" value="dislike"
                                                class="action-button <?= $userReaction === 'dislike' ? 'active' : '' ?>"
                                                style="color: <?= $userReaction === 'dislike' ? '#ff4757' : 'var(--text-light)' ?>">
                                            <i class="fas fa-thumbs-down"></i>
                                            <span><?= $reactions['dislikes'] ?? 0 ?></span>
                                        </button>
                                    </form>
                                    <a href="commentaire.php?id=<?= htmlspecialchars($post['post_id']) ?>" class="action-button">
                                        <i class="far fa-comment"></i> 
                                        <?php 
                                            $comments = $postController->getComments($post['post_id']);
                                            echo count($comments);
                                        ?>
                                    </a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['post_id']) ?>">
                                        <button type="submit" name="action" value="save" 
                                                class="action-button <?= $isSaved ? 'active' : '' ?>"
                                                style="color: <?= $isSaved ? 'var(--primary)' : 'var(--text-light)' ?>">
                                            <i class="far fa-bookmark"></i>
                                        </button>
                                    </form>
                                    <button class="action-button">Share</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Right Column - Forums & Resources -->
            <div style="width: 25%;">
                <!-- Popular Forums -->
                <div class="card">
                    <h3>Popular Forums</h3>
                    <div class="group-item">
                        <div class="group-avatar" style="background-color: var(--primary-light);"></div>
                        <div class="group-info">
                            <p class="group-name">Tactical Urbanism</p>
                            <p class="group-meta">37 active discussions</p>
                        </div>
                    </div>
                    <div class="group-item">
                        <div class="group-avatar" style="background-color: var(--primary-light);"></div>
                        <div class="group-info">
                            <p class="group-name">Energy Renovation</p>
                            <p class="group-meta">28 active discussions</p>
                        </div>
                    </div>
                    <div class="group-item">
                        <div class="group-avatar" style="background-color: var(--primary-light);"></div>
                        <div class="group-info">
                            <p class="group-name">Citizen Participation</p>
                            <p class="group-meta">29 active discussions</p>
                        </div>
                    </div>
                </div>

                <!-- Suggestions -->
                <div class="card">
                    <h3>Suggestions</h3>
                    <p style="color: var(--text-light); font-size: 12px;">People to follow</p>
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; margin: 15px 0;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="profile-avatar" style="width: 40px; height: 40px;"></div>
                            <div>
                                <p style="font-weight: 500; margin: 0;">Marie Lambert</p>
                                <p style="color: var(--text-light); font-size: 12px; margin: 0;">Landscape Architect</p>
                            </div>
                        </div>
                        <button class="btn btn-outline" style="font-size: 12px; padding: 4px 12px;">Follow</button>
                    </div>
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; margin: 15px 0;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="profile-avatar" style="width: 40px; height: 40px;"></div>
                            <div>
                                <p style="font-weight: 500; margin: 0;">Lucas Renaud</p>
                                <p style="color: var(--text-light); font-size: 12px; margin: 0;">Landscape Architect</p>
                            </div>
                        </div>
                        <button class="btn btn-outline" style="font-size: 12px; padding: 4px 12px;">Follow</button>
                    </div>
                </div>

                <!-- Resources -->
                <div class="card">
                    <h3>Resources</h3>
                    <div class="group-item">
                        <div class="group-avatar" style="background-color: #f1f1f1; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 10px; color: var(--text-medium);">PDF</span>
                        </div>
                        <div class="group-info">
                            <p class="group-name">3D Model Library</p>
                            <p class="group-meta">20 items</p>
                        </div>
                    </div>
                    <div class="group-item">
                        <div class="group-avatar" style="background-color: #f1f1f1; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 10px; color: var(--text-medium);">PDF</span>
                        </div>
                        <div class="group-info">
                            <p class="group-name">Urban Planning Regulations</p>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const translateButtons = document.querySelectorAll('.translate-btn');
        
        translateButtons.forEach(button => {
            button.addEventListener('click', async function() {
                const text = this.dataset.text;
                const resultDiv = this.nextElementSibling;
                
                // Show loading state
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Translating...';
                
                try {
                    const response = await fetch('cont.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=translate&text=${encodeURIComponent(text)}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        resultDiv.innerHTML = `<p><strong>French Translation:</strong><br>${result.translated_text}</p>`;
                        resultDiv.style.display = 'block';
                    } else {
                        resultDiv.innerHTML = `<p style="color: red;">Translation failed: ${result.error}</p>`;
                        resultDiv.style.display = 'block';
                    }
                } catch (error) {
                    resultDiv.innerHTML = '<p style="color: red;">Error occurred while translating</p>';
                    resultDiv.style.display = 'block';
                } finally {
                    // Reset button state
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-language"></i> Translate to French';
                }
            });
        });
    });
    </script>
</body>
</html> 