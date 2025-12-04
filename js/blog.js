document.addEventListener('DOMContentLoaded', function() {
    const publicPostsList = document.getElementById('public-posts-list');
    const privatePostsList = document.getElementById('private-posts-list');
    const postForm = document.getElementById('post-form');
    const postIdInput = document.getElementById('post-id');
    const postTitleInput = document.getElementById('post-title');
    const postContentInput = document.getElementById('post-content');
    const postIsPublicCheckbox = document.getElementById('post-is-public');
    const savePostBtn = document.getElementById('save-post-btn');
    const notificationContainer = document.getElementById('notification-container');

    // Helper to HTML-escape strings
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // Helper to show notifications (reused from dashboard.js pattern)
    function showNotification(message, isError = false) {
        const notification = document.createElement('div');
        notification.className = `notification ${isError ? 'error' : ''}`;
        notification.textContent = message;
        
        notificationContainer.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        setTimeout(() => {
            notification.classList.remove('show');
            notification.addEventListener('transitionend', () => {
                notification.remove();
            });
        }, 3000);
    }

    // --- Fetch & Render Posts ---
    async function fetchPosts(type) { // 'public' or 'private'
        const targetList = type === 'public' ? publicPostsList : privatePostsList;
        targetList.innerHTML = `<p class="no-posts-message">Cargando posts ${type === 'public' ? 'públicos' : 'privados'}...</p>`;

        try {
            const response = await fetch(`api/posts.php?type=${type}`);
            const result = await response.json();

            if (result.status === 'success') {
                if (result.posts.length > 0) {
                    renderPosts(result.posts, targetList);
                } else {
                    targetList.innerHTML = `<p class="no-posts-message">No hay posts ${type === 'public' ? 'públicos' : 'privados'} aún.</p>`;
                }
            } else {
                showNotification(`Error cargando posts ${type}: ${result.message}`, true);
                targetList.innerHTML = `<p class="no-posts-message">Error cargando posts ${type}.</p>`;
            }
        } catch (error) {
            console.error(`Error fetching ${type} posts:`, error);
            showNotification(`Error de red al cargar posts ${type}.`, true);
            targetList.innerHTML = `<p class="no-posts-message">Error de red al cargar posts ${type}.</p>`;
        }
    }

    function renderPosts(posts, targetList) {
        targetList.innerHTML = ''; // Clear previous content
        posts.forEach(post => {
            const postElement = document.createElement('div');
            postElement.className = 'post';
            postElement.dataset.postId = post.id;
            postElement.innerHTML = `
                <h3 class="post-title">${escapeHtml(post.title)} ${post.is_public == 0 ? '(Privado)' : ''}</h3>
                <p class="post-author">Por: ${escapeHtml(post.username)} | <small>${new Date(post.created_at).toLocaleString()}</small></p>
                <div class="post-content">${escapeHtml(post.content)}</div>
                ${post.user_id == currentUserId ? `
                <div class="post-actions">
                    <button class="edit-post-btn">Editar</button>
                    <button class="delete-post-btn">Eliminar</button>
                </div>` : ''}
            `;
            targetList.appendChild(postElement);

            if (post.user_id == currentUserId) {
                postElement.querySelector('.edit-post-btn')?.addEventListener('click', () => editPost(post));
                postElement.querySelector('.delete-post-btn')?.addEventListener('click', () => deletePost(post.id));
            }
        });
    }

    // --- Post Form Submission ---
    if (postForm) {
        postForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const id = postIdInput.value;
            const title = postTitleInput.value.trim();
            const content = postContentInput.value.trim();
            const is_public = postIsPublicCheckbox.checked ? 1 : 0;

            if (!title || !content) {
                showNotification('El título y el contenido del post son obligatorios.', true);
                return;
            }

            const method = id ? 'PUT' : 'POST';
            const url = id ? `api/posts.php?id=${id}` : 'api/posts.php';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, title: title, content: content, is_public: is_public })
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showNotification(result.message);
                    // Clear form
                    postIdInput.value = '';
                    postTitleInput.value = '';
                    postContentInput.value = '';
                    postIsPublicCheckbox.checked = false; // Default to private after post
                    savePostBtn.textContent = 'Publicar Post';
                    
                    // Refresh posts
                    fetchPosts('public');
                    // Check if user is logged in before fetching private posts
                    if (currentUserId !== null) {
                        fetchPosts('private');
                    }
                } else {
                    showNotification(`Error: ${result.message}`, true);
                }
            } catch (error) {
                console.error('Error saving post:', error);
                showNotification('Error de red al guardar el post.', true);
            }
        });
    }

    // --- Edit Post ---
    function editPost(post) {
        postIdInput.value = post.id;
        postTitleInput.value = post.title;
        postContentInput.value = post.content;
        postIsPublicCheckbox.checked = post.is_public == 1;
        savePostBtn.textContent = 'Actualizar Post';
        showNotification('Cargando post para edición.');
        window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll to form
    }

    // --- Delete Post ---
    async function deletePost(id) {
        if (confirm('¿Estás seguro de que quieres eliminar este post?')) {
            try {
                const response = await fetch(`api/posts.php?id=${id}`, { method: 'DELETE' });
                const result = await response.json();

                if (result.status === 'success') {
                    showNotification(result.message);
                    // Refresh posts
                    fetchPosts('public');
                    if (currentUserId !== null) {
                        fetchPosts('private');
                    }
                } else {
                    showNotification(`Error: ${result.message}`, true);
                }
            } catch (error) {
                console.error('Error deleting post:', error);
                showNotification('Error de red al eliminar el post.', true);
            }
        }
    }

    // --- Initial Load ---
    fetchPosts('public');
    // Check if user is logged in before fetching private posts
    if (currentUserId !== null) {
        fetchPosts('private');
    }
});