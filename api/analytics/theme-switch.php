<?php
/**
 * Theme switch analytics
 * POST /api/analytics/theme-switch.php
 * Body: { theme: "dark" | "light" }
 * Only records the event if analytics consent exists for the current account/session
 */

error_reporting(0);
ini_set('display_errors', '0');
ob_start();

try {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    $baseDir = dirname(__DIR__, 2); // this file lives in /api/analytics/
    require_once $baseDir . '/includes/bootstrap.php';
    require_once INCLUDES_PATH . '/Database.php';
    require_once INCLUDES_PATH . '/auth.php';

    initSession();
    $pdo = Database::getConnection();


    $data = json_decode(file_get_contents('php://input'), true);
    $theme = isset($data['theme']) ? trim($data['theme']) : null;

    if (!$theme || !in_array($theme, ['dark', 'light'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid theme']);
        exit;
    }

    $userId = getCurrentUserId();
    $sessionId = session_id();

    // Check consent: prefer account record when logged in
    $consentStmt = null;
    if ($userId) {
        $consentStmt = $pdo->prepare('SELECT analytics_allowed FROM consent WHERE account_id = :aid LIMIT 1');
        $consentStmt->execute(['aid' => $userId]);
    } else {
        $consentStmt = $pdo->prepare('SELECT analytics_allowed FROM consent WHERE session_id = :sid LIMIT 1');
        $consentStmt->execute(['sid' => $sessionId]);
    }

    $consent = $consentStmt->fetch(PDO::FETCH_ASSOC);
    $enabled = $consent ? (bool)$consent['analytics_allowed'] : false;

    if ($enabled) {
        $props = json_encode(['theme' => $theme]);
        $insert = $pdo->prepare('INSERT INTO analytics_events (account_id, session_id, event_type, props, ts) VALUES (:aid, :sid, :etype, :props, NOW())');
        $insert->execute([
            'aid' => $userId ?: null,
            'sid' => $sessionId ?: null,
            'etype' => 'theme_switch',
            'props' => $props
        ]);

        echo json_encode(['success' => true, 'recorded' => true, 'action' => 'inserted']);
        ob_end_flush();
        exit;
    }

    // If consent not enabled, do nothing but return success
    echo json_encode(['success' => true, 'recorded' => false, 'message' => 'consent_not_given']);
    ob_end_flush();
    exit;

} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Fatal error']);
}
