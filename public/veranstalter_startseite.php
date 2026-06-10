<!-- Lena Strohmenger Beginn -->

<?php
require_once('include/session_management.php');
sitzungStarten();
zugriffPruefen('veranstalter_loginname');

require_once('classes/Rennen.php');

$fehlermeldung = "";
$erfolgsmeldung = "";



// Formularverarbeitung
if (isset($_POST['anlegen'])) {
    if (!csrfTokenGueltig()) {
        $fehlermeldung = "Ungültige Anfrage. Bitte die Seite neu laden.";
    } else {
        $datum = $_POST['datum'];
        $plz = $_POST['plz'];
        $ort = $_POST['ort'];
        $kilometer = $_POST['kilometer'];
        $hoehenmeter = $_POST['hoehenmeter'];
        $maximale_steigung = $_POST['maximale_steigung'];

        if (!is_numeric($maximale_steigung)) {
            $fehlermeldung = "Maximale Steigung muss eine Zahl sein!";
        } elseif ($maximale_steigung > 100) {
            $fehlermeldung = "Fehler: Maximale Steigung darf nicht größer als 100% sein!";
        } elseif (!is_numeric($hoehenmeter)) {
            $fehlermeldung = "Höhenmeter muss eine Zahl sein!";
        } elseif (!is_numeric($kilometer)) {
            $fehlermeldung = "Kilometer muss eine Zahl sein!";
        } elseif (strlen($plz) != 5 || !ctype_digit($plz)) {
            $fehlermeldung = "Postleitzahl muss aus genau 5 Ziffern bestehen!";
        } else {
            $rennen_anlegen = new Rennen();
            $gespeichert = $rennen_anlegen->rennenAnlegen($datum, $plz, $ort, $kilometer, $maximale_steigung, $hoehenmeter, $_SESSION['veranstalter_loginname']);

            if ($gespeichert) {
                $erfolgsmeldung = "Rennen erfolgreich angelegt!";
            } else {
                $fehlermeldung = "Fehler beim Anlegen des Rennens.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veranstalter Startseite</title>

    <style>
        fieldset input {
            width: 250px;
        }
    </style>
</head>

<body>

    <h1>Willkommen <?php echo htmlentities($_SESSION['veranstalter_loginname']); ?>!</h1>

    <p>Du bist jetzt eingeloggt.</p>

    <form action="logout.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <button type="submit">Logout</button>
    </form>

    <h2>Rennen-Ergebniserfassung</h2>
    <p><a href="rennen_ergebniserfassung.php">Rennen Ergebniserfassung</a></p>

    <h2>Neues Rennen anlegen</h2>

    <?php if (!empty($fehlermeldung)): ?>
        <p><?php echo $fehlermeldung; ?></p>
    <?php endif; ?>

    <?php if (!empty($erfolgsmeldung)): ?>
        <p><?php echo $erfolgsmeldung; ?></p>
    <?php endif; ?>


    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <fieldset>
            <legend>Bitte gib die Daten für das Rennen unten ein</legend>

            <p>
                <label for="rennid">Renn-ID</label><br>
                <input id="rennid" type="text" value="Wird automatisch vergeben" readonly>
            </p>
            <p>
                <label for="datum">Datum</label><br>
                <input id="datum" name="datum" type="date" required>
            </p>
            <p>
                <label for="plz">PLZ</label><br>
                <input id="plz" name="plz" type="text" maxlength="5" placeholder="z. B. 70173" required>
            </p>
            <p>
                <label for="ort">Ort</label><br>
                <input id="ort" name="ort" type="text" placeholder="z. B. Stuttgart" required>
            </p>
            <p>
                <label for="kilometer">Kilometer</label><br>
                <input id="kilometer" name="kilometer" type="number" placeholder="z. B. 42.5" step="0.1" min="0.1"
                    required> km
            </p>
            <p>
                <label for="hoehenmeter">Höhenmeter</label><br>
                <input id="hoehenmeter" name="hoehenmeter" type="number" placeholder="z. B. 850" step="1" min="0"
                    required>
            </p>
            <p>
                <label for="maximale_steigung">Maximale Steigung (%)</label><br>
                <input id="maximale_steigung" name="maximale_steigung" type="number" placeholder="z. B. 12" step="0.1"
                    min="0" max="100" required> %
            </p>
            <p>
                <label for="veranstalter">Veranstalter</label><br>
                <input id="veranstalter" type="text"
                    value="<?php echo htmlentities($_SESSION['veranstalter_loginname']); ?>" readonly>
            </p>
            <p>
                <input type="submit" name="anlegen" value="Anlegen">
            </p>

            <p>
                <a href="index.php">Zurück zur Startseite</a>
            </p>
        </fieldset>
    </form>
</body>

</html>

<!-- Lena Strohmenger Ende -->