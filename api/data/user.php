<?php
/**
 * User Data API Endpoint
 * GET /api/data/user.php
 *
 * Returns recent test attempts for the current user (date, benchmark, score)
 * Uses prepared statements and session-based authentication
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

    // Load bootstrap and helpers (go up to project root)
    require_once __DIR__ . '/../../includes/bootstrap.php';
    require_once INCLUDES_PATH . '/Database.php';
    require_once INCLUDES_PATH . '/auth.php';

    // Clear any output from includes
    ob_clean();

    // Only allow GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    // Require authenticated user (session)
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }

    $userId = getCurrentUserId();

    // Use PDO to fetch all attempts for the user in chronological order and compute status
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        "SELECT ta.id, ta.testscore_id, ta.score, ta.attempted_at, t.test_name AS benchmark, t.test_type AS test_type
         FROM testattempt ta
         INNER JOIN testscore ts ON ta.testscore_id = ts.id
         INNER JOIN test t ON ts.test_id = t.id
         WHERE ts.account_id = :account_id
         ORDER BY ta.attempted_at ASC"
    );
    $stmt->execute(['account_id' => $userId]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $states = [];
    $attempts = [];

    foreach ($rows as $row) {
        $tsid = (int)$row['testscore_id'];
        $score = isset($row['score']) ? (float)$row['score'] : null;
        $type = $row['test_type'] ?? 'maximize';

        if (!isset($states[$tsid])) {
            $states[$tsid] = ['count' => 0, 'last' => null, 'best' => null];
        }

        $state = &$states[$tsid];

        if ($state['count'] === 0) {
            $status = 'First-time';
        } else {
            if ($type === 'minimize') {
                if ($state['best'] === null || $score <= $state['best']) {
                    $status = 'Personal best';
                } elseif ($score < $state['last']) {
                    $status = 'Better';
                } else {
                    $status = 'Worse';
                }
            } else {
                if ($state['best'] === null || $score >= $state['best']) {
                    $status = 'Personal best';
                } elseif ($score > $state['last']) {
                    $status = 'Better';
                } else {
                    $status = 'Worse';
                }
            }
        }

        $attempts[] = [
            'date' => date('Y-m-d', strtotime($row['attempted_at'])),
            'timestamp' => date('Y-m-d H:i:s', strtotime($row['attempted_at'])),
            'benchmark' => $row['benchmark'],
            'score' => $score,
            'status' => $status
        ];

        $state['count'] += 1;
        $state['last'] = $score;
        if ($state['best'] === null) {
            $state['best'] = $score;
        } else {
            if ($type === 'minimize') {
                $state['best'] = min($state['best'], $score);
            } else {
                $state['best'] = max($state['best'], $score);
            }
        }
    }

    $attempts = array_reverse($attempts);

    http_response_code(200);
    echo json_encode(['success' => true, 'attempts' => $attempts]);
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

