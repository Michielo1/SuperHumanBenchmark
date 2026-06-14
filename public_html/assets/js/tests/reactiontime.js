/**
 * Reaction Time Test JavaScript
 * Handles the reaction time test game logic and API communication
 */

// Test state
let testState = {
    testId: null,
    delay: null,
    special: null,
    isActive: false,
    startTime: null
};

// DOM elements
const coloredBox = document.querySelector('.colored-box');
const textInBox = document.querySelector('.text-in-box');
const lastResultText = document.querySelector('.last-result');


let currentColor = "red";
let timeouts = []; // All timeouts that have to be stopped if the test ends early
let round = 0;
// Defer initial round fetch until DOMContentLoaded so apiFetch (from api.js) is available.
// getRound() will be called after DOMContentLoaded below.

/**
 * Gets the current round number from the server and updates the local round to that number
 * This function makes sure the progress bar stays correct when refreshing the page whilst in the middle of a test.
 */
async function getRound() {
    try {
        // Call the getRound endpoint (relative path)
        const response = await apiFetch('test/reaction_test.php/getRound', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const raw = await response.text();
        let result = null;
        try {
            result = raw ? JSON.parse(raw) : null;
        } catch (e) {
            console.error('Error getting round: non-JSON response', raw.slice(0, 400));
            throw e;
        }

        round = result?.round || 0;
        document.getElementById("progress-bar-filling").style.width = 10 * round + "%";
    } catch (error) {
        console.error('Error getting round:', error);
    }
}

/**
 * Initialize the test when the page loads
 */
document.addEventListener('DOMContentLoaded', function () {
    // Add click event to the colored box
    coloredBox.addEventListener('click', handleBoxClick);
    // Fetch current round after DOM is loaded and after deferred scripts (like api.js) have executed
    getRound();
});

/**
 * Handle clicks on the colored box
 */
async function handleBoxClick() {
    if (!testState.isActive) {
        // Start the test
        startTest();
    } else {
        testState.isActive = false;
        timeouts.forEach(timeout => {
            clearTimeout(timeout);
        });
        const succeeded = await submitReactionData();
        if (succeeded) {
            stopTest();
        } else {
            textInBox.textContent = "Wrong click to try again";
            document.getElementById("progress-bar-filling").style.width = "0%";
        }
        currentColor = "red";
        coloredBox.style.backgroundColor = "#ff0000";
    }
}

/**
 * Start the reaction time test by calling the API
 */
async function startTest() {
    lastResultText.textContent = "";
    try {
        // Call the start endpoint (relative path)
        const response = await apiFetch('test/reaction_test.php/start', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        let result;
        const raw = await response.text();
        try {
            result = raw ? JSON.parse(raw) : null;
        } catch (e) {
            console.error('Start API returned non-JSON response:', raw.slice(0, 400));
            textInBox.textContent = 'Server error: see console';
            throw new Error('Invalid JSON response from start endpoint');
        }

        // Log the result to console

        if (result.success) {
            // Store test data
            testState.testId = result.data.id;
            testState.delay = result.data.delay;
            testState.special = result.data.special;
            testState.isActive = true;

            // Update UI
            textInBox.textContent = 'Wait for green...';
            coloredBox.classList.add('waiting');

            prepareBoxChange();
        } else {
            console.error('Failed to start test:', result.message);
        }

    } catch (error) {
        console.error('Error starting test:', error);
        textInBox.textContent = 'Error! Click to retry';
        testState.isActive = false;
    }
}

/**
 * Stop the test and submit results to the API
 */
async function stopTest() {
    if (round != 0) {
        textInBox.textContent = "Click to start the next round"
    } else {
        textInBox.textContent = "You finished the test! Click to play again";
    }
    if (testState.special == "moveBox" || testState.special == "moveBoxFakeout") {
        moveBoxUp();
    }
    // Will need to send: id, refresh_rate, start_time, click_time, delay
}

function moveBoxUp() {
    let box = document.getElementById("colored-box");
    box.style.marginTop = "0px";
}

class Color {
    constructor(name, hex) {
        this.name = name;
        this.hex = hex;
    }
}

let colors = [new Color("blue", "#4444ff"),
new Color("red", "#ff0000"),
new Color("yellow", "#ffff00"),
new Color("magenta", "#ee00ee"),
new Color("purple", "#aa00aa"),
new Color("pink", "#ff77ff"),
new Color("gray", "#999999"),
new Color("orange", "#ff8800"),
new Color("indigo", "#0000aa"),
new Color("teal", "#00ffcc"),
new Color("brown", "#663300")]

/**
 * Sets the box to a random color that is not green.
 * currentColor is updated.
 */
function setRandomColor() {
    let box = document.getElementById("colored-box");
    let color = colors[Math.floor(Math.random() * colors.length)];
    box.style.backgroundColor = color.hex;
    currentColor = color.name;
    let timeLeft = testState.delay * 1000 - (new Date().getTime() - testState.startTime);
    if (timeLeft > 1000 && Math.random() < 0.75) {
        timeouts.push(setTimeout(setRandomColor, Math.random() * (timeLeft - 100)));
    }
}

/**
 * Sets the box to green.
 * currentColor is updated.
 */
function setGreen() {
    document.getElementById("colored-box").style.backgroundColor = "#00dd00";
    currentColor = "green";
    textInBox.textContent = "Click!";
}

/**
 * Move the box down then set the color to green.
 */
function moveBoxDown() {
    let box = document.getElementById("colored-box");
    for (let i = 0; i < 100; i++) {
        timeouts.push(setTimeout(function () { box.style.marginTop = 4 * i + "px"; }, i * 2));
    }
}

function duck() {
    textInBox.textContent = "DUCK!";
    let duck = document.getElementById("duck");
    for (let i = 0; i < 100; i++) {
        timeouts.push(setTimeout(function () { duck.style.marginLeft = (100 - 2 * i) + "%"; }, 400 + i * 10));
    }
    timeouts.push(setTimeout(function () { new Audio("../assets/audio/quack.mp3").play(); }, 400));
    setTimeout(function () { duck.style.marginLeft = "100%"; }, 1500);
}

function moveBoxFakeout() {
    let box = document.getElementById("colored-box");
    for (let i = 0; i < 50; i++) {
        timeouts.push(setTimeout(function () { box.style.marginTop = 4 * i + "px"; }, i * 2));
    }
    for (let i = 50; i < 100; i++) {
        timeouts.push(setTimeout(function () { box.style.marginTop = 400 - 4 * i + "px"; }, i * 2 + 100));
    }
}

function prepareBoxChange() {
    testState.startTime = new Date().getTime();
    timeouts.push(setTimeout(setGreen, 1000 * testState.delay));
    if (testState.special == "randomColors") {
        timeouts.push(setTimeout(setRandomColor, 2000 + Math.random() * 1000 * (testState.delay - 2.1)));
    } else if (testState.special == "moveBox") {
        timeouts.push(setTimeout(moveBoxDown, 1000 * testState.delay - (200 + Math.random() * 100)));
    } else if (testState.special == "duck") {
        timeouts.push(setTimeout(duck, 1000 * testState.delay - (1400 + Math.random() * 400)));
    } else if (testState.special == "moveBoxFakeout") {
        timeouts.push(setTimeout(moveBoxFakeout, 1000 * testState.delay - (200 + Math.random() * 100)));
    }
}

/**
 * Submit the data to the API endpoint
 * returns true if the box was clicked at the right time, false if the box was clicked too early.
 */
async function submitReactionData() {
    let response;
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || null;
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        if (csrf) headers['X-CSRF-Token'] = csrf;

        response = await apiFetch('test/reaction_test.php/stop', {
            method: 'POST',
            credentials: 'same-origin',
            headers,
            body: JSON.stringify({
                id: testState.testId,
                refresh_rate: 0.016, //temporary
                start_time: testState.startTime,
                click_time: new Date().getTime(),
                delay: testState.delay,
                theme: (function () { const m = document.cookie.match(/(?:^|; )theme=([^;]+)/); return m ? m[1] : 'light'; })()
            })
        });
    } catch (e) {
        console.error('Network error when submitting reaction data:', e);
        lastResultText.textContent = 'Network error. Try again.';
        return false;
    }

    if (!response.ok) {
        const text = await response.text();
        console.error('Stop API error response:', text);
        lastResultText.textContent = 'Server error. Try again.';
        return false;
    }

    let result;
    try {
        result = await response.json();
    } catch (e) {
        const text = await response.text();
        console.error('Stop API returned non-JSON response:', text);
        lastResultText.textContent = 'Server error. See console for details';
        return false;
    }

    round = result.data.round;
    document.getElementById("progress-bar-filling").style.width = (10 * round) + "%";
    if (result.success) {
        if (round == 0) {
            lastResultText.textContent = "average time: " + Math.round(result.data.reaction_time) + "ms";
        } else {
            lastResultText.textContent = Math.round(result.data.reaction_time) + "ms";
        }
    }
    return result.success;
}
