<?php
/**
 * Admin Account Deletion API
 * POST /api/data/delete_account.php
 * Body params (application/json or form-encoded):
 *  - email (string) - account email to delete
 *
 * Requirements:
 *  - Caller must be authenticated and an admin
 *  - CSRF token required (use X-CSRF-Token header for JSON requests)
 *  - Cannot delete own account via this endpoint
 *  - Will refuse to delete the last remaining admin
 */

// Disable error output to preserve JSON responses
error_reporting(0);
ini_set('display_errors', '0');

ob_start();
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');

    require_once __DIR__ . '/../../includes/bootstrap.php';
    require_once INCLUDES_PATH . '/Database.php';
    require_once INCLUDES_PATH . '/auth.php';
    require_once INCLUDES_PATH . '/csrf.php';
    require_once CLASSES_PATH . '/Account.php';

    // Only POST allowed
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    // Require authentication
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }

    // Require admin privileges
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Forbidden', 'message' => 'admin_only']);
        exit;
    }

    // Require CSRF protection for state-changing request
    requireCsrf();

    // Read input - support JSON or form-encoded
    $email = null;
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (is_array($data) && isset($data['email'])) $email = trim($data['email']);
    } else {
        // fallback to POST form data
        if (isset($_POST['email'])) $email = trim($_POST['email']);
    }

    if ($email === null || $email === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Bad Request', 'message' => 'email_required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Bad Request', 'message' => 'invalid_email']);
        exit;
    }

    $pdo = Database::getConnection();

    // Find the target account
    $target = Account::findByEmail($email);
    if ($target === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Not Found', 'message' => 'account_not_found']);
        exit;
    }

    // Prevent admins from deleting themselves via this endpoint
    $currentId = getCurrentUserId();
    if ($currentId !== null && $currentId === $target->getId()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Forbidden', 'message' => 'cannot_delete_self']);
        exit;
    }

    // If the target is an admin, ensure we don't remove the last admin
    if ($target->getIsAdmin()) {
        $stmt = $pdo->prepare('SELECT COUNT(*) as c FROM account WHERE is_admin = 1');
        $stmt->execute();
        $count = (int)$stmt->fetchColumn();
        if ($count <= 1) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden', 'message' => 'cannot_delete_last_admin']);
            exit;
        }
    }

    // TODO: Optionally remove uploaded files (profile/banner) if stored locally.
    // We intentionally avoid making assumptions about file storage here.

    // Perform deletion (Account::delete uses primary key, DB will cascade related rows)
    $deleted = $target->delete();

    if (!$deleted) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error', 'message' => 'delete_failed']);
        exit;
    }

    // Success
    echo json_encode(['success' => true, 'message' => 'account_deleted']);
    ob_end_flush();

} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Fatal error', 'message' => $e->getMessage()]);
}
