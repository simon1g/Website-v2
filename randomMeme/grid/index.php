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
        const videosPerPage = 9;
        const pagesToKeep = 4;
        const maxVisibleItems = videosPerPage * pagesToKeep;
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
            video.src = `/randomMeme/memes/${videoSrc}`;

            container.addEventListener('click', () => {
                openVideoInModal(videoSrc);
            });

            container.appendChild(video);
            return container;
        }

        function openVideoInModal(videoSrc) {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            const videoPath = `/randomMeme/memes/${videoSrc}`;
            modalVideo.src = videoPath;
            modal.classList.add('active');
            modalVideo.play().catch(e => console.error("Error playing video:", e));

            if (window.location.search !== `?video=${encodeURIComponent(videoSrc)}`) {
                history.pushState({video: videoSrc}, '', `?video=${encodeURIComponent(videoSrc)}`);
            }
        }

        function closeModal() {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            if (!modal.classList.contains('active')) return;

            modalVideo.pause();
            modalVideo.src = '';
            modal.classList.remove('active');

            if (window.location.search.includes('?video=')) {
                history.pushState({}, '', window.location.pathname);
            }
        }

        window.addEventListener('popstate', (event) => {
            const urlParams = new URLSearchParams(window.location.search);
            const videoParam = urlParams.get('video');

            if (videoParam) {
                const modalVideoSrc = document.getElementById('modalVideo').src;
                if (!document.getElementById('videoModal').classList.contains('active') || !modalVideoSrc.endsWith(videoParam)) {
                    openVideoInModal(videoParam);
                }
            } else {
                closeModal();
            }
        });

        document.getElementById('videoModal').addEventListener('click', (e) => {
            if (e.target.classList.contains('video-modal')) {
                closeModal();
            }
        });

        async function loadMoreVideos() {
            if (loading || !allVideos.length) return;
            loading = true;

            clearTimeout(loadingTimeout);

            const grid = document.getElementById('videoGrid');
            const loadingIndicator = document.getElementById('loading');
            loadingIndicator.style.display = 'block';

            const currentChildrenCount = grid.children.length;
            const itemsToRemove = currentChildrenCount - maxVisibleItems;

            if (itemsToRemove > 0) {
                for (let i = 0; i < itemsToRemove; i++) {
                    if (grid.firstChild) {
                        if (observer) {
                            observer.unobserve(grid.firstChild);
                        }
                        grid.removeChild(grid.firstChild);
                    }
                }
            }

            try {
                const start = page * videosPerPage;
                const end = start + videosPerPage;
                const videosToLoad = allVideos.slice(start, end);

                if (videosToLoad.length > 0) {
                    const fragment = document.createDocumentFragment();

                    for (let i = 0; i < videosToLoad.length; i++) {
                        const videoElementContainer = createVideoElement(videosToLoad[i]);
                        fragment.appendChild(videoElementContainer);
                        if (observer) {
                            observer.observe(videoElementContainer);
                        }
                    }
                    grid.appendChild(fragment);

                    page++;
                }

                if (end >= allVideos.length) {
                    loadingIndicator.style.display = 'none';
                }

            } catch (error) {
                console.error('Error loading more videos:', error);
                loadingIndicator.textContent = 'Error loading more videos.';
                loadingIndicator.style.display = 'block';
            } finally {
                loadingTimeout = setTimeout(() => {
                    loading = false;
                    checkScrollAndLoad();
                }, 200);
            }
        }

        function setupIntersectionObserver() {
            const options = {
                root: null,
                rootMargin: '200px 0px 200px 0px',
                threshold: 0.01
            };

            observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const video = entry.target.querySelector('video');
                        if (video && video.preload === 'none') {
                            video.preload = 'metadata';
                        }
                    }
                });
            }, options);
        }

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

        function checkScrollAndLoad() {
            if (loading || page * videosPerPage >= allVideos.length) return;

            const {scrollTop, scrollHeight, clientHeight} = document.documentElement;
            if (scrollTop + clientHeight >= scrollHeight - 600) {
                loadMoreVideos();
            }
        }

        function setupInfiniteScroll() {
            window.addEventListener('scroll', throttle(checkScrollAndLoad, 250));
        }

        async function init() {
            setupIntersectionObserver();
            await fetchVideos();
            if (allVideos.length > 0) {
                await loadMoreVideos();
                const urlParams = new URLSearchParams(window.location.search);
                const videoParam = urlParams.get('video');
                if (videoParam) {
                    setTimeout(() => openVideoInModal(videoParam), 100);
                }
            } else {
                document.getElementById('loading').textContent = 'No videos found or failed to load.';
                document.getElementById('loading').style.display = 'block';
            }
            setupInfiniteScroll();

            document.body.style.overflow = 'auto';
        }

        function shareVideo() {
            const modalVideo = document.getElementById('modalVideo');
            const videoName = modalVideo.src.split('/').pop();
            const shareUrl = `${window.location.origin}/randomMeme/memes/${videoName}`;
            navigator.clipboard.writeText(shareUrl).then(() => {
                const shareBtn = document.querySelector('.modal-share-button');
                const originalText = shareBtn.textContent;
                shareBtn.textContent = 'Link Copied!';
                setTimeout(() => {
                    shareBtn.textContent = originalText;
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy link: ', err);
                alert('Failed to copy link.');
            });
        }

        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>