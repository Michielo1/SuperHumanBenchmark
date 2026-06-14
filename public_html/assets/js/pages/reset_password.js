document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('form');
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');


    if (!form) {
        console.error('[ERROR] Reset password form NOT found in DOM');
        return;
    }

    // State to ensure only one message is shown at a time and to prevent duplicate submissions
    let currentMessage = null;
    let currentMessageTimeout = null;
    let isSubmitting = false;
    const submitButton = form.querySelector('button[type="submit"]');

    // Check if token is present
    if (!token) {
        console.error('[ERROR] No reset token provided in URL');
        showError('Invalid reset link. Please request a new password reset.');
        return;
    }


    form.addEventListener('submit', async (e) => {
        console.group('[RESET_PASSWORD] Submit event');
        e.preventDefault();

        if (isSubmitting) {
            console.warn('[RESET_PASSWORD] Submit ignored: request already in progress');
            console.groupEnd();
            return;
        }

        // Get inputs
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const password = passwordInput?.value;
        const confirmPassword = confirmPasswordInput?.value;

        if (!password || !confirmPassword) {
            console.warn('[RESET_PASSWORD] Validation failed: missing fields');
            showError('Please fill in all required fields.');
            console.groupEnd();
            return;
        }

        // Validate password length
        if (password.length < 8) {
            console.warn('[RESET_PASSWORD] Validation failed: password too short');
            showError('Password must be at least 8 characters long.');
            console.groupEnd();
            return;
        }

        // Validate passwords match
        if (password !== confirmPassword) {
            console.warn('[RESET_PASSWORD] Validation failed: passwords do not match');
            showError('Passwords do not match.');
            console.groupEnd();
            return;
        }

        try {
            isSubmitting = true;
            if (submitButton) submitButton.disabled = true;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || null;
            const headers = { 'Content-Type': 'application/json' };
            if (csrf) headers['X-CSRF-Token'] = csrf;

            const response = await apiFetch(`auth/reset_password.php?token=${token}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers,
                body: JSON.stringify({ password })
            });

            // Guarded JSON parsing
            let data = null;
            const contentType = response.headers.get('Content-Type') || '';
            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                console.error('[RESET_PASSWORD] Expected JSON response but received:', text);
                throw new Error('Invalid JSON response from server');
            }


            if (response.ok) {
                showSuccess('Password reset successfully! You can now login with your new password.');
                form.reset();
                // Redirect to login page after 3 seconds
                setTimeout(() => {
                    window.location.href = 'login_page.php';
                }, 3000);
            } else {
                console.error('[RESET_PASSWORD] Request failed:', data.error);
                showError(data.error || 'An error occurred. Please try again.');
            }
        } catch (error) {
            console.error('[RESET_PASSWORD] Network error:', error);
            showError('An error occurred. Please check your connection and try again.');
        } finally {
            isSubmitting = false;
            if (submitButton) submitButton.disabled = false;
        }

        console.groupEnd();
    });

    function clearCurrentMessage() {
        if (currentMessage) {
            currentMessage.remove();
            currentMessage = null;
        }
        if (currentMessageTimeout) {
            clearTimeout(currentMessageTimeout);
            currentMessageTimeout = null;
        }
    }

    function showError(message) {
        // If an error is already shown, just update the text and reset its timer
        if (currentMessage && currentMessage.classList.contains('error-message')) {
            currentMessage.textContent = message;
            if (currentMessageTimeout) clearTimeout(currentMessageTimeout);
            currentMessageTimeout = setTimeout(clearCurrentMessage, 5000);
            return;
        }

        // Remove any existing message (success or error)
        clearCurrentMessage();

        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;

        if (form) {
            form.insertBefore(errorDiv, form.firstChild);
        }

        currentMessage = errorDiv;
        currentMessageTimeout = setTimeout(clearCurrentMessage, 5000);
    }

    function showSuccess(message) {
        // If a success is already shown, just update the text and reset its timer
        if (currentMessage && currentMessage.classList.contains('success-message')) {
            currentMessage.textContent = message;
            if (currentMessageTimeout) clearTimeout(currentMessageTimeout);
            currentMessageTimeout = setTimeout(clearCurrentMessage, 10000);
            return;
        }

        // Remove any existing message (success or error)
        clearCurrentMessage();

        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;

        if (form) {
            form.insertBefore(successDiv, form.firstChild);
        }

        currentMessage = successDiv;
        currentMessageTimeout = setTimeout(clearCurrentMessage, 10000);
    }
});
