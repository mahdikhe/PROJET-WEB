<?php
session_start();
header('Content-Type: application/json');

try {
    // Debug information
    error_log("Starting image upload process...");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    if (!isset($_FILES['image'])) {
        throw new Exception('No image file was sent');
    }

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $error_message = isset($error_messages[$_FILES['image']['error']]) 
            ? $error_messages[$_FILES['image']['error']] 
            : 'Unknown upload error';
        throw new Exception('Upload error: ' . $error_message);
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = dirname(__DIR__, 2) . '/uploads/';
    error_log("Upload directory path: " . $upload_dir);
    
    if (!file_exists($upload_dir)) {
        error_log("Creating uploads directory...");
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception('Failed to create uploads directory');
        }
        error_log("Uploads directory created successfully");
    }

    // Check directory permissions
    if (!is_writable($upload_dir)) {
        error_log("Upload directory is not writable. Current permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4));
        throw new Exception('Upload directory is not writable');
    }

    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowed_extensions));
    }
    
    $max_file_size = 5 * 1024 * 1024; // 5MB
    if ($_FILES['image']['size'] > $max_file_size) {
        throw new Exception('File size too large. Maximum size is 5MB.');
    }
    
    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    error_log("Attempting to save file to: " . $upload_path);
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        error_log("File uploaded successfully to: " . $upload_path);
        $image_path = 'uploads/' . $new_filename;
        
        // Store the image path in session
        $_SESSION['uploaded_image'] = $image_path;
        
        echo json_encode([
            'success' => true,
            'path' => $image_path
        ]);
    } else {
        error_log("Failed to move uploaded file. PHP error: " . error_get_last()['message']);
        throw new Exception('Failed to move uploaded file. Please check server permissions.');
    }
} catch (Exception $e) {
    error_log("Upload error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 