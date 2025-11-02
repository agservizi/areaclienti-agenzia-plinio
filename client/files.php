<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$successMessage = null;
$errorMessage = null;

try {
    $files = getUserFiles($user['id'], $pdo);
} catch (PDOException $exception) {
    $files = [];
    $errorMessage = 'Impossibile recuperare i documenti: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1>Documenti caricati</h1>
        <p>Documentazione condivisa con l'agenzia. Per caricamenti aggiuntivi contatta il supporto.</p>

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
            <p class="mb-0">Non sono presenti documenti disponibili.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Nome file</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Dimensione</th>
                            <th scope="col">Caricato il</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($file['filename_original'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($file['mime'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(number_format(((float) $file['size']) / 1024, 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?> KB</td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($file['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/client.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
