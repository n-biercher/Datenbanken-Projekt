<!-- Lena Strohmenger Beginn -->

<?php
session_start();

if (!isset($_SESSION['teamchef_loginname'])) {
    header("Location: teamchef_anmelden.php");
    exit();
}

include_once('dbh.php');

class Rennen extends Dbh
{
    public function alleRennenHolen()
    {
        $sql = "SELECT * FROM Rennen WHERE Datum >= CURDATE() ORDER BY Datum ASC";
        $stmt = $this->connect()->query($sql);
        return $stmt->fetchAll();
    }

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

    public function fahrerAnmelden($mitarbeiter_id, $renn_id)
    {
        $sql_team = "SELECT Teamname FROM Fahrer WHERE `Mitarbeiter-ID` = ?";
        $stmt_team = $this->connect()->prepare($sql_team);
        $stmt_team->execute([$mitarbeiter_id]);
        $fahrer = $stmt_team->fetch();

        $sql_insert = "INSERT INTO Teilnahme (MitarbeiterId, Teamname, RennId)
                       VALUES (?, ?, ?)";
        $stmt_insert = $this->connect()->prepare($sql_insert);
        $stmt_insert->execute([$mitarbeiter_id, $fahrer['Teamname'], $renn_id]);
    }

    public function teilnahmenKopieren($altes_rennen, $neues_rennen)
    {
        $sql = "CALL kopiere_anmeldungen(?, ?)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$altes_rennen, $neues_rennen]);
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

if (isset($_POST['rennen_auswaehlen'])) {
    if (!empty($_POST['rennid']) && !empty($_POST['anzahl_fahrer'])) {
        $renn_id = $_POST['rennid'];
        $anzahl_fahrer = (int) $_POST['anzahl_fahrer'];
        $formular_anzeigen = true;
    } else {
        $fehlermeldung = "Bitte wähle ein Rennen aus und gib die Anzahl der Fahrer an.";
    }
}

if (isset($_POST['kopieren_anzeigen'])) {
    if (!empty($_POST['rennid'])) {
        $renn_id = $_POST['rennid'];
        $kopieren_anzeigen = true;
    } else {
        $fehlermeldung = "Bitte wähle zuerst das neue Rennen aus.";
    }
}

if (isset($_POST['fahrer_anmelden'])) {
    $renn_id = $_POST['renn_id'];

    for ($i = 1; $i <= $_POST['anzahl_fahrer']; $i++) {
        $mitarbeiter_id = $_POST['fahrer_' . $i];
        $rennen_objekt->fahrerAnmelden($mitarbeiter_id, $renn_id);
    }

    $erfolgsmeldung = "Fahrer wurden erfolgreich angemeldet!";
}

if (isset($_POST['kopieren'])) {
    if (!empty($_POST['altes_rennen']) && !empty($_POST['renn_id'])) {
        $altes_rennen = $_POST['altes_rennen'];
        $neues_rennen = $_POST['renn_id'];

        if ($altes_rennen == $neues_rennen) {
            $fehlermeldung = "Du kannst nicht in dasselbe Rennen kopieren.";
        } else {
            $rennen_objekt->teilnahmenKopieren($altes_rennen, $neues_rennen);
            $erfolgsmeldung = "Fahrer wurden erfolgreich kopiert!";
        }
    } else {
        $fehlermeldung = "Bitte wähle ein Rennen aus, aus dem kopiert werden soll.";
    }

    $kopieren_anzeigen = true;
    $renn_id = $_POST['renn_id'];
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
    <title>Team <?php echo htmlspecialchars($teamname); ?> verwalten</title>
=======
    <title>Teamchef Startseite</title>
>>>>>>> 9d839d91a3d4ddf013a705c45e12bab591474e8e
</head>

<body>

<<<<<<< HEAD
<?php if ($meldung !== ""): ?>
    <p><?php echo htmlspecialchars($meldung); ?></p>
<?php endif; ?>
=======
<h1>Willkommen <?php echo $_SESSION['teamchef_loginname']; ?>!</h1>

<p><a href="logout.php">Logout</a></p>
<p><a href="team_verwalten.php">Team verwalten</a></p>

<h2>Fahrer zu einem Rennen anmelden</h2>
>>>>>>> 9d839d91a3d4ddf013a705c45e12bab591474e8e

<form action="" method="POST">
    <fieldset>
        <legend>Bitte wähle ein Rennen aus</legend>

        <?php
        if (!empty($alle_rennen)) {
            foreach ($alle_rennen as $rennen) {
                echo '<p>';
                echo '<label>';
                echo '<input type="radio" name="rennid" value="' . $rennen['RennId'] . '"';

                if ($renn_id == $rennen['RennId']) {
                    echo ' checked';
                }

                echo '> ';
                echo 'Rennen ' . $rennen['RennId'] . ' - ';
                echo $rennen['Datum'] . ' - ';
                echo $rennen['Ort'] . ' - ';
                echo $rennen['Kilometer'] . ' km';
                echo '</label>';
                echo '</p>';
            }
        } else {
            echo '<p>Es sind aktuell keine Rennen vorhanden.</p>';
        }
        ?>

        <p>
            <label for="anzahl_fahrer">Anzahl Fahrer anmelden:</label><br>
            <input id="anzahl_fahrer" name="anzahl_fahrer" type="number" min="1" max="10"
                   value="<?php echo $anzahl_fahrer; ?>">
        </p>

        <p>
            <input type="submit" name="rennen_auswaehlen" value="Rennen auswählen">
            <input type="submit" name="kopieren_anzeigen" value="Anmeldung kopieren" formnovalidate>
        </p>

        <?php
        if (!empty($fehlermeldung)) {
            echo '<p style="color: red;">' . $fehlermeldung . '</p>';
        }

        if (!empty($erfolgsmeldung)) {
            echo '<p>' . $erfolgsmeldung . '</p>';
        }
        ?>

        <?php if ($formular_anzeigen) { ?>

            <h3>Fahrer für Rennen <?php echo $renn_id; ?> auswählen</h3>

            <input type="hidden" name="renn_id" value="<?php echo $renn_id; ?>">
            <input type="hidden" name="anzahl_fahrer" value="<?php echo $anzahl_fahrer; ?>">

            <table border="1" cellpadding="8">
                <tr>
                    <th>Nr.</th>
                    <th>Fahrername</th>
                </tr>

                <?php
                for ($i = 1; $i <= $anzahl_fahrer; $i++) {
                    echo '<tr>';
                    echo '<td>' . $i . '</td>';
                    echo '<td>';
                    echo '<select name="fahrer_' . $i . '" required>';
                    echo '<option value="">Bitte wählen</option>';

                    foreach ($alle_fahrer as $fahrer) {
                        echo '<option value="' . $fahrer['Mitarbeiter-ID'] . '">';
                        echo $fahrer['Vorname'] . ' ' . $fahrer['Nachname'];
                        echo '</option>';
                    }

                    echo '</select>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </table>

            <p>
                <input type="submit" name="fahrer_anmelden" value="Fahrer anmelden">
            </p>

        <?php } ?>

        <?php if ($kopieren_anzeigen) { ?>

            <h3>Rennen auswählen, aus dem kopiert werden soll</h3>

            <input type="hidden" name="renn_id" value="<?php echo $renn_id; ?>">

            <?php
            if (!empty($rennen_mit_teilnahmen)) {
                foreach ($rennen_mit_teilnahmen as $rennen) {
                    echo '<p>';
                    echo '<label>';
                    echo '<input type="radio" name="altes_rennen" value="' . $rennen['RennId'] . '"> ';
                    echo 'Rennen ' . $rennen['RennId'] . ' - ';
                    echo $rennen['Datum'] . ' - ';
                    echo $rennen['Ort'] . ' - ';
                    echo $rennen['Kilometer'] . ' km';
                    echo '</label>';

                    $fahrer_liste = $rennen_objekt->fahrerNachRennenHolen(
                        $rennen['RennId'],
                        $_SESSION['teamchef_loginname']
                    );

                    echo '<br><small>Fahrer: ';

                    foreach ($fahrer_liste as $fahrer) {
                        echo $fahrer['Vorname'] . ' ' . $fahrer['Nachname'] . '; ';
                    }

                    echo '</small>';
                    echo '</p>';
                }

                echo '<p><input type="submit" name="kopieren" value="Fahrer aus ausgewähltem Rennen kopieren"></p>';
            } else {
                echo '<p>Es gibt noch kein Rennen mit angemeldeten Fahrern.</p>';
            }
            ?>

        <?php } ?>

    </fieldset>
</form>

</body>
</html>

<!-- Lena Strohmenger Ende -->