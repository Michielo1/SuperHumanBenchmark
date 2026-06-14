<!-- Nav bar styles are loaded in main page -->
<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';
// Determine base path relative to current file location
if (!isset($basePath)) {
    // If we're in a pages or tests directory, go up one level
    $currentDir = basename(dirname($_SERVER['PHP_SELF']));
    $basePath = (($currentDir === 'pages') || ($currentDir === 'tests')) ? '../' : '';
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="topnav">
    <div class="topnav_logo">
        <a href="<?php echo $basePath; ?>index.php">
            <img class="logo-light"
                src="<?php echo isset($assetPath) ? $assetPath : $basePath; ?>assets/img/logo_with_green_edge.svg"
                alt="Logo">
            <img class="logo-dark"
                src="<?php echo isset($assetPath) ? $assetPath : $basePath; ?>assets/img/whitelogo.svg"
                alt="Logo">
        </a>
    </div>
    <div class="topnav_text">
        <a href="<?php echo $basePath; ?>index.php#home">Home</a>
        <a href="<?php echo $basePath; ?>pages/scores.php">Score</a>
        <a href="<?php echo $basePath; ?>pages/user_dashboard.php">Dashboard</a>
        <a href="<?php echo $basePath; ?>pages/about_us.php">About</a>
    </div>

    <div class="topnav_account">
        <button class="theme-toggle" aria-label="Toggle theme">
            <!-- Sun icon (shown in light mode) -->
            <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <!-- Moon icon (shown in dark mode) -->
            <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
        <a href="<?php echo $basePath; ?>pages/account_page.php" class="account-icon" aria-label="Account">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="5"/>
                <path d="M20 21a8 8 0 0 0-16 0"/>
            </svg>
        </a>
    </div>
    <div class="devider"></div>
</div>

<button class="hamburger_menu" id="hamburger" aria-label="menu" aria-expanded="false">
    <span class="stripes"></span>
    <span class="stripes"></span>
    <span class="stripes"></span>
</button>

<div class="sidebar">
    <nav class="content_sidebar" aria-label="Sidebar_navigation">
        <div class="sidebar_nav" aria-hidden="true"></div>
        <div class="sidebar_logo">
            <div class="logo_home">
                <a href="<?php echo $basePath; ?>index.php">
                    <img class="logo-light"
                        src="<?php echo isset($assetPath) ? $assetPath : $basePath; ?>assets/img/logo_with_green_edge.svg"
                        alt="Logo">
                    <img class="logo-dark"
                        src="<?php echo isset($assetPath) ? $assetPath : $basePath; ?>assets/img/whitelogo.svg"
                        alt="Logo">
                </a>
            </div>
        </div>
        
        <a class="link_content <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>" 
            href="<?php echo $basePath; ?>index.php#home">
            <img src="<?php echo $basePath; ?>assets/img/home_icon.svg" alt="home">
            <span>Home</span>
        </a>
        <a class="link_content <?php echo ($current_page === 'scores.php') ? 'active' : ''; ?>"
            href="<?php echo $basePath; ?>pages/scores.php">
            <img src="<?php echo $basePath; ?>assets/img/timer_icon_black.svg" alt="score">
            <span>Score</span>
        </a>
        <a class="link_content <?php echo ($current_page === 'user_dashboard.php') ? 'active' : ''; ?>" 
            href="<?php echo $basePath; ?>pages/user_dashboard.php">
            <img src="<?php echo $basePath; ?>assets/img/dashboard.svg" alt="">
            <span>Dashboard</span>
        </a>
        <a class="link_content <?php echo ($current_page === 'about_us.php') ? 'active' : ''; ?>" 
            href="<?php echo $basePath; ?>pages/about_us.php">
            <img src="<?php echo $basePath; ?>assets/img/info_icon_black.svg" alt="">
            <span>About</span>
        </a>
        
        <div class="theme_account">
            <button class="theme-toggle link_content" aria-label="Toggle theme">
                <!-- Sun icon (shown in light mode) -->
                <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <span>Theme</span>
                <!-- Moon icon (shown in dark mode) -->
                <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
            <a href="<?php echo $basePath; ?>pages/account_page.php" class="account-icon link_content" aria-label="Account">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="5"/>
                    <path d="M20 21a8 8 0 0 0-16 0"/>
                </svg>
                <span>Account</span>
            </a>
        </div>
    </div>
    </nav>
    <script src="../assets/js/pages/sidebar.js" defer></script>
</div>
