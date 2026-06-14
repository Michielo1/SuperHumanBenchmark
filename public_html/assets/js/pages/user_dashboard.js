// Fetch attempts from server API
async function fetchAttempts() {
    try {
        const res = await apiFetch('data/user.php', { credentials: 'same-origin' });
        if (!res.ok) {
            console.error('Failed to fetch attempts', res.status);
            return [];
        }
        const json = await res.json();
        if (!json.success) {
            console.error('API returned an error', json);
            return [];
        }
        return json.attempts || [];
    } catch (err) {
        console.error('Error fetching attempts', err);
        return [];
    }
}

// Latest fetched attempts (used for redraws)
let latestAttempts = [];

// Populate the attempts table
function populateAttemptsTable(attempts) {
    const tbody = document.getElementById('attemptsTableBody');
    tbody.innerHTML = '';

    // Helper to escape HTML
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function parseTimestamp(ts) {
        // Convert 'YYYY-MM-DD HH:MM:SS' -> 'YYYY-MM-DDTHH:MM:SS' for reliable parsing
        if (!ts) return null;
        return new Date(ts.replace(' ', 'T'));
    }

    function formatDateTime(ts) {
        const d = parseTimestamp(ts);
        if (!d || isNaN(d.getTime())) return '';
        // Format as 'YYYY-MM-DD HH:MM:SS'
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
    }

    // Sort by timestamp desc to show newest first
    attempts.sort((a, b) => {
        const da = parseTimestamp(a.timestamp);
        const db = parseTimestamp(b.timestamp);
        return (db ? db.getTime() : 0) - (da ? da.getTime() : 0);
    });

    attempts.forEach(attempt => {
        const row = document.createElement('tr');

        // Sanitize benchmark name to a safe CSS class (lowercase, non-alphanum -> hyphen)
        const safeBenchmarkClass = (attempt.benchmark || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        const statusText = attempt.status || 'completed';
        const safeStatusClass = 'status-' + statusText.toLowerCase().replace(/[^a-z0-9]+/g, '-');

        row.innerHTML = `
            <td>${escapeHtml(formatDateTime(attempt.timestamp || attempt.date || ''))}</td>
            <td><span class="benchmark-badge benchmark-${safeBenchmarkClass}">${escapeHtml(attempt.benchmark || '')}</span></td>
            <td>${escapeHtml(String(attempt.score ?? ''))}</td>
            <td><span class="status-badge ${safeStatusClass}">${escapeHtml(statusText)}</span></td>
        `;

        tbody.appendChild(row);
    });
}

// Draw a line chart on canvas (handles empty and single-point data)
function drawLineChart(canvasId, data, color) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const padding = 50;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Set background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    // Basic checks
    if (!Array.isArray(data) || data.length === 0) {
        // No data - draw a centered note
        ctx.fillStyle = '#6b7280';
        ctx.font = '14px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('No data', width / 2, height / 2);
        return;
    }

    // Calculate dimensions
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;

    // Find min and max values (guard against non-numeric)
    const scores = data.map(d => Number(d.score)).filter(s => !isNaN(s));

    if (scores.length === 0) {
        ctx.fillStyle = '#6b7280';
        ctx.font = '14px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('No numeric data', width / 2, height / 2);
        return;
    }

    let maxScore = Math.max(...scores);
    let minScore = Math.min(...scores);

    // If all scores are equal, expand range so point(s) sit pleasantly
    if (maxScore === minScore) {
        maxScore = maxScore + 1;
        minScore = minScore - 1;
    }

    const scoreRange = maxScore - minScore || 1;

    // Draw grid lines
    ctx.strokeStyle = '#d0d0d0';
    ctx.lineWidth = 1;

    // Horizontal grid lines
    for (let i = 0; i <= 5; i++) {
        const y = padding + (chartHeight / 5) * i;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.stroke();

        // Y-axis labels
        const value = maxScore - (scoreRange / 5) * i;
        ctx.fillStyle = '#6b7280';
        ctx.font = '12px sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText(Math.round(value), padding - 10, y + 4);
    }

    // Draw axes
    ctx.strokeStyle = '#1B211A';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, height - padding);
    ctx.lineTo(width - padding, height - padding);
    ctx.stroke();

    // Draw data points and line
    ctx.strokeStyle = color;
    ctx.fillStyle = color;
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    ctx.beginPath();

    const len = data.length;

    data.forEach((point, index) => {
        // X position: if only one point, center it
        const x = len === 1 ? padding + chartWidth / 2 : padding + (chartWidth / (len - 1)) * index;
        const normalizedScore = (Number(point.score) - minScore) / scoreRange;
        const y = height - padding - (normalizedScore * chartHeight);

        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }

        // Draw point
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(x, y, 5, 0, Math.PI * 2);
        ctx.fill();

        // X-axis labels
        ctx.fillStyle = '#6b7280';
        ctx.font = '12px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`#${point.attempt}`, x, height - padding + 20);

        // Score labels on points
        ctx.fillStyle = '#1B211A';
        ctx.font = 'bold 11px sans-serif';
        ctx.fillText(point.score, x, y - 10);
    });

    // Draw the line
    ctx.strokeStyle = color;
    ctx.lineWidth = 3;
    ctx.stroke();

    // Add axis labels
    ctx.fillStyle = '#1B211A';
    ctx.font = 'bold 14px sans-serif';

    // Y-axis label
    ctx.save();
    ctx.translate(15, height / 2);
    ctx.rotate(-Math.PI / 2);
    ctx.textAlign = 'center';
    ctx.fillText('Score', 0, 0);
    ctx.restore();

    // X-axis label
    ctx.textAlign = 'center';
    ctx.fillText('Attempt', width / 2, height - 10);
}

