<?php
/**
 * Forgot Password API Endpoint
 * POST /api/auth/forgot_password.php
 *
 * Required: email
 * Generates a reset token and sends an email
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
requireCsrf();

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    // Validate required fields
    $required = ['email'];
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
    $email = strtolower(trim((string)($input['email'] ?? '')));
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

    // Check if email exists
    $account = Account::findByEmail($email);

    if ($account === null) {
        // For security, don't reveal if email exists or not
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'If the email exists, a reset link has been sent'
        ]);
        exit;
    }

    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Store token in the model and persist
    $account->setResetToken($token, $expires);
    if (!$account->save()) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to store reset token']);
        exit;
    }

    // Send email with reset link
    // Build reset link using either configured BASE_URL or by deriving the project base path from the script name
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    $basePath = '';
    if (defined('BASE_URL') && BASE_URL) {
        $basePath = rtrim(BASE_URL, '/');
    } else {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $derived = dirname(dirname(dirname($scriptName)));
        $basePath = $derived === '/' ? '' : rtrim($derived, '/');
    }
    $resetLink = $protocol . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/pages/reset_password.php?token=' . $token;
    $subject = "Password Reset Request";
    $message = "Hello " . $account->getFirstName() . ",\n\n";
    $message .= "You requested a password reset. Click the link below to reset your password:\n\n";
    $message .= $resetLink . "\n\n";
    $message .= "This link will expire in 1 hour.\n\n";
    $message .= "If you didn't request this, please ignore this email.\n\n";
    $message .= "Best regards,\nThe Team";

    // Send email using PHP's built-in mail function
    $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
    $headers .= "Reply-To: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $sent = mail($email, $subject, $message, $headers);

    if ($sent) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'If the email exists, a reset link has been sent'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to send email']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } else {
        error_log('Forgot password error: ' . $e->getMessage());
        echo json_encode(['error' => 'An error occurred']);
    }
} catch (Exception $e) {
    http_response_code(500);
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        error_log('Forgot password error: ' . $e->getMessage());
        echo json_encode(['error' => 'An error occurred']);
    }
}
