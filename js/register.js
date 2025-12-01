document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('register-form');
    const responseContainer = document.createElement('div');
    registerForm.parentElement.appendChild(responseContainer);

    registerForm.addEventListener('submit', function (e) {
        e.preventDefault();
        responseContainer.textContent = '';

        const formData = new FormData(registerForm);
        const data = Object.fromEntries(formData.entries());

        fetch('api/register.php', {
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
                    window.location.href = 'login.php';
                }, 3000); // Redirect to login after 3 seconds
            } else {
                responseContainer.style.color = 'red';
            }
        })
        .catch(error => {
            responseContainer.textContent = 'An error occurred while trying to register.';
            responseContainer.style.color = 'red';
            console.error('Error:', error);
        });
    });
});
