
<?php
/* Lena Strohmenger Beginn
Seite auf der Veranstalter sich einloggen oder neu registrieren können */

require_once('include/session_management.php');
sitzungStarten();

require_once('classes/Veranstalter.php');

$fehlermeldung = "";

if (isset($_POST['registrieren'])) {
    if (!csrfTokenGueltig()) {
        $fehlermeldung = "Ungültige Anfrage. Bitte die Seite neu laden.";
    } else {
        $loginname = $_POST['veranstalter_loginname'];
        $passwort = $_POST['veranstalter_kennwort'];
        $passwort_bestaetigen = $_POST['veranstalter_kennwort_bestaetigung'];

        if (empty($loginname) || empty($passwort) || empty($passwort_bestaetigen)) {
            $fehlermeldung = "Bitte alle Felder ausfüllen!";
        } elseif (strlen($loginname) > 50) {
            $fehlermeldung = "Loginname darf maximal 50 Zeichen lang sein!";
        } else {
            $veranstalter_objekt = new Veranstalter();
            $ergebnis = $veranstalter_objekt->veranstalterRegistrieren($loginname, $passwort, $passwort_bestaetigen);

            if ($ergebnis === true) {
                $_SESSION['veranstalter_loginname'] = $loginname;
                header("Location: veranstalter_startseite.php");
                exit();
            } else {
                $fehlermeldung = $ergebnis;
            }
        }
    }
}


if (isset($_POST['login'])) {
    if (!csrfTokenGueltig()) {
        $fehlermeldung = "Ungültige Anfrage. Bitte die Seite neu laden.";
    } else {
        $loginname = $_POST['veranstalter_loginname'];
        $passwort = $_POST['veranstalter_kennwort'];

        if (empty($loginname) || empty($passwort)) {
            $fehlermeldung = "Bitte Loginname und Kennwort eingeben!";
        } else {
            $veranstalter_objekt = new Veranstalter();
            $ergebnis = $veranstalter_objekt->veranstalterAnmelden($loginname, $passwort);

            if ($ergebnis === true) {
                $_SESSION['veranstalter_loginname'] = $loginname;
                header("Location: veranstalter_startseite.php");
                exit();
            } else {
                $fehlermeldung = $ergebnis;
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
    <title>Veranstalter einloggen</title>
</head>

<body>

    <h1>Veranstalter einloggen</h1>

    <?php if (!empty($fehlermeldung)): ?>
        <p>
            <?php echo $fehlermeldung; ?>
        </p>
    <?php endif; ?>


    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <fieldset>
            <legend>Login-Daten unten eintragen</legend>

            <p>
                <label for="veranstalter_loginname">Loginname</label><br>
                <input id="veranstalter_loginname" name="veranstalter_loginname" type="text" maxlength="50"
                    placeholder="Loginname eingeben" required>
            </p>

            <p>
                <label for="veranstalter_kennwort">Kennwort</label><br>
                <input id="veranstalter_kennwort" name="veranstalter_kennwort" type="password"
                    placeholder="Kennwort eingeben" required>
            </p>

            <p>
                <label for="veranstalter_kennwort_bestaetigung">Kennwort bestätigen</label><br>
                <input id="veranstalter_kennwort_bestaetigung" name="veranstalter_kennwort_bestaetigung" type="password"
                    placeholder="Kennwort bestätigen">
            </p>

            <p>
                <input type="submit" name="login" value="Anmelden">
                <input type="submit" name="registrieren" value="Neu Registrieren">
            </p>

            <p><a href="index.php">Zurück zur Startseite</a></p>
        </fieldset>
    </form>

</body>

</html>

<!-- Lena Strohmenger Ende -->