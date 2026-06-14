-- Demo Setup SQL
-- Run this once to initialize the demo database with demo accounts.
-- The demo auto-reset will also re-create these accounts after each reset.

-- Demo user account (password: user)
INSERT INTO account (username, first_name, last_name, email, wachtwoord, is_admin)
VALUES ('demo_user', 'Demo', 'User', 'user@demo.com', '$2y$10$YOeQuW9xuoJNwHVvDcjF1eYdYRPFbdZJ1FPe0g5GnFh2wpIBAZSwu', FALSE)
ON DUPLICATE KEY UPDATE username = username;

-- Demo admin account (password: admin)
INSERT INTO account (username, first_name, last_name, email, wachtwoord, is_admin)
VALUES ('demo_admin', 'Demo', 'Admin', 'admin@demo.com', '$2y$10$uAA6mm/oFFZp8KYIkIAEJuGqkGf71AzM3lw4OK/6vlR06zIJfnmFK', TRUE)
ON DUPLICATE KEY UPDATE username = username;
