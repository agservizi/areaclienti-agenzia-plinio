<?php
/**
 * Utility CLI script to migrate plaintext passwords in the `users` table
 * to secure hashes using PHP's password_hash().
 *
 * Usage:
 *   php scripts/migrate_password_hashes.php [--dry-run]
 *
 * When --dry-run is provided no data is written and the script only reports
 * what would be changed. Without the flag the users table is updated in-place.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Questo script va eseguito solo da riga di comando.\n");
    exit(1);
}

$dryRun = in_array('--dry-run', $argv, true);

require __DIR__ . '/../includes/db_connect.php';

function passwordLooksHashed(?string $password): bool
{
    if ($password === null || $password === '') {
        return false;
    }

    $info = password_get_info($password);

    return $info['algo'] !== 0;
}

echo $dryRun
    ? "Modalità anteprima: nessuna modifica permanente verrà applicata.\n"
    : "Avvio migrazione password...\n";

$stmt = $pdo->query('SELECT id, email, password FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
$skippedEmpty = 0;
$alreadyHashed = 0;

if (!$dryRun) {
    $pdo->beginTransaction();
}

try {
    foreach ($users as $user) {
        $id = (int) $user['id'];
        $email = $user['email'] ?? 'sconosciuta';
        $password = $user['password'] ?? '';

        if ($password === '') {
            $skippedEmpty++;
            echo "[SKIP] Utente {$email} ha password vuota, ignorato.\n";
            continue;
        }

        if (passwordLooksHashed($password)) {
            $alreadyHashed++;
            continue;
        }

        $newHash = password_hash($password, PASSWORD_DEFAULT);

        if ($newHash === false) {
            throw new RuntimeException("Impossibile generare hash per l'utente {$email}.");
        }

        echo "[UPDATE] Utente {$email} migrato a hash sicuro." . ($dryRun ? " (dry-run)" : '') . "\n";

        if (!$dryRun) {
            $update = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $update->execute([$newHash, $id]);
        }

        $updated++;
    }

    if (!$dryRun) {
        $pdo->commit();
    }
} catch (Throwable $exception) {
    if (!$dryRun && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Errore durante la migrazione: " . $exception->getMessage() . "\n");
    exit(1);
}

echo "\nRisultato:\n";
echo "  - Password aggiornate: {$updated}\n";
echo "  - Password già sicure: {$alreadyHashed}\n";
echo "  - Password vuote ignorate: {$skippedEmpty}\n";

echo $dryRun
    ? "\nNessuna modifica è stata applicata (modalità dry-run).\n"
    : "\nMigrazione completata con successo.\n";
