<?php
/**
 * Admin-only endpoint to compute the effect of dark vs light theme on test scores.
 * GET /api/analytics/theme-effect.php
 * Optional query: ?test_name=reaction_test
 */

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once INCLUDES_PATH . '/Database.php';
require_once INCLUDES_PATH . '/auth.php';

initSession();
header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'admin_only']);
    exit;
}

try {
    $pdo = Database::getConnection();
    $testFilter = isset($_GET['test_name']) ? trim($_GET['test_name']) : null;

    // Query averages per test and theme for attempts that include theme in metadata
        $sql = 'SELECT t.id AS test_id, t.test_name, t.test_type,
                 COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ae.props, "$.theme")), JSON_UNQUOTE(JSON_EXTRACT(ta.metadata, "$.theme"))) AS theme,
                 AVG(ta.score) AS avg_score, COUNT(*) AS cnt
             FROM testattempt ta
             JOIN testscore ts ON ta.testscore_id = ts.id
             JOIN test t ON ts.test_id = t.id
             LEFT JOIN analytics_events ae ON ta.analytics_event_id = ae.id AND ae.event_type = "test_attempt"
             WHERE (JSON_EXTRACT(ae.props, "$.theme") IS NOT NULL OR JSON_EXTRACT(ta.metadata, "$.theme") IS NOT NULL)';
    $params = [];
    if ($testFilter) {
        $sql .= " AND t.test_name = :tname";
        $params['tname'] = $testFilter;
    }
    $sql .= " GROUP BY t.id, theme";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Also get totals per test and counts missing theme
        $totSql = 'SELECT t.id AS test_id, t.test_name, COUNT(*) AS total_attempts,
                 SUM(CASE WHEN (JSON_EXTRACT(ae.props, "$.theme") IS NOT NULL OR JSON_EXTRACT(ta.metadata, "$.theme") IS NOT NULL) THEN 1 ELSE 0 END) AS attempts_with_theme
             FROM testattempt ta
             JOIN testscore ts ON ta.testscore_id = ts.id
             JOIN test t ON ts.test_id = t.id
             LEFT JOIN analytics_events ae ON ta.analytics_event_id = ae.id AND ae.event_type = "test_attempt"';
    if ($testFilter) {
        $totSql .= " WHERE t.test_name = :tname";
    }
    $totSql .= " GROUP BY t.id";
    $totStmt = $pdo->prepare($totSql);
    $totStmt->execute($testFilter ? ['tname' => $testFilter] : []);
    $totals = $totStmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize results
    $byTest = [];
    foreach ($rows as $r) {
        $tn = $r['test_name'];
        if (!isset($byTest[$tn])) {
            $byTest[$tn] = [
                'test_name' => $tn,
                'test_type' => $r['test_type'],
                'counts' => [],
                'avg' => [],
            ];
        }
        $theme = $r['theme'] ?? 'unknown';
        $byTest[$tn]['avg'][$theme] = (float)$r['avg_score'];
        $byTest[$tn]['counts'][$theme] = (int)$r['cnt'];
    }

    foreach ($totals as $t) {
        $tn = $t['test_name'];
        if (!isset($byTest[$tn])) {
            $byTest[$tn] = [
                'test_name' => $tn,
                'test_type' => null,
                'counts' => [],
                'avg' => []
            ];
        }
        $byTest[$tn]['total_attempts'] = (int)$t['total_attempts'];
        $byTest[$tn]['attempts_with_theme'] = (int)$t['attempts_with_theme'];
        $byTest[$tn]['attempts_missing_theme'] = $byTest[$tn]['total_attempts'] - $byTest[$tn]['attempts_with_theme'];
    }

    // Compute percent improvement per test
    $result = [];
    foreach ($byTest as $tn => $info) {
        $avg_light = $info['avg']['light'] ?? null;
        $avg_dark = $info['avg']['dark'] ?? null;
        $test_type = $info['test_type'];
        $percent_change = null;
        if ($avg_light !== null && $avg_dark !== null && $avg_light != 0) {
            if ($test_type === 'minimize') {
                // Lower is better: improvement if dark makes value smaller
                $percent_change = (($avg_light - $avg_dark) / $avg_light) * 100.0;
            } else {
                // maximize (default): higher is better
                $percent_change = (($avg_dark - $avg_light) / $avg_light) * 100.0;
            }
        }

        $result[] = [
            'test_name' => $tn,
            'test_type' => $test_type,
            'avg_light' => $avg_light,
            'avg_dark' => $avg_dark,
            'percent_change_dark_vs_light' => $percent_change,
            'counts' => $info['counts'] ?? [],
            'total_attempts' => $info['total_attempts'] ?? 0,
            'attempts_with_theme' => $info['attempts_with_theme'] ?? 0,
            'attempts_missing_theme' => $info['attempts_missing_theme'] ?? 0
        ];
    }

    echo json_encode(['success' => true, 'data' => $result]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'server_error']);
    exit;
}
