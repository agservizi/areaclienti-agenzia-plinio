<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Documenti';
$adminActive = 'files';

$successMessage = null;
$errorMessage = null;

try {
    $files = getAllFiles($pdo);
} catch (PDOException $exception) {
    $files = [];
    $errorMessage = 'Impossibile caricare i documenti: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <div class="glass-container">
        <div class="admin-page-header">
            <h2 class="admin-page-title">Documenti condivisi</h2>
            <p class="admin-page-subtitle">Consulta i file caricati dai clienti o pubblicati dall'agenzia.</p>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($files)): ?>
            <p class="mb-0">Non sono presenti file condivisi.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Utente</th>
                            <th scope="col">Nome originale</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Dimensione</th>
                            <th scope="col">Percorso</th>
                            <th scope="col">Caricato il</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($file['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($file['user_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small><?php echo htmlspecialchars($file['user_email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($file['filename_original'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($file['mime'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(isset($file['size']) ? number_format(((float) $file['size']) / 1024, 2, ',', '.') . ' KB' : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($file['storage_path'] ?? $file['filename_stored'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(isset($file['created_at']) ? date('d/m/Y H:i', strtotime($file['created_at'])) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
