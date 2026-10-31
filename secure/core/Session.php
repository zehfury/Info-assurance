<?php
declare(strict_types=1);

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_name((string) config('session_name'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true); // prevent session fixation
        $_SESSION['user'] = [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'name'     => trim($user['first_name'] . ' ' . $user['last_name']),
            'role'     => $user['role'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function pullFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    public static function csrfToken(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function verifyCsrf(mixed $token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $token);
    }
}
