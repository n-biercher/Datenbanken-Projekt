<?php 

include_once('include/dbh.php');
class Veranstalter extends Dbh
{

    // Passwortstärke überprüfen
    private function passwortPruefen($passwort)
    {
        if (strlen($passwort) < 8) {
            return "Mindestens 8 Zeichen";
        }
        if (!preg_match('/[A-Z]/', $passwort)) {
            return "Mindestens 1 Großbuchstabe";
        }
        if (!preg_match('/[a-z]/', $passwort)) {
            return "Mindestens 1 Kleinbuchstabe";
        }
        if (!preg_match('/[0-9]/', $passwort)) {
            return "Mindestens 1 Zahl";
        }
        if (!preg_match('/[\W_]/', $passwort)) {
            return "Mindestens 1 Sonderzeichen";
        }
        return true;
    }

    // Veranstalter registrieren
    public function veranstalterRegistrieren($loginname, $passwort, $passwort2)
    {
        if ($passwort !== $passwort2) {
            return "Kennwörter stimmen nicht überein";
        }

        $check = $this->passwortPruefen($passwort);
        if ($check !== true) {
            return $check;
        }

        // Prüfen ob Loginname existiert
        $sql = "SELECT VeranstalterLoginName FROM Veranstalter WHERE VeranstalterLoginName = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$loginname]);

        if ($stmt->fetch()) {
            return "Loginname bereits vergeben!";
        }

        // Passwort hashen
        $hash = password_hash($passwort, PASSWORD_DEFAULT);

        // Einfügen
        $sql = "INSERT INTO Veranstalter (VeranstalterLoginName, Kennwort) VALUES (?, ?)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$loginname, $hash]);

        return true;
    }

    //Veranstalter anmelden
    public function veranstalterAnmelden($loginname, $passwort)
    {
        $sql = "SELECT * FROM Veranstalter WHERE VeranstalterLoginName = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$loginname]);
        $user = $stmt->fetch();

        if (!$user) {
            return "Bitte registriere dich zuerst!";
        }

        // Passwort prüfen 
        if (password_verify($passwort, $user['Kennwort'])) {
            return true;
        } else {
            return "Falsches Kennwort!";
        }
    }
}
