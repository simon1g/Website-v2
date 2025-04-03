<?php require_once(__DIR__ . '/config.php'); ?>
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
</head>
<body>
    <?php
        include(get_site_path('/ascii_border.php'));
        include(get_site_path('/matrix_effect.php'));
    ?>
    <!--main content -->
    <div class="navbar">
        <?php include(get_site_path('/navbar.html')); ?>
    </div>
    <div class="middle-content">
        <h1><?php echo ascii_border("Simon1G"); ?></h1>
        <p>half bake webpage on the world wide web</p>
    </div>
</body>
</html>
