<!-- Lena Strohmenger Beginn -->

<?php
session_start();
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
    <form action="" method="POST">
        <fieldset>
            <legend>Login-Daten unten eintragen</legend>

            <p>
                <label for="veranstalter_loginname">Loginname</label><br>
                <input id="veranstalter_loginname" name="veranstalter_loginname" required>
            </p>

            <p>
                <label for="veranstalter_kennwort">Kennwort</label><br>
                <input id="veranstalter_kennwort" name="veranstalter_kennwort" type="password" required>
            </p>

            <p>
                <label for="veranstalter_kennwort_bestaetigung">Kennwort bestätigen</label><br>
                <input id="veranstalter_kennwort_bestaetigung" name="veranstalter_kennwort_bestaetigung" type="password">
            </p>

            <p>
                <input type="submit" name="login" value="Anmelden">
                <input type="submit" name="registrieren" value="Neu Registrieren">
            </p>

            <p><a href="index.php">Zurück zur Startseite</a></p>
        </fieldset>
    </form>

<?php

include_once 'dbh.php';

class Veranstalter extends Dbh
{
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

    public function veranstalterRegistrieren($loginname, $passwort, $passwort2)
    {
        if ($passwort !== $passwort2) {
            echo "Kennwörter stimmen nicht überein";
            return;
        }

        $check = $this->überprüfePasswort($passwort);
        if ($check !== true) {
            echo $check;
            return;
        }

        // Prüfen ob Loginname existiert
        $sql = "SELECT VeranstalterLoginName FROM Veranstalter WHERE VeranstalterLoginName = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$loginname]);

        if ($stmt->fetch()) {
            echo "Loginname bereits vergeben!";
            return;
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

    public function veranstalterAnmelden($loginname, $passwort)
    {
        $sql = "SELECT * FROM Veranstalter WHERE VeranstalterLoginName = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$loginname]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "Bitte registriere dich zuerst!";
            return;
        }

        // Passwort prüfen 
        if (password_verify($passwort, $user['Kennwort'])) {
            $_SESSION['veranstalter_loginname'] = $user['VeranstalterLoginName'];
            header("Location: veranstalter_startseite.php");
            exit();
        } else {
            echo "Falsches Kennwort!";
        }
    }
}

// Registrierung
if (isset($_POST['registrieren'])) {
    $loginname = $_POST['veranstalter_loginname'];
    $passwort = $_POST['veranstalter_kennwort'];
    $passwort2 = $_POST['veranstalter_kennwort_bestaetigung'];

    if (empty($loginname) || empty($passwort) || empty($passwort2)) {
        echo "Bitte alle Felder ausfüllen!";
    } else {
        $obj = new Veranstalter();
        $obj->veranstalterRegistrieren($loginname, $passwort, $passwort2);
    }
}

// Login
if (isset($_POST['login'])) {
    $loginname = $_POST['veranstalter_loginname'];
    $passwort = $_POST['veranstalter_kennwort'];

    if (empty($loginname) || empty($passwort)) {
        echo "Bitte Loginname und Kennwort eingeben!";
    } else {
        $obj = new Veranstalter();
        $obj->veranstalterAnmelden($loginname, $passwort);
    }
}
?>

</body>
</html>

<!-- Lena Strohmenger Ende -->