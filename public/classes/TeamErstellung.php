<!-- Nicolas Biercher Beginn -->

<?php

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(403);
    exit();
}

include_once('include/dbh.php');

class TeamRegistrierung extends Dbh {
    private function istKennwortSicher(string $kennwort): true|string {
        if (strlen($kennwort) < 8) {
            return "Das Kennwort muss mindestens 8 Zeichen lang sein";
        }
        if (!preg_match('/[A-Z]/', $kennwort)) {
            return "Das Kennwort muss mindestens einen Großbuchstaben enthalten";
        }
        if (!preg_match('/[a-z]/', $kennwort)) {
            return "Das Kennwort muss mindestens einen Kleinbuchstaben enthalten";
        }
        if (!preg_match('/[0-9]/', $kennwort)) {
            return "Das Kennwort muss mindestens eine Zahl enthalten";
        }
        if (!preg_match('/[\W_]/', $kennwort)) {
            return "Das Kennwort muss mindestens ein Sonderzeichen enthalten";
        }
        return true;
    }

    public function registrieren(
    string $teamname,
    string $vorname,
    string $nachname,
    string $loginname,
    string $kennwort,
    string $kennwort_bestaetigung): ?string {

        if ($kennwort !== $kennwort_bestaetigung) {
            return "Kennwörter stimmen nicht überein";
        }

        $pruefung = $this->istKennwortSicher($kennwort);
        if ($pruefung !== true) {
            return $pruefung;
        }

        $db = $this->connect();

        $stmt = $db->prepare("SELECT TeamchefLoginName FROM Teamchef WHERE TeamchefLoginName = ?");
        $stmt->execute([$loginname]);
        if ($stmt->fetch()) {
            return "Loginname existiert bereits";
        }

        $stmt = $db->prepare("SELECT Teamname FROM Team WHERE Teamname = ?");
        $stmt->execute([$teamname]);
        if ($stmt->fetch()) {
            return "Teamname existiert bereits";
        }

        $hash = password_hash($kennwort, PASSWORD_DEFAULT);

        try {
            $db->beginTransaction();

            $db->prepare("INSERT INTO Teamchef (TeamchefLoginName, Kennwort, Vorname, Nachname) VALUES (?, ?, ?, ?)")
               ->execute([$loginname, $hash, $vorname, $nachname]);

            $db->prepare("INSERT INTO Team (Teamname, TeamchefLoginName) VALUES (?, ?)")
               ->execute([$teamname, $loginname]);

            $db->commit();
            return null;

        } catch (PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }
}

?>

<!-- Nicolas Biercher Ende -->
