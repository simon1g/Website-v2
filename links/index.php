<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php'); ?>
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
    <link rel="stylesheet" href="/links/links.css">
    <link rel="icon" href="/icon.ico">
</head>
<body>
    <?php
        include(get_site_path('/ascii_border.php'));
        include(get_site_path('/matrix_effect.php'));
    ?>
    <div class="navbar">
        <?php include(get_site_path('/navbar.html')); ?>
    </div>
        <div class="middle-content">
            <h1 class="ascii-title"><?php echo ascii_border('Links to my pages'); ?></h1>
            <div class="ascii-links">
                <a href="https://www.reddit.com/user/_ssSimon_/" target="_blank">Reddit</a>
                <a href="https://soundcloud.com/simon1g-xyz" target="_blank">SoundCloud</a>
                <a href="https://x.com/simon1g_" target="_blank">Twitter</a>
                <a href="https://www.youtube.com/@imon1G" target="_blank">YouTube</a>
                <a href="https://bsky.app/profile/simon1g.xyz" target="_blank">BlueSky</a>
                <a href="https://myanimelist.net/animelist/Simon_1g" target="_blank">My Anime List</a>
            </div>
        </div>
    </div>
</body>
</html>
