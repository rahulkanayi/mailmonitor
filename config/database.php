<?php
declare(strict_types=1);

function database(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $envFile = dirname(__DIR__) . '/.env';
    $fileConfig = is_file($envFile) ? parse_ini_file($envFile, false, INI_SCANNER_RAW) : [];
    if ($fileConfig === false) {
        throw new RuntimeException('Unable to read database configuration from .env.');
    }

    $config = static fn (string $key, string $default): string =>
        (getenv($key) !== false ? (string) getenv($key) : (string) ($fileConfig[$key] ?? $default));

    $host = $config('DB_HOST', '127.0.0.1');
    $port = $config('DB_PORT', '3306');
    $name = $config('DB_NAME', 'mailmonitor');
    $user = $config('DB_USER', 'mailmonitor');
    $password = $config('DB_PASSWORD', '');

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $name
    );

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
