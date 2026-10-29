<?php
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        flash('Your session expired. Please try again.', 'error');
        redirect('admin/categories.php');
    }
    $id     = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    $pdo    = db();

    if ($action === 'delete' && $id) {
        $row = $pdo->prepare('SELECT image FROM categories WHERE id = ?');
        $row->execute([$id]);
        $img = (string) $row->fetchColumn();
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        // Only remove files that were uploaded through the admin, never the bundled images.
        if ($img !== '' && str_starts_with($img, 'uploads/products/')) {
            @unlink(BASE_PATH . '/' . $img);
        }
        flash('Category deleted.');
    } elseif ($action === 'toggle' && $id) {
        $pdo->prepare("UPDATE categories SET status = CASE status WHEN 'published' THEN 'draft' ELSE 'published' END WHERE id = ?")->execute([$id]);
        flash('Status updated.');
    } elseif (($action === 'up' || $action === 'down') && $id) {
        $rows = array_map('intval', $pdo->query('SELECT id FROM categories ORDER BY sort_order, id')->fetchAll(PDO::FETCH_COLUMN));
        $pos = array_search($id, $rows, true);
        if ($pos !== false) {
            $swap = $action === 'up' ? $pos - 1 : $pos + 1;
            if (isset($rows[$swap])) {
                [$rows[$pos], $rows[$swap]] = [$rows[$swap], $rows[$pos]];
            }
            $upd = $pdo->prepare('UPDATE categories SET sort_order = ? WHERE id = ?');
            foreach ($rows as $i => $rid) {
                $upd->execute([$i + 1, $rid]);
            }
        }
    }
    redirect('admin/categories.php');
}

$rows = db()->query('SELECT * FROM categories ORDER BY sort_order, id')->fetchAll();
$adminTitle = 'Product categories';
require __DIR__ . '/_header.php';
?>
<p><a class="btn btn-primary" href="<?= e(url('admin/category-edit.php')) ?>">+ Add category</a></p>
<div class="table-wrap">
<table class="table">
    <thead><tr><th>Category</th><th>Items</th><th>Status</th><th>Order</th><th class="right">Actions</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="5" class="muted">No categories yet.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><strong><?= e($r['title']) ?></strong><br><small class="muted">/product.php?slug=<?= e($r['slug']) ?></small></td>
            <td><?= count(lines($r['items'])) ?></td>
            <td><span class="pill pill-<?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
            <td>
                <form method="post" class="inline">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <button class="icon-btn" name="action" value="up" title="Move up" aria-label="Move up">&uarr;</button>
                    <button class="icon-btn" name="action" value="down" title="Move down" aria-label="Move down">&darr;</button>
                </form>
            </td>
            <td class="right actions">
                <a class="btn btn-sm btn-ghost-dark" href="<?= e(url('admin/category-edit.php?id=' . $r['id'])) ?>">Edit</a>
                <form method="post" class="inline">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <button class="btn btn-sm btn-ghost-dark" name="action" value="toggle"><?= $r['status'] === 'published' ? 'Unpublish' : 'Publish' ?></button>
                </form>
                <form method="post" class="inline" data-confirm="Delete this category permanently?">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <button class="btn btn-sm btn-danger" name="action" value="delete">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
