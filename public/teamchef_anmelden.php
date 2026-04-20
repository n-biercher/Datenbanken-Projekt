<!-- Nicolas Biercher Beginn -->
<?php
session_start();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teamchef anmelden</title>
</head>

<body>

<h1>Login</h1>

<form action="" method="POST">
    <fieldset>
        <legend>Login</legend>

        <p>
            <label>Loginname</label><br>
            <input name="loginname" required>
        </p>

        <p>
            <label>Kennwort</label><br>
            <input name="kennwort" type="password" required>
        </p>

        <p>
            <input type="submit" name="login" value="Einloggen">
        </p>

        <p>
            <a href="index.php">Zurück</a>
        </p>
    </fieldset>
</form>

<?php

include_once 'dbh.php';

class TeamchefLogin extends Dbh
{
    public function login($loginname, $kennwort)
    {
        $stmt = $this->connect()->prepare(
            "SELECT * FROM Teamchef WHERE TeamchefLoginName = ?"
        );
        $stmt->execute([$loginname]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "User nicht gefunden";
            return;
        }

        if (password_verify($kennwort, $user['Kennwort'])) {

            $_SESSION['teamchef_loginname'] = $user['TeamchefLoginName'];
            $_SESSION['vorname'] = $user['Vorname'];
            $_SESSION['nachname'] = $user['Nachname'];

            header("Location: index.php");
            exit();

        } else {
            echo "Falsches Passwort";
        }
    }
}

if (isset($_POST['login'])) {
    $login = new TeamchefLogin();
    $login->login($_POST['loginname'], $_POST['kennwort']);
}
?>

</body>
</html>
<!-- Nicolas Biercher Ende -->