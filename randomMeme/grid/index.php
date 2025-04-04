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
        const videosPerPage = 8; // Reduced from 16 to load fewer videos at once
        let observer;
        let loadingTimeout;

        async function fetchVideos() {
            try {
                const response = await fetch('/randomMeme/memes.json');
                if (!response.ok) throw new Error('Failed to fetch videos');
                allVideos = await response.json();
                shuffleArray(allVideos);
            } catch (error) {
                console.error('Error loading videos:', error);
                document.getElementById('loading').textContent = 'Error loading videos. Please refresh.';
            }
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
            video.preload = 'none';
            video.muted = true;
            video.loop = true;
            video.playbackRate = 1.0;
            
            // Add loading state indicator
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'video-loading';
            loadingIndicator.textContent = 'Loading...';
            container.appendChild(loadingIndicator);

            // Load video source after a delay to prevent too many simultaneous requests
            setTimeout(() => {
                video.src = `/randomMeme/memes/${videoSrc}`;
                video.addEventListener('loadedmetadata', () => {
                    loadingIndicator.remove();
                });
                video.addEventListener('error', () => {
                    loadingIndicator.textContent = 'Error loading video';
                });
            }, Math.random() * 1000); // Stagger loading

            // Add hover effect with debouncing
            let playTimeout;
            container.addEventListener('mouseenter', () => {
                clearTimeout(playTimeout);
                playTimeout = setTimeout(() => {
                    video.preload = 'auto';
                    video.play().catch(() => {});
                }, 100);
            });

            container.addEventListener('mouseleave', () => {
                clearTimeout(playTimeout);
                video.pause();
                video.currentTime = 0;
            });

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
            
            // Clear previous timeout if exists
            clearTimeout(loadingTimeout);
            
            try {
                const start = page * videosPerPage;
                const end = start + videosPerPage;
                const videosToLoad = allVideos.slice(start, end);

                if (videosToLoad.length > 0) {
                    const grid = document.getElementById('videoGrid');
                    // Load videos with a slight delay between each
                    for (let i = 0; i < videosToLoad.length; i++) {
                        setTimeout(() => {
                            grid.appendChild(createVideoElement(videosToLoad[i]));
                        }, i * 100);
                    }
                    page++;
                }

                document.getElementById('loading').style.display = 
                    end >= allVideos.length ? 'none' : 'block';
            } catch (error) {
                console.error('Error loading more videos:', error);
            } finally {
                // Add delay before allowing next load
                loadingTimeout = setTimeout(() => {
                    loading = false;
                }, 500);
            }
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

        // Throttle scroll event
        function throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        }

        // Update infinite scroll detection
        function setupInfiniteScroll() {
            window.addEventListener('scroll', throttle(() => {
                if (loading) return;
                
                const {scrollTop, scrollHeight, clientHeight} = document.documentElement;
                if (scrollTop + clientHeight >= scrollHeight - 600) {
                    loadMoreVideos();
                }
            }, 250));
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