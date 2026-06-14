<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';
requireAuth('../pages/login_page.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Typing Test - Super Human Benchmark</title>
    <meta name="description" content="Test your typing speed and accuracy with Super Human Benchmark's typing test." />
    <meta name="author" content="SuperHumanBenchmark" />
    <meta name="theme-color" content="#628141" />

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">


    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/components/footer.css">
    <link rel="stylesheet" href="../assets/css/tests/typingtest.css">
    <script defer src="../assets/js/theme.js"></script>
    <script defer src="../assets/js/tests/typingtest.js"></script>
    <?php include '../components/cookie-consent-include.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/demo-banner.php'; ?>
    <!-- Navbar -->
    <?php $assetPath = '../'; include '../components/nav_bar/nav_bar.php'; ?>
    
    <div class="typing-container" id="typingscreen">
        <header class="typing-header">
            <h1>Tpying Test</h1>
            <p class="typing-subtitle">Type the words below as quickly and accurately as possible</p>
        </header>

        <div class="typing-stats">
            <div class="stat-item">
                <span class="stat-label">WPM</span>
                <span class="stat-value" id="wpm">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Accuracy</span>
                <span class="stat-value" id="accuracy">100%</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Time</span>
                <span class="stat-value" id="timer">60s</span>
            </div>
        </div>

        <div class="typing-box-wrapper">
            <div class="typing-box" id="typingBox">
                <div class="words-display" id="wordsDisplay">
                    <!-- Words will be populated by JavaScript -->
                </div>
            </div>
        </div>

        <div class="input-section">
            <input
                type="text"
                id="typingInput"
                class="typing-input"
                placeholder="Click here or start typing to begin..."
                autocomplete="off"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
            />
        </div>

        <div class="controls">
            <button class="btn-restart" id="restartBtn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 4v6h6M23 20v-6h-6"/>
                    <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/>
                </svg>
                Restart
            </button>
        </div>

        <div class="instructions">
            <h3>How It Works</h3>
            <p>
                Type the words shown in the box above as quickly and accurately as possible.
                The test will run for 60 seconds. Your words per minute (WPM) and accuracy
                will be calculated based on your performance. Press space after each word
                to move to the next one.
            </p>

            <p>
                This is a very simple test. Honestly. Simple enough that even a child can do it.
                And if you are a child, congratulations! This test cares just as little about
                that as it does about excuses.
            </p>

            <p>
                This is not a complex challenge. Stay consistent, focus, and do your best.
                The test isn’t about intelligence, it’s about attention.
                Missing a letter usually means you’re rushing.
                Typing an incorrect word means you stopped paying attention.
            </p>

            <p>
                Good luck.<br>
                You’re probably going to need it :).
            </p>
        </div>
    </div>
    
    <div class="result" id="resultscreen" hidden>
        <div class=result_header>
            <h1>TPYING TEST</h1>
            <p class="result_subtitle">This test is sponsored by NordWPM.</p>
        </div>
        <section class="result_layout">
            <div class="all_stats">
                <div class="stat">
                    <div class="wpm">WPM</div>
                    <div class="WPM_stat">30</div>
                </div>

                <div class="stat">
                    <div class="accuracy">Accuracy</div>
                    <div class="accuracy_stat">60%</div>
                </div>

                <div class="stat">
                    <div class="error">Errors</div>
                    <div class="error_stat">6</div>
                </div>
            </div>

            <canvas id="graph" class="graph_layout"  width="900" height="420"></canvas>

        </section>
    </div>

    <div class="controls">
        <button class="btn-restart" id="restartbtnresult">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 4v6h6M23 20v-6h-6"/>
                <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/>
            </svg>
            Restart
        </button>
    </div>

    <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>
    <?php require_once __DIR__ . '/../../includes/demo-footer.php'; ?>
</body>
</html>
