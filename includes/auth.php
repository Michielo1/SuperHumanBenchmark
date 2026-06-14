<?php
/**
 * Authentication Helper
 * Provides session management and authentication checks
 */

require_once __DIR__ . '/bootstrap.php';

/**
 * Start session if not already started
 */
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 *
 * @return bool
 */
function isLoggedIn(): bool {
    initSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user ID
 *
 * @return int|null
 */
function getCurrentUserId(): ?int {
    initSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Check if current user is an admin
 *
 * @return bool
 */
function isAdmin(): bool {
    initSession();
    $userId = getCurrentUserId();
    if ($userId === null) return false;

    // Load Account class and check flag
    require_once CLASSES_PATH . '/Account.php';
    $account = Account::findById((int)$userId);
    return $account !== null && $account->getIsAdmin();
}

/**
 * Require admin access - redirect to $redirectUrl if not an admin
 *
 * @param string $redirectUrl Where to redirect non-admin users (default: user_dashboard.php)
 */
function requireAdmin(string $redirectUrl = 'user_dashboard.php'): void {
    if (!isAdmin()) {
        initSession();
        $current = $_SERVER['REQUEST_URI'] ?? '';

        // Avoid adding redirect when already on the target page
        if ($current && strpos($current, basename($redirectUrl)) === false) {
            $sep = strpos($redirectUrl, '?') === false ? '?' : '&';
            $redirectUrl .= $sep . 'redirect=' . urlencode($current);
        }

        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Require authentication - redirect to login if not authenticated
 *
 * @param string $redirectUrl URL to redirect to if not logged in (default: login_page.php)
 */
function requireAuth(string $redirectUrl = 'login_page.php'): void {
    if (!isLoggedIn()) {
        initSession();
        $current = $_SERVER['REQUEST_URI'] ?? '';
        $loginUrl = $redirectUrl;

        // Avoid adding redirect when already on the login page (prevents loops)
        if ($current && strpos($current, basename($redirectUrl)) === false) {
            $sep = strpos($loginUrl, '?') === false ? '?' : '&';
            $loginUrl .= $sep . 'redirect=' . urlencode($current);
        }

        header('Location: ' . $loginUrl);
        exit;
    }
}

/**
 * Logout current user
 */
function logout(): void {
    initSession();
    $_SESSION = [];

    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}
