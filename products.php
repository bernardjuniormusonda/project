<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Products';
$metaDesc  = 'Browse ' . setting('site_name') . ' product categories: printing, IT equipment, office supplies, workwear, cleaning and more.';
$cats = db()->query("SELECT * FROM categories WHERE status = 'published' ORDER BY sort_order, id")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow">One partner, many solutions</p>
        <h1>Our products</h1>
        <p class="lead">Wide range of products. Competitive prices. Reliable supply. Quality guaranteed.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="search-row">
            <label class="sr-only" for="product-search">Search products</label>
            <input id="product-search" type="search" placeholder="Search products, e.g. toner, laptop, overalls..." autocomplete="off">
            <span class="muted small" id="search-count"></span>
        </div>

        <?php if (!$cats): ?>
            <p class="center muted">Products will be listed here soon.</p>
        <?php else: ?>
            <div class="card-grid" id="product-grid">
                <?php foreach ($cats as $c): ?>
                    <?php require __DIR__ . '/includes/category-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <p class="center muted" id="no-results" hidden>No matching products. <a href="<?= e(url('quote.php')) ?>">Ask us</a>: we can source almost anything.</p>
        <?php endif; ?>

        <div class="panel more-panel">
            <h3>Can't see what you need?</h3>
            <p>Our range goes well beyond this list. Tell us what you are looking for and we will source it.</p>
            <a class="btn btn-primary" href="<?= e(url('quote.php')) ?>">Request a quote</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
