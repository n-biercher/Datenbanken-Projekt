<!-- Lena Strohmenger Beginn -->

<?php
include_once('login_schutz.php');
checkLogin('teamchef_loginname', 'teamchef_anmelden.php');

include_once('dbh.php');

class Rennen extends Dbh
{
    //Holt alle Rennen, die in der Zukunft liegen aus der Datenbank
    public function alleRennenHolen()
    {
        $sql = "SELECT * FROM Rennen WHERE Datum >= CURDATE() ORDER BY Datum ASC";
        $stmt = $this->connect()->query($sql);
        return $stmt->fetchAll();
    }

    //Holt alle Rennen, an denen Fahrer des Teams teilnehmen, aus der Datenbank
    public function rennenMitTeilnahmenHolen($teamchef_loginname)
    {
        $sql = "SELECT DISTINCT r.RennId, r.Datum, r.Ort, r.Kilometer
                FROM Rennen r
                JOIN Teilnahme t ON r.RennId = t.RennId
                JOIN Fahrer f ON t.MitarbeiterId = f.`Mitarbeiter-ID`
                JOIN Team te ON f.Teamname = te.Teamname
                WHERE te.TeamchefLoginName = ?
                ORDER BY r.Datum DESC";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$teamchef_loginname]);
        return $stmt->fetchAll();
    }

    //Holt alle Fahrer eines bestimmten Rennens, die zum Teamchef gehören, aus der Datenbank
    public function fahrerNachRennenHolen($renn_id, $teamchef_loginname)
    {
        $sql = "SELECT f.Vorname, f.Nachname
                FROM Teilnahme t
                JOIN Fahrer f ON t.MitarbeiterId = f.`Mitarbeiter-ID`
                JOIN Team te ON f.Teamname = te.Teamname
                WHERE t.RennId = ? AND te.TeamchefLoginName = ?";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$renn_id, $teamchef_loginname]);
        return $stmt->fetchAll();
    }

    //Holt alle Fahrer eines Teamchefs aus der Datenbank
    public function alleFahrerHolen($teamchef_loginname)
    {
        $sql = "SELECT Fahrer.`Mitarbeiter-ID`, Fahrer.Vorname, Fahrer.Nachname
                FROM Fahrer
                JOIN Team ON Fahrer.Teamname = Team.Teamname
                WHERE Team.TeamchefLoginName = ?";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$teamchef_loginname]);
        return $stmt->fetchAll();
    }
    //Prüft ob ein Rennen existiert
    public function rennenExistiert($renn_id)
    {
        $sql = "SELECT COUNT(*) FROM Rennen WHERE RennId = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$renn_id]);

        return $stmt->fetchColumn() > 0;
    }

    public function rennenGehoertZuTeamchef($renn_id, $teamchef_loginname)
    {
        $sql = "SELECT COUNT(*)
            FROM Teilnahme t
            JOIN Fahrer f ON t.MitarbeiterId = f.`Mitarbeiter-ID`
            JOIN Team te ON f.Teamname = te.Teamname
            WHERE t.RennId = ?
            AND te.TeamchefLoginName = ?";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$renn_id, $teamchef_loginname]);

        return $stmt->fetchColumn() > 0;
    }

    //Meldet einen Fahrer zu einem Rennen an
    public function fahrerAnmelden($fahrer_ids, $renn_id, $teamchef_loginname)
    {
        $verbindung = $this->connect();

        try {
            $verbindung->beginTransaction();

            foreach ($fahrer_ids as $mitarbeiter_id) {

                $sql_team = "SELECT f.Teamname 
                         FROM Fahrer f 
                         JOIN Team t ON f.Teamname = t.Teamname 
                         WHERE f.`Mitarbeiter-ID` = ? 
                         AND t.TeamchefLoginName = ?";

                $stmt_team = $verbindung->prepare($sql_team);
                $stmt_team->execute([$mitarbeiter_id, $teamchef_loginname]);
                $fahrer = $stmt_team->fetch();

                if (!$fahrer) {
                    $verbindung->rollBack();
                    return false;
                }

                $sql_check = "SELECT COUNT(*) 
                          FROM Teilnahme 
                          WHERE MitarbeiterId = ? 
                          AND Teamname = ? 
                          AND RennId = ?";

                $stmt_check = $verbindung->prepare($sql_check);
                $stmt_check->execute([$mitarbeiter_id, $fahrer['Teamname'], $renn_id]);

                if ($stmt_check->fetchColumn() > 0) {
                    $verbindung->rollBack();
                    return false;
                }

                $sql_insert = "INSERT INTO Teilnahme (MitarbeiterId, Teamname, RennId) 
                           VALUES (?, ?, ?)";

                $stmt_insert = $verbindung->prepare($sql_insert);
                $stmt_insert->execute([$mitarbeiter_id, $fahrer['Teamname'], $renn_id]);
            }

            $verbindung->commit();
            return true;

        } catch (PDOException $e) {
            $verbindung->rollBack();
            return false;
        }
    }

