<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
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
                <source src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <div class="button-container">
            <span class="share-link" onclick="copyToClipboard()">Share Video Link</span>
            <span class="refresh-button" onclick="loadNewVideo()">New Video</span>
            <a href="/randomMeme/grid" class="grid-button">View All Memes</a>
        </div>
    </div>

    <script>
        let videoQueue = [];
        let seenVideos = [];
        
        async function fetchVideos() {
            const response = await fetch('/randomMeme/memes.json');
            const videos = await response.json();
            return videos;
        }

        async function loadNewVideo() {
            // Get video from URL parameter if exists
            const urlParams = new URLSearchParams(window.location.search);
            const videoParam = urlParams.get('video');
            
            if (videoParam) {
                const videoElement = document.querySelector('video');
                videoElement.src = `/randomMeme/memes/${videoParam}`;
                videoElement.load();
                videoElement.play();
                return;
            }

            if (videoQueue.length === 0) {
                const allVideos = await fetchVideos();
                videoQueue = allVideos.filter(video => !seenVideos.includes(video));
                if (videoQueue.length === 0) {
                    seenVideos = [];
                    videoQueue = [...allVideos];
                }
                shuffleArray(videoQueue);
            }

            const video = videoQueue.shift();
            if (video) {
                seenVideos.push(video);
                const videoElement = document.querySelector('video');
                videoElement.src = `/randomMeme/memes/${video}`;
                videoElement.load();
                videoElement.play();
            }
        }

        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

        function copyToClipboard() {
            const videoSrc = document.querySelector('video').src;
            const videoName = videoSrc.split('/').pop(); // Get just the filename
            const shareUrl = `${window.location.origin}/randomMeme/memes/${videoName}`;
            navigator.clipboard.writeText(shareUrl).then(() => {
                const shareLink = document.querySelector('.share-link');
                shareLink.textContent = "Copied!";
                shareLink.classList.add('success');

                setTimeout(() => {
                    shareLink.textContent = "Share Video Link";
                    shareLink.classList.remove('success');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy link: ', err);
                alert('Failed to copy link.');
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const video = document.querySelector('video');
            if (video) {
                video.volume = 0.5;
            }
            loadNewVideo();
        });
    </script>
</body>
</html>
