-- Seed per creare o aggiornare un account amministratore di base
-- Eseguire con: SOURCE sql/seeds/20251101_seed_admin_user.sql;
-- Aggiornare nome/email/password se necessario prima dell'esecuzione.

INSERT INTO users (role, username, name, email, password, phone)
VALUES
    ('admin', 'admin', 'Administrator', 'admin@agservizi.it', '$argon2id$v=19$m=65536,t=4,p=1$QUNNNGc4cUs4b0JObElaZg$13Nx9sM0FRUHs1aY/ez9pOdaUDXmhyuAp/S6dB/zLow', NULL)
ON DUPLICATE KEY UPDATE
    role = VALUES(role),
    username = VALUES(username),
    name = VALUES(name),
    password = VALUES(password),
    phone = VALUES(phone),
    updated_at = CURRENT_TIMESTAMP;
