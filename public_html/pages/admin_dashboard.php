<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';
requireAuth('login_page.php');
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/components/footer.css">
    <?php include '../components/cookie-consent-include.php'; ?>
</head>
    <link rel="stylesheet" href="../assets/css/pages/admin_dashboard.css">
    <link rel="stylesheet" href="../assets/css/components/sidebar.css">
    <script src="../assets/js/theme.js" defer></script>
    <script src="../assets/js/pages/sidebar.js" defer></script>
</head>

<body>
    <?php require_once __DIR__ . '/../../includes/demo-banner.php'; ?>

    <div class="layout">
        <div class="sidebar_account">
            <!-- Sidebar -->
            <?php $assetPath = ''; include '../components/sidebar/sidebar.php'; ?>
        </div>
        <div class="content">
            <header class="dashboard-header">
                <h1>Admin Dashboard</h1>
                <p class="user-info">Administrator Access</p>
            </header>
            <main class="dashboard-content">
                <!-- Benchmark Statistics Section -->
                <section class="benchmark-stats">
                    <h2>Benchmark Statistics</h2>
                    <div id="statsGrid" class="stats-grid">
                        <div class="stat-loading">Loading statistics...</div>
                    </div>
                    <!-- Theme Analytics Panel -->
                    <div id="themeAnalytics" class="theme-analytics" style="margin-top:1.5rem;">
                        <h3>Theme Analytics</h3>
                        <div id="themeAnalyticsGrid" class="stats-grid">
                            <div class="stat-loading">Loading theme analytics...</div>
                        </div>
                    </div>
                </section>

                <!-- User Management Section -->
                <section class="user-management">
                    <div class="section-header">
                        <h2>User Management</h2>
                        <input type="text" id="userSearch" class="search-input" placeholder="Search users...">
                    </div>

                    <div class="users-table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Total Attempts</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <button class="pagination-btn" id="prevPage" disabled>Previous</button>
                        <span class="pagination-info">Page <span id="currentPage">1</span> of <span
                                id="totalPages">10</span></span>
                        <button class="pagination-btn" id="nextPage">Next</button>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- User Details Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>User Details</h2>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detail-row">
                    <span class="detail-label">User ID:</span>
                    <span class="detail-value" id="modalUserId"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Username:</span>
                    <span class="detail-value" id="modalUsername"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">First Name:</span>
                    <span class="detail-value" id="modalFirstName"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Infix:</span>
                    <span class="detail-value" id="modalInfix"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Last Name:</span>
                    <span class="detail-value" id="modalLastName"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value" id="modalEmail"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Join Date:</span>
                    <span class="detail-value" id="modalJoined"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Attempts:</span>
                    <span class="detail-value" id="modalAttempts"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="action-btn delete-account" id="modalDeleteAccount">Delete Account</button>
                <button class="modal-btn-secondary" id="modalClose">Close</button>
            </div>
        </div>
    </div>

    <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>

    <script src="../assets/js/pages/admin_dashboard.js"></script>
    <?php require_once __DIR__ . '/../../includes/demo-footer.php'; ?>
</body>

</html>
