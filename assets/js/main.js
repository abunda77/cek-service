/**
 * Main JavaScript functionality for the login page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Apply fade-in animation to login container
    const loginContainer = document.querySelector('.login-container');
    if (loginContainer) {
        loginContainer.classList.add('fade-in');
    }

    // Form validation
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const errorContainer = document.getElementById('error-container');
            
            let hasErrors = false;
            let errorMessages = [];

            // Reset previous errors
            if (errorContainer) {
                errorContainer.innerHTML = '';
                errorContainer.style.display = 'none';
            }

            // Validate username
            if (username === '') {
                errorMessages.push('Username harus diisi');
                document.getElementById('username').classList.add('border-red-500');
                hasErrors = true;
            } else {
                document.getElementById('username').classList.remove('border-red-500');
            }

            // Validate password
            if (password === '') {
                errorMessages.push('Password harus diisi');
                document.getElementById('password').classList.add('border-red-500');
                hasErrors = true;
            } else {
                document.getElementById('password').classList.remove('border-red-500');
            }

            // Show error messages if any
            if (hasErrors && errorContainer) {
                event.preventDefault();
                errorMessages.forEach(message => {
                    const errorElement = document.createElement('p');
                    errorElement.textContent = message;
                    errorContainer.appendChild(errorElement);
                });
                errorContainer.style.display = 'block';
            }
        });
    }

    // Add input focus effects
    const inputFields = document.querySelectorAll('.form-input');
    inputFields.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}); 