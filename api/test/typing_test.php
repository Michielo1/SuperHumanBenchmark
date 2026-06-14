<?php
/**
 * Typing Test API Endpoint
 * Handles typing test word generation, transformations, start and end requests
 *
 * GET  /api/test/typing_test/get_words - Get random words for the test
 * GET  /api/test/typing_test/get_transformation - Get any special transformations
 * GET  /api/test/typing_test/start - Start a typing test
 * POST /api/test/typing_test/eind - End a typing test and submit results (expects JSON: { id: string, results: [{index: number, typed: string}, ...] })
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/cache.php';

header('Content-Type: application/json');

require_once INCLUDES_PATH . '/csrf.php';

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Parse the request URI to determine the endpoint
$request_uri = $_SERVER['REQUEST_URI'];
$path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));

// Determine which endpoint was called
$endpoint = end($path_parts);

// Route to appropriate handler
switch ($endpoint) {
    case 'get_words':
        handleGetWords($method);
        break;
    case 'start':
        handleStart($method);
        break;
    case 'eind':
        if ($method === 'POST') requireCsrf();
        handleEind($method);
        break;
    case 'active':
        if ($method === 'POST') requireCsrf();
        handleActive($method);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        exit;
}

/**
 * Handle /api/test/typing_test/get_words
 * Returns a list of words for the typing test (uses cache, TTL 6 hours)
 */
function handleGetWords($method) {
    // Only allow GET requests
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed. Use GET.']);
        exit;
    }

    // Get parameters
    $word_count = isset($_GET['count']) ? intval($_GET['count']) : 250;
    $word_count = max(10, min($word_count, 500)); // Clamp between 10 and 500

    $category = isset($_GET['category']) ? $_GET['category'] : 'brainrot';
    $type = isset($_GET['type']) ? $_GET['type'] : 'lowercase';

    // Get words (getWords will use file cache internally). Also request cache metadata.
    $cache_meta = null;
    $words = getWords($word_count, $category, $type, 21600, $cache_meta);

    if (!is_array($words) || count($words) < 1) {
        http_response_code(503);
        echo json_encode([
            'error' => 'Failed to fetch words from external API',
            'message' => 'Service temporarily unavailable'
        ]);
        exit;
    }

    // Prepare cache info for response
    $cache_info = [
        'used' => !empty($cache_meta['cached']),
        'backend' => $cache_meta['backend'] ?? CACHE_BACKEND,
        'cached_at' => isset($cache_meta['created']) && $cache_meta['created'] ? date(DATE_ATOM, $cache_meta['created']) : null,
        'stored' => isset($cache_meta['stored']) ? (bool)$cache_meta['stored'] : false
    ];

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Words generated',
        'data' => [
            'words' => array_values($words),
            'count' => count($words),
            'cache' => $cache_info
        ]
    ]);
    exit;
}

/**
 * Fetches words from external API.
 * It processes words for the test.
 * Returns a shuffled array of strings.
 */
function getWords(int $word_count = 300, $category = 'brainrot', $type = 'lowercase', int $cache_ttl = 21600, array &$cache_meta = null) {
    $word_count = max(10, min($word_count, 500)); // Clamp between 10 and 500

    // Build cache key and attempt to read (with metadata)
    $cache_key = "typing_words:{$category}:{$type}:{$word_count}";
    $cached_with_meta = file_cache_get_with_meta($cache_key, $cache_ttl);

    if (is_array($cached_with_meta['data']) && count($cached_with_meta['data']) > 0) {
        // Fill metadata for caller
        if (is_array($cache_meta)) {
            $cache_meta = $cached_with_meta['meta'];
        } elseif ($cache_meta === null) {
            $cache_meta = $cached_with_meta['meta'];
        }

        // Return a shuffled copy to avoid predictable sequences
        $words = $cached_with_meta['data'];
        shuffle($words);
        return $words;
    }

    // Call external API
    $api_url = "https://random-words-api.kushcreates.com/api?category={$category}&type={$type}&words={$word_count}";

    $response = @file_get_contents($api_url);
    if ($response === false) {
        // Ensure cache_meta indicates not cached
        $cache_meta = ['cached' => false, 'created' => null, 'expires_at' => null, 'backend' => CACHE_BACKEND];
        return [];
    }

    $word_objects = json_decode($response, true);
    if ($word_objects === null || !is_array($word_objects)) {
        $cache_meta = ['cached' => false, 'created' => null, 'expires_at' => null, 'backend' => CACHE_BACKEND];
        return [];
    }

    $words = array_map(function($item) {
        if (!isset($item['word']) || $item['word'] === null) {
            return '';
        } else {
            return $item['word'];
        }
    }, $word_objects);

    $filtered_words = [];
    foreach ($words as $i) {
        if(is_string($i) && $i !== '') {
            $filtered_words[] = $i;
        }
    }

    $words = $filtered_words;
    // Split multi-word entries into single words
    $words = splitWords($words);

    // Cache processed (un-shuffled) words for future requests
    if (count($words) > 0) {
        $set_ok = file_cache_set($cache_key, $words, $cache_ttl);
        if ($set_ok) {
            // Read back meta to capture created/expires times
            $stored = file_cache_get_with_meta($cache_key, $cache_ttl);
            $cache_meta = $stored['meta'] ?? ['cached' => false, 'created' => null, 'expires_at' => null, 'backend' => CACHE_BACKEND, 'stored' => true];
            // indicate that we stored in this request
            $cache_meta['stored'] = $cache_meta['stored'] ?? true;
        } else {
            $cache_meta = ['cached' => false, 'created' => null, 'expires_at' => null, 'backend' => CACHE_BACKEND, 'stored' => false];
            error_log("cache: failed to store key {$cache_key} using backend " . CACHE_BACKEND);
        }
    }

    shuffle($words);
    return $words;
}

