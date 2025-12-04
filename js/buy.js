document.addEventListener('DOMContentLoaded', function() {
    const buyNowButton = document.getElementById('buy-now-button');
    const notificationContainer = document.getElementById('notification-container');

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

    if (buyNowButton) {
        buyNowButton.addEventListener('click', async function() {
            buyNowButton.disabled = true;
            buyNowButton.textContent = 'Procesando Compra...';
            showNotification('Simulando compra, por favor espera...');

            // Simulate a delay for payment processing
            await new Promise(resolve => setTimeout(resolve, 2000)); 

            showNotification('¡Compra simulada exitosa! Iniciando descarga...');
            buyNowButton.textContent = 'Descargando...';

            // Trigger the download
            window.location.href = 'api/download_project.php';

            // Re-enable button after a short delay, assuming download starts
            setTimeout(() => {
                buyNowButton.disabled = false;
                buyNowButton.textContent = 'Comprar Ahora';
            }, 3000);
        });
    }
});
