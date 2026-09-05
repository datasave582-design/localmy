CREATE TABLE IF NOT EXISTS users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 firebase_uid VARCHAR(128) NOT NULL UNIQUE,
 name VARCHAR(150) NOT NULL DEFAULT 'MyLocal User',
 email VARCHAR(190) NOT NULL DEFAULT '',
 phone VARCHAR(40) NOT NULL DEFAULT '',
 photo TEXT NULL,
 role ENUM('user','admin') NOT NULL DEFAULT 'user',
 status ENUM('active','blocked') NOT NULL DEFAULT 'active',
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL,
 INDEX idx_role(role), INDEX idx_email(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_data (
 path VARCHAR(500) PRIMARY KEY,
 value_json LONGTEXT NOT NULL,
 updated_at DATETIME NOT NULL,
 INDEX idx_updated(updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
