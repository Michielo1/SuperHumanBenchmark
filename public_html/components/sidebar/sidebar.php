<?php
// Determine base path relative to current file location
if (!isset($basePath)) {
    $currentDir = basename(dirname($_SERVER['PHP_SELF']));
    $basePath = (($currentDir === 'pages') || ($currentDir === 'tests')) ? '../' : '';
}

// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<button class="hamburger_menu" id="hamburger" aria-label="menu" aria-expanded="false">
    <span class="stripes"></span>
    <span class="stripes"></span>
    <span class="stripes"></span>
</button>

<div class="sidebar">
    <a href="<?php echo $basePath; ?>index.php">
        <div id="logo_home">
            <!-- Light & Dark logos -->
            <img
                class="logo-light"
                src="<?php echo $basePath; ?>assets/img/logo_with_green_edge.svg"
                alt="Logo light"
            >
            <img
                class="logo-dark"
                src="<?php echo $basePath; ?>assets/img/whitelogo.svg"
                alt="Logo dark"
            >
        </div>
    </a>

    <nav class="content_sidebar" aria-label="Sidebar navigation">
        <div class="sidebar_nav" aria-hidden="true"></div>

        <a href="<?php echo $basePath; ?>pages/account_page.php"
           class="link_content <?php echo ($current_page === 'account_page.php') ? 'active' : ''; ?>">
            <img src="<?php echo $basePath; ?>assets/img/profile_icon.svg" alt="">
            <span>Profile</span>
        </a>

        <a href="<?php echo $basePath; ?>pages/user_dashboard.php"
           class="link_content <?php echo ($current_page === 'user_dashboard.php') ? 'active' : ''; ?>">
            <img src="<?php echo $basePath; ?>assets/img/dashboard.svg" alt="">
            <span>Dashboard</span>
        </a>

        <?php if (function_exists('isAdmin') && isAdmin()): ?>
            <a href="<?php echo $basePath; ?>pages/admin_dashboard.php"
               class="link_content <?php echo ($current_page === 'admin_dashboard.php') ? 'active' : ''; ?>">
                <img src="<?php echo $basePath; ?>assets/img/dashboard.svg" alt="">
                <span>Admin</span>
            </a>
        <?php endif; ?>

        <a href="<?php echo $basePath; ?>pages/privacy.php"
           class="link_content <?php echo ($current_page === 'privacy.php') ? 'active' : ''; ?>">
            <img src="<?php echo $basePath; ?>assets/img/privacy_icon.svg" alt="">
            <span>Privacy</span>
        </a>

        <button class="link_content theme-toggle" aria-label="Toggle dark mode">
            <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>

            <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 style="display:none;">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>

            <span>Theme</span>
        </button>

        <a href="#" id="logout_link" class="link_content">
            <img src="<?php echo $basePath; ?>assets/img/logout_icon.svg" alt="">
            <span>Log Out</span>
        </a>
    </nav>
</div>


