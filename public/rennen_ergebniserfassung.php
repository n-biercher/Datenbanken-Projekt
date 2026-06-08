<!-- Lena Strohmenger Beginn -->

<?php
include_once('session_management.php');
sitzungStarten();
zugriffPruefen('veranstalter_loginname', 'veranstalter_einloggen.php');

include_once('dbh.php');


class Ergebniserfassung extends Dbh
{

    public function vergangeneRennenHolen($veranstalter_loginname)
    {
        $sql = "SELECT * 
            FROM Rennen 
            WHERE VeranstalterLoginName = ? 
            ORDER BY Datum DESC";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$veranstalter_loginname]);
        return $stmt->fetchAll();
    }


    public function fahrerZuRennenHolen($renn_id, $veranstalter_loginname)
    {
        $sql = "SELECT t.MitarbeiterId, t.Teamname, t.Startnummer, f.Vorname,f.Nachname
            FROM Teilnahme t
            JOIN Fahrer f
                 ON t.MitarbeiterId = f.`Mitarbeiter-ID`
                 AND t.Teamname = f.Teamname
            JOIN Rennen r
                 ON t.RennId = r.RennId
            WHERE t.RennId = ?
            AND r.VeranstalterLoginName = ?";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$renn_id, $veranstalter_loginname]);
        return $stmt->fetchAll();
    }

    public function ergebnisseSpeichern($renn_id, $fahrer_daten, $mitarbeiter_ids)
    {
        $verbindung = $this->connect();

        try {
            $verbindung->beginTransaction();

            $sql = "UPDATE Teilnahme 
                SET Platzierung = ?, Zeit = ? 
                WHERE RennId = ? 
                AND MitarbeiterId = ? 
                AND Teamname = ?";

            $stmt = $verbindung->prepare($sql);

            foreach ($mitarbeiter_ids as $mitarbeiter_id) {
                $platzierung = $_POST['platzierung_' . $mitarbeiter_id];
                $zeit = $_POST['zeit_' . $mitarbeiter_id];
                $teamname = $fahrer_daten[$mitarbeiter_id];

                $stmt->execute([$platzierung, $zeit, $renn_id, $mitarbeiter_id, $teamname]);
            }

            $verbindung->commit();
            return true;

        } catch (PDOException $e) {
            $verbindung->rollBack();
            return false;
        }
    }

}

$erfassung_objekt = new Ergebniserfassung();
$vergangeneRennen = $erfassung_objekt->vergangeneRennenHolen($_SESSION['veranstalter_loginname']);

$renn_id = "";
$tabelle_anzeigen = false;
$vergebene_platzierungen = [];

$fehlermeldung = "";
$erfolgsmeldung = "";

if (isset($_POST['rennen_auswaehlen'])) {
    if (!empty($_POST['rennid'])) {
        $renn_id = $_POST['rennid'];
        $tabelle_anzeigen = true;
    } else {
        $fehlermeldung = "Bitte wähle ein Rennen aus.";
    }
}

if ($tabelle_anzeigen) {
    $fahrer_liste = $erfassung_objekt->fahrerZuRennenHolen($renn_id, $_SESSION['veranstalter_loginname']);
}


if (isset($_POST['ergebnisse_speichern'])) {
    $renn_id = $_POST['renn_id'];
    $fahrer_liste = $erfassung_objekt->fahrerZuRennenHolen($renn_id, $_SESSION['veranstalter_loginname']);

    $fehler = false;

    if (empty($fahrer_liste)) {
        $fehlermeldung = "Ungültiges Rennen oder keine Fahrer vorhanden.";
        $fehler = true;
    }

    $fahrer_daten = [];

    foreach ($fahrer_liste as $fahrer) {
        $fahrer_daten[$fahrer['MitarbeiterId']] = $fahrer['Teamname'];
    }

    $max_platzierung = count($fahrer_liste);

    foreach ($_POST['mitarbeiter_ids'] as $mitarbeiter_id) {


        if (!isset($fahrer_daten[$mitarbeiter_id])) {
            $fehlermeldung = "Ungültiger Fahrer übermittelt.";
            $fehler = true;
            break;
        }
        $platzierung = $_POST['platzierung_' . $mitarbeiter_id];
        $zeit = $_POST['zeit_' . $mitarbeiter_id];
        $teamname = $fahrer_daten[$mitarbeiter_id];

        if (empty($platzierung) || empty($zeit)) {
            $fehlermeldung = "Bitte fülle alle Platzierungen und Zeiten aus.";
            $fehler = true;
            break;
        }

        if ($platzierung < 1 || $platzierung > $max_platzierung) {
            $fehlermeldung = "Ungültige Platzierung für Mitarbeiter-ID: " . $mitarbeiter_id;
            $fehler = true;
            break;
        }
        if (in_array($platzierung, $vergebene_platzierungen)) {
            $fehlermeldung = "Jede Platzierung darf nur einmal vergeben werden.";
            $fehler = true;
            break;
        }

        $vergebene_platzierungen[] = $platzierung;
    }
    if (!$fehler) {
        $gespeichert = $erfassung_objekt->ergebnisseSpeichern(
            $renn_id,
            $fahrer_daten,
            $_POST['mitarbeiter_ids']
        );

        if ($gespeichert) {
            $erfolgsmeldung = "Ergebnisse wurden erfolgreich gespeichert!";
        } else {
            $fehlermeldung = "Fehler beim Speichern der Ergebnisse.";
        }
    }


}


