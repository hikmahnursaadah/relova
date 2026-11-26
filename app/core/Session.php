<?php

class Session
{
    private const REMEMBER_COOKIE = 'rewear_remember';
    private const REMEMBER_SECONDS = 30 * 86400;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $remember = ($_COOKIE[self::REMEMBER_COOKIE] ?? '') === '1';
        if ($remember) {
            ini_set('session.gc_maxlifetime', (string) self::REMEMBER_SECONDS);
        }

        session_name('rewear_session');
        session_set_cookie_params([
            'lifetime' => $remember ? self::REMEMBER_SECONDS : 0,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function remember(): void
    {
        if (!ini_get('session.use_cookies')) {
            return;
        }

        $options = self::cookieOptions(time() + self::REMEMBER_SECONDS);
        setcookie(self::REMEMBER_COOKIE, '1', $options);
        setcookie(session_name(), session_id(), $options);
        $_COOKIE[self::REMEMBER_COOKIE] = '1';
    }

    public static function forgetRemember(): void
    {
        if (!ini_get('session.use_cookies')) {
            return;
        }

        $expired = self::cookieOptions(time() - 42000);
        setcookie(self::REMEMBER_COOKIE, '', $expired);
        unset($_COOKIE[self::REMEMBER_COOKIE]);

        setcookie(session_name(), session_id(), self::cookieOptions(0));
    }

    private static function cookieOptions(int $expires): array
    {
        $params = session_get_cookie_params();

        return [
            'expires' => $expires,
            'path' => $params['path'] ?: '/',
            'domain' => $params['domain'] ?? '',
            'secure' => (bool) $params['secure'],
            'httponly' => (bool) $params['httponly'],
            'samesite' => 'Lax',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function pullFlash(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $messages;
    }

    public static function old(array $input): void
    {
        $_SESSION['_old'] = $input;
    }

    public static function pullOld(): array
    {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
        return $old;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
