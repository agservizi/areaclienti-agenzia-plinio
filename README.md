# AG Servizi Area Personale

Portale PHP 8 per la gestione di clienti e operatori AG Servizi. Include area cliente, area admin, API interne, logging applicativo e storage opzionale cifrato per gli allegati.

## Requisiti

- PHP 8.1+
- Estensioni: pdo_mysql, sodium, json, iconv, fileinfo
- MySQL 8+
- Server web configurato per puntare alla cartella del progetto

## Configurazione rapida

1. Copiare `.env.example` (se presente) oppure creare `.env` impostando le variabili richieste (DB, app, storage). I valori vengono caricati automaticamente da `includes/config.php` e non sono salvati nel repository.
2. Installare le dipendenze PHP native necessarie (nessun composer richiesto).
3. Importare lo schema principale per un database vuoto:
   ```bash
   mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" < sql/install_full.sql
   ```
   In alternativa, eseguire `sql/migrations/20251101_fresh_install.sql` dopo aver dato `USE "$DB_NAME";`.
4. Creare un admin iniziale:
   ```bash
   php scripts/seed_admin.php --email=admin@agservizi.test --password=Admin123!
   ```
5. (Opzionale) Attivare lo storage cifrato filesystem:
   ```bash
   php scripts/generate_storage_key.php
   ```
   Impostare nel `.env`: `STORAGE_DRIVER=filesystem`.
6. Configurare il server web (Apache/Nginx) per inviare tutte le richieste PHP alla root del progetto.

## Struttura principale

- `admin/` pagine area amministrativa.
- `client/` pagine area cliente.
- `api/` endpoint REST interni con risposte JSON.
- `includes/` configurazione, helper, autenticazione, reportistica.
- `assets/` CSS, JS e vendor locali (Bootstrap incluso).
- `templates/` componenti riutilizzabili.
- `scripts/` utility CLI per chiavi e admin seed.
- `uploads/` storage chiaro (driver mysql).
- `storage/encrypted/` storage cifrato (driver filesystem).
- `logs/` file di log con rotazione giornaliera automatica.

## Database

- Tabelle principali: `users`, `services`, `requests`.
- `sql/schema.sql` contiene schema e dati demo per i servizi.
- Script `scripts/seed_admin.php` crea o aggiorna l'admin rispetto all'email fornita.
- Richieste e allegati vengono salvati in JSON all'interno della tabella `requests`.

## Storage allegati

- Driver `mysql` (default): file salvati nel percorso `uploads/` con nome random.
- Driver `filesystem`: contenuto cifrato con `libsodium` in `storage/encrypted/{user_id}`.
- Generare la chiave tramite `php scripts/generate_storage_key.php`; lo script crea `storage/.master.key` con permessi 600.
- Download allegati gestiti da `request-download.php`, con controllo ruolo e proprietario.

## Sicurezza

- Autenticazione basata su sessioni e ruoli (cliente/admin).
- Password salvate con `password_hash` Argon2id.
- CSRF token rigenerato e validato su tutte le form sensibili.
- Log applicativi in `logs/` con rotazione automatica (mantiene `max_files` recenti in configurazione).
- Upload filtrati per MIME, dimensione e, se attivo, cifrati.

## Reportistica

- Pagina `admin/reports.php` con filtri per data, stato e servizio.
- Esportazione CSV e PDF nativa (generatore minimale, nessuna dipendenza esterna).
- Script riutilizzabili in `includes/reporting.php` per futuri report custom.

## Checklist test manuali

Vedere `docs/testing-checklist.md` per la lista completa. Suggerito almeno:

- Registrazione utente e login.
- Creazione richiesta con allegato (entrambi i driver storage).
- Download allegato lato cliente e admin.
- Aggiornamento stato richiesta da area admin.
- Export CSV/PDF dalla pagina report.

## Note operative

- Log ruotano automaticamente: configurare `logs.max_files` in `includes/config.php` o `.env`.
- In ambienti multi-server assicurarsi di condividere la chiave di cifratura in modo sicuro.
- Per debug attivare `APP_DEBUG=true` nel `.env`; ricordarsi di disattivarlo in produzione.
