/**
 * Registration Page JavaScript
 * Handles form submission and API call for user registration
 */

document.addEventListener('DOMContentLoaded', function () {

    function username_valid(username) {
        return /^[a-zA-Z0-9._-]{3,50}$/.test(username);
    }

    function name_valid(name) {
        return /^[\p{L}][\p{L}\p{M}\s'\-.]{0,49}$/u.test(name);
    }

    function infix_valid(infix) {
        if (!infix) {
            return true;
        }
        return /^[\p{L}\p{M}\s'\-.]{1,20}$/u.test(infix);
    }

    function email_valid(email) {
        return /^[a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,}$/.test(email);
    }

    const form = document.querySelector('form');

    if (!form) {
        console.error('Registration form not found');
        return;
    }


    // Prevent default form submission and handle via API
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Get form values
        const userName = document.getElementById('username')?.value.trim();
        const firstName = document.getElementById('fname')?.value.trim() || document.getElementById('first_name')?.value.trim();
        const infix = document.getElementById('infix')?.value.trim();
        const lastName = document.getElementById('lname')?.value.trim() || document.getElementById('last_name')?.value.trim();
        const email = document.getElementById('email')?.value.trim();
        const password = document.getElementById('password')?.value;

        const agreeTerms = document.getElementById('agree_terms');

        if (!agreeTerms || !agreeTerms.checked) {
            showError('You must agree to our Policy to create an account');
            return;
        }

        // Basic client-side validation
        if (!userName || !firstName || !lastName || !email || !password) {
            console.error('Validation failed: missing fields');
            showError('Please fill in all required fields');
            return;
        }

        if (!username_valid(userName)) {
            showError('Username may only contain letters, numbers, dots, underscores and hyphens, and must be 3–50 characters long.');
            return;
        }

        if (!name_valid(firstName)) {
            showError('Firstname may only contain letters, spaces, -, .');
            return;
        }

        if (!infix_valid(infix)) {
            showError('Infix may only contain letters and spaces max 20 characters.');
            return;
        }
        
        if (!name_valid(lastName)) {
                showError('Last name may only contain letters, spaces, -, .');
            return;
        }

        if (!email_valid(email.toLowerCase())) {
            showError('Email may only contain lowercase letters, numbers, ., _, -, @.');
            return;
        }

        if (password.length < 8) {
            console.error('Validation failed: password too short');
            showError('Password must be at least 8 characters long');
            return;
        }

        if (password.length > 60) {
            console.error('Validation failed: password too long');
            showError('Password must be shorter than 60 characters.');
            return;
        }


        const registrationData = {
            username: userName,
            first_name: firstName,
            last_name: lastName,
            email: email,
            password: password
        };

        // Add infix only if provided
        if (infix) {
            registrationData.infix = infix;
        }

        // Disable submit button during request
        const submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
        const originalValue = submitButton.value || submitButton.textContent;
        submitButton.disabled = true;

        if (submitButton.tagName === 'BUTTON') {
            submitButton.textContent = 'Creating account...';
        } else {
            submitButton.value = 'Creating account...';
        }


        try {
            // Read CSRF token from meta tag or hidden input (fallback)
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="csrf_token"]')?.value
                || null;

            // Build headers and include CSRF token when available
            const headers = {
                'Content-Type': 'application/json'
            };
            if (csrf) headers['X-CSRF-Token'] = csrf;

            // Use relative path from pages/ to api/
            const response = await apiFetch('auth/register.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers,
                body: JSON.stringify(registrationData)
            });

            let data;
            try {
                data = await response.json();
            } catch (err) {
                const text = await response.text();
                console.error('Non-JSON response from register:', response.status, text);
                data = { error: text };
            }

            if (response.ok) {
                // Registration successful
                showSuccess('Account created successfully! Redirecting to login...');

                // Clear form
                form.reset();
                removeMessages();
                // Redirect to login page after 2 seconds
                setTimeout(() => {
                    window.location.href = 'login_page.php';
                }, 2000);
            } else {
                // Registration failed
                console.error('Registration failed:', data.error);
                showError(data.error || 'Registration failed. Please try again.');
                submitButton.disabled = false;

                if (submitButton.tagName === 'BUTTON') {
                    submitButton.textContent = originalValue;
                } else {
                    submitButton.value = originalValue;
                }
            }
        } catch (error) {
            console.error('Registration error:', error);
            showError('Network error. Please check your connection and try again.');
            submitButton.disabled = false;

            if (submitButton.tagName === 'BUTTON') {
                submitButton.textContent = originalValue;
            } else {
                submitButton.value = originalValue;
            }
        }
    });
});

/**
 * Display error message to user
 */
function showError(message) {
    const slot = document.querySelector('.message-slot');
    if (!slot) return;

    slot.className = 'message message-slot error-message';
    slot.textContent = message;
    slot.style.cssText = `
        padding: 12px 20px;
        margin: 10px 0;
        background-color: #fee;
        border: 1px solid #fcc;
        border-radius: 4px;
        color: #c33;
        font-size: 14px;
    `;
}


/**
 * Display success message to user
 */
function showSuccess(message) {
    const slot = document.querySelector('.message-slot');
    if (!slot) return;

    slot.className = 'message message-slot success-message';
    slot.textContent = message;
    slot.style.cssText = `
        padding: 12px 20px;
        margin: 10px 0;
        background-color: #efe;
        border: 1px solid #cfc;
        border-radius: 4px;
        color: #3c3;
        font-size: 14px;
    `;
}


/**
 * Remove all message elements
 */
function removeMessages() {
    const slot = document.querySelector('.message-slot');
    if (!slot) return;

    // reset to invisible reserved space
    slot.className = 'message message-slot';
    slot.textContent = '';
    slot.style.cssText = '';
}

