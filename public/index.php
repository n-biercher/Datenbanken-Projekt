<?php
declare(strict_types=1);

// Minimal front controller for the web root.
$basePath = dirname(__DIR__);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Future bootstrap location, for example:
// require $basePath . '/src/config/bootstrap.php';

if ($requestPath === '/' || $requestPath === '/index.php') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Datenbanken-Projekt</h1>';
    echo '<p>Der Front-Controller unter /public/index.php ist aktiv.</p>';
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo '404 Not Found';
