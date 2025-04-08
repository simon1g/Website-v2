let page = 0;
const perPage = 10;
let loading = false;
let allPostsLoaded = false;

function formatDate(date, time) {
    const dateObj = new Date(`${date}T${time}`);
    if (!isNaN(dateObj)) {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        };
        return `Posted on: ${dateObj.toLocaleDateString(undefined, options)}`;
    }
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

    if (post.images && post.images.length > 0) {
        const imageContainer = document.createElement('div');
        imageContainer.className = 'post-images';
        
        post.images.forEach(imageUrl => {
            const img = document.createElement('img');
            img.src = imageUrl;
            img.className = 'post-image';
            img.onerror = () => {
                img.style.display = 'none';
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
loadPosts();
