<?php
declare(strict_types=1);

// Minimal front controller for the web root.
$basePath = dirname(__DIR__);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Future bootstrap location, for example:
// require $basePath . '/src/config/bootstrap.php';

if ($requestPath === '/' || $requestPath === '/index.php') {
    header('Content-Type: text/html; charset=utf-8');
    echo <<<'HTML'
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stadtradeln</title>
</head>
<body>
    <h1>Willkommen zum Stadtradeln</h1>
    <p>Bitte wähle eine der folgenden Optionen:</p>

    <ul>
        <li><a href="/gruppe/register">Neue Gruppe registrieren</a></li>
        <li><a href="/benutzer/register">Benutzer registrieren (Kilometerfassung)</a></li>
        <li><a href="/benutzer/login">Benutzer-Login</a></li>
        <li><a href="/admin/login">Admin Login</a></li>
        <li><a href="/admin/register">Admin Registrierung</a></li>
    </ul>
    <hr>
</body>
</html>
HTML;
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo '404 Not Found';
