/**
 * Typing Test JavaScript
 * Handles the typing test game logic and word display
 */

// Test configuration
const TEST_DURATION = 60; // seconds
let testId = null;
let activeRequest = false;

// Test state
let testState = {
    words: [],
    currentWordIndex: 0,
    currentLetterIndex: 0,
    isTestActive: false,
    isTestComplete: false,
    startTime: null,
    endTime: null,
    timerInterval: null,
    correctWords: 0,
    incorrectWords: 0,
    totalKeystrokes: 0,
    correctKeystrokes: 0,
    currentWordCorrect: true,
    wpmHistory: [],
    previousSeconds: -1,
    wordResults: []
};

// DOM elements
let wordsDisplay;
let typingInput;
let restartBtn;
let wpmDisplay;
let accuracyDisplay;
let timerDisplay;
let resultsSection;

/**
 * Initialize the test when the page loads
 */
document.addEventListener('DOMContentLoaded', function () {
    // Get DOM elements
    wordsDisplay = document.getElementById('wordsDisplay');
    typingInput = document.getElementById('typingInput');
    restartBtn = document.getElementById('restartBtn');
    restartbtnresult = document.getElementById('restartbtnresult');
    wpmDisplay = document.getElementById('wpm');
    accuracyDisplay = document.getElementById('accuracy');
    timerDisplay = document.getElementById('timer');
    resultsSection = document.getElementById('resultscreen');

    // Set up event listeners
    typingInput.addEventListener('input', handleTyping);
    typingInput.addEventListener('focus', handleInputFocus);
    restartBtn.addEventListener('click', restartTest);
    restartbtnresult.addEventListener('click', restartTest);

    // Initialize the test
    initializeTest();
});

/*
 * Starts typing test session.
 */
async function startTypingSession() {
    let response, data;
    const endpoint = 'test/typing_test.php/start?count=250&category=brainrot&type=lowercase';
    try {
        response = await apiFetch(endpoint, {
            method: 'GET',
            credentials: 'include',
        });
        const text = await response.text();
        try {
            data = JSON.parse(text);
        } catch (jsonErr) {
            // If response is not JSON, show error and log raw response
            console.error('[TYPING] API did not return valid JSON:', text);
            showTypingApiError('Failed to load words: API returned invalid data.');
            throw new Error('API returned invalid JSON');
        }
        if (!data || !data.data || !Array.isArray(data.data.words)) {
            showTypingApiError('Failed to load words: API response missing words.');
            throw new Error('API response missing words');
        }

        // Log cache usage info if present
        if (data.data.cache) {
            try {
                const c = data.data.cache;
                console.log(`[TYPING] Words served_from_cache=${c.used ? 'yes' : 'no'} backend=${c.backend} cached_at=${c.cached_at} stored=${c.stored ? 'yes' : 'no'}`);
            } catch (e) {
                // ignore any logging errors because the user shouldnt care about this
            }
        }

        testId = data.data.id;
        return data.data;
    } catch (err) {
        if (!response) {
            showTypingApiError('Failed to connect to API.');
        }
        throw err;
    }
}

function showTypingApiError(msg) {
    if (typeof wordsDisplay !== 'undefined') {
        wordsDisplay.innerHTML = `<span class="word error">${msg}</span>`;
    }
    if (typeof typingInput !== 'undefined') {
        typingInput.disabled = true;
    }
}

/**
 * Initialize the test
 */
async function initializeTest() {
    // Show loading state
    wordsDisplay.innerHTML = '<span class="word">Loading words...</span>';
    typingInput.disabled = true;

    // Fetch words from API (backend will return max 250 by default)
    const session = await startTypingSession();
    const words = session.words;

    // Reset state
    testState = {
        words: words,
        currentWordIndex: 0,
        currentLetterIndex: 0,
        isTestActive: false,
        isTestComplete: false,
        startTime: null,
        endTime: null,
        timerInterval: null,
        correctWords: 0,
        incorrectWords: 0,
        totalKeystrokes: 0,
        correctKeystrokes: 0,
        currentWordCorrect: true,
        wpmHistory: [],
        previousSeconds: -1,
        wordResults: []
    };


    // Clear input
    typingInput.value = '';
    typingInput.disabled = false;

    // Reset displays
    wpmDisplay.textContent = '0';
    accuracyDisplay.textContent = '100%';
    timerDisplay.textContent = TEST_DURATION + 's';

    // Hide results
    // resultsSection.style.display = 'none';

    // Render words
    renderWords();
}

