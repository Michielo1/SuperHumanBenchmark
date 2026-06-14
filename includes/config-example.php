<?php
// Copy this file to config.php and fill in your actual database credentials
// NEVER commit config.php to version control!

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// API base URL for client-side scripts. Set via env variable API_BASE_URL or fallback to '/api/'.
// Example: 'https://api.example.com/' for remote deployments.
if (!defined('API_BASE_URL')) {
    define('API_BASE_URL', '/api');
}

// Environment setting (development or production)
define('ENVIRONMENT', 'development');

// Display errors in development
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// ============================================================================
// DEMO MODE CONFIGURATION
// Uncomment and adjust for demo deployments.
// The demo mode adds a top banner, bottom attribution bar, disables registration,
// and provides demo accounts (user@demo.com / user, admin@demo.com / admin).
// The database auto-resets every 30 minutes (configurable).
// ============================================================================
// require_once __DIR__ . '/demo-config.php';
// define('DEMO_MODE', true);
// define('DEMO_RESET_MINUTES', 30);
