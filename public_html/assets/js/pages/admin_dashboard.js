// State for admin user listing (fetched from server)
let currentPage = 1;
const usersPerPage = 10;
let currentUsers = [];
let totalUsers = 0;
let currentSearch = '';

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    setupPagination();
    setupSearch();
    setupModal();

    // Fetch stats and populate users list
    fetchStats();
    fetchAdminUsers(1);
    fetchThemeAnalytics();
});

// Fetch aggregated stats from server and render into the dashboard
async function fetchStats() {
    const grid = document.getElementById('statsGrid');
    if (!grid) return;

    grid.innerHTML = '<div class="stat-loading">Loading statistics...</div>';

    try {
        const res = await apiFetch('stats.php');
        if (!res.ok) {
            grid.innerHTML = '<div class="stat-error">Failed to load statistics</div>';
            console.error('Failed to fetch stats', res.status);
            return;
        }

        const json = await res.json();
        if (!json.success || !Array.isArray(json.tests)) {
            grid.innerHTML = '<div class="stat-error">No statistics available</div>';
            console.error('Stats API error', json);
            return;
        }

        grid.innerHTML = '';

        // Sort tests by attempts descending
        json.tests.sort((a, b) => (b.attempts_count || 0) - (a.attempts_count || 0));

        // Create a card for each test
        json.tests.forEach(test => {
            const card = document.createElement('div');
            card.className = 'stat-card';

            // Normalize test name/type to support both API formats
            const testName = test.test_name ?? test.naam ?? test.name ?? 'Unknown';
            const testType = test.test_type ?? test.type ?? '';

            const avg = test.avg_score !== null ? Number(test.avg_score).toFixed(2) : '—';
            const attempts = test.attempts_count ?? 0;
            const players = test.unique_players ?? 0;

            // Determine unit suffix
            let suffix = '';
            if (testType === 'minimize') suffix = ' ms';
            else if (typeof testName === 'string' && testName.toLowerCase().includes('type')) suffix = ' WPM';

            card.innerHTML = `
                <h3>${escapeHtml(testName)}</h3>
                <div class="stat-value">${escapeHtml(String(avg))}${avg !== '—' ? suffix : ''}</div>
                <div class="stat-label">Average Score</div>
                <div class="stat-meta">Based on ${escapeHtml(String(attempts))} attempts • ${escapeHtml(String(players))} players</div>
            `;

            grid.appendChild(card);
        });



    } catch (err) {
        grid.innerHTML = '<div class="stat-error">Error loading statistics</div>';
        console.error('Error fetching stats', err);
    }
}

