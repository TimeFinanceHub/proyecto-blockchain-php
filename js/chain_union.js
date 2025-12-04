document.addEventListener('DOMContentLoaded', function() {
    const triggerUnionBtn = document.getElementById('trigger-union');
    const segmentA = document.getElementById('segment-a');
    const segmentB = document.getElementById('segment-b');
    const segmentC = document.getElementById('segment-c');
    const unionPoint = document.getElementById('union-point');
    const finalChain = document.getElementById('final-chain');
    const notificationContainer = document.getElementById('notification-container');

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

    if (triggerUnionBtn) {
        triggerUnionBtn.addEventListener('click', () => {
            // Reset state
            segmentA.style.transform = '';
            segmentB.style.transform = '';
            segmentC.style.transform = '';
            segmentA.classList.remove('merged');
            segmentB.classList.remove('merged');
            segmentC.classList.remove('merged');
            unionPoint.classList.remove('active');
            finalChain.classList.remove('active');
            finalChain.style.transform = 'scale(0)';
            triggerUnionBtn.disabled = true;
            triggerUnionBtn.textContent = 'Simulando...';

            showNotification('Iniciando simulación de unión...', false);

            // Phase 1: Chains move towards center
            setTimeout(() => {
                segmentA.style.transform = 'translateX(-50px)';
                segmentB.style.transform = 'translateX(0px)'; // Center
                segmentC.style.transform = 'translateX(50px)';
                showNotification('Cadenas independientes convergiendo...', false);
            }, 500);

            // Phase 2: Show union point, chains merge visually
            setTimeout(() => {
                unionPoint.classList.add('active');
                segmentA.classList.add('merged');
                segmentB.classList.add('merged');
                segmentC.classList.add('merged');
                segmentA.style.transform = 'translateX(0px)';
                segmentB.style.transform = 'translateX(0px)';
                segmentC.style.transform = 'translateX(0px)';
                showNotification('¡Unión de cadenas en progreso!', false);
            }, 2000);

            // Phase 3: Final unified chain appears
            setTimeout(() => {
                unionPoint.classList.remove('active');
                finalChain.classList.add('active');
                finalChain.style.transform = 'scale(1)';
                showNotification('¡Cadena unificada formada exitosamente!', false);
                triggerUnionBtn.disabled = false;
                triggerUnionBtn.textContent = 'Simular Unión';
            }, 4000);
        });
    }
});
