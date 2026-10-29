<?php
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

const QUOTE_STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'quoted' => 'Quoted', 'won' => 'Won', 'closed' => 'Closed'];

$filter = (string) ($_GET['status'] ?? 'all');
if ($filter !== 'all' && !isset(QUOTE_STATUSES[$filter])) {
    $filter = 'all';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        flash('Your session expired. Please try again.', 'error');
        redirect('admin/quotes.php');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    if ($id && $action === 'save') {
        $status = (string) ($_POST['status'] ?? 'new');
        if (!isset(QUOTE_STATUSES[$status])) {
            $status = 'new';
        }
        $note = mb_substr(trim((string) ($_POST['admin_note'] ?? '')), 0, 3000);
        db()->prepare('UPDATE quotes SET status = ?, admin_note = ? WHERE id = ?')->execute([$status, $note, $id]);
        flash('Quote request updated.');
    } elseif ($id && $action === 'delete') {
        db()->prepare('DELETE FROM quotes WHERE id = ?')->execute([$id]);
        flash('Quote request deleted.');
    }
    redirect('admin/quotes.php' . ($filter !== 'all' ? '?status=' . $filter : ''));
}

$counts = ['all' => (int) db()->query('SELECT COUNT(*) FROM quotes')->fetchColumn()];
foreach (db()->query('SELECT status, COUNT(*) c FROM quotes GROUP BY status') as $r) {
    $counts[$r['status']] = (int) $r['c'];
}
if ($filter === 'all') {
    $rows = db()->query('SELECT * FROM quotes ORDER BY id DESC LIMIT 500')->fetchAll();
} else {
    $stmt = db()->prepare('SELECT * FROM quotes WHERE status = ? ORDER BY id DESC LIMIT 500');
    $stmt->execute([$filter]);
    $rows = $stmt->fetchAll();
}

$adminTitle = 'Quote requests';
require __DIR__ . '/_header.php';
?>
<div class="tabs">
    <a href="<?= e(url('admin/quotes.php')) ?>"<?= $filter === 'all' ? ' class="active"' : '' ?>>All (<?= $counts['all'] ?>)</a>
    <?php foreach (QUOTE_STATUSES as $k => $label): ?>
        <a href="<?= e(url('admin/quotes.php?status=' . $k)) ?>"<?= $filter === $k ? ' class="active"' : '' ?>><?= e($label) ?> (<?= $counts[$k] ?? 0 ?>)</a>
    <?php endforeach; ?>
    <span class="spacer"></span>
    <a class="btn btn-sm btn-ghost-dark" href="<?= e(url('admin/export.php?type=quotes')) ?>">Download CSV</a>
</div>

<?php if (!$rows): ?><p class="muted">Nothing here yet.</p><?php endif; ?>
<?php foreach ($rows as $r): ?>
    <details class="entry<?= $r['status'] === 'new' ? ' unread' : '' ?>">
        <summary>
            <span class="who"><?= e($r['name']) ?></span>
            <span class="what"><?= e($r['organisation'] !== '' ? $r['organisation'] : $r['ref']) ?></span>
            <span class="pill pill-<?= e($r['status']) ?>"><?= e(QUOTE_STATUSES[$r['status']] ?? $r['status']) ?></span>
            <span class="when"><?= e(substr($r['created_at'], 0, 16)) ?></span>
        </summary>
        <div class="entry-body">
            <p><strong>Ref:</strong> <?= e($r['ref']) ?> &middot; <strong>Email:</strong> <a href="mailto:<?= e($r['email']) ?>?subject=<?= rawurlencode('Your quote request ' . $r['ref']) ?>"><?= e($r['email']) ?></a>
                <?php if ($r['phone'] !== ''): ?> &middot; <strong>Phone:</strong> <a href="tel:+<?= e(phone_digits($r['phone'])) ?>"><?= e($r['phone']) ?></a><?php endif; ?>
                <?php if ($r['location'] !== ''): ?><br><strong>Deliver to:</strong> <?= e($r['location']) ?><?php endif; ?>
                <?php if ($r['needed_by'] !== ''): ?> &middot; <strong>Needed by:</strong> <?= e($r['needed_by']) ?><?php endif; ?></p>
            <?php if ($r['items'] !== ''): ?><p class="label">Items requested</p><div class="quote"><?= nl2br(e($r['items'])) ?></div><?php endif; ?>
            <?php if ($r['details'] !== ''): ?><p class="label">Details</p><div class="quote"><?= nl2br(e($r['details'])) ?></div><?php endif; ?>

            <form method="post" class="form inline-form">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <div class="row">
                    <div class="field"><label for="st<?= (int) $r['id'] ?>">Status</label>
                        <select id="st<?= (int) $r['id'] ?>" name="status">
                            <?php foreach (QUOTE_STATUSES as $k => $label): ?><option value="<?= e($k) ?>"<?= $r['status'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="field"><label for="nt<?= (int) $r['id'] ?>">Internal note <span class="opt">(not shown to customer)</span></label>
                        <input id="nt<?= (int) $r['id'] ?>" name="admin_note" maxlength="3000" value="<?= e($r['admin_note']) ?>"></div>
                </div>
                <div class="btn-row">
                    <button class="btn btn-sm btn-primary" name="action" value="save">Save</button>
                    <button class="btn btn-sm btn-danger" name="action" value="delete" data-confirm-btn="Delete this quote request permanently?">Delete</button>
                </div>
            </form>
        </div>
    </details>
<?php endforeach; ?>
<?php require __DIR__ . '/_footer.php'; ?>
