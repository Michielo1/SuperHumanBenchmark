<?php
/**
 * Reaction Test API Endpoint
 * Handles reaction test start and stop requests
 *
 * GET  /api/test/reaction/start - Start a reaction test
 * POST /api/test/reaction/stop  - Stop a reaction test and submit results
 */


require_once __DIR__ . '/../../includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
require_once INCLUDES_PATH . '/csrf.php';

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Parse the request URI to determine the endpoint
$request_uri = $_SERVER['REQUEST_URI'];
$path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));

// Determine which endpoint was called
$endpoint = end($path_parts);

// Start session to keep track of session variables (guard to avoid duplicate session_start notices)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["round"])) {
    $_SESSION["results"] = [];
    $_SESSION["round"] = 0;
    $_SESSION["delay"];
    $_SESSION["id"];
}

// Route to appropriate handler
// This is here because we have a single file handle 3 endpoints
switch ($endpoint) {
    case 'start':
        handleStart($method);
        break;
    case 'stop':
        // Enforce CSRF for state-changing POST
        if ($method === 'POST') requireCsrf();
        handleStop($method);
        break;
    case 'getRound':
        handleGetRound($method);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        exit;
}

/**
 * Handle /api/test/reaction/getRound
 * Returns current round number
 */
function handleGetRound($method) {
    // Only allow GET requests
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed. Use GET.']);
        exit;
    }
    http_response_code(200);
    echo json_encode(['round' => $_SESSION["round"]]);
    exit;
}

/**
 * Handle /api/test/reaction/start
 * Returns test initialization data
 */
function handleStart($method) {
    // Only allow GET requests
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed. Use GET.']);
        exit;
    }

    // Determine special transform with weighted probability
    $rand = mt_rand() / mt_getrandmax() * 100;
    if ($rand <= 30) {
        $special = 'randomColors';
    } elseif ($rand <= 40) {
        $special = 'moveBox';
    } elseif ($rand <= 45) {
        $special = 'duck';
    } elseif ($rand <= 46) {
        $special = 'moveBoxFakeout';
    } else {
        $special = 'none';
    }

    $_SESSION['id'] = uniqid('test_', true);
    $_SESSION['delay'] = mt_rand() / mt_getrandmax() * 12 + 3; // Set delay to a random float between 3 and 15.
    // Return test initialization data
    http_response_code(200);
    $response = json_encode([
        'success' => true,
        'message' => 'Reaction test started',
        'data' => [
            'id' => $_SESSION['id'],
            'delay' => $_SESSION['delay'],
            'special' => $special
        ]
    ]);
    echo $response;
    exit;
}

/**
 * Handle /api/test/reaction/stop
 * Receives test results and validates them
 */
