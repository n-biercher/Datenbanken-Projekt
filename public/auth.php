<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Authentifizierung | Stadtradeln</title>
</head>
<body>
    <p><a href="/index.php">Zurück zur Startseite</a></p>
    <h1>Authentifizierung</h1>
    <p>Diese Seite ist aktuell nur ein statisches HTML-Mockup.</p>

    <p>
        <a href="/auth.php?mode=register&role=teamchef">Team erstellen</a>
        |
        <a href="/auth.php?mode=login&role=teamchef">Teamchef anmelden</a>
        |
        <a href="/auth.php?mode=register&role=veranstalter">Veranstalter registrieren</a>
        |
        <a href="/auth.php?mode=login&role=veranstalter">Veranstalter anmelden</a>
    </p>

    <h2>Team erstellen</h2>
    <form>
        <p>
            <label for="teamname">Teamname</label><br>
            <input id="teamname" name="teamname">
        </p>
        <p>
            <label for="vorname">Vorname</label><br>
            <input id="vorname" name="vorname">
        </p>
        <p>
            <label for="nachname">Nachname</label><br>
            <input id="nachname" name="nachname">
        </p>
        <p>
            <label for="loginname">Loginname</label><br>
            <input id="loginname" name="loginname">
        </p>
        <p>
            <label for="kennwort">Kennwort</label><br>
            <input id="kennwort" name="kennwort" type="password">
        </p>
        <p>
            <label for="kennwort_bestaetigung">Kennwort bestätigen</label><br>
            <input id="kennwort_bestaetigung" name="kennwort_bestaetigung" type="password">
        </p>
        <p><input type="submit" value="Speichern"></p>
    </form>

    <h2>Veranstalter registrieren</h2>
    <form>
        <p>
            <label for="veranstalter_loginname">Loginname</label><br>
            <input id="veranstalter_loginname" name="veranstalter_loginname">
        </p>
        <p>
            <label for="veranstalter_kennwort">Kennwort</label><br>
            <input id="veranstalter_kennwort" name="veranstalter_kennwort" type="password">
        </p>
        <p><input type="submit" value="Speichern"></p>
    </form>

    <h2>Anmeldung</h2>
    <form>
        <p>
            <label for="anmeldung_loginname">Loginname</label><br>
            <input id="anmeldung_loginname" name="anmeldung_loginname">
        </p>
        <p>
            <label for="anmeldung_kennwort">Kennwort</label><br>
            <input id="anmeldung_kennwort" name="anmeldung_kennwort" type="password">
        </p>
        <p><input type="submit" value="Anmelden"></p>
    </form>

    <hr>
</body>
</html>
