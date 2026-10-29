<?php
declare(strict_types=1);

function config(): array
{
    static $config = null;
    if ($config === null) {
        $fichero = __DIR__ . '/../config.local.php';
        if (!is_file($fichero)) {
            http_response_code(500);
            exit('Falta config.local.php. Cópialo desde config.example.php y ajústalo.');
        }
        $config = require $fichero;
    }
    return $config;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = config()['db'];
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['port'], $c['name']);
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