function handleStop($method) {
    // Only allow POST requests
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed. Use POST.']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    // STEP 1: Read parameters
    // Required fields: "id", "refresh_rate", "start_time", "click_time", "delay"
    $required = ['id', 'refresh_rate', 'start_time', 'click_time', 'delay'];
    $missing = [];

    foreach ($required as $field) {
        if (!isset($input[$field])) {
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


    // STEP 2: Calculate minimal real click_time and validate
    //       Humans can't click instantly, so use refresh_rate of monitor to check if it's fake
    $minimal_time = $input['start_time'] + $input['refresh_rate'] + $input['delay'] * 1000;
    // TODO: Check if refresh rate > 0 and make start time server side
    // STEP 3: If valid, add time to array of times, if not valid, reset array.
    if ($input['click_time'] > $minimal_time && $input['delay'] == $_SESSION['delay'] && $input['id'] == $_SESSION['id']) {
        // Valid time
        $reaction_time = $input['click_time'] - $input['start_time'] - $input['delay'] * 1000;
        $_SESSION['results'][] = $reaction_time;
        $_SESSION['round']++;
        if ($_SESSION['round'] == 10) {
            $_SESSION['round'] = 0;
            // Calculate average
            $total_time = 0;
            foreach ($_SESSION['results'] as $result) {
                $total_time += $result;
            }
            $average_time = $total_time / 10;
            $metadata = [
                'results' => $_SESSION['results'],
                'refresh_rate' => $input['refresh_rate'],
                'delay' => $input['delay']
            ];

            // Save to DB
            require_once INCLUDES_PATH . '/classes/TestScore.php';
            require_once INCLUDES_PATH . '/classes/TestAttempt.php';
            require_once INCLUDES_PATH . '/classes/Test.php';
            require_once INCLUDES_PATH . '/auth.php';
            initSession();
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$user_id) {
                http_response_code(403);
                echo json_encode(['error' => 'No user_id in session']);
                exit;
            }

            $reactionTest = Test::findByTestName('reaction_test');
            if (!$reactionTest) {
                http_response_code(500);
                echo json_encode(['error' => 'Reaction test definition not found in database']);
                exit;
            }

            $test_id = $reactionTest->getId();
            $testScore = TestScore::findByAccountAndTest($user_id, $test_id);
            $isMinimize = $reactionTest->isMinimize();
            $score = $average_time; // For reaction test, score is average reaction time (lower is better)

            if (!$testScore) {
                $testScore = new TestScore([
                    'account_id' => $user_id,
                    'test_id' => $test_id,
                    'last_score' => $score,
                    'high_score' => $score,
                    'attempt_count' => 1
                ]);
            } else {
                $testScore->setLastScore($score);
                $testScore->setAttemptCount($testScore->getAttemptCount() + 1);
                if ($isMinimize) {
                    if ($score < $testScore->getHighScore()) {
                        $testScore->setHighScore($score);
                    }
                } else {
                    if ($score > $testScore->getHighScore()) {
                        $testScore->setHighScore($score);
                    }
                }
            }
            $testScore->save();

            // Insert a consent-gated analytics event (if user/session consented)
            $pdo = Database::getConnection();
            $sessionId = session_id();
            $analytics_event_id = null;
            $consentStmt = $pdo->prepare('SELECT analytics_allowed FROM consent WHERE account_id = :aid LIMIT 1');
            $consentStmt->execute(['aid' => $user_id]);
            $consent = $consentStmt->fetch(PDO::FETCH_ASSOC);
            $enabled = $consent ? (bool)$consent['analytics_allowed'] : false;
            if ($enabled) {
                $props = json_encode(['theme' => isset($input['theme']) ? $input['theme'] : null]);
                $evt = $pdo->prepare('INSERT INTO analytics_events (account_id, session_id, event_type, props, ts) VALUES (:aid, :sid, :etype, :props, NOW())');
                $evt->execute([
                    'aid' => $user_id ?: null,
                    'sid' => $sessionId ?: null,
                    'etype' => 'test_attempt',
                    'props' => $props
                ]);
                $analytics_event_id = (int)$pdo->lastInsertId();
            }

            $attempt = new TestAttempt([
                'testscore_id' => $testScore->getId(),
                'score' => $score,
                'metadata' => $metadata,
                'analytics_event_id' => $analytics_event_id
            ]);
            $attempt->save();
            $_SESSION['results'] = [];
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Succesfully completed last round.',
                'data' => [
                    'id' => $input['id'],
                    'reaction_time' => $average_time,
                    'round' => $_SESSION['round'],
                ]
            ]);
            exit;
        }
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Succesfully clicked the box when it was green',
            'data' => [
                'id' => $input['id'],
                'reaction_time' => $reaction_time,
                'round' => $_SESSION['round'],
            ]
        ]);
        exit;
    }
    // Invalid time
    $_SESSION['results'] = [];
    $_SESSION['round'] = 0;


    // Placeholder response
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => 'Box clicked too early',
        'data' => [
            'id' => $input['id'],
        ]
    ]);
    exit;
}
