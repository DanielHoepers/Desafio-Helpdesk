<?php
function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;

    $env = [];
    $envPath = __DIR__ . '/../.env';
    if (is_file($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line[0] === '#') continue;
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            if ($k !== '') $env[$k] = $v;
        }
    }

    $host = $env['DB_HOST'];
    $port = $env['DB_PORT'];
    $name = $env['DB_NAME'];
    $user = $env['DB_USER'];
    $pass = $env['DB_PASS'];

    $odbc = "pgsql:host=$host;port=$port;dbname=$name";

    $pdo = new PDO($odbc, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}
