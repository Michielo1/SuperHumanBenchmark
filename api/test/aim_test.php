<?php
/**
 * Aim Test API Endpoint
 * Handles aim test word generation, transformations, start and end requests
 *
 * GET  /api/test/typing_test/get_words - Get random words for the test
 * GET  /api/test/typing_test/get_transformation - Get any special transformations
 * GET  /api/test/typing_test/start - Start a typing test
 * POST /api/test/typing_test/eind - End a typing test and submit results
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';

header('Content-Type: application/json');

require_once INCLUDES_PATH . '/csrf.php';

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Parse the request URI to determine the endpoint
$request_uri = $_SERVER['REQUEST_URI'];
$path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));

// Determine which endpoint was called
$endpoint = end($path_parts);

$targetSize = 50;
$targetGoal = 15;

// Route to appropriate handler
switch ($endpoint) {
    case 'start':
        handleStart($method);
        break;
    case 'next':
        if ($method === 'POST') requireCsrf();
        handleNext($method, $targetSize);
        break;
    case 'eind':
        if ($method === 'POST') requireCsrf();
        handleEind($method);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        exit;
}



/**
 * Handle /api/test/typing_test/eind
 * Receives and validates typing test results
 */
function handleStart($method) {
    // Got this from typing test but with GET
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed. Use GET.']);
        exit;
    }

    initSession();
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => 'You must be logged in to start an aim test'
        ]);
        exit;
    }

    $account_id = $_SESSION['user_id'];

    // Unique run ID
    $run_id = uniqid('aimtest_', true);

    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'classic';
    $duration_ms = isset($_GET['duration_ms']) ? intval($_GET['duration_ms']) : 30000;

    // Basic validation
    if (!preg_match('/^[a-z0-9_-]{2,32}$/i', $mode)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid mode']);
        exit;
    }
    $duration_ms = max(5000, min($duration_ms, 120000));

    // Saved at session to validate at END
    $_SESSION['aim_test'] = [
        'id' => $run_id,
        'mode' => $mode,
        'duration_ms' => $duration_ms,
        'account_id' => $account_id,
        'started_at' => microtime(true),
        'ended_at' => null
    ];

    // Reset runtime aim state so targetNum and position start fresh on each run
    $_SESSION['aim_state'] = [
        'x' => 475,
        'y' => 225,
        'targetNum' => 0,
        'totalTime' => 0
    ];

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Aim test started',
        'data' => [
            'id' => $run_id,
            'mode' => $mode,
            'duration_ms' => $duration_ms
        ]
    ]);

    exit;
}

