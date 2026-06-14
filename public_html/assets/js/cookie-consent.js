// Cookie consent popup logic
(function () {
    function setCookie(name, value, days) {
        const maxAge = days * 24 * 60 * 60; // seconds
        document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
    }

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function showPopup() {
        const el = document.getElementById('cookie-consent-popup');
        if (el) el.style.display = 'block';
    }

    function hidePopup() {
        const el = document.getElementById('cookie-consent-popup');
        if (el) el.style.display = 'none';
    }

    // Determine API path using global variable injected by PHP (assetPath or basePath)
    var apiBase = (typeof window.assetPath !== 'undefined') ? window.assetPath : ((typeof window.basePath !== 'undefined') ? window.basePath : '');
    function postConsent(enabled) {
        // Try to sync to server, but fail silently
        // CSRF removed for consent POST
        fetch(apiBase + 'api/consent.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ analytics_enabled: !!enabled })
        }).catch(() => { });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const existing = getCookie('analytics_consent');

        // First try to sync from server; server has priority when user logged in or session known
        fetch(apiBase + 'api/consent.php', { method: 'GET', credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (data && typeof data.consent_exists !== 'undefined') {
                    if (data.consent_exists) {
                        // set cookie for quick client-side checks
                        setCookie('analytics_consent', data.analytics_enabled ? 'true' : 'false', 365);
                    } else {
                        // no consent was recorded – show the popup so the user can choose
                        showPopup();
                    }
                } else {
                    // unexpected response – show popup to be safe
                    showPopup();
                }
            }).catch(() => {
                // On error, show popup so user can choose (do not silently assume opt-out)
                showPopup();
            });

        // Attach handlers
        const accept = document.getElementById('cookie-consent-accept');
        const decline = document.getElementById('cookie-consent-decline');

        if (accept) accept.addEventListener('click', function () {
            setCookie('analytics_consent', 'true', 365);
            postConsent(true);
            hidePopup();
        });

        if (decline) decline.addEventListener('click', function () {
            setCookie('analytics_consent', 'false', 365);
            postConsent(false);
            hidePopup();
        });
    });
})();
