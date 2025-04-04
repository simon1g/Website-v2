<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meme Grid - simon1g</title>
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
        <div class="video-grid" id="videoGrid"></div>
        <div class="loading" id="loading">Loading...</div>
    </div>
    
    <div class="video-modal" id="videoModal">
        <div class="modal-content">
            <video controls id="modalVideo">
                <source src="" type="video/mp4">
            </video>
            <div class="modal-buttons">
                <span class="back-button" onclick="closeModal()">Go Back</span>
                <span class="modal-share-button" onclick="shareVideo()">Share Video</span>
            </div>
        </div>
    </div>

    <script>
        let page = 0;
        let loading = false;
        let allVideos = [];
        const videosPerPage = 16;
        let observer;

        async function fetchVideos() {
            const response = await fetch('/randomMeme/memes.json');
            allVideos = await response.json();
            shuffleArray(allVideos); // Randomize initial order
        }

        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

        function createVideoElement(videoSrc) {
            const container = document.createElement('div');
            container.className = 'video-grid-item';
            
            const video = document.createElement('video');
            video.src = `/randomMeme/memes/${videoSrc}`;
            video.preload = 'none'; // Don't preload until needed
            video.muted = true;
            video.loop = true;

            // Lazy loading with Intersection Observer
            observer.observe(container);

            // Add hover effect
            container.addEventListener('mouseenter', () => {
                video.preload = 'auto';
                video.play().catch(() => {});
            });
            container.addEventListener('mouseleave', () => {
                video.pause();
                video.currentTime = 0;
            });

            // Update click handler for modal
            container.addEventListener('click', () => {
                openVideoInModal(videoSrc);
            });

            container.appendChild(video);
            return container;
        }

        // Add modal functions
        function openVideoInModal(videoSrc) {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            modalVideo.src = `/randomMeme/memes/${videoSrc}`;
            modal.classList.add('active');
            modalVideo.play();
            
            // Update URL without reload
            history.pushState({video: videoSrc}, '', `?video=${videoSrc}`);
        }

        function closeModal() {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            modalVideo.pause();
            modalVideo.src = '';
            modal.classList.remove('active');
            
            // Remove video parameter from URL without reload
            history.pushState({}, '', window.location.pathname);
        }

        // Handle browser back/forward
        window.addEventListener('popstate', (event) => {
            if (event.state && event.state.video) {
                openVideoInModal(event.state.video);
            } else {
                closeModal();
            }
        });

        // Add click handler for modal background
        document.getElementById('videoModal').addEventListener('click', (e) => {
            // If click is on modal background (not the video or back button)
            if (e.target.classList.contains('video-modal')) {
                closeModal();
            }
        });

        async function loadMoreVideos() {
            if (loading) return;
            loading = true;
            
            const start = page * videosPerPage;
            const end = start + videosPerPage;
            const videosToLoad = allVideos.slice(start, end);

            if (videosToLoad.length > 0) {
                const grid = document.getElementById('videoGrid');
                videosToLoad.forEach(video => {
                    grid.appendChild(createVideoElement(video));
                });
                page++;
            }

            document.getElementById('loading').style.display = 
                end >= allVideos.length ? 'none' : 'block';
            
            loading = false;
        }

        // Initialize Intersection Observer
        function setupIntersectionObserver() {
            observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const video = entry.target.querySelector('video');
                        if (video && video.preload === 'none') {
                            video.preload = 'metadata';
                        }
                    }
                });
            }, {
                rootMargin: '50px'
            });
        }

        // Update infinite scroll detection
        function setupInfiniteScroll() {
            window.addEventListener('scroll', () => {
                if (loading) return;
                
                const {scrollTop, scrollHeight, clientHeight} = document.documentElement;
                if (scrollTop + clientHeight >= scrollHeight - 100) {
                    loadMoreVideos();
                }
            });
        }

        // Initialize
        async function init() {
            setupIntersectionObserver();
            await fetchVideos();
            await loadMoreVideos();
            setupInfiniteScroll();
            
            // Force body overflow to be visible
            document.body.style.overflow = 'auto';

            // Check for video parameter
            const urlParams = new URLSearchParams(window.location.search);
            const videoParam = urlParams.get('video');
            if (videoParam) {
                openVideoInModal(videoParam);
            }
        }

        function shareVideo() {
            const videoSrc = document.getElementById('modalVideo').src;
            navigator.clipboard.writeText(videoSrc).then(() => {
                const shareBtn = document.querySelector('.modal-share-button');
                const originalText = shareBtn.textContent;
                shareBtn.textContent = 'Copied!';
                setTimeout(() => {
                    shareBtn.textContent = originalText;
                }, 2000);
            });
        }

        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>