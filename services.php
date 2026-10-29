<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Services';
$metaDesc  = 'Procurement, bulk wholesale distribution, corporate branding, web design, sourcing and after-sales support.';
$services = [
    ['box',     'Procurement & supply', 'We source and supply what your organisation needs, from a single item to a full order, so your team can focus on its work. Affordable procurement services are available for academic institutions.'],
    ['truck',   'Bulk wholesale distribution', 'Consistent supply in volume across our product categories, priced competitively, with nationwide coverage and reliable delivery.'],
    ['print',   'Corporate branding solutions', 'Business cards, ID cards, printed stationery, banners, name tags, embroidery, screen printing, DTG and promotional merchandise carrying your brand.'],
    ['globe',   'Import & local sourcing', 'We combine local suppliers with imports, partnering with leading suppliers and brands to bring you the best.'],
    ['code',    'Web development & design', 'Websites and digital design for businesses and institutions that want a professional presence online.'],
    ['headset', 'Customer support & after-sales service', 'Help before, during and after your order: questions, follow-ups and support when you need it.'],
];
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow">Our service areas</p>
        <h1>Services</h1>
        <p class="lead">Nationwide coverage. Reliable delivery.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="service-grid wide">
            <?php foreach ($services as [$ic, $t, $d]): ?>
                <div class="service reveal"><span class="service-icon"><?= icon($ic, 28) ?></span><div><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-inner">
        <div><h2>Which service do you need?</h2><p>Send us the details and we will respond with a quote.</p></div>
        <a class="btn btn-accent" href="<?= e(url('quote.php')) ?>">Request a quote</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
