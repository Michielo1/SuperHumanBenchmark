<?php
/**
 * Demo Auto-Reset Logic
 * Runs periodically to clear user data, leaderboards, and scores.
 * Preserves demo accounts, test definitions, and sample data.
 *
 * Called from bootstrap.php. Uses file-based timestamp tracking.
 */

if (!defined('DEMO_MODE') || !DEMO_MODE) {
    return;
}

/**
 * Get the last reset timestamp from file, or null if never reset.
 */
function getDemoLastReset(): ?int {
    if (!file_exists(DEMO_RESET_FILE)) {
        return null;
    }
    $content = file_get_contents(DEMO_RESET_FILE);
    return $content !== false ? (int)$content : null;
}

/**
 * Save the current timestamp as the last reset time.
 */
function saveDemoLastReset(): void {
    file_put_contents(DEMO_RESET_FILE, (string)time());
}

/**
 * Check if enough time has passed since last reset to trigger a new reset.
 */
function shouldDemoReset(): bool {
    $last = getDemoLastReset();
    if ($last === null) {
        return true;
    }
    return (time() - $last) >= (DEMO_RESET_MINUTES * 60);
}

/**
 * Perform the actual database reset.
 * Clears: account, testscore, testattempt, analytics_events, consent
 * Preserves: test (benchmark definitions)
 * Re-creates demo accounts.
 */
function demoResetDatabase(): bool {
    try {
        require_once INCLUDES_PATH . '/Database.php';
        $db = Database::getConnection();

        // Disable foreign key checks for clean truncation
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');

        // Truncate all user-facing tables
        $db->exec('TRUNCATE TABLE testattempt');
        $db->exec('TRUNCATE TABLE testscore');
        $db->exec('TRUNCATE TABLE analytics_events');
        $db->exec('TRUNCATE TABLE consent');
        $db->exec('TRUNCATE TABLE account');

        // Re-enable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');

        // Re-create demo accounts with hashed passwords
        require_once CLASSES_PATH . '/Account.php';

        // Demo user
        $user = new Account();
        $user->setUsername('demo_user');
        $user->setFirstName('Demo');
        $user->setLastName('User');
        $user->setEmail(DEMO_USER_EMAIL);
        $user->hashAndSetPassword(DEMO_USER_PASSWORD);
        $user->setIsAdmin(false);
        $user->save();

        // Demo admin
        $admin = new Account();
        $admin->setUsername('demo_admin');
        $admin->setFirstName('Demo');
        $admin->setLastName('Admin');
        $admin->setEmail(DEMO_ADMIN_EMAIL);
        $admin->hashAndSetPassword(DEMO_ADMIN_PASSWORD);
        $admin->setIsAdmin(true);
        $admin->save();

        saveDemoLastReset();
        return true;
    } catch (Exception $e) {
        error_log('Demo reset error: ' . $e->getMessage());
        return false;
    }
}

// Run reset check on every request (safe: only acts when interval elapsed)
if (shouldDemoReset()) {
    // Simple file lock to prevent concurrent resets
    $lockFile = DEMO_RESET_FILE . '.lock';
    $fp = fopen($lockFile, 'c');
    if ($fp && flock($fp, LOCK_EX | LOCK_NB)) {
        // Re-check after acquiring lock (another request may have reset already)
        if (shouldDemoReset()) {
            demoResetDatabase();
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    } elseif ($fp) {
        fclose($fp);
    }
}
