<!-- Lena Strohmenger Beginn -->

<?php
session_start();

if (!isset($_SESSION['veranstalter_loginname'])) {
    header("Location: veranstalter_login.php");
    exit();
}
include_once 'dbh.php';

$meldung = "";

class Rennen extends Dbh
{

    //Neues Rennen anlegen
    public function rennenAnlegen($datum, $plz, $ort, $kilometer, $steigung, $hoehenmeter)
    {
        $sql = "INSERT INTO Rennen (Datum, PLZ, Ort, Kilometer, Steigung, Hoehenmeter, VeranstalterLoginName) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$datum, $plz, $ort, $kilometer, $steigung, $hoehenmeter, $_SESSION['veranstalter_loginname']]);
    }

}

// Formularverarbeitung
if (isset($_POST['anlegen'])) {
    $datum = $_POST['datum'];
    $plz = $_POST['plz'];
    $ort = $_POST['ort'];
    $kilometer = $_POST['kilometer'];
    $hoehenmeter = $_POST['hoehenmeter'];
    $maximale_steigung = $_POST['maximale_steigung'];

    if (!is_numeric($maximale_steigung)) {
        echo "<p>Maximale Steigung muss eine Zahl sein!</p>";

    } elseif ($maximale_steigung > 100) {
        echo "<p>Fehler: Maximale Steigung darf nicht größer als 100% sein!</p>";


    } elseif (!is_numeric($hoehenmeter)) {

        echo "<p>Höhenmeter muss eine Zahl sein!</p>";

    } elseif (!is_numeric($kilometer)) {

        echo "<p>Kilometer muss eine Zahl sein!</p>";

    } else {
        $rennen_anlegen = new Rennen();
        $rennen_anlegen->rennenAnlegen($datum, $plz, $ort, $kilometer, $maximale_steigung, $hoehenmeter);
        $meldung = "<p>Rennen erfolgreich angelegt!</p>";
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

    <p><a href="logout.php">Logout</a></p>

    <h2>Rennen-Ergebniserfassung</h2>
    <p><a href="rennen_ergebniserfassung.php">Rennen Ergebniserfassung</a></p>

    <h2>Neues Rennen anlegen</h2>

    <form action="" method="POST">
        <fieldset>
            <legend>Bitte gebe die Daten für das Rennen unten ein</legend>
            <p>
                <label for="rennid">Renn-ID</label><br>
                <input id="rennid" value="Wird automatisch vergeben" disabled>
            </p>
            <p>
                <label for="datum">Datum</label><br>
                <input id="datum" name="datum" type="date" required>
            </p>
            <p>
                <label for="plz">PLZ</label><br>
                <input id="plz" name="plz" maxlength="5" placeholder="Postleitzahl" required>
            </p>
            <p>
                <label for="ort">Ort</label><br>
                <input id="ort" name="ort" placeholder="Ort des Rennens" required>
            </p>
            <p>
                <label for="kilometer">Kilometer</label><br>
                <input type="number" id="kilometer" name="kilometer" placeholder="Zu fahrende Kilometer" step="0.1"
                    min="0" required> Km
            </p>
            <p>
                <label for="hoehenmeter">Höhenmeter</label><br>
                <input type="number" id="hoehenmeter" name="hoehenmeter" placeholder="Zu fahrende Höhenmeter" step="1"
                    min="0" required>
            </p>
            <p>
                <label for="maximale_steigung">Maximale Steigung (%)</label><br>
                <input type="number" id="maximale_steigung" name="maximale_steigung"
                    placeholder="Maximale Steigung in %" step="1" min="0" max="100" required> %
            </p>
            <p>
                <label>Veranstalter</label><br>
                <input value="<?php echo htmlentities($_SESSION['veranstalter_loginname']); ?>" readonly>
            </p>

            <p><input type="submit" name="anlegen" value="Anlegen">
            </p>
            <p><a href="index.php">Zurück zur Startseite</a></p>
        </fieldset>
    </form>

    <?php echo $meldung; ?>


</body>

</html>

<!-- Lena Strohmenger Ende -->