<?php
/**
 * Nicolas Biercher
 */

// Direktaufruf über den Browser verhindern
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(403);
    exit();
}

define('SITZUNG_TIMEOUT_SEKUNDEN', 1800);

function sitzungStarten(): void
{
    session_start();

    // Session-ID beim ersten Aufruf neu vergeben
    if (empty($_SESSION['sitzung_gestartet'])) {
        session_regenerate_id(true);
        $_SESSION['sitzung_gestartet'] = true;
    }

    // Fingerabdruck aus User-Agent und IP-Adresse
    $fingerabdruck = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . $_SERVER['REMOTE_ADDR']);

    if (empty($_SESSION['fingerabdruck'])) {
        $_SESSION['fingerabdruck'] = $fingerabdruck;
    } elseif (!hash_equals($_SESSION['fingerabdruck'], $fingerabdruck)) {
        // Die Sitzung wird beendet, wenn der Fingerabdruck nicht zu dem der Session passt
        sitzungBeenden();
        header("Location: index.php?fehler=sitzung_ungueltig");
        exit();
    }

    // Inaktivitäts-Timeout prüfen: Sitzung nach 30 Minuten ohne Aktivität beenden
    if (!empty($_SESSION['letzte_aktivitaet'])) {
        $inaktiv_seit = time() - $_SESSION['letzte_aktivitaet'];

        if ($inaktiv_seit > SITZUNG_TIMEOUT_SEKUNDEN) {
            sitzungBeenden();
            header("Location: index.php?fehler=sitzung_abgelaufen");
            exit();
        }
    }

    $_SESSION['letzte_aktivitaet'] = time();

    // CSRF-Token einmal pro Sitzung setzen
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function sitzungBeenden(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_destroy();
}

function zugriffPruefen(string $sitzungsschluessel): void
{
    if (!isset($_SESSION[$sitzungsschluessel])) {
        header("Location: index.php?fehler=kein_zugriff");
        exit();
    }
}

function csrfTokenGueltig(): bool
{
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// Nicolas Biercher Ende