<?php
/**
 * Tests API Endpoint
 * Returns all available tests/benchmarks
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

    // Clear any output from includes
    ob_clean();

    // Handle GET requests only
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    // Get all tests
    $tests = Test::getAll();

    // Format response
    $response = [
        'success' => true,
        'tests' => []
    ];

    foreach ($tests as $test) {
        $response['tests'][] = [
            'id' => $test->getId(),
            'naam' => $test->getTestName(),
            'beschrijving' => $test->getTestDescription(),
            'type' => $test->getTestType()
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
