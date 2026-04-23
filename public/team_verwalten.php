<?php
session_start();
if (!isset($_SESSION['teamchef_loginname'])) {
    header("Location: teamchef_anmelden.php");
    exit();
}

include_once('dbh.php');

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

    public function fahrerAnlegen($vorname, $nachname, $strasse, $hausnummer, $telefonnummer, $plz, $ort, $teamname)
    {
        $db_verbindung = $this->connect();

        $query = $db_verbindung->prepare("
            CALL FahrerAnlegen(?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $query->execute([
            $vorname,
            $nachname,
            $strasse,
            $hausnummer,
            $telefonnummer,
            $plz,
            $ort,
            $teamname
        ]);

        $ergebnis = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        return $ergebnis;
    }
}

$teamchef_loginname = $_SESSION['teamchef_loginname'];

$verwaltung = new TeamVerwaltung();
$team = $verwaltung->holeTeamnameVomTeamchef($teamchef_loginname);

if (!$team) {
    echo "Kein Team gefunden.";
    exit;
}

$teamname = $team['Teamname'];
$meldung = "";

if (isset($_POST['fahrer_anlegen'])) {
    $vorname = trim($_POST['vorname']);
    $nachname = trim($_POST['nachname']);
    $strasse = trim($_POST['strasse']);
    $hausnummer = trim($_POST['hausnummer']);
    $telefonnummer = trim($_POST['telefonnummer']);
    $plz = trim($_POST['plz']);
    $ort = trim($_POST['ort']);

    try {
        $ergebnis = $verwaltung->fahrerAnlegen(
            $vorname,
            $nachname,
            $strasse,
            $hausnummer,
            $telefonnummer,
            $plz,
            $ort,
            $teamname
        );

        $meldung = $ergebnis['meldung'];
    } catch (PDOException $e) {
        $meldung = "Fehler beim Anlegen des Fahrers.";
    }
}

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

<?php if ($meldung !== ""): ?>
    <p><?php echo htmlspecialchars($meldung); ?></p>
<?php endif; ?>

<h2>Neuen Fahrer anlegen</h2>

<form action="" method="POST">
    <p>
        <label>Vorname</label><br>
        <input name="vorname" required>
    </p>

    <p>
        <label>Nachname</label><br>
        <input name="nachname" required>
    </p>

    <p>
        <label>Straße</label><br>
        <input name="strasse" required>
    </p>

    <p>
        <label>Hausnummer</label><br>
        <input name="hausnummer" required>
    </p>

    <p>
        <label>Telefonnummer</label><br>
        <input name="telefonnummer" required>
    </p>

    <p>
        <label>PLZ</label><br>
        <input name="plz" maxlength="5" required>
    </p>

    <p>
        <label>Ort</label><br>
        <input name="ort" required>
    </p>

    <p>
        <input type="submit" name="fahrer_anlegen" value="Fahrer anlegen">
    </p>
</form>

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