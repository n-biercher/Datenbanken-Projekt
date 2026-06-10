<?php
/**
 * Authentifizierung eines Teamchefs
 * Nicolas Biercher
 */

// Direktaufruf über den Browser verhindern
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(403);
    exit();
}

include_once ('include/dbh.php');

class TeamchefLogin extends Dbh {
    public function login(string $loginname, string $kennwort): ?string {
        $anmelde_abfrage = $this->connect()->prepare(
            "SELECT TeamchefLoginName, Kennwort, Vorname, Nachname FROM Teamchef WHERE TeamchefLoginName = ?"
        );
        $anmelde_abfrage->execute([$loginname]);
        $user = $anmelde_abfrage->fetch();

        if (!$user) {
            return "Loginname oder Kennwort ist falsch.";
        }

        if (password_verify($kennwort, $user['Kennwort'])) {
            $_SESSION['teamchef_loginname'] = $user['TeamchefLoginName'];
            $_SESSION['vorname']             = $user['Vorname'];
            $_SESSION['nachname']            = $user['Nachname'];

            header("Location: index.php");
            exit();
        }

        return "Loginname oder Kennwort ist falsch.";
    }
}

// Nicolas Biercher Ende
