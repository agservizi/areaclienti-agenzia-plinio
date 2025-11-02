<?php

declare(strict_types=1);

namespace App\Controllers;

use function current_user;
use function is_logged_in;
use function redirect;
use function render;

class PageController
{
    public function landing(): void
    {
        if (is_logged_in()) {
            $user = current_user();
            if ($user && $user['role'] === 'admin') {
                redirect('/admin/dashboard');
            }
            redirect('/client/dashboard');
        }
        redirect('/auth/login');
    }
}
