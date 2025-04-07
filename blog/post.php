<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
session_start();

if (!isset($_SESSION['blog_access']) || !$_SESSION['blog_access']) {
    header('Location: /blog/');
    exit;
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
        ?>
    </div>
    <?php include(get_site_path('/matrix_effect.php')); ?>
    <div class="blog-container">
        <div class="blog-header">
            <?php
            $post_id = $_GET['id'] ?? '';
            $post_file = __DIR__ . '/posts/' . $post_id . '.json';
            
            if (file_exists($post_file)) {
                $content = json_decode(file_get_contents($post_file), true);
                if (!empty($content['title'])) {
                    echo '<h1>' . htmlspecialchars($content['title']) . '</h1>';
                }
            } else {
                echo '<h1>Post not found</h1>';
            }
            ?>
        </div>
        <div class="blog-content">
            <?php
            if (file_exists($post_file)) {
                $date = $content['date'] ?? '';
                $time = $content['time'] ?? '';
                if ($date && $time) {
                    echo '<div class="post-meta">Posted on: ' . htmlspecialchars($date) . ' at ' . htmlspecialchars($time) . '</div>';
                }
                
                if (!empty($content['content'])) {
                    echo '<div class="post-content">' . nl2br(htmlspecialchars($content['content'])) . '</div>';
                }
                
                if (!empty($content['images'])) {
                    echo '<div class="post-images">';
                    foreach ($content['images'] as $image) {
                        echo '<img src="' . htmlspecialchars($image) . '" class="post-image" alt="Post image">';
                    }
                    echo '</div>';
                }
            } else {
                echo '<p>Post not found</p>';
            }
            ?>
            <p><a href="/blog/">← Back to blog</a></p>
        </div>
    </div>
    <script src="/blog/js/lightbox.js"></script>
</body>
</html>
