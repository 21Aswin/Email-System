<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;
use App\Core\Response;

class CsrfMiddleware
{
    public function handle(): bool
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        // Check header first (X-CSRF-TOKEN), then POST body (_csrf_token)
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? null;

        // If JSON payload has _csrf_token
        if (!$token && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['_csrf_token'] ?? null;
        }

        if (!Session::validateCsrfToken($token)) {
            $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
            if ($isApi) {
                Response::error('Invalid or expired CSRF token.', 403);
                return false;
            }

            Session::flash('error', 'Session expired. Please try again.');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? '/login');
            return false;
        }

        return true;
    }
}