function handleEind($method) {
    // Parse JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    // Required fields
    $required = ['id', 'total_words', 'time_taken'];
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

    // Validate session and ID
    initSession();
    if (!isset($_SESSION['aim_test']) || !isset($_SESSION['aim_test']['id'], $_SESSION['aim_test']['account_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid aim test session found']);
        exit;
    }
    $session = $_SESSION['aim_test'];
    if ($input['id'] !== $session['id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Test ID does not match session']);
        exit;
    }

    // Verify time_taken > delay
    $delay = isset($session['delay']) ? (float)$session['delay'] : 0;
    $time_taken = (float)$input['time_taken'] / 1000;
    if ($time_taken <= $delay) {
        http_response_code(422);
        echo json_encode([
            'error' => 'Invalid result: time_taken must be greater than delay',
            'detail' => [
                'time_taken' => $time_taken,
                'delay' => $delay
            ]
        ]);
        exit;
    }

    // Calculate time taken using (time_taken - delay)
    $active_time = $time_taken - $delay;


    // Save to database: TestScore and TestAttempt
    require_once INCLUDES_PATH . '/classes/TestScore.php';
    require_once INCLUDES_PATH . '/classes/TestAttempt.php';
    require_once INCLUDES_PATH . '/classes/Test.php';

    // Get test_id for aim test
    $aimTest = Test::findByTestName('aim_test');
    if (!$aimTest) {
        http_response_code(500);
        echo json_encode(['error' => 'aim test definition not found in database']);
        exit;
    }
    $test_id = $aimTest->getId();


    // Find or create TestScore for this user and test
    $testScore = TestScore::findByAccountAndTest($session['account_id'], $test_id);
    $score = $active_time; // For aim test, score is time (lower is better)
    if (!$testScore) {
        $testScore = new TestScore([
            'account_id' => $session['account_id'],
            'test_id' => $test_id,
            'last_score' => $score,
            'high_score' => $score,
            'attempt_count' => 1
        ]);
    } else {
        $testScore->setLastScore($score);
        $testScore->setAttemptCount($testScore->getAttemptCount() + 1);
        // Lower is better for aim test
        if ($score < $testScore->getHighScore()) {
            $testScore->setHighScore($score);
        }
    }
    $testScore->save();

    // Insert TestAttempt with metadata
    // Insert consent-gated analytics event (if consented)
    $pdo = Database::getConnection();
    $sessionId = session_id();
    $analytics_event_id = null;
    $consentStmt = $pdo->prepare('SELECT analytics_allowed FROM consent WHERE account_id = :aid LIMIT 1');
    $consentStmt->execute(['aid' => $session['account_id']]);
    $consent = $consentStmt->fetch(PDO::FETCH_ASSOC);
    $enabled = $consent ? (bool)$consent['analytics_allowed'] : false;
    if ($enabled) {
        $props = json_encode(['theme' => isset($input['theme']) ? $input['theme'] : null]);
        $evt = $pdo->prepare('INSERT INTO analytics_events (account_id, session_id, event_type, props, ts) VALUES (:aid, :sid, :etype, :props, NOW())');
        $evt->execute([
            'aid' => $session['account_id'] ?: null,
            'sid' => $sessionId ?: null,
            'etype' => 'test_attempt',
            'props' => $props
        ]);
        $analytics_event_id = (int)$pdo->lastInsertId();
    }

    $attempt = new TestAttempt([
        'testscore_id' => $testScore->getId(),
        'score' => $score,
        'metadata' => [
            'active_time' => $active_time,
            'delay' => $delay,
            'raw_time_taken' => $time_taken
        ],
        'analytics_event_id' => $analytics_event_id
    ]);
    $attempt->save();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'OK',
        'time taken' => $active_time
    ]);
    exit;
}

/**
 * Handle /api/test/typing_test/start
 * Initializes a typing test session
 */
