<?php
/**
 * In-memory cache helper
 * Uses APCu if available for cross-request in-memory caching (recommended).
 * If APCu is not available, falls back to a per-request in-memory cache (no disk writes,
 * but not persistent across requests).
 *
 * Functions (preserve old names used by the app):
 * - file_cache_get(string $key, int $ttl_seconds): mixed|null
 * - file_cache_set(string $key, mixed $data, int $ttl_seconds = 21600): bool
 *
 * Note: This intentionally avoids filesystem writes. For persistent in-memory caching
 * across requests, enable the APCu extension (php-apcu) or use Redis/Memcached and
 * adapt these helpers accordingly.
 */

if (!defined('BASE_PATH')) {
    // bootstrap should define BASE_PATH; fail gracefully
    throw new RuntimeException('BASE_PATH is not defined. Ensure includes/bootstrap.php is loaded.');
}

// Detect APCu availability (robust check)
$apcu_available = false;
if (function_exists('apcu_enabled')) {
    $apcu_available = apcu_enabled();
} elseif (function_exists('apcu_fetch') && ini_get('apc.enabled') !== '0') {
    $apcu_available = true;
}

// Prefer APCu if available. Otherwise prefer a persistent file cache in BASE_PATH /cache when writable,
// falling back to per-request local memory only if necessary.
$cache_backend = 'local';
if ($apcu_available) {
    $cache_backend = 'apcu';
} else {
    // Try to set up a disk cache directory
    $disk_dir = BASE_PATH . '/cache';
    if (is_dir($disk_dir) || @mkdir($disk_dir, 0755, true)) {
        // writable?
        if (is_writable($disk_dir)) {
            $cache_backend = 'file';
        }
    }
}

define('CACHE_BACKEND', $cache_backend);
if (CACHE_BACKEND === 'apcu') {
    // nothing
} elseif (CACHE_BACKEND === 'file') {
    error_log('cache: APCu not available, using file-backed cache at ' . BASE_PATH . '/cache');
} else {
    error_log('cache: APCu not available and file cache not writable, using per-request in-memory fallback (not persistent across requests)');
}

/**
 * Internal: shared per-request local cache (returned by reference).
 * This ensures setter/getter share the same array without globals.
 */
function &file_local_cache() {
    static $local_cache = [];
    return $local_cache;
}

/**
 * File backend helpers
 */
function cache_file_path(string $key): string {
    $hash = hash('sha256', $key);
    return BASE_PATH . '/cache/fc_' . $hash . '.cache';
}

function cache_file_write(string $path, array $data): bool {
    $tmp = $path . '.' . uniqid('tmp', true);
    $encoded = serialize($data);
    $fp = @fopen($tmp, 'wb');
    if (!$fp) return false;
    $ok = false;
    if (flock($fp, LOCK_EX)) {
        $written = fwrite($fp, $encoded);
        fflush($fp);
        flock($fp, LOCK_UN);
        $ok = $written !== false;
    }
    fclose($fp);
    if ($ok) {
        @rename($tmp, $path);
    } else {
        @unlink($tmp);
    }
    return $ok;
}

function cache_file_read(string $path) {
    if (!is_file($path)) return null;
    $fp = @fopen($path, 'rb');
    if (!$fp) return null;
    $data = null;
    if (flock($fp, LOCK_SH)) {
        $contents = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        if ($contents !== false) {
            $data = @unserialize($contents);
        }
    }
    fclose($fp);
    return $data;
}

/**
 * Normalize/validate cache key. Replaces unsupported characters with underscores.
 */
function sanitize_cache_key(string $key): string {
    $key = trim($key);
    if ($key === '') {
        throw new InvalidArgumentException('Cache key must be a non-empty string');
    }
    return preg_replace('/[^A-Za-z0-9_:-]/', '_', $key);
}

/**
 * Internal: store wrapper structure used to keep metadata with cached values.
 */
function _file_cache_wrap_value($data, int $ttl_seconds): array {
    $now = time();
    return [
        '__fc_wrapped' => true,
        'created' => $now,
        'expires_at' => $now + $ttl_seconds,
        'value' => $data
    ];
}

/**
 * Get cache value by key. Returns null if not found or expired.
 * $ttl_seconds applies only to the local fallback (APCu enforces its own expiry).
 * This unwraps values stored by our `file_cache_set` wrapper.
 */
function file_cache_get(string $key, ?int $ttl_seconds = null) {
    $key = sanitize_cache_key($key);

    if (CACHE_BACKEND === 'apcu') {
        $val = apcu_fetch($key, $success);
        if ($success === false) {
            return null;
        }
        // If we stored a wrapper (for metadata), unwrap transparently
        if (is_array($val) && isset($val['__fc_wrapped']) && isset($val['value'])) {
            return $val['value'];
        }
        return $val;
    }

    if (CACHE_BACKEND === 'file') {
        $path = cache_file_path($key);
        $entry = cache_file_read($path);
        if (!is_array($entry) || !isset($entry['__fc_wrapped'])) {
            return null;
        }
        $expires_at = $entry['expires_at'] ?? ($entry['created'] + ($ttl_seconds ?? 21600));
        if ($expires_at < time()) {
            @unlink($path);
            return null;
        }
        return $entry['value'] ?? null;
    }

    // Local per-request fallback (shared storage)
    $local_cache = &file_local_cache();
    if (!isset($local_cache[$key])) {
        return null;
    }
    $entry = $local_cache[$key];
    $expires_at = $entry['expires_at'] ?? ($entry['created'] + ($ttl_seconds ?? 21600));
    if ($expires_at < time()) {
        unset($local_cache[$key]);
        return null;
    }
    return $entry['value'] ?? $entry['data'] ?? null;
}

