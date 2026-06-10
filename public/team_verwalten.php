<?php
/**
 * Verwaltungsseite für Fahrer eines Teams
 * Nicolas Biercher
 */

include_once('include/session_management.php');
include_once('classes/TeamVerwaltung.php');

sitzungStarten();
zugriffPruefen('teamchef_loginname');

$teamchef_loginname = $_SESSION['teamchef_loginname'];
$verwaltung = new TeamVerwaltung();

$meldung = "";
$fehler = "";
$bearbeitungsmodus = false;

function leereFormulardaten(): array {
    return [
        'mitarbeiter_id' => '',
        'vorname' => '',
        'nachname' => '',
        'strasse' => '',
        'hausnummer' => '',
        'telefonnummer' => '',
        'plz' => '',
        'ort' => ''
    ];
}

$formulardaten = leereFormulardaten();

try {
    $team = $verwaltung->teamNachLoginnamenLaden($teamchef_loginname);
} catch (PDOException $e) {
    $team = false;
}

if (!$team) {
    header("Location: index.php?fehler=kein_team");
    exit();
}

$teamname = $team['Teamname'];

if (isset($_GET['bearbeiten'])) {
    $mitarbeiter_id = filter_input(INPUT_GET, 'bearbeiten', FILTER_VALIDATE_INT);

    if ($mitarbeiter_id === false || $mitarbeiter_id === null || $mitarbeiter_id <= 0) {
        $fehler = "Ungültige Mitarbeiter-ID.";
    } else {
        try {
            $fahrer = $verwaltung->einzelnenFahrerLaden($mitarbeiter_id, $teamname);

            if ($fahrer) {
                $bearbeitungsmodus = true;

                $formulardaten['mitarbeiter_id'] = $verwaltung->sicherenWertAuslesen($fahrer, 'Mitarbeiter-ID');
                $formulardaten['vorname'] = $verwaltung->sicherenWertAuslesen($fahrer, 'Vorname');
                $formulardaten['nachname'] = $verwaltung->sicherenWertAuslesen($fahrer, 'Nachname');
                $formulardaten['strasse'] = $verwaltung->sicherenWertAuslesen($fahrer, 'Straße');
                $formulardaten['hausnummer'] = $verwaltung->sicherenWertAuslesen($fahrer, 'Hausnummer');
                $formulardaten['telefonnummer'] = $verwaltung->sicherenWertAuslesen($fahrer, 'Telefonnummer');
                $formulardaten['plz'] = $verwaltung->sicherenWertAuslesen($fahrer, 'PLZ');
                $formulardaten['ort'] = $verwaltung->sicherenWertAuslesen($fahrer, 'Ort');
            } else {
                $fehler = "Fahrer wurde nicht gefunden.";
            }
        } catch (PDOException $e) {
            $fehler = "Fehler beim Laden des Fahrers.";
        }
    }
}

if (isset($_POST['fahrer_speichern'])) {
    if (!csrfTokenGueltig()) {
        $fehler = "Ungültige Anfrage. Bitte die Seite neu laden.";
    } else {
        $formulardaten['mitarbeiter_id'] = $verwaltung->postWertLesen('mitarbeiter_id');
        $formulardaten['vorname'] = $verwaltung->postWertLesen('vorname');
        $formulardaten['nachname'] = $verwaltung->postWertLesen('nachname');
        $formulardaten['strasse'] = $verwaltung->postWertLesen('strasse');
        $formulardaten['hausnummer'] = $verwaltung->postWertLesen('hausnummer');
        $formulardaten['telefonnummer'] = $verwaltung->postWertLesen('telefonnummer');
        $formulardaten['plz'] = $verwaltung->postWertLesen('plz');
        $formulardaten['ort'] = $verwaltung->postWertLesen('ort');

        $bearbeitungsmodus = isset($_POST['bearbeitungsmodus']) && $_POST['bearbeitungsmodus'] === '1';

        $fehlerliste = $verwaltung->fahrerDatenValidieren($formulardaten);

        if (!empty($fehlerliste)) {
            $fehler = implode(" ", $fehlerliste);
        } else {
            try {
                if ($bearbeitungsmodus && $formulardaten['mitarbeiter_id'] === '') {
                    $fehler = "Mitarbeiter-ID fehlt.";
                } else {
                    $mitarbeiter_id = $bearbeitungsmodus ? (int) $formulardaten['mitarbeiter_id'] : null;

                    $ergebnis = $verwaltung->fahrerSpeichern(
                        $mitarbeiter_id,
                        $formulardaten['vorname'],
                        $formulardaten['nachname'],
                        $formulardaten['strasse'],
                        $formulardaten['hausnummer'],
                        $formulardaten['telefonnummer'],
                        $formulardaten['plz'],
                        $formulardaten['ort'],
                        $teamname
                    );

                    if ($ergebnis['erfolg'] == 1) {
                        $meldung = $ergebnis['meldung'];
                        if (!$bearbeitungsmodus) {
                            $formulardaten = leereFormulardaten();
                        }
                    } else {
                        $fehler = $ergebnis['meldung'];
                    }
                }
            } catch (PDOException $e) {
                $fehler = $bearbeitungsmodus
                    ? "Fehler beim Ändern des Fahrers."
                    : "Fehler beim Anlegen des Fahrers.";
            }
        }
    }
}

