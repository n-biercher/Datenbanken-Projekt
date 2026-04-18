<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veranstalter einloggen</title>
</head>
<body>

<h1>Veranstalter einloggen</h1>
 <form>

 <fieldset>
        <legend>Login-Daten unten eintragen</legend>
         <form>
        <p>
            <label for="veranstalter_loginname">Loginname</label><br>
            <input id="veranstalter_loginname" name="veranstalter_loginname">
        </p>
        <p>
            <label for="veranstalter_kennwort">Kennwort</label><br>
            <input id="veranstalter_kennwort" name="veranstalter_kennwort" type="password">
        </p>

        <p><input type="submit" name="login" value="Anmelden">
        <input type="submit" name="registrieren" value="Neu Registrieren"></p>
        <p><a href="index.php">Zurück zur Startseite</a></p>
    </form>

 </fieldset>

</body>
</html>