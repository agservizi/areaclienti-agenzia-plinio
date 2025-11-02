<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = isset($_POST['ticket_id']) ? (int) $_POST['ticket_id'] : 0;
    $action = $_POST['action'] ?? 'status';

    if ($ticketId <= 0) {
        $errorMessage = 'Ticket non valido.';
    } else {
        try {
            $ticketStmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ? LIMIT 1');
            $ticketStmt->execute([$ticketId]);
            $ticket = $ticketStmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                $errorMessage = 'Ticket non trovato.';
            } else {
                if ($action === 'message') {
                    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
                    if ($message === '') {
                        $errorMessage = 'Inserisci un messaggio.';
                    } else {
                        $messages = [];
                        if (!empty($ticket['messages'])) {
                            $decoded = json_decode($ticket['messages'], true);
                            if (is_array($decoded)) {
                                $messages = $decoded;
                            }
                        }

                        $messages[] = [
                            'sender' => 'operator',
                            'body' => $message,
                            'created_at' => date(DATE_ATOM),
                        ];

                        $update = $pdo->prepare('UPDATE tickets SET messages = ?, updated_at = NOW(), status = IF(status = "closed", "in_progress", status) WHERE id = ?');
                        $update->execute([
                            json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            $ticketId,
                        ]);

                        recordAuditLog($pdo, $user['id'], 'ticket.reply', ['ticket_id' => $ticketId]);
                        $successMessage = 'Risposta inviata correttamente.';
                    }
                } else {
                    $status = $_POST['status'] ?? 'open';
                    $allowed = ['open', 'in_progress', 'closed'];

                    if (!in_array($status, $allowed, true)) {
                        $errorMessage = 'Stato non valido.';
                    } else {
                        $update = $pdo->prepare('UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?');
                        $update->execute([$status, $ticketId]);
                        recordAuditLog($pdo, $user['id'], 'ticket.update_status', ['ticket_id' => $ticketId, 'status' => $status]);
                        $successMessage = 'Stato del ticket aggiornato.';
                    }
                }
            }
        } catch (PDOException $exception) {
            $errorMessage = 'Errore durante la gestione del ticket: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprova più tardi.');
        }
    }
}

try {
    $tickets = getAllTickets($pdo);
} catch (PDOException $exception) {
    $tickets = [];
    $errorMessage = 'Impossibile caricare i ticket: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'errore inatteso.');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1 class="text-white mb-4">Gestione Ticket</h1>
        <p>Visualizza le richieste di supporto dei clienti e aggiorna gli stati.</p>

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

        <?php if (empty($tickets)): ?>
            <p class="mb-0">Non sono presenti ticket.</p>
        <?php else: ?>
            <div class="accordion" id="adminTicketAccordion">
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
                        <h2 class="accordion-header" id="ticket-heading-<?php echo (int) $ticket['id']; ?>">
                            <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#ticket-collapse-<?php echo (int) $ticket['id']; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="ticket-collapse-<?php echo (int) $ticket['id']; ?>">
                                <span class="badge bg-<?php echo htmlspecialchars(statusBadge($ticket['status']), ENT_QUOTES, 'UTF-8'); ?> me-2 text-uppercase">
                                    <?php echo htmlspecialchars($ticket['status'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <?php echo htmlspecialchars($ticket['subject'], ENT_QUOTES, 'UTF-8'); ?>
                                <small class="ms-auto">
                                    Cliente: <?php echo htmlspecialchars($ticket['user_name'] ?? $ticket['user_email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?> |
                                    Aperto il <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ticket['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                </small>
                            </button>
                        </h2>
                        <div id="ticket-collapse-<?php echo (int) $ticket['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="ticket-heading-<?php echo (int) $ticket['id']; ?>" data-bs-parent="#adminTicketAccordion">
                            <div class="accordion-body">
                                <?php if (empty($messages)): ?>
                                    <p class="text-muted">Nessuno scambio registrato.</p>
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

                                <div class="border-top pt-3 mt-3">
                                    <form method="post" class="row g-2 align-items-center">
                                        <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                                        <input type="hidden" name="action" value="status">
                                        <div class="col-sm-4">
                                            <label class="form-label" for="status-<?php echo (int) $ticket['id']; ?>">Stato</label>
                                            <select class="form-select" id="status-<?php echo (int) $ticket['id']; ?>" name="status">
                                                <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                                <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In progress</option>
                                                <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2 text-sm-end">
                                            <button class="btn btn-outline-light mt-3 mt-sm-0" type="submit">Aggiorna</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="border-top pt-3 mt-3">
                                    <form method="post" class="row g-3">
                                        <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                                        <input type="hidden" name="action" value="message">
                                        <div class="col-12">
                                            <label class="form-label" for="reply-<?php echo (int) $ticket['id']; ?>">Risposta operatore</label>
                                            <textarea class="form-control" id="reply-<?php echo (int) $ticket['id']; ?>" name="message" rows="3" required></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button class="btn btn-outline-light" type="submit">Invia risposta</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<footer class="footer-glass mt-5">
    <div class="container text-center">
        <small>&copy; <span data-current-year></span> Agenzia Plinio - Ticket</small>
    </div>
</footer>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/admin.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
