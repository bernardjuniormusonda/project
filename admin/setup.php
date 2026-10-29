<?php
require __DIR__ . '/../includes/bootstrap.php';

// One-time setup: only available while no administrator exists.
if (admin_exists()) {
    redirect('admin/login.php');
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $pass  = (string) ($_POST['password'] ?? '');
    $pass2 = (string) ($_POST['password2'] ?? '');

    if (!csrf_ok()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    if (!preg_match('~^[A-Za-z0-9_.-]{3,40}$~', $username)) {
        $errors[] = 'Username must be 3 to 40 characters: letters, numbers, dot, dash or underscore.';
    }
    if (strlen($pass) < 10) {
        $errors[] = 'Password must be at least 10 characters.';
    }
    if ($pass !== $pass2) {
        $errors[] = 'The two passwords do not match.';
    }

    if (!$errors) {
        db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
            ->execute([$username, password_hash($pass, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        flash('Administrator account created. Please log in.');
        redirect('admin/login.php');
    }
}

$adminTitle = 'Create administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>First-time setup</title>
    <link rel="icon" type="image/png" href="<?= e(url('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="auth-page">
<main class="auth-card">
    <img class="auth-logo" src="<?= e(url('assets/img/logo.png')) ?>" alt="Printel Enterprise" width="190" height="63">
    <h1>First-time setup</h1>
    <p class="muted">Create the administrator account for your site. This page disappears once an account exists.</p>
    <?php if ($errors): ?><div class="alert alert-error" role="alert"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form class="form" method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="field"><label for="username">Username</label>
            <input id="username" name="username" required value="<?= e($username) ?>"></div>
        <div class="field"><label for="password">Password (10+ characters)</label>
            <input id="password" name="password" type="password" required minlength="10"></div>
        <div class="field"><label for="password2">Repeat password</label>
            <input id="password2" name="password2" type="password" required minlength="10"></div>
        <button class="btn btn-primary block" type="submit">Create account</button>
    </form>
</main>
</body>
</html>
