<?php
declare(strict_types=1);

/**
 * @var array $requests List of request rows
 * @var bool $isAdmin    Whether to show admin columns
 */
?>
<div class="table-responsive">
    <table class="table table-dark-modern table-hover align-middle mb-0">
        <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Servizio</th>
            <th scope="col">Stato</th>
            <th scope="col">Creata</th>
            <th scope="col">Aggiornata</th>
            <?php if (!empty($isAdmin)): ?>
                <th scope="col">Utente</th>
                <th scope="col" class="text-end">Azione</th>
            <?php else: ?>
                <th scope="col" class="text-end">Azioni</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($requests)): ?>
            <tr>
                <td colspan="<?= !empty($isAdmin) ? '7' : '6' ?>">
                    <div class="placeholder-card">Nessuna richiesta trovata.</div>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td>#<?= escape((string) $request['id']) ?></td>
                    <td><?= escape($request['service_title'] ?? '') ?></td>
                    <td>
                        <span class="badge-status <?= escape($request['status'] ?? '') ?>">
                            <?= escape(ucfirst((string) $request['status'])) ?>
                        </span>
                    </td>
                    <td><?= escape(format_date($request['created_at'] ?? null)) ?></td>
                    <td><?= escape(format_date($request['updated_at'] ?? null)) ?></td>
                    <?php if (!empty($isAdmin)): ?>
                        <td><?= escape($request['user_email'] ?? '-') ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-accent" href="/admin/manage-requests.php?id=<?= (int) $request['id'] ?>">Dettagli</a>
                        </td>
                    <?php else: ?>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-accent" href="/client/requests.php?id=<?= (int) $request['id'] ?>">Visualizza</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
