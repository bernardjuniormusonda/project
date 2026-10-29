<?php
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        flash('Your session expired. Please try again.', 'error');
        redirect('admin/messages.php');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    if ($id && $action === 'delete') {
        db()->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        flash('Message deleted.');
    } elseif ($id && $action === 'read') {
        db()->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    } elseif ($id && $action === 'unread') {
        db()->prepare('UPDATE messages SET is_read = 0 WHERE id = ?')->execute([$id]);
    } elseif ($action === 'read_all') {
        db()->exec('UPDATE messages SET is_read = 1');
        flash('All marked as read.');
    }
    redirect('admin/messages.php');
}

$rows = db()->query('SELECT * FROM messages ORDER BY id DESC LIMIT 500')->fetchAll();
$adminTitle = 'Messages';
require __DIR__ . '/_header.php';
?>
<div class="tabs">
    <span class="spacer"></span>
    <a class="btn btn-sm btn-ghost-dark" href="<?= e(url('admin/export.php?type=messages')) ?>">Download CSV</a>
    <form method="post" class="inline">
        <?= csrf_field() ?><input type="hidden" name="action" value="read_all">
        <button class="btn btn-sm btn-ghost-dark" type="submit">Mark all read</button>
    </form>
</div>

<?php if (!$rows): ?><p class="muted">No messages yet.</p><?php endif; ?>
<?php foreach ($rows as $r): ?>
    <details class="entry<?= $r['is_read'] ? '' : ' unread' ?>">
        <summary>
            <span class="who"><?= e($r['name']) ?></span>
            <span class="what"><?= e($r['subject'] ?: 'No subject') ?></span>
            <span class="when"><?= e(substr($r['created_at'], 0, 16)) ?></span>
            <?= $r['is_read'] ? '' : '<span class="badge">new</span>' ?>
        </summary>
        <div class="entry-body">
            <p><strong>Email:</strong> <a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a>
                <?php if ($r['phone'] !== ''): ?> &middot; <strong>Phone:</strong> <a href="tel:+<?= e(phone_digits($r['phone'])) ?>"><?= e($r['phone']) ?></a><?php endif; ?></p>
            <div class="quote"><?= nl2br(e($r['message'])) ?></div>
            <div class="btn-row">
                <form method="post" class="inline">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <button class="btn btn-sm btn-ghost-dark" name="action" value="<?= $r['is_read'] ? 'unread' : 'read' ?>"><?= $r['is_read'] ? 'Mark unread' : 'Mark read' ?></button>
                </form>
                <form method="post" class="inline" data-confirm="Delete this message permanently?">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <button class="btn btn-sm btn-danger" name="action" value="delete">Delete</button>
                </form>
            </div>
        </div>
    </details>
<?php endforeach; ?>
<?php require __DIR__ . '/_footer.php'; ?>
