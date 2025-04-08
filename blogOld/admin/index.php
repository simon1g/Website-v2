<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
session_start();

$base_dir = dirname(__DIR__);
$error_message = '';
$posts_dir = $base_dir . '/posts';
$images_dir = $base_dir . '/images';

error_log("Base directory: " . $base_dir);
error_log("Posts directory: " . $posts_dir);
error_log("Images directory: " . $images_dir);

try {
    foreach ([$posts_dir, $images_dir] as $dir) {
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                $last_error = error_get_last();
                $error_details = $last_error ? ' (' . $last_error['message'] . ')' : '';
                error_log("Failed to create directory: " . $dir . $error_details);
                throw new Exception('Failed to create directory: ' . $dir . $error_details);
            }
            error_log("Created directory: " . $dir);
            @chown($dir, 'www-data');
            @chgrp($dir, 'www-data');
            @chmod($dir, 0755);
        } else {
             if (!@chmod($dir, 0755)) {
                 error_log("Failed to set permissions on existing directory: " . $dir);
             }
        }

        if (!is_writable($dir)) {
             error_log("Directory not writable by web server: " . $dir);
        }
    }
} catch (Exception $e) {
    $error_message = 'Error setting up directories: ' . $e->getMessage();
    error_log($error_message);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === 'LOL123321') {
        $_SESSION['admin'] = true;
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $error_message = 'Invalid password';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['content']) || isset($_FILES['images']))) {
    if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
        $error_message = 'Unauthorized: Please log in.';
    } elseif (isset($_POST['password'])) {
    } else {
        try {
            $post_id = 'post_' . uniqid(true);
            $image_urls = [];
            if (isset($_FILES['images'])) {
                if (!is_writable($images_dir)) {
                     throw new Exception('Images directory is not writable by the web server.');
                }
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                         if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                            throw new Exception('Error uploading file: ' . $_FILES['images']['name'][$key] . ' (Error code: ' . $_FILES['images']['error'][$key] . ')');
                         }
                         continue;
                    }

                    if (empty($_FILES['images']['name'][$key]) || !is_uploaded_file($tmp_name)) {
                        continue;
                    }

                    $filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['images']['name'][$key]));
                    $filepath = $images_dir . '/' . $filename;

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

            $post_content = trim($_POST['content'] ?? '');
            if (!empty($post_content) || !empty($image_urls)) {
                try {
                    $timezone = new DateTimeZone('Europe/Warsaw');
                    $now = new DateTime('now', $timezone);
                    $poland_date = $now->format('Y-m-d');
                    $poland_time = $now->format('H:i:s');
                } catch (Exception $tz_e) {
                    error_log("Timezone error: " . $tz_e->getMessage() . " - Falling back to server time.");
                    $now = new DateTime('now');
                    $poland_date = $now->format('Y-m-d');
                    $poland_time = $now->format('H:i:s');
                    $error_message .= ' Warning: Could not get Poland time, using server time. ';
                }

                $new_post = [
                    'id' => $post_id,
                    'date' => $poland_date,
                    'time' => $poland_time,
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

                if (empty($error_message)) {
                    header('Location: /blog/');
                    exit;
                }
            } else {
                $error_message = 'Post must contain either text or at least one valid uploaded image.';
            }
        } catch (Exception $e) {
            $error_message = 'Error processing post: ' . $e->getMessage();
            error_log($error_message);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_last') {
    if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
        $error_message = 'Unauthorized: Please log in.';
    } else {
        try {
            $files = glob($posts_dir . '/*.json');
            if (!empty($files)) {
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
    <title>simon1g - New Post</title>
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
        include(get_site_path('/navbar.html'));
        include(get_site_path('/ascii_border.php'));
        ?>
    </div>
    <div class="blog-content">
        <h1>Create New Blog Entry</h1>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['admin'])): ?>
            <form method="POST" class="admin-form">
                <label for="password">Admin Password:</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        <?php else: ?>
            <form method="POST" class="post-form" enctype="multipart/form-data">
                <label for="content">Entry Content:</label>
                <textarea id="content" name="content" placeholder="Write your entry here..."></textarea>

                <label for="images">Upload Images (optional):</label>
                <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,image/avif" multiple>
                <small>Allowed types: JPG, PNG, GIF, WEBP, AVIF</small> <br><br>

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