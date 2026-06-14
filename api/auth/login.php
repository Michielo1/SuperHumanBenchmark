<?php
/**
 * Login API Endpoint
 * POST /api/auth/login.php
 *
 * Required: email and password
 */

// Disable HTML error output for API endpoints
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set up error handler to catch fatal errors and return JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal server error',
            'details' => defined('ENVIRONMENT') && ENVIRONMENT === 'development' ? $error['message'] : null
        ]);
    }
});

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// Load dependencies
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Account.php';
require_once INCLUDES_PATH . '/csrf.php';
// Require CSRF token for login (guarded duplicate removal)
requireCsrf();

try {
    //Get JSON input.
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    $required = ['email', 'password'];
    $missing = [];

    foreach ($required as $field) {
        if (empty($input[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Missing required fields',
            'missing_fields' => $missing
        ]);
        exit;
    }

    // Validate email format
    $email = strtolower(trim((string)$input['email']));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

    // Check email
    $account = Account::findByEmail($email);
    if ($account === null) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    // Check password.
    $hash = $account->getWachtwoord();
    if (!password_verify((string)$input['password'], $hash)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    // Success with login
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['user_id'] = $account->getId();

    // Optional redirect passed from client. Sanitize to prevent open-redirects.
    $redirect = $input['redirect'] ?? null;
    if (!empty($redirect)) {
        $parsed = parse_url($redirect);
        if ($parsed === false || isset($parsed['scheme']) || isset($parsed['host'])) {
            $redirect = null;
        } else {
            $path = $parsed['path'] ?? '';
            if ($path === '' || $path[0] !== '/') {
                $redirect = null;
            } else {
                $redirect = $path;
                if (isset($parsed['query'])) $redirect .= '?' . $parsed['query'];
                if (isset($parsed['fragment'])) $redirect .= '#' . $parsed['fragment'];
            }
        }
    }

    http_response_code(200);
    $response = ['success' => true, 'message' => 'Login successful'];
    if ($redirect !== null) {
        $response['redirect'] = $redirect;
    }
    echo json_encode($response);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred during login']);
    exit;
}
