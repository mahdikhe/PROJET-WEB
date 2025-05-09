<?php
require_once dirname(__DIR__, 2) . '/controller/Controller.php';
require_once dirname(__DIR__, 2) . '/model/model.php';

error_log("Starting add.php script...");

$postController = new PostController();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));
    
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        error_log("Add post action detected");
        try {
            // Validate input
            if (empty($_POST['title']) || empty($_POST['content']) || empty($_POST['author'])) {
                error_log("Validation failed: Empty fields detected");
                throw new Exception("All fields are required");
            }

            // Sanitize and validate input
            $title = filter_var(trim($_POST['title']), FILTER_SANITIZE_STRING);
            $content = filter_var(trim($_POST['content']), FILTER_SANITIZE_STRING);
            $author = filter_var(trim($_POST['author']), FILTER_SANITIZE_STRING);

            // Validate title (letters, numbers, spaces only)
            if (!preg_match('/^[a-zA-Z0-9\s]+$/', $title)) {
                throw new Exception("Title can only contain letters, numbers and spaces");
            }

            // Validate author (letters, numbers, spaces only)
            if (!preg_match('/^[a-zA-Z0-9\s]+$/', $author)) {
                throw new Exception("Author name can only contain letters, numbers and spaces");
            }

            // Validate content (letters, numbers, spaces only)
            if (!preg_match('/^[a-zA-Z0-9\s]+$/', $content)) {
                throw new Exception("Content can only contain letters, numbers and spaces");
            }

            error_log("Sanitized input:");
            error_log("Title: " . $title);
            error_log("Content: " . $content);
            error_log("Author: " . $author);

            if (empty($title) || empty($content) || empty($author)) {
                error_log("Validation failed: Empty fields after sanitization");
                throw new Exception("Invalid input data");
            }

            // Handle image upload if present
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = dirname(__DIR__, 2) . '/uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowed_extensions));
                }
                
                $max_file_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['image']['size'] > $max_file_size) {
                    throw new Exception("File size too large. Maximum size is 5MB.");
                }
                
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_path = 'uploads/' . $new_filename;
                    error_log("Image uploaded successfully. Path: " . $image_path);
                    error_log("Full upload path: " . $upload_path);
                    error_log("Upload directory exists: " . (file_exists($upload_dir) ? 'yes' : 'no'));
                    error_log("Upload directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4));
                } else {
                    error_log("Failed to upload image. Upload path: " . $upload_path);
                    error_log("Upload error: " . $_FILES['image']['error']);
                    error_log("Upload directory writable: " . (is_writable($upload_dir) ? 'yes' : 'no'));
                    throw new Exception("Failed to upload image.");
                }
            }

            // Create post object
            error_log("Creating Post object with image path: " . ($image_path ?? 'no image'));
            $post = new Post($title, $content, $author);
            
            // Add image path if available in session
            if (isset($_SESSION['uploaded_image'])) {
                error_log("Found uploaded image in session: " . $_SESSION['uploaded_image']);
                $post->setImagePath($_SESSION['uploaded_image']);
                unset($_SESSION['uploaded_image']); // Clear the session after using it
            }
            
            error_log("Post object created successfully");
            
            // Add post to database
            error_log("Attempting to add post to database...");
            $result = $postController->addPost($post);
            
            if ($result) {
                error_log("Post added successfully with ID: " . $result);
                header("Location: cont.php");
                exit();
            } else {
                error_log("Failed to add post: No result returned");
                throw new Exception("Failed to add post to database");
            }
        } catch (Exception $e) {
            error_log("Error in add.php: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            $error = $e->getMessage();
        }
    } else {
        error_log("Invalid action: " . ($_POST['action'] ?? 'none'));
    }
} else {
    error_log("Not a POST request");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post - CityPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../style1.css">
    <style>
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25);
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .alert-danger h4 {
            margin-top: 0;
            color: #721c24;
        }
        .alert-danger ul {
            margin-bottom: 0;
        }
        .btn-outline {
            border: 1px solid #dc3545;
            color: #dc3545;
            background: transparent;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-outline:hover {
            background: #dc3545;
            color: white;
        }
        .mt-2 {
            margin-top: 0.5rem;
        }
        .mt-3 {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="cont.php" class="logo">
                <img src="../../assets/logo.png" alt="CityPulse Logo" style="height: 35px; margin-right: 10px;">
                CityPulse
            </a>
            <nav class="main-nav">
                <a href="cont.php">Posts</a>
                <a href="event.html">Events</a>
                <a href="forums.html">Forums</a>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 40px;">
        <div class="card">
            <h2 class="card-title">Add New Post</h2>
            <?php if ($error): ?>
                <div class="error-message" style="color: red; background-color: #ffebee; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="form-section" id="addPostForm" onsubmit="return validateForm()" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="title">Post Title</label>
                    <input type="text" id="title" name="title" class="form-control" required 
                           value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>"
                           maxlength="255">
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="form-group">
                    <label for="content">Post Content</label>
                    <div style="position: relative;">
                        <textarea id="content" name="content" class="form-control" rows="5" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                        <div class="invalid-feedback"></div>
                        <button type="button" id="voiceButton" class="btn btn-primary" style="position: absolute; right: 10px; top: 10px;">
                            <i class="fas fa-microphone"></i> Start Voice Input
                        </button>
                        <div id="voiceStatus" style="position: absolute; right: 10px; top: 50px; font-size: 12px; color: #666;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="author">Author Name</label>
                    <input type="text" id="author" name="author" class="form-control" required
                           value="<?= isset($_POST['author']) ? htmlspecialchars($_POST['author']) : '' ?>"
                           maxlength="100">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="window.location.href='cont.php'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Post</button>
                </div>
            </form>
        </div>
    </main>

    <script>
    // Validation functions
    function validateField(input, type, options = {}) {
        const value = input.value.trim();
        let isValid = true;
        let errorMessage = "";

        switch(type) {
            case "title":
                if (value === "") {
                    isValid = false;
                    errorMessage = "Please enter a title";
                } else if (value.length < 3) {
                    isValid = false;
                    errorMessage = "Title must be at least 3 characters long";
                } else if (!/^[a-zA-Z0-9\s]+$/.test(value)) {
                    isValid = false;
                    errorMessage = "Title can only contain letters, numbers and spaces. Please try again.";
                }
                break;
            case "content":
                if (value === "") {
                    isValid = false;
                    errorMessage = "Please enter content";
                } else if (value.length < 10) {
                    isValid = false;
                    errorMessage = "Content must be at least 10 characters long";
                } else if (!/^[a-zA-Z0-9\s]+$/.test(value)) {
                    isValid = false;
                    errorMessage = "Content can only contain letters, numbers and spaces. Please try again.";
                }
                break;
            case "author":
                if (value === "") {
                    isValid = false;
                    errorMessage = "Please enter author name";
                } else if (value.length < 2) {
                    isValid = false;
                    errorMessage = "Author name must be at least 2 characters long";
                } else if (!/^[a-zA-Z0-9\s]+$/.test(value)) {
                    isValid = false;
                    errorMessage = "Author name can only contain letters, numbers and spaces. Please try again.";
                }
                break;
        }

        if (!isValid) {
            input.classList.add("is-invalid");
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains("invalid-feedback")) {
                feedback.textContent = errorMessage;
                feedback.style.display = "block";
            }
            
            // Add retry button
            const retryButton = document.createElement('button');
            retryButton.type = 'button';
            retryButton.className = 'btn btn-outline btn-sm mt-2';
            retryButton.innerHTML = '<i class="fas fa-redo"></i> Try Again';
            retryButton.onclick = function() {
                input.value = '';
                input.focus();
                input.classList.remove("is-invalid");
                feedback.style.display = "none";
                this.remove();
            };
            
            // Remove any existing retry button
            const existingRetry = feedback.nextElementSibling;
            if (existingRetry && existingRetry.classList.contains('btn-outline')) {
                existingRetry.remove();
            }
            
            feedback.parentNode.insertBefore(retryButton, feedback.nextSibling);
        } else {
            input.classList.remove("is-invalid");
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains("invalid-feedback")) {
                feedback.style.display = "none";
            }
            // Remove retry button if it exists
            const retryButton = feedback.nextElementSibling;
            if (retryButton && retryButton.classList.contains('btn-outline')) {
                retryButton.remove();
            }
        }

        return isValid;
    }

    // Form Validation Setup
    function setupFormValidation(formId, validationRules) {
        const form = document.getElementById(formId);
        if (!form) return;

        // Add event listeners for real-time validation
        Object.entries(validationRules).forEach(([fieldId, rules]) => {
            const input = document.getElementById(fieldId);
            if (input) {
                // Validate on blur
                input.addEventListener("blur", () => validateField(input, rules.type, rules.options));
                // Validate on input change
                input.addEventListener("input", () => {
                    if (input.classList.contains("is-invalid")) {
                        validateField(input, rules.type, rules.options);
                    }
                });
            }
        });

        // Form submission validation
        form.addEventListener("submit", function(e) {
            let isValid = true;
            const errorMessages = [];

            Object.entries(validationRules).forEach(([fieldId, rules]) => {
                const input = document.getElementById(fieldId);
                if (input && !validateField(input, rules.type, rules.options)) {
                    isValid = false;
                    errorMessages.push(input.getAttribute("data-error-message") || "Invalid input");
                }
            });

            if (!isValid) {
                e.preventDefault();
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = `
                    <h4><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h4>
                    <ul>
                        ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
                    </ul>
                    <button class="btn btn-outline btn-sm mt-2" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i> Dismiss
                    </button>
                `;
                
                // Remove any existing error message
                const existingError = form.querySelector('.alert-danger');
                if (existingError) {
                    existingError.remove();
                }
                
                form.insertBefore(errorDiv, form.firstChild);
            }
        });
    }

    // Setup validation for add post form
    document.addEventListener('DOMContentLoaded', function() {
        setupFormValidation('addPostForm', {
            'title': { type: 'title' },
            'content': { type: 'content' },
            'author': { type: 'author' }
        });
    });

    // Voice recognition functionality
    const voiceButton = document.getElementById('voiceButton');
    const contentTextarea = document.getElementById('content');
    const voiceStatus = document.getElementById('voiceStatus');
    let recognition = null;

    // Initialize speech recognition
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = 'en-US';

        // Handle recognition results
        recognition.onresult = function(event) {
            let text = '';
            for (let i = 0; i < event.results.length; i++) {
                text += event.results[i][0].transcript;
            }
            contentTextarea.value = text;
        };

        // Handle errors
        recognition.onerror = function(event) {
            voiceStatus.textContent = 'Error: ' + event.error;
            voiceStatus.style.color = 'red';
            voiceButton.innerHTML = '<i class="fas fa-microphone"></i> Start Voice Input';
            voiceButton.classList.remove('btn-danger');
        };

        // Handle recognition end
        recognition.onend = function() {
            voiceButton.innerHTML = '<i class="fas fa-microphone"></i> Start Voice Input';
            voiceButton.classList.remove('btn-danger');
            voiceStatus.textContent = 'Click to start voice input';
        };

        // Toggle voice recognition
        voiceButton.addEventListener('click', function() {
            if (voiceButton.innerHTML.includes('Start')) {
                try {
                    recognition.start();
                    voiceButton.innerHTML = '<i class="fas fa-stop"></i> Stop Voice Input';
                    voiceButton.classList.add('btn-danger');
                    voiceStatus.textContent = 'Listening...';
                    voiceStatus.style.color = 'green';
                } catch (error) {
                    voiceStatus.textContent = 'Error starting voice input';
                    voiceStatus.style.color = 'red';
                }
            } else {
                recognition.stop();
                voiceButton.innerHTML = '<i class="fas fa-microphone"></i> Start Voice Input';
                voiceButton.classList.remove('btn-danger');
                voiceStatus.textContent = 'Voice input stopped';
            }
        });
    } else {
        voiceButton.style.display = 'none';
        voiceStatus.textContent = 'Voice input is not supported in your browser';
        voiceStatus.style.color = 'red';
    }

    // Image preview functionality
    function previewImage(input) {
        const preview = document.getElementById('preview');
        const imagePreview = document.getElementById('imagePreview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                imagePreview.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeImage() {
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const preview = document.getElementById('preview');
        
        imageInput.value = '';
        preview.src = '#';
        imagePreview.style.display = 'none';
    }

    // Form validation
    function validateForm() {
        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();
        const author = document.getElementById('author').value.trim();
        const imageInput = document.getElementById('image');

        if (!title || !content || !author) {
            alert('All fields are required');
            return false;
        }

        if (title.length > 255) {
            alert('Title is too long (maximum 255 characters)');
            return false;
        }

        if (author.length > 100) {
            alert('Author name is too long (maximum 100 characters)');
            return false;
        }

        // Validate image if one is selected
        if (imageInput.files.length > 0) {
            const file = imageInput.files[0];
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
                return false;
            }

            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                alert('Image size should be less than 5MB');
                return false;
            }
        }

        return true;
    }
    </script>
</body>
</html> 