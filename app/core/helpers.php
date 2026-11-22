<?php

function config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require dirname(__DIR__, 2) . '/config/config.php';
    }
    if ($key === null) {
        return $config;
    }
    return $config[$key] ?? $default;
}

function base_url(): string
{
    return rtrim((string) config('base_url', ''), '/');
}

function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    if ($path === '/') {
        return base_url() . '/';
    }
    return base_url() . $path;
}

function asset(string $path): string
{
    return base_url() . '/assets/' . ltrim($path, '/');
}

function upload_url(string $path): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return base_url() . '/' . ltrim($path, '/');
}

function media_url(?string $path, string $fallback = 'assets/images/products/kaos-putih.jpg'): string
{
    if (!$path) {
        return asset($fallback);
    }
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return base_url() . '/' . ltrim($path, '/');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-') ?: 'produk';
}

function old(string $key, mixed $default = ''): string
{
    static $old = null;
    if ($old === null) {
        $old = Session::pullOld();
    }
    return e($old[$key] ?? $default);
}

function csrf_field(): string
{
    return Csrf::field();
}

function input(string $key, mixed $default = ''): mixed
{
    return trim((string) ($_POST[$key] ?? $_GET[$key] ?? $default));
}

function post(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function query(string $key, mixed $default = ''): mixed
{
    return $_GET[$key] ?? $default;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function request_wants_json(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    return str_contains($accept, 'application/json') || strtolower($requestedWith) === 'xmlhttprequest';
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function redirect(string $path): never
{
    if (!preg_match('#^https?://#', $path)) {
        $path = url($path);
    }
    header('Location: ' . $path, true, 302);
    exit;
}

function back(string $fallback = '/'): never
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer && str_starts_with($referer, 'http')) {
        header('Location: ' . $referer, true, 302);
        exit;
    }
    redirect($fallback);
}

function rupiah(int|float|string|null $amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function tanggal(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '-';
    }
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '-';
    }
    $result = date('j', $timestamp) . ' ' . $bulan[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    if ($withTime) {
        $result .= ', ' . date('H.i', $timestamp);
    }
    return $result;
}

function waktu_relatif(?string $date): string
{
    if (!$date) {
        return '-';
    }
    $diff = time() - strtotime($date);
    if ($diff < 60) {
        return 'Baru saja';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . ' menit lalu';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . ' jam lalu';
    }
    if ($diff < 604800) {
        return floor($diff / 86400) . ' hari lalu';
    }
    return tanggal($date);
}

function order_statuses(): array
{
    return [
        'menunggu' => 'Menunggu Konfirmasi',
        'dikonfirmasi' => 'Dikonfirmasi',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];
}

function order_status_label(?string $status): string
{
    return order_statuses()[$status] ?? 'Tidak diketahui';
}

function order_status_class(?string $status): string
{
    return match ($status) {
        'menunggu' => 'status--waiting',
        'dikonfirmasi' => 'status--confirmed',
        'diproses' => 'status--processing',
        'dikirim' => 'status--shipped',
        'selesai' => 'status--done',
        'dibatalkan' => 'status--cancelled',
        default => 'status--neutral',
    };
}

function order_status_flow(): array
{
    return ['menunggu', 'dikonfirmasi', 'diproses', 'dikirim', 'selesai'];
}

function conditions(): array
{
    return ['Sangat Baik', 'Baik', 'Cukup Baik'];
}

function sizes(): array
{
    return ['XS', 'S', 'M', 'L', 'XL', 'XXL', '36', '37', '38', '39', '40', '41', '42', '43', '44', 'Satu Ukuran'];
}

function product_types(): array
{
    return [
        'atasan' => 'Atasan',
        'bawahan' => 'Bawahan',
        'outerwear' => 'Outerwear',
        'dress' => 'Dress',
        'sepatu' => 'Sepatu',
    ];
}

function role_label(?string $role): string
{
    return match ($role) {
        'admin' => 'Administrator',
        'penjual' => 'Penjual',
        'pembeli' => 'Pembeli',
        default => 'Pengguna',
    };
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $parts = array_filter($parts);
    if (count($parts) === 1) {
        return mb_strtoupper(mb_substr($parts[0], 0, 2));
    }
    return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
}

function avatar_color(string $seed): string
{
    $colors = ['#c7654b', '#6d7457', '#a47a54', '#677a82', '#9b6d72', '#7b6e96', '#8d7d58'];
    return $colors[abs(crc32($seed)) % count($colors)];
}

function active_nav(string $path): string
{
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_url();
    if ($base && str_starts_with($current, $base)) {
        $current = substr($current, strlen($base)) ?: '/';
    }
    $path = '/' . trim($path, '/');
    if ($path === '/') {
        return $current === '/' ? 'is-active' : '';
    }
    return str_starts_with($current, $path) ? 'is-active' : '';
}

function is_current(string $path): bool
{
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_url();
    if ($base && str_starts_with($current, $base)) {
        $current = substr($current, strlen($base)) ?: '/';
    }
    return rtrim($current, '/') === rtrim('/' . trim($path, '/'), '/');
}

function product_image(?string $path, string $fallback = 'assets/images/products/kaos-putih.jpg'): string
{
    return media_url($path, $fallback);
}

function excerpt(?string $text, int $limit = 120): string
{
    $text = trim((string) $text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit)) . '…';
}

