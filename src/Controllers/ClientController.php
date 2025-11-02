<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CoverageCheck;
use App\Models\FileStore;
use App\Models\Shipment;
use App\Models\SimOrder;
use App\Models\SPIDRequest;
use App\Models\Ticket;
use App\Models\User;
use RuntimeException;
use function app_log;
use function current_user;
use function crypto_decrypt_file;
use function flash;
use function redirect;
use function render;
use function require_login;
use function sanitize;
use function validate_email;
use function validate_required;

class ClientController
{
    public function dashboard(): void
    {
        require_login();
        $user = current_user();
        $userId = (int) $user['id'];

        $spidCount = SPIDRequest::countForUser($userId);
        $simCount = SimOrder::countForUser($userId);
        $shipmentCount = Shipment::countForUser($userId);
        $openTickets = Ticket::countOpenForUser($userId);

        $recentShipments = Shipment::latestForUser($userId, 5);
        $recentTickets = Ticket::latestForUser($userId, 5);

        render('client/dashboard', [
            'page_title' => 'Dashboard cliente',
            'spidCount' => $spidCount,
            'simCount' => $simCount,
            'shipmentCount' => $shipmentCount,
            'openTickets' => $openTickets,
            'recentShipments' => $recentShipments,
            'recentTickets' => $recentTickets,
        ]);
    }

    public function profile(): void
    {
        require_login();
        $user = User::find((int) current_user()['id']);
        render('client/profile', [
            'page_title' => 'Impostazioni profilo',
            'user' => $user,
        ]);
    }

    public function updateProfile(): void
    {
        require_login();
        $data = sanitize($_POST);
        $userId = (int) current_user()['id'];

        $errors = validate_required($data, [
            'name' => 'Nome e cognome',
            'email' => 'Email',
        ]);
        if (!empty($data['email']) && !validate_email($data['email'])) {
            $errors['email'] = 'Email non valida';
        }
        if ($errors) {
            flash('danger', implode('<br>', $errors));
            redirect('/client/profile');
        }

        if (!User::updateProfile($userId, [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
        ])) {
            flash('danger', 'Impossibile aggiornare il profilo al momento');
            redirect('/client/profile');
        }

        flash('success', 'Profilo aggiornato con successo');
        redirect('/client/profile');
    }

    public function services(): void
    {
        require_login();
        render('client/services/index', [
            'page_title' => 'Servizi disponibili',
        ]);
    }

    public function spidForm(): void
    {
        require_login();
        render('client/services/spid', [
            'page_title' => 'Richiesta SPID',
        ]);
    }

    public function submitSpid(): void
    {
        require_login();
        $data = sanitize($_POST);
        $user = current_user();

        $required = [
            'service_level' => 'Livello servizio',
            'document_number' => 'Numero documento',
        ];
        $errors = validate_required($data, $required);
        if ($errors) {
            flash('danger', implode('<br>', $errors));
            redirect('/client/spid/request');
        }

        SPIDRequest::create([
            'user_id' => (int) $user['id'],
            'status' => 'pending',
            'data' => [
                'service_level' => $data['service_level'],
                'document_number' => $data['document_number'],
                'notes' => $data['notes'] ?? '',
            ],
        ]);

        flash('success', 'Richiesta SPID inviata correttamente');
        redirect('/client/services');
    }

    public function simForm(): void
    {
        require_login();
        render('client/services/sim', [
            'page_title' => 'Richiesta attivazione SIM',
        ]);
    }

    public function submitSim(): void
    {
        require_login();
        $data = sanitize($_POST);
        $required = [
            'operator' => 'Operatore',
            'plan' => 'Piano',
        ];
        $errors = validate_required($data, $required);
        if ($errors) {
            flash('danger', implode('<br>', $errors));
            redirect('/client/sim/request');
        }

        SimOrder::create([
            'user_id' => (int) current_user()['id'],
            'operator' => $data['operator'],
            'plan' => $data['plan'],
            'status' => 'pending',
            'details' => [
                'notes' => $data['notes'] ?? '',
            ],
        ]);

        flash('success', 'Richiesta SIM inviata');
        redirect('/client/services');
    }

