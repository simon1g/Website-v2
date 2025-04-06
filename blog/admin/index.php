<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
session_start();

// Clean up paths using consistent directory separators
$base_dir = dirname(__DIR__); // Get parent directory path
$error_message = '';
// Note: blog.json isn't used in *this* script for reading/writing posts list,
// only for creating individual post files. Keep if needed elsewhere.
// $blog_file = $base_dir . '/blog.json';
$posts_dir = $base_dir . '/posts';
$images_dir = $base_dir . '/images';

// Debug logging
error_log("Base directory: " . $base_dir);
error_log("Posts directory: " . $posts_dir);
error_log("Images directory: " . $images_dir);

// Ensure directories exist with proper permissions for Linux Apache
try {
    foreach ([$posts_dir, $images_dir] as $dir) {
        if (!is_dir($dir)) { // More robust check than file_exists for directories
            if (!@mkdir($dir, 0755, true)) {
                $last_error = error_get_last();
                $error_details = $last_error ? ' (' . $last_error['message'] . ')' : '';
                error_log("Failed to create directory: " . $dir . $error_details);
                throw new Exception('Failed to create directory: ' . $dir . $error_details);
            }
            error_log("Created directory: " . $dir);
            // Set owner/group if possible (might fail depending on permissions)
            @chown($dir, 'www-data');
            @chgrp($dir, 'www-data');
            // Set permissions after creation
            @chmod($dir, 0755);
        } else {
            // Ensure proper permissions even if it exists
             if (!@chmod($dir, 0755)) {
                 error_log("Failed to set permissions on existing directory: " . $dir);
                 // Optionally throw an exception if permissions are critical
             }
        }

        // Double check writability for the web server
        if (!is_writable($dir)) {
             error_log("Directory not writable by web server: " . $dir);
             // Optionally throw an exception if writability is critical immediately
             // throw new Exception('Directory not writable: ' . $dir);
        }
    }
} catch (Exception $e) {
    $error_message = 'Error setting up directories: ' . $e->getMessage();
    error_log($error_message);
    // Consider dying here if directories are essential for the page to function
    // die($error_message);
}

/*
// Handle blog.json creation and permissions - Keep if needed for other parts of the site
$blog_file = $base_dir . '/blog.json'; // Define it here if needed
if (!file_exists($blog_file)) {
    if (@file_put_contents($blog_file, json_encode([], JSON_PRETTY_PRINT)) === false) {
        // Handle error - maybe log and set error message
        error_log("Error: Unable to create blog file: " . $blog_file);
        $error_message .= ' Error: Unable to create blog file.';
    } else {
        @chmod($blog_file, 0664); // Set permissions after creation
    }
} elseif (!is_writable($blog_file)) { // Ensure blog file is writable if it exists
    if (!@chmod($blog_file, 0664)) {
         error_log("Error: Unable to set blog file writable: " . $blog_file);
         $error_message .= ' Error: Unable to make blog file writable.';
    }
}
*/

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === 'LOL123321') { // Consider using a more secure password hashing mechanism
        $_SESSION['admin'] = true;
        // Redirect after successful login to prevent form resubmission
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $error_message = 'Invalid password';
    }
}

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['content']) || isset($_FILES['images']))) {
    // Check if admin session exists AND the password field is NOT set (to avoid processing login and post simultaneously)
    if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
        $error_message = 'Unauthorized: Please log in.';
    } elseif (isset($_POST['password'])) {
         // Don't process post if it was a login attempt
    } else {
        try {
            $post_id = 'post_' . uniqid(true);

            // Handle image upload
            $image_urls = [];
            if (isset($_FILES['images'])) {
                if (!is_writable($images_dir)) {
                     throw new Exception('Images directory is not writable by the web server.');
                }
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    // Check for upload errors first
                    if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                         if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_NO_FILE) { // Ignore "no file" errors if multiple uploads are allowed
                            throw new Exception('Error uploading file: ' . $_FILES['images']['name'][$key] . ' (Error code: ' . $_FILES['images']['error'][$key] . ')');
                         }
                         continue; // Skip this file if no file was uploaded or another error occured
                    }

                    // Basic check for non-empty file name and existence of temp file
                    if (empty($_FILES['images']['name'][$key]) || !is_uploaded_file($tmp_name)) {
                        continue; // Skip invalid uploads
                    }

                    $filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['images']['name'][$key]));
                    $filepath = $images_dir . '/' . $filename;

                    // Verify MIME type using PHP's finfo
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if (!$finfo) {
                        throw new Exception('Failed to open fileinfo database.');
                    }
                    $mime_type = finfo_file($finfo, $tmp_name);
                    finfo_close($finfo);

                    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
                    if ($mime_type && in_array($mime_type, $allowed_mime_types)) {
                        if (move_uploaded_file($tmp_name, $filepath)) {
                            chmod($filepath, 0644);
                            // Use relative path from web root for URLs
                            $image_urls[] = '/blog/images/' . $filename;
                             error_log("Successfully moved image: " . $filepath);
                        } else {
                            $last_error = error_get_last();
                            $error_details = $last_error ? ' (' . $last_error['message'] . ')' : '';
                            error_log('Failed to move uploaded image: ' . $filename . $error_details . ' to ' . $filepath);
                            throw new Exception('Failed to move uploaded image: ' . $filename . $error_details);
                        }
                    } else {
                         error_log('Invalid image MIME type: ' . $mime_type . ' for file ' . $filename);
                         throw new Exception('Invalid file type uploaded: ' . htmlspecialchars($_FILES['images']['name'][$key]) . '. Only JPG, PNG, GIF, WEBP, AVIF allowed.');
                    }
                }
            }

            // Only create post if there's content or images uploaded
            $post_content = trim($_POST['content'] ?? '');
            if (!empty($post_content) || !empty($image_urls)) {

                // *** Get current time in Poland (Warsaw) ***
                try {
                    $timezone = new DateTimeZone('Europe/Warsaw');
                    $now = new DateTime('now', $timezone);
                    $poland_date = $now->format('Y-m-d'); // Format YYYY-MM-DD
                    $poland_time = $now->format('H:i:s'); // Format HH:MM:SS (24-hour)
                } catch (Exception $tz_e) {
                    // Fallback or error handling if timezone fails
                    error_log("Timezone error: " . $tz_e->getMessage() . " - Falling back to server time.");
                    $now = new DateTime('now'); // Use server default time as fallback
                    $poland_date = $now->format('Y-m-d');
                    $poland_time = $now->format('H:i:s');
                    $error_message .= ' Warning: Could not get Poland time, using server time. ';
                }
                // *** End of Poland time retrieval ***


                $new_post = [
                    'id' => $post_id,
                    'date' => $poland_date, // Use Poland date
                    'time' => $poland_time, // Use Poland time
                    'content' => $post_content,
                    'images' => $image_urls
                ];

                if (!is_writable($posts_dir)) {
                     throw new Exception('Posts directory is not writable by the web server.');
                }
                $post_file = $posts_dir . '/' . $post_id . '.json';
                if (file_put_contents($post_file, json_encode($new_post, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
                    $last_error = error_get_last();
                    $error_details = $last_error ? ' (' . $last_error['message'] . ')' : '';
                    error_log('Failed to write post file: ' . $post_file . $error_details);
                    throw new Exception('Failed to write post file.' . $error_details);
                }
                chmod($post_file, 0644);
                error_log("Successfully created post file: " . $post_file);

                // Redirect only if there were NO errors during the process
                if (empty($error_message)) {
                    header('Location: /blog/'); // Redirect to the main blog page
                    exit;
                }
            } else {
                $error_message = 'Post must contain either text or at least one valid uploaded image.';
            }
        } catch (Exception $e) {
            $error_message = 'Error processing post: ' . $e->getMessage();
            error_log($error_message); // Log the detailed error
        }
    }
}