?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rennen Ergebniserfassung</title>
</head>

<body>
    <h1>Rennen Ergebniserfassung</h1>

    <p><a href="logout.php">Logout</a></p>

    <?php if (!empty($fehlermeldung)): ?>
        <p>
            <?php echo htmlentities($fehlermeldung); ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($erfolgsmeldung)): ?>
        <p>
            <?php echo htmlentities($erfolgsmeldung); ?>
        </p>
    <?php endif; ?>


    <form action="" method="POST">
        <fieldset>
            <legend>Bitte wähle ein Rennen aus um die Ergbnisse zu erfassen</legend>

            <?php
            foreach ($vergangeneRennen as $rennen) {
                echo '<p>';

                echo '<label>';

                echo '<input type="radio" name="rennid" value="' . $rennen['RennId'] . '"> ';

                echo 'Rennen ' . $rennen['RennId'] . ' - ';

                echo $rennen['Datum'] . ' - ';

                echo htmlentities($rennen['Ort']) . ' - ';

                echo $rennen['Kilometer'] . ' km';

                echo '</label>';

                echo '</p>';
            }
            ?>

            <p>
                <input type="submit" name="rennen_auswaehlen" value="Rennen auswählen">
            </p>

        </fieldset>
    </form>

    <?php if ($tabelle_anzeigen) { ?>

        <h2>Ergebnisse für Rennen
            <?php echo $renn_id; ?> erfassen
        </h2>

        <form action="" method="POST">
            <input type="hidden" name="renn_id" value="<?php echo $renn_id; ?>">

            <table border="1" cellpadding="8">
                <tr>

                    <th>Startnummer</th>
                    <th>Fahrer</th>
                    <th>Teamname</th>
                    <th>Platzierung</th>
                    <th>Zeit</th>
                </tr>

                <?php
                foreach ($fahrer_liste as $fahrer) {
                    echo '<tr>';

                    echo '<td>' . $fahrer['Startnummer'] . '</td>';
                    echo '<td>' . htmlentities($fahrer['Vorname']) . ' ' . htmlentities($fahrer['Nachname']) . '</td>';
                    echo '<td>' . htmlentities($fahrer['Teamname']) . '</td>';

                    echo '<td>';
                    echo '<select name="platzierung_' . $fahrer['MitarbeiterId'] . '" required>';
                    echo '<option value="">Bitte wählen</option>';

                    for ($platz = 1; $platz <= count($fahrer_liste); $platz++) {
                        echo '<option value="' . $platz . '">' . $platz . '</option>';
                    }

                    echo '</select>';
                    echo '</td>';

                    echo '<td><input type="time" name="zeit_' . $fahrer['MitarbeiterId'] . '" step="1" required></td>';

                    echo '<input type="hidden" name="mitarbeiter_ids[]" value="' . $fahrer['MitarbeiterId'] . '">';


                    echo '</tr>';
                }
                ?>
            </table>

            <p>
                <input type="submit" name="ergebnisse_speichern" value="Ergebnisse speichern">
            </p>

            <p><a href="veranstalter_startseite.php">Zurück zur Startseite</a></p>
        </form>

    <?php } ?>

</body>

</html>

<!-- Lena Strohmenger Ende -->