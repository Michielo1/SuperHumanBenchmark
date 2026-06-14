-- Database Schema
-- This is used in database setup

-- Table account
-- id: INT, PRIMARY KEY, AUTO_INCREMENT
-- username: VARCHAR(50) UNIQUE NOT NULL
-- first_name: VARCHAR(50), NOT NULL
-- infix: VARCHAR(20), NULLABLE
-- last_name: VARCHAR(50), NOT NULL
-- email: VARCHAR(100), UNIQUE, NOT NULL
-- wachtwoord: VARCHAR(255), NOT NULL
-- created_at: TIMESTAMP, DEFAULT CURRENT_TIMESTAMP

CREATE TABLE IF NOT EXISTS account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    infix VARCHAR(20),
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    wachtwoord VARCHAR(255) NOT NULL,
    banner_image VARCHAR(255),
    profile_image VARCHAR(255),
    about_you TEXT,
    reset_token VARCHAR(255),
    reset_token_expires DATETIME,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table test
-- id: INT, PRIMARY KEY, AUTO_INCREMENT
-- test_name: VARCHAR(100), NOT NULL
-- description: TEXT, NULLABLE
-- test_type: VARCHAR(50), NOT NULL ; is the goal to minimize a value or maximize it?
-- created_at: TIMESTAMP, DEFAULT CURRENT_TIMESTAMP

