<?php
/**
 * Public Admin Data API
 * GET /api/data/admin.php
 *
 * Returns paginated account data for the admin dashboard.
 * Query params:
 *  - page (int, default 1)
 *  - per_page (int, default 10)
 *  - search (string, optional) matches first_name, last_name, email, or id
 */

// Disable all error output to prevent breaking JSON
error_reporting(0);
ini_set('display_errors', '0');

ob_start();
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    require_once __DIR__ . '/../../includes/bootstrap.php';
    require_once INCLUDES_PATH . '/Database.php';
    require_once INCLUDES_PATH . '/auth.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }

    // Only allow admin users to access this endpoint
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Forbidden']);
        exit;
    }

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = isset($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;

    $offset = ($page - 1) * $per_page;

    $pdo = Database::getConnection();

    $where = '';
    $params = [];
    if ($search !== null && $search !== '') {
        $where = ' WHERE (a.first_name LIKE :s OR a.last_name LIKE :s OR a.email LIKE :s)';
        $params['s'] = '%' . $search . '%';
        if (is_numeric($search)) {
            $where .= ' OR a.id = :id';
            $params['id'] = (int)$search;
        }
    }

    // Total count (for pagination)
    $countSql = "SELECT COUNT(*) as c FROM account a" . $where;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $sql = "
        SELECT a.id, a.username, a.first_name, a.infix, a.last_name, a.email, a.is_admin, a.created_at,
            (SELECT COUNT(ta.id) FROM testscore ts LEFT JOIN testattempt ta ON ta.testscore_id = ts.id WHERE ts.account_id = a.id) AS total_attempts,
            (SELECT COUNT(DISTINCT ts.test_id) FROM testscore ts WHERE ts.account_id = a.id) AS unique_tests,
            (SELECT MAX(ta.attempted_at) FROM testscore ts JOIN testattempt ta ON ta.testscore_id = ts.id WHERE ts.account_id = a.id) AS last_attempt
        FROM account a
        " . $where . "
        ORDER BY a.id ASC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);

    // Bind params
    foreach ($params as $k => $v) {
        $stmt->bindValue(':' . $k, $v);
    }
    $stmt->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $users = [];
    foreach ($rows as $r) {
        $fullname = trim($r['first_name'] . ' ' . ($r['infix'] ? $r['infix'] . ' ' : '') . $r['last_name']);
        $users[] = [
            'id' => (int)$r['id'],
            'username' => $r['username'],
            'first_name' => $r['first_name'],
            'infix' => $r['infix'],
            'last_name' => $r['last_name'],
            'full_name' => $fullname,
            'email' => $r['email'],
            'is_admin' => isset($r['is_admin']) ? (bool)$r['is_admin'] : false,
            'joined' => $r['created_at'],
            'total_attempts' => (int)$r['total_attempts'],
            'unique_tests' => (int)$r['unique_tests'],
            'last_attempt' => $r['last_attempt'] // may be null
        ];
    }

    echo json_encode([
        'success' => true,
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'users' => $users
    ]);

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
