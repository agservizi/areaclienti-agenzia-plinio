-- Script manuale per finalizzare lo schema dopo la migrazione principale
-- Assicurarsi di eseguire prima la migrazione principale (20251101_align_schema.sql)
-- e aver verificato la presenza delle colonne role/phone/created_at/updated_at.

-- 1. Allineare gli ID a unsigned e ripristinare le chiavi esterne
-- Scommentare solo se necessario: verifica prima
-- (users.id deve diventare INT UNSIGNED AUTO_INCREMENT).

-- ALTER TABLE activity_logs DROP FOREIGN KEY activity_logs_ibfk_1;
-- ALTER TABLE users MODIFY COLUMN id INT UNSIGNED NOT NULL AUTO_INCREMENT;
-- ALTER TABLE activity_logs
--     MODIFY COLUMN user_id INT UNSIGNED NOT NULL,
--     ADD CONSTRAINT activity_logs_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- 2. Allineare services.id a INT UNSIGNED se indispensabile.
-- ALTER TABLE services MODIFY COLUMN id INT UNSIGNED NOT NULL AUTO_INCREMENT;

-- 3. Ripristinare i vincoli FK tra requests → users/services
-- solo dopo che i tipi combaciano e che non ci sono record orfani.
-- SET FOREIGN_KEY_CHECKS = 0;
-- DELETE FROM requests WHERE user_id NOT IN (SELECT id FROM users);
-- DELETE FROM requests WHERE service_id NOT IN (SELECT id FROM services);
-- SET FOREIGN_KEY_CHECKS = 1;
-- ALTER TABLE requests ADD CONSTRAINT fk_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
-- ALTER TABLE requests ADD CONSTRAINT fk_requests_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE;