// Handle delete last post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_last') {
    if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
        $error_message = 'Unauthorized: Please log in.';
    } else {
        try {
            $files = glob($posts_dir . '/*.json');
            if (!empty($files)) {
                // Sort files by modification time, newest first
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                $latest_file = $files[0];
                if (unlink($latest_file)) {
                    header('Location: /blog/');
                    exit;
                } else {
                    throw new Exception('Failed to delete the post file.');
                }
            } else {
                $error_message = 'No posts to delete.';
            }
        } catch (Exception $e) {
            $error_message = 'Error deleting post: ' . $e->getMessage();
            error_log($error_message);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>simon1g - New Post</title> <!-- More specific title -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/styles.css">
    <link rel="icon" href="/icon.ico">
    <link rel="stylesheet" href="/blog/blog.css">
    <script src="/blog/js/compression.js"></script>
    <script>
    function confirmDelete() {
        return confirm('Are you sure you want to delete the last post? This action cannot be undone.');
    }
    </script>
</head>
<body>
    <div class="navbar">
        <?php
        // Assuming get_site_path is defined in config.php or globally available
        // Make sure these includes don't output anything before header() calls if redirects happen
        include(get_site_path('/navbar.html'));
        include(get_site_path('/ascii_border.php'));
        // include(get_site_path('/matrix_effect.php')); // Uncomment if needed
        ?>
    </div>
    <div class="blog-content">
        <h1>Create New Blog Entry</h1> <!-- Added heading -->

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['admin'])): ?>
            <form method="POST" class="admin-form">
                <label for="password">Admin Password:</label> <!-- Added label for accessibility -->
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        <?php else: ?>
            <form method="POST" class="post-form" enctype="multipart/form-data">
                <label for="content">Entry Content:</label> <!-- Added label -->
                <textarea id="content" name="content" placeholder="Write your entry here..."></textarea>

                <label for="images">Upload Images (optional):</label> <!-- Added label -->
                <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,image/avif" multiple>
                <small>Allowed types: JPG, PNG, GIF, WEBP, AVIF</small> <br><br> <!-- Info for user -->

                 <!-- No longer need client date/time hidden fields -->
                <!-- <input type="hidden" name="client_date" id="client_date" required> -->
                <!-- <input type="hidden" name="client_time" id="client_time" required> -->

                <button type="submit">Post Entry</button>
            </form>

            <form method="POST" style="margin-top: 20px;" onsubmit="return confirmDelete();">
                <input type="hidden" name="action" value="delete_last">
                <button type="submit" style="background-color: #ff4444;">Delete Last Post</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>