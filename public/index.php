<?php
/**
 * Startseite des Portals mit unterschiedlicher Navigation für verschiedene Nutzer
 * Nicolas Biercher
 */
include_once('include/session_management.php');
sitzungStarten();

$fehler = '';

if (isset($_GET['fehler'])) {
    if ($_GET['fehler'] === 'sitzung_ungueltig') {
        $fehler = 'Deine Sitzung ist ungültig. Bitte melde dich erneut an.';
    } elseif ($_GET['fehler'] === 'sitzung_abgelaufen') {
        $fehler = 'Deine Sitzung ist abgelaufen. Bitte melde dich erneut an.';
    } elseif ($_GET['fehler'] === 'kein_zugriff') {
        $fehler = 'Kein Zugriff. Bitte melde dich zuerst an.';
    } elseif ($_GET['fehler'] === 'kein_team') {
        $fehler = 'Deinem Account ist kein Team zugeordnet.';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal – Radrennen</title>
</head>
<body>

<h1>Willkommen zum Radrennen</h1>

<?php if (!empty($fehler)): ?>
    <p style="color: red;"><b><?php echo htmlspecialchars($fehler); ?></b></p>
<?php endif; ?>

<?php if (isset($_SESSION['teamchef_loginname'])): ?>

    <p>
        Eingeloggt als Teamchef:
        <b><?php echo htmlspecialchars($_SESSION['teamchef_loginname']); ?></b>
    </p>

    <ul>
        <li><a href="team_verwalten.php">Team verwalten</a></li>
        <li><a href="teamchef_startseite.php">Team Startseite</a></li>
        <li>
            <form action="logout.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit">Logout</button>
            </form>
        </li>
    </ul>

<?php elseif (isset($_SESSION['veranstalter_loginname'])): ?>

    <p>
        Eingeloggt als Veranstalter:
        <b><?php echo htmlspecialchars($_SESSION['veranstalter_loginname']); ?></b>
    </p>

    <ul>
        <li><a href="veranstalter_startseite.php">Veranstalter Startseite</a></li>
        <li><a href="rennen_ergebniserfassung.php">Rennen Ergebniserfassung</a></li>
        <li>
            <form action="logout.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit">Logout</button>
            </form>
        </li>
    </ul>

<?php elseif (isset($_SESSION['fahrer_loginname'])): ?>

    <p>
        Eingeloggt als Fahrer:
        <b><?php echo htmlspecialchars($_SESSION['fahrer_loginname']); ?></b>
    </p>

    <ul>
        <li>
            <form action="logout.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit">Logout</button>
            </form>
        </li>
    </ul>

<?php else: ?>

    <p><b>Bitte wähle eine der folgenden Optionen:</b></p>

    <ul>
        <li><a href="team_erstellen.php">Team erstellen</a></li>
        <li><a href="teamchef_anmelden.php">Teamchef anmelden</a></li>
        <li><a href="veranstalter_einloggen.php">Veranstalter einloggen</a></li>
    </ul>

<?php endif; ?>

<hr>

</body>
</html>
<!-- Nicolas Biercher Ende -->
