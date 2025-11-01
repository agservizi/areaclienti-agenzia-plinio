-- Database per Area Clienti Agenzia Plinio
-- Creazione database e tabelle

CREATE DATABASE IF NOT EXISTS agenzia_plinio_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agenzia_plinio_clienti;

-- Tabella utenti (clienti e admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(10),
    fiscal_code VARCHAR(16),
    user_type ENUM('client', 'admin') DEFAULT 'client',
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL
);

-- Tabella sessioni di login
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabella richieste servizi generiche
CREATE TABLE service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_type ENUM('spedizioni', 'attivazioni_digitali', 
                     'caf_patronato', 'visure', 'telefonia_utenze', 'servizi_postali', 
                     'ritiro_pacchi') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    estimated_cost DECIMAL(10,2),
    actual_cost DECIMAL(10,2),
    notes TEXT,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    assigned_to INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabella spedizioni
CREATE TABLE shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    carrier ENUM('brt', 'poste', 'tnt', 'fedex') NOT NULL,
    tracking_number VARCHAR(100),
    sender_name VARCHAR(100) NOT NULL,
    sender_address TEXT NOT NULL,
    sender_city VARCHAR(50) NOT NULL,
    sender_postal_code VARCHAR(10) NOT NULL,
    sender_phone VARCHAR(20),
    recipient_name VARCHAR(100) NOT NULL,
    recipient_address TEXT NOT NULL,
    recipient_city VARCHAR(50) NOT NULL,
    recipient_postal_code VARCHAR(10) NOT NULL,
    recipient_phone VARCHAR(20),
    package_weight DECIMAL(5,2),
    package_dimensions VARCHAR(50),
    package_content TEXT,
    declared_value DECIMAL(10,2),
    insurance_requested BOOLEAN DEFAULT FALSE,
    delivery_type ENUM('standard', 'express', 'same_day') DEFAULT 'standard',
    special_instructions TEXT,
    shipping_cost DECIMAL(10,2),
    pickup_date DATE,
    estimated_delivery DATE,
    actual_delivery TIMESTAMP NULL,
    status ENUM('pending', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE
);

-- Tabella attivazioni digitali
CREATE TABLE digital_activations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    service_type ENUM('spid', 'pec', 'firma_digitale') NOT NULL,
    provider VARCHAR(50) DEFAULT 'namirial',
    identity_document_type ENUM('carta_identita', 'patente', 'passaporto') NOT NULL,
    identity_document_number VARCHAR(50) NOT NULL,
    fiscal_code VARCHAR(16) NOT NULL,
    activation_code VARCHAR(100),
    status ENUM('pending', 'documents_verification', 'activated', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT,
    activation_date TIMESTAMP NULL,
    expiry_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE
);

-- Tabella file allegati
CREATE TABLE attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_by INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabella notifiche
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabella log attività
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50),
    resource_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Inserimento utente admin di default
INSERT INTO users (username, email, password_hash, first_name, last_name, user_type, is_active, email_verified) 
VALUES ('admin', 'admin@agenziaplinio.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Agenzia Plinio', 'admin', TRUE, TRUE);

-- Indici per performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_service_requests_user_id ON service_requests(user_id);
CREATE INDEX idx_service_requests_status ON service_requests(status);
CREATE INDEX idx_service_requests_service_type ON service_requests(service_type);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);