<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
session_start();

// Add this near the top of the file after session_start():
if (!extension_loaded('gd')) {
    error_log("Warning: GD library is not installed. Images will not be resized.");
}

// Clean up paths using consistent directory separators
$base_dir = dirname(__DIR__); // Get parent directory path
$error_message = '';
$blog_file = $base_dir . '/blog.json';
$posts_dir = $base_dir . '/posts';
$images_dir = $base_dir . '/images';

// Debug logging
error_log("Base directory: " . $base_dir);
error_log("Posts directory: " . $posts_dir);
error_log("Images directory: " . $images_dir);

// Ensure directories exist with proper permissions for Linux Apache
try {
    foreach ([$posts_dir, $images_dir] as $dir) {
        if (!file_exists($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                error_log("Failed to create directory: " . $dir);
                throw new Exception('Failed to create directory: ' . $dir);
            }
            // For Linux: set owner to www-data
            @chown($dir, 'www-data');
            @chgrp($dir, 'www-data');
        }
        // Ensure proper permissions
        @chmod($dir, 0755);
    }
} catch (Exception $e) {
    $error_message = 'Error: ' . $e->getMessage();
    error_log($error_message);
}

// Handle blog.json creation and permissions
if (!file_exists($blog_file)) {
    if (@file_put_contents($blog_file, json_encode([], JSON_PRETTY_PRINT)) === false) {
        die('Error: Unable to create blog file');
    }
    chmod($blog_file, 0664);
}

// Ensure blog file is writable
if (!is_writable($blog_file)) {
    chmod($blog_file, 0664);
}

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === 'LOL123321') {
        $_SESSION['admin'] = true;
    }
}

// Add this function before the form handling code
function resizeImage($sourcePath, $destinationPath, $maxWidth = 1920, $maxHeight = 1080, $quality = 80) {
    // Check if GD is available
    if (!extension_loaded('gd')) {
        // If GD is not available, just copy the file
        if (copy($sourcePath, $destinationPath)) {
            return true;
        }
        return false;
    }

    // Rest of the resizeImage function
    list($origWidth, $origHeight, $type) = getimagesize($sourcePath);
    
    // Don't resize if image is smaller than max dimensions
    if ($origWidth <= $maxWidth && $origHeight <= $maxHeight) {
        return copy($sourcePath, $destinationPath);
    }
    
    // Original resizeImage code continues here...
    // Calculate new dimensions while maintaining aspect ratio
    $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
    $newWidth = round($origWidth * $ratio);
    $newHeight = round($origHeight * $ratio);
    
    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Handle different image types
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            // Preserve transparency
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }
    
    // Resize
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    
    // Save
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $destinationPath, $quality);
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $destinationPath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($newImage, $destinationPath);
            break;
    }
    
    // Clean up
    imagedestroy($newImage);
    imagedestroy($source);
    
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['content']) || isset($_FILES['images']))) {
    if (!$_SESSION['admin']) {
        $error_message = 'Unauthorized';
    } else {
        try {
            $post_id = 'post_' . uniqid(true);
            
            // Handle image upload
            $image_urls = [];
            if (isset($_FILES['images'])) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['images']['name'][$key]);
                        $filepath = $images_dir . '/' . $filename;
                        
                        // Debug logging
                        error_log("Full upload path: " . $filepath);
                        
                        // Verify MIME type
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $tmp_name);
                        finfo_close($finfo);
                        
                        if (strpos($mime_type, 'image/') === 0) {
                            // Resize and save the image
                            if (resizeImage($tmp_name, $filepath)) {
                                chmod($filepath, 0644);
                                // Set proper ownership on Linux
                                @chown($filepath, 'www-data');
                                @chgrp($filepath, 'www-data');
                                
                                $image_urls[] = '/blog/images/' . $filename;
                            } else {
                                error_log("Failed to resize image: " . $filename);
                                throw new Exception('Failed to resize image: ' . $filename);
                            }
                        }
                    }
                }
            }
            
            // Only create post if there's content or images
            if (!empty($_POST['content']) || !empty($image_urls)) {
                $new_post = [
                    'id' => $post_id,
                    'date' => date('Y-m-d'),
                    'time' => date('H:i:s', strtotime('+2 hours')),
                    'content' => $_POST['content'] ?? '',
                    'images' => $image_urls
                ];
                
                $post_file = $posts_dir . '/' . $post_id . '.json';
                if (file_put_contents($post_file, json_encode($new_post, JSON_PRETTY_PRINT)) === false) {
                    throw new Exception('Failed to write post file');
                }
                chmod($post_file, 0644);
                
                if (empty($error_message)) {
                    header('Location: /blog/');
                    exit;
                }
            } else {
                $error_message = 'Post must contain either text or images';
            }
        } catch (Exception $e) {
            $error_message = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>simon1g</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/styles.css">
    <link rel="icon" href="/icon.ico">
    <link rel="stylesheet" href="/blog/blog.css">
</head>
<body>
    <div class="navbar">
        <?php 
        include(get_site_path('/navbar.html')); 
        include(get_site_path('/ascii_border.php'));
        #include(get_site_path('/matrix_effect.php'));
        ?>
    </div>
    <div class="blog-content">
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!isset($_SESSION['admin'])): ?>
            <form method="POST" class="admin-form">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        <?php else: ?>
            <form method="POST" class="post-form" enctype="multipart/form-data">
                <textarea name="content" placeholder="Write your entry here..."></textarea>
                <input type="file" name="images[]" accept="image/*" multiple>
                <button type="submit">Post Entry</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
