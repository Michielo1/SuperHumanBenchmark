/**
 * Theme Toggle - Handles light/dark mode preference
 * Stores preference in cookies
 */

// Get theme from cookie
function getTheme() {
    const match = document.cookie.match(/theme=([^;]+)/);
    return match ? match[1] : 'light';
}

// Set theme in cookie and update icon
function setTheme(theme) {
    // Store in cookie (expires in 1 year)
    document.cookie = `theme=${theme}; path=/; max-age=${365 * 24 * 60 * 60}`;

    // Store data attribute for future CSS implementation
    document.documentElement.setAttribute('data-theme', theme);

    // Add/remove dark-mode class for CSS
    if (theme === 'dark') {
        document.documentElement.classList.add('dark-mode');
    } else {
        document.documentElement.classList.remove('dark-mode');
    }
}

// Update which icon is visible
function updateThemeIcon(theme) {
    const isdark = theme === 'dark';
    
    document.querySelectorAll('.theme-toggle').forEach((btn) => {
        const sunIcon = btn.querySelector('.sun-icon');
        const moonIcon = btn.querySelector('.moon-icon');
        
        if (sunIcon) {
            sunIcon.style.display = isdark ? 'none' : 'inline-block';
        }

        if (moonIcon) {
            moonIcon.style.display = isdark ? 'inline-block' : 'none';
        }
    });
}

// Read any cookie by name
function getCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[1]) : null;
}

// Send theme switch analytics if analytics consent is enabled
function sendThemeSwitchAnalytics(theme) {
    try {
        // Only send when analytics consent cookie is set to "true"
        const consent = getCookie('analytics_consent');
        if (consent !== 'true') return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || null;
        const headers = { 'Content-Type': 'application/json' };
        if (csrf) headers['X-CSRF-Token'] = csrf;

        // Resolve API base: prefer server-provided `window.API_BASE_URL`, otherwise fall back to a sensible relative path
        const apiBase = (typeof window !== 'undefined' && window.API_BASE_URL)
            ? window.API_BASE_URL
            : ['pages', 'tests'].some(d => location.pathname.split('/').includes(d)) ? '../api/' : 'api/';

        apiFetch('analytics/theme-switch.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers,
            body: JSON.stringify({ theme: theme })
        }).catch(() => {
            // fail silently - analytics must not affect UX
        });
    } catch (e) {
        // swallow errors intentionally
    }
}

// Toggle between light and dark
function toggleTheme() {
    const currentTheme = getTheme();
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);
    updateThemeIcon(newTheme);
    // After theme switch, attempt to send analytics (only if consented)
    sendThemeSwitchAnalytics(newTheme);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const theme = getTheme();
    setTheme(theme);
    updateThemeIcon(theme);

    // Click listener theme button.
    document.querySelectorAll(".theme-toggle").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      toggleTheme();
    });
  });
});