// Friendly label helper
function friendlyName(name) {
    return String(name || '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, ch => ch.toUpperCase());
}

// Initialize dashboard
async function initDashboard() {
    const attempts = await fetchAttempts();
    populateAttemptsTable(attempts);

    // Store attempts globally for resize redraws
    latestAttempts = attempts;

    // Draw all charts from attempts
    drawAllCharts(attempts);

}

// Draw all charts for a given attempts array
function drawAllCharts(attempts) {
    const canvasMap = {
        'reaction_test': { id: 'chartA', color: '#628141' },
        'type_test': { id: 'chartB', color: '#7a9a5a' },
        'aim_test': { id: 'chartC', color: '#8baa6b' }
    };

    // Group attempts by benchmark
    const grouped = {};
    attempts.forEach(a => {
        const name = a.benchmark || 'unknown';
        if (!grouped[name]) grouped[name] = [];
        grouped[name].push(a);
    });

    // For each known benchmark, prepare data and draw chart
    Object.keys(canvasMap).forEach(testName => {
        const map = canvasMap[testName];
        const group = grouped[testName] || [];

        // Sort chronologically (oldest first) using full timestamp when available
        group.sort((x, y) => {
            const dx = x.timestamp ? new Date(x.timestamp.replace(' ', 'T')) : new Date(x.date);
            const dy = y.timestamp ? new Date(y.timestamp.replace(' ', 'T')) : new Date(y.date);
            return dx - dy;
        });

        const dataPoints = group.map((g, i) => ({ attempt: i + 1, score: g.score }));

        // Set heading text if present
        const canvas = document.getElementById(map.id);
        if (canvas) {
            const h3 = canvas.previousElementSibling;
            if (h3 && h3.tagName === 'H3') {
                h3.textContent = `${friendlyName(testName)} - Performance Over Time`;
            }
            drawLineChart(map.id, dataPoints, map.color);
        }
    });
}

// Run initialization when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    initDashboard();
}

// Handle window resize (debounced) and redraw charts from latest attempts
let resizeTimer = null;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        drawAllCharts(latestAttempts);
    }, 150);
});
