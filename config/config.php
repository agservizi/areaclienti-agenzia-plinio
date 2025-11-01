<?php
/**
 * Configurazione generale dell'applicazione
 */

// Configurazioni generali
define('SITE_URL', 'http://localhost/area-clienti');
define('SITE_NAME', 'Area Clienti - Agenzia Plinio');
define('ADMIN_EMAIL', 'admin@agenziaplinio.it');

// Configurazioni sicurezza
define('ENCRYPTION_KEY', 'your-secret-encryption-key-here'); // Cambiare in produzione
define('SESSION_TIMEOUT', 3600); // 1 ora
define('MAX_LOGIN_ATTEMPTS', 5);

// Configurazioni email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');

// Configurazioni upload file
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// Configurazioni servizi
define('BRT_API_KEY', 'your-brt-api-key');
define('POSTE_API_KEY', 'your-poste-api-key');
define('TNT_API_KEY', 'your-tnt-api-key');

// Configurazioni debug
define('DEBUG_MODE', true); // Cambiare a false in produzione
define('LOG_ERRORS', true);

// Timezone
date_default_timezone_set('Europe/Rome');

// Avvio sessione sicura
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}