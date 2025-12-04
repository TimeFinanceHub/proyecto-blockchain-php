document.addEventListener('DOMContentLoaded', function() {
    const conversationsDiv = document.getElementById('conversations');
    const messagesDisplay = document.getElementById('messages-display');
    const messageForm = document.getElementById('message-form');
    const messageContentInput = document.getElementById('message-content');
    const chatWithUsernameSpan = document.getElementById('chat-with-username');
    const messageViewHeader = document.getElementById('message-view-header');
    const noConvoSelectedP = document.getElementById('no-convo-selected');
    const notificationContainer = document.getElementById('notification-container');
    const userSearchInput = document.getElementById('user-search-input'); // New
    const userSearchBtn = document.getElementById('user-search-btn'); // New
    const userSearchResultsDiv = document.getElementById('user-search-results'); // New

    let activeReceiverId = null;
    let activeReceiverUsername = '';

    // Helper to show notifications (reused pattern)
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

    // Helper to HTML-escape strings (reused pattern)
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // --- User Search ---
    if (userSearchBtn && userSearchInput && userSearchResultsDiv) {
        userSearchBtn.addEventListener('click', async () => {
            const query = userSearchInput.value.trim();
            if (query.length < 3) {
                showNotification('Please enter at least 3 characters to search.', true);
                return;
            }
            try {
                const response = await fetch(`api/users.php?q=${encodeURIComponent(query)}`);
                const result = await response.json();

                if (result.status === 'success') {
                    renderSearchResults(result.users);
                } else {
                    showNotification(`Error searching users: ${result.message}`, true);
                    userSearchResultsDiv.innerHTML = `<p style="padding:0.5rem;">Error searching users: ${escapeHtml(result.message)}</p>`;
                }
            } catch (error) {
                console.error('Error searching users:', error);
                showNotification('Network error searching users.', true);
                userSearchResultsDiv.innerHTML = '<p style="padding:0.5rem;">Network error searching users.</p>';
            }
        });
    }

    function renderSearchResults(users) {
        userSearchResultsDiv.innerHTML = '';
        if (users.length === 0) {
            userSearchResultsDiv.innerHTML = '<p style="padding:0.5rem;">No users found.</p>';
            return;
        }
        users.forEach(user => {
            const resultItem = document.createElement('div');
            resultItem.className = 'conversation-item'; // Reuse styling
            resultItem.dataset.userId = user.id;
            resultItem.innerHTML = `<span class="username">${escapeHtml(user.username)}</span>`;
            resultItem.addEventListener('click', () => handleUserSearchResultClick(user));
            userSearchResultsDiv.appendChild(resultItem);
        });
    }

    function handleUserSearchResultClick(user) {
        // Clear search results
        userSearchResultsDiv.innerHTML = '';
        userSearchInput.value = '';

        // Check if conversation already exists in the main list
        let convoItem = document.querySelector(`.conversation-item[data-user-id="${user.id}"]`);
        if (!convoItem) {
            // Create a new item for the conversations list if it doesn't exist
            convoItem = document.createElement('div');
            convoItem.className = 'conversation-item';
            convoItem.dataset.userId = user.id;
            convoItem.innerHTML = `<span class="username">${escapeHtml(user.username)}</span>`;
            convoItem.addEventListener('click', () => {
                // Ensure active state and message fetching
                document.querySelectorAll('.conversation-item.active').forEach(item => item.classList.remove('active'));
                convoItem.classList.add('active');
                activeReceiverId = user.id;
                activeReceiverUsername = user.username;
                chatWithUsernameSpan.textContent = escapeHtml(activeReceiverUsername);
                messageViewHeader.style.display = 'block';
                messageForm.style.display = 'flex';
                noConvoSelectedP.style.display = 'none';
                fetchConversationMessages(activeReceiverId);
            });
            conversationsDiv.prepend(convoItem); // Add to top of conversation list
        }

        // Simulate click on the new/existing conversation item
        convoItem.click();
    }

    // --- Fetch & Render Conversations ---
    async function fetchConversations() {
        conversationsDiv.innerHTML = '<p class="no-conversation-selected">Cargando conversaciones...</p>';
        try {
            const response = await fetch('api/messages.php'); // GET without other_user_id
            const result = await response.json();

            if (result.status === 'success') {
                if (result.conversations.length > 0) {
                    renderConversations(result.conversations);
                } else {
                    conversationsDiv.innerHTML = '<p class="no-conversation-selected">No hay conversaciones aún.</p>';
                }
            } else {
                showNotification(`Error cargando conversaciones: ${result.message}`, true);
                conversationsDiv.innerHTML = `<p class="no-conversation-selected">Error cargando conversaciones: ${result.message}</p>`;
            }
        } catch (error) {
            console.error('Error fetching conversations:', error);
            showNotification('Error de red al cargar conversaciones.', true);
            conversationsDiv.innerHTML = '<p class="no-conversation-selected">Error de red al cargar conversaciones.</p>';
        }
    }

    function renderConversations(conversations) {
        conversationsDiv.innerHTML = '';
        conversations.forEach(convoUser => {
            const convoItem = document.createElement('div');
            convoItem.className = 'conversation-item';
            convoItem.dataset.userId = convoUser.id;
            convoItem.innerHTML = `<span class="username">${escapeHtml(convoUser.username)}</span>`;
            
            convoItem.addEventListener('click', () => {
                // Remove active class from previous item
                document.querySelectorAll('.conversation-item.active').forEach(item => item.classList.remove('active'));
                // Add active class to current item
                convoItem.classList.add('active');

                activeReceiverId = convoUser.id;
                activeReceiverUsername = convoUser.username;
                chatWithUsernameSpan.textContent = escapeHtml(activeReceiverUsername);
                messageViewHeader.style.display = 'block';
                messageForm.style.display = 'flex';
                noConvoSelectedP.style.display = 'none'; // Hide "Select a conversation" message
                fetchConversationMessages(activeReceiverId);
            });
            conversationsDiv.appendChild(convoItem);
        });
    }

    // --- Fetch & Render Messages in Active Conversation ---
    async function fetchConversationMessages(otherUserId) {
        messagesDisplay.innerHTML = '<p style="text-align: center;">Cargando mensajes...</p>';
        try {
            const response = await fetch(`api/messages.php?other_user_id=${otherUserId}`);
            const result = await response.json();

            if (result.status === 'success') {
                renderMessages(result.messages);
            } else {
                showNotification(`Error cargando mensajes: ${result.message}`, true);
                messagesDisplay.innerHTML = `<p style="text-align: center;">Error al cargar mensajes: ${result.message}</p>`;
            }
        } catch (error) {
            console.error('Error fetching messages:', error);
            showNotification('Error de red al cargar mensajes.', true);
            messagesDisplay.innerHTML = '<p style="text-align: center;">Error de red al cargar mensajes.</p>';
        }
    }

    function renderMessages(messages) {
        messagesDisplay.innerHTML = ''; // Clear previous messages
        if (messages.length === 0) {
            messagesDisplay.innerHTML = '<p style="text-align: center;">No hay mensajes en esta conversación.</p>';
            return;
        }

        messages.forEach(msg => {
            const msgItem = document.createElement('div');
            msgItem.className = `message-item ${msg.sender_id == currentUserId ? 'sent' : 'received'}`;
            msgItem.innerHTML = `
                <div class="sender">${escapeHtml(msg.sender_username)}</div>
                <div class="content">${escapeHtml(msg.content)}</div>
                <div class="timestamp">${new Date(msg.created_at).toLocaleString()}</div>
            `;
            messagesDisplay.appendChild(msgItem);
        });
        // Scroll to bottom
        messagesDisplay.scrollTop = messagesDisplay.scrollHeight;
    }

    // --- Send Message Form Submission ---
    if (messageForm) {
        messageForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const content = messageContentInput.value.trim();

            if (!content) {
                showNotification('El mensaje no puede estar vacío.', true);
                return;
            }
            if (!activeReceiverId) {
                showNotification('Selecciona una conversación para enviar un mensaje.', true);
                return;
            }

            try {
                const response = await fetch('api/messages.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ receiver_id: activeReceiverId, content: content })
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showNotification('Mensaje enviado.');
                    messageContentInput.value = '';
                    fetchConversationMessages(activeReceiverId); // Refresh active conversation
                } else {
                    showNotification(`Error enviando mensaje: ${result.message}`, true);
                }
            } catch (error) {
                console.error('Error sending message:', error);
                showNotification('Error de red al enviar mensaje.', true);
            }
        });
    }

    // --- Initial Load ---
    fetchConversations();
});