-- Aggiunge il campo username per gli account amministratore.
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS username VARCHAR(60) NULL AFTER role;

ALTER TABLE users
    ADD UNIQUE INDEX IF NOT EXISTS users_username_unique (username);
