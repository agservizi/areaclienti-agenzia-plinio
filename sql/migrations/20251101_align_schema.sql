-- Migrazione schema per allineare il database esistente al portale AG Servizi
-- Eseguire dopo aver effettuato un backup completo del database.
-- Testare prima su un ambiente di staging.

-- === TABELLA users ===
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin','client') NOT NULL DEFAULT 'client',
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(40) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS role ENUM('admin','client') NOT NULL DEFAULT 'client',
    ADD COLUMN IF NOT EXISTS name VARCHAR(120) NOT NULL,
    ADD COLUMN IF NOT EXISTS email VARCHAR(190) NOT NULL,
    ADD COLUMN IF NOT EXISTS password VARCHAR(255) NOT NULL,
    ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD UNIQUE INDEX IF NOT EXISTS idx_users_email (email);

ALTER TABLE users
    MODIFY COLUMN role ENUM('admin','client') NOT NULL DEFAULT 'client',
    MODIFY COLUMN name VARCHAR(120) NOT NULL,
    MODIFY COLUMN email VARCHAR(190) NOT NULL,
    MODIFY COLUMN password VARCHAR(255) NOT NULL,
    MODIFY COLUMN phone VARCHAR(40) NULL;

-- === TABELLA services ===
CREATE TABLE IF NOT EXISTS services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    category VARCHAR(120) NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE services
    ADD COLUMN IF NOT EXISTS slug VARCHAR(150) NOT NULL,
    ADD COLUMN IF NOT EXISTS title VARCHAR(150) NOT NULL,
    ADD COLUMN IF NOT EXISTS description TEXT NULL,
    ADD COLUMN IF NOT EXISTS category VARCHAR(120) NOT NULL,
    ADD COLUMN IF NOT EXISTS enabled TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD UNIQUE INDEX IF NOT EXISTS idx_services_slug (slug),
    ADD INDEX IF NOT EXISTS idx_services_enabled (enabled);

ALTER TABLE services
    MODIFY COLUMN slug VARCHAR(150) NOT NULL,
    MODIFY COLUMN title VARCHAR(150) NOT NULL,
    MODIFY COLUMN category VARCHAR(120) NOT NULL,
    MODIFY COLUMN enabled TINYINT(1) NOT NULL DEFAULT 1;

-- === TABELLA requests ===
CREATE TABLE IF NOT EXISTS requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    status ENUM('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
    data JSON NULL,
    attachments JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_requests_user (user_id),
    INDEX idx_requests_service (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE requests
    ADD COLUMN IF NOT EXISTS user_id INT UNSIGNED NOT NULL,
    ADD COLUMN IF NOT EXISTS service_id INT UNSIGNED NOT NULL,
    ADD COLUMN IF NOT EXISTS status ENUM('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS data JSON NULL,
    ADD COLUMN IF NOT EXISTS attachments JSON NULL,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD INDEX IF NOT EXISTS idx_requests_status (status),
    ADD INDEX IF NOT EXISTS idx_requests_created_at (created_at);

ALTER TABLE requests
    MODIFY COLUMN user_id INT UNSIGNED NOT NULL,
    MODIFY COLUMN service_id INT UNSIGNED NOT NULL,
    MODIFY COLUMN status ENUM('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
    ALGORITHM=INPLACE, LOCK=NONE;

-- === VINCOLI ===
SET @fk_user_count := (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'requests'
            AND COLUMN_NAME = 'user_id'
            AND REFERENCED_TABLE_NAME = 'users'
);

SET @sql_fk_user := IF(@fk_user_count = 0,
        'ALTER TABLE requests ADD CONSTRAINT fk_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE',
        'SELECT 1');
PREPARE stmt FROM @sql_fk_user;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_service_count := (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'requests'
            AND COLUMN_NAME = 'service_id'
            AND REFERENCED_TABLE_NAME = 'services'
);

SET @sql_fk_service := IF(@fk_service_count = 0,
        'ALTER TABLE requests ADD CONSTRAINT fk_requests_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE',
        'SELECT 1');
PREPARE stmt FROM @sql_fk_service;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Popolamento dati servizi (idempotente)
INSERT INTO services (slug, title, description, category, enabled)
VALUES
    ('connettivita-fibra', 'Connettivita Fibra', 'Attivazione connettivita FTTH per aziende e privati.', 'Connettivita', 1),
    ('telefonia-mobile', 'Telefonia Mobile Business', 'Soluzioni voce e dati per team distribuiti.', 'Telefonia', 1),
    ('security-audit', 'Security Audit', 'Valutazione completa della postura di sicurezza ICT.', 'Consulenza', 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    category = VALUES(category),
    enabled = VALUES(enabled),
    updated_at = CURRENT_TIMESTAMP;
