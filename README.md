# Portale Servizi Agenzia Plinio

Portale clienti e amministrazione realizzato in PHP 8.x con MySQL e interfaccia glassmorphism basata su Bootstrap 5.

## Struttura principale

- `config/env.php` - Variabili di configurazione applicative e database.
- `includes/` - Connessione al database, helper e layout condivisi.
- `auth/` - Pagine di autenticazione (login, registrazione, recupero).
- `client/` - Area riservata ai clienti registrati.
- `admin/` - Area di gestione per gli amministratori.
- `assets/` - Risorse statiche locali (CSS, JS, immagini, font).

## Requisiti

- PHP 8.1+
- Estensione PDO MySQL attiva
- Server web configurato per puntare alla cartella `portal`
- Database MySQL configurato con le credenziali presenti in `config/env.php`

## Setup rapido

1. Copia i file ufficiali di Bootstrap 5.3 (`bootstrap.min.css` e `bootstrap.bundle.min.js`) nelle rispettive cartelle sotto `assets/`.
2. Importa le tabelle richieste (`utenti`, `servizi`, ecc.) nel database remoto specificato nelle variabili d'ambiente.
3. Aggiorna `config/env.php` con eventuali credenziali personalizzate.
4. Assicurati che il server web abbia i permessi di lettura sui file del progetto.

## Note

- Le funzionalità di salvataggio impostazioni e ticket sono predisposte e possono essere collegate alle tabelle dedicate quando disponibili.
- Le password utenti sono gestite tramite `password_hash`/`password_verify`.
- I percorsi verso asset e pagine interne utilizzano l'URL configurato in `config/env.php`.
