<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = '';
$cats = db()->query("SELECT * FROM categories WHERE status = 'published' ORDER BY sort_order, id")->fetchAll();
$featured = array_slice($cats, 0, 6);
$why = lines(setting('why_us'));
$creds = lines(setting('credentials'));
$clients = lines(setting('clients'));
$services = [
    ['code',    'Web development & design', 'Websites and digital design for businesses and institutions.'],
    ['box',     'Procurement & supply', 'We source and supply what your organisation needs, when you need it.'],
    ['truck',   'Bulk wholesale distribution', 'Consistent supply in volume, with nationwide coverage.'],
    ['print',   'Corporate branding solutions', 'Print, embroider and brand your uniforms, stationery and merchandise.'],
    ['globe',   'Import & local sourcing', 'Local and imported products from leading suppliers and brands.'],
    ['headset', 'Customer support & after-sales', 'Friendly help before, during and after every order.'],
];
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-copy reveal">
            <p class="eyebrow"><?= e(setting('hero_kicker')) ?></p>
            <h1><?= e(setting('hero_title')) ?></h1>
            <p class="lead"><?= e(setting('hero_text')) ?></p>
            <div class="btn-row">
                <a class="btn btn-accent" href="<?= e(url('quote.php')) ?>">Request a quote</a>
                <a class="btn btn-ghost" href="<?= e(url('products.php')) ?>">Browse products</a>
            </div>
            <ul class="hero-points">
                <?php foreach (array_slice($why, 0, 4) as $w): ?>
                    <li><?= icon('check', 16) ?> <?= e($w) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="mosaic">
                <img class="m1" src="<?= e(url('assets/img/products/digital-printing.jpg')) ?>"<?= img_srcset('assets/img/products/digital-printing.jpg', '(max-width: 700px) 70vw, 340px') ?> alt="">
                <img class="m2" src="<?= e(url('assets/img/products/promotional.jpg')) ?>"<?= img_srcset('assets/img/products/promotional.jpg', '(max-width: 700px) 70vw, 340px') ?> alt="">
                <img class="m3" src="<?= e(url('assets/img/products/workwear-ppe.jpg')) ?>"<?= img_srcset('assets/img/products/workwear-ppe.jpg', '(max-width: 700px) 70vw, 340px') ?> alt="">
                <div class="m-badge"><strong>Quality</strong><strong>Variety</strong><strong>Reliability</strong></div>
            </div>
        </div>
    </div>
</section>

<section class="trust-strip">
    <div class="container trust-grid">
        <div class="trust reveal"><?= icon('award', 30) ?><div><strong>Trusted supplier</strong><span>Serving businesses &amp; institutions</span></div></div>
        <div class="trust reveal"><?= icon('shield', 30) ?><div><strong>Quality assured</strong><span>Products you can rely on</span></div></div>
        <div class="trust reveal"><?= icon('truck', 30) ?><div><strong>On-time delivery</strong><span>Nationwide coverage</span></div></div>
        <div class="trust reveal"><?= icon('tag', 30) ?><div><strong>Competitive prices</strong><span>Wholesale value in bulk</span></div></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <p class="eyebrow dark">One partner, many solutions</p>
            <h2>What we supply</h2>
        </div>
        <div class="card-grid">
            <?php foreach ($featured as $c): ?>
                <?php require __DIR__ . '/includes/category-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <p class="center"><a class="btn btn-primary" href="<?= e(url('products.php')) ?>">See all <?= count($cats) ?> categories</a></p>
    </div>
</section>

<section class="section section-tint">
    <div class="container split">
        <div class="reveal">
            <p class="eyebrow dark">Who we are</p>
            <h2>Creativity with purpose</h2>
            <p class="mission"><?= e(setting('motto')) ?></p>
        </div>
        <div class="prose reveal">
            <p><?= e(setting('about_who')) ?></p>
            <p><?= e(setting('about_what')) ?></p>
            <p><?= e(setting('institutions_note')) ?></p>
            <p><a class="link-arrow" href="<?= e(url('about.php')) ?>">More about us <?= icon('arrow', 16) ?></a></p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <p class="eyebrow dark">How we help</p>
            <h2>Our services</h2>
        </div>
        <div class="service-grid">
            <?php foreach ($services as [$ic, $t, $d]): ?>
                <div class="service reveal"><span class="service-icon"><?= icon($ic, 26) ?></span><div><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($clients || $creds): ?>
<section class="section section-tint">
    <div class="container center">
        <?php if ($clients): ?>
            <p class="eyebrow dark">Working together. Building success.</p>
            <h2>Organisations we serve</h2>
            <ul class="pill-list">
                <?php foreach ($clients as $cl): ?><li><?= e($cl) ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($creds): ?>
            <ul class="cred-list">
                <?php foreach ($creds as $cr): ?><li><?= icon('shield', 18) ?> <?= e($cr) ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="container cta-inner">
        <div><h2>Need it in bulk? Let's talk.</h2><p>Tell us what you need and we will come back with a competitive quote.</p></div>
        <a class="btn btn-accent" href="<?= e(url('quote.php')) ?>">Request a quote</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