    //Kopiert die Fahreranmeldungen von einem Rennen zu einem anderen Rennen
    public function teilnahmenKopieren($altes_rennen, $neues_rennen)
    {
        try {
            $stmt = $this->connect()->prepare("CALL kopiere_anmeldungen(?, ?)");
            $stmt->execute([$altes_rennen, $neues_rennen]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

}

$rennen_objekt = new Rennen();
$alle_rennen = $rennen_objekt->alleRennenHolen();
$alle_fahrer = $rennen_objekt->alleFahrerHolen($_SESSION['teamchef_loginname']);
$rennen_mit_teilnahmen = $rennen_objekt->rennenMitTeilnahmenHolen($_SESSION['teamchef_loginname']);

$renn_id = "";
$anzahl_fahrer = 0;
$formular_anzeigen = false;
$kopieren_anzeigen = false;
$fehlermeldung = "";
$erfolgsmeldung = "";

if (isset($_POST['kopieren_anzeigen'])) {
    $kopieren_anzeigen = true;
}

if (isset($_POST['rennen_auswaehlen'])) {
    if (!empty($_POST['rennid']) && !empty($_POST['anzahl_fahrer'])) {

        $renn_id = $_POST['rennid'];
        $anzahl_fahrer = (int) $_POST['anzahl_fahrer'];

        if ($anzahl_fahrer < 1 || $anzahl_fahrer > count($alle_fahrer)) {
            $fehlermeldung = "Bitte gib eine gültige Anzahl an Fahrern ein.";
        } else {
            $formular_anzeigen = true;
        }
    }
}
if (isset($_POST['fahrer_anmelden'])) {
    $renn_id = $_POST['renn_id'];
    $doppelt = false;
    $ausgewaehlte_fahrer = [];

    if (!$rennen_objekt->rennenExistiert($renn_id)) {
        $fehlermeldung = "Ungültiges Rennen ausgewählt.";
        $doppelt = true;
    }

    if (!$doppelt) {
        for ($i = 1; $i <= $_POST['anzahl_fahrer']; $i++) {
            $mitarbeiter_id = $_POST['fahrer_' . $i];

            if (in_array($mitarbeiter_id, $ausgewaehlte_fahrer)) {
                $fehlermeldung = "Ein Fahrer darf nicht mehrfach ausgewählt werden!";
                $doppelt = true;
                break;
            }

            $ausgewaehlte_fahrer[] = $mitarbeiter_id;
        }
    }

    if (!$doppelt) {
        $angemeldet = $rennen_objekt->fahrerAnmelden(
            $ausgewaehlte_fahrer,
            $renn_id,
            $_SESSION['teamchef_loginname']
        );

        if ($angemeldet) {
            $erfolgsmeldung = "Fahrer wurden erfolgreich angemeldet!";
        } else {
            $fehlermeldung = "Mindestens ein Fahrer ist bereits angemeldet oder ungültig.";
        }
    }
}

if (isset($_POST['kopieren'])) {
    $altes_rennen = $_POST['altes_rennen'] ?? '';
    $neues_rennen = $_POST['neues_rennen'] ?? '';

    if (empty($altes_rennen) || empty($neues_rennen)) {
        $fehlermeldung = "Bitte wähle beide Rennen aus!";
        $kopieren_anzeigen = true;
    } elseif ($altes_rennen === $neues_rennen) {
        $fehlermeldung = "Bitte wähle zwei verschiedene Rennen aus!";
        $kopieren_anzeigen = true;
    } elseif (!$rennen_objekt->rennenGehoertZuTeamchef($altes_rennen, $_SESSION['teamchef_loginname'])) {
        $fehlermeldung = "Das Quell-Rennen gehört nicht zu deinem Team.";
        $kopieren_anzeigen = true;
    } elseif (!$rennen_objekt->rennenExistiert($neues_rennen)) {
        $fehlermeldung = "Das Ziel-Rennen ist ungültig.";
        $kopieren_anzeigen = true;
    } else {
        $kopiert = $rennen_objekt->teilnahmenKopieren($altes_rennen, $neues_rennen);

        if ($kopiert) {
            $erfolgsmeldung = "Fahrer wurden erfolgreich kopiert!";
        } else {
            $fehlermeldung = "Fehler beim Kopieren der Fahrer.";
            $kopieren_anzeigen = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teamchef Startseite</title>
</head>

<body>

    <h1>Willkommen <?php echo htmlentities($_SESSION['teamchef_loginname']); ?>!</h1>

    <p><a href="logout.php">Logout</a></p>
    <p><a href="team_verwalten.php">Team verwalten</a></p>

    <?php if (!empty($fehlermeldung)): ?>
        <p><?php echo $fehlermeldung; ?></p>
    <?php endif; ?>
    <?php if (!empty($erfolgsmeldung)): ?>
        <p><?php echo $erfolgsmeldung; ?></p>
    <?php endif; ?>

    <!-- Fahrer manuell anmelden  -->
    <h2>Fahrer zu einem Rennen anmelden</h2>

    <form action="" method="POST">
        <fieldset>
            <legend>Schritt 1: Rennen und Anzahl Fahrer wählen</legend>

            <?php foreach ($alle_rennen as $rennen):
                $isChecked = ($renn_id == $rennen['RennId']) ? 'checked' : '';
                $rennId = $rennen['RennId'];
                $datum = $rennen['Datum'];
                $ort = $rennen['Ort'];
                $kilometer = $rennen['Kilometer'];
                ?>
                <p>
                    <label>
                        <input type="radio" name="rennid" value="<?php echo $rennId; ?>" <?php echo $isChecked; ?>>
                        Rennen <?php echo $rennId; ?> –
                        <?php echo $datum; ?> –
                        <?php echo htmlentities($ort); ?> –
                        <?php echo $kilometer; ?> km
                    </label>
                </p>
            <?php endforeach; ?>

            <label>Anzahl Fahrer:
                <input id="anzahl_fahrer" type="number" name="anzahl_fahrer" min="1" max=<?php echo count($alle_fahrer); ?> value="<?php echo $anzahl_fahrer; ?>" required>
            </label>

            <br><br>
            <input type="submit" name="rennen_auswaehlen" value="Rennen auswählen">
        </fieldset>
    </form>

    <?php if ($formular_anzeigen): ?>
        <form action="" method="POST">
            <fieldset>
                <legend>Schritt 2: Fahrer auswählen</legend>

                <input type="hidden" name="renn_id" value="<?php echo $renn_id; ?>">
                <input type="hidden" name="anzahl_fahrer" value="<?php echo $anzahl_fahrer; ?>">

                <table border="1">
                    <?php for ($i = 1; $i <= $anzahl_fahrer; $i++):
                        $fahrerName = 'fahrer_' . $i;
                        ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td>
                                <select id="<?php echo $fahrerName; ?>" name="<?php echo $fahrerName; ?>" required>
                                    <?php foreach ($alle_fahrer as $fahrer):
                                        $fahrerIdValue = $fahrer['Mitarbeiter-ID'];
                                        $fahrerFullName = $fahrer['Vorname'] . ' ' . $fahrer['Nachname'];
                                        ?>
                                        <option value="<?php echo $fahrerIdValue; ?>">
                                            <?php echo htmlentities($fahrerFullName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </table>

                <br>
                <input type="submit" name="fahrer_anmelden" value="Fahrer anmelden">
            </fieldset>
        </form>
    <?php endif; ?>


    <!-- Anmeldung kopieren  -->
    <h2>Anmeldung aus bestehendem Rennen kopieren</h2>

    <form action="" method="POST">
        <input type="submit" name="kopieren_anzeigen" value="Kopierformular anzeigen">
    </form>

    <?php if ($kopieren_anzeigen): ?>
        <form action="" method="POST">
            <fieldset>
                <legend>Teilnahmen kopieren</legend>

                <table>
                    <tr>
                        <td style="vertical-align:top; padding-right:40px">
                            <strong>Quell-Rennen (bisherige Fahrer)</strong><br>
                            <?php foreach ($rennen_mit_teilnahmen as $rennen): ?>
                                <p>
                                    <label>
                                        <input type="radio" name="altes_rennen" value="<?php echo $rennen['RennId']; ?>">
                                        Rennen <?php echo $rennen['RennId']; ?> –
                                        <?php echo $rennen['Datum']; ?>
                                    </label>
                                    <br>
                                    <small>Fahrer:
                                        <?php
                                        $fahrer_liste = $rennen_objekt->fahrerNachRennenHolen(
                                            $rennen['RennId'],
                                            $_SESSION['teamchef_loginname']
                                        );
                                        $namen = array_map(fn($f) => $f['Vorname'] . ' ' . $f['Nachname'], $fahrer_liste);
                                        echo htmlentities(implode(', ', $namen));
                                        ?>
                                    </small>
                                </p>
                            <?php endforeach; ?>
                        </td>
                        <td style="vertical-align:top">
                            <strong>Ziel-Rennen (wohin kopieren)</strong><br>
                            <?php foreach ($alle_rennen as $rennen): ?>
                                <p>
                                    <label>
                                        <input type="radio" name="neues_rennen" value="<?php echo $rennen['RennId']; ?>">
                                        Rennen <?php echo $rennen['RennId']; ?> –
                                        <?php echo $rennen['Datum']; ?> –
                                        <?php echo htmlentities($rennen['Ort']); ?>
                                    </label>
                                </p>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>

                <br>
                <input type="submit" name="kopieren" value="Fahrer kopieren">
            </fieldset>
        </form>
    <?php endif; ?>

</body>

</html>

<!-- Lena Strohmenger Ende -->