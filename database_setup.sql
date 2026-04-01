-- ============================================================
--  database_setup.sql  (final version — Task 4 with email)
--  Run this in phpMyAdmin (SQL tab)
-- ============================================================

CREATE DATABASE IF NOT EXISTS users_db;
USE users_db;

-- Users table with email column added
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(100) NOT NULL UNIQUE,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- If you already have a users table, run these to add missing columns:
-- ALTER TABLE users ADD COLUMN email VARCHAR(150) NOT NULL DEFAULT '' AFTER username;
-- ALTER TABLE users ADD COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user' AFTER password;

-- Password resets table
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    token      VARCHAR(64)  NOT NULL UNIQUE,
    expires_at DATETIME     NOT NULL,
    used       TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed admin account (password = "password" — change in production)
INSERT IGNORE INTO users (username, email, password, role)
VALUES (
    'admin_user',
    'martin.frederico29@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);

DESCRIBE users;
DESCRIBE password_resets;