/**
 * Set cache value for key. Returns true on success.
 * Stores a wrapper containing metadata so that callers can learn when it was cached.
 */
function file_cache_set(string $key, $data, int $ttl_seconds = 21600): bool {
    $key = sanitize_cache_key($key);

    $wrapped = _file_cache_wrap_value($data, $ttl_seconds);

    if (CACHE_BACKEND === 'apcu') {
        // APCu supports TTL on store; still store wrapper as the value for metadata
        return apcu_store($key, $wrapped, $ttl_seconds);
    }

    if (CACHE_BACKEND === 'file') {
        $path = cache_file_path($key);
        return cache_file_write($path, $wrapped);
    }

    // Local per-request fallback
    $local_cache = &file_local_cache();
    $local_cache[$key] = $wrapped;
    return true;
}

/**
 * Get cache value along with metadata: ['data' => mixed|null, 'meta' => ['cached' => bool, 'created' => int|null, 'expires_at' => int|null, 'backend' => string]]
 */
function file_cache_get_with_meta(string $key, ?int $ttl_seconds = null): array {
    $key = sanitize_cache_key($key);

    if (CACHE_BACKEND === 'apcu') {
        $val = apcu_fetch($key, $success);
        if ($success === false) {
            return ['data' => null, 'meta' => ['cached' => false, 'created' => null, 'expires_at' => null, 'backend' => 'apcu']];
        }
        if (is_array($val) && isset($val['__fc_wrapped'])) {
            return ['data' => $val['value'], 'meta' => ['cached' => true, 'created' => $val['created'] ?? null, 'expires_at' => $val['expires_at'] ?? null, 'backend' => 'apcu']];
        }
        // Unwrapped value: we don't know created time
        return ['data' => $val, 'meta' => ['cached' => true, 'created' => null, 'expires_at' => null, 'backend' => 'apcu']];
    }

    if (CACHE_BACKEND === 'file') {
        $path = cache_file_path($key);
        $entry = cache_file_read($path);
        if (!is_array($entry) || !isset($entry['__fc_wrapped'])) {
            return ['data' => null, 'meta' => ['cached' => false, 'created' => null, 'expires_at' => null, 'backend' => 'file']];
        }
        $expires_at = $entry['expires_at'] ?? ($entry['created'] + ($ttl_seconds ?? 21600));
        if ($expires_at < time()) {
            @unlink($path);
            return ['data' => null, 'meta' => ['cached' => false, 'created' => null, 'expires_at' => null, 'backend' => 'file']];
        }
        return ['data' => $entry['value'] ?? null, 'meta' => ['cached' => true, 'created' => $entry['created'] ?? null, 'expires_at' => $expires_at, 'backend' => 'file']];
    }

    $local_cache = &file_local_cache();
    if (!isset($local_cache[$key])) {
        return ['data' => null, 'meta' => ['cached' => false, 'created' => null, 'expires_at' => null, 'backend' => 'local']];
    }
    $entry = $local_cache[$key];

    $expires_at = $entry['expires_at'] ?? ($entry['created'] + ($ttl_seconds ?? 21600));
    if ($expires_at < time()) {
        unset($local_cache[$key]);
        return ['data' => null, 'meta' => ['cached' => false, 'created' => null, 'expires_at' => null, 'backend' => 'local']];
    }

    $data = $entry['value'] ?? $entry['data'] ?? null;
    return ['data' => $data, 'meta' => ['cached' => true, 'created' => $entry['created'] ?? null, 'expires_at' => $expires_at, 'backend' => 'local']];
}

/**
 * Delete a cache key. Returns true if the key existed and was removed (or deletion succeeded for APCu).
 */
function file_cache_delete(string $key): bool {
    $key = sanitize_cache_key($key);

    if (CACHE_BACKEND === 'apcu') {
        return apcu_delete($key);
    }

    if (CACHE_BACKEND === 'file') {
        $path = cache_file_path($key);
        if (is_file($path)) {
            return @unlink($path);
        }
        return false;
    }

    $local_cache = &file_local_cache();
    if (isset($local_cache[$key])) {
        unset($local_cache[$key]);
        return true;
    }
    return false;
}

/**
 * Clear all cache entries. For APCu this clears the user cache; for file backend it removes cache files;
 * for local fallback it resets the array.
 */
function file_cache_clear(): void {
    if (CACHE_BACKEND === 'apcu') {
        apcu_clear_cache();
        return;
    }

    if (CACHE_BACKEND === 'file') {
        $dir = BASE_PATH . '/cache';
        if (is_dir($dir)) {
            $files = glob($dir . '/fc_*.cache');
            if (is_array($files)) {
                foreach ($files as $f) {
                    @unlink($f);
                }
            }
        }
        return;
    }

    $local_cache = &file_local_cache();
    $local_cache = [];
}
