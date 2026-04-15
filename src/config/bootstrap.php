<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once dirname(__DIR__) . '/models/AuthService.php';
require_once dirname(__DIR__) . '/controllers/HomeController.php';
require_once dirname(__DIR__) . '/controllers/AuthController.php';
require_once dirname(__DIR__) . '/controllers/DashboardController.php';
require_once dirname(__DIR__) . '/controllers/AppController.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function render_view(string $view, array $data = []): string
{
    $viewFile = dirname(__DIR__) . '/views/' . $view . '.php';

    if (!is_file($viewFile)) {
        throw new RuntimeException('View nicht gefunden: ' . $view);
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $viewFile;
    return (string) ob_get_clean();
}

function render_page(string $title, string $view, array $data = []): string
{
    $content = render_view($view, $data);

    return render_view('layout/main', [
        'title' => $title,
        'content' => $content,
    ]);
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): void
{
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        throw new RuntimeException('Ungültige Anfrage. Bitte Formular erneut absenden.');
    }
}

function set_flash(string $message): void
{
    $_SESSION['flash_message'] = $message;
}

function pull_flash(): ?string
{
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $message = (string) $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $message;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['auth_user'] = $user;
}

function current_user(): ?array
{
    return isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])
        ? $_SESSION['auth_user']
        : null;
}

function require_login(): void
{
    if (current_user() === null) {
        set_flash('Bitte melde dich zuerst an.');
        redirect('/auth?mode=login&role=teamchef');
    }
}

function logout_user(): void
{
    unset($_SESSION['auth_user']);
    session_regenerate_id(true);
}
