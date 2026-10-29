<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'About us';
$metaDesc  = 'About ' . setting('site_name') . ': a trusted wholesale supplier for businesses, institutions and communities in Zambia.';
$why = lines(setting('why_us'));
$creds = lines(setting('credentials'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow">About us</p>
        <h1>About <?= e(setting('site_name')) ?></h1>
        <p class="lead"><?= e(setting('motto')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container two-up">
        <div class="panel accent-left reveal">
            <h2>Who we are</h2>
            <p><?= e(setting('about_who')) ?></p>
        </div>
        <div class="panel accent-left reveal">
            <h2>What we do</h2>
            <p><?= e(setting('about_what')) ?></p>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container split">
        <div class="reveal">
            <p class="eyebrow dark">Our mission</p>
            <h2>Why we exist</h2>
            <p class="mission"><?= e(setting('mission')) ?></p>
        </div>
        <div class="reveal">
            <h2>Why choose us?</h2>
            <ul class="checklist">
                <?php foreach ($why as $w): ?><li><?= e($w) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <p class="eyebrow dark">What sets us apart</p>
            <h2>Our unique proposition</h2>
        </div>
        <div class="two-up">
            <div class="help-card static reveal"><?= icon('globe', 34) ?><h3>Network leverage</h3><p><?= e(setting('proposition_network')) ?></p></div>
            <div class="help-card static reveal"><?= icon('award', 34) ?><h3>Our dedication</h3><p><?= e(setting('proposition_dedication')) ?></p></div>
        </div>
        <?php if (setting('institutions_note') !== ''): ?>
            <p class="center note-line"><?= icon('book', 20) ?> <?= e(setting('institutions_note')) ?></p>
        <?php endif; ?>
    </div>
</section>

<?php if ($creds): ?>
<section class="section section-tint">
    <div class="container center">
        <h2>Registered. Compliant. Trusted.</h2>
        <ul class="cred-list">
            <?php foreach ($creds as $cr): ?><li><?= icon('shield', 18) ?> <?= e($cr) ?></li><?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="container cta-inner">
        <div><h2>Let's work together</h2><p>Talk to us about your next order.</p></div>
        <a class="btn btn-accent" href="<?= e(url('quote.php')) ?>">Request a quote</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
