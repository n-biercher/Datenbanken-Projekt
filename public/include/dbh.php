<?php 

    $dbServername = "DB-PHP-Projekte";
    $dbUsername = "gruppe5";
    $dbPassword = "uFImZLfaHtD8";
    $dbName = "gruppe5";

    $conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

    if (!$conn) {
    die("❌ Verbindung fehlgeschlagen: " . mysqli_connect_error());
}

    echo "✅ Verbindung erfolgreich!";

    