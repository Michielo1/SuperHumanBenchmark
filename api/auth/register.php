<?php
/**
 * Registration API Endpoint
 * POST /api/auth/register.php
 *
 * Required: username, first_name, last_name, email, password
 * Optional: infix
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

// Remove characters
function remove_chars(string $s):string {
    return preg_replace('/[\x00-\x1F\x7F]/u', '', $s) ?? '';
}

// Makes user input normalized
function norm(string $s, int $max_length):string {
    $s = trim($s);
    $s = remove_chars($s);
    $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
    if (mb_strlen($s, 'UTF-8') > $max_length) {
        $s = mb_substr($s, 0, $max_length, 'UTF-8');
    }

    return $s;
}

// Validate username
function username_valid(string $s):bool {
    return (bool)preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $s);
}

// Validate names
function name_valid(string $s):bool {
    return (bool)preg_match("/^[\p{L}][\p{L}\p{M}\s\-\.]{0,49}$/u", $s);
}

// Validate infix
function infix_valid(string $s):bool {
    if ($s === '') { 
        return true;
    }
    return (bool)preg_match("/^[\p{L}\p{M}\s\-\.]{1,20}$/u", $s);
}

// Validat email
function email_valid(string $email):bool {
    return (bool)preg_match(
        '/^[a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
        $email
    );
}

// Block registration in demo mode
if (defined('DEMO_MODE') && DEMO_MODE) {
    http_response_code(403);
    echo json_encode(['error' => 'Registration is disabled in demo mode. Use the demo accounts on the login page.']);
    exit;
}

try {
    // Get JSON input
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);

    if ($input === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    // Validate required fields
    $required = ['username', 'first_name', 'last_name', 'email', 'password'];
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

    $username = norm((string)$input['username'], 50);
    $first_name = norm((string)$input['first_name'], 50);
    $infix = isset($input['infix']) ? norm((string)$input['infix'], 20) : '';
    $last_name = norm((string)$input['last_name'], 50);
    $email_raw = norm((string)$input['email'], 100);
    $email = strtolower($email_raw);
    $password  = (string)$input['password'];

    // Checks username
    if (!username_valid($username)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid username']);
        exit;
    }

    // Checks firstname
    if (!name_valid($first_name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid firstname']);
        exit;
    }

    // Checks infix
    if (!infix_valid($infix)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid infix']);
        exit;
    }

    // Checks lastname
    if (!name_valid($last_name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid lastname']);
        exit;
    }

    // Validate email format
    if (!email_valid($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

    // Validate password length (min 8 characters and max 60)
    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 8 characters long']);
        exit;
    }

    if (strlen($password) > 60) {
        http_response_code(400);
        echo json_encode(['error' => 'Password cannot be longer then 60 characters']);
        exit;
    }

    // Check if username already exists
    $existingUsername = Account::findByUsername($username);
    if ($existingUsername !== null) {
        http_response_code(409);
        echo json_encode(['error' => 'Username already registered']);
        exit;
    }

    // Check if email already exists
    $existingAccount = Account::findByEmail($email);

    if ($existingAccount !== null) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already registered']);
        exit;
    }

    // Create new account
    $account = new Account();
    $account->setUsername($username);
    $account->setFirstName($first_name);
    $account->setLastName($last_name);
    $account->setEmail($email);

    // Set optional infix
    if ($infix !== '') {
        $account->setInfix($infix);
    }

    // Hash and set password
    $account->hashAndSetPassword($password);

    // Save to database
    if ($account->save()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully',
            'account' => $account->toArray()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create account']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } else {
        error_log('Registration error: ' . $e->getMessage());
        echo json_encode(['error' => 'An error occurred during registration']);
    }
} catch (Exception $e) {
    http_response_code(500);
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        error_log('Registration error: ' . $e->getMessage());
        echo json_encode(['error' => 'An error occurred during registration']);
    }
}