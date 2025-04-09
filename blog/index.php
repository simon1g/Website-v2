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
    <script>
        if (localStorage.getItem('hasSeenDissolve')) {
            document.addEventListener('DOMContentLoaded', function() {
                document.documentElement.style.background = '#ffffff';
                document.body.style.background = '#ffffff';
                document.body.className = 'blank';
                document.body.innerHTML = '<div class="blank-state"></div>';
                
                setTimeout(() => {
                    const messageContainer = document.createElement('div');
                    messageContainer.style.position = 'fixed';
                    messageContainer.style.top = '50%';
                    messageContainer.style.left = '50%';
                    messageContainer.style.transform = 'translate(-50%, -50%)';
                    messageContainer.style.display = 'flex';
                    messageContainer.style.flexDirection = 'column';
                    messageContainer.style.alignItems = 'center';
                    messageContainer.style.gap = '20px';
                    messageContainer.style.fontFamily = 'Arial, sans-serif';
                    
                    const firstMessage = document.createElement('div');
                    firstMessage.style.color = '#000000';
                    firstMessage.style.fontSize = '24px';
                    firstMessage.style.transition = 'opacity 2s';
                    firstMessage.style.opacity = '0';
                    firstMessage.textContent = 'still here huh?';
                    
                    const secondMessage = document.createElement('div');
                    secondMessage.style.color = '#000000';
                    secondMessage.style.fontSize = '24px';
                    secondMessage.style.opacity = '0';
                    secondMessage.style.transition = 'opacity 2s';
                    secondMessage.textContent = 'hmmm sooo how\'s your day?';
                    
                    messageContainer.appendChild(firstMessage);
                    messageContainer.appendChild(secondMessage);
                    document.body.appendChild(messageContainer);
                    
                    // Fade in first message
                    setTimeout(() => {
                        firstMessage.style.opacity = '1';
                        // Fade in second message after 4 seconds
                        setTimeout(() => {
                            secondMessage.style.opacity = '1';
                            // Close page after both messages are shown
                            setTimeout(() => {
                                window.close();
                            }, 2000);
                        }, 4000);
                    }, 100);
                }, 10000);
            });
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = document.querySelector('.dissolve-overlay');
                const messages = document.querySelectorAll('.message-line');
                const content = document.querySelector('.content-wrapper');
                
                setTimeout(() => {
                    content.classList.add('fade-out');
                    overlay.style.opacity = '1';
                    localStorage.setItem('hasSeenDissolve', 'true');
                    
                    messages.forEach((msg, index) => {
                        setTimeout(() => {
                            msg.style.opacity = '1';
                        }, (index * 3000) + 2000);
                    });

                    // Redirect to blank state after messages
                    setTimeout(() => {
                        window.location.reload();
                    }, 12000);
                }, 5000);
            });
        }
    </script>
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
</body>
</html>