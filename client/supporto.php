<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$tickets = [];
$successMessage = null;
$errorMessage = null;

try {
    $tickets = getUserTickets($user['id'], $pdo);
} catch (PDOException $exception) {
    $errorMessage = 'Impossibile caricare i ticket: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'errore inatteso.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    try {
        if ($action === 'message') {
            $ticketId = isset($_POST['ticket_id']) ? (int) $_POST['ticket_id'] : 0;
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';

            if ($ticketId <= 0 || $message === '') {
                $errorMessage = 'Messaggio non valido.';
            } else {
                $ticketStmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ? AND user_id = ? LIMIT 1');
                $ticketStmt->execute([$ticketId, $user['id']]);
                $ticket = $ticketStmt->fetch(PDO::FETCH_ASSOC);

                if (!$ticket) {
                    $errorMessage = 'Ticket non trovato.';
                } else {
                    $messages = [];
                    if (!empty($ticket['messages'])) {
                        $decoded = json_decode($ticket['messages'], true);
                        if (is_array($decoded)) {
                            $messages = $decoded;
                        }
                    }

                    $messages[] = [
                        'sender' => 'client',
                        'body' => $message,
                        'created_at' => date(DATE_ATOM),
                    ];

                    $update = $pdo->prepare('UPDATE tickets SET messages = ?, updated_at = NOW(), status = IF(status = "closed", "in_progress", status) WHERE id = ?');
                    $update->execute([
                        json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        $ticketId,
                    ]);

                    recordAuditLog($pdo, $user['id'], 'ticket.update', ['ticket_id' => $ticketId]);
                    $successMessage = 'Messaggio inviato con successo.';
                }
            }
        } else {
            $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
            $category = isset($_POST['category']) ? trim($_POST['category']) : 'assistenza';
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';

            if ($subject === '' || $message === '') {
                $errorMessage = 'Compila oggetto e descrizione per aprire un ticket.';
            } else {
                $messages = json_encode([
                    [
                        'sender' => 'client',
                        'body' => $message,
                        'created_at' => date(DATE_ATOM),
                        'category' => $category,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                $insert = $pdo->prepare('INSERT INTO tickets (user_id, subject, status, messages, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
                $insert->execute([$user['id'], $subject, 'open', $messages]);

                recordAuditLog($pdo, $user['id'], 'ticket.create', ['subject' => $subject]);
                $successMessage = 'Ticket aperto con successo.';
            }
        }

        $tickets = getUserTickets($user['id'], $pdo);
    } catch (PDOException $exception) {
        $errorMessage = 'Errore nella gestione del ticket: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'errore inatteso.');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1>Supporto clienti</h1>
        <p>Apri un ticket o rispondi a uno già esistente per contattare l'assistenza.</p>

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

        <form method="post" class="row g-3 mb-5">
            <input type="hidden" name="action" value="create">
            <div class="col-md-6">
                <label class="form-label" for="subject">Oggetto</label>
                <input class="form-control" type="text" id="subject" name="subject" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="category">Categoria</label>
                <select class="form-select" id="category" name="category">
                    <option value="assistenza">Assistenza tecnica</option>
                    <option value="fatturazione">Fatturazione</option>
                    <option value="commerciale">Commerciale</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="message">Descrizione</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-outline-light" type="submit">Apri Ticket</button>
            </div>
        </form>

        <h2 class="h4 mb-3">I tuoi ticket</h2>
        <?php if (empty($tickets)): ?>
            <p class="mb-0">Nessun ticket aperto al momento.</p>
        <?php else: ?>
            <div class="accordion" id="ticketAccordion">
                <?php foreach ($tickets as $index => $ticket):
                    $messages = [];
                    if (!empty($ticket['messages'])) {
                        $decoded = json_decode($ticket['messages'], true);
                        if (is_array($decoded)) {
                            $messages = $decoded;
                        }
                    }
                ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?php echo (int) $ticket['id']; ?>">
                            <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo (int) $ticket['id']; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse-<?php echo (int) $ticket['id']; ?>">
                                <span class="me-2 badge bg-<?php echo htmlspecialchars(statusBadge($ticket['status']), ENT_QUOTES, 'UTF-8'); ?> text-uppercase">
                                    <?php echo htmlspecialchars($ticket['status'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <?php echo htmlspecialchars($ticket['subject'], ENT_QUOTES, 'UTF-8'); ?>
                                <small class="ms-auto">Aperto il <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ticket['created_at'])), ENT_QUOTES, 'UTF-8'); ?></small>
                            </button>
                        </h2>
                        <div id="collapse-<?php echo (int) $ticket['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading-<?php echo (int) $ticket['id']; ?>" data-bs-parent="#ticketAccordion">
                            <div class="accordion-body">
                                <?php if (empty($messages)): ?>
                                    <p>Nessun messaggio disponibile.</p>
                                <?php else: ?>
                                    <div class="list-group mb-4">
                                        <?php foreach ($messages as $entry): ?>
                                            <div class="list-group-item list-group-item-dark mb-2">
                                                <div class="d-flex justify-content-between">
                                                    <strong><?php echo htmlspecialchars($entry['sender'] === 'client' ? 'Cliente' : 'Operatore', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <small><?php echo htmlspecialchars(isset($entry['created_at']) ? date('d/m/Y H:i', strtotime($entry['created_at'])) : '', ENT_QUOTES, 'UTF-8'); ?></small>
                                                </div>
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($entry['body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($ticket['status'] !== 'closed'): ?>
                                    <form method="post" class="row g-3">
                                        <input type="hidden" name="action" value="message">
                                        <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                                        <div class="col-12">
                                            <label class="form-label" for="reply-<?php echo (int) $ticket['id']; ?>">Invia un messaggio</label>
                                            <textarea class="form-control" id="reply-<?php echo (int) $ticket['id']; ?>" name="message" rows="3" required></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button class="btn btn-outline-light" type="submit">Invia</button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Ticket chiuso. Se necessiti di ulteriore assistenza, apri un nuovo ticket.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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
