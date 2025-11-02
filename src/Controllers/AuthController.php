<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Exception;
use function app_log;
use function clear_login_attempts;
use function flash;
use function has_too_many_attempts;
use function is_logged_in;
use function login_user;
use function logout_user;
use function redirect;
use function record_login_attempt;
use function render;
use function sanitize;
use function validate_email;
use function validate_required;
use function validate_string_length;

class AuthController
{
    public function showLogin(): void
    {
        if (is_logged_in()) {
            redirect('/client/dashboard');
        }

        render('auth/login', [
            'page_title' => 'Accedi al portale',
        ], ['layout' => 'public']);
    }

    public function login(): void
    {
        $data = sanitize($_POST);
        $email = strtolower($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!validate_email($email) || $password === '') {
            flash('danger', 'Credenziali non valide');
            redirect('/auth/login');
        }

        if (has_too_many_attempts($email)) {
            flash('danger', 'Troppi tentativi di accesso. Riprova tra 15 minuti.');
            redirect('/auth/login');
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            record_login_attempt($email);
            flash('danger', 'Email o password errati');
            redirect('/auth/login');
        }

        clear_login_attempts($email);
        login_user($user);
        User::touchLogin((int) $user['id']);

        if ($user['role'] === 'admin') {
            redirect('/admin/dashboard');
        }
        redirect('/client/dashboard');
    }

    public function showRegister(): void
    {
        if (is_logged_in()) {
            redirect('/client/dashboard');
        }

        render('auth/register', [
            'page_title' => 'Registrati al portale',
        ], ['layout' => 'public']);
    }

    public function register(): void
    {
        $data = sanitize($_POST);
        $required = [
            'name' => 'Nome e cognome',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirm' => 'Conferma password',
        ];
        $errors = validate_required($data, $required);

        if (!empty($data['email']) && !validate_email($data['email'])) {
            $errors['email'] = 'Email non valida';
        }
        if (!empty($data['password']) && !validate_string_length($data['password'], 8, 255)) {
            $errors['password'] = 'La password deve contenere almeno 8 caratteri';
        }
        if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) {
            $errors['password_confirm'] = 'Le password non coincidono';
        }

        if ($errors) {
            flash('danger', implode('<br>', $errors));
            redirect('/auth/register');
        }

        $email = strtolower($data['email']);
        if (User::findByEmail($email)) {
            flash('danger', 'Esiste già un account registrato con questa email');
            redirect('/auth/register');
        }

        try {
            $userId = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
                'phone' => $data['phone'] ?? null,
                'role' => 'client',
            ]);
        } catch (Exception $exception) {
            app_log('auth', 'Registrazione fallita', ['error' => $exception->getMessage()]);
            flash('danger', 'Registrazione non riuscita. Riprova più tardi.');
            redirect('/auth/register');
        }

        $user = User::find((int) $userId);
        if ($user) {
            login_user($user);
            flash('success', 'Registrazione completata. Benvenuto!');
            redirect('/client/dashboard');
        }

        flash('danger', 'Registrazione non riuscita. Riprova.');
        redirect('/auth/register');
    }

    public function logout(): void
    {
        logout_user();
        flash('success', 'Sei uscito dal portale.');
        redirect('/');
    }
}
