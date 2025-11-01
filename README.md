# Area Clienti - Agenzia Plinio

Sistema completo di gestione dell'area clienti per l'Agenzia Plinio, sviluppato in PHP nativo, JavaScript vanilla e CSS avanzato.

## 🚀 Caratteristiche Principali

- **Autenticazione sicura** con gestione sessioni e protezioni contro attacchi
- **Dashboard interattiva** con statistiche in tempo reale
- **Gestione servizi completa** per tutti i servizi offerti dall'agenzia
- **Design responsive** ottimizzato per tutti i dispositivi
- **Interfaccia moderna** con animazioni e transizioni fluide
- **Sistema di notifiche** per aggiornamenti in tempo reale

## 📋 Servizi Disponibili

### Servizi Principali
- **Spedizioni** - Gestione spedizioni nazionali e internazionali (BRT, Poste, TNT/FedEx)
- **Pagamenti** - Bollettini, F24, PagoPA, MAV/RAV, Bonifici
- **Biglietteria** - Biglietti treno Trenitalia e Italo
- **Attivazioni Digitali** - SPID, PEC, Firma Digitale (Namirial)

### Servizi Secondari
- **CAF e Patronato** - Pratiche fiscali e assistenza previdenziale
- **Visure** - Visure CRIF, catastali, camerali, protestati
- **Telefonia e Utenze** - Contratti luce, gas, telefonia
- **Servizi Postali** - Invio email e PEC
- **Ritiro Pacchi** - Gestione punti di ritiro

## 🛠️ Tecnologie Utilizzate

- **Backend**: PHP 7.4+ nativo
- **Frontend**: JavaScript ES6+ vanilla, CSS3 avanzato
- **Database**: MySQL 5.7+
- **Icons**: Font Awesome 6
- **Fonts**: Inter (Google Fonts)

## 📁 Struttura del Progetto

```
area-clienti/
├── api/                    # Endpoint API REST
│   └── auth/              # API di autenticazione
├── assets/                # Risorse statiche
│   ├── css/              # Fogli di stile
│   ├── js/               # Script JavaScript
│   └── images/           # Immagini e loghi
├── config/               # Configurazioni
│   ├── config.php        # Configurazione generale
│   ├── database.php      # Configurazione database
│   └── database.sql      # Script creazione database
├── includes/             # File PHP includibili
│   ├── auth.php          # Classe autenticazione
│   ├── header.php        # Header template
│   ├── footer.php        # Footer template
│   └── sidebar.php       # Sidebar template
├── pages/                # Pagine dell'area clienti
├── admin/                # Area amministrativa
├── services/             # Classi dei servizi
└── index.php            # Pagina di login
```

## 🚀 Installazione

### Prerequisiti
- PHP 7.4 o superiore
- MySQL 5.7 o superiore
- Web server (Apache/Nginx)
- Composer (opzionale)

### Passaggi di Installazione

1. **Clona il repository**
```bash
git clone [repository-url]
cd area-clienti
```

2. **Configura il database**
```sql
-- Importa il file SQL
mysql -u username -p database_name < config/database.sql
```

3. **Configura l'applicazione**
```php
// Modifica config/config.php
define('SITE_URL', 'http://tuo-dominio.com/area-clienti');
define('ADMIN_EMAIL', 'admin@agenziaplinio.it');

// Modifica config/database.php
private $host = 'localhost';
private $database = 'agenzia_plinio_clienti';
private $username = 'tuo_username';
private $password = 'tua_password';
```

4. **Imposta i permessi**
```bash
chmod 755 -R area-clienti/
chmod 777 -R area-clienti/uploads/ # Se presente
```

5. **Configura il web server**
```apache
# .htaccess per Apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## 👤 Account Predefiniti

### Administrator
- **Username**: `admin`
- **Email**: `admin@agenziaplinio.it`
- **Password**: `password` (da cambiare immediatamente)

## 🔐 Sicurezza

- Hashing delle password con `password_hash()`
- Protezione CSRF con token
- Validazione e sanitizzazione input
- Sessioni sicure con timeout
- Protezione contro SQL injection
- Limitazione tentativi di login

## 📱 Responsive Design

L'interfaccia è completamente responsive e ottimizzata per:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (fino a 767px)

## 🎨 Personalizzazione CSS

Il sistema utilizza variabili CSS per facilitare la personalizzazione:

```css
:root {
    --primary-color: #2c5aa0;
    --secondary-color: #f39c12;
    --success-color: #27ae60;
    --danger-color: #e74c3c;
    /* ... altre variabili */
}
```

## 📊 API Documentation

### Autenticazione
- `POST /api/auth/login.php` - Login utente
- `POST /api/auth/logout.php` - Logout utente
- `POST /api/auth/register.php` - Registrazione cliente
- `GET /api/auth/check-session.php` - Verifica sessione

### Servizi
- `GET /api/services/list.php` - Lista servizi
- `POST /api/services/request.php` - Nuova richiesta
- `GET /api/services/request/{id}` - Dettagli richiesta

## 🔧 Configurazioni Avanzate

### Email
```php
// config/config.php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### API Keys Servizi
```php
// config/config.php
define('BRT_API_KEY', 'your-brt-api-key');
define('POSTE_API_KEY', 'your-poste-api-key');
define('TNT_API_KEY', 'your-tnt-api-key');
```

## 🐛 Debug

Per abilitare il debug mode:
```php
// config/config.php
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
```

## 📞 Supporto

Per supporto tecnico contattare:
- **Email**: info@agenziaplinio.it
- **Telefono**: +39 081 0584542

## 📄 Licenza

© 2025 AG Servizi Via Plinio 72. Tutti i diritti riservati.
P.IVA: 08442881218 | REA: NA-985288

---

**Versione**: 1.0.0  
**Ultimo aggiornamento**: Novembre 2025