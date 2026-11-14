<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;
use App\Core\Response;

class AuthMiddleware
{
    public function handle(): bool
    {
        Session::start();
        $user = Session::get('user');

        if (!$user || empty($user['id'])) {
            $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
            if ($isApi) {
                Response::error('Unauthorized. Please log in.', 401);
                return false;
            }

            Session::flash('error', 'Please sign in to access your mailbox.');
            Response::redirect('/login');
            return false;
        }

        return true;
    }
}
