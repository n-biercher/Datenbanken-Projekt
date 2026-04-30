<!-- Nicolas Biercher Beginn -->

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
            ORDER BY `Mitarbeiter-ID` ASC
        ");
        $query->execute([$teamname]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function holeFahrer($mitarbeiter_id, $teamname)
    {
        $db_verbindung = $this->connect();

        $query = $db_verbindung->prepare("
            SELECT *
            FROM Fahrer
            WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
        ");
        $query->execute([$mitarbeiter_id, $teamname]);

        return $query->fetch(PDO::FETCH_ASSOC);
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

    public function fahrerAendern($mitarbeiter_id, $vorname, $nachname, $strasse, $hausnummer, $telefonnummer, $plz, $ort, $teamname)
    {
        $db_verbindung = $this->connect();

        $query = $db_verbindung->prepare("
            UPDATE Fahrer
            SET
                Vorname = ?,
                Nachname = ?,
                `Straße` = ?,
                Hausnummer = ?,
                Telefonnummer = ?,
                PLZ = ?,
                Ort = ?
            WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
        ");

        $query->execute([
            $vorname,
            $nachname,
            $strasse,
            $hausnummer,
            $telefonnummer,
            $plz,
            $ort,
            $mitarbeiter_id,
            $teamname
        ]);

        return "Fahrer wurde erfolgreich geändert.";
    }

    public function fahrerLoeschen($mitarbeiter_id, $teamname)
    {
        $db_verbindung = $this->connect();

        $query = $db_verbindung->prepare("
            DELETE FROM Fahrer
            WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
        ");
        $query->execute([$mitarbeiter_id, $teamname]);

        if ($query->rowCount() > 0) {
            return "Fahrer wurde erfolgreich gelöscht.";
        }

        return "Fahrer wurde nicht gefunden.";
    }
}

function wert($array, $key)
{
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : '';
}

function eingabe($name)
{
    return isset($_POST[$name]) ? trim($_POST[$name]) : '';
}

$teamchef_loginname = $_SESSION['teamchef_loginname'];
$verwaltung = new TeamVerwaltung();

$meldung = "";
$fehler = "";
$modal_oeffnen = false;
$bearbeitungsmodus = false;

$formular_daten = [
    'mitarbeiter_id' => '',
    'vorname' => '',
    'nachname' => '',
    'strasse' => '',
    'hausnummer' => '',
    'telefonnummer' => '',
    'plz' => '',
    'ort' => ''
];

try {
    $team = $verwaltung->holeTeamnameVomTeamchef($teamchef_loginname);

    if (!$team) {
        echo "Kein Team gefunden.";
        exit();
    }

    $teamname = $team['Teamname'];
} catch (PDOException $e) {
    echo "Fehler beim Laden des Teams.";
    exit();
}

if (isset($_POST['fahrer_anlegen'])) {
    $formular_daten['vorname'] = eingabe('vorname');
    $formular_daten['nachname'] = eingabe('nachname');
    $formular_daten['strasse'] = eingabe('strasse');
    $formular_daten['hausnummer'] = eingabe('hausnummer');
    $formular_daten['telefonnummer'] = eingabe('telefonnummer');
    $formular_daten['plz'] = eingabe('plz');
    $formular_daten['ort'] = eingabe('ort');

    try {
        $ergebnis = $verwaltung->fahrerAnlegen(
            $formular_daten['vorname'],
            $formular_daten['nachname'],
            $formular_daten['strasse'],
            $formular_daten['hausnummer'],
            $formular_daten['telefonnummer'],
            $formular_daten['plz'],
            $formular_daten['ort'],
            $teamname
        );

        if ($ergebnis && isset($ergebnis['erfolg']) && $ergebnis['erfolg'] == 1) {
            $meldung = $ergebnis['meldung'];
            $formular_daten = [
                'mitarbeiter_id' => '',
                'vorname' => '',
                'nachname' => '',
                'strasse' => '',
                'hausnummer' => '',
                'telefonnummer' => '',
                'plz' => '',
                'ort' => ''
            ];
        } elseif ($ergebnis && isset($ergebnis['meldung'])) {
            $fehler = $ergebnis['meldung'];
            $modal_oeffnen = true;
        } else {
            $fehler = "Der Fahrer konnte nicht angelegt werden.";
            $modal_oeffnen = true;
        }
    } catch (PDOException $e) {
        $fehler = "Fehler beim Anlegen des Fahrers.";
        $modal_oeffnen = true;
    }
}

if (isset($_POST['fahrer_aendern'])) {
    $formular_daten['mitarbeiter_id'] = eingabe('mitarbeiter_id');
    $formular_daten['vorname'] = eingabe('vorname');
    $formular_daten['nachname'] = eingabe('nachname');
    $formular_daten['strasse'] = eingabe('strasse');
    $formular_daten['hausnummer'] = eingabe('hausnummer');
    $formular_daten['telefonnummer'] = eingabe('telefonnummer');
    $formular_daten['plz'] = eingabe('plz');
    $formular_daten['ort'] = eingabe('ort');

    try {
        if ($formular_daten['mitarbeiter_id'] === '') {
            $fehler = "Mitarbeiter-ID fehlt.";
            $modal_oeffnen = true;
            $bearbeitungsmodus = true;
        } else {
            $meldung = $verwaltung->fahrerAendern(
                $formular_daten['mitarbeiter_id'],
                $formular_daten['vorname'],
                $formular_daten['nachname'],
                $formular_daten['strasse'],
                $formular_daten['hausnummer'],
                $formular_daten['telefonnummer'],
                $formular_daten['plz'],
                $formular_daten['ort'],
                $teamname
            );
        }
    } catch (PDOException $e) {
        $fehler = "Fehler beim Ändern des Fahrers.";
        $modal_oeffnen = true;
        $bearbeitungsmodus = true;
    }
}

if (isset($_POST['fahrer_loeschen'])) {
    $mitarbeiter_id = eingabe('mitarbeiter_id');

    try {
        if ($mitarbeiter_id === '') {
            $fehler = "Mitarbeiter-ID fehlt.";
        } else {
            $meldung = $verwaltung->fahrerLoeschen($mitarbeiter_id, $teamname);
        }
    } catch (PDOException $e) {
        $fehler = "Fahrer konnte nicht gelöscht werden, da noch Trainings- oder Renndaten vorhanden sind.";
    }
}

if (isset($_GET['bearbeiten'])) {
    try {
        $fahrer = $verwaltung->holeFahrer($_GET['bearbeiten'], $teamname);

        if ($fahrer) {
            $bearbeitungsmodus = true;
            $modal_oeffnen = true;

            $formular_daten['mitarbeiter_id'] = wert($fahrer, 'Mitarbeiter-ID');
            $formular_daten['vorname'] = wert($fahrer, 'Vorname');
            $formular_daten['nachname'] = wert($fahrer, 'Nachname');
            $formular_daten['strasse'] = wert($fahrer, 'Straße');
            $formular_daten['hausnummer'] = wert($fahrer, 'Hausnummer');
            $formular_daten['telefonnummer'] = wert($fahrer, 'Telefonnummer');
            $formular_daten['plz'] = wert($fahrer, 'PLZ');
            $formular_daten['ort'] = wert($fahrer, 'Ort');
        } else {
            $fehler = "Fahrer wurde nicht gefunden.";
        }
    } catch (PDOException $e) {
        $fehler = "Fehler beim Laden des Fahrers.";
    }
}

try {
    $alle_fahrer = $verwaltung->holeAlleFahrer($teamname);
} catch (PDOException $e) {
    $alle_fahrer = [];
    $fehler = "Fehler beim Laden der Fahrer.";
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

<h1>Team: <?php echo htmlspecialchars($teamname); ?> verwalten</h1>

<p><a href="index.php">Zurück zur Startseite</a></p>

<?php if ($meldung !== ""): ?>
    <p><?php echo htmlspecialchars($meldung); ?></p>
<?php endif; ?>

<?php if ($fehler !== ""): ?>
    <p><?php echo htmlspecialchars($fehler); ?></p>
<?php endif; ?>

<p>
    <button type="button" onclick="document.getElementById('fahrer_modal').showModal();">
        Fahrer erstellen
    </button>
</p>

<dialog id="fahrer_modal">
    <h2>
        <?php
        if ($bearbeitungsmodus) {
            echo "Fahrer bearbeiten";
        } else {
            echo "Fahrer erstellen";
        }
        ?>
    </h2>

    <form action="" method="POST">
        <?php if ($bearbeitungsmodus): ?>
            <input type="hidden" name="mitarbeiter_id" value="<?php echo htmlspecialchars($formular_daten['mitarbeiter_id']); ?>">
            <p>Mitarbeiter-ID: <?php echo htmlspecialchars($formular_daten['mitarbeiter_id']); ?></p>
        <?php endif; ?>

        <p>
            <label>Vorname</label><br>
            <input name="vorname" value="<?php echo htmlspecialchars($formular_daten['vorname']); ?>" required>
        </p>

        <p>
            <label>Nachname</label><br>
            <input name="nachname" value="<?php echo htmlspecialchars($formular_daten['nachname']); ?>" required>
        </p>

        <p>
            <label>Straße</label><br>
            <input name="strasse" value="<?php echo htmlspecialchars($formular_daten['strasse']); ?>" required>
        </p>

        <p>
            <label>Hausnummer</label><br>
            <input name="hausnummer" value="<?php echo htmlspecialchars($formular_daten['hausnummer']); ?>" required>
        </p>

        <p>
            <label>Telefonnummer</label><br>
            <input name="telefonnummer" value="<?php echo htmlspecialchars($formular_daten['telefonnummer']); ?>" required>
        </p>

        <p>
            <label>PLZ</label><br>
            <input name="plz" maxlength="5" value="<?php echo htmlspecialchars($formular_daten['plz']); ?>" required>
        </p>

        <p>
            <label>Ort</label><br>
            <input name="ort" value="<?php echo htmlspecialchars($formular_daten['ort']); ?>" required>
        </p>

        <p>
            <?php if ($bearbeitungsmodus): ?>
                <input type="submit" name="fahrer_aendern" value="Änderungen speichern">
            <?php else: ?>
                <input type="submit" name="fahrer_anlegen" value="Fahrer anlegen">
            <?php endif; ?>
        </p>
    </form>

    <form method="dialog">
        <p>
            <button>Schließen</button>
        </p>
    </form>
</dialog>

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
        <th>Aktion</th>
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
                <td>
                    <a href="team_verwalten.php?bearbeiten=<?php echo urlencode(wert($fahrer, 'Mitarbeiter-ID')); ?>">Bearbeiten</a>

                    <form action="" method="POST">
                        <input type="hidden" name="mitarbeiter_id" value="<?php echo wert($fahrer, 'Mitarbeiter-ID'); ?>">
                        <input type="submit" name="fahrer_loeschen" value="Löschen" onclick="return confirm('Soll dieser Fahrer wirklich gelöscht werden?');">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="10">Keine Fahrer gefunden</td>
        </tr>
    <?php endif; ?>
</table>

<?php if ($modal_oeffnen): ?>
<script>
document.getElementById('fahrer_modal').showModal();
</script>
<?php endif; ?>

</body>
</html>

<!-- Nicolas Biercher Ende -->