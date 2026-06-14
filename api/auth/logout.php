<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json');

require_once INCLUDES_PATH . '/csrf.php';
// Require CSRF token for logout action
requireCsrf(); // Require CSRF token for logout action

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();
    session_destroy();

    // Delete the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
}

echo json_encode(['success' => true]);
exit;