/*
 * Split the words if they have multiple words.
 */
function splitWords(array $words) {
    $result_word = [];

    foreach ($words as $i) {
        if (!is_string($i)) {
            continue;
        }

        $i = trim($i);
        if ($i === '') {
            continue;
        }

        $split_word = preg_split('/\s+/', $i);

        foreach ($split_word as $j) {
            $j = trim($j);
            if ($j !== '') {
                $result_word[] = $j;
            }
        }
    }

    return $result_word;
}


/**
 * Handle /api/test/typing_test/start
 * Initializes a typing test session
 */
function handleStart($method) {
    // Only allow GET requests
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed. Use GET.']);
        exit;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $word_count = isset($_GET['count']) ? intval($_GET['count']) : 250;
    $category   = isset($_GET['category']) ? $_GET['category'] : 'brainrot';
    $type       = isset($_GET['type']) ? $_GET['type'] : 'lowercase';

    $cache_meta = null;
    $words = getWords($word_count, $category, $type, 21600, $cache_meta);
    if (count($words) < 10) {
        http_response_code(503);
        echo json_encode(['error' => 'Failed to fetch enough words']);
        exit;
    }

    $user_id = uniqid('typingtest_', true);
    $start_ts = microtime(true);

    $_SESSION['typing_test'] = [
        'id' => $user_id,
        'account_id' => $_SESSION['account_id'] ?? null,
        'start_ts' => $start_ts,
        'words' => $words,
        'mutated' => [],
        'rev' => 1
    ];

    $cache_info = [
        'used' => !empty($cache_meta['cached']),
        'backend' => $cache_meta['backend'] ?? CACHE_BACKEND,
        'cached_at' => isset($cache_meta['created']) && $cache_meta['created'] ? date(DATE_ATOM, $cache_meta['created']) : null,
        'stored' => isset($cache_meta['stored']) ? (bool)$cache_meta['stored'] : false
    ];

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Typing test started',
        'data' => [
            'id' => $user_id,
            'rev' => 1,
            'words' => $words,
            'cache' => $cache_info
        ]
    ]);
    exit;
}

/**
 * Handle /api/test/typing_test/eind
 * Receives and validates typing test results
 */
