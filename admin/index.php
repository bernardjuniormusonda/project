<?php
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pdo = db();
$count = fn(string $sql) => (int) $pdo->query($sql)->fetchColumn();
$kpis = [
    'New quote requests' => $count("SELECT COUNT(*) FROM quotes WHERE status = 'new'"),
    'Open quotes'        => $count("SELECT COUNT(*) FROM quotes WHERE status IN ('new','contacted','quoted')"),
    'Unread messages'    => $count('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
    'Live categories'    => $count("SELECT COUNT(*) FROM categories WHERE status = 'published'"),
];
$quotes = $pdo->query('SELECT * FROM quotes ORDER BY id DESC LIMIT 6')->fetchAll();
$msgs   = $pdo->query('SELECT * FROM messages ORDER BY id DESC LIMIT 6')->fetchAll();

$adminTitle = 'Dashboard';
require __DIR__ . '/_header.php';
?>
<p class="muted">Welcome back, <?= e($_SESSION['admin_name'] ?? 'admin') ?>.</p>
<div class="kpis">
    <?php foreach ($kpis as $label => $n): ?>
        <div class="kpi"><strong><?= $n ?></strong><span><?= e($label) ?></span></div>
    <?php endforeach; ?>
</div>

<div class="two-col">
    <section class="panel">
        <h2>Latest quote requests</h2>
        <?php if (!$quotes): ?><p class="muted">No quote requests yet.</p><?php endif; ?>
        <ul class="feed">
            <?php foreach ($quotes as $q): ?>
                <li><strong><?= e($q['name']) ?></strong><?= $q['organisation'] !== '' ? ' <span class="muted">(' . e($q['organisation']) . ')</span>' : '' ?>
                    <span class="pill pill-<?= e($q['status']) ?>"><?= e(ucfirst($q['status'])) ?></span>
                    <span class="muted"><?= e($q['ref']) ?> &middot; <?= e(substr($q['created_at'], 0, 16)) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <a class="link-arrow" href="<?= e(url('admin/quotes.php')) ?>">All quote requests <?= icon('arrow', 16) ?></a>
    </section>
    <section class="panel">
        <h2>Latest messages</h2>
        <?php if (!$msgs): ?><p class="muted">No messages yet.</p><?php endif; ?>
        <ul class="feed">
            <?php foreach ($msgs as $m): ?>
                <li><strong><?= e($m['name']) ?></strong> <?= $m['is_read'] ? '' : '<span class="badge">new</span>' ?>
                    <span class="muted"><?= e($m['subject'] ?: 'No subject') ?> &middot; <?= e(substr($m['created_at'], 0, 16)) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <a class="link-arrow" href="<?= e(url('admin/messages.php')) ?>">All messages <?= icon('arrow', 16) ?></a>
    </section>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
