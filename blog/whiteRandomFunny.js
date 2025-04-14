let currentLine = 0;
const lines = document.querySelectorAll('.text-line');

async function showNextLine() {
    if (currentLine < lines.length) {
        lines[currentLine].style.display = 'block';
        await new Promise(resolve => setTimeout(resolve, 50));
        lines[currentLine].style.opacity = '1';
        await new Promise(resolve => setTimeout(resolve, 3000));
        lines[currentLine].style.opacity = '0';
        await new Promise(resolve => setTimeout(resolve, 1000));
        lines[currentLine].style.display = 'none';
        
        currentLine++;
        showNextLine();
    } else {
        const videoContainer = document.getElementById('video-container');
        const video = document.createElement('video');
        video.src = 'Lmfao.mp4';
        video.controls = true;
        video.autoplay = true;
        videoContainer.appendChild(video);
    }
}

lines.forEach(line => {
    line.style.display = 'none';
    line.style.opacity = '0';
    line.style.transition = 'opacity 1s ease-in-out';
});

if (lines.length > 0) {
    showNextLine();
}