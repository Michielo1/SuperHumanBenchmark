<?php
/**
 * Public Stats API
 * GET /api/stats.php
 *
 * Returns aggregated statistics per test (average score, attempts count, unique players)
 * Optional query params:
 *  - test: filter by test name ("test_name")
 */

// Disable all error output to prevent breaking JSON
error_reporting(0);
ini_set('display_errors', '0');

// Start output buffering to catch any unexpected output
ob_start();

// Convert PHP errors to exceptions
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');

    // Resolve project root and load bootstrap
    $baseDir = dirname(__DIR__);
    require_once $baseDir . '/includes/bootstrap.php';
    require_once INCLUDES_PATH . '/Database.php';

    // Only allow GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    $filterTest = isset($_GET['test']) ? trim($_GET['test']) : null;

    $pdo = Database::getConnection();

    $sql = "
        SELECT
            t.id,
            t.test_name,
            t.test_type,
            COUNT(ta.id) AS attempts_count,
            AVG(ta.score) AS avg_score,
            COUNT(DISTINCT ts.account_id) AS unique_players
        FROM test t
        LEFT JOIN testscore ts ON ts.test_id = t.id
        LEFT JOIN testattempt ta ON ta.testscore_id = ts.id
    ";

    $params = [];
    if ($filterTest) {
        $sql .= " WHERE t.test_name = :test_name";
        $params['test_name'] = $filterTest;
    }

    $sql .= " GROUP BY t.id ORDER BY t.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tests = [];

    foreach ($rows as $r) {
        $attemptsCount = (int)$r['attempts_count'];
        $avgScore = $r['avg_score'] !== null ? (float)$r['avg_score'] : null;

        $tests[] = [
            'id' => (int)$r['id'],
            'test_name' => $r['test_name'],
            'test_type' => $r['test_type'],
            'attempts_count' => $attemptsCount,
            'avg_score' => $avgScore,
            'unique_players' => (int)$r['unique_players']
        ];
    }

    echo json_encode([
        'success' => true,
        'tests' => $tests
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
