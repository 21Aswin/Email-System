<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use Exception;

class AuthController
{
    public function showLogin(): void
    {
        View::render('auth/login', [
            'title' => 'Sign In - nkinfyx groups Webmail',
            'error' => Session::flash('error'),
            'success' => Session::flash('success')
        ], 'layouts/auth');
    }

    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            AuthService::attempt($email, $password);
            Session::flash('success', 'Welcome back!');
            Response::redirect('/mail/inbox');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
            Response::redirect('/login');
        }
    }

    public function showRegister(): void
    {
        View::render('auth/register', [
            'title' => 'Create Account - nkinfyx groups Webmail',
            'error' => Session::flash('error'),
            'success' => Session::flash('success')
        ], 'layouts/auth');
    }

    public function register(): void
    {
        $name = $_POST['display_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($password !== $passwordConfirm) {
            Session::flash('error', 'Passwords do not match.');
            Response::redirect('/register');
            return;
        }

        try {
            AuthService::register($name, $email, $password);
            Session::flash('success', 'Account created successfully! Welcome to nkinfyx groups.');
            Response::redirect('/mail/inbox');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
            Response::redirect('/register');
        }
    }

    public function logout(): void
    {
        AuthService::logout();
        Response::redirect('/login');
    }
}
