<?php
/**
 * Abmeldelogik
 * Nicolas Biercher
 */
include_once('include/session_management.php');
sitzungStarten();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfTokenGueltig()) {
    sitzungBeenden();
    $abgemeldet = true;
} else {
    $abgemeldet = false;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Abmelden</title>
</head>
<body>

<?php if ($abgemeldet): ?>
    <h1>Abgemeldet</h1>
    <p>Du wurdest erfolgreich abgemeldet.</p>
    <p><a href="index.php">Zurück zur Startseite</a></p>
<?php else: ?>
    <h1>Fehler</h1>
    <p>Ungültige Anfrage. Bitte die Seite neu laden.</p>
    <p><a href="index.php">Zurück zur Startseite</a></p>
<?php endif; ?>

</body>
</html>
<!-- Nicolas Biercher Ende -->
