<?php
declare(strict_types=1);

$basePath = dirname(__DIR__);
require $basePath . '/src/config/bootstrap.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$appController = new AppController();
$appController->dispatch($requestPath, $_SERVER['REQUEST_METHOD'] ?? 'GET');
