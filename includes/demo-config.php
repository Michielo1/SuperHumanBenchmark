<?php
/**
 * Demo Configuration
 * Controls demo mode behavior. Include via bootstrap.php.
 *
 * To disable demo mode, set DEMO_MODE to false.
 * To change the auto-reset interval, modify DEMO_RESET_MINUTES.
 */

// Enable demo mode (set to false to disable)
if (!defined('DEMO_MODE')) {
    define('DEMO_MODE', true);
}

// Auto-reset interval in minutes (clears user data, leaderboards, scores)
if (!defined('DEMO_RESET_MINUTES')) {
    define('DEMO_RESET_MINUTES', 30);
}

// Demo user credentials (regular user)
if (!defined('DEMO_USER_EMAIL')) {
    define('DEMO_USER_EMAIL', 'user@demo.com');
}
if (!defined('DEMO_USER_PASSWORD')) {
    define('DEMO_USER_PASSWORD', 'user');
}

// Demo admin credentials
if (!defined('DEMO_ADMIN_EMAIL')) {
    define('DEMO_ADMIN_EMAIL', 'admin@demo.com');
}
if (!defined('DEMO_ADMIN_PASSWORD')) {
    define('DEMO_ADMIN_PASSWORD', 'admin');
}

// Demo reset file path (tracks last reset time)
if (!defined('DEMO_RESET_FILE')) {
    define('DEMO_RESET_FILE', BASE_PATH . '/demo_reset_timestamp.dat');
}
