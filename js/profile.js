document.addEventListener('DOMContentLoaded', function() {
    const profileDetails = document.getElementById('profile-details');
    const profileForm = document.getElementById('profile-form');
    const usernameInput = document.getElementById('username');
    const phoneInput = document.getElementById('phone');
    const instagramInput = document.getElementById('instagram_handle'); // New
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

    // --- Fetch & Display Profile ---
    async function fetchProfile() {
        profileDetails.innerHTML = '<p>Cargando perfil...</p>';
        try {
            const response = await fetch('api/profile.php');
            const result = await response.json();

            if (result.status === 'success') {
                renderProfile(result.profile);
                prefillForm(result.profile);
            } else {
                showNotification(`Error cargando perfil: ${result.message}`, true);
                profileDetails.innerHTML = `<p>Error al cargar perfil: ${result.message}</p>`;
            }
        } catch (error) {
            console.error('Error fetching profile:', error);
            showNotification('Error de red al cargar perfil.', true);
            profileDetails.innerHTML = '<p>Error de red al cargar perfil.</p>';
        }
    }

    function renderProfile(profile) {
        profileDetails.innerHTML = `
            <div class="profile-info-item">
                <span>Username:</span>
                <span>${escapeHtml(profile.username)}</span>
            </div>
            <div class="profile-info-item">
                <span>Email:</span>
                <span>${escapeHtml(profile.email)}</span>
            </div>
            <div class="profile-info-item">
                <span>Phone:</span>
                <span>${escapeHtml(profile.phone)}</span>
            </div>
            <div class="profile-info-item">
                <span>Instagram:</span>
                <span>${escapeHtml(profile.instagram_handle || 'N/A')}</span>
            </div>
            <div class="profile-info-item">
                <span>Email Verified:</span>
                <span class="${profile.email_verified ? 'verified-status' : 'unverified-status'}">
                    ${profile.email_verified ? 'Sí' : 'No (Pendiente de Aprobación)'}
                </span>
            </div>
            <div class="profile-info-item">
                <span>Phone Verified:</span>
                <span class="${profile.phone_verified ? 'verified-status' : 'unverified-status'}">
                    ${profile.phone_verified ? 'Sí' : 'No (Pendiente de Aprobación)'}
                </span>
            </div>
        `;
    }

    function prefillForm(profile) {
        usernameInput.value = profile.username;
        phoneInput.value = profile.phone;
        instagramInput.value = profile.instagram_handle || ''; // New
    }

    // --- Profile Form Submission ---
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = usernameInput.value.trim();
            const phone = phoneInput.value.trim();
            const instagram_handle = instagramInput.value.trim(); // New

            if (!username || !phone) {
                showNotification('El nombre de usuario y el teléfono son obligatorios.', true);
                return;
            }

            try {
                const response = await fetch('api/profile.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: username, phone: phone, instagram_handle: instagram_handle }) // New
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showNotification(result.message);
                    fetchProfile(); // Refresh profile details
                } else {
                    showNotification(`Error actualizando perfil: ${result.message}`, true);
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                showNotification('Error de red al actualizar perfil.', true);
            }
        });
    }

    // --- Initial Load ---
    fetchProfile();
});
