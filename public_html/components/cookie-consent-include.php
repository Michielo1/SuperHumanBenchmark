<?php
// Ensure session is started before generating CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// This snippet sets a JS variable for asset path, to be included in <head> of every page
if (!isset($assetPath)) {
    $currentDir = basename(dirname($_SERVER['PHP_SELF']));
    $assetPath = (($currentDir === 'pages') || ($currentDir === 'tests')) ? '../' : '';
}
?>
<?php
// Expose CSRF token to JavaScript via meta tag so fetch() callers can read it.
try {
    if (defined('INCLUDES_PATH')) {
        require_once INCLUDES_PATH . '/csrf.php';
    } else {
        // fallback: try relative path from public_html/components/ (go up two levels to repository root)
        $csrf_file = dirname(__DIR__, 2) . '/includes/csrf.php';
        if (file_exists($csrf_file)) {
            require_once $csrf_file;
        }
    }
    if (function_exists('get_csrf_token')) {
        $csrf = htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8');
        echo "<meta name=\"csrf-token\" content=\"{$csrf}\">\n";
    }
} catch (Exception $e) {
    // silent fallback if CSRF helper not available
}
?>
<script>
    window.assetPath = <?php echo json_encode($assetPath); ?>;
</script>
<?php
// Expose API base to JavaScript via window.API_BASE_URL
try {
    if (defined('INCLUDES_PATH')) {
        require_once INCLUDES_PATH . '/bootstrap.php';
    } else {
        require_once __DIR__ . '/../../../includes/bootstrap.php';
    }
    $api_base = defined('API_BASE_URL') ? API_BASE_URL : '/api';
    if (substr($api_base, -1) !== '/') $api_base .= '/';
    echo "<script>window.API_BASE_URL = " . json_encode($api_base) . "; if (!window.API_BASE_URL.endsWith('/')) window.API_BASE_URL += '/';</script>\n";
} catch (Exception $e) {
    // silent fallback - do nothing
}
?>
<script src="<?php echo $assetPath; ?>assets/js/api.js" defer></script>
<script src="<?php echo $assetPath; ?>assets/js/cookie-consent.js" defer></script>
