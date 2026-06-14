<?php
// includes/csrf.php
// Minimal CSRF protection helper

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? generate_csrf_token();
}

function validate_csrf_token($token) {
    // Ensure token and session token are strings before using hash_equals
    if (!isset($_SESSION['csrf_token'])) return false;
    if (!is_string($_SESSION['csrf_token'])) return false;
    if (!is_string($token)) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require a valid CSRF token for state-changing requests.
 * Accepts token via `X-CSRF-Token` header, `csrf_token` POST field
 * or JSON body field `csrf_token`.
 */
function requireCsrf() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = null;

    // Prefer header
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    }

    // Fallback to form POST
    if ($token === null && isset($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    }

    // NOTE: Do NOT read php://input here. Reading the raw input will consume the
    // stream and prevent the endpoint from reading JSON request bodies later.
    // For JSON requests, clients MUST send the token in the `X-CSRF-Token` header.

    if (!validate_csrf_token($token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
}
