<?php
// Nicolas Biercher Beginn

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(403);
    exit();
}

include_once('include/dbh.php');

class TeamVerwaltung extends Dbh {
    function sicherenWertAuslesen(array $datensatz, string $schluessel): string {
        return isset($datensatz[$schluessel]) ? htmlspecialchars($datensatz[$schluessel]) : '';
    }

    function postWertLesen(string $feldname): string {
        return isset($_POST[$feldname]) ? trim($_POST[$feldname]) : '';
    }

    function fahrerDatenValidieren(array $daten): array {
        $fehlerliste = [];

        if (strlen($daten['vorname']) < 2 || strlen($daten['vorname']) > 50) {
            $fehlerliste[] = "Vorname muss zwischen 2 und 50 Zeichen lang sein.";
        }

        if (strlen($daten['nachname']) < 2 || strlen($daten['nachname']) > 50) {
            $fehlerliste[] = "Nachname muss zwischen 2 und 50 Zeichen lang sein.";
        }

        if (strlen($daten['strasse']) < 2 || strlen($daten['strasse']) > 100) {
            $fehlerliste[] = "Straße muss zwischen 2 und 100 Zeichen lang sein.";
        }

        if (!preg_match('/^\d+[a-zA-Z]?$/', $daten['hausnummer'])) {
            $fehlerliste[] = "Hausnummer ist ungültig.";
        }

        if (!preg_match('/^[\d\s\+\-\/]{6,20}$/', $daten['telefonnummer'])) {
            $fehlerliste[] = "Telefonnummer enthält ungültige Zeichen oder ist zu kurz/lang.";
        }

        if (!preg_match('/^\d{5}$/', $daten['plz'])) {
            $fehlerliste[] = "PLZ muss genau 5 Ziffern enthalten.";
        }

        if (strlen($daten['ort']) < 2 || strlen($daten['ort']) > 100) {
            $fehlerliste[] = "Ort muss zwischen 2 und 100 Zeichen lang sein.";
        }

        return $fehlerliste;
    }

    public function teamNachLoginnamenLaden(string $loginname): array|false {
        $db = $this->connect();

        $abfrage = $db->prepare("
            SELECT Teamname
            FROM Team
            WHERE TeamchefLoginName = ?
        ");
        $abfrage->execute([$loginname]);

        return $abfrage->fetch(PDO::FETCH_ASSOC);
    }

    public function alleFahrerDesTeamsLaden(string $teamname): array {
        $db = $this->connect();

        $abfrage = $db->prepare("
            SELECT `Mitarbeiter-ID`, Vorname, Nachname, `Straße`, Hausnummer, Telefonnummer, PLZ, Ort, Teamname
            FROM Fahrer
            WHERE Teamname = ?
            ORDER BY `Mitarbeiter-ID` ASC
        ");
        $abfrage->execute([$teamname]);

        return $abfrage->fetchAll(PDO::FETCH_ASSOC);
    }

    public function einzelnenFahrerLaden(int $mitarbeiter_id, string $teamname): array|false {
        $db = $this->connect();

        $abfrage = $db->prepare("
            SELECT `Mitarbeiter-ID`, Vorname, Nachname, `Straße`, Hausnummer, Telefonnummer, PLZ, Ort, Teamname
            FROM Fahrer
            WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
        ");
        $abfrage->execute([$mitarbeiter_id, $teamname]);

        return $abfrage->fetch(PDO::FETCH_ASSOC);
    }

    public function fahrerSpeichern(
        ?int   $mitarbeiter_id,
        string $vorname,
        string $nachname,
        string $strasse,
        string $hausnummer,
        string $telefonnummer,
        string $plz,
        string $ort,
        string $teamname): array {
        if ($mitarbeiter_id !== null) {
            $this->fahrerDatenAktualisieren(
                $mitarbeiter_id, $vorname, $nachname, $strasse,
                $hausnummer, $telefonnummer, $plz, $ort, $teamname
            );
            return ['erfolg' => 1, 'meldung' => 'Fahrer wurde erfolgreich geändert.'];
        }

        $ergebnis = $this->fahrerNeuAnlegen(
            $vorname, $nachname, $strasse, $hausnummer,
            $telefonnummer, $plz, $ort, $teamname
        );
        return $ergebnis ?: ['erfolg' => 0, 'meldung' => 'Fahrer konnte nicht angelegt werden.'];
    }

    private function fahrerNeuAnlegen(
        string $vorname,
        string $nachname,
        string $strasse,
        string $hausnummer,
        string $telefonnummer,
        string $plz,
        string $ort,
        string $teamname): array|false {
        $db = $this->connect();

        $db->beginTransaction();

        try {
            $abfrage = $db->prepare("
                CALL fahrer_anlegen(?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $abfrage->execute([
                $vorname,
                $nachname,
                $strasse,
                $hausnummer,
                $telefonnummer,
                $plz,
                $ort,
                $teamname
            ]);

            $ergebnis = $abfrage->fetch(PDO::FETCH_ASSOC);
            $abfrage->closeCursor();

            $db->commit();

            return $ergebnis;
        } catch (PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function fahrerDatenAktualisieren(
        int    $mitarbeiter_id,
        string $vorname,
        string $nachname,
        string $strasse,
        string $hausnummer,
        string $telefonnummer,
        string $plz,
        string $ort,
        string $teamname): string {
        $abfrage = $this->connect()->prepare("
            UPDATE Fahrer
            SET
                Vorname       = ?,
                Nachname      = ?,
                `Straße`      = ?,
                Hausnummer    = ?,
                Telefonnummer = ?,
                PLZ           = ?,
                Ort           = ?
            WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
        ");

        $abfrage->execute([
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

    public function fahrerAusTeamEntfernen(int $mitarbeiter_id, string $teamname): string {
        $abfrage = $this->connect()->prepare("
            DELETE FROM Fahrer
            WHERE `Mitarbeiter-ID` = ? AND Teamname = ?
        ");
        $abfrage->execute([$mitarbeiter_id, $teamname]);

        if ($abfrage->rowCount() > 0) {
            return "Fahrer wurde erfolgreich gelöscht.";
        }

        return "Fahrer wurde nicht gefunden.";
    }
}

// Nicolas Biercher Ende