CREATE TABLE IF NOT EXISTS test (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(100) NOT NULL,
    test_description TEXT,
    test_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test data
INSERT INTO test (test_name, test_description, test_type) VALUES
('reaction_test', 'Test your reflexes with a reaction test!', 'minimize');

INSERT INTO test (test_name, test_description, test_type) VALUES
('type_test', 'Test how quick you can type and adjust to changes!', 'maximize');

INSERT INTO test (test_name, test_description, test_type) VALUES
('aim_test', 'Do you always hit your targets?', 'minimize');

-- Table testscore
-- id: INT, PRIMARY KEY, AUTO_INCREMENT
-- account_id: INT, FOREIGN KEY → Account.id, NOT NULL
-- test_id: INT, FOREIGN KEY → Test.id, NOT NULL
-- last_score: DECIMAL(10,2), NOT NULL
-- high_score: DECIMAL(10,2), NOT NULL
-- attempt_count: INT, DEFAULT 0
-- updated_at: TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- UNIQUE KEY (account_id, test_id)

CREATE TABLE IF NOT EXISTS testscore (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    test_id INT NOT NULL,
    last_score DECIMAL(10,2) NOT NULL,
    high_score DECIMAL(10,2) NOT NULL,
    attempt_count INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (account_id, test_id),
    FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES test(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table to store analytics events (event log). Only recorded when analytics consent is enabled
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NULL,
    session_id VARCHAR(128) NULL,
    event_type VARCHAR(50) NOT NULL,
    props JSON NULL,
    ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_analytics_events_account (account_id),
    INDEX idx_analytics_events_session (session_id),
    INDEX idx_analytics_events_type (event_type),
    INDEX idx_analytics_events_ts (ts),
    FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table testattempt
-- id: INT, PRIMARY KEY, AUTO_INCREMENT
-- testscore_id: INT, FOREIGN KEY → TestScore.id, NOT NULL
-- score: DECIMAL(10,2), NOT NULL
-- attempted_at: TIMESTAMP, DEFAULT CURRENT_TIMESTAMP
-- metadata: JSON, NULLABLE
-- analytics_event_id: INT, FOREIGN KEY → analytics_events.id, NULLABLE (paired event when consented)

CREATE TABLE IF NOT EXISTS testattempt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    testscore_id INT NOT NULL,
    score DECIMAL(10,2) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON,
    analytics_event_id INT NULL,
    INDEX idx_testattempt_analytics_event (analytics_event_id),
    FOREIGN KEY (testscore_id) REFERENCES testscore(id) ON DELETE CASCADE,
    FOREIGN KEY (analytics_event_id) REFERENCES analytics_events(id) ON DELETE SET NULL
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table consent
-- id: INT, PRIMARY KEY, AUTO_INCREMENT
-- account_id: INT, FOREIGN KEY → Account.id, UNIQUE, NULLABLE
-- session_id: VARCHAR(128), NULLABLE
-- analytics_allowed: BOOLEAN, DEFAULT FALSE (replaces cookies_toegestaan)
-- marketing_emails: BOOLEAN, DEFAULT FALSE
-- given_at: TIMESTAMP, DEFAULT CURRENT_TIMESTAMP
-- updated_at: TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

CREATE TABLE IF NOT EXISTS consent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NULL,
    session_id VARCHAR(128) NULL,
    analytics_allowed BOOLEAN DEFAULT FALSE,
    marketing_emails BOOLEAN DEFAULT FALSE,
    given_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_account (account_id),
    UNIQUE KEY uq_session (session_id),
    FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SAMPLE DATA FOR TESTING (UNCOMMENT TO USE)
-- ============================================================================

INSERT IGNORE INTO account (username, first_name, infix, last_name, email, wachtwoord) VALUES
('john1', 'John', NULL, 'Doe', 'john.doe@example.com', '$2y$10$YourHashedPasswordHere'),
('jane2', 'Jane', NULL, 'Smith', 'jane.smith@example.com', '$2y$10$YourHashedPasswordHere'),
('michael3', 'Michael', 'van', 'Berg', 'michael.vberg@example.com', '$2y$10$YourHashedPasswordHere'),
('sarah4', 'Sarah', NULL, 'Johnson', 'sarah.j@example.com', '$2y$10$YourHashedPasswordHere'),
('david5', 'David', 'de', 'Vries', 'david.devries@example.com', '$2y$10$YourHashedPasswordHere'),
('emma6', 'Emma', NULL, 'Williams', 'emma.w@example.com', '$2y$10$YourHashedPasswordHere'),
('robert7', 'Robert', NULL, 'Brown', 'robert.brown@example.com', '$2y$10$YourHashedPasswordHere'),
('lisa8', 'Lisa', 'van der', 'Meer', 'lisa.vandermeer@example.com', '$2y$10$YourHashedPasswordHere'),
('thomas9', 'Thomas', NULL, 'Anderson', 'thomas.a@example.com', '$2y$10$YourHashedPasswordHere'),
('anna10', 'Anna', NULL, 'Martinez', 'anna.m@example.com', '$2y$10$YourHashedPasswordHere'),
('james11', 'James', NULL, 'Taylor', 'james.taylor@example.com', '$2y$10$YourHashedPasswordHere'),
('sophie12', 'Sophie', NULL, 'Garcia', 'sophie.garcia@example.com', '$2y$10$YourHashedPasswordHere'),
('daniel13', 'Daniel', 'van', 'Dijk', 'daniel.vandijk@example.com', '$2y$10$YourHashedPasswordHere'),
('olivia14', 'Olivia', NULL, 'Lee', 'olivia.lee@example.com', '$2y$10$YourHashedPasswordHere'),
('ryan15', 'Ryan', NULL, 'White', 'ryan.white@example.com', '$2y$10$YourHashedPasswordHere');


-- Sample TestScores for Reaction Test (minimize - lower is better, in milliseconds)
-- Assuming test_id=1 is reaction_test

-- Sample TestScores for Reaction Test (minimize - lower is better, in milliseconds)
INSERT IGNORE INTO testscore (account_id, test_id, last_score, high_score, attempt_count, updated_at) VALUES
(1, 1, 712.50, 748.75, 25, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 1, 689.25, 724.25, 18, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 1, 741.00, 789.50, 31, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(4, 1, 703.75, 736.75, 12, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 1, 728.25, 772.00, 20, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(6, 1, 694.50, 739.50, 22, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(7, 1, 716.00, 755.00, 15, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(8, 1, 752.75, 804.25, 28, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(9, 1, 701.25, 742.25, 19, DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(10, 1, 768.50, 821.00, 35, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(11, 1, 709.75, 747.75, 17, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(12, 1, 744.25, 798.50, 24, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(13, 1, 696.00, 741.00, 21, DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(14, 1, 687.50, 732.50, 16, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(15, 1, 723.75, 769.25, 26, DATE_SUB(NOW(), INTERVAL 4 HOUR));

-- Sample TestScores for Type Test (maximize - higher is better, in WPM)
-- Assuming test_id=2 is type_test

-- Sample TestScores for Type Test (maximize - higher is better, in WPM)
INSERT IGNORE INTO testscore (account_id, test_id, last_score, high_score, attempt_count, updated_at) VALUES
(1, 2, 9.50, 11.25, 30, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 2, 12.75, 12.75, 22, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(3, 2, 7.25, 9.50, 28, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 2, 10.00, 12.75, 19, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 2, 8.50, 10.25, 24, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(6, 2, 11.25, 13.50, 26, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(7, 2, 9.75, 11.00, 21, DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(8, 2, 6.50, 8.75, 33, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(9, 2, 13.25, 14.50, 18, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(10, 2, 8.00, 9.25, 27, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(11, 2, 10.50, 12.75, 20, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(12, 2, 6.25, 8.50, 29, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(13, 2, 14.75, 15.00, 23, DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(14, 2, 11.50, 13.25, 17, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(15, 2, 9.00, 11.75, 25, DATE_SUB(NOW(), INTERVAL 4 HOUR));

-- Sample TestScores for Aim Test (maximize - higher is better, in points)
-- Assuming test_id=3 is aim_test

-- Sample TestScores for Aim Test (maximize - higher is better, in points)
INSERT IGNORE INTO testscore (account_id, test_id, last_score, high_score, attempt_count, updated_at) VALUES
(1, 3, 89.25, 94.875, 42, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 3, 91.50, 91.50, 35, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(3, 3, 87.32, 90.65, 48, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 3, 90.16, 93.12, 31, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 3, 88.41, 92.34, 39, DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(6, 3, 93.89, 93.89, 29, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 3, 91.20, 94.750, 36, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(8, 3, 86.50, 90.89, 44, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(9, 3, 92.45, 96.58, 27, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(10, 3, 89.77, 92.11, 41, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(11, 3, 91.68, 95.34, 33, DATE_SUB(NOW(), INTERVAL 7 HOUR)),
(12, 3, 87.80, 90.42, 46, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(13, 3, 92.12, 95.67, 30, DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(14, 3, 94.77, 94.77, 25, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(15, 3, 90.34, 93.92, 38, DATE_SUB(NOW(), INTERVAL 9 HOUR));

