document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('login-form');
    const responseContainer = document.createElement('div');
    loginForm.parentElement.appendChild(responseContainer);

    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        responseContainer.textContent = '';

        const formData = new FormData(loginForm);
        const data = Object.fromEntries(formData.entries());

        fetch('api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            responseContainer.textContent = result.message;
            if (result.status === 'success') {
                responseContainer.style.color = 'green';
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 2000); // Redirect to dashboard after 2 seconds
            } else {
                responseContainer.style.color = 'red';
            }
        })
        .catch(error => {
            responseContainer.textContent = 'An error occurred while trying to log in.';
            responseContainer.style.color = 'red';
            console.error('Error:', error);
        });
    });
});
