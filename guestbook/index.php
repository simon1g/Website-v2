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
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <link rel="stylesheet" href="/styles.css">
    <link rel="stylesheet" href="/guestbook/guestbook.css">
    <link rel="icon" href="/icon.ico">
</head>
<body>
    <?php
        include(get_site_path('/matrix_effect.php'));
    ?>
    <div class="navbar">
        <?php include(get_site_path('/navbar.html')); ?>
    </div>
    <div class="middle-content">
        <form id="guestbook-form" method="POST" action="/guestbook/submit-guestbook.php">
          <label for="name">Name:</label>
          <input type="text" id="name" name="name" maxlength="50" required>
          
          <label for="message">Message (maximum 50 characters):</label>
          <textarea id="message" name="message" maxlength="50" required></textarea>
          
          <!-- Turnstile Widget -->
          <div class="cf-turnstile" data-sitekey="0x4AAAAAAA0uvojr--a3xihU"></div>
          
          <button type="submit">Submit</button>
          <button type="button" onclick="window.location.href='/guestbook/view-entries/'">View All Entries</button>
        </form>
    </div>
</body>
</html>
