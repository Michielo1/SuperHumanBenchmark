document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const urlParams = new URLSearchParams(window.location.search);
    const redirectParam = urlParams.get('redirect');

    if (!form) {
        console.error('[ERROR] Login form NOT found in DOM');
        return;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Get inputs
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const email = emailInput?.value.trim();
        const password = passwordInput?.value;

        if (!email || !password) {
            showError('Please fill in all required fields.');
            console.groupEnd();
            return;
        }

        const loginData = { email, password };
        if (redirectParam) {
            loginData.redirect = redirectParam;
        }

        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

        const originalValue =
            submitButton.tagName === 'BUTTON'
                ? submitButton.textContent
                : submitButton.value;

        submitButton.disabled = true;
        if (submitButton.tagName === 'BUTTON') {
            submitButton.textContent = 'Logging in...';
        } else {
            submitButton.value = 'Logging in...';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="csrf_token"]')?.value
                || null;
            const headers = { 'Content-Type': 'application/json' };
            if (csrfToken) headers['X-CSRF-Token'] = csrfToken;
            const response = await apiFetch('auth/login.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers,
                body: JSON.stringify(loginData)
            });

            // Try to parse JSON but gracefully handle non-JSON responses
            const raw = await response.text();
            let data = {};
            try {
                data = raw ? JSON.parse(raw) : {};
            } catch (e) {
                // Not JSON; log raw response for debugging and keep data as empty object
                console.log('[LOGIN] Server response (non-JSON):', response.status, raw);
                data = {};
            }

            if (response.ok) {
                showSuccess('Successfully logged in!');
                form.reset();

                // Server or page may provide a redirect target. Prefer server-provided redirect.
                const serverRedirect = data?.redirect || null;
                const pageRedirect = redirectParam || null;

                function safeRedirectTarget(target) {
                    if (!target) return null;
                    try {
                        const u = new URL(target, window.location.origin);
                        if (u.origin !== window.location.origin) return null;
                        return u.pathname + u.search + u.hash;
                    } catch (e) {
                        return null;
                    }
                }

                const finalTarget = safeRedirectTarget(serverRedirect) || safeRedirectTarget(pageRedirect) || 'user_dashboard.php';

                setTimeout(() => {
                    window.location.href = finalTarget;
                }, 1000);
            } else {
                // Log parsed error or raw response for debugging
                if (data && data.error) {
                    console.error('[LOGIN] Login failed:', data.error);
                    showError(data.error || 'Login failed.');
                } else {
                    console.error('[LOGIN] Login failed, server status:', response.status);
                    showError('Server error. See console for details.');
                }
                restoreButton();
            }
        } catch (error) {
            console.error('[LOGIN] Network or JS error:', error);
            showError('Network error. Please try again.');
            restoreButton();
        }

        function restoreButton() {
            submitButton.disabled = false;
            if (submitButton.tagName === 'BUTTON') {
                submitButton.textContent = originalValue;
            } else {
                submitButton.value = originalValue;
            }
        }
    });
});

function showError(message) {
    removeMessages();

    const div = document.createElement('div');
    div.className = 'message error-message';
    div.textContent = message;
    div.style.cssText = `
        padding: 12px 20px;
        margin: 10px 0;
        background-color: #fee;
        border: 1px solid #fcc;
        border-radius: 4px;
        color: #c33;
    `;

    document.querySelector('form').prepend(div);
}

function showSuccess(message) {
    removeMessages();

    const div = document.createElement('div');
    div.className = 'message success-message';
    div.textContent = message;
    div.style.cssText = `
        padding: 12px 20px;
        margin: 10px 0;
        background-color: #e6fffa;
        border: 1px solid #81e6d9;
        border-radius: 4px;
        color: #065f46;
    `;

    document.querySelector('form').prepend(div);
}

function removeMessages() {
    document.querySelectorAll('.message').forEach(m => m.remove());
}
