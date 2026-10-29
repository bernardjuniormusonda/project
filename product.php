<?php
require __DIR__ . '/includes/bootstrap.php';

$slug = (string) ($_GET['slug'] ?? '');
$stmt = db()->prepare("SELECT * FROM categories WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$c = $stmt->fetch();

if (!$c) {
    http_response_code(404);
    $pageTitle = 'Category not found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container center"><h1>Category not found</h1>'
       . '<p>The page you are looking for does not exist or is no longer available.</p>'
       . '<p><a class="btn btn-primary" href="' . e(url('products.php')) . '">Back to products</a></p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $c['title'];
$metaDesc  = $c['summary'];
$others = db()->prepare("SELECT slug, title FROM categories WHERE status = 'published' AND id != ? ORDER BY sort_order, id");
$others->execute([$c['id']]);
$others = $others->fetchAll();
$items = lines($c['items']);
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow"><a class="crumb" href="<?= e(url('products.php')) ?>">Products</a> / <?= e($c['title']) ?></p>
        <h1><?= e($c['title']) ?></h1>
        <p class="lead"><?= e($c['summary']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container detail-grid">
        <article>
            <?php if ($c['image'] !== ''): ?>
                <img class="detail-image" src="<?= e(url($c['image'])) ?>"<?= img_srcset($c['image'], '(max-width: 900px) 100vw, 800px') ?> alt="<?= e($c['title']) ?>">
            <?php endif; ?>
            <?php if ($c['tagline'] !== ''): ?><p class="tagline"><?= e($c['tagline']) ?></p><?php endif; ?>
            <div class="prose"><?= paragraphs($c['body']) ?></div>

            <?php if ($items): ?>
                <h2 class="items-title">We supply</h2>
                <p class="muted small">Tap <strong>Add</strong> to build a quote list, then send it to us in one go.</p>
                <ul class="items">
                    <?php foreach ($items as $it): ?>
                        <li>
                            <span><?= e($it) ?></span>
                            <button type="button" class="add-btn" data-item="<?= e($it) ?>" data-cat="<?= e($c['title']) ?>" aria-label="Add <?= e($it) ?> to quote list"><?= icon('plus', 16) ?> <span>Add</span></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>

        <aside class="sidebar">
            <div class="panel">
                <h3>Get a quote</h3>
                <p class="small muted">Ask for prices on anything in this category. We reply with a competitive quote.</p>
                <a class="btn btn-accent block" href="<?= e(url('quote.php')) ?>">Request a quote <span class="basket-count" data-basket-count hidden>0</span></a>
                <a class="btn btn-ghost-dark block" href="tel:+<?= e(phone_digits(setting('phone'))) ?>">Call <?= e(setting('phone')) ?></a>
            </div>
            <?php if ($others): ?>
                <div class="panel">
                    <h3>More categories</h3>
                    <ul class="side-links">
                        <?php foreach ($others as $o): ?>
                            <li><a href="<?= e(url('product.php?slug=' . $o['slug'])) ?>"><?= e($o['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
