<?php if (($flash ?? null) !== null): ?>
    <p><?= escape((string) $flash) ?></p>
<?php endif; ?>

<h1>Dashboard</h1>
<p>Die Anmeldung funktioniert. Von hier aus können als Nächstes die geschützten Bereiche aufgebaut werden.</p>

<p>Rolle: <?= escape((string) ($user['role'] ?? '')) ?></p>
<p>Loginname: <?= escape((string) ($user['loginname'] ?? '')) ?></p>

<?php if (($user['role'] ?? '') === 'teamchef' && isset($user['teamname'])): ?>
    <p>Team: <?= escape((string) $user['teamname']) ?></p>
<?php endif; ?>

<form method="post" action="/logout">
    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
    <input type="submit" value="Abmelden">
</form>

<hr>
