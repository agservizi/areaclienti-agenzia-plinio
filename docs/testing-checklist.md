# Checklist Test Manuali

## Autenticazione
- [ ] Registrazione nuovo cliente con email unica.
- [ ] Login cliente, logout e nuova sessione.
- [ ] Reset password admin da `admin/manage-users.php` e verifica accesso con password temporanea.

## Area Cliente
- [ ] Creazione richiesta servizio con e senza allegato.
- [ ] Download allegato per richiesta esistente.
- [ ] Aggiornamento dati profilo (nome e telefono).
- [ ] Dashboard: conteggi richieste corretti dopo nuove azioni.

## Area Admin
- [ ] Accesso admin e verifica dashboard statistiche.
- [ ] Filtrare richieste per stato/servizio in `admin/manage-requests.php`.
- [ ] Aggiornare stato richiesta e salvare nota interna.
- [ ] Gestione utenti: promozione/demozione ruolo e reset password.
- [ ] Reportistica: filtri per data e export CSV e PDF.

## API e Sicurezza
- [ ] Tentativo di accesso a endpoint admin senza ruolo corretto (atteso 403 JSON).
- [ ] CSRF: invio form senza token produce errore.
- [ ] Upload file con MIME non consentito viene respinto.

## Storage e Logging
- [ ] Driver `mysql`: verificare salvataggio file in `uploads/`.
- [ ] Driver `filesystem`: generare chiave, inviare allegato, verificare cifratura e download.
- [ ] Controllare log giornaliero in `logs/` e rimozione file oltre `max_files`.

## Regressione generale
- [ ] Navigazione completa con browser desktop (Chrome/Firefox) e responsive mobile.
- [ ] Verifica traduzioni/label in italiano su tutte le pagine principali.