// Fetch theme analytics and render into dashboard
async function fetchThemeAnalytics() {
    const grid = document.getElementById('themeAnalyticsGrid');
    if (!grid) return;

    grid.innerHTML = '<div class="stat-loading">Loading theme analytics...</div>';

    try {
        const res = await apiFetch('analytics/theme-effect.php', { credentials: 'same-origin' });
        if (!res.ok) {
            grid.innerHTML = '<div class="stat-error">Failed to load theme analytics</div>';
            console.error('Failed to fetch theme analytics', res.status);
            return;
        }

        const json = await res.json();
        if (!json.success) {
            grid.innerHTML = '<div class="stat-error">No theme analytics: ' + escapeHtml(json.error || 'not available') + '</div>';
            console.error('Theme analytics API error', json);
            return;
        }

        if (!Array.isArray(json.data) || json.data.length === 0) {
            grid.innerHTML = '<div class="stat-error">No theme analytics data</div>';
            return;
        }

        grid.innerHTML = '';

        json.data.forEach(item => {
            const card = document.createElement('div');
            card.className = 'stat-card';

            const pct = item.percent_change_dark_vs_light;
            const avgLight = item.avg_light;
            const avgDark = item.avg_dark;
            const testType = item.test_type || '';
            const nameLower = (item.test_name || '').toLowerCase();

            // Determine unit suffix
            let suffix = '';
            if (testType === 'minimize') suffix = ' ms';
            else if (nameLower.includes('type')) suffix = ' WPM';

            // Format numbers
            const fmt = (v) => (v === null || v === undefined) ? 'N/A' : Number(v).toFixed(2);

            // Compute multiplier and delta where possible
            let multiplier = null;
            let delta = null;
            if (avgLight !== null && avgLight !== undefined && avgDark !== null && avgDark !== undefined) {
                if (Number(avgLight) !== 0) multiplier = Number(avgDark) / Number(avgLight);
                delta = Number(avgDark) - Number(avgLight);
            }

            // Human readable conclusion
            let conclusion = 'Comparison unavailable';
            if (pct !== null && pct !== undefined && avgLight !== null && avgDark !== null) {
                const absPct = Math.abs(Number(pct)).toFixed(2) + '%';
                if (testType === 'minimize') {
                    // lower is better
                    if (Number(pct) > 0) {
                        conclusion = `Dark is ${absPct} better (avg ${fmt(avgDark)}${suffix} vs ${fmt(avgLight)}${suffix})`;
                    } else if (Number(pct) < 0) {
                        conclusion = `Dark is ${absPct} worse (avg ${fmt(avgDark)}${suffix} vs ${fmt(avgLight)}${suffix})`;
                    } else {
                        conclusion = `No meaningful difference (both ≈ ${fmt(avgLight)}${suffix})`;
                    }
                } else {
                    // maximize (higher is better)
                    if (Number(pct) > 0) {
                        conclusion = `Dark is ${absPct} better (avg ${fmt(avgDark)}${suffix} vs ${fmt(avgLight)}${suffix})`;
                    } else if (Number(pct) < 0) {
                        conclusion = `Dark is ${absPct} worse (avg ${fmt(avgDark)}${suffix} vs ${fmt(avgLight)}${suffix})`;
                    } else {
                        conclusion = `No meaningful difference (both ≈ ${fmt(avgLight)}${suffix})`;
                    }
                }
            }

            // Multiplier string when available
            let multStr = '';
            if (multiplier !== null && !Number.isNaN(multiplier) && Number.isFinite(multiplier)) {
                multStr = ` (${multiplier.toFixed(2)}×)`;
            }

            card.innerHTML = `
                <h3>${escapeHtml(item.test_name)}</h3>
                <div class="stat-value">${escapeHtml(String(pct === null || pct === undefined ? 'N/A' : Number(pct).toFixed(2) + '%'))}</div>
                <div class="stat-label">${escapeHtml(conclusion)}${escapeHtml(multStr)}</div>
                <div class="stat-meta">Attempts with theme: ${escapeHtml(String(item.attempts_with_theme || 0))} • Total attempts: ${escapeHtml(String(item.total_attempts || 0))}</div>
            `;

            grid.appendChild(card);
        });

    } catch (err) {
        grid.innerHTML = '<div class="stat-error">Error loading theme analytics</div>';
        console.error('Error fetching theme analytics', err);
    }
}

// small helper to avoid XSS
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Render user table for current page (data provided by server)
function renderUserTable() {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '';

    const usersToDisplay = currentUsers || [];

    usersToDisplay.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(String(user.id))}</td>
            <td>${escapeHtml(user.username || 'example')}</td>
            <td>${escapeHtml(user.email || '')}</td>
            <td>${escapeHtml(user.joined || '')}</td>
            <td>${escapeHtml(String(user.total_attempts ?? 0))}</td>
            <td>
                <button class="action-btn delete-account" data-user-id="${user.id}">Delete Account</button>
                <button class="action-btn view-details" data-user-id="${user.id}">View Details</button>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Add event listeners for action buttons
    document.querySelectorAll('.delete-account').forEach(btn => {
        btn.addEventListener('click', handleDeleteAccount);
    });

    document.querySelectorAll('.view-details').forEach(btn => {
        btn.addEventListener('click', handleViewDetails);
    });

    updatePaginationInfo();
}

// Setup pagination
function setupPagination() {
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            fetchAdminUsers(currentPage - 1);
        }
    });

    nextBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(totalUsers / usersPerPage);
        if (currentPage < totalPages) {
            fetchAdminUsers(currentPage + 1);
        }
    });

    updatePaginationButtons();
}

// Update pagination buttons state
function updatePaginationButtons() {
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const totalPages = Math.ceil(totalUsers / usersPerPage) || 1;

    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages || totalPages === 0;
}

