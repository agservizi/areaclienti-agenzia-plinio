# Guida all'Installazione - Area Clienti Agenzia Plinio

## 📋 Requisiti di Sistema

### Server Requirements
- **PHP**: 7.4 o superiore (consigliato 8.0+)
- **MySQL**: 5.7 o superiore (consigliato 8.0+)
- **Web Server**: Apache 2.4+ o Nginx 1.18+
- **Memoria**: Minimo 256MB RAM
- **Spazio Disco**: Minimo 500MB

### Estensioni PHP Richieste
```
- mysqli o PDO
- json
- curl
- mbstring
- openssl
- session
- filter
- hash
```

### Estensioni PHP Opzionali
```
- gd (per manipolazione immagini)
- zip (per backup automatici)
- mail (per invio email)
```

## 🔧 Installazione Passo per Passo

### 1. Preparazione del Server

#### Su Apache
```bash
# Verifica che mod_rewrite sia abilitato
sudo a2enmod rewrite
sudo systemctl restart apache2

# Verifica configurazione PHP
php -v
php -m | grep -E 'mysqli|pdo|json|curl'
```

#### Su Nginx
```nginx
# Aggiungi al tuo virtual host
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 2. Configurazione Database

#### Creazione Database
```sql
-- Connettiti a MySQL come root
mysql -u root -p

-- Crea il database
CREATE DATABASE agenzia_plinio_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crea utente dedicato (consigliato)
CREATE USER 'agenzia_user'@'localhost' IDENTIFIED BY 'password_sicura_qui';
GRANT ALL PRIVILEGES ON agenzia_plinio_clienti.* TO 'agenzia_user'@'localhost';
FLUSH PRIVILEGES;

-- Importa lo schema del database
USE agenzia_plinio_clienti;
SOURCE /path/to/config/database.sql;
```

#### Verifica Installazione Database
```sql
-- Verifica che le tabelle siano state create
SHOW TABLES;

-- Verifica utente admin predefinito
SELECT username, email, user_type FROM users WHERE user_type = 'admin';
```

### 3. Configurazione File

#### Copia i File
```bash
# Copia tutti i file nella directory web
cp -r area-clienti/ /var/www/html/

# Imposta i permessi corretti
sudo chown -R www-data:www-data /var/www/html/area-clienti/
sudo chmod -R 755 /var/www/html/area-clienti/
sudo chmod -R 777 /var/www/html/area-clienti/uploads/ # Se presente
```

#### Configura config.php
```php
<?php
// Modifica config/config.php

// URL del sito (IMPORTANTE: cambiare)
define('SITE_URL', 'https://tuodominio.com/area-clienti');
define('ADMIN_EMAIL', 'admin@agenziaplinio.it');

// Chiave di crittografia (IMPORTANTE: generare una nuova)
define('ENCRYPTION_KEY', 'genera_chiave_sicura_32_caratteri');

// Configurazioni email SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'email@agenziaplinio.it');
define('SMTP_PASSWORD', 'password_app_gmail');

// Debug (SOLO in sviluppo)
define('DEBUG_MODE', false); // Impostare a false in produzione
?>
```

#### Configura database.php
```php
<?php
// Modifica config/database.php

class Database {
    private $host = 'localhost';
    private $database = 'agenzia_plinio_clienti';
    private $username = 'agenzia_user';    // Utente creato sopra
    private $password = 'password_sicura_qui';  // Password dell'utente
    private $charset = 'utf8mb4';
    // ... resto del codice
}
?>
```

### 4. Configurazione Web Server

#### Virtual Host Apache
```apache
<VirtualHost *:80>
    ServerName agenziaplinio.local
    DocumentRoot /var/www/html/area-clienti
    
    <Directory /var/www/html/area-clienti>
        AllowOverride All
        Require all granted
        
        # Sicurezza aggiuntiva
        <Files "*.php">
            Require all granted
        </Files>
        
        <FilesMatch "^(config|includes)/">
            Require all denied
        </FilesMatch>
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/agenzia_error.log
    CustomLog ${APACHE_LOG_DIR}/agenzia_access.log combined
</VirtualHost>