function handleNext($method, $targetSize) {
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed. Use POST.']);
        exit;
    }

    // local variables that need to be stored across calls of the event
    initSession();

    if (!isset($_SESSION['aim_state'])) {
        $_SESSION['aim_state'] = [
            'x' => 475,
            'y' => 225,
            'targetNum' => 0,
            'totalTime' => 0
        ];
    }

    $state = &$_SESSION['aim_state'];

    // get mouse and movement coordinates from the front end
    $data = json_decode(file_get_contents("php://input"), true);

    // update mouse data
    $mouseX = isset($data['x']) ? (float)$data['x'] : 0;
    $mouseY = isset($data['y']) ? (float)$data['y'] : 0;

    // apply incremental movement if provided (we expect dx/dy to be incremental deltas)
    $dx = isset($data['dx']) ? (float)$data['dx'] : 0;
    $dy = isset($data['dy']) ? (float)$data['dy'] : 0;

    // client-side bookkeeping (for diagnostics and safe resync)
    $clientTargetX = isset($data['clientTargetX']) ? (float)$data['clientTargetX'] : null;
    $clientTargetY = isset($data['clientTargetY']) ? (float)$data['clientTargetY'] : null;
    $clientLastSentX = isset($data['lastSentTargetX']) ? (float)$data['lastSentTargetX'] : null;
    $clientLastSentY = isset($data['lastSentTargetY']) ? (float)$data['lastSentTargetY'] : null;

    // canvas size can be provided by the client to allow proper clamping
    $canvasWidth = isset($data['canvasWidth']) ? intval($data['canvasWidth']) : 1000;
    $canvasHeight = isset($data['canvasHeight']) ? intval($data['canvasHeight']) : 500;
    $maxX = max(0, $canvasWidth - $targetSize);
    $maxY = max(0, $canvasHeight - $targetSize);

    // small tolerance to account for movement/rounding jitter (raised to reduce false misses from scaling/rounding)
    $tolerance = 12;

    // check hit against the position before we apply this increment
    $preX = $state['x'];
    $preY = $state['y'];
    $preHit = (
        $mouseX >= $preX - $tolerance &&
        $mouseX <= $preX + $targetSize + $tolerance &&
        $mouseY >= $preY - $tolerance &&
        $mouseY <= $preY + $targetSize + $tolerance
    );

    // apply movement deltas
    $state['x'] += $dx;
    $state['y'] += $dy;

    // clamp to canvas bounds
    if ($state['x'] < 0) $state['x'] = 0;
    if ($state['x'] > $maxX) $state['x'] = $maxX;
    if ($state['y'] < 0) $state['y'] = 0;
    if ($state['y'] > $maxY) $state['y'] = $maxY;

    // check hit against the position after movement as well
    $postHit = (
        $mouseX >= $state['x'] - $tolerance &&
        $mouseX <= $state['x'] + $targetSize + $tolerance &&
        $mouseY >= $state['y'] - $tolerance &&
        $mouseY <= $state['y'] + $targetSize + $tolerance
    );

    $hitCheck = $preHit || $postHit;

    // server-side fallback: if client reports localHit and we're just outside tolerance, accept it
    $fallbackApplied = false;
    if (!$hitCheck && isset($data['localHit']) && $data['localHit']) {
        // use a slightly larger tolerance for the fallback to account for client rounding/scale
        $fallbackTol = $tolerance + 6; // e.g., 18 when $tolerance is 12
        $fallbackPreHit = (
            $mouseX >= $preX - $fallbackTol &&
            $mouseX <= $preX + $targetSize + $fallbackTol &&
            $mouseY >= $preY - $fallbackTol &&
            $mouseY <= $preY + $targetSize + $fallbackTol
        );
        if ($fallbackPreHit) {
            $hitCheck = true;
            $fallbackApplied = true;
        }
    }

    // safe resync: if client reports no movement (dx/dy == 0) but has a different target position, align server to client
    $resynced = false;
    $resyncThreshold = 20; // pixels
    if (!$hitCheck && $dx == 0 && $dy == 0 && $clientTargetX !== null && $clientTargetY !== null) {
        $dxDiff = abs($state['x'] - $clientTargetX);
        $dyDiff = abs($state['y'] - $clientTargetY);
        if ($dxDiff > $resyncThreshold || $dyDiff > $resyncThreshold) {
            // clamp client-provided coords to canvas
            $state['x'] = max(0, min($clientTargetX, $maxX));
            $state['y'] = max(0, min($clientTargetY, $maxY));
            $resynced = true;
        }
    }

    // check if the target is hit and update values accordingly
    if ($hitCheck) {
        $state['targetNum']++;
        // Do not call handleEind() here; let frontend handle /eind
        if($state['targetNum'] >= 15) {
            // Optionally, you could set a flag or do nothing
        }
        $state['x'] = rand(0, $maxX);
        $state['y'] = rand(0, $maxY);

        $state['totalTime'] += $data['clickTime'] - $data['startTime'];
    }

    // build response
    $response = [
        'hit' => $hitCheck,
        'x' => $state['x'],
        'y' => $state['y'],
        'num' => $state['targetNum'],
        'totalTimeTaken' => $state['totalTime']
    ];

    echo json_encode($response);
    exit;
}




