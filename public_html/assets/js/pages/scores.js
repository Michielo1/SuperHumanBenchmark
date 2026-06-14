// Scores Page JavaScript
// Handles benchmark leaderboard display and tab switching

// State management
let currentBenchmark = null;
let availableTests = [];

// Initialize when page loads
document.addEventListener('DOMContentLoaded', async () => {
    await loadAvailableTests();
    if (availableTests.length > 0) {
        currentBenchmark = availableTests[0].naam;
        initializeTabs();
        loadLeaderboard(currentBenchmark);
    }
});

// Load available tests from API
async function loadAvailableTests() {
    try {
        const response = await apiFetch('tests.php');
        const text = await response.text();
        let result;

        try {
            result = JSON.parse(text);
        } catch (e) {
            console.log('Non-JSON response from tests API:', text);
            throw new Error('Invalid JSON from tests API');
        }

        if (!response.ok) {
            console.log('API Error (tests):', response.status, text);
            throw new Error(result.message || result.error || `HTTP error! status: ${response.status}`);
        }

        if (result.success && result.tests && result.tests.length > 0) {
            availableTests = result.tests;
            createTabs();
        } else {
            showTabsError();
        }
    } catch (error) {
        console.log('Error loading tests:', error);
        showTabsError();
    }
}

// Create tab buttons dynamically
function createTabs() {
    const tabsContainer = document.getElementById('benchmarkTabs');
    tabsContainer.innerHTML = '';

    availableTests.forEach((test, index) => {
        const button = document.createElement('button');
        button.className = 'tab-button' + (index === 0 ? ' active' : '');
        button.setAttribute('data-benchmark', test.naam);

        // Format test name for display (e.g., "reaction_test" -> "Reaction Test")
        const displayName = formatTestName(test.naam);
        button.textContent = displayName;

        tabsContainer.appendChild(button);
    });
}

// Format test name for display
function formatTestName(naam) {
    return naam
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

// Show error when tabs can't be loaded
function showTabsError() {
    const tabsContainer = document.getElementById('benchmarkTabs');
    tabsContainer.innerHTML = '<div class="error-tabs">Failed to load tests</div>';
}

// Initialize tab buttons
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const benchmark = button.getAttribute('data-benchmark');

            // Update active tab
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            // Load new leaderboard
            currentBenchmark = benchmark;
            loadLeaderboard(benchmark);
        });
    });
}

// Get test info by name
function getTestInfo(benchmarkName) {
    const test = availableTests.find(t => t.naam === benchmarkName);
    if (!test) return null;

    // Determine unit based on test type and name
    let unit = 'pts'; // default
    if (test.naam.includes('reaction')) {
        unit = 'ms';
    } else if (test.naam.includes('type')) {
        unit = 'WPM';
    } else if (test.naam.includes('aim')) {
        unit = 'sec';
    }

    return {
        title: formatTestName(test.naam) + ' Leaderboard',
        test_description: test.test_description || test.beschrijving || 'No description available',
        type: test.type,
        unit: unit
    };
}

// Load leaderboard data for a specific benchmark
async function loadLeaderboard(benchmarkName) {
    const info = getTestInfo(benchmarkName);

    if (!info) {
        console.error('Test not found:', benchmarkName);
        showState('error');
        return;
    }

    // Update header
    document.getElementById('currentBenchmarkTitle').textContent = info.title;
    document.getElementById('benchmarkDescription').textContent = info.test_description;

    // Show loading state
    showState('loading');

    try {
        const response = await apiFetch(`leaderboard.php?benchmark=${benchmarkName}`);
        const text = await response.text();
        let result;

        try {
            result = JSON.parse(text);
        } catch (e) {
            console.log('Non-JSON response from leaderboard API:', text);
            throw new Error('Invalid JSON from leaderboard API');
        }

        if (!response.ok) {
            // API returned an error - log the details
            console.log('API Error (leaderboard):', response.status, text);
            throw new Error(result.message || result.error || `HTTP error! status: ${response.status}`);
        }

        if (result.success && result.leaderboard && result.leaderboard.length > 0) {
            // Update benchmark info from API response
            if (result.benchmark) {
                info.test_description = result.benchmark.test_description || result.benchmark.beschrijving || info.test_description;
                info.type = result.benchmark.test_type || result.benchmark.type;
                document.getElementById('benchmarkDescription').textContent = info.test_description;
            }

            displayLeaderboard(result.leaderboard, info);
        } else {
            showState('empty');
        }
    } catch (error) {
        console.log('Error loading leaderboard:', error);
        showState('error');
    }
}

// Display leaderboard data in table
function displayLeaderboard(data, info) {
    const tbody = document.getElementById('leaderboardTableBody');
    tbody.innerHTML = '';

    data.forEach((entry, index) => {
        const rank = index + 1;
        const row = document.createElement('tr');

        // Add highlight class if this is the current user
        // if (entry.is_current_user) {
        //     row.classList.add('highlight');
        // }

        row.innerHTML = `
            <td class="rank-column">
                <span class="rank-badge rank-${rank <= 3 ? rank : 'other'}">
                    ${rank}
                </span>
            </td>
            <td class="player-column">
                <span class="player-name">${escapeHtml(entry.player_name)}</span>
            </td>
            <td class="score-column">
                <span class="score-value">${formatScore(entry.high_score, info.unit)}</span>
            </td>
            <td class="attempts-column">
                ${entry.attempts}
            </td>
            <td class="date-column">
                ${formatDate(entry.updated_at)}
            </td>
        `;

        tbody.appendChild(row);
    });

    showState('leaderboard');
}

// Format score with appropriate unit
function formatScore(score, unit) {
    return `${score} ${unit}`;
}

// Format date to readable format
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) {
        return 'Today';
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else {
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show different UI states
function showState(state) {
    const loadingState = document.getElementById('loadingState');
    const errorState = document.getElementById('errorState');
    const emptyState = document.getElementById('emptyState');
    const leaderboardContainer = document.getElementById('leaderboardContainer');

    // Hide all states
    loadingState.style.display = 'none';
    errorState.style.display = 'none';
    emptyState.style.display = 'none';
    leaderboardContainer.style.display = 'none';

    // Show requested state
    switch (state) {
        case 'loading':
            loadingState.style.display = 'flex';
            break;
        case 'error':
            errorState.style.display = 'block';
            break;
        case 'empty':
            emptyState.style.display = 'block';
            break;
        case 'leaderboard':
            leaderboardContainer.style.display = 'block';
            break;
    }
}
