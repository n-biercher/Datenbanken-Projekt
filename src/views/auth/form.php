<p><a href="/">Zurück zur Startseite</a></p>
<h1><?= escape((string) ($titleText ?? ($mode === 'register' ? 'Registrierung' : 'Anmeldung'))) ?></h1>

<?php if (($flash ?? null) !== null): ?>
    <p><?= escape((string) $flash) ?></p>
<?php endif; ?>

<?php if (($error ?? null) !== null): ?>
    <p><?= escape((string) $error) ?></p>
<?php endif; ?>

<form method="post" action="/auth?mode=<?= escape((string) $mode) ?>&role=<?= escape((string) $role) ?>">
    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

    <?php if ($mode === 'register' && $role === 'teamchef'): ?>
        <p>
            <label for="teamname">Teamname</label><br>
            <input id="teamname" name="teamname" required maxlength="50" value="<?= escape((string) ($values['teamname'] ?? '')) ?>">
        </p>
        <p>
            <label for="vorname">Vorname</label><br>
            <input id="vorname" name="vorname" required maxlength="45" value="<?= escape((string) ($values['vorname'] ?? '')) ?>">
        </p>
        <p>
            <label for="nachname">Nachname</label><br>
            <input id="nachname" name="nachname" required maxlength="45" value="<?= escape((string) ($values['nachname'] ?? '')) ?>">
        </p>
    <?php endif; ?>

    <p>
        <label for="loginname"><?= $role === 'teamchef' ? 'Teamchef-Loginname' : 'Veranstalter-Loginname' ?></label><br>
        <input id="loginname" name="loginname" required maxlength="50" value="<?= escape((string) ($values['loginname'] ?? '')) ?>">
    </p>

    <p>
        <label for="kennwort">Kennwort</label><br>
        <input id="kennwort" name="kennwort" type="password" required minlength="8">
    </p>

    <?php if ($mode === 'register'): ?>
        <p>
            <label for="kennwort_bestaetigung">Kennwort bestätigen</label><br>
            <input id="kennwort_bestaetigung" name="kennwort_bestaetigung" type="password" required minlength="8">
        </p>
    <?php endif; ?>

    <p><input type="submit" value="<?= $mode === 'register' ? 'Registrieren' : 'Anmelden' ?>"></p>
</form>

<hr>
