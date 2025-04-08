<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
session_start();

/*
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === 'LOL123321') {
        $_SESSION['blog_access'] = true;
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $error_message = 'invalid password bozo';
    }
}

if (!isset($_SESSION['blog_access']) || !$_SESSION['blog_access']) {
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
        <link rel="stylesheet" href="/blog/blog.css">
        <link rel="icon" href="/icon.ico">
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
            <div class="blog-content">
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <form method="POST" class="admin-form">
                    <label for="password">Password pls:</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                    <button type="submit">Access!</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
*/
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
    <link rel="stylesheet" href="/blog/blog.css">
    <link rel="icon" href="/icon.ico">
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
        <div class="middle-content">
            <div class="blog-header">
                <?php echo ascii_border("Posts"); ?>
                <p>cuz this only counts</p>
            </div>
        </div>
        <div class="blog-content">
            <div id="posts-container">
            </div>
            <div id="loading" style="display: none;">
                Loading more posts...
            </div>
        </div>
    </div>
    <a href="/blog/admin/" class="admin-button">⚙️</a>
    <script src="/blog/js/infinite-scroll.js"></script>
    <script src="/blog/js/lightbox.js"></script>
</body>
</html>
