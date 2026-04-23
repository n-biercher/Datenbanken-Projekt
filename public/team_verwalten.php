<!-- Nicolas Biercher Beginn -->
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Team verwalten</title>
</head>
<body>

<h1>Team verwalten</h1>

<?php

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
            SELECT
                `Mitarbeiter-ID`,
                Vorname,
                Nachname,
                Straße,
                Hausnummer,
                Telefonnummer,
                PLZ,
                Ort
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
            SELECT
                `Mitarbeiter-ID`,
                Vorname,
                Nachname,
                Straße,
                Hausnummer,
                Telefonnummer,
                PLZ,
                Ort
            FROM Fahrer
            WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
        ");
        $query->execute([$mitarbeiter_id, $teamname]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function sindFahrerdatenGueltig($mitarbeiter_id, $vorname, $nachname, $strasse, $hausnummer, $telefonnummer, $plz, $ort)
    {
        if ($mitarbeiter_id === '' || !ctype_digit($mitarbeiter_id)) {
            return "Die Mitarbeiter-ID muss eine ganze Zahl sein";
        }

        if (trim($vorname) === '') {
            return "Vorname darf nicht leer sein";
        }

        if (trim($nachname) === '') {
            return "Nachname darf nicht leer sein";
        }

        if (trim($strasse) === '') {
            return "Straße darf nicht leer sein";
        }

        if (trim($hausnummer) === '') {
            return "Hausnummer darf nicht leer sein";
        }

        if (trim($telefonnummer) === '') {
            return "Telefonnummer darf nicht leer sein";
        }

        if (!preg_match('/^[0-9]{5}$/', $plz)) {
            return "Die PLZ muss aus genau 5 Ziffern bestehen";
        }

        if (trim($ort) === '') {
            return "Ort darf nicht leer sein";
        }

        return true;
    }

    public function neuerFahrer($mitarbeiter_id, $vorname, $nachname, $strasse, $hausnummer, $telefonnummer, $plz, $ort, $teamname)
    {
        $pruefung = $this->sindFahrerdatenGueltig($mitarbeiter_id, $vorname, $nachname, $strasse, $hausnummer, $telefonnummer, $plz, $ort);
        if ($pruefung !== true) {
            return $pruefung;
        }

        $db_verbindung = $this->connect();

        $vorhanden_query = $db_verbindung->prepare("
            SELECT *
            FROM Fahrer
            WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
        ");
        $vorhanden_query->execute([$mitarbeiter_id, $teamname]);

        if ($vorhanden_query->fetch()) {
            return "Diese Mitarbeiter-ID existiert in deinem Team bereits";
        }

        $insert_query = $db_verbindung->prepare("
            INSERT INTO Fahrer
            (`Mitarbeiter-ID`, Vorname, Nachname, Straße, Hausnummer, Telefonnummer, PLZ, Ort, Teamname)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert_query->execute([
            $mitarbeiter_id,
            $vorname,
            $nachname,
            $strasse,
            $hausnummer,
            $telefonnummer,
            $plz,
            $ort,
            $teamname
        ]);

        return "Fahrer wurde erfolgreich angelegt";
    }

    public function fahrerAendern($mitarbeiter_id, $vorname, $nachname, $strasse, $hausnummer, $telefonnummer, $plz, $ort, $teamname)
    {
        $pruefung = $this->sindFahrerdatenGueltig($mitarbeiter_id, $vorname, $nachname, $strasse, $hausnummer, $telefonnummer, $plz, $ort);
        if ($pruefung !== true) {
            return $pruefung;
        }

        $db_verbindung = $this->connect();

        $update_query = $db_verbindung->prepare("
            UPDATE Fahrer
            SET
                Vorname = ?,
                Nachname = ?,
                Straße = ?,
                Hausnummer = ?,
                Telefonnummer = ?,
                PLZ = ?,
                Ort = ?
            WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
        ");
        $update_query->execute([
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

        if ($update_query->rowCount() === 0) {
            return "Fahrer konnte nicht geändert werden";
        }

        return "Fahrerdaten wurden erfolgreich geändert";
    }

    public function fahrerLoeschen($mitarbeiter_id, $teamname)
    {
        $db_verbindung = $this->connect();

        try {
            $delete_query = $db_verbindung->prepare("
                DELETE FROM Fahrer
                WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
            ");
            $delete_query->execute([$mitarbeiter_id, $teamname]);

            if ($delete_query->rowCount() === 0) {
                return "Fahrer konnte nicht gelöscht werden";
            }

            return "Fahrer wurde erfolgreich gelöscht";
        } catch (PDOException $e) {
            return "Fahrer konnte nicht gelöscht werden, da noch abhängige Trainings- oder Renndaten vorhanden sind";
        }
    }
}

if (!isset($_SESSION['teamchef_loginname'])) {
    echo "<p>Bitte zuerst als Teamchef anmelden.</p>";
    echo '<p><a href="teamchef_anmelden.php">Zum Login</a></p>';
    exit;
}

$teamchef_loginname = $_SESSION['teamchef_loginname'];
$verwaltung = new TeamVerwaltung();
$team = $verwaltung->holeTeamnameVomTeamchef($teamchef_loginname);

if (!$team) {
    echo "<p>Es wurde kein Team zu diesem Teamchef gefunden.</p>";
    echo '<p><a href="index.php">Zurück</a></p>';
    exit;
}

$teamname = $team['Teamname'];
$meldung = "";
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

if (isset($_POST['speichern'])) {
    $mitarbeiter_id = trim($_POST['mitarbeiter_id']);
    $vorname = trim($_POST['vorname']);
    $nachname = trim($_POST['nachname']);
    $strasse = trim($_POST['strasse']);
    $hausnummer = trim($_POST['hausnummer']);
    $telefonnummer = trim($_POST['telefonnummer']);
    $plz = trim($_POST['plz']);
    $ort = trim($_POST['ort']);
    $modus = $_POST['modus'];

    if ($modus === 'bearbeiten') {
        $meldung = $verwaltung->fahrerAendern(
            $mitarbeiter_id,
            $vorname,
            $nachname,
            $strasse,
            $hausnummer,
            $telefonnummer,
            $plz,
            $ort,
            $teamname
        );
    } else {
        $meldung = $verwaltung->neuerFahrer(
            $mitarbeiter_id,
            $vorname,
            $nachname,
            $strasse,
            $hausnummer,
            $telefonnummer,
            $plz,
            $ort,
            $teamname
        );
    }
}

if (isset($_POST['loeschen'])) {
    $mitarbeiter_id = $_POST['mitarbeiter_id'];
    $meldung = $verwaltung->fahrerLoeschen($mitarbeiter_id, $teamname);
}

if (isset($_GET['bearbeiten'])) {
    $fahrer = $verwaltung->holeFahrer($_GET['bearbeiten'], $teamname);

    if ($fahrer) {
        $bearbeitungsmodus = true;
        $formular_daten['mitarbeiter_id'] = $fahrer['Mitarbeiter-ID'];
        $formular_daten['vorname'] = $fahrer['Vorname'];
        $formular_daten['nachname'] = $fahrer['Nachname'];
        $formular_daten['strasse'] = $fahrer['Straße'];
        $formular_daten['hausnummer'] = $fahrer['Hausnummer'];
        $formular_daten['telefonnummer'] = $fahrer['Telefonnummer'];
        $formular_daten['plz'] = $fahrer['PLZ'];
        $formular_daten['ort'] = $fahrer['Ort'];
    }
} elseif (isset($_POST['speichern']) && $meldung !== "Fahrer wurde erfolgreich angelegt" && $meldung !== "Fahrerdaten wurden erfolgreich geändert") {
    $bearbeitungsmodus = ($_POST['modus'] === 'bearbeiten');
    $formular_daten['mitarbeiter_id'] = trim($_POST['mitarbeiter_id']);
    $formular_daten['vorname'] = trim($_POST['vorname']);
    $formular_daten['nachname'] = trim($_POST['nachname']);
    $formular_daten['strasse'] = trim($_POST['strasse']);
    $formular_daten['hausnummer'] = trim($_POST['hausnummer']);
    $formular_daten['telefonnummer'] = trim($_POST['telefonnummer']);
    $formular_daten['plz'] = trim($_POST['plz']);
    $formular_daten['ort'] = trim($_POST['ort']);
}

$alle_fahrer = $verwaltung->holeAlleFahrer($teamname);
?>

<p>Angemeldet als Teamchef: <?php echo htmlspecialchars($teamchef_loginname); ?></p>
<p>Team: <?php echo htmlspecialchars($teamname); ?></p>

<?php if ($meldung !== ""): ?>
    <p><?php echo htmlspecialchars($meldung); ?></p>
<?php endif; ?>

<form action="" method="POST">
    <fieldset>
        <legend>
            <?php
            if ($bearbeitungsmodus) {
                echo "Fahrer bearbeiten";
            } else {
                echo "Neuen Fahrer anlegen";
            }
            ?>
        </legend>

        <input type="hidden" name="modus" value="<?php echo $bearbeitungsmodus ? 'bearbeiten' : 'neu'; ?>">

        <p>
            <label>Mitarbeiter-ID</label><br>
            <input
                name="mitarbeiter_id"
                value="<?php echo htmlspecialchars($formular_daten['mitarbeiter_id']); ?>"
                <?php echo $bearbeitungsmodus ? 'readonly' : 'required'; ?>
                <?php echo !$bearbeitungsmodus ? 'required' : ''; ?>
            >
        </p>

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
            <input type="submit" name="speichern" value="Speichern">
        </p>

        <p>
            <a href="team_verwalten.php">Neuen Fahrer erfassen</a>
        </p>

        <p>
            <a href="index.php">Zurück</a>
        </p>
    </fieldset>
</form>

<h2>Alle Fahrer im Team</h2>

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
        <th>Aktion</th>
    </tr>

    <?php if (count($alle_fahrer) > 0): ?>
        <?php foreach ($alle_fahrer as $fahrer): ?>
            <tr>
                <td><?php echo htmlspecialchars($fahrer['Mitarbeiter-ID']); ?></td>
                <td><?php echo htmlspecialchars($fahrer['Vorname']); ?></td>
                <td><?php echo htmlspecialchars($fahrer['Nachname']); ?></td>
                <td><?php echo htmlspecialchars($fahrer['Straße']); ?></td>
                <td><?php echo htmlspecialchars($fahrer['Hausnummer']); ?></td>
                <td><?php echo htmlspecialchars($fahrer['Telefonnummer']); ?></td>
                <td><?php echo htmlspecialchars($fahrer['PLZ']); ?></td>
                <td><?php echo htmlspecialchars($fahrer['Ort']); ?></td>
                <td>
                    <a href="team_verwalten.php?bearbeiten=<?php echo urlencode($fahrer['Mitarbeiter-ID']); ?>">Bearbeiten</a>

                    <form action="" method="POST" style="display:inline;">
                        <input type="hidden" name="mitarbeiter_id" value="<?php echo htmlspecialchars($fahrer['Mitarbeiter-ID']); ?>">
                        <input type="submit" name="loeschen" value="Löschen" onclick="return confirm('Soll dieser Fahrer wirklich gelöscht werden?');">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="9">Noch keine Fahrer im Team vorhanden</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
<!-- Nicolas Biercher Ende -->