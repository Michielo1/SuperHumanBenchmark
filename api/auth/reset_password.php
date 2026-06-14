<?php
/**
 * Reset Password API Endpoint
 * POST /api/auth/reset_password.php
 *
 * Required: token, password
 * Validates token and updates password
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
// Require CSRF token for reset-password
requireCsrf();

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    // Token should be provided as a query parameter (?token=...)
    $token = $_GET['token'] ?? null;
    if (empty($token)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing reset token']);
        exit;
    }

    // Validate required fields (only password is expected in the JSON body)
    $required = ['password'];
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

    // Validate password length (minimum 8 characters)
    if (strlen($input['password']) < 8) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 8 characters long']);
        exit;
    }

    // Check if token exists in database
    // Use the project's Database::getConnection() method and correct table name
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id, reset_token, reset_token_expires FROM account WHERE reset_token = ?");
    $stmt->execute([$token]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or expired reset token']);
        exit;
    }

    // Check if token is expired
    $expires = new DateTime($account['reset_token_expires']);
    $now = new DateTime();

    if ($now > $expires) {
        http_response_code(400);
        echo json_encode(['error' => 'Reset token has expired']);
        exit;
    }

    // Update password and clear token
    $account = Account::findById($account['id']);
    $account->hashAndSetPassword($input['password']);
    $account->clearResetToken();

    if ($account->save()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update password']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } else {
        error_log('Reset password error: ' . $e->getMessage());
        echo json_encode(['error' => 'An error occurred']);
    }
} catch (Exception $e) {
    http_response_code(500);
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        error_log('Reset password error: ' . $e->getMessage());
        echo json_encode(['error' => 'An error occurred']);
    }
}
