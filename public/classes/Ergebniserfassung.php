<!-- Lena Strohmenger Beginn -->
<?php
require('include/dbh.php');

class Ergebniserfassung extends Dbh
{

    public function rennenHolen($veranstalter_loginname)
    {
        $sql = "SELECT *
            FROM Rennen r
            WHERE r.VeranstalterLoginName = ?
            AND NOT EXISTS (
                SELECT 1
                FROM Teilnahme t
                WHERE t.RennId = r.RennId
                AND t.Platzierung IS NOT NULL
                AND t.Zeit IS NOT NULL
            )
            ORDER BY r.Datum DESC";

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

    public function ergebnisseSchonErfasst($renn_id)
    {
        $sql = "SELECT COUNT(*)
            FROM Teilnahme
            WHERE RennId = ?
            AND Platzierung IS NOT NULL
            AND Zeit IS NOT NULL";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$renn_id]);

        return $stmt->fetchColumn() > 0;
    }
    public function ergebnisseSpeichern($renn_id, $ergebnisse)
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

            foreach ($ergebnisse as $mitarbeiter_id => $daten) {
                $stmt->execute([
                    $daten['platzierung'],
                    $daten['zeit'],
                    $renn_id,
                    $mitarbeiter_id,
                    $daten['teamname']
                ]);
            }

            $verbindung->commit();
            return true;

        } catch (PDOException $e) {
            $verbindung->rollBack();
            return false;
        }
    }

}
?>

<!-- Lena Strohmenger Ende -->