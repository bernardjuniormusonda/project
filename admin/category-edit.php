<?php
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pdo = db();
$id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$c = [
    'title' => '', 'slug' => '', 'tagline' => '', 'summary' => '', 'body' => '', 'items' => '',
    'icon' => 'box', 'image' => '', 'status' => 'published',
];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        flash('Category not found.', 'error');
        redirect('admin/categories.php');
    }
    $c = $existing;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['title', 'slug', 'tagline', 'summary', 'body', 'items', 'icon', 'status'] as $f) {
        $c[$f] = trim((string) ($_POST[$f] ?? ''));
    }

    if (!csrf_ok()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    if ($c['title'] === '' || mb_strlen($c['title']) > 160) {
        $errors[] = 'Title is required (max 160 characters).';
    }
    if (mb_strlen($c['tagline']) > 120) {
        $errors[] = 'Tagline must be 120 characters or fewer.';
    }
    if (mb_strlen($c['summary']) > 300) {
        $errors[] = 'Summary must be 300 characters or fewer.';
    }
    if (mb_strlen($c['items']) > 5000 || mb_strlen($c['body']) > 8000) {
        $errors[] = 'The description or item list is too long.';
    }
    if (!array_key_exists($c['icon'], icon_choices())) {
        $c['icon'] = 'box';
    }
    if (!in_array($c['status'], ['published', 'draft'], true)) {
        $c['status'] = 'draft';
    }
    $c['slug'] = slugify($c['slug'] !== '' ? $c['slug'] : $c['title']);

    $chk = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?');
    $chk->execute([$c['slug'], $id]);
    if ((int) $chk->fetchColumn() > 0) {
        $errors[] = 'Another category already uses that URL name. Please change it.';
    }

    // Optional image upload.
    $newImage = null;
    if (!$errors && !empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['image'];
        if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > cfg('max_upload', 3145728)) {
            $errors[] = 'Image upload failed or the file is too large (max ' . round(cfg('max_upload', 3145728) / 1048576) . ' MB).';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
            if (!$ext || !@getimagesize($f['tmp_name'])) {
                $errors[] = 'Image must be a JPG, PNG or WebP file.';
            } else {
                $dir = BASE_PATH . '/uploads/products';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $name = bin2hex(random_bytes(10)) . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
                    $newImage = 'uploads/products/' . $name;
                } else {
                    $errors[] = 'Could not save the uploaded image.';
                }
            }
        }
    }
    $removeImage = !empty($_POST['remove_image']);

    if (!$errors) {
        $oldImage = $c['image'];
        if ($newImage) {
            $c['image'] = $newImage;
        } elseif ($removeImage) {
            $c['image'] = '';
        }
        if ($c['image'] !== $oldImage && str_starts_with($oldImage, 'uploads/products/')) {
            @unlink(BASE_PATH . '/' . $oldImage);
        }

        if ($id) {
            $pdo->prepare('UPDATE categories SET title=?, slug=?, tagline=?, summary=?, body=?, items=?, icon=?, image=?, status=? WHERE id=?')
                ->execute([$c['title'], $c['slug'], $c['tagline'], $c['summary'], $c['body'], $c['items'], $c['icon'], $c['image'], $c['status'], $id]);
            flash('Category updated.');
        } else {
            $max = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM categories')->fetchColumn();
            $pdo->prepare('INSERT INTO categories (title, slug, tagline, summary, body, items, icon, image, status, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$c['title'], $c['slug'], $c['tagline'], $c['summary'], $c['body'], $c['items'], $c['icon'], $c['image'], $c['status'], $max + 1]);
            flash('Category created.');
        }
        redirect('admin/categories.php');
    }
}

$adminTitle = $id ? 'Edit category' : 'Add category';
require __DIR__ . '/_header.php';
?>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="form panel" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $id ?>">
    <div class="row">
        <div class="field"><label for="title">Title</label><input id="title" name="title" required maxlength="160" value="<?= e($c['title']) ?>"></div>
        <div class="field"><label for="slug">URL name <span class="opt">(auto if blank)</span></label><input id="slug" name="slug" maxlength="80" value="<?= e($c['slug']) ?>"></div>
    </div>
    <div class="field"><label for="tagline">Tagline <span class="opt">(short slogan, optional)</span></label><input id="tagline" name="tagline" maxlength="120" value="<?= e($c['tagline']) ?>"></div>
    <div class="field"><label for="summary">Short summary <span class="opt">(shown on cards, max 300)</span></label>
        <textarea id="summary" name="summary" rows="2" maxlength="300"><?= e($c['summary']) ?></textarea></div>
    <div class="field"><label for="items">What you supply <span class="opt">(one item per line; customers can add each to their quote list)</span></label>
        <textarea id="items" name="items" rows="8"><?= e($c['items']) ?></textarea></div>
    <div class="field"><label for="body">Description <span class="opt">(separate paragraphs with a blank line)</span></label>
        <textarea id="body" name="body" rows="6"><?= e($c['body']) ?></textarea></div>
    <div class="row">
        <div class="field"><label for="icon">Icon</label>
            <select id="icon" name="icon">
                <?php foreach (icon_choices() as $k => $label): ?><option value="<?= e($k) ?>"<?= $c['icon'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select></div>
        <div class="field"><label for="status">Status</label>
            <select id="status" name="status">
                <option value="published"<?= $c['status'] === 'published' ? ' selected' : '' ?>>Published</option>
                <option value="draft"<?= $c['status'] === 'draft' ? ' selected' : '' ?>>Draft (hidden)</option>
            </select></div>
    </div>
    <div class="field"><label for="image">Image <span class="opt">(JPG, PNG or WebP, up to <?= round(cfg('max_upload', 3145728) / 1048576) ?> MB)</span></label>
        <?php if ($c['image'] !== ''): ?>
            <div class="thumb"><img src="<?= e(url($c['image'])) ?>" alt="Current image"><label class="check"><input type="checkbox" name="remove_image" value="1"> Remove current image</label></div>
        <?php endif; ?>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"></div>
    <div class="btn-row">
        <button class="btn btn-primary" type="submit">Save category</button>
        <a class="btn btn-ghost-dark" href="<?= e(url('admin/categories.php')) ?>">Cancel</a>
    </div>
</form>
<?php require __DIR__ . '/_footer.php'; ?>
