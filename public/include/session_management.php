<!-- Nicolas Biercher Beginn -->

<?php

define('SITZUNG_TIMEOUT_SEKUNDEN', 1800); // 30 Minuten Inaktivität

function sitzungStarten(): void
{
    session_start();

    // neue Sitzungs-ID beim ersten Start vergeben
    if (empty($_SESSION['sitzung_gestartet'])) {
        session_regenerate_id(true);
        $_SESSION['sitzung_gestartet'] = true;
    }

    // Fingerabdruck aus Browser und IP initialisieren
    $fingerabdruck = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);

    if (empty($_SESSION['fingerabdruck'])) {
        $_SESSION['fingerabdruck'] = $fingerabdruck;
    } elseif (!hash_equals($_SESSION['fingerabdruck'], $fingerabdruck)) {
        sitzungBeenden();
        header("Location: index.php?fehler=sitzung_ungueltig");
        exit();
    }

    // Sitzungs-Timeout prüfen
    if (!empty($_SESSION['letzte_aktivitaet'])) {
        $inaktiv_seit = time() - $_SESSION['letzte_aktivitaet'];

        if ($inaktiv_seit > SITZUNG_TIMEOUT_SEKUNDEN) {
            sitzungBeenden();
            header("Location: index.php?fehler=sitzung_abgelaufen");
            exit();
        }
    }

    // Zeitstempel der letzten Aktivität aktualisieren
    $_SESSION['letzte_aktivitaet'] = time();

    // CSRF-Token generieren, falls noch nicht vorhanden
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function sitzungBeenden(): void
{
    $_SESSION = [];
    session_destroy();
}

function zugriffPruefen(string $sitzungsschluessel, string $weiterleitungsziel = 'index.php?fehler=kein_zugriff'): void
{
    if (!isset($_SESSION[$sitzungsschluessel])) {
        header("Location: $weiterleitungsziel");
        exit();
    }
}

function csrfTokenGueltig(): bool
{
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

?>

<!-- Nicolas Biercher Ende -->