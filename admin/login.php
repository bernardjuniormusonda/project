<?php
require __DIR__ . '/../includes/bootstrap.php';

if (!admin_exists()) {
    redirect('admin/setup.php');
}
if (is_admin()) {
    redirect('admin/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $pass     = (string) ($_POST['password'] ?? '');

    if (!csrf_ok()) {
        $error = 'Your session expired. Please try again.';
    } elseif (!rate_ok('login', 8, 900)) {
        $error = 'Too many login attempts. Please wait 15 minutes and try again.';
    } else {
        $stmt = db()->prepare('SELECT * FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        // Verify against a dummy hash when the user is unknown to keep timing similar.
        $hash = $admin['password_hash'] ?? '$2y$10$abcdefghijklmnopqrstuuG8fQ0n5s8m3G2xq3k1o1oXlH1v6w5Kq';
        if (password_verify($pass, $hash) && $admin) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($pass, PASSWORD_DEFAULT), $admin['id']]);
            }
            redirect('admin/index.php');
        }
        $error = 'Incorrect username or password.';
    }
}
$flash = flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login</title>
    <link rel="icon" type="image/png" href="<?= e(url('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="auth-page">
<main class="auth-card">
    <img class="auth-logo" src="<?= e(url('assets/img/logo.png')) ?>" alt="Printel Enterprise" width="190" height="63">
    <h1>Admin login</h1>
    <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form class="form" method="post">
        <?= csrf_field() ?>
        <div class="field"><label for="username">Username</label>
            <input id="username" name="username" required autofocus autocomplete="username"></div>
        <div class="field"><label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"></div>
        <button class="btn btn-primary block" type="submit">Log in</button>
    </form>
    <p class="center small"><a href="<?= e(url('index.php')) ?>">&larr; Back to website</a></p>
</main>
</body>
</html>
