const GOAL_HITS = 15;
const canvas = document.querySelector('canvas');
const ctx = canvas.getContext("2d");
const startOverlay = document.getElementById('start-overlay');
function updateProgressBar(hits, goal = 15) {
    const el = document.getElementById("progress-bar-filling");
    if (!el) return;
    const pct = Math.max(0, Math.min(100, (hits / goal) * 100));
    el.style.width = pct + "%";
}

// Responsive canvas sizing
function resizeCanvas() {
    // Set canvas width to 80% of parent .canvas div, height to keep 2:1 ratio
    const parent = canvas.parentElement;
    const width = Math.max(300, Math.floor(parent.offsetWidth));
    const height = Math.floor(width / 2);
    canvas.width = width;
    canvas.height = height;
    // Clamp target position to always be visible
    aimState.targetX = Math.max(0, Math.min(aimState.targetX, canvas.width - aimState.targetSize));
    aimState.targetY = Math.max(0, Math.min(aimState.targetY, canvas.height - aimState.targetSize));
    renderTarget();
}

window.addEventListener('resize', resizeCanvas);
window.addEventListener('DOMContentLoaded', resizeCanvas);


// track last-sent position so we can send incremental dx/dy (not absolute coords)

const HIT_TOLERANCE = 8;

let mouseX = 0;
let mouseY = 0;

let aimState = {
    targetX: 475,
    targetY: 225,
    targetSpeed: 6,
    targetSize: 50,
    xDirection: 1,
    yDirection: 1,
    lastSentTargetX: 425,
    lastSentTargetY: 275,
    targetType: "normal"
};


// Only render target after test starts
if (!startOverlay) {
    renderTarget();
}

let testState = {
    testId: null,
    delay: null,
    special: null,
    isActive: false,
    startTime: null,
    totalTime: null
};

function getRandomInt(max) {
    return Math.floor(Math.random() * max);
}

// activates every time the user clicks

// Only allow clicks if overlay is not present
canvas.addEventListener("click", async function (e) {
    if (startOverlay && startOverlay.style.display !== 'none') {
        // Ignore clicks on canvas until overlay is gone
        return;
    }
    if (!testState.isActive) {
        // Should not happen, but safeguard
        return;
    }
    // compute accurate mouse coords on click (do not rely on last mousemove only)
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const clickX = (e.clientX - rect.left) * scaleX;
    const clickY = (e.clientY - rect.top) * scaleY;
    await submitClick(clickX, clickY);
});

if (startOverlay) {
    startOverlay.addEventListener('click', function () {
        startOverlay.style.display = 'none';
        startTest();
        renderTarget();
    });
}

function targetRuns() {
    if (mouseX > aimState.targetX - 25 &&
        mouseX < aimState.targetX + 75 &&
        mouseY > aimState.targetY - 25 &&
        mouseY < aimState.targetY + 75) {

        aimState.targetX += (aimState.targetX + 25 - mouseX) * 0.3;
        aimState.targetY += (aimState.targetY + 25 - mouseY) * 0.3;

        // makes sure the target doesnt go out of bounds
        if (aimState.targetX < 0) {
            aimState.targetX = 0;
        }
        if (aimState.targetX > canvas.width - 50) {
            aimState.targetX = canvas.width - 50;
        }
        if (aimState.targetY < 0) {
            aimState.targetY = 0;
        }
        if (aimState.targetY > canvas.height - 50) {
            aimState.targetY = canvas.height - 50;
        }

        renderTarget();
    }
}

function targetTP() {
    if (getRandomInt(30) == 0) {
        // pick coordinates fully inside the canvas so the target remains visible
        aimState.targetX = Math.floor(Math.random() * (canvas.width - aimState.targetSize));
        aimState.targetY = Math.floor(Math.random() * (canvas.height - aimState.targetSize));
        // redraw immediately so user sees the teleport
        renderTarget();
    }
}

function randomDirection() {
    switch (getRandomInt(40)) {
        case 0:
            aimState.xDirection = 1;
            aimState.yDirection = 1;
            break;
        case 1:
            aimState.xDirection = -1;
            aimState.yDirection = 1;
            break;
        case 2:
            aimState.xDirection = 1;
            aimState.yDirection = -1;
            break;
        case 3:
            aimState.xDirection = -1;
            aimState.yDirection = -1;
            break;
    }
}

function targetMoves() {

    randomDirection();

    aimState.targetX += aimState.targetSpeed * aimState.xDirection;
    aimState.targetY += aimState.targetSpeed * aimState.yDirection;

    if (aimState.targetX < 0) {
        aimState.targetX = 0;
        aimState.xDirection *= -1;
    }
    if (aimState.targetX > canvas.width - 50) {
        aimState.targetX = canvas.width - 50;
        aimState.xDirection *= -1;
    }
    if (aimState.targetY < 0) {
        aimState.targetY = 0;
        aimState.yDirection *= -1;
    }
    if (aimState.targetY > canvas.height - 50) {
        aimState.targetY = canvas.height - 50;
        aimState.yDirection *= -1;
    }
    renderTarget();
}

