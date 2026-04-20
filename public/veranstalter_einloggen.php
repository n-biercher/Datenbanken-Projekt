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
                <input id="veranstalter_loginname" name="veranstalter_loginname">
            </p>
            <p>
                <label for="veranstalter_kennwort">Kennwort</label><br>
                <input id="veranstalter_kennwort" name="veranstalter_kennwort" type="password">
            </p>

            <p><input type="submit" name="login" value="Anmelden">
                <input type="submit" name="registrieren" value="Neu Registrieren">
            </p>
            <p><a href="index.php">Zurück zur Startseite</a></p>
        </fieldset>
    </form>

    <?php

    include_once 'dbh.php';

    class Veranstalter extends Dbh
    {
        public function veranstalterRegistrieren($veranstalter_loginname, $veranstalter_kennwort)
        {
            $sql = "SELECT VeranstalterLoginName FROM Veranstalter WHERE VeranstalterLoginName = ?";

            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$veranstalter_loginname]);

            if ($stmt->fetch()) {
                echo "Loginname bereits vergeben!";
                return;
            }

            $sql = "INSERT INTO Veranstalter (VeranstalterLoginName, Kennwort) VALUES (?, ?)";

            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$veranstalter_loginname, $veranstalter_kennwort]);

            $_SESSION['veranstalter_loginname'] = $veranstalter_loginname;

            header("Location: veranstalter_startseite.php");
            exit();
        }
        public function veranstalterAnmelden($veranstalter_loginname, $veranstalter_kennwort)
        {
            $sql = "SELECT * FROM Veranstalter WHERE VeranstalterLoginName = ?";
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$veranstalter_loginname]);
            $result = $stmt->fetch();

            if (!$result) {
                echo "Bitte registriere dich zuerst!";
                return;
            }

            if ($result['Kennwort'] === $veranstalter_kennwort) {
                $_SESSION['veranstalter_loginname'] = $result['VeranstalterLoginName'];
                header("Location: veranstalter_startseite.php");
                exit();
            } else {
                echo "Falsches Kennwort!";
            }
        }
    }

    if (isset($_POST['registrieren'])) {
        $veranstalter_loginname = $_POST['veranstalter_loginname'];
        $veranstalter_kennwort = $_POST['veranstalter_kennwort'];

        $veranstalterRegistrieren = new Veranstalter();
        $veranstalterRegistrieren->veranstalterRegistrieren($veranstalter_loginname, $veranstalter_kennwort);

    }

    if (isset($_POST['login'])) {
        $veranstalter_loginname = $_POST['veranstalter_loginname'];
        $veranstalter_kennwort = $_POST['veranstalter_kennwort'];

        $veranstalter_einloggen = new Veranstalter();
        $veranstalter_einloggen->veranstalterAnmelden($veranstalter_loginname, $veranstalter_kennwort);
    }
    ?>


</body>

</html>

<!-- Lena Strohmenger Ende -->