function handleEind($method) {
    // Only allow POST requests
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed. Use POST.']);
        exit;
    }

    // Parse JSON input
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if ($input === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    // Required fields: session id + per-word results
    $required = ['id', 'results'];
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
            'missing_fields' => $missing,
        ]);
        exit;
    }

    initSession();
    if (!isset($_SESSION['typing_test']) || !isset($_SESSION['typing_test']['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid typing test session found']);
        exit;
    }
    $session = $_SESSION['typing_test'];
    if ($input['id'] !== $session['id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Test ID does not match session']);
        exit;
    }

    // Compute active time from server-side start timestamp (prevents client tampering)
    $start_ts = isset($session['start_ts']) ? (float)$session['start_ts'] : null;
    if ($start_ts === null) {
        http_response_code(500);
        echo json_encode(['error' => 'Session start timestamp missing']);
        exit;
    }

    $now = microtime(true);
    $active_time = $now - $start_ts;

    if ($active_time <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid active time calculated']);
        exit;
    }

    // Validate and compute metrics from results
    $results = $input['results'];
    if (!is_array($results)) {
        http_response_code(400);
        echo json_encode(['error' => 'results must be an array']);
        exit;
    }

    $words = $session['words'];
    $total_words = 0;
    $correct_words = 0;
    $correct_keystrokes = 0;
    $total_keystrokes = 0;

    foreach ($results as $r) {
        if (!isset($r['index']) || !isset($r['typed'])) {
            continue;
        }
        $idx = (int)$r['index'];
        $typed = (string)$r['typed'];

        if ($idx < 0 || $idx >= count($words)) {
            continue;
        }

        $total_words++;
        $target = $words[$idx];

        // If the client reported a per-word keystroke count (includes the space that ended the word), prefer that
        $keystrokes_reported = null;
        if (isset($r['keystrokes']) && is_numeric($r['keystrokes'])) {
            $keystrokes_reported = max(0, intval($r['keystrokes']));
        }

        if ($keystrokes_reported !== null) {
            $total_keystrokes += $keystrokes_reported;
        } else {
            $total_keystrokes += strlen($typed);
        }

        $minlen = min(strlen($typed), strlen($target));
        for ($i = 0; $i < $minlen; $i++) {
            if ($typed[$i] === $target[$i]) {
                $correct_keystrokes++;
            }
        }

        if ($typed === $target) {
            $correct_words++;
        }
    }

    if ($total_keystrokes === 0) {
        http_response_code(422);
        echo json_encode(['error' => 'No typed input received']);
        exit;
    }

    $accuracy = round(($correct_keystrokes / $total_keystrokes) * 100, 2);
    $wpm = round(($correct_words / $active_time) * 60, 2);

    // Validate accuracy threshold
    if ($accuracy < 75) {
        http_response_code(422);
        echo json_encode([
            'error' => 'Your accuracy was too low to save a valid result. Please try again and aim for at least 75% accuracy!',
            'friendly' => true
        ]);
        exit;
    }

    // Prepare canonical values for DB storage

    // --- DB logic ---

    // --- DB logic ---
    require_once INCLUDES_PATH . '/classes/TestScore.php';
    require_once INCLUDES_PATH . '/classes/TestAttempt.php';
    require_once INCLUDES_PATH . '/classes/Test.php';

    // Find test_id for typing test
    $typingTest = Test::findByTestName('type_test');
    if (!$typingTest) {
        http_response_code(500);
        echo json_encode(['error' => 'Typing test definition not found in database']);
        exit;
    }
    $test_id = $typingTest->getId();
    $account_id = $_SESSION['user_id'] ?? null;
    if (!$account_id) {
        http_response_code(403);
        echo json_encode(['error' => 'No user_id in session']);
        exit;
    }

    // Find or create TestScore
    $testScore = TestScore::findByAccountAndTest($account_id, $test_id);
    $isMinimize = $typingTest->isMinimize();
    $score = $wpm; // For typing test, score is WPM

    if (!$testScore) {
        // First attempt for this user/test
        $testScore = new TestScore([
            'account_id' => $account_id,
            'test_id' => $test_id,
            'last_score' => $score,
            'high_score' => $score,
            'attempt_count' => 1
        ]);
    } else {
        // Update last_score and attempt_count
        $testScore->setLastScore($score);
        $testScore->setAttemptCount($testScore->getAttemptCount() + 1);
        // Update high_score if needed
        if ($isMinimize) {
            // Lower is better
            if ($score < $testScore->getHighScore()) {
                $testScore->setHighScore($score);
            }
        } else {
            // Higher is better
            if ($score > $testScore->getHighScore()) {
                $testScore->setHighScore($score);
            }
        }
    }
    $testScore->save();

    // Insert consent-gated analytics event (if consented)
    $pdo = Database::getConnection();
    $sessionId = session_id();
    $analytics_event_id = null;
    $consentStmt = $pdo->prepare('SELECT analytics_allowed FROM consent WHERE account_id = :aid LIMIT 1');
    $consentStmt->execute(['aid' => $account_id]);
    $consent = $consentStmt->fetch(PDO::FETCH_ASSOC);
    $enabled = $consent ? (bool)$consent['analytics_allowed'] : false;
    if ($enabled) {
        $props = json_encode(['theme' => isset($input['theme']) ? $input['theme'] : null]);
        $evt = $pdo->prepare('INSERT INTO analytics_events (account_id, session_id, event_type, props, ts) VALUES (:aid, :sid, :etype, :props, NOW())');
        $evt->execute([
            'aid' => $account_id ?: null,
            'sid' => $sessionId ?: null,
            'etype' => 'test_attempt',
            'props' => $props
        ]);
        $analytics_event_id = (int)$pdo->lastInsertId();
    }

    // Insert TestAttempt
    $attempt = new TestAttempt([
        'testscore_id' => $testScore->getId(),
        'score' => $score,
        'metadata' => [
            'total_words' => $total_words,
            'active_time' => $active_time,
            'accuracy' => $accuracy
        ],
        'analytics_event_id' => $analytics_event_id
    ]);
    $attempt->save();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'wpm' => $wpm,
        'correct_words' => $correct_words,
        'total_words' => $total_words,
        'accuracy' => $accuracy,
        'active_time' => $active_time
    ]);
    exit;
}


