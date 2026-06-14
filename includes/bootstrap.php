<?php
/**
 * Bootstrap file - defines application-wide path constants
 * Include this file at the start of any entry point
 * 
 * For deployment to /var/www/html/, update BASE_PATH to '/var/www'
 */

// Define the base path of the application (one level up from includes/)
// For development: /home/michielk
// For production: /var/www
define('BASE_PATH', dirname(__DIR__));

// Define common directory paths
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('API_PATH', BASE_PATH . '/api');
define('DATABASE_PATH', BASE_PATH . '/database');
define('CLASSES_PATH', INCLUDES_PATH . '/classes');


// Define the base URL for API endpoints (change as needed for deployment)
// Example: '/api' for local, 'https://api.example.com' for production
if (!defined('API_BASE_URL')) {
	define('API_BASE_URL', '/api');
}

// Detect if the current request is over HTTPS, directly or via a reverse proxy.
// We check common proxy headers (X-Forwarded-Proto, X-Forwarded-SSL) to support deployments
// behind load balancers or proxies that terminate TLS.
$__is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
	|| (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
	|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && stripos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false)
	|| (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on');

define('IS_HTTPS', (bool)$__is_https);

// OPTIONAL: Redirect plain HTTP requests to HTTPS at the application level.
// This is a fallback if you prefer not to change webserver/Apache configuration.
// Behavior:
// - Only redirects for GET and HEAD (to avoid breaking POST requests)
// - Honors common proxy headers via IS_HTTPS detection (X-Forwarded-Proto / X-Forwarded-SSL)
// - Can be disabled by setting env var SKIP_HTTPS_REDIRECT or setting ENVIRONMENT === 'development'
if (!IS_HTTPS && php_sapi_name() !== 'cli' && empty(getenv('SKIP_HTTPS_REDIRECT')) && !(defined('ENVIRONMENT') && ENVIRONMENT === 'development')) {
	$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
	if (in_array($method, ['GET', 'HEAD'], true)) {
		$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? null;
		if ($host) {
			$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
			$httpsUrl = 'https://' . $host . $requestUri;
			header('Location: ' . $httpsUrl, true, 301);
			exit;
		}
	}
}

// Configure secure session cookie parameters (must be set before session_start())
// - secure: only send cookie over HTTPS
// - httponly: prevent access from JavaScript
// - samesite: mitigate CSRF by restricting cross-site sending
// Use array form (PHP >= 7.3) if available, else fallback to legacy signature
if (PHP_VERSION_ID >= 70300) {
	session_set_cookie_params([
		'lifetime' => 0,
		'path' => '/',
		'domain' => '',
		'secure' => IS_HTTPS,
		'httponly' => true,
		'samesite' => 'Strict'
	]);
} else {
	// Legacy fallback (no native samesite support) — set secure/httponly via ini
	ini_set('session.cookie_secure', IS_HTTPS ? '1' : '0');
	ini_set('session.cookie_httponly', '1');
	session_set_cookie_params(0, '/', '', IS_HTTPS, true);
}

// Send HSTS header when served over HTTPS to instruct browsers to always use HTTPS
if (IS_HTTPS) {
	header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
}

// Load configuration
require_once INCLUDES_PATH . '/config.php';
