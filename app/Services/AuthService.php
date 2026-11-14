<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Models\User;
use Exception;

class AuthService
{
    /**
     * Authenticate user credentials
     */
    public static function attempt(string $email, string $password): array
    {
        $email = strtolower(trim($email));

        // Basic brute-force throttling using session
        $failKey = 'login_fails_' . md5($email);
        $fails = (int)Session::get($failKey, 0);
        $lockKey = 'login_lockout_' . md5($email);
        $lockoutUntil = (int)Session::get($lockKey, 0);

        if ($lockoutUntil > time()) {
            $remaining = $lockoutUntil - time();
            throw new Exception("Too many failed login attempts. Please wait {$remaining} seconds before trying again.");
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $fails++;
            Session::set($failKey, $fails);
            if ($fails >= 5) {
                Session::set($lockKey, time() + 300); // 5 min lockout
                Session::remove($failKey);
                throw new Exception("Account temporarily locked due to 5 failed attempts. Please retry after 5 minutes.");
            }
            throw new Exception("Invalid email address or password.");
        }

        if ($user['status'] !== 'active') {
            throw new Exception("Your account is currently {$user['status']}. Please contact administrator.");
        }

        // Reset fail counter
        Session::remove($failKey);
        Session::remove($lockKey);

        // Regenerate session ID to prevent session fixation
        Session::regenerate();

        // Store user in session
        $safeUserData = [
            'id' => (int)$user['id'],
            'display_name' => $user['display_name'],
            'email' => $user['email'],
            'status' => $user['status']
        ];
        Session::set('user', $safeUserData);

        return $safeUserData;
    }

    /**
     * Register a new user
     */
    public static function register(string $displayName, string $email, string $password): array
    {
        $displayName = trim($displayName);
        $email = strtolower(trim($email));

        if (mb_strlen($displayName) < 2) {
            throw new Exception("Display name must be at least 2 characters.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please provide a valid email address.");
        }

        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long.");
        }

        if (User::findByEmail($email)) {
            throw new Exception("An account with this email address already exists.");
        }

        $userId = User::create($displayName, $email, $password);
        $newUser = User::findById($userId);

        // Log the newly registered user in
        Session::regenerate();
        $safeUserData = [
            'id' => (int)$newUser['id'],
            'display_name' => $newUser['display_name'],
            'email' => $newUser['email'],
            'status' => $newUser['status']
        ];
        Session::set('user', $safeUserData);

        return $safeUserData;
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int)$u['id'] : null;
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