if (isset($_POST['fahrer_loeschen'])) {
    if (!csrfTokenGueltig()) {
        $fehler = "Ungültige Anfrage. Bitte die Seite neu laden.";
    } else {
        $mitarbeiter_id = filter_input(INPUT_POST, 'mitarbeiter_id', FILTER_VALIDATE_INT);

        if ($mitarbeiter_id === false || $mitarbeiter_id === null || $mitarbeiter_id <= 0) {
            $fehler = "Ungültige Mitarbeiter-ID.";
        } else {
            try {
                $meldung = $verwaltung->fahrerAusTeamEntfernen($mitarbeiter_id, $teamname);
            } catch (PDOException $e) {
                $fehler = "Fahrer konnte nicht gelöscht werden, da noch Trainings- oder Renndaten vorhanden sind.";
            }
        }
    }
}

try {
    $alle_fahrer = $verwaltung->alleFahrerDesTeamsLaden($teamname);
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

        <h2>
            <?php echo $bearbeitungsmodus ? "Fahrer bearbeiten" : "Fahrer erstellen"; ?>
        </h2>

        <form action="" method="POST">
            <input type="hidden" name="csrf_token"        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="bearbeitungsmodus" value="<?php echo $bearbeitungsmodus ? '1' : '0'; ?>">

            <?php if ($bearbeitungsmodus): ?>
                <input type="hidden" name="mitarbeiter_id" value="<?php echo htmlspecialchars($formulardaten['mitarbeiter_id']); ?>">
                <p>Mitarbeiter-ID: <?php echo htmlspecialchars($formulardaten['mitarbeiter_id']); ?></p>
            <?php endif; ?>

            <p>
                <label for="vorname">Vorname</label><br>
                <input id="vorname" name="vorname" value="<?php echo htmlspecialchars($formulardaten['vorname']); ?>" required>
            </p>

            <p>
                <label for="nachname">Nachname</label><br>
                <input id="nachname" name="nachname" value="<?php echo htmlspecialchars($formulardaten['nachname']); ?>" required>
            </p>

            <p>
                <label for="strasse">Straße</label><br>
                <input id="strasse" name="strasse" value="<?php echo htmlspecialchars($formulardaten['strasse']); ?>" required>
            </p>

            <p>
                <label for="hausnummer">Hausnummer</label><br>
                <input id="hausnummer" name="hausnummer" value="<?php echo htmlspecialchars($formulardaten['hausnummer']); ?>" required>
            </p>

            <p>
                <label for="telefonnummer">Telefonnummer</label><br>
                <input id="telefonnummer" name="telefonnummer" value="<?php echo htmlspecialchars($formulardaten['telefonnummer']); ?>" required>
            </p>

            <p>
                <label for="plz">PLZ</label><br>
                <input id="plz" name="plz" maxlength="5" value="<?php echo htmlspecialchars($formulardaten['plz']); ?>" required>
            </p>

            <p>
                <label for="ort">Ort</label><br>
                <input id="ort" name="ort" value="<?php echo htmlspecialchars($formulardaten['ort']); ?>" required>
            </p>

            <p>
                <input type="submit" name="fahrer_speichern" value="Speichern">
            </p>
        </form>

        <?php if ($bearbeitungsmodus): ?>
            <p><a href="team_verwalten.php">Neuen Fahrer erstellen</a></p>
        <?php endif; ?>

        <h2>Alle Fahrer</h2>

        <table>
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
                        <td><?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'Mitarbeiter-ID'); ?></td>
                        <td><?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'Vorname'); ?></td>
                        <td><?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'Nachname'); ?></td>
                        <td><?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'Straße'); ?></td>
                        <td><?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'Hausnummer'); ?></td>
                        <td><?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'Telefonnummer'); ?></td>
                        <td><?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'PLZ'); ?></td>
                        <td><?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'Ort'); ?></td>
                        <td><?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'Teamname'); ?></td>
                        <td>
                            <a href="team_verwalten.php?bearbeiten=<?php echo urlencode($verwaltung->sicherenWertAuslesen($fahrer, 'Mitarbeiter-ID')); ?>">Bearbeiten</a>

                            <form action="" method="POST">
                                <input type="hidden" name="csrf_token"     value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="mitarbeiter_id" value="<?php echo $verwaltung->sicherenWertAuslesen($fahrer, 'Mitarbeiter-ID'); ?>">
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

    </body>
</html>

<!-- Nicolas Biercher Ende -->