# Per HTTPS (consigliato)
<VirtualHost *:443>
    ServerName agenziaplinio.com
    DocumentRoot /var/www/html/area-clienti
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # Stesso contenuto del virtual host HTTP
</VirtualHost>
```

#### Configurazione Nginx
```nginx
server {
    listen 80;
    server_name agenziaplinio.com;
    root /var/www/html/area-clienti;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Gestione file PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Protezione file sensibili
    location ~ ^/(config|includes)/ {
        deny all;
        return 404;
    }
    
    # File statici
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Pretty URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 5. Configurazione SSL (Consigliato)

#### Con Let's Encrypt
```bash
# Installa Certbot
sudo apt install certbot python3-certbot-apache

# Ottieni certificato SSL
sudo certbot --apache -d agenziaplinio.com -d www.agenziaplinio.com

# Rinnovo automatico
sudo crontab -e
# Aggiungi: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 6. Test dell'Installazione

#### Verifica Funzionamento Base
1. Visita `https://tuodominio.com/area-clienti`
2. Dovresti vedere la pagina di login
3. Testa il login con le credenziali admin:
   - Username: `admin`
   - Password: `password` (da cambiare immediatamente)

#### Test di Sicurezza
```bash
# Verifica che i file di configurazione non siano accessibili
curl -I https://tuodominio.com/area-clienti/config/config.php
# Dovrebbe restituire 403 o 404

# Verifica headers di sicurezza
curl -I https://tuodominio.com/area-clienti/
# Verifica presenza di X-Frame-Options, X-Content-Type-Options, etc.
```

### 7. Configurazioni Post-Installazione

#### Cambia Password Admin
1. Accedi come admin
2. Vai su "Il Mio Profilo"
3. Cambia immediatamente la password predefinita

#### Configura Email
```php
// Test invio email
// Crea un file test-email.php temporaneo
<?php
require_once 'config/config.php';

$to = 'test@example.com';
$subject = 'Test Email Agenzia Plinio';
$message = 'Test di configurazione email';
$headers = 'From: ' . ADMIN_EMAIL;

if (mail($to, $subject, $message, $headers)) {
    echo 'Email inviata con successo';
} else {
    echo 'Errore nell\'invio email';
}
?>
```

#### Configura Backup Automatico
```bash
#!/bin/bash
# backup-script.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/agenzia-plinio"
DB_NAME="agenzia_plinio_clienti"
DB_USER="agenzia_user"
DB_PASS="password_sicura_qui"

# Crea directory backup
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Backup file
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/html/area-clienti

# Rimuovi backup vecchi (più di 30 giorni)
find $BACKUP_DIR -type f -mtime +30 -delete

# Aggiungi a crontab per esecuzione giornaliera
# 0 2 * * * /path/to/backup-script.sh
```

### 8. Monitoraggio e Manutenzione

#### Log Files
```bash
# Controlla i log Apache
tail -f /var/log/apache2/agenzia_error.log

# Controlla i log dell'applicazione
tail -f /var/www/html/area-clienti/logs/app.log # Se implementato
```

#### Performance Monitoring
```bash
# Controlla spazio disco
df -h

# Controlla memoria
free -h

# Controlla processo MySQL
systemctl status mysql

# Controlla processo Apache/Nginx
systemctl status apache2
# o
systemctl status nginx
```

## 🚨 Checklist di Sicurezza

- [ ] Password admin cambiata
- [ ] Database utente dedicato creato
- [ ] File di configurazione protetti
- [ ] SSL/HTTPS configurato
- [ ] Headers di sicurezza impostati
- [ ] Debug mode disabilitato in produzione
- [ ] Backup automatico configurato
- [ ] Firewall configurato
- [ ] PHP e MySQL aggiornati
- [ ] Permessi file corretti

## 🆘 Risoluzione Problemi Comuni

### Errore "Permission Denied"
```bash
sudo chown -R www-data:www-data /var/www/html/area-clienti/
sudo chmod -R 755 /var/www/html/area-clienti/
```

### Errore Database Connection
1. Verifica credenziali in `config/database.php`
2. Controlla che MySQL sia in esecuzione: `systemctl status mysql`
3. Testa connessione: `mysql -u agenzia_user -p agenzia_plinio_clienti`

### Errore 500 Internal Server Error
1. Controlla log Apache: `tail -f /var/log/apache2/error.log`
2. Verifica sintassi PHP: `php -l /var/www/html/area-clienti/index.php`
3. Controlla permessi file

### URL Rewriting non funziona
1. Verifica mod_rewrite abilitato: `apache2ctl -M | grep rewrite`
2. Controlla `.htaccess` presente e leggibile
3. Verifica `AllowOverride All` nel virtual host

## 📞 Supporto Tecnico

Per assistenza tecnica:
- **Email**: info@agenziaplinio.it
- **Telefono**: +39 081 0584542
- **Orari**: Lun-Ven 9:00-13:20, 16:00-19:20

---

**Ultima revisione**: Novembre 2025