function status_produk_label(?string $status): string
{
    return match ($status) {
        'aktif' => 'Aktif',
        'terjual' => 'Terjual',
        'ditinjau' => 'Ditinjau',
        'dihapus' => 'Dihapus',
        default => 'Tidak diketahui',
    };
}

function status_produk_class(?string $status): string
{
    return match ($status) {
        'aktif' => 'status--done',
        'terjual' => 'status--shipped',
        'ditinjau' => 'status--waiting',
        'dihapus' => 'status--cancelled',
        default => 'status--neutral',
    };
}

function icon(string $name, string $class = ''): string
{
    $icons = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
        'bag' => '<path d="M6 8h12l1 13H5L6 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>',
        'chat' => '<path d="M21 12a8 8 0 0 1-8 8H4l2.2-3.3A8 8 0 1 1 21 12Z"/><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01"/>',
        'arrow-right' => '<path d="M4 12h16"/><path d="m14 6 6 6-6 6"/>',
        'arrow-up-right' => '<path d="M7 17 17 7"/><path d="M7 7h10v10"/>',
        'arrow-left' => '<path d="M20 12H4"/><path d="m10 6-6 6 6 6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'chevron-left' => '<path d="m15 6-6 6 6 6"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'truck' => '<path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'box' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
        'shield' => '<path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-4.8"/>',
        'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>',
        'map-pin' => '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone' => '<path d="M5 3h4l2 5-2.5 1.5a15 15 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2C10 21 3 14 3 5a2 2 0 0 1 2-2Z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'tag' => '<path d="M3 11V4a1 1 0 0 1 1-1h7l10 10-8 8L3 11Z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'hanger' => '<path d="M12 7a2.5 2.5 0 1 1 2.5 2.5c-.9.2-1.5.9-1.5 1.8V13"/><path d="m12 13-8 5.5a1 1 0 0 0 .6 1.8h14.8a1 1 0 0 0 .6-1.8L12 13Z"/>',
        'sparkle' => '<path d="M12 2.5 13.8 9l6.5 1.8-6.5 1.8L12 19l-1.8-6.4-6.5-1.8L10.2 9 12 2.5Z"/><path d="m19 17 .7 2.3L22 20l-2.3.7L19 23l-.7-2.3L16 20l2.3-.7L19 17Z"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'logout' => '<path d="M10 4H5v16h5M14 8l4 4-4 4M8 12h10"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5v.2h-4v-.2a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3v-4h.2a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.5V3h4v.2a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.5 1h.2v4h-.2a1.7 1.7 0 0 0-1.5 1Z"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 5a3.5 3.5 0 0 1 0 7M17 14.5a5.5 5.5 0 0 1 4.5 5.5"/>',
        'shirt' => '<path d="m8 3 4 2 4-2 4 4-3 3v11H7V10L4 7l4-4Z"/><path d="M9 3a3 3 0 0 0 6 0"/>',
        'shoe' => '<path d="M3 15h8l3-4 3 2c2 1 4 1 4 4v2H3v-4Z"/><path d="M3 15v-4l3 1 2 3"/>',
        'jacket' => '<path d="m8 3 4 3 4-3 4 4-3 3v11h-3V9l-2 2-2-2v12H7V10L4 7l4-4Z"/>',
        'pants' => '<path d="M7 3h10l1 18h-4l-2-9-2 9H6L7 3Z"/><path d="M7 7h10"/>',
        'accessory' => '<path d="M5 9h14l1 12H4L5 9Z"/><path d="M8 9V7a4 4 0 0 1 8 0v2"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14M10 11v6M14 11v6"/>',
        'edit' => '<path d="M4 20h4L20 8l-4-4L4 16v4Z"/><path d="m13 7 4 4"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'alert' => '<path d="M12 3 2.5 20h19L12 3Z"/><path d="M12 9v5M12 17.5h.01"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5h.01"/>',
        'leaf' => '<path d="M20 4C11 4 5 8 5 15c0 2 1 4 3 5C12 19 20 14 20 4Z"/><path d="M4 21c2-6 6-9 12-12"/>',
        'recycle' => '<path d="m7 8 3-5 4 6M5 12l-2 4 4 4M17 8l3 5-3 3M9 21h8l-2-4M5 16h5"/>',
        'ruler' => '<path d="m4 16 12-12 4 4L8 20l-4-4Z"/><path d="m9 11 2 2M12 8l2 2M15 5l2 2"/>',
        'palette' => '<path d="M12 3a9 9 0 1 0 0 18h1.5a2 2 0 0 0 0-4H13a2 2 0 0 1 0-4h3a5 5 0 0 0 5-5c0-3-4-5-9-5Z"/><circle cx="7.5" cy="10" r="1"/><circle cx="10" cy="6.5" r="1"/><circle cx="15" cy="7" r="1"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'play' => '<path d="m8 5 11 7-11 7V5Z"/>',
        'pause' => '<path d="M8 5v14M16 5v14"/>',
        'rotate' => '<path d="M20 11a8 8 0 1 0-2.3 5.7"/><path d="M20 4v7h-7"/>',
        'arrow-down' => '<path d="M12 4v16"/><path d="m6 14 6 6 6-6"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'credit-card' => '<rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M2.5 10h19M6 15h4"/>',
    ];

    $path = $icons[$name] ?? $icons['info'];
    $class = trim('icon ' . $class);
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

function star_markup(int|float $rating, string $class = ''): string
{
    $rounded = round($rating * 2) / 2;
    $html = '<span class="stars ' . e($class) . '" aria-label="' . e(number_format($rating, 1, ',', '.')) . ' dari 5">';
    for ($i = 1; $i <= 5; $i++) {
        $state = $i <= $rounded ? 'is-filled' : (($i - 0.5) === $rounded ? 'is-half' : '');
        $html .= '<span class="star ' . $state . '">' . icon('star') . '</span>';
    }
    $html .= '</span>';
    return $html;
}

function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran foto melebihi batas 4 MB.',
        UPLOAD_ERR_PARTIAL => 'Foto gagal diunggah secara utuh. Coba lagi.',
        UPLOAD_ERR_NO_FILE => 'Tidak ada foto yang dipilih.',
        default => 'Foto gagal diunggah. Silakan coba lagi.',
    };
}

function body_page(?string $page = null): string
{
    $page = $page ?: '';
    return 'page-' . ($page ?: 'default');
}
