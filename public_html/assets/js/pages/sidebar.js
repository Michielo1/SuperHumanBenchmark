// Hover over category sidebar
const sidebar = document.querySelector('.content_sidebar');
const sidebar_nav_indicator = document.querySelector('.sidebar_nav');
const links = document.querySelectorAll('.content_sidebar .link_content');

// Move indicator to the element.
function move_nav_indicator(link) {
    const sidebar_position = sidebar.getBoundingClientRect();
    const link_position = link.getBoundingClientRect();

    const top = link_position.top - sidebar_position.top;
    sidebar_nav_indicator.style.transform = `translateY(${top}px)`;
    sidebar_nav_indicator.style.height = `${link_position.height}px`;
}

// Look for active link.
const active = document.querySelector('.content_sidebar .link_content.active') || links[0];

// Move indicator to the hover of mouse.
function mouse_moving(event) {
    move_nav_indicator(event.currentTarget);
}

// Set indicator to active link when outside the sidebar.
function mouse_outside_sidebar() {
    move_nav_indicator(active);
}

// When the window resized, set it to the correct position.
function resize() {
    move_nav_indicator(active);
}

move_nav_indicator(active);

links.forEach(function (link) {
    link.addEventListener('mouseenter', mouse_moving);
});

sidebar.addEventListener('mouseleave', mouse_outside_sidebar);

window.addEventListener('resize', resize);

// Hamburger menu
const hamburger = document.getElementById('hamburger');
const menu = document.querySelector('.sidebar');
const mobile = window.matchMedia('(max-width: 1000px)');

function mobile_open(open) {
    menu.classList.toggle('open', open);
    hamburger.classList.toggle('open', open);
}

let menu_is_open = false;

hamburger.addEventListener('click', () => {
    if (!mobile.matches) {
        return;
    }

    menu_is_open = !menu_is_open;
    mobile_open(menu_is_open);

});

mobile.addEventListener('change', (ev) => {
    if (!ev.matches) {
        mobile_open(false);
    }
});

// Logout functionality
const logoutLink = document.getElementById('logout_link');
if (logoutLink) {
    logoutLink.addEventListener('click', async (e) => {
        e.preventDefault();

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || null;
            const headers = {};
            if (csrf) headers['X-CSRF-Token'] = csrf;

            const response = await apiFetch('auth/logout.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    window.location.href = '../index.php';
                } else {
                    window.location.href = '../index.php';
                }
            }
        } catch (error) {
            console.error('Logout failed:', error);
            // Fallback redirect
            window.location.href = '../index.php';
        }
    });
}
