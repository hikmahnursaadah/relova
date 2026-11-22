<?php

class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }

        self::$loaded = true;
        $id = (int) ($_SESSION['user_id'] ?? 0);
        if ($id <= 0) {
            return null;
        }

        $model = new UserModel();
        $user = $model->find($id);
        if (!$user || $user['status'] !== 'aktif') {
            unset($_SESSION['user_id']);
            self::$user = null;
            return null;
        }

        self::$user = $user;
        return self::$user;
    }

    public static function refresh(): void
    {
        self::$loaded = false;
        self::$user = null;
        self::user();
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    public static function isSeller(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        if (in_array($user['role'], ['penjual', 'admin'], true)) {
            return true;
        }
        return (new ProductModel())->countForSeller((int) $user['id']) > 0;
    }

    public static function attempt(string $email, string $password): bool
    {
        $model = new UserModel();
        $user = $model->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        if ($user['status'] !== 'aktif') {
            return false;
        }

        Session::regenerate();
        $_SESSION['user_id'] = (int) $user['id'];
        self::$loaded = false;
        self::$user = null;
        self::user();
        return true;
    }

    public static function loginById(int $id): void
    {
        Session::regenerate();
        $_SESSION['user_id'] = $id;
        self::$loaded = false;
        self::$user = null;
        self::user();
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        self::$user = null;
        self::$loaded = true;
    }

    public static function requireLogin(?string $message = null): void
    {
        if (!self::check()) {
            if (request_wants_json()) {
                json_response(['ok' => false, 'message' => $message ?: 'Silakan masuk terlebih dahulu.'], 401);
            }
            Session::flash('info', $message ?: 'Silakan masuk untuk melanjutkan.');
            $next = $_SERVER['REQUEST_URI'] ?? '/';
            redirect('/masuk?next=' . urlencode($next));
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin('Silakan masuk sebagai administrator.');
        if (!self::isAdmin()) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Akses ditolak'], 'main');
            exit;
        }
    }

    public static function safeNext(?string $next, string $fallback = '/'): string
    {
        if (!$next) {
            return $fallback;
        }
        if (!str_starts_with($next, '/') || str_starts_with($next, '//')) {
            return $fallback;
        }
        return $next;
    }
}
