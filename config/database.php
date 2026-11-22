<?php
/**
 * Konfigurasi koneksi basis data.
 *
 * Secara bawaan aplikasi memakai SQLite agar mudah dijalankan untuk
 * kebutuhan demonstrasi. Untuk MySQL, ubah DB_CONNECTION menjadi "mysql"
 * dan sesuaikan kredensial di bawah, lalu impor database/rewear.sql.
 */
return [
    'driver' => getenv('DB_CONNECTION') ?: 'sqlite',

    'sqlite' => [
        'path' => getenv('DB_DATABASE') ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'rewear.sqlite',
    ],

    'mysql' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'rewear',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
];
