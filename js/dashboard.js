document.addEventListener('DOMContentLoaded', function () {

    // --- Common Elements & Helpers ---
    const notificationContainer = document.getElementById('notification-container');

    function showNotification(message, isError = false) {
        const notification = document.createElement('div');
        notification.className = `notification ${isError ? 'error' : ''}`;
        notification.textContent = message;
        
        notificationContainer.appendChild(notification);

        // Trigger the animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Hide and remove the notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            notification.addEventListener('transitionend', () => {
                notification.remove();
            });
        }, 3000);
    }
    
    // --- Blockchain ---
    const blockchainSection = document.getElementById('blockchain');
    if (blockchainSection) {
        const chainContainer = document.createElement('div');
        chainContainer.id = 'chain-container';
        const mineForm = `
            <div class="mine-form">
                <h3>Mine a New Block</h3>
                <textarea id="block-data" placeholder="Enter data for the new block"></textarea>
                <button id="mine-button">Mine Block</button>
            </div>
        `;
        blockchainSection.innerHTML += mineForm;
        blockchainSection.appendChild(chainContainer);
        
        const mineButton = document.getElementById('mine-button');
        const blockDataTextarea = document.getElementById('block-data');

        async function fetchChain(showMinedAnimation = false) {
            try {
                const response = await fetch('api/get_chain.php');
                const result = await response.json();
                if (result.status === 'success') {
                    renderChain(result.chain, showMinedAnimation);
                } else {
                    showNotification(result.message, true);
                }
            } catch (error) {
                console.error('Error fetching chain:', error);
            }
        }

        function renderChain(chain, showMinedAnimation) {
            chainContainer.innerHTML = '<h4>Current Blockchain:</h4>';
            chain.forEach((block, index) => {
                const blockElement = document.createElement('div');
                blockElement.className = 'block';
                if (showMinedAnimation && index === chain.length - 1) {
                    blockElement.classList.add('fade-in');
                }
                blockElement.innerHTML = `
                    <div class="block-header">Block #${block.index}</div>
                    <p><strong>Timestamp:</strong> ${new Date(block.timestamp * 1000).toUTCString()}</p>
                    <p><strong>Data:</strong> ${typeof block.data === 'object' ? JSON.stringify(block.data) : block.data}</p>
                    <p><strong>Hash:</strong> <span class="hash">${block.hash}</span></p>
                    <p><strong>Previous Hash:</strong> <span class="hash">${block.previousHash}</span></p>
                    <p><strong>Nonce:</strong> ${block.nonce}</p>
                `;
                chainContainer.appendChild(blockElement);
            });
        }

        mineButton.addEventListener('click', async function () {
            const data = blockDataTextarea.value;
            if (!data) {
                showNotification('Block data cannot be empty.', true);
                return;
            }

            mineButton.classList.add('is-mining');
            mineButton.disabled = true;
            mineButton.textContent = 'Mining...';

            try {
                const response = await fetch('api/mine_block.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ data: data })
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showNotification('Block mined successfully!');
                    blockDataTextarea.value = '';
                    fetchChain(true); // Refresh and animate the new block
                } else {
                    showNotification(`Error: ${result.message}`, true);
                }
            } catch (error) {
                showNotification('An error occurred while mining.', true);
                console.error('Mining error:', error);
            } finally {
                mineButton.classList.remove('is-mining');
                mineButton.disabled = false;
                mineButton.textContent = 'Mine Block';
            }
        });

        fetchChain();
    }

    // --- YouTube Gallery (CRUD) ---
    const videoGalleryContainer = document.getElementById('video-gallery-container');
    const youtubeUrlInput = document.getElementById('youtube-url');
    const addVideoButton = document.getElementById('add-video-button');

    async function fetchVideos() {
        try {
            const response = await fetch('api/videos.php');
            const result = await response.json();
            if (result.status === 'success') {
                renderVideos(result.videos);
            } else if (response.status !== 401) {
                showNotification(result.message, true);
            }
        } catch (error) {
            console.error('Error fetching videos:', error);
        }
    }

    function renderVideos(videos, newVideoId = null) {
        if (!videos || videos.length === 0) {
            videoGalleryContainer.innerHTML = '<p>No videos in your gallery yet. Add one above!</p>';
            return;
        }
        
        if (newVideoId === null) { // Full re-render
             videoGalleryContainer.innerHTML = '';
        }

        videos.forEach(video => {
            // Avoid re-rendering existing items on add
            if (newVideoId !== null && video.id !== newVideoId) return;

            const videoWrapper = document.createElement('div');
            videoWrapper.className = 'video-wrapper fade-in';
            videoWrapper.dataset.id = video.id;
            
            videoWrapper.innerHTML = `
                <iframe src="https://www.youtube.com/embed/${video.video_id}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <button class="delete-video-btn">Delete</button>
            `;
            
            videoWrapper.querySelector('.delete-video-btn').onclick = () => deleteVideo(video.id);
            
            if (videoGalleryContainer.querySelector('p')) {
                videoGalleryContainer.innerHTML = '';
            }
            videoGalleryContainer.appendChild(videoWrapper);
        });
    }

    addVideoButton.addEventListener('click', async () => {
        const url = youtubeUrlInput.value.trim();
        if (!url) {
            showNotification('Please enter a YouTube URL.', true);
            return;
        }
        try {
            const response = await fetch('api/videos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: url })
            });
            const result = await response.json();
            if (result.status === 'success') {
                showNotification('Video added!');
                youtubeUrlInput.value = '';
                renderVideos([result.video], result.video.id);
            } else {
                showNotification(`Error: ${result.message}`, true);
            }
        } catch (error) {
            console.error('Error adding video:', error);
        }
    });

    async function deleteVideo(id) {
        const videoWrapper = document.querySelector(`.video-wrapper[data-id='${id}']`);
        if (videoWrapper && confirm('Are you sure you want to delete this video?')) {
            videoWrapper.classList.add('fade-out');
            videoWrapper.addEventListener('animationend', async () => {
                try {
                    const response = await fetch(`api/videos.php?id=${id}`, { method: 'DELETE' });
                    const result = await response.json();
                    if (result.status === 'success') {
                        showNotification('Video deleted.');
                        videoWrapper.remove();
                         if (videoGalleryContainer.childElementCount === 0) {
                            videoGalleryContainer.innerHTML = '<p>No videos in your gallery yet. Add one above!</p>';
                        }
                    } else {
                        showNotification(`Error: ${result.message}`, true);
                        videoWrapper.classList.remove('fade-out');
                    }
                } catch (error) {
                    console.error('Error deleting video:', error);
                    videoWrapper.classList.remove('fade-out');
                }
            });
        }
    }

    // --- To-Do List ---
    const taskList = document.getElementById('task-list');
    const newTaskInput = document.getElementById('new-task-input');
    const addTaskButton = document.getElementById('add-task-button');

    async function fetchTasks() {
        try {
            const response = await fetch('api/todo.php');
            const result = await response.json();
            if (result.status === 'success') {
                renderTasks(result.tasks);
            } else if (response.status !== 401) {
                showNotification(result.message, true);
            }
        } catch (error) {
            console.error('Error fetching tasks:', error);
        }
    }

    function renderTasks(tasks, newTask = null) {
        if (!newTask) { // Full re-render if no new task, otherwise append
            taskList.innerHTML = '';
        }
        
        if (!tasks || tasks.length === 0) {
            taskList.innerHTML = '<p>No tasks yet. Add one above!</p>';
            return;
        }

        tasks.forEach(task => {
            // If we are adding a new task, only render the new one
            if (newTask && task.id !== newTask.id) return;

            const li = document.createElement('li');
            li.dataset.id = task.id;
            li.className = task.is_completed ? 'completed fade-in' : 'fade-in';
            
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = task.is_completed;
            checkbox.addEventListener('change', () => toggleTaskCompletion(task.id, !task.is_completed));

            const span = document.createElement('span');
            span.textContent = task.task;

            const deleteButton = document.createElement('button');
            deleteButton.textContent = 'Delete';
            deleteButton.className = 'delete-btn';
            deleteButton.addEventListener('click', () => deleteTask(task.id));

            li.appendChild(checkbox);
            li.appendChild(span);
            li.appendChild(deleteButton);
            taskList.appendChild(li);
        });
    }
    
    addTaskButton.addEventListener('click', async () => {
        const taskText = newTaskInput.value.trim();
        if (taskText) {
            try {
                const response = await fetch('api/todo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ task: taskText })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    showNotification('Task added!');
                    newTaskInput.value = '';
                    renderTasks([result.task], result.task); // Render only the new task
                } else {
                    showNotification(`Error: ${result.message}`, true);
                }
            } catch (error) {
                console.error('Error adding task:', error);
            }
        }
    });

    async function toggleTaskCompletion(id, is_completed) {
        const taskElement = document.querySelector(`#task-list li[data-id='${id}']`);
        if (taskElement) {
            taskElement.classList.toggle('completed', is_completed);
            try {
                await fetch('api/todo.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, is_completed: is_completed })
                });
                showNotification(`Task ${is_completed ? 'completed' : 'uncompleted'}!`);
            } catch (error) {
                console.error('Error updating task:', error);
                showNotification('Error updating task.', true);
                taskElement.classList.toggle('completed', !is_completed); // Revert if error
            }
        }
    }

    async function deleteTask(id) {
        const taskElement = document.querySelector(`#task-list li[data-id='${id}']`);
        if (taskElement && confirm('Are you sure you want to delete this task?')) {
            taskElement.classList.add('fade-out');
            taskElement.addEventListener('animationend', async () => {
                try {
                    await fetch(`api/todo.php?id=${id}`, { method: 'DELETE' });
                    showNotification('Task deleted.');
                    taskElement.remove();
                     if (taskList.childElementCount === 0) {
                        taskList.innerHTML = '<p>No tasks yet. Add one above!</p>';
                    }
                } catch (error) {
                    console.error('Error deleting task:', error);
                    showNotification('Error deleting task.', true);
                    taskElement.classList.remove('fade-out'); // Revert animation if error
                }
            });
        }
    }

    // --- Initial Data Load ---
    fetchVideos();
    // --- Video Notes ---
    const videoNotesPanel = document.getElementById('video-notes-panel');
    const toggleNotesPanelBtn = document.getElementById('toggle-notes-panel-btn');
    const closeNotesPanelBtn = document.getElementById('close-notes-panel-btn');
    const videoNoteForm = document.getElementById('video-note-form');
    const noteIdInput = document.getElementById('note-id');
    const noteVideoUrlInput = document.getElementById('note-video-url');
    const noteTimestampInput = document.getElementById('note-timestamp');
    const noteTitleInput = document.getElementById('note-title');
    const noteContentInput = document.getElementById('note-content');
    const notesList = document.getElementById('notes-list');
    const saveNoteBtn = document.getElementById('save-note-btn');

    // Toggle panel visibility
    if (toggleNotesPanelBtn) {
        toggleNotesPanelBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default link behavior
            videoNotesPanel.classList.toggle('open');
            if (videoNotesPanel.classList.contains('open')) {
                fetchNotes();
            }
        });
    }

    if (closeNotesPanelBtn) {
        closeNotesPanelBtn.addEventListener('click', () => {
            videoNotesPanel.classList.remove('open');
        });
    }

    // Fetch and render notes
    async function fetchNotes() {
        try {
            const response = await fetch('api/video_notes.php');
            const result = await response.json();
            if (result.status === 'success') {
                renderNotes(result.notes);
            } else if (response.status !== 401) {
                showNotification(result.message, true);
            }
        } catch (error) {
            console.error('Error fetching notes:', error);
            showNotification('Error loading video notes.', true);
        }
    }

    function renderNotes(notes) {
        notesList.innerHTML = ''; // Clear existing notes
        if (!notes || notes.length === 0) {
            notesList.innerHTML = '<p class="no-notes-message">No hay notas guardadas aún.</p>';
            return;
        }

        notes.forEach(note => {
            const li = document.createElement('li');
            li.dataset.id = note.id;
            li.className = 'video-note-item fade-in';
            li.innerHTML = `
                <h4>${note.title}</h4>
                ${note.video_url ? `<p>Video: <a href="${note.video_url}" target="_blank">${note.video_url.substring(0, 30)}...</a></p>` : ''}
                ${note.timestamp_in_video ? `<p>Tiempo: ${formatTime(note.timestamp_in_video)}</p>` : ''}
                <p>${note.content.substring(0, 100)}...</p>
                <div class="note-actions">
                    <button class="edit-note-btn">Editar</button>
                    <button class="delete-note-btn">Eliminar</button>
                </div>
            `;
            notesList.appendChild(li);

            li.querySelector('.edit-note-btn').addEventListener('click', () => editNote(note));
            li.querySelector('.delete-note-btn').addEventListener('click', () => deleteNote(note.id));
        });
    }

    // Helper to format time (e.g., 120 -> 02:00)
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
    }

    // Add/Edit Note Form Submission
    if (videoNoteForm) {
        videoNoteForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = noteIdInput.value;
            const video_url = noteVideoUrlInput.value.trim() || null;
            const timestamp_in_video = noteTimestampInput.value.trim() || null;
            const title = noteTitleInput.value.trim();
            const content = noteContentInput.value.trim();

            if (!title || !content) {
                showNotification('El título y el contenido de la nota son obligatorios.', true);
                return;
            }

            const method = id ? 'PUT' : 'POST';
            const url = 'api/video_notes.php';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        video_url: video_url,
                        timestamp_in_video: timestamp_in_video,
                        title: title,
                        content: content
                    })
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showNotification(result.message);
                    // Clear form
                    noteIdInput.value = '';
                    noteVideoUrlInput.value = '';
                    noteTimestampInput.value = '';
                    noteTitleInput.value = '';
                    noteContentInput.value = '';
                    if (saveNoteBtn) saveNoteBtn.textContent = 'Guardar Nota';
                    fetchNotes(); // Refresh list
                } else {
                    showNotification(`Error: ${result.message}`, true);
                }
            } catch (error) {
                console.error('Error saving note:', error);
                showNotification('An error occurred while saving the note.', true);
            }
        });
    }


    // Populate form for editing
    function editNote(note) {
        noteIdInput.value = note.id;
        noteVideoUrlInput.value = note.video_url || '';
        noteTimestampInput.value = note.timestamp_in_video || '';
        noteTitleInput.value = note.title;
        noteContentInput.value = note.content;
        if (saveNoteBtn) saveNoteBtn.textContent = 'Actualizar Nota';
        videoNotesPanel.classList.add('open'); // Ensure panel is open
        showNotification('Cargando nota para edición.');
    }

    // Delete Note
    async function deleteNote(id) {
        const noteItem = document.querySelector(`#notes-list li[data-id='${id}']`);
        if (noteItem && confirm('¿Estás seguro de que quieres eliminar esta nota?')) {
            noteItem.classList.add('fade-out');
            noteItem.addEventListener('animationend', async () => {
                try {
                    const response = await fetch(`api/video_notes.php?id=${id}`, { method: 'DELETE' });
                    const result = await response.json();
                    if (result.status === 'success') {
                        showNotification('Nota eliminada.');
                        noteItem.remove();
                        if (notesList.childElementCount === 0) {
                            notesList.innerHTML = '<p class="no-notes-message">No hay notas guardadas aún.</p>';
                        }
                    } else {
                        showNotification(`Error: ${result.message}`, true);
                        noteItem.classList.remove('fade-out'); // Revert animation if error
                    }
                } catch (error) {
                    console.error('Error deleting note:', error);
                    showNotification('An error occurred while deleting the note.', true);
                    noteItem.classList.remove('fade-out'); // Revert animation if error
                }
            });
        }
    }

});

