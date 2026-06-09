<!-- Lena Strohmenger Beginn -->
<?php

class Dbh {
    private $host = "localhost";
    private $user = "gruppe5";
    private $password = "uFImZLfaHtD8";
    private $dbName = "gruppe5";
    private ?PDO $pdo = null;

    protected function connect(): PDO {
        if ($this->pdo === null) {
            try {
                $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName . ';charset=utf8mb4';
                $this->pdo = new PDO($dsn, $this->user, $this->password);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die(" Verbindung fehlgeschlagen: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }
}

?>

<!-- Lena Strohmenger Ende -->




