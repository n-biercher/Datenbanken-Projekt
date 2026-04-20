<!-- Lena Strohmenger Beginn -->
<?php 

class Dbh {
    private $host = "localhost";
    private $user = "gruppe5";
    private $password = "uFImZLfaHtD8";
    private $dbName = "gruppe5";
    protected function connect() {
        try {   
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName;
            $pdo = new PDO($dsn, $this->user, $this->password);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die(" Verbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
}

?>

<!-- Lena Strohmenger Ende -->




