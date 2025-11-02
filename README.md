# Portale Agenzia Plinio

Portale web in PHP nativo per la gestione dei servizi dell'Agenzia Plinio. Include un'area riservata clienti e un pannello admin coerenti tra loro, con frontend sviluppato in Bootstrap locale e JavaScript vanilla.

## Requisiti

- PHP \>= 8.1 con estensioni `pdo_mysql`, `openssl` o `sodium`
- Composer
- MySQL/MariaDB
- Server web configurato per puntare la root pubblica a `public/`

## Setup rapido

1. **Installazione dipendenze**
   ```powershell
   composer install
   ```

2. **Configurazione ambiente**
   - Copia `.env.example` in `.env` e popola le variabili (l'app legge il file tramite `src/Helpers/Env.php`).
   - Imposta i permessi di scrittura su `storage/` e `logs/`.
   - Genera `APP_KEY` con 32 caratteri casuali (es. `php -r "echo bin2hex(random_bytes(16));"`).

3. **Bootstrap e assets locali**
   - Scarica l'ultima release di Bootstrap 5 (CSS + bundle JS) e posiziona i file in `public/assets/bootstrap/` come `bootstrap.min.css` e `bootstrap.bundle.min.js`.
   - Scarica Bootstrap Icons (CSS + webfonts) e posiziona i file in `public/assets/icons/`.
   - Rimuovi i file placeholder forniti in questa repo una volta sostituiti con gli asset ufficiali.

4. **Database**
   - Crea un database MySQL dedicato.
   - Importa `migrations/schema.sql`.

5. **Server locale**
   ```powershell
   php -S localhost:8080 -t public
   ```

## Struttura principali cartelle

```
/public              Front controller e assets
/src
  controllers        Logica di routing
  models             Accesso dati (PDO)
  helpers            Funzioni di supporto (env, auth, csrf, crypto, mail)
  views              Template PHP per client/admin
/storage/files_encrypted  File caricati cifrati
/logs                Log applicativi
/migrations          Script SQL
```

## Sicurezza chiave

- Password memorizzate con `password_hash`
- Sessioni configurate con cookie sicuri, HttpOnly e SameSite
- Token CSRF globale per tutte le richieste POST
- Validazione server-side con sanitizzazione output (`htmlspecialchars`)
- File upload cifrati su disco con Sodium (fallback OpenSSL)
- PHPMailer per invio email tramite SMTP configurato

## Script principali (MVP)

- Autenticazione (registrazione, login, logout)
- Dashboard cliente con riepilogo servizi
- Dashboard admin con KPIs, gestione utenti, pratiche, spedizioni e ticket
- API JSON per coverage check e tracking
- Gestione documenti con upload cifrato
- Logging su file in `logs/app.log`

## Test e QA

- Esegui `composer dump-autoload` dopo modifiche a helper
- Usa account admin (ruolo `admin`) per verificare pannello gestori
- Importa dati demo con script personalizzati in `migrations/`

## Produzione

- Imposta `APP_ENV=production` e `APP_DEBUG=false`
- Configura HTTPS obbligatorio (necessario per cookie `secure`)
- Pianifica backup del DB e della cartella `storage/`
- Sposta le chiavi di cifratura fuori dalla webroot quando possibile
