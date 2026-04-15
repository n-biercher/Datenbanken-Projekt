<?php
declare(strict_types=1);

function get_database_connection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'gruppe5';
    $user = getenv('DB_USER') ?: 'gruppe5';
    $password = getenv('DB_PASSWORD') ?: '';

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        throw new RuntimeException(
            'Die Datenbankverbindung konnte nicht aufgebaut werden. ' .
            'Prüfe DB_HOST, DB_PORT, DB_NAME, DB_USER und DB_PASSWORD.',
            0,
            $exception
        );
    }

    return $pdo;
}
