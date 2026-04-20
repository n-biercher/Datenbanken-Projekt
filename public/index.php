<!-- Nicolas Biercher Beginn -->
<?php
session_start();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal</title>
</head>
<body>

<h1>Willkommen zum Stadtradeln</h1>

<?php if (isset($_SESSION['teamchef_loginname'])): ?>

    <p>
        Eingeloggt als:
        <b><?php echo htmlspecialchars($_SESSION['teamchef_loginname']); ?></b>
    </p>

    <ul>
        <li><a href="logout.php">Logout</a></li>
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