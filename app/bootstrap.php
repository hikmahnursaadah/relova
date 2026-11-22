<?php

declare(strict_types=1);

define('APP_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/config.php';
date_default_timezone_set($config['timezone']);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

require BASE_PATH . '/app/core/helpers.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/app/core/' . $class . '.php',
        BASE_PATH . '/app/models/' . $class . '.php',
        BASE_PATH . '/app/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

Session::start();
Auth::user();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!Csrf::validate()) {
        http_response_code(419);
        if (request_wants_json()) {
            json_response(['ok' => false, 'message' => 'Sesi keamanan kedaluwarsa. Muat ulang halaman.'], 419);
        }
        Session::flash('error', 'Sesi keamanan kedaluwarsa. Silakan muat ulang halaman lalu coba lagi.');
        back('/');
    }
}

$router = new Router();
require BASE_PATH . '/app/routes.php';

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