/**
 * Render the words display
 */
function renderWords() {
    wordsDisplay.innerHTML = '';

    testState.words.forEach((word, wordIndex) => {
        const wordSpan = document.createElement('span');
        wordSpan.className = 'word';

        // Current word.
        if (wordIndex === testState.currentWordIndex) {
            wordSpan.classList.add('current');

            // Make a span for each letter.
            for (let i = 0; i < word.length; i++) {
                const letterSpan = document.createElement('span');
                letterSpan.className = 'letter';
                letterSpan.textContent = word[i];
                wordSpan.appendChild(letterSpan);
            }

            wordsDisplay.appendChild(wordSpan);
            return;
        }

        // Done typing words.
        if (wordIndex < testState.currentWordIndex) {
            wordSpan.classList.add('done');

            const response = testState.wordResults[wordIndex];
            const typed = response?.typed ?? '';
            const target = word;

            // Check each letter of target word.
            for (let i = 0; i < target.length; i++) {
                const letterSpan = document.createElement('span');
                letterSpan.className = 'letter';
                letterSpan.textContent = target[i];

                if (typed[i] === target[i]) {
                    letterSpan.classList.add('correct');
                } else {
                    letterSpan.classList.add('incorrect');
                }

                wordSpan.appendChild(letterSpan);
            }

            wordsDisplay.appendChild(wordSpan);
            return;
        }

        // Words after user word.
        wordSpan.classList.add('pending');

        for (let i = 0; i < word.length; i++) {
            const letterSpan = document.createElement('span');
            letterSpan.className = 'letter';
            letterSpan.textContent = word[i];
            wordSpan.appendChild(letterSpan);
        }

        wordsDisplay.appendChild(wordSpan);
    });
}

/**
 * Handle input focus (start test on first focus)
 */
function handleInputFocus() {
    if (!testState.isTestActive && !testState.isTestComplete) {
        startTest();
    }
}

/**
 * Start the test
 */
function startTest() {
    testState.isTestActive = true;
    testState.startTime = Date.now();

    // Start timer
    testState.timerInterval = setInterval(updateTimer, 100);
}

/**
 * Update the timer display
 */
function updateTimer() {
    const elapsed = (Date.now() - testState.startTime) / 1000;
    const remaining = Math.max(0, TEST_DURATION - elapsed);

    timerDisplay.textContent = Math.ceil(remaining) + 's';

    // Update WPM
    updateWPM();

    // Check if time is up
    if (remaining <= 0) {
        endTest();
    }
}

/**
 * Update WPM display
 */
function updateWPM() {
    if (!testState.startTime) {
        return;
    }

    const elapsedMinutes = Math.max(0.001, (Date.now() - testState.startTime) / 1000 / 60); // minutes
    const correctWords = testState.correctWords;
    const wpm = Math.round(correctWords / elapsedMinutes) || 0;

    wpmDisplay.textContent = wpm;

    // Update accuracy
    const accuracy = testState.totalKeystrokes > 0
        ? Math.round((testState.correctKeystrokes / testState.totalKeystrokes) * 100)
        : 100;
    accuracyDisplay.textContent = accuracy + '%';

    const timepast = Math.floor((Date.now() - testState.startTime) / 1000);
    if (timepast !== testState.previousSeconds && timepast <= TEST_DURATION) {
        testState.previousSeconds = timepast;
        testState.wpmHistory.push({
            t: timepast,
            wpm: wpm
        });
    }
}

/**
 * Handle typing input
 */
