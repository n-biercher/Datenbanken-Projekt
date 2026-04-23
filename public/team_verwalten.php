<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'dbh.php';

class TeamVerwaltung extends Dbh
{
    public function holeTeamnameVomTeamchef($teamchef_loginname)
    {
        $db_verbindung = $this->connect();

        $query = $db_verbindung->prepare("
            SELECT Teamname
            FROM Team
            WHERE TeamchefLoginName = ?
        ");
        $query->execute([$teamchef_loginname]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function holeAlleFahrer($teamname)
    {
        $db_verbindung = $this->connect();

        $query = $db_verbindung->prepare("
            SELECT *
            FROM Fahrer
            WHERE Teamname = ?
        ");
        $query->execute([$teamname]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!isset($_SESSION['teamchef_loginname'])) {
    echo "Bitte zuerst als Teamchef anmelden.";
    exit;
}

$teamchef_loginname = $_SESSION['teamchef_loginname'];

$verwaltung = new TeamVerwaltung();
$team = $verwaltung->holeTeamnameVomTeamchef($teamchef_loginname);

if (!$team) {
    echo "Kein Team gefunden.";
    exit;
}

$teamname = $team['Teamname'];
$alle_fahrer = $verwaltung->holeAlleFahrer($teamname);

function wert($array, $key)
{
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : '';
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Team verwalten</title>
</head>
<body>

<h1>Team verwalten</h1>

<p>Angemeldet als Teamchef: <?php echo htmlspecialchars($teamchef_loginname); ?></p>
<p>Team: <?php echo htmlspecialchars($teamname); ?></p>

<h2>Alle Fahrer</h2>

<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Mitarbeiter-ID</th>
        <th>Vorname</th>
        <th>Nachname</th>
        <th>Straße</th>
        <th>Hausnummer</th>
        <th>Telefonnummer</th>
        <th>PLZ</th>
        <th>Ort</th>
        <th>Teamname</th>
    </tr>

    <?php if (count($alle_fahrer) > 0): ?>
        <?php foreach ($alle_fahrer as $fahrer): ?>
            <tr>
                <td><?php echo wert($fahrer, 'Mitarbeiter-ID'); ?></td>
                <td><?php echo wert($fahrer, 'Vorname'); ?></td>
                <td><?php echo wert($fahrer, 'Nachname'); ?></td>
                <td><?php echo wert($fahrer, 'Straße'); ?></td>
                <td><?php echo wert($fahrer, 'Hausnummer'); ?></td>
                <td><?php echo wert($fahrer, 'Telefonnummer'); ?></td>
                <td><?php echo wert($fahrer, 'PLZ'); ?></td>
                <td><?php echo wert($fahrer, 'Ort'); ?></td>
                <td><?php echo wert($fahrer, 'Teamname'); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="9">Keine Fahrer gefunden</td>
        </tr>
    <?php endif; ?>
</table>

<p><a href="index.php">Zurück</a></p>

</body>
</html>