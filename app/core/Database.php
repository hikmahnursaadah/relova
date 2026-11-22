<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $driver = $config['driver'];

        if ($driver === 'sqlite') {
            $path = $config['sqlite']['path'];
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $isNew = !file_exists($path);
            self::$pdo = new PDO('sqlite:' . $path);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
            self::$pdo->exec('PRAGMA journal_mode = WAL');
            self::$pdo->exec('PRAGMA busy_timeout = 5000');

            if ($isNew) {
                self::migrateSqlite(self::$pdo);
                (new Seeder())->run(self::$pdo);
            }
            return self::$pdo;
        }

        $db = $config['mysql'];
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['database'], $db['charset']);
        self::$pdo = new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return self::$pdo;
    }

    private static function migrateSqlite(PDO $pdo): void
    {
        $schema = file_get_contents(dirname(__DIR__, 2) . '/database/sqlite.sql');
        if ($schema === false) {
            throw new RuntimeException('Skema basis data tidak ditemukan.');
        }
        $pdo->exec($schema);
    }
}
