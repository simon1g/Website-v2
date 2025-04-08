<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
session_start();
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
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        .content-wrapper {
            opacity: 1;
            visibility: visible;
            transition: opacity 2s;
            position: relative;
            z-index: 1;
        }

        .content-wrapper.fade-out {
            opacity: 0;
        }

        .dissolve-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 1000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 2s;
        }

        .end-message {
            position: fixed;
            left: 50%;
            top: 40%;
            transform: translate(-50%, -50%);
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 24px;
            color: black;
            z-index: 1001;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .message-line {
            opacity: 0;
            transition: opacity 1.5s;
        }
    </style>
</head>
<body>
    <div class="dissolve-overlay"></div>
    <div class="end-message">
        <div class="message-line">it's over</div>
        <div class="message-line">thanks!</div>
        <div class="message-line">fancy ain't it :P</div>
    </div>
    <div class="content-wrapper">
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
    </div>
    <script src="/blog/js/infinite-scroll.js"></script>
    <script src="/blog/js/lightbox.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.querySelector('.dissolve-overlay');
            const messages = document.querySelectorAll('.message-line');
            const content = document.querySelector('.content-wrapper');
            
            setTimeout(() => {
                content.classList.add('fade-out');
                overlay.style.opacity = '1';
                
                // Show messages sequentially
                messages.forEach((msg, index) => {
                    setTimeout(() => {
                        msg.style.opacity = '1';
                    }, (index * 3000) + 2000); // Start after initial fade
                });
            }, 5000); // Changed from 10000 to 5000
        });
    </script>
</body>
</html>