    public function checkCoverage(): void
    {
        require_login();
        $data = sanitize($_POST);
        $required = [
            'address' => 'Indirizzo',
            'operator' => 'Operatore',
        ];
        $errors = validate_required($data, $required);
        if ($errors) {
            flash('danger', implode('<br>', $errors));
            redirect('/client/services');
        }

        $result = CoverageCheck::create([
            'user_id' => (int) current_user()['id'],
            'address' => $data['address'],
            'operator' => $data['operator'],
            'result' => [
                'coverage' => rand(70, 99) . '%',
                'technology' => 'FTTC',
                'note' => 'Risultato simulato. Verifica in negozio per conferma.',
            ],
        ]);

        flash('info', 'Copertura stimata: ' . ($result['result']['coverage'] ?? 'N/D'));
        redirect('/client/services');
    }

    public function shipments(): void
    {
        require_login();
        $shipments = Shipment::forUser((int) current_user()['id']);
        render('client/shipments', [
            'page_title' => 'Spedizioni',
            'shipments' => $shipments,
        ]);
    }

    public function createShipment(): void
    {
        require_login();
        $data = sanitize($_POST);
        $required = [
            'sender_name' => 'Mittente',
            'recipient_name' => 'Destinatario',
            'weight' => 'Peso',
        ];
        $errors = validate_required($data, $required);
        if ($errors) {
            flash('danger', implode('<br>', $errors));
            redirect('/client/shipments');
        }

        $tracking = strtoupper(bin2hex(random_bytes(4)));
        Shipment::create([
            'user_id' => (int) current_user()['id'],
            'tracking_code' => $tracking,
            'sender' => [
                'name' => $data['sender_name'],
                'address' => $data['sender_address'] ?? '',
            ],
            'recipient' => [
                'name' => $data['recipient_name'],
                'address' => $data['recipient_address'] ?? '',
            ],
            'weight' => (float) ($data['weight'] ?? 0),
            'dimensions' => $data['dimensions'] ?? '',
            'status' => 'created',
        ]);

        flash('success', 'Spedizione creata. Tracking: ' . $tracking);
        redirect('/client/shipments');
    }

    public function tickets(): void
    {
        require_login();
        $tickets = Ticket::forUser((int) current_user()['id']);
        render('client/tickets', [
            'page_title' => 'Supporto clienti',
            'tickets' => $tickets,
        ]);
    }

    public function createTicket(): void
    {
        require_login();
        $data = sanitize($_POST);
        $required = [
            'subject' => 'Oggetto',
            'message' => 'Messaggio',
        ];
        $errors = validate_required($data, $required);
        if ($errors) {
            flash('danger', implode('<br>', $errors));
            redirect('/client/tickets');
        }

        Ticket::create([
            'user_id' => (int) current_user()['id'],
            'subject' => $data['subject'],
            'status' => 'open',
            'messages' => [
                [
                    'from' => 'client',
                    'content' => $data['message'],
                    'at' => date('Y-m-d H:i'),
                ],
            ],
        ]);

        flash('success', 'Ticket creato con successo');
        redirect('/client/tickets');
    }

    public function documents(): void
    {
        require_login();
        $files = FileStore::forUser((int) current_user()['id']);
        render('client/documents', [
            'page_title' => 'Documenti',
            'files' => $files,
        ]);
    }

    public function downloadDocument(int $id): void
    {
        require_login();
        $file = FileStore::findForUser($id, (int) current_user()['id']);
        if (!$file) {
            http_response_code(404);
            echo 'Documento non trovato';
            return;
        }

        $storage = __DIR__ . '/../../storage/files_encrypted';
        try {
            $contents = crypto_decrypt_file($file['filename_storage'], $storage, $file['iv']);
        } catch (RuntimeException $exception) {
            app_log('files', 'Download fallito', ['error' => $exception->getMessage()]);
            http_response_code(500);
            echo 'Impossibile recuperare il documento';
            return;
        }

        header('Content-Type: ' . $file['mime']);
        header('Content-Disposition: attachment; filename="' . $file['filename_original'] . '"');
        header('Content-Length: ' . strlen($contents));
        echo $contents;
    }
}
