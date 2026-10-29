<?php
/** @var string $adminTitle */
$cur = basename($_SERVER['SCRIPT_NAME']);
$newQuotes = (int) db()->query("SELECT COUNT(*) FROM quotes WHERE status = 'new'")->fetchColumn();
$unreadMsg = (int) db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$flash = flash();
$items = [
    'index.php'      => ['Dashboard', 0],
    'quotes.php'     => ['Quote requests', $newQuotes],
    'messages.php'   => ['Messages', $unreadMsg],
    'categories.php' => ['Product categories', 0],
    'settings.php'   => ['Settings', 0],
];
if ($cur === 'category-edit.php') {
    $cur = 'categories.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle ?? 'Admin') ?> | Admin</title>
    <link rel="icon" type="image/png" href="<?= e(url('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script src="<?= e(url('assets/js/main.js')) ?>" defer></script>
</head>
<body class="admin">
<div class="admin-shell">
    <aside class="admin-side">
        <p class="admin-brand"><img src="<?= e(url('assets/img/logo-white.png')) ?>" alt="<?= e(setting('site_name')) ?>" width="150" height="44"><small>Admin panel</small></p>
        <nav aria-label="Admin">
            <?php foreach ($items as $file => [$label, $badge]): ?>
                <a href="<?= e(url('admin/' . $file)) ?>"<?= $cur === $file ? ' class="active"' : '' ?>>
                    <?= e($label) ?><?php if ($badge): ?><span class="badge"><?= $badge ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">View website</a>
        </nav>
        <form method="post" action="<?= e(url('admin/logout.php')) ?>" class="logout-form">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-sm" type="submit">Log out</button>
        </form>
    </aside>
    <main class="admin-main" id="main">
        <h1 class="admin-title"><?= e($adminTitle ?? 'Admin') ?></h1>
        <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div><?php endif; ?>
