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
        const videosPerPage = 9;
        const pagesToKeep = 4; // Keep current page + 3 previous pages in DOM = 4 * 9 = 36 items max
        const maxVisibleItems = videosPerPage * pagesToKeep;
        let observer;
        let loadingTimeout;

        async function fetchVideos() {
            try {
                const response = await fetch('/randomMeme/memes.json');
                if (!response.ok) throw new Error('Failed to fetch videos');
                allVideos = await response.json();
                shuffleArray(allVideos);
                console.log(`Fetched and shuffled ${allVideos.length} videos.`);
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
            video.preload = 'none'; // Start with none, let observer handle loading metadata
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

        // Add modal functions
        function openVideoInModal(videoSrc) {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            const videoPath = `/randomMeme/memes/${videoSrc}`;
            console.log('Opening modal for:', videoPath);
            modalVideo.src = videoPath;
            modal.classList.add('active');
            modalVideo.play().catch(e => console.error("Error playing video:", e)); // Play and catch potential errors

            // Update URL without reload
            // Only push state if the video isn't already in the URL (prevents duplicate history entries)
            if (window.location.search !== `?video=${encodeURIComponent(videoSrc)}`) {
                history.pushState({video: videoSrc}, '', `?video=${encodeURIComponent(videoSrc)}`);
            }
        }

        function closeModal() {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            if (!modal.classList.contains('active')) return; // Avoid closing if already closed

            console.log('Closing modal');
            modalVideo.pause();
            modalVideo.src = ''; // Release video resources
            modal.classList.remove('active');

            // Remove video parameter from URL without reload only if a video was open
             if (window.location.search.includes('?video=')) {
                history.pushState({}, '', window.location.pathname);
            }
        }

        // Handle browser back/forward
        window.addEventListener('popstate', (event) => {
            const urlParams = new URLSearchParams(window.location.search);
            const videoParam = urlParams.get('video');
            console.log('Popstate event:', event.state, 'URL video param:', videoParam);

            if (videoParam) {
                // If URL has video param, ensure modal is open for that video
                 const modalVideoSrc = document.getElementById('modalVideo').src;
                 // Check if the modal is already open with the correct video to avoid reloading
                 if (!document.getElementById('videoModal').classList.contains('active') || !modalVideoSrc.endsWith(videoParam)) {
                    openVideoInModal(videoParam);
                 }
            } else {
                // If URL has no video param, ensure modal is closed
                closeModal();
            }
        });


        // Add click handler for modal background
        document.getElementById('videoModal').addEventListener('click', (e) => {
            // If click is on modal background (not the video or buttons)
            if (e.target.classList.contains('video-modal')) {
                closeModal();
            }
        });

        async function loadMoreVideos() {
            if (loading || !allVideos.length) return; // Don't load if already loading or no videos fetched
            loading = true;

            clearTimeout(loadingTimeout); // Clear previous timeout if any

            const grid = document.getElementById('videoGrid');
            const loadingIndicator = document.getElementById('loading');
            loadingIndicator.style.display = 'block'; // Show loading indicator

            // --- Unload old videos ---
            const currentChildrenCount = grid.children.length;
            const itemsToRemove = currentChildrenCount - maxVisibleItems;

            if (itemsToRemove > 0) {
                console.log(`Removing ${itemsToRemove} oldest video elements.`);
                for (let i = 0; i < itemsToRemove; i++) {
                    if (grid.firstChild) {
                        // Optionally unobserve before removing if observer is active
                         if (observer) {
                             observer.unobserve(grid.firstChild);
                         }
                        grid.removeChild(grid.firstChild);
                    }
                }
                 // Adjust scroll position slightly if needed, though usually not necessary for top removal
                 // window.scrollBy(0, 1); // Example: tiny scroll to potentially help browser reflow
                 // window.scrollBy(0, -1);
            }
            // --- End Unload ---


            try {
                const start = page * videosPerPage;
                const end = start + videosPerPage;
                const videosToLoad = allVideos.slice(start, end);

                console.log(`Loading page ${page + 1}, videos ${start + 1}-${Math.min(end, allVideos.length)}`);

                if (videosToLoad.length > 0) {
                    const fragment = document.createDocumentFragment(); // Use fragment for better performance

                    for (let i = 0; i < videosToLoad.length; i++) {
                        const videoElementContainer = createVideoElement(videosToLoad[i]);
                        fragment.appendChild(videoElementContainer);
                        // Observe the new element for preloading
                        if (observer) {
                            observer.observe(videoElementContainer);
                        }
                    }
                    // Append all new elements at once
                    grid.appendChild(fragment);

                    page++;
                }

                // Hide loading indicator if no more videos or after loading
                if (end >= allVideos.length) {
                    console.log('All videos loaded.');
                    loadingIndicator.style.display = 'none';
                } else {
                    // Keep loading indicator if more videos exist, but might be hidden by footer/end of page
                     // loadingIndicator.style.display = 'block'; // Already set at start
                }


            } catch (error) {
                console.error('Error loading more videos:', error);
                 loadingIndicator.textContent = 'Error loading more videos.';
                 loadingIndicator.style.display = 'block';
            } finally {
                 // Add a small delay before allowing the next load trigger
                loadingTimeout = setTimeout(() => {
                    loading = false;
                    // Check if we are already near the bottom after loading, if so, load more immediately
                    checkScrollAndLoad();
                }, 200); // Short delay to prevent rapid fire loading
            }
        }

        // Initialize Intersection Observer
        function setupIntersectionObserver() {
            // Options: rootMargin positive means trigger *before* element enters viewport
            const options = {
                root: null, // relative to document viewport
                rootMargin: '200px 0px 200px 0px', // Load metadata when item is 200px below or above viewport
                threshold: 0.01 // Trigger even if 1% is visible
            };

            observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const video = entry.target.querySelector('video');
                        if (video && video.preload === 'none') {
                            // console.log('Preloading metadata for:', video.src);
                            video.preload = 'metadata';
                            // No need to unobserve, metadata only needs to load once
                            // obs.unobserve(entry.target); // Optional: unobserve after triggering
                        }
                        // Optional: Play muted video on hover/intersection (can be performance intensive)
                        // if (video && video.paused && video.muted) {
                        //     video.play().catch(e => {}); // Autoplay muted on view
                        // }
                    } else {
                         // Optional: Pause video when it goes out of view
                        // const video = entry.target.querySelector('video');
                        // if (video && !video.paused) {
                        //     video.pause();
                        // }
                    }
                });
            }, options);
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

        // Function to check scroll position and load if needed
        function checkScrollAndLoad() {
             if (loading || page * videosPerPage >= allVideos.length) return; // Don't load if loading or all loaded

             const {scrollTop, scrollHeight, clientHeight} = document.documentElement;
             // Trigger loading when closer to the bottom (e.g., 600px from bottom)
             if (scrollTop + clientHeight >= scrollHeight - 600) {
                 console.log('Scroll threshold reached, loading more videos.');
                 loadMoreVideos();
             }
        }

        // Setup infinite scroll listener
        function setupInfiniteScroll() {
            window.addEventListener('scroll', throttle(checkScrollAndLoad, 250)); // Check every 250ms during scroll
        }

        // Initialize
        async function init() {
            console.log('Initializing Meme Grid...');
            setupIntersectionObserver();
            await fetchVideos();
            if (allVideos.length > 0) {
                await loadMoreVideos(); // Load the first batch
                 // Check URL for direct video link AFTER first batch is potentially loaded
                 const urlParams = new URLSearchParams(window.location.search);
                 const videoParam = urlParams.get('video');
                 if (videoParam) {
                     console.log('Video parameter found in URL:', videoParam);
                     // Ensure the modal opening happens after the initial content might be rendered
                     setTimeout(() => openVideoInModal(videoParam), 100);
                 }
            } else {
                 document.getElementById('loading').textContent = 'No videos found or failed to load.';
                 document.getElementById('loading').style.display = 'block';
            }
            setupInfiniteScroll();

            // Ensure body allows scrolling (sometimes CSS might interfere)
            document.body.style.overflow = 'auto';
        }

        function shareVideo() {
            const modalVideo = document.getElementById('modalVideo');
            const videoUrl = window.location.href; // Share the URL with the ?video= parameter

            if (navigator.share) { // Use Web Share API if available
                 navigator.share({
                     title: 'Check out this meme!',
                     text: 'Found this cool meme video:',
                     url: videoUrl,
                 })
                 .then(() => console.log('Successful share'))
                 .catch((error) => console.log('Error sharing:', error));
            } else { // Fallback to clipboard
                 navigator.clipboard.writeText(videoUrl).then(() => {
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
        }


        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>