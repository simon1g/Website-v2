<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
session_start();

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
                        
                        // Verify MIME type
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $tmp_name);
                        finfo_close($finfo);
                        
                        if (strpos($mime_type, 'image/') === 0) {
                            if (move_uploaded_file($tmp_name, $filepath)) {
                                chmod($filepath, 0644);
                                $image_urls[] = '/blog/images/' . $filename;
                            } else {
                                throw new Exception('Failed to move uploaded image: ' . $filename);
                            }
                        }
                    }
                }
            }
            
            // Only create post if there's content or images
            if (!empty($_POST['content']) || !empty($image_urls)) {
                // Get client date and time
                $date = $_POST['client_date'];
                $time = $_POST['client_time'];
                
                // Validate date format
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    throw new Exception('Invalid date format');
                }
                // Validate time format
                if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                    throw new Exception('Invalid time format');
                }
                
                $new_post = [
                    'id' => $post_id,
                    'date' => $date,
                    'time' => $time,
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
                <input type="hidden" name="client_date" id="client_date" required>
                <input type="hidden" name="client_time" id="client_time" required>
                <button type="submit">Post Entry</button>
            </form>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.querySelector('.post-form');
                    form.addEventListener('submit', function(e) {
                        const now = new Date();
                        const date = now.toISOString().split('T')[0];
                        const time = now.toTimeString().split(' ')[0];
                        
                        const dateField = document.getElementById('client_date');
                        const timeField = document.getElementById('client_time');
                        
                        if (!dateField.value) dateField.value = date;
                        if (!timeField.value) timeField.value = time;
                    });
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
