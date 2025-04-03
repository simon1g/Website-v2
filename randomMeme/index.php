<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
session_start();

$jsonPath = get_site_path('/randomMeme/memes.json');

if (file_exists($jsonPath)) {
    $memeList = json_decode(file_get_contents($jsonPath), true);

    if ($memeList) {
        if (!isset($_SESSION['video_queue'])) {
            $_SESSION['video_queue'] = $memeList;
            shuffle($_SESSION['video_queue']);
        }

        if (!isset($_SESSION['seen_videos'])) {
            $_SESSION['seen_videos'] = [];
        }

        do {
            $randomFile = array_shift($_SESSION['video_queue']);
        } while (in_array($randomFile, $_SESSION['seen_videos']) && !empty($_SESSION['video_queue']));

        if ($randomFile) {
            $_SESSION['seen_videos'][] = $randomFile;
        }

        if (empty($_SESSION['video_queue'])) {
            $_SESSION['video_queue'] = array_diff($memeList, $_SESSION['seen_videos']);
            shuffle($_SESSION['video_queue']);
        }
    } else {
        $randomFile = '';
    }

    if ($randomFile):
        $videoPath = htmlspecialchars('/randomMeme/memes/' . basename($randomFile));
    else:
        echo "<p>No memes found in the JSON file.</p>";
    endif;
} else {
    echo "<p>JSON file not found at $jsonPath.</p>";
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
            <link rel="stylesheet" href="/randomMeme/randomMeme.css">
            <link rel="icon" href="/icon.ico">
        </head>
        <body>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/matrix_effect.php'; ?>
            <div class="navbar">
                <?php include(get_site_path('/navbar.html')); ?>
            </div>
            <div class="middle-content">
                <div class="video-size">
                    <video controls>
                        <source src="<?= $videoPath ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <!--</div>
                <div class="button-container">
                    <span class="share-link" onclick="copyToClipboard('<?= $videoPath ?>')">Share Video Link</span>
                    <span class="refresh-button" onclick="location.reload()">New Video</span>
                    <a href="/randomMeme/grid" class="grid-button">View All Memes</a>
                </div>-->
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const video = document.querySelector('video');
                    if (video) {
                        video.volume = 0.5;
                    }
                });

                function copyToClipboard(text) {
                    const tempInput = document.createElement('input');
                    tempInput.value = window.location.origin + text;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);

                    const shareLink = document.querySelector('.share-link');
                    shareLink.textContent = "Copied!";
                    shareLink.classList.add('success');

                    setTimeout(() => {
                        shareLink.textContent = "Share Video Link";
                        shareLink.classList.remove('success');
                    }, 2000);
                }
            </script>
        </body>
        </html>
