<?php if (($flashMessage ?? null) !== null): ?>
    <p><?= escape((string) $flashMessage) ?></p>
<?php endif; ?>

<h1>Willkommen zum Radrennen</h1>
<p>Bitte wähle eine der folgenden Optionen:</p>

<ul>
    <li><a href="/auth?mode=register&role=teamchef">Team erstellen</a></li>
    <li><a href="/auth?mode=login&role=teamchef">Teamchef anmelden</a></li>
    <li><a href="/auth?mode=register&role=veranstalter">Veranstalter registrieren</a></li>
    <li><a href="/auth?mode=login&role=veranstalter">Veranstalter anmelden</a></li>
</ul>

<?php if (($isLoggedIn ?? false) === true): ?>
    <p><a href="/dashboard">Zum Dashboard</a></p>
<?php endif; ?>

<hr>
