<?php
declare(strict_types=1);

final class AuthService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function registerTeamchef(array $data): void
    {
        $teamname = $this->requireText($data['teamname'] ?? '', 'Bitte einen Teamnamen angeben.', 50);
        $loginname = $this->requireText($data['loginname'] ?? '', 'Bitte einen Teamchef-Loginnamen angeben.', 50);
        $vorname = $this->requireText($data['vorname'] ?? '', 'Bitte einen Vornamen angeben.', 45);
        $nachname = $this->requireText($data['nachname'] ?? '', 'Bitte einen Nachnamen angeben.', 45);
        $hash = $this->validateAndHashPassword(
            (string) ($data['kennwort'] ?? ''),
            (string) ($data['kennwort_bestaetigung'] ?? '')
        );

        if ($this->teamExists($teamname)) {
            throw new RuntimeException('Der Teamname existiert bereits.');
        }

        if ($this->teamchefExists($loginname)) {
            throw new RuntimeException('Der Teamchef-Loginname ist bereits vergeben.');
        }

        $this->pdo->beginTransaction();

        try {
            $insertTeamchef = $this->pdo->prepare(
                'INSERT INTO Teamchef (TeamchefLoginName, Kennwort, Vorname, Nachname)
                 VALUES (:loginname, :kennwort, :vorname, :nachname)'
            );
            $insertTeamchef->execute([
                'loginname' => $loginname,
                'kennwort' => $hash,
                'vorname' => $vorname,
                'nachname' => $nachname,
            ]);

            $insertTeam = $this->pdo->prepare(
                'INSERT INTO Team (Teamname, TeamchefLoginName)
                 VALUES (:teamname, :loginname)'
            );
            $insertTeam->execute([
                'teamname' => $teamname,
                'loginname' => $loginname,
            ]);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw new RuntimeException('Die Registrierung des Teamchefs ist fehlgeschlagen.', 0, $exception);
        }
    }

    public function registerVeranstalter(array $data): void
    {
        $loginname = $this->requireText($data['loginname'] ?? '', 'Bitte einen Veranstalter-Loginnamen angeben.', 50);
        $hash = $this->validateAndHashPassword(
            (string) ($data['kennwort'] ?? ''),
            (string) ($data['kennwort_bestaetigung'] ?? '')
        );

        if ($this->veranstalterExists($loginname)) {
            throw new RuntimeException('Der Veranstalter-Loginname ist bereits vergeben.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO Veranstalter (VeranstalterLoginName, Kennwort)
             VALUES (:loginname, :kennwort)'
        );
        $statement->execute([
            'loginname' => $loginname,
            'kennwort' => $hash,
        ]);
    }

    public function login(string $role, string $loginname, string $password): array
    {
        $role = $role === 'veranstalter' ? 'veranstalter' : 'teamchef';
        $loginname = $this->requireText($loginname, 'Bitte einen Loginnamen angeben.', 50);

        if ($password === '') {
            throw new RuntimeException('Bitte ein Kennwort angeben.');
        }

        if ($role === 'teamchef') {
            $statement = $this->pdo->prepare(
                'SELECT tc.TeamchefLoginName AS loginname, tc.Kennwort, t.Teamname
                 FROM Teamchef tc
                 LEFT JOIN Team t ON t.TeamchefLoginName = tc.TeamchefLoginName
                 WHERE tc.TeamchefLoginName = :loginname'
            );
        } else {
            $statement = $this->pdo->prepare(
                'SELECT VeranstalterLoginName AS loginname, Kennwort
                 FROM Veranstalter
                 WHERE VeranstalterLoginName = :loginname'
            );
        }

        $statement->execute(['loginname' => $loginname]);
        $user = $statement->fetch();

        if ($user === false || !password_verify($password, (string) ($user['Kennwort'] ?? ''))) {
            throw new RuntimeException('Loginname oder Kennwort ist ungültig.');
        }

        $result = [
            'role' => $role,
            'loginname' => (string) $user['loginname'],
        ];

        if ($role === 'teamchef' && isset($user['Teamname'])) {
            $result['teamname'] = (string) $user['Teamname'];
        }

        return $result;
    }

    private function validateAndHashPassword(string $password, string $confirmation): string
    {
        if ($password === '') {
            throw new RuntimeException('Bitte ein Kennwort angeben.');
        }

        if (mb_strlen($password) < 8) {
            throw new RuntimeException('Das Kennwort muss mindestens 8 Zeichen lang sein.');
        }

        if ($password !== $confirmation) {
            throw new RuntimeException('Die Kennwort-Bestätigung stimmt nicht überein.');
        }

        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function requireText(string $value, string $message, int $maxLength): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new RuntimeException($message);
        }

        if (mb_strlen($value) > $maxLength) {
            throw new RuntimeException('Ein eingegebener Wert ist zu lang.');
        }

        return $value;
    }

    private function teamExists(string $teamname): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM Team WHERE Teamname = :teamname');
        $statement->execute(['teamname' => $teamname]);

        return $statement->fetchColumn() !== false;
    }

    private function teamchefExists(string $loginname): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM Teamchef WHERE TeamchefLoginName = :loginname');
        $statement->execute(['loginname' => $loginname]);

        return $statement->fetchColumn() !== false;
    }

    private function veranstalterExists(string $loginname): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM Veranstalter WHERE VeranstalterLoginName = :loginname');
        $statement->execute(['loginname' => $loginname]);

        return $statement->fetchColumn() !== false;
    }
}
