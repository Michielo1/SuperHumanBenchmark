<?php require_once __DIR__ . '/../../includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboards - Benchmark Scores</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/components/nav_bar.css">
    <link rel="stylesheet" href="../assets/css/components/footer.css">
    <link rel="stylesheet" href="../assets/css/pages/scores.css">
    <?php include '../components/cookie-consent-include.php'; ?>
</head>

<body>
    <!-- Navbar -->
    <?php $assetPath = '../'; include '../components/nav_bar/nav_bar.php'; ?>

    <div class="scores-container">
        <header class="scores-header">
            <h1>Benchmark Leaderboards</h1>
            <p class="subtitle">See how you stack up against other players</p>
        </header>

        <main class="scores-content">
            <!-- Benchmark Tabs -->
            <section class="benchmark-tabs" id="benchmarkTabs">
                <!-- Tabs will be dynamically loaded -->
                <div class="loading-tabs">Loading tests...</div>
            </section>

            <!-- Leaderboard Section -->
            <section class="leaderboard-section">
                <div class="leaderboard-header">
                    <h2 id="currentBenchmarkTitle">Reaction Test Leaderboard</h2>
                    <p class="benchmark-description" id="benchmarkDescription">Test your reflexes with a reaction test!</p>
                </div>

                <!-- Loading State -->
                <div class="loading-state" id="loadingState">
                    <div class="spinner"></div>
                    <p>Loading leaderboard...</p>
                </div>

                <!-- Error State -->
                <div class="error-state" id="errorState" style="display: none;">
                    <p>Failed to load leaderboard data. Please try again later.</p>
                </div>

                <!-- Leaderboard Table -->
                <div class="leaderboard-table-container" id="leaderboardContainer" style="display: none;">
                    <table class="leaderboard-table">
                        <thead>
                            <tr>
                                <th class="rank-column">Rank</th>
                                <th class="player-column">Player</th>
                                <th class="score-column">Best Score</th>
                                <th class="attempts-column">Attempts</th>
                                <th class="date-column">Last Updated</th>
                            </tr>
                        </thead>
                        <tbody id="leaderboardTableBody">
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div class="empty-state" id="emptyState" style="display: none;">
                    <p>No scores yet for this benchmark. Be the first to set a score!</p>
                </div>
            </section>
        </main>
    </div>

    <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/pages/scores.js"></script>
</body>

</html>