/*
 * Choose a letter to swap.
 */
function chooseLetter(string $word, int $user_typed, ?int $avoid = null ) {
    $type_length = strlen($word);
    if ($type_length < 4) {
        return NULL;
    }

    $max_letter = $type_length - 2;

    $min = max(1, min($user_typed, $max_letter));
    $max = max(1, min($user_typed + 3, $max_letter));
    if ($min > $max) {
        return NULL;
    }

    for ($i = 0; $i < 10; $i++) {
        $try = random_int($min, $max);
        if ($avoid === null || $try !== $avoid) {
            return $try;
        }
    }

    if ($avoid !== null && $min === $max && $min === $avoid) {
        return null;
    } else {
        return $min;
    }
}

/*
 * Swap the letters of a word.
 */
function swapLetter(string $word, int $i) {
    $characters = str_split($word);

    if (!isset($characters[$i], $characters[$i + 1])) {
        return $word;
    }

    $tmp_char = $characters[$i];
    $characters[$i] = $characters[$i + 1];
    $characters[$i + 1] = $tmp_char;
    return implode('', $characters);
}

/*
 * Swaps the letter of a word two times.
 */
function twoSwaps(string $word, int $user_typed) {
    $letter_chosen = chooseLetter($word, $user_typed);
    if ($letter_chosen === null) {
        return $word;
    }

    $word_2 = swapLetter($word, $letter_chosen);
    if ($word_2 === $word) {
        return $word;
    }

    $letter_chosen_2 = chooseLetter($word_2, $user_typed, $letter_chosen);
    if ($letter_chosen_2 === null) {
        return $word_2;
    }

    $word_3 = swapLetter($word_2, $letter_chosen_2);
    return $word_3;
}

/*
 * Updates during type test.
 */
function handleActive($method) {
    // Only POST requests are allowed.
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed. Use POST.']);
        exit;
    }

    // Start session.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['typing_test']['id'], $_SESSION['typing_test']['words'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No active typing test session']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if(!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    $required = ['id', 'word_index', 'typed_length'];
    foreach($required as $i) {
        if (!isset($input[$i])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing field: $i"]);
            exit;
        }
    }

    $session = &$_SESSION['typing_test'];
    if ($input['id'] !== $session['id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Test ID mismatch']);
        exit;
    }


    $word_index = (int)$input['word_index'];
    $user_type  = (int)$input['typed_length'];

    $words = $session['words'];
    if ($word_index < 0 || $word_index >= count($words)) {
        http_response_code(422);
        echo json_encode(['error' => 'word_index out of range']);
        exit;
    }

    // Only swap letters if user typed over 2.
    $try_word = ($user_type > 2);
    $already_mutated = isset($session['mutated'][$word_index]);

    if ($try_word && !$already_mutated) {
        if (mt_rand(1, 100) <= 60) {
            $old_word = $words[$word_index];
            $new_word = twoSwaps($old_word, $user_type);
            if ($new_word !== $old_word) {
                $session['words'][$word_index] = $new_word;
                $session['mutated'][$word_index] = true;
                $session['rev'] = ($session['rev'] ?? 1) + 1;

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'rev' => $session['rev'],
                    'mutation' => [
                        'word_index' => $word_index,
                        'old_word' => $old_word,
                        'new_word' => $new_word,
                    ]
                ]);
                exit;
            }
        }
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'rev' => $session['rev'] ?? 1
    ]);
    exit;
}
