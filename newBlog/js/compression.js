async function compressImage(file) {
    return new Promise((resolve) => {
        const maxWidth = 1280;
        const maxHeight = 720;
        const quality = 0.9; // 90% quality

        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function(e) {
            const img = new Image();
            img.src = e.target.result;
            img.onload = function() {
                // Calculate new dimensions while maintaining aspect ratio
                let width = img.width;
                let height = img.height;
                if (width > maxWidth || height > maxHeight) {
                    const ratio = Math.min(maxWidth / width, maxHeight / height);
                    width *= ratio;
                    height *= ratio;
                }

                // Create canvas and draw resized image
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Convert to Blob
                canvas.toBlob(
                    (blob) => {
                        // Create a new file from the blob
                        const compressedFile = new File([blob], file.name, {
                            type: file.type,
                            lastModified: Date.now()
                        });
                        resolve(compressedFile);
                    },
                    file.type,
                    quality
                );
            };
        };
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.post-form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const fileInput = document.querySelector('input[type="file"]');
            const files = fileInput.files;
            
            if (files.length > 0) {
                const formData = new FormData();
                
                // Add all other form data
                const content = document.querySelector('#content').value;
                formData.append('content', content);
                
                // Compress and append each image
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (file.type.startsWith('image/')) {
                        const compressedFile = await compressImage(file);
                        formData.append('images[]', compressedFile);
                    }
                }
                
                // Submit the form with compressed images
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                    }
                });
            } else {
                // If no images, submit form normally
                form.submit();
            }
        });
    }
});