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
}


$rennen_objekt = new Rennen();
$alle_rennen = $rennen_objekt->alleRennenHolen();
$alle_fahrer = $rennen_objekt->alleFahrerHolen($_SESSION['teamchef_loginname']);

$renn_id = "";
$anzahl_fahrer = 0;
$formular_anzeigen = false;
$fehlermeldung = "";

if (isset($_POST['rennen_auswaehlen'])) {
    if (isset($_POST['rennid']) && isset($_POST['anzahl_fahrer'])) {
        $renn_id = $_POST['rennid'];
        $anzahl_fahrer = (int) $_POST['anzahl_fahrer'];
        $formular_anzeigen = true;
    } else {
        $fehlermeldung = "Bitte wähle ein Rennen aus und gib die Anzahl der Fahrer an.";
    }
}

if (isset($_POST['fahrer_anmelden'])) {

    $renn_id = $_POST['renn_id'];

    for ($i = 1; $i <= $_POST['anzahl_fahrer']; $i++) {

        $mitarbeiter_id = $_POST['fahrer_' . $i];

        $rennen_objekt->fahrerAnmelden($mitarbeiter_id, $renn_id);

    }

}

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veranstalter Startseite</title>
</head>

<body>

    <h1>Willkommen <?php echo $_SESSION['teamchef_loginname']; ?>!</h1>

    <p>Du bist jetzt eingeloggt.</p>

    <p><a href="logout.php">Logout</a></p>
    <p><a href="team_verwalten.php">Team verwalten</a></p> <!-- Nicolas Biercher -->

    <h2>Fahrer zu einem Rennen anmelden</h2>

    <form action="" method="POST">
        <fieldset>
            <legend>Bitte wähle ein Rennen aus</legend>

            <?php
            if (!empty($alle_rennen)) {
                foreach ($alle_rennen as $rennen) {
                    echo '<p>';
                    echo '<label>';
                    echo '<input type="radio" name="rennid" value="' . $rennen['RennId'] . '"> ';
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
                <input id="anzahl_fahrer" name="anzahl_fahrer" type="number" min="1" max="10" required value="<?php echo $anzahl_fahrer; ?>">
            </p>

            <p><input type="submit" name="rennen_auswaehlen" value="Rennen auswählen" required></p>

            <?php
            if (!empty($fehlermeldung)) {
                echo '<p style="color: red;">' . $fehlermeldung . '</p>';
            }
            ?>

            <?php
            if ($formular_anzeigen) { ?>
                <h3>Fahrer für Rennen <?php echo $renn_id; ?> eingeben</h3>

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

                <p><input type="submit" name="fahrer_anmelden" value="Fahrer anmelden"></p>
            <?php } ?>


        </fieldset>
    </form>
</body>

</html>

<!-- Lena Strohmenger Ende -->