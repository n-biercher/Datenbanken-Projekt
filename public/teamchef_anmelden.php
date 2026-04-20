<!-- Nicolas Biercher Beginn -->
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teamchef anmelden</title>
</head>

<body>
    <h1>Teamchef anmelden</h1>
    <form action="" method="POST">
        <fieldset>
            <legend>Login-Daten unten eintragen</legend>
            <p>
                <label for="teamchef_loginname">Loginname</label><br>
                <input id="teamchef_loginname" name="teamchef_loginname">
            </p>
            <p>
                <label for="teamchef_kennwort">Kennwort</label><br>
                <input id="teamchef_kennwort" name="teamchef_kennwort" type="password">
            </p>

            <p><input type="submit" name="login" value="login"></p>
            <p><a href="index.php">Zurück zur Startseite</a></p>
        </fieldset>
    </form>

    <?php

    include_once 'dbh.php';

    class Teamchef extends Dbh
    {
        public function teamchefAnmelden($teamchef_loginname, $teamchef_kennwort)
        {
            $sql = "SELECT * FROM Teamchef WHERE TeamchefLoginName = ?";
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$teamchef_loginname]);
            $result = $stmt->fetch();

            if (!$result) {
                echo "Bitte registriere dich zuerst!";
                return;
            }

            if ($result['Kennwort'] === $teamchef_kennwort) {
                echo "Willkommen, " . $result['TeamchefLoginName'] . "!";
            } else {
                echo "Falsches Kennwort!";
            }
        }
    }

    if (isset($_POST['login'])) {
        $teamchef_loginname = $_POST['teamchef_loginname'];
        $teamchef_kennwort = $_POST['teamchef_kennwort'];

        $teamchef_einloggen = new teamchef();
        $teamchef_einloggen->teamchefAnmelden($teamchef_loginname, $teamchef_kennwort);
    }
    ?>


</body>

</html>

<!-- Nicolas Biercher Ende -->