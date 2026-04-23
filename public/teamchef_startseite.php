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
}

$rennen_objekt = new Rennen();
$alle_rennen = $rennen_objekt->alleRennenHolen();

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
    <p><a href="team_verwalten.php">Team verwalten</a></p> // Nicolas Biercher

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

            <p><input type="submit" name="rennen_auswaehlen" value="Rennen auswählen"></p>
        </fieldset>
    </form>
</body>



</html>

<!-- Lena Strohmenger Ende -->