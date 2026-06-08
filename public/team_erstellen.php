<!-- Nicolas Biercher Beginn -->
<?php
include_once('session_management.php');
sitzungStarten();
include_once('TeamErstellung.php');

$meldung = '';
$fehler  = '';

if (isset($_POST['registrieren'])) {
    $teamname      = trim($_POST['teamname']             ?? '');
    $vorname       = trim($_POST['vorname']              ?? '');
    $nachname      = trim($_POST['nachname']             ?? '');
    $loginname     = trim($_POST['loginname']            ?? '');
    $kennwort      = $_POST['kennwort']                  ?? '';
    $kennwort_best = $_POST['kennwort_bestaetigung']     ?? '';

    if (empty($teamname) || empty($vorname) || empty($nachname) || empty($loginname) || empty($kennwort) || empty($kennwort_best)) {
        $fehler = "Bitte alle Felder ausfüllen!";
    } elseif (strlen($loginname) > 50) {
        $fehler = "Loginname darf maximal 50 Zeichen lang sein!";
    } else {
        try {
            $reg    = new TeamRegistrierung();
            $fehler = $reg->registrieren($teamname, $vorname, $nachname, $loginname, $kennwort, $kennwort_best);

            if ($fehler === null) {
                $meldung = "Registrierung erfolgreich!";
                $fehler  = '';
            }
        } catch (PDOException $e) {
            $fehler = "Fehler bei der Registrierung.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Team erstellen</title>
</head>
<body>

<h1>Team erstellen</h1>

<?php if ($meldung !== ''): ?>
    <p><?php echo htmlspecialchars($meldung); ?></p>
<?php endif; ?>

<?php if ($fehler !== ''): ?>
    <p><?php echo htmlspecialchars($fehler); ?></p>
<?php endif; ?>

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

</body>
</html>
<!-- Nicolas Biercher Ende -->
