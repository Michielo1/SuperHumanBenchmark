<?php
/**
 * Leaderboard API Endpoint
 * Returns top scores for a specific benchmark
 */

// Disable all error output to prevent breaking JSON
error_reporting(0);
ini_set('display_errors', '0');

// Start output buffering to catch any unexpected output
ob_start();

// Set error handler to convert errors to exceptions
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');

    // Determine base path - go up from api/ to project root
    $baseDir = dirname(__DIR__);

    require_once $baseDir . '/includes/bootstrap.php';
    require_once INCLUDES_PATH . '/Database.php';
    require_once CLASSES_PATH . '/Test.php';
    require_once CLASSES_PATH . '/TestScore.php';

    // Clear any output from includes
    ob_clean();

    // Handle GET requests only
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    // Get benchmark name from query parameter
    $benchmark = isset($_GET['benchmark']) ? trim($_GET['benchmark']) : '';

    if (empty($benchmark)) {
        http_response_code(400);
        echo json_encode(['error' => 'Benchmark parameter is required']);
        exit;
    }

    // Get optional limit parameter (default 15, max 50)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
    $limit = min(max($limit, 1), 50); // Clamp between 1 and 50

    // Fetch the test
    $test = Test::findByTestName($benchmark);

    if (!$test) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Benchmark not found',
            'requested' => $benchmark,
            'message' => "No benchmark found with name: $benchmark"
        ]);
        ob_end_flush();
        exit;
    }

    // Get leaderboard
    $leaderboard = TestScore::getLeaderboard($test->getId(), $limit);

    // Format response
    $response = [
        'success' => true,
        'benchmark' => [
            'id' => $test->getId(),
            'test_name' => $test->getTestName(),
            'test_description' => $test->getTestDescription(),
            'test_type' => $test->getTestType()
        ],
        'leaderboard' => []
    ];

    // Format each entry
    foreach ($leaderboard as $entry) {
        $fullName = trim($entry['first_name']);
        if (!empty($entry['infix'])) {
            $fullName .= ' ' . $entry['infix'];
        }
        $fullName .= ' ' . $entry['last_name'];

        $response['leaderboard'][] = [
            'account_id' => (int)$entry['account_id'],
            'player_name' => $fullName,
            'high_score' => (float)$entry['high_score'],
            'attempts' => (int)$entry['attempt_count'],
            'updated_at' => $entry['updated_at']
        ];
    }

    http_response_code(200);
    echo json_encode($response);
    ob_end_flush();

} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    // Catch any fatal errors or other throwables
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error',
        'message' => $e->getMessage()
    ]);
}
