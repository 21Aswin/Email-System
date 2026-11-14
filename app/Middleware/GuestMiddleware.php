<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;
use App\Core\Response;

class GuestMiddleware
{
    public function handle(): bool
    {
        Session::start();
        $user = Session::get('user');

        if ($user && !empty($user['id'])) {
            Response::redirect('/mail/inbox');
            return false;
        }

        return true;
    }
}
