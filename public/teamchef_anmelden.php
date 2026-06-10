<?php
/**
 * Login-Formular für Teamchefs
 * Nicolas Biercher
 */
include_once('include/session_management.php');
include_once('classes/TeamchefLogin.php');
sitzungStarten();

if (isset($_SESSION['teamchef_loginname'])) {
    header("Location: index.php");
    exit();
}

$fehler = '';

if (isset($_POST['login'])) {
    if (!csrfTokenGueltig()) {
        $fehler = "Ungültige Anfrage. Bitte die Seite neu laden.";
    } else {
        $login    = new TeamchefLogin();
        $ergebnis = $login->login(
            trim($_POST['loginname'] ?? ''),
            $_POST['kennwort'] ?? ''
        );
        if ($ergebnis === null) {
            header("Location: index.php");
            exit();
        }
        $fehler = $ergebnis;
    }
}
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

<?php if ($fehler !== ''): ?>
    <p><?php echo htmlspecialchars($fehler); ?></p>
<?php endif; ?>

<form action="" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <fieldset>
        <legend>Login</legend>

        <p>
            <label for="loginname">Loginname</label><br>
            <input id="loginname" name="loginname" required>
        </p>

        <p>
            <label for="kennwort">Kennwort</label><br>
            <input id="kennwort" name="kennwort" type="password" required>
        </p>

        <p>
            <input type="submit" name="login" value="Einloggen">
        </p>

        <p>
            <a href="index.php">Zurück</a>
        </p>
    </fieldset>
</form>
</body>
</html>
<!-- Nicolas Biercher Ende -->