// actiavtes every time the mouse is moves
canvas.addEventListener("mousemove", function (e) {
    // tracks the mouse position
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    mouseX = (e.clientX - rect.left) * scaleX;
    mouseY = (e.clientY - rect.top) * scaleY;

    // moves the target if applicable
    switch (aimState.targetType) {
        case "runs": targetRuns(); break;
        case "moves": targetMoves(); break;
        case "tp": targetTP(); break;
    }

});

async function startTest() {

    try {
        // Call the start endpoint (relative path)
        const response = await apiFetch('test/aim_test.php/start', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        let result, rawText = await response.text();
        try {
            result = JSON.parse(rawText);
        } catch (jsonErr) {
            alert('Raw response from server (not JSON):\n' + rawText);
            console.error('Raw response from server (not JSON):', rawText);
            throw jsonErr;
        }

        // Log the result to console

        if (result.success) {
            // Store test data
            testState.testId = result.data.id;
            testState.delay = result.data.delay;
            testState.special = result.data.special;
            testState.isActive = true;
            testState.startTime = Date.now();
            updateProgressBar(0, 15);

        } else {
            console.error('Failed to start test:', result.message);
        }

    } catch (error) {
        console.error('Error starting test:', error);
        // Show error in the .text div
        const textDiv = document.querySelector('.text');
        if (textDiv) {
            textDiv.textContent = 'Error! Click to retry';
        }
        testState.isActive = false;
    }
}

// gives the backend the position of the mouse and how much the target has moved
// and updates values acordingly

// client-side pre-check for hits (helps UX)
function isLocalHit(mx, my) {
    return mx >= aimState.targetX - HIT_TOLERANCE &&
        mx <= aimState.targetX + aimState.targetSize + HIT_TOLERANCE &&
        my >= aimState.targetY - HIT_TOLERANCE &&
        my <= aimState.targetY + aimState.targetSize + HIT_TOLERANCE;
}

function generateType() {
    switch (getRandomInt(7)) {
        case 0: aimState.targetType = "runs"; break;
        case 1: aimState.targetType = "tp"; break;
        case 2: aimState.targetType = "waldo"; break;
        case 3: aimState.targetType = "moves"; break;
        case 4: aimState.targetType = "smol"; break;
        default: aimState.targetType = "normal"; break;
    }
}
async function submitClick(clickX = null, clickY = null) {
    // prefer click coords provided by the event handler; fall back to last-known mouse positions
    const x = (clickX !== null && clickX !== undefined) ? clickX : mouseX;
    const y = (clickY !== null && clickY !== undefined) ? clickY : mouseY;

    // client-side pre-check (fast feedback)
    const localHit = isLocalHit(x, y);
    if (!localHit) {
        return;
    }

    // send incremental dx/dy (not absolute position). This avoids sending the current absolute target to the backend.
    const dx = aimState.targetX - aimState.lastSentTargetX;
    const dy = aimState.targetY - aimState.lastSentTargetY;

    const payload = {
        "x": mouseX,
        "y": mouseY,
        "dx": dx,
        "dy": dy,
        "canvasWidth": canvas.width,
        "canvasHeight": canvas.height,
        "localHit": localHit,
        "clientTargetX": aimState.targetX,
        "clientTargetY": aimState.targetY,
        "lastSentTargetX": aimState.lastSentTargetX,
        "lastSentTargetY": aimState.lastSentTargetY,
        "clickTime": new Date().getTime(),
        "startTime": testState.startTime
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || null;
    const headers = { 'Content-Type': 'application/json' };
    if (csrf) headers['X-CSRF-Token'] = csrf;

    const response = await apiFetch('test/aim_test.php/next', {
        method: 'POST',
        credentials: 'same-origin',
        headers,
        body: JSON.stringify(payload)
    });
    const data = await response.json();


    // clamp helper
    const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
    const maxX = canvas.width - aimState.targetSize;
    const maxY = canvas.height - aimState.targetSize;

    if (data.hit) {
        // if hit, server may reposition -- use server's authoritative coordinates
        aimState.targetX = clamp(Math.round(data.x), 0, maxX);
        aimState.targetY = clamp(Math.round(data.y), 0, maxY);
        aimState.lastSentTargetX = aimState.targetX;
        aimState.lastSentTargetY = aimState.targetY;
        aimState.targetType = data.type;
        testState.totalTime = data.totalTimeTaken;

        // Check if test is over (15 hits)
        if (typeof data.num !== "undefined") {
            updateProgressBar(data.num, GOAL_HITS);

            if (data.num >= GOAL_HITS) {
                testState.isActive = false;

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                const textDiv = document.querySelector(".text");
                if (textDiv) textDiv.innerHTML = "<h1>AIMBOT completed</h1><p>Well done soldier... Your score has been saved.</p>";

                // Call endAimTest to save results
                endAimTest();

                return;
            }
        }

        testState.startTime = Date.now();
        generateType();

        if (aimState.targetType == "smol") {
            aimState.targetSize = 2;
        } else {
            aimState.targetSize = 50;
        }

        renderTarget();

    } else {
        // on miss, update our last-sent base by the increment we just reported, then clamp
        aimState.lastSentTargetX = clamp(aimState.lastSentTargetX + dx, 0, maxX);
        aimState.lastSentTargetY = clamp(aimState.lastSentTargetY + dy, 0, maxY);
        // keep visible target inside canvas too
        aimState.targetX = clamp(aimState.targetX, 0, maxX);
        aimState.targetY = clamp(aimState.targetY, 0, maxY);
    }
    // Send final POST to /eind when test is over
    async function endAimTest() {
            // Get test ID and total time from sessionStorage or testState
            const testId = testState.testId;

            // Prefer an explicit totalTime if the server returned it; otherwise compute from startTime
            const time_taken = (typeof testState.totalTime === 'number' && testState.totalTime > 0)
                ? testState.totalTime
                : (testState.startTime ? (Date.now() - testState.startTime) : 30000);

            // Read theme from cookie
            function getThemeFromCookie() {
                const match = document.cookie.match(/(?:^|; )theme=([^;]+)/);
                return match ? match[1] : 'light';
            }

            const payload = {
                id: testId,
                total_words: 15,
                time_taken: time_taken,
                theme: getThemeFromCookie()
            };
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || null;
            const headers = { 'Content-Type': 'application/json' };
            if (csrf) headers['X-CSRF-Token'] = csrf;

            const response = await apiFetch('test/aim_test.php/eind', {
                method: 'POST',
                credentials: 'same-origin',
                headers,
                body: JSON.stringify(payload)
            });
            const result = await response.json();
        } catch (error) {
            console.error('Error ending aim test:', error);
        }
    }
}

// prints the target in the canvas
function renderTarget() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    switch (aimState.targetType) {
        case "runs": ctx.fillStyle = 'green'; break;
        case "moves": ctx.fillStyle = 'yellow'; break;
        case "smol": ctx.fillStyle = 'blue'; break;
        case "tp": ctx.fillStyle = 'purple'; break;
        case "waldo": ctx.fillStyle = '#ef4135'; break;
        default: ctx.fillStyle = 'black'; break;
    }

    ctx.fillRect(aimState.targetX, aimState.targetY, aimState.targetSize, aimState.targetSize);

    if (aimState.targetType == "waldo") {
        ctx.fillStyle = '#ff0000';
        // Pixel mask for the real target
        const size = aimState.targetSize;
        const mask = Array.from({ length: size }, () => Array(size).fill(false));
        let dummies = [];
        let maxDummies = 250;
        let attempts = 0;
        while (dummies.length < maxDummies && attempts < maxDummies * 20) {
            let dummyX = Math.floor(Math.random() * (canvas.width - aimState.targetSize));
            let dummyY = Math.floor(Math.random() * (canvas.height - aimState.targetSize));
            // Simulate marking the mask
            let newMask = mask.map(row => row.slice());
            for (let dx = 0; dx < size; dx++) {
                for (let dy = 0; dy < size; dy++) {
                    let px = aimState.targetX + dx;
                    let py = aimState.targetY + dy;
                    if (
                        px >= dummyX && px < dummyX + aimState.targetSize &&
                        py >= dummyY && py < dummyY + aimState.targetSize
                    ) {
                        newMask[dx][dy] = true;
                    }
                }
            }
            // Check if at least one pixel remains visible
            let visibleCount = 0;
            for (let dx = 0; dx < size; dx++) {
                for (let dy = 0; dy < size; dy++) {
                    if (!newMask[dx][dy]) {
                        visibleCount++;
                        if (visibleCount >= 5) break;
                    }
                }
                if (visibleCount >= 5) break;
            }
            if (visibleCount >= 5) {
                // Accept this dummy, update mask
                for (let dx = 0; dx < size; dx++) {
                    for (let dy = 0; dy < size; dy++) {
                        mask[dx][dy] = newMask[dx][dy];
                    }
                }
                dummies.push({ x: dummyX, y: dummyY, w: aimState.targetSize, h: aimState.targetSize });
            }
            attempts++;
        }
        // Draw all dummies
        for (let d of dummies) {
            ctx.fillRect(d.x, d.y, d.w, d.h);
        }
    }
}
