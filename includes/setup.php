<?php
/**
 * Database Setup Script
 * Run this file once to create the database tables
 * Access via: php includes/setup.php (CLI) or through browser with protection
 */

// Prevent running if already set up (comment out to allow re-running)
$setupFlagFile = __DIR__ . '/.setup_complete';
if (file_exists($setupFlagFile)) {
    die("Setup already completed. Delete .setup_complete file to run again.\n");
}

// Load configuration
if (!file_exists(__DIR__ . '/config.php')) {
    die("Error: config.php not found. Copy config-example.php to config.php and configure it first.\n");
}

require_once __DIR__ . '/bootstrap.php';

// Path to schema file
$schemaFile = __DIR__ . '/../database/schema.sql';

if (!file_exists($schemaFile)) {
    die("Error: Schema file not found at $schemaFile\n");
}

echo "Starting database setup...\n\n";

try {
    // Connect to MySQL server (without selecting database)
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    echo "✓ Connected to MySQL server\n";

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
                CHARACTER SET " . DB_CHARSET . "
                COLLATE " . DB_CHARSET . "_unicode_ci");

    echo "✓ Database '" . DB_NAME . "' created or already exists\n";

    // Select the database
    $pdo->exec("USE `" . DB_NAME . "`");

    // Read the schema file
    $sql = file_get_contents($schemaFile);

    if ($sql === false) {
        throw new Exception("Failed to read schema file");
    }

    // Remove SQL comments (lines starting with --)
    $sqlLines = explode("\n", $sql);
    $sqlLines = array_filter($sqlLines, function($line) {
        return !preg_match('/^\s*--/', trim($line));
    });
    $sql = implode("\n", $sqlLines);

    // Split SQL statements (basic split by semicolon)
    $statements = explode(';', $sql);

    // Clean and filter statements
    $statements = array_values(array_filter(
        array_map('trim', $statements),
        function($stmt) {
            return !empty($stmt);
        }
    ));

    echo "✓ Schema file loaded (" . count($statements) . " statements found)\n\n";

    // Execute each statement
    foreach ($statements as $index => $statement) {
        try {
            echo "Executing statement " . ($index + 1) . "...\n";
            // Show a preview of the statement
            $preview = substr(preg_replace('/\s+/', ' ', $statement), 0, 60);
            echo "  → " . $preview . "...\n";

            $pdo->exec($statement);
            echo "✓ Success\n\n";
        } catch (PDOException $e) {
            die("\n✗ Failed on statement " . ($index + 1) . ": " . $e->getMessage() . "\n");
        }
    }

    echo "=================================\n";
    echo "Database setup completed successfully!\n";
    echo "=================================\n";

    // Create setup complete flag
    file_put_contents($setupFlagFile, date('Y-m-d H:i:s'));
    echo "\nSetup flag created. Delete .setup_complete to run setup again.\n";

} catch (PDOException $e) {
    die("\n✗ Database Error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("\n✗ Error: " . $e->getMessage() . "\n");
}
