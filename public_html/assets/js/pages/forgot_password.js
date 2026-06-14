document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('form');

    if (!form) {
        console.error('[ERROR] Forgot password form NOT found in DOM');
        return;
    }

    // State to ensure only one message is shown at a time and to prevent duplicate submissions
    let currentMessage = null;
    let currentMessageTimeout = null;
    let isSubmitting = false;
    const submitButton = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', async (e) => {
        console.group('[FORGOT_PASSWORD] Submit event');
        e.preventDefault();

        if (isSubmitting) {
            console.warn('[FORGOT_PASSWORD] Submit ignored: request already in progress');
            console.groupEnd();
            return;
        }

        // Get input
        const emailInput = document.getElementById('email');
        const email = emailInput?.value.trim();

        if (!email) {
            console.warn('[FORGOT_PASSWORD] Validation failed: missing email');
            showError('Please enter your email address.');
            console.groupEnd();
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            console.warn('[FORGOT_PASSWORD] Validation failed: invalid email format');
            showError('Please enter a valid email address.');
            console.groupEnd();
            return;
        }

        try {
            isSubmitting = true;
            if (submitButton) submitButton.disabled = true;

            // Use same relative path style as other pages (go up one level to /api)
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || null;
            const headers = { 'Content-Type': 'application/json' };
            if (csrf) headers['X-CSRF-Token'] = csrf;

            const response = await apiFetch('auth/forgot_password.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers,
                body: JSON.stringify({ email })
            });

            // Guarded JSON parsing: if the server returns HTML (404/500), avoid throwing raw parse errors
            let data = null;
            const contentType = response.headers.get('Content-Type') || '';
            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                console.error('[FORGOT_PASSWORD] Expected JSON response but received:', text);
                throw new Error('Invalid JSON response from server');
            }


            if (response.ok) {
                showSuccess('If the email exists, a reset link has been sent to your email address.');
                form.reset();
            } else {
                console.error('[FORGOT_PASSWORD] Request failed:', data.error);
                showError(data.error || 'An error occurred. Please try again.');
            }
        } catch (error) {
            console.error('[FORGOT_PASSWORD] Network error:', error);
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
