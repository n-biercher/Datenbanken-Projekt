<!-- Lena Strohmenger Beginn -->

<?php
include_once('session_management.php');
sitzungStarten();

include_once 'dbh.php';

$fehlermeldung = "";

class Veranstalter extends Dbh
{

    // Passwortstärke überprüfen
    private function überprüfePasswort($passwort)
    {
        if (strlen($passwort) < 8) {
            return "Mindestens 8 Zeichen";
        }
        if (!preg_match('/[A-Z]/', $passwort)) {
            return "Mindestens 1 Großbuchstabe";
        }
        if (!preg_match('/[a-z]/', $passwort)) {
            return "Mindestens 1 Kleinbuchstabe";
        }
        if (!preg_match('/[0-9]/', $passwort)) {
            return "Mindestens 1 Zahl";
        }
        if (!preg_match('/[\W_]/', $passwort)) {
            return "Mindestens 1 Sonderzeichen";
        }
        return true;
    }

    // Veranstalter registrieren
    public function veranstalterRegistrieren($loginname, $passwort, $passwort2)
    {
        if ($passwort !== $passwort2) {
            return "Kennwörter stimmen nicht überein";
        }

        $check = $this->überprüfePasswort($passwort);
        if ($check !== true) {
            return $check;
        }

        // Prüfen ob Loginname existiert
        $sql = "SELECT VeranstalterLoginName FROM Veranstalter WHERE VeranstalterLoginName = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$loginname]);

        if ($stmt->fetch()) {
            return "Loginname bereits vergeben!";
        }

        // Passwort hashen
        $hash = password_hash($passwort, PASSWORD_DEFAULT);

        // Einfügen
        $sql = "INSERT INTO Veranstalter (VeranstalterLoginName, Kennwort) VALUES (?, ?)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$loginname, $hash]);

        $_SESSION['veranstalter_loginname'] = $loginname;
        header("Location: veranstalter_startseite.php");
        exit();
    }

    //Veranstalter anmelden
    public function veranstalterAnmelden($loginname, $passwort)
    {
        $sql = "SELECT * FROM Veranstalter WHERE VeranstalterLoginName = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$loginname]);
        $user = $stmt->fetch();

        if (!$user) {
            return "Bitte registriere dich zuerst!";
        }

        // Passwort prüfen 
        if (password_verify($passwort, $user['Kennwort'])) {
            $_SESSION['veranstalter_loginname'] = $user['VeranstalterLoginName'];
            header("Location: veranstalter_startseite.php");
            exit();
        } else {
            return "Falsches Kennwort!";
        }
    }
}

// Registrierung
if (isset($_POST['registrieren'])) {
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

        if ($ergebnis !== true) {
            $fehlermeldung = $ergebnis;
        }
    }
}

// Login
if (isset($_POST['login'])) {
    $loginname = $_POST['veranstalter_loginname'];
    $passwort = $_POST['veranstalter_kennwort'];

    if (empty($loginname) || empty($passwort)) {
        $fehlermeldung = "Bitte Loginname und Kennwort eingeben!";
    } else {
        $veranstalter_objekt = new Veranstalter();
        $ergebnis = $veranstalter_objekt->veranstalterAnmelden($loginname, $passwort);

        if ($ergebnis !== true) {
            $fehlermeldung = $ergebnis;
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