function handleTyping(e) {
    if (testState.isTestComplete) return;

    const input = typingInput.value;
    handleMutation(input.length);
    const currentWord = testState.words[testState.currentWordIndex];

    // Check if user pressed space (word complete)
    if (input.endsWith(' ')) {
        // Remove the old cursors.
        const letter_cursor = wordsDisplay.querySelectorAll('.letter.current');
        letter_cursor.forEach(function (letter) {
            letter.classList.remove('current');
        });

        handleWordComplete(input.trim(), input.length);
        typingInput.value = '';

        // Set cursor to the next first letter of word.
        const allwords = wordsDisplay.querySelectorAll('.word')
        const nextword = allwords[testState.currentWordIndex];

        if (nextword) {
            const letters = nextword.querySelectorAll('.letter');
            const firstletter = letters[0];

            if (firstletter) {
                firstletter.classList.add('current');
            }
        }
        return;
    }

    // Update letter highlighting
    updateLetterHighlight(input, currentWord);
}

/*
 * Handles the mutations when typing in test.
 */
async function handleMutation(typedLen) {
    if (!testId) {
        return;
    }

    if (!testState.isTestActive) {
        return;
    }

    if (typedLen < 3) {
        return;
    }

    if (activeRequest) {
        return;
    }

    activeRequest = true;
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || null;
        const headers = { 'Content-Type': 'application/json' };
        if (csrf) headers['X-CSRF-Token'] = csrf;

        const response = await apiFetch('test/typing_test.php/active', {
            method: 'POST',
            credentials: 'include',
            headers,
            body: JSON.stringify({
                id: testId,
                word_index: testState.currentWordIndex,
                typed_length: typedLen
            })
        });

        let data = {};
        try {
            data = await response.json();
        } catch (e) {
            const text = await response.text();
            console.error('[TYPING] /active returned non-JSON:', text);
            activeRequest = false;
            return;
        }

        // Show mutation if word returned from backend.
        if (data.mutation) {
            const word_index = data.mutation.word_index;
            const new_word = data.mutation.new_word;
            testState.words[word_index] = new_word;
            renderWords();

            const currentWord = testState.words[testState.currentWordIndex];
            updateLetterHighlight(typingInput.value, currentWord);
        }
    } catch (err) {
        console.error('active error', err);
    } finally {
        activeRequest = false;
    }
}


/**
 * Update letter highlighting based on current input
 */
function updateLetterHighlight(input, currentWord) {
    const wordElements = wordsDisplay.querySelectorAll('.word');
    const currentWordElement = wordElements[testState.currentWordIndex];
    const letters = currentWordElement.querySelectorAll('.letter');

    let allCorrect = true;

    letters.forEach((letter, index) => {
        letter.classList.remove('correct', 'incorrect', 'current');

        if (index < input.length) {
            if (input[index] === currentWord[index]) {
                letter.classList.add('correct');
            } else {
                letter.classList.add('incorrect');
                allCorrect = false;
            }
        } else if (index === input.length) {
            letter.classList.add('current');
        }
    });

    testState.currentWordCorrect = allCorrect && input.length === currentWord.length;
}

/**
 * Handle word completion
 */
function handleWordComplete(typedWord, typedLength) {
    const index = testState.currentWordIndex;
    const currentWord = testState.words[testState.currentWordIndex];

    // Determine keystrokes for this completed word. Use the raw typed length (includes the space that ended the word) when available.
    const actualKeystrokes = (typeof typedLength === 'number' && typedLength >= 0) ? typedLength : typedWord.length;

    // Update statistics
    testState.totalKeystrokes += actualKeystrokes;

    if (typedWord === currentWord) {
        testState.correctWords++;
        // Count correct keystrokes (letters only; spaces are not part of the word)
        testState.correctKeystrokes += typedWord.length;
        testState.wordResults[index] = {
            typed: typedWord,
            target: currentWord,
            keystrokes: actualKeystrokes
        };
        markWordAs('correct');
    } else {
        testState.incorrectWords++;
        // Count correct letters
        for (let i = 0; i < Math.min(typedWord.length, currentWord.length); i++) {
            if (typedWord[i] === currentWord[i]) {
                testState.correctKeystrokes++;
            }
        }
        testState.wordResults[index] = {
            typed: typedWord,
            target: currentWord,
            keystrokes: actualKeystrokes
        };
        markWordAs('incorrect');
    }

    // Move to next word
    testState.currentWordIndex++;

    // Check if we've reached the end
    if (testState.currentWordIndex >= testState.words.length) {
        endTest();
        return;
    }

    renderWords();

    // Update display
    updateWordDisplay();
}

