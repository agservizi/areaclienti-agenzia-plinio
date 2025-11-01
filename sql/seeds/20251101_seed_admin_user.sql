-- Seed per creare o aggiornare un account amministratore di base
-- Eseguire con: SOURCE sql/seeds/20251101_seed_admin_user.sql;
-- Aggiornare nome/email/password se necessario prima dell'esecuzione.

INSERT INTO users (role, name, email, password, phone)
VALUES
    ('admin', 'Administrator', 'admin@agservizi.it', '$argon2id$v=19$m=65536,t=4,p=1$UHRkMUcvUmpicjEycDRZcw$z4b/htXP7wyoTqm3mz5NxNviTUCjG45sIs3O5AxxtTA', NULL)
ON DUPLICATE KEY UPDATE
    role = VALUES(role),
    name = VALUES(name),
    password = VALUES(password),
    phone = VALUES(phone),
    updated_at = CURRENT_TIMESTAMP;
