<!-- Nicolas Biercher Beginn -->
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Team erstellen</title>
</head>
<body>

<h1>Team erstellen</h1>

<form action="" method="POST">
    <fieldset>
        <legend>Bitte Team-Daten eingeben</legend>

        <p>
            <label>Teamname</label><br>
            <input name="teamname" required>
        </p>

        <p>
            <label>Vorname</label><br>
            <input name="vorname" required>
        </p>

        <p>
            <label>Nachname</label><br>
            <input name="nachname" required>
        </p>

        <p>
            <label>Loginname</label><br>
            <input name="loginname" required>
        </p>

        <p>
            <label>Kennwort</label><br>
            <input name="kennwort" type="password" required>
        </p>

        <p>
            <label>Kennwort bestätigen</label><br>
            <input name="kennwort_bestaetigung" type="password" required>
        </p>

        <p>
            <input type="submit" name="registrieren" value="Registrieren">
        </p>

        <p>
            <a href="index.php">Zurück</a>
        </p>
    </fieldset>
</form>

<?php

include_once 'dbh.php';

class TeamRegistrierung extends Dbh
{
    public function registrieren($teamname, $vorname, $nachname, $loginname, $kennwort, $kennwort_bestaetigung)
    {
        if ($kennwort !== $kennwort_bestaetigung) {
            echo "Kennwörter stimmen nicht überein";
            return;
        }

        $db_verbindung = $this->connect();

        try {
            $db_verbindung->beginTransaction();

            $loginname_query = $db_verbindung->prepare("SELECT * FROM Teamchef WHERE TeamchefLoginName = ?");
            $loginname_query->execute([$loginname]);
            if ($loginname_query->fetch()) {
                echo "Loginname existiert bereits";
                return;
            }

            $teamname_query = $db_verbindung->prepare("SELECT * FROM Team WHERE Teamname = ?");
            $teamname_query->execute([$teamname]);
            if ($teamname_query->fetch()) {
                echo "Teamname existiert bereits";
                return;
            }

            $hash = password_hash($kennwort, PASSWORD_DEFAULT);

            $teamchef_erstellen_query = $db_verbindung->prepare("
                INSERT INTO Teamchef (TeamchefLoginName, Kennwort, Vorname, Nachname)
                VALUES (?, ?, ?, ?)
            ");
            $teamchef_erstellen_query->execute([$loginname, $hash, $vorname, $nachname]);

            $team_erstellen_query = $db_verbindung->prepare("
                INSERT INTO Team (Teamname, TeamchefLoginName)
                VALUES (?, ?)
            ");
            $team_erstellen_query->execute([$teamname, $loginname]);

            $db_verbindung->commit();

            echo "Registrierung erfolgreich!";
        } catch (PDOException $e) {
            $db_verbindung->rollBack();
            echo "Fehler: " . $e->getMessage();
        }
    }
}

if (isset($_POST['registrieren'])) {
    $reg = new TeamRegistrierung();
    $reg->registrieren(
        $_POST['teamname'],
        $_POST['vorname'],
        $_POST['nachname'],
        $_POST['loginname'],
        $_POST['kennwort'],
        $_POST['kennwort_bestaetigung']
    );
}
?>

</body>
</html>
<!-- Nicolas Biercher Ende -->