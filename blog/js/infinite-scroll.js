let page = 0;
const perPage = 10;
let loading = false;
let allPostsLoaded = false;

function formatDate(date, time) {
    return `Posted on: ${date} at ${time}`;
}

function createPostElement(post) {
    const article = document.createElement('article');
    article.className = 'blog-preview';

    const meta = document.createElement('div');
    meta.className = 'post-meta';
    meta.textContent = formatDate(post.date, post.time);

    const content = document.createElement('div');
    content.className = 'post-content';
    content.innerHTML = post.content.replace(/\r\n|\n/g, '<br>');

    // Add images if they exist
    if (post.images && post.images.length > 0) {
        const imageContainer = document.createElement('div');
        imageContainer.className = 'post-images';
        
        post.images.forEach(imageUrl => {
            console.log('Loading image:', imageUrl); // Debug log
            const img = document.createElement('img');
            img.src = imageUrl;
            img.className = 'post-image';
            // Add error handling for images
            img.onerror = () => {
                console.error('Failed to load image:', imageUrl);
                img.style.display = 'none';
            };
            img.onload = () => {
                console.log('Image loaded successfully:', imageUrl);
            };
            imageContainer.appendChild(img);
        });
        
        content.appendChild(imageContainer);
    }

    article.appendChild(meta);
    article.appendChild(content);
    return article;
}

async function loadPosts() {
    if (loading || allPostsLoaded) return;
    
    loading = true;
    document.getElementById('loading').style.display = 'block';

    try {
        const response = await fetch(`/blog/api/get_posts.php?page=${page}&per_page=${perPage}`);
        const posts = await response.json();

        if (posts.length < perPage) {
            allPostsLoaded = true;
        }

        const container = document.getElementById('posts-container');
        posts.forEach(post => {
            container.appendChild(createPostElement(post));
        });

        page++;
    } catch (error) {
        console.error('Error loading posts:', error);
    } finally {
        loading = false;
        document.getElementById('loading').style.display = 'none';
    }
}

function handleScroll() {
    const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
    if (scrollTop + clientHeight >= scrollHeight - 500) {
        loadPosts();
    }
}

window.addEventListener('scroll', handleScroll);
loadPosts(); // Load initial posts