/**
 * Mark the current word as correct or incorrect
 */
function markWordAs(className) {
    const wordElements = wordsDisplay.querySelectorAll('.word');
    const currentWordElement = wordElements[testState.currentWordIndex];

    currentWordElement.classList.remove('current', 'pending');
    currentWordElement.classList.add('done');

    currentWordElement.dataset.result = className;
}

/**
 * Update the word display for the next word
 */
function updateWordDisplay() {
    const wordElements = wordsDisplay.querySelectorAll('.word');

    wordElements.forEach((wordElement, index) => {
        wordElement.classList.remove('current');

        if (index === testState.currentWordIndex) {
            wordElement.classList.add('current');
            wordElement.classList.remove('pending');

            // Scroll the current word into view smoothly
            // This creates a line-by-line scrolling effect
            const currentWordRect = wordElement.getBoundingClientRect();
            const containerRect = wordsDisplay.getBoundingClientRect();

            // If current word is in bottom half of container, scroll it to the middle
            if (currentWordRect.top > containerRect.top + containerRect.height / 2) {
                wordElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
}

/**
 * End the test
 */
function endTest() {
    testState.isTestActive = false;
    testState.isTestComplete = true;
    testState.endTime = Date.now();

    // Stop timer
    if (testState.timerInterval) {
        clearInterval(testState.timerInterval);
    }

    // Disable input
    typingInput.disabled = true;

    // Calculate final statistics
    const elapsed = Math.max(0.001, (testState.endTime - testState.startTime) / 1000 / 60); // minutes
    // Client-side estimate with 2 decimal precision (matches server rounding)
    const finalWPM = Math.round((testState.correctWords / elapsed) * 100) / 100;
    const finalAccuracy = testState.totalKeystrokes > 0
        ? Math.round((testState.correctKeystrokes / testState.totalKeystrokes) * 100)
        : 100;

    // Result screen
    const resultscreen = document.getElementById('resultscreen');

    // If client-side accuracy is below threshold, show invalid result immediately
    const clientInvalidAccuracy = finalAccuracy < 75;
    if (clientInvalidAccuracy) {
        resultscreen.querySelector('.WPM_stat').textContent = '-';
        resultscreen.querySelector('.accuracy_stat').textContent = '-';
        resultscreen.querySelector('.error_stat').textContent = testState.incorrectWords;
        let errorBox = resultscreen.querySelector('.accuracy-error');
        if (!errorBox) {
            errorBox = document.createElement('div');
            errorBox.className = 'accuracy-error';
            errorBox.style.color = '#c0392b';
            errorBox.style.fontWeight = 'bold';
            errorBox.style.marginTop = '20px';
            resultscreen.appendChild(errorBox);
        }
        errorBox.textContent = 'Your accuracy was too low to save a valid result. Please try again and aim for at least 75% accuracy!';
    } else {
        // Show client's final WPM estimate while server computes authoritative WPM
        resultscreen.querySelector('.WPM_stat').textContent = finalWPM.toString();
        resultscreen.querySelector('.accuracy_stat').textContent = finalAccuracy + '%';
        resultscreen.querySelector('.error_stat').textContent = testState.incorrectWords;
    }

    document.getElementById('typingscreen').style.display = 'none';
    resultscreen.hidden = false;

    displayGraph(document.getElementById('graph'), testState.wpmHistory, TEST_DURATION);
    // Scroll to results
    resultsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    // Build detailed results for the server (only completed words)
    const results = [];
    for (let i = 0; i < testState.currentWordIndex; i++) {
        const wr = testState.wordResults[i] || { typed: '' };
        results.push({
            index: i,
            typed: wr.typed || '',
            keystrokes: (typeof wr.keystrokes === 'number') ? wr.keystrokes : ((wr.typed && typeof wr.typed === 'string') ? wr.typed.length : 0)
        });
    }

    const payload = {
        id: testId,
        results: results
    };

    // Send results to backend
    sendTypingTestResults(payload);
}

/**
 * Send typing test results to backend
 */
async function sendTypingTestResults(payload) {
    try {
        // Attach theme from cookie
        const themeMatch = document.cookie.match(/(?:^|; )theme=([^;]+)/);
        payload.theme = themeMatch ? themeMatch[1] : 'light';

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || null;
        const headers = { 'Content-Type': 'application/json' };
        if (csrf) headers['X-CSRF-Token'] = csrf;

        const response = await apiFetch('test/typing_test.php/eind', {
            method: 'POST',
            credentials: 'include',
            headers,
            body: JSON.stringify(payload)
        });

        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            console.error('[TYPING] Error parsing /eind JSON:', jsonError);
            const text = await response.text();
            console.error('[TYPING] /eind response text:', text);
            // If we couldn't parse JSON, show a generic error state so UI doesn't keep client-side defaults.
            const resultscreen = document.getElementById('resultscreen');
            resultscreen.querySelector('.WPM_stat').textContent = '-';
            resultscreen.querySelector('.accuracy_stat').textContent = '-';
            resultscreen.querySelector('.error_stat').textContent = '-';
            let errorBox = resultscreen.querySelector('.accuracy-error');
            if (!errorBox) {
                errorBox = document.createElement('div');
                errorBox.className = 'accuracy-error';
                errorBox.style.color = '#c0392b';
                errorBox.style.fontWeight = 'bold';
                errorBox.style.marginTop = '20px';
                resultscreen.appendChild(errorBox);
            }
            errorBox.textContent = 'Unexpected response from server.';
            return;
        }

        const resultscreen = document.getElementById('resultscreen');

        // Treat HTTP errors or explicit server-side errors as invalid results and show '-' for stats.
        if (!response.ok || (data && data.error)) {
            resultscreen.querySelector('.WPM_stat').textContent = '-';
            resultscreen.querySelector('.accuracy_stat').textContent = '-';
            resultscreen.querySelector('.error_stat').textContent = '-';
            let errorBox = resultscreen.querySelector('.accuracy-error');
            if (!errorBox) {
                errorBox = document.createElement('div');
                errorBox.className = 'accuracy-error';
                errorBox.style.color = '#c0392b';
                errorBox.style.fontWeight = 'bold';
                errorBox.style.marginTop = '20px';
                resultscreen.appendChild(errorBox);
            }
            errorBox.textContent = (data && data.error) ? data.error : `Server returned ${response.status} ${response.statusText}`;
            return;
        }

        // Successful server response - display authoritative WPM and stats
        if (data && typeof data.wpm !== 'undefined') {
            resultscreen.querySelector('.WPM_stat').textContent = data.wpm;
            resultscreen.querySelector('.accuracy_stat').textContent = (typeof data.accuracy !== 'undefined') ? (data.accuracy + '%') : resultscreen.querySelector('.accuracy_stat').textContent;
            if (typeof data.total_words !== 'undefined' && typeof data.correct_words !== 'undefined') {
                resultscreen.querySelector('.error_stat').textContent = (data.total_words - data.correct_words);
            }
        }
    } catch (error) {
        console.error('[TYPING] Error sending results to /eind:', error);
    }
}

/**
 * Restart the test
 */
function restartTest() {
    if (testState.timerInterval) {
        clearInterval(testState.timerInterval);
    }
    document.getElementById('resultscreen').hidden = true;
    document.getElementById('typingscreen').style.display = 'block';

    initializeTest();
    typingInput.focus();
}

/*
 * Remove the data of first seconds of test.
 */
function filter_data(data, ignoreSeconds) {
    const result = [];

    for (let i = 0; i < data.length; i++) {
        const j = data[i];
        if (j.t >= ignoreSeconds) {
            result.push(j);
        }
    }
    return result;
}

/*
 * Makes the shifted seconds the new zeropoint.
 */
function shift_zeropoint(data, secondshifts) {
    const result = [];

    for (let i = 0; i < data.length; i++) {
        const j = data[i];
        result.push({
            t: j.t - secondshifts,
            wpm: j.wpm

        });
    }

    return result;
}

/*
 * Makes the x scale responsive.
 */
function responsiveScaleX(t, duration, real_width, padding_left) {
    return padding_left + (t / duration) * real_width;
}


/*
 * Makes the y scale responsive.
 */
function responsiveScaleY(w, maxWpm, real_height, padding_top) {
    return padding_top + real_height - (w / maxWpm) * real_height;
}

/*
 * change the fonts of the text around the graph.
 */
function graph_font(ctx, element, fontsize) {
    const elm = element || document.body;
    const style = getComputedStyle(elm);
    ctx.font = `${fontsize}px ${style.fontFamily}`;
    ctx.fillStyle = style.color || '#000000';
}

/*
 * Dislays the graph.
 */
function displayGraph(canvas, type_data, duration) {
    if (!canvas) {
        return;
    }
    const ctx = canvas.getContext('2d');
    const layout_font = canvas.closest('.graph_layout') || document.querySelector('.graph_layout') || canvas;

    const width = canvas.width;
    const height = canvas.height;

    ctx.clearRect(0, 0, width, height);

    // Margins
    const padding_top = 20;
    const padding_bottom = 60;
    const padding_right = 20;
    const padding_left = 60;

    // Canvas real size
    const real_width = width - padding_left - padding_right;
    const real_height = height - padding_top - padding_bottom;

    const ignore_seconds = 3;
    const filtered = filter_data(type_data, ignore_seconds);

    if (!filtered || filtered.length < 2) {
        ctx.font = '20px sans-serif';
        ctx.fillText('Insufficient data', padding_left, padding_top + 20);
        return;
    }

    const shifted = shift_zeropoint(filtered, ignore_seconds);
    const shiftedDuration = duration - ignore_seconds;

    // WPM
    let maxWpm = 10;
    for (let i = 0; i < filtered.length; i++) {
        const all_data = filtered[i];
        maxWpm = Math.max(maxWpm, all_data.wpm);
    }

    maxWpm = Math.ceil(maxWpm / 10) * 10;

    // Words per minute text
    ctx.save();

    graph_font(ctx, layout_font, 10);

    ctx.translate(15, padding_top + real_height / 2);
    ctx.rotate(-Math.PI / 2);

    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('Words Per Minute', 0, 0);

    ctx.restore();

    // Time text
    ctx.save();

    graph_font(ctx, layout_font, 10);

    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';

    ctx.fillText(
        'Time in (s)', padding_left + real_width / 2, padding_top + real_height + 35
    );

    ctx.restore();

    ctx.strokeStyle = '#628141';
    ctx.lineWidth = 5;

    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';


    // Makes line of graph curvier.
    ctx.beginPath();
    for (let i = 0; i < shifted.length - 1; i++) {
        const current = shifted[i];
        const next = shifted[i + 1];

        const x1 = responsiveScaleX(current.t, shiftedDuration, real_width, padding_left);
        const y1 = responsiveScaleY(current.wpm, maxWpm, real_height, padding_top);
        const x2 = responsiveScaleX(next.t, shiftedDuration, real_width, padding_left);
        const y2 = responsiveScaleY(next.wpm, maxWpm, real_height, padding_top);

        const cx = (x1 + x2) / 2;
        const cy = (y1 + y2) / 2;

        if (i === 0) {
            ctx.moveTo(x1, y1);
        }

        ctx.quadraticCurveTo(x1, y1, cx, cy);
    }
    ctx.stroke();

    graph_font(ctx, layout_font, 10);

    // Y-axis steps for indication of wpm.
    const yStep = 30;
    for (let i = 0; i <= maxWpm; i += yStep) {
        const y = responsiveScaleY(i, maxWpm, real_height, padding_top);
        ctx.fillText(i.toString(), padding_left - 35, y + 4);
    }

    // X-axis steps for indication of time
    const xStep = 10;
    for (let i = 0; i <= shiftedDuration; i += xStep) {
        const x = responsiveScaleX(i, shiftedDuration, real_width, padding_left);
        ctx.fillText(i.toString(), x - 6, padding_top + real_height + 20);
    }

    ctx.strokeStyle = '#000000';
    ctx.lineWidth = 5;

    // X-axis line
    ctx.beginPath();
    ctx.moveTo(padding_left, padding_top + real_height);
    ctx.lineTo(padding_left + real_width, padding_top + real_height);
    ctx.stroke();

    // Y-axis line
    ctx.beginPath();
    ctx.moveTo(padding_left, padding_top);
    ctx.lineTo(padding_left, padding_top + real_height);
    ctx.stroke();
}
