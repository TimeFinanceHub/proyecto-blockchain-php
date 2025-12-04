document.addEventListener('DOMContentLoaded', function () {
    const usersTableBody = document.getElementById('users-table-body');
    const notificationContainer = document.getElementById('notification-container');
    const projectPriceInput = document.getElementById('project-price'); // New
    const priceSettingForm = document.getElementById('price-setting-form'); // New
    const savePriceBtn = document.getElementById('save-price-btn'); // New

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

    // Helper to HTML-escape strings
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    async function fetchUsers() {
        usersTableBody.innerHTML = '<tr><td colspan="7" class="no-users-message">Loading users...</td></tr>';
        try {
            const response = await fetch('api/admin_users.php');
            const result = await response.json();

            if (result.status === 'success') {
                renderUsers(result.users);
            } else {
                showNotification(`Error: ${result.message}`, true);
                usersTableBody.innerHTML = `<tr><td colspan="7" class="no-users-message">Error loading users: ${result.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            showNotification('An error occurred while fetching users.', true);
            usersTableBody.innerHTML = '<tr><td colspan="7" class="no-users-message">Failed to load users.</td></tr>';
        }
    }

    function renderUsers(users) {
        usersTableBody.innerHTML = ''; // Clear existing content
        if (users.length === 0) {
            usersTableBody.innerHTML = '<tr><td colspan="7" class="no-users-message">No users found.</td></tr>';
            return;
        }

        users.forEach(user => {
            const row = document.createElement('tr');
            row.dataset.userId = user.id;
            row.innerHTML = `
                <td data-label="ID">${escapeHtml(String(user.id))}</td>
                <td data-label="Username">${escapeHtml(user.username)}</td>
                <td data-label="Email">${escapeHtml(user.email)}</td>
                <td data-label="Phone">${escapeHtml(user.phone)}</td>
                <td data-label="Email Verified">
                    <input type="checkbox" class="verification-checkbox" data-type="email" ${user.email_verified ? 'checked' : ''}>
                </td>
                <td data-label="Phone Verified">
                    <input type="checkbox" class="verification-checkbox" data-type="phone" ${user.phone_verified ? 'checked' : ''}>
                </td>
                <td data-label="Actions">
                    <button class="action-btn save-verification-btn">Save</button>
                </td>
            `;
            usersTableBody.appendChild(row);
        });

        // Add event listeners for save buttons
        usersTableBody.querySelectorAll('.save-verification-btn').forEach(button => {
            button.addEventListener('click', async (event) => {
                const row = event.target.closest('tr');
                const userId = row.dataset.userId;
                const emailCheckbox = row.querySelector('[data-type="email"]');
                const phoneCheckbox = row.querySelector('[data-type="phone"]');

                const data = {
                    id: userId,
                    email_verified: emailCheckbox.checked ? 1 : 0,
                    phone_verified: phoneCheckbox.checked ? 1 : 0
                };

                try {
                    const response = await fetch('api/admin_users.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        showNotification(result.message);
                    } else {
                        showNotification(`Error: ${result.message}`, true);
                    }
                } catch (error) {
                    console.error('Error updating user verification:', error);
                    showNotification('An error occurred while updating verification status.', true);
                }
            });
        });
    }

    // --- Project Price Management ---
    async function fetchProjectPrice() {
        if (!projectPriceInput) return; // Ensure element exists
        try {
            const response = await fetch('api/settings.php');
            const result = await response.json();
            if (result.status === 'success') {
                projectPriceInput.value = result.project_price;
            } else {
                showNotification(`Error cargando precio: ${result.message}`, true);
            }
        } catch (error) {
            console.error('Error fetching project price:', error);
            showNotification('Error de red al cargar el precio del proyecto.', true);
        }
    }

    if (priceSettingForm) {
        priceSettingForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const newPrice = projectPriceInput.value;

            if (!newPrice || isNaN(newPrice) || newPrice < 0) {
                showNotification('Formato de precio inválido. Debe ser un número no negativo.', true);
                return;
            }

            try {
                const response = await fetch('api/settings.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ project_price: newPrice })
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showNotification(result.message);
                } else {
                    showNotification(`Error actualizando precio: ${result.message}`, true);
                }
            } catch (error) {
                console.error('Error updating project price:', error);
                showNotification('Error de red al actualizar el precio del proyecto.', true);
            }
        });
    }

    // Initial fetches
    fetchUsers();
    fetchProjectPrice();
});

