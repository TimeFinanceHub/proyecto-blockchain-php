document.addEventListener('DOMContentLoaded', function () {
    const usersTableBody = document.getElementById('users-table-body');
    const notificationContainer = document.getElementById('notification-container');

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
                <td data-label="ID">${user.id}</td>
                <td data-label="Username">${user.username}</td>
                <td data-label="Email">${user.email}</td>
                <td data-label="Phone">${user.phone}</td>
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

    // Initial fetch of users
    fetchUsers();
});

