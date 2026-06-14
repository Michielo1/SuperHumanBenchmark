<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';
requireAuth('login_page.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Dashboard</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/components/footer.css">
    <?php include '../components/cookie-consent-include.php'; ?>
</head>
    <link rel="stylesheet" href="../assets/css/pages/user_dashboard.css">
    <link rel="stylesheet" href="../assets/css/components/sidebar.css">
    <script src="../assets/js/theme.js" defer></script>
    <script src="../assets/js/pages/sidebar.js" defer></script>
</head>

<body>

    <div class="layout">
        <div class="sidebar_account">
            <!-- Sidebar -->
            <?php $assetPath = ''; include '../components/sidebar/sidebar.php'; ?>
        </div>
        <div class="content">
            <header class="dashboard-header">
                <h1>Account Dashboard</h1>
                <?php
                    require_once CLASSES_PATH . '/Account.php';
                    $account = null;
                    $userId = getCurrentUserId();
                    if ($userId !== null) {
                        $account = Account::findById((int)$userId);
                    }
                    $firstName = $account ? htmlspecialchars($account->getFirstName(), ENT_QUOTES, 'UTF-8') : 'User';
                ?>
                <p class="user-info">Welcome back, <?php echo $firstName; ?></p>
            </header>
            <main class="dashboard-content">
                <!-- Benchmark Attempts Section -->
                <section class="benchmark-attempts">
                    <h2>Recent Benchmark Attempts</h2>
                    <div class="attempts-table-container">
                        <table class="attempts-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Benchmark</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="attemptsTableBody">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Benchmark Graphs Section -->
                <section class="benchmark-graphs">
                    <h2>Performance Trends</h2>

                    <div class="graph-container">
                        <div class="graph-card">
                            <h3>Benchmark A - Performance Over Time</h3>
                            <canvas id="chartA" width="600" height="300"></canvas>
                        </div>
                    </div>

                    <div class="graph-container">
                        <div class="graph-card">
                            <h3>Benchmark B - Performance Over Time</h3>
                            <canvas id="chartB" width="600" height="300"></canvas>
                        </div>
                    </div>

                    <div class="graph-container">
                        <div class="graph-card">
                            <h3>Benchmark C - Performance Over Time</h3>
                            <canvas id="chartC" width="600" height="300"></canvas>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>
    <script src="../assets/js/pages/user_dashboard.js"></script>
</body>

</html>