// Update pagination info display
function updatePaginationInfo() {
    const totalPages = Math.ceil(totalUsers / usersPerPage) || 1;
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
}

// Setup search functionality
function setupSearch() {
    const searchInput = document.getElementById('userSearch');
    let timer = null;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(timer);
        currentSearch = e.target.value.trim();
        timer = setTimeout(() => {
            currentPage = 1;
            fetchAdminUsers(1);
        }, 350);
    });
}

// Handle delete account action
async function handleDeleteAccount(e) {
    const userId = parseInt(e.target.dataset.userId);
    const user = (currentUsers || []).find(u => u.id === userId);

    if (!user) return alert('User not found');

    if (!confirm(`Delete account for ${user.full_name || user.email}? This action is irreversible.`)) {
        return;
    }

    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrf = csrfMeta ? csrfMeta.getAttribute('content') : null;

        const res = await apiFetch('data/delete_account.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                ...(csrf ? { 'X-CSRF-Token': csrf } : {})
            },
            body: JSON.stringify({ email: user.email })
        });

        const json = await res.json();
        if (!res.ok || !json.success) {
            alert('Failed to delete account: ' + (json.message || json.error || res.status));
            console.error('Delete account error', res.status, json);
            return;
        }

        // Remove user from currentUsers and update UI
        currentUsers = currentUsers.filter(u => u.id !== userId);
        totalUsers = Math.max(0, totalUsers - 1);
        renderUserTable();
        alert('Account deleted');

    } catch (err) {
        console.error('Error deleting account', err);
        alert('Error deleting account');
    }
}

// Handle view details action
function handleViewDetails(e) {
    const userId = parseInt(e.target.dataset.userId);
    const user = (currentUsers || []).find(u => u.id === userId);

    if (!user) return alert('User not found');

    // Populate modal with user data
    document.getElementById('modalUserId').textContent = escapeHtml(String(user.id));
    // Username provided by server
    document.getElementById('modalUsername').textContent = escapeHtml(user.username || '');
    document.getElementById('modalFirstName').textContent = escapeHtml(user.first_name || '');
    document.getElementById('modalInfix').textContent = escapeHtml(user.infix || '');
    document.getElementById('modalLastName').textContent = escapeHtml(user.last_name || '');
    document.getElementById('modalEmail').textContent = escapeHtml(user.email || '');
    document.getElementById('modalJoined').textContent = escapeHtml(user.joined || '');
    document.getElementById('modalAttempts').textContent = escapeHtml(String(user.total_attempts ?? 0));

    // Store user ID for delete account button in modal
    document.getElementById('modalDeleteAccount').dataset.userId = user.id; 

    // Show modal
    document.getElementById('userModal').classList.add('show');
}

// Setup modal functionality
function setupModal() {
    const modal = document.getElementById('userModal');
    const closeBtn = document.getElementById('closeModal');
    const closeModalBtn = document.getElementById('modalClose');
    const modalDeleteBtn = document.getElementById('modalDeleteAccount');

    // Close modal when clicking X
    closeBtn.addEventListener('click', () => {
        modal.classList.remove('show');
    });

    // Close modal when clicking Close button
    closeModalBtn.addEventListener('click', () => {
        modal.classList.remove('show');
    });

    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });

    // Handle delete account from modal
    modalDeleteBtn.addEventListener('click', (e) => {
        handleDeleteAccount(e);
        modal.classList.remove('show');
    });
}

// Fetch paginated users from server
async function fetchAdminUsers(page = 1) {
    try {
        const params = new URLSearchParams({ page, per_page: usersPerPage });
        if (currentSearch) params.set('search', currentSearch);

        const res = await apiFetch(`data/admin.php?${params.toString()}`);
        if (!res.ok) {
            console.error('Failed to fetch admin users', res.status);
            return;
        }

        const json = await res.json();
        if (!json.success) {
            console.error('Admin users API returned error', json);
            return;
        }

        currentUsers = json.users || [];
        totalUsers = json.total || 0;
        currentPage = json.page || page;

        renderUserTable();
        updatePaginationButtons();

    } catch (err) {
        console.error('Error fetching admin users', err);
    }
}

// small helper to avoid XSS
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
