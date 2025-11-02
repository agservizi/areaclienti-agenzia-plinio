-- Migrazione iniziale per database vuoto
-- Assicurarsi di aver selezionato il database corretto prima dell'esecuzione (es. USE u427445037_portal;)

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin','client') NOT NULL DEFAULT 'client',
    username VARCHAR(60) NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(40) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_requests_service (service_id),
    INDEX idx_requests_status (status),
    INDEX idx_requests_created_at (created_at),
    CONSTRAINT fk_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_requests_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO services (slug, title, description, category, enabled) VALUES
    ('connettivita-fibra', 'Connettivita Fibra', 'Attivazione connettivita FTTH per aziende e privati.', 'Connettivita', 1),
    ('telefonia-mobile', 'Telefonia Mobile Business', 'Soluzioni voce e dati per team distribuiti.', 'Telefonia', 1),
    ('security-audit', 'Security Audit', 'Valutazione completa della postura di sicurezza ICT.', 'Consulenza', 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    category = VALUES(category),
    enabled = VALUES(enabled),
    updated_at = CURRENT_TIMESTAMP;
