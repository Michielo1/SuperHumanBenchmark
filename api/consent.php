<?php
/**
 * Consent API
 * POST /api/consent.php  -> { analytics_enabled: bool }
 * GET  /api/consent.php  -> { analytics_enabled: bool }
 */

error_reporting(0);
ini_set('display_errors', '0');
ob_start();

try {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    $baseDir = dirname(__DIR__);
    require_once $baseDir . '/includes/bootstrap.php';
    require_once INCLUDES_PATH . '/Database.php';
    require_once INCLUDES_PATH . '/auth.php';
    require_once INCLUDES_PATH . '/csrf.php';

    initSession();

    $pdo = Database::getConnection();
    $userId = getCurrentUserId();
    $sessionId = session_id();
    $method = $_SERVER['REQUEST_METHOD'];

    // Preflight request
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    /**
     * GET: Fetch consent status
     */
    if ($method === 'GET') {
        $row = null;

        if ($userId) {
            // Try account-bound consent first
            $stmt = $pdo->prepare(
                'SELECT id, analytics_allowed FROM consent WHERE account_id = :aid LIMIT 1'
            );
            $stmt->execute(['aid' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // If none exists, try migrating session consent
            if (!$row) {
                $stmt = $pdo->prepare(
                    'SELECT id, analytics_allowed FROM consent WHERE session_id = :sid LIMIT 1'
                );
                $stmt->execute(['sid' => $sessionId]);
                $srow = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($srow) {
                    $pdo->beginTransaction();
                    try {
                        $check = $pdo->prepare(
                            'SELECT id FROM consent WHERE account_id = :aid LIMIT 1 FOR UPDATE'
                        );
                        $check->execute(['aid' => $userId]);
                        $acct = $check->fetch(PDO::FETCH_ASSOC);

                        if ($acct) {
                            // Merge into existing account consent
                            $upd = $pdo->prepare(
                                'UPDATE consent
                                 SET analytics_allowed = :enabled, updated_at = NOW()
                                 WHERE account_id = :aid'
                            );
                            $upd->execute([
                                'enabled' => $srow['analytics_allowed'],
                                'aid' => $userId
                            ]);

                            $del = $pdo->prepare('DELETE FROM consent WHERE id = :id');
                            $del->execute(['id' => $srow['id']]);
                        } else {
                            // Attach session consent to account
                            $attach = $pdo->prepare(
                                'UPDATE consent
                                 SET account_id = :aid, session_id = NULL, updated_at = NOW()
                                 WHERE id = :id'
                            );
                            $attach->execute([
                                'aid' => $userId,
                                'id' => $srow['id']
                            ]);
                        }

                        $stmt = $pdo->prepare(
                            'SELECT analytics_allowed FROM consent WHERE account_id = :aid LIMIT 1'
                        );
                        $stmt->execute(['aid' => $userId]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);

                        $pdo->commit();
                    } catch (Throwable $e) {
                        $pdo->rollBack();
                        $row = null;
                    }
                }
            }
        } else {
            // Anonymous session consent
            $stmt = $pdo->prepare(
                'SELECT analytics_allowed FROM consent WHERE session_id = :sid LIMIT 1'
            );
            $stmt->execute(['sid' => $sessionId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            'success' => true,
            'analytics_enabled' => $row ? (bool)$row['analytics_allowed'] : null,
            'consent_exists' => (bool)$row
        ]);

        ob_end_flush();
        exit;
    }

    /**
     * POST: Store consent status
     * CSRF intentionally not required for consent
     */
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data) || !array_key_exists('analytics_enabled', $data)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing analytics_enabled'
            ]);
            ob_end_flush();
            exit;
        }

        $analyticsEnabled = (bool)$data['analytics_enabled'];
        $enabledInt = $analyticsEnabled ? 1 : 0;

        if ($userId) {
            $pdo->beginTransaction();
            try {
                // Update existing account consent
                $stmt = $pdo->prepare(
                    'UPDATE consent
                     SET analytics_allowed = :enabled, updated_at = NOW()
                     WHERE account_id = :aid'
                );
                $stmt->execute([
                    'enabled' => $enabledInt,
                    'aid' => $userId
                ]);

                if ($stmt->rowCount() === 0) {
                    // Check for session consent to migrate
                    $stmt = $pdo->prepare(
                        'SELECT id FROM consent WHERE session_id = :sid LIMIT 1 FOR UPDATE'
                    );
                    $stmt->execute(['sid' => $sessionId]);
                    $srow = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($srow) {
                        $check = $pdo->prepare(
                            'SELECT id FROM consent WHERE account_id = :aid LIMIT 1 FOR UPDATE'
                        );
                        $check->execute(['aid' => $userId]);
                        $acct = $check->fetch(PDO::FETCH_ASSOC);

                        if ($acct) {
                            $upd = $pdo->prepare(
                                'UPDATE consent
                                 SET analytics_allowed = :enabled, updated_at = NOW()
                                 WHERE account_id = :aid'
                            );
                            $upd->execute([
                                'enabled' => $enabledInt,
                                'aid' => $userId
                            ]);

                            $del = $pdo->prepare('DELETE FROM consent WHERE id = :id');
                            $del->execute(['id' => $srow['id']]);
                        } else {
                            $attach = $pdo->prepare(
                                'UPDATE consent
                                 SET account_id = :aid, session_id = NULL,
                                     analytics_allowed = :enabled, updated_at = NOW()
                                 WHERE id = :id'
                            );
                            $attach->execute([
                                'aid' => $userId,
                                'enabled' => $enabledInt,
                                'id' => $srow['id']
                            ]);
                        }
                    } else {
                        $ins = $pdo->prepare(
                            'INSERT INTO consent (account_id, analytics_allowed, given_at)
                             VALUES (:aid, :enabled, NOW())'
                        );
                        $ins->execute([
                            'aid' => $userId,
                            'enabled' => $enabledInt
                        ]);
                    }
                }

                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            // Anonymous session consent
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare(
                    'UPDATE consent
                     SET analytics_allowed = :enabled, updated_at = NOW()
                     WHERE session_id = :sid'
                );
                $stmt->execute([
                    'enabled' => $enabledInt,
                    'sid' => $sessionId
                ]);

                if ($stmt->rowCount() === 0) {
                    $stmt = $pdo->prepare(
                        'INSERT INTO consent (session_id, analytics_allowed, given_at)
                         VALUES (:sid, :enabled, NOW())'
                    );
                    $stmt->execute([
                        'sid' => $sessionId,
                        'enabled' => $enabledInt
                    ]);
                }

                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

        echo json_encode([
            'success' => true,
            'analytics_enabled' => $analyticsEnabled
        ]);

        ob_end_flush();
        exit;
    }

    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    ob_end_flush();
    exit;

} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error'
    ]);
}
