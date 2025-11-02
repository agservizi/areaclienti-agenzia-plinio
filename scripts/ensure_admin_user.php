<?php
/**
 * Garantisce l'esistenza di un account amministratore con
 * username "admin" e password "admin" (hash sicuro).
 *
 * Utilizzo:
 *   php scripts/ensure_admin_user.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Questo script va eseguito solo da riga di comando.\n");
    exit(1);
}

require __DIR__ . '/../includes/db_connect.php';

$username = 'admin';
$passwordPlain = 'admin';
$defaultEmail = 'admin@example.com';
$defaultName = 'Administrator';

$hash = password_hash($passwordPlain, PASSWORD_DEFAULT);

$pdo->beginTransaction();

try {
    $selectByUsername = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $selectByUsername->execute([$username]);
    $adminUser = $selectByUsername->fetch(PDO::FETCH_ASSOC);

    if (!$adminUser) {
        $selectByRole = $pdo->prepare('SELECT * FROM users WHERE role = ? ORDER BY id ASC LIMIT 1');
        $selectByRole->execute(['admin']);
        $adminUser = $selectByRole->fetch(PDO::FETCH_ASSOC);
    }

    if ($adminUser) {
        $id = (int) $adminUser['id'];
        $email = $adminUser['email'] ?? '';
        $name = $adminUser['name'] ?? '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailCheck = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?');
            $emailCandidate = $defaultEmail;
            $suffix = 1;

            while (true) {
                $emailCheck->execute([$emailCandidate, $id]);
                $exists = (int) $emailCheck->fetchColumn();

                if ($exists === 0) {
                    $email = $emailCandidate;
                    break;
                }

                $emailCandidate = sprintf('admin%d@example.com', $suffix++);
            }
        }

        if ($name === '') {
            $name = $defaultName;
        }

        $update = $pdo->prepare(
            'UPDATE users
             SET role = :role, username = :username, name = :name, email = :email, password = :password,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $update->execute([
            'role' => 'admin',
            'username' => $username,
            'name' => $name,
            'email' => $email,
            'password' => $hash,
            'id' => $id,
        ]);

        $pdo->commit();

        echo "Aggiornato l'account amministratore esistente (ID {$id}).\n";
        exit(0);
    }

    $emailCheck = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $emailCandidate = $defaultEmail;
    $suffix = 1;

    while (true) {
        $emailCheck->execute([$emailCandidate]);
        $exists = (int) $emailCheck->fetchColumn();

        if ($exists === 0) {
            break;
        }

        $emailCandidate = sprintf('admin%d@example.com', $suffix++);
    }

    $insert = $pdo->prepare(
        'INSERT INTO users (role, username, name, email, password, phone, created_at, updated_at)
         VALUES (:role, :username, :name, :email, :password, NULL, NOW(), NOW())'
    );
    $insert->execute([
        'role' => 'admin',
        'username' => $username,
        'name' => $defaultName,
        'email' => $emailCandidate,
        'password' => $hash,
    ]);

    $newId = (int) $pdo->lastInsertId();
    $pdo->commit();

    echo "Creato nuovo account amministratore con ID {$newId}.\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Errore: " . $exception->getMessage() . "\n");
    exit(1);
}
