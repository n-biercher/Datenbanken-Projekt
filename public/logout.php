<?php
/**
 * Abmeldelogik
 * Nicolas Biercher
 */
include_once('include/session_management.php');
sitzungStarten();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfTokenGueltig()) {
    sitzungBeenden();
    header('Location: index.php?status=abgemeldet');
} else {
    header('Location: index.php?fehler=ungueltige_anfrage');
}
exit();
// Nicolas Biercher Ende
