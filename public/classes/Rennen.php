
<?php
/* * Rennen Klasse
 * Lena Strohmenger Beginn
 * Verwaltet Rennen, die Anmeldung von Fahrern zu Rennen und das Kopieren von Teilnahmen von einem Rennen zu einem anderen Rennen
 * Der Trigger startnummer_vergeben ist ebenfalls von mir erstellt worden 
*/

require_once('include/dbh.php');
class Rennen extends Dbh
{
    //Neues Rennen anlegen
    public function rennenAnlegen($datum, $plz, $ort, $kilometer, $steigung, $hoehenmeter, $veranstalter_loginname)
    {
        try {
            $sql = "INSERT INTO Rennen (Datum, PLZ, Ort, Kilometer, Steigung, Hoehenmeter, VeranstalterLoginName) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$datum, $plz, $ort, $kilometer, $steigung, $hoehenmeter, $veranstalter_loginname]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
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
                AND t.Teamname = f.Teamname
                JOIN Team te ON f.Teamname = te.Teamname
                WHERE te.TeamchefLoginName = ?
                ORDER BY r.Datum DESC";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$teamchef_loginname]);
        return $stmt->fetchAll();
    }

    //holt alle Rennen ohne Teilnahmen aus der Datenbank
    public function rennenOhneTeilnahmenHolen($teamchef_loginname)
    {
        $sql = "SELECT r.*
            FROM Rennen r
            WHERE r.Datum >= CURDATE()
            AND NOT EXISTS (
                SELECT 1
                FROM Teilnahme t
                JOIN Fahrer f
                    ON t.MitarbeiterId = f.`Mitarbeiter-ID`
                    AND t.Teamname = f.Teamname
                JOIN Team te
                    ON f.Teamname = te.Teamname
                WHERE t.RennId = r.RennId
                AND te.TeamchefLoginName = ?
            )
            ORDER BY r.Datum ASC";

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
                AND t.Teamname = f.Teamname
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

    //Prüft ob ein Rennen zum Teamchef gehört
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

//Lena Strohmenger Ende
?>

