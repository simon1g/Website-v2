document.addEventListener('DOMContentLoaded', function() {
    // Create lightbox element
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    document.body.appendChild(lightbox);

    // Handle image clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('post-image')) {
            const img = document.createElement('img');
            img.src = e.target.src;
            lightbox.innerHTML = '';
            lightbox.appendChild(img);
            lightbox.classList.add('active');
        }
    });

    // Close lightbox when clicking it
    lightbox.addEventListener('click', function() {
        lightbox.classList.remove('active');
    });
});
