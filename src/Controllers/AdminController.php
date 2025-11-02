<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Shipment;
use App\Models\SimOrder;
use App\Models\SPIDRequest;
use App\Models\Ticket;
use App\Models\User;
use function flash;
use function redirect;
use function render;
use function require_login;
use function sanitize;
use function send_mail;
use function validate_email;
use function validate_required;

class AdminController
{
    public function dashboard(): void
    {
        require_login('admin');
        $stats = [
            'users' => User::count(),
            'spid_pending' => SPIDRequest::countByStatus('pending'),
            'sim_processing' => SimOrder::countByStatus('processing'),
            'shipments_today' => Shipment::countCreatedToday(),
            'tickets_open' => Ticket::countByStatus('open'),
        ];
        $recentSpid = SPIDRequest::latest(5);
        $recentTickets = Ticket::latest(5);

        render('admin/dashboard', [
            'page_title' => 'Dashboard Admin',
            'stats' => $stats,
            'recentSpid' => $recentSpid,
            'recentTickets' => $recentTickets,
        ], ['layout' => 'admin']);
    }

    public function users(): void
    {
        require_login('admin');
        $users = User::all();
        render('admin/users', [
            'page_title' => 'Gestione utenti',
            'users' => $users,
        ], ['layout' => 'admin']);
    }

    public function createUser(): void
    {
        require_login('admin');
        $data = sanitize($_POST);
        $required = [
            'name' => 'Nome',
            'email' => 'Email',
            'role' => 'Ruolo',
        ];
        $errors = validate_required($data, $required);
        if (!empty($data['email']) && !validate_email($data['email'])) {
            $errors['email'] = 'Email non valida';
        }
        $username = trim((string) ($data['username'] ?? ''));
        if ($username !== '' && User::findByUsername($username)) {
            $errors['username'] = 'Username già in uso';
        }

        if ($errors) {
            flash('danger', implode('<br>', $errors));
            redirect('/admin/users');
        }
        if (User::findByEmail($data['email'])) {
            flash('danger', 'Esiste già un account con questa email');
            redirect('/admin/users');
        }

        User::create([
            'username' => $username !== '' ? $username : null,
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => password_hash($data['password'] ?? bin2hex(random_bytes(6)), PASSWORD_DEFAULT),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
        ]);

        flash('success', 'Utente creato');
        redirect('/admin/users');
    }

    public function updateUserRole(): void
    {
        require_login('admin');
        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'client';
        if (!in_array($role, ['client', 'admin'], true)) {
            flash('danger', 'Ruolo non valido');
            redirect('/admin/users');
        }
        if (!User::updateRole($userId, $role)) {
            flash('danger', 'Impossibile aggiornare il ruolo');
            redirect('/admin/users');
        }
        flash('success', 'Ruolo aggiornato');
        redirect('/admin/users');
    }

    public function spid(): void
    {
        require_login('admin');
        $requests = SPIDRequest::all();
        render('admin/spid', [
            'page_title' => 'Pratiche SPID',
            'requests' => $requests,
        ], ['layout' => 'admin']);
    }

    public function updateSpidStatus(): void
    {
        require_login('admin');
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            flash('danger', 'Stato non valido');
            redirect('/admin/spid');
        }
        SPIDRequest::updateStatus($id, $status);
        flash('success', 'Stato SPID aggiornato');
        redirect('/admin/spid');
    }

    public function simOrders(): void
    {
        require_login('admin');
        $orders = SimOrder::all();
        render('admin/sim-orders', [
            'page_title' => 'Pratiche telefonia',
            'orders' => $orders,
        ], ['layout' => 'admin']);
    }

    public function updateSimStatus(): void
    {
        require_login('admin');
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'processing', 'active', 'cancelled'], true)) {
            flash('danger', 'Stato non valido');
            redirect('/admin/sim-orders');
        }
        SimOrder::updateStatus($id, $status);
        flash('success', 'Stato pratica SIM aggiornato');
        redirect('/admin/sim-orders');
    }

    public function shipments(): void
    {
        require_login('admin');
        $shipments = Shipment::all();
        render('admin/shipments', [
            'page_title' => 'Spedizioni',
            'shipments' => $shipments,
        ], ['layout' => 'admin']);
    }

    public function updateShipmentStatus(): void
    {
        require_login('admin');
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'created';
        if (!in_array($status, ['created', 'in_transit', 'delivered', 'cancelled'], true)) {
            flash('danger', 'Stato non valido');
            redirect('/admin/shipments');
        }
        Shipment::updateStatus($id, $status);
        flash('success', 'Spedizione aggiornata');
        redirect('/admin/shipments');
    }

    public function tickets(): void
    {
        require_login('admin');
        $tickets = Ticket::all();
        render('admin/tickets', [
            'page_title' => 'Ticket di assistenza',
            'tickets' => $tickets,
        ], ['layout' => 'admin']);
    }

    public function replyTicket(): void
    {
        require_login('admin');
        $id = (int) ($_POST['id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        if ($message === '') {
            flash('danger', 'Il messaggio non può essere vuoto');
            redirect('/admin/tickets');
        }
        Ticket::appendMessage($id, [
            'from' => 'admin',
            'content' => $message,
            'at' => date('Y-m-d H:i'),
        ]);
        flash('success', 'Risposta inviata');
        redirect('/admin/tickets');
    }

    public function sendBroadcast(): void
    {
        require_login('admin');
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        if ($subject === '' || $body === '') {
            flash('danger', 'Oggetto e messaggio sono obbligatori');
            redirect('/admin/dashboard');
        }
        $users = User::allClients();
        $sent = 0;
        foreach ($users as $user) {
            if (send_mail($user['email'], $subject, nl2br($body))) {
                $sent++;
            }
        }
        flash('success', "Email inviate: {$sent}");
        redirect('/admin/dashboard');
    }
}
