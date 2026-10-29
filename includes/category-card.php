<?php /** @var array $c a category row */ ?>
<article class="card reveal" data-title="<?= e(strtolower($c['title'] . ' ' . $c['summary'] . ' ' . $c['items'])) ?>">
    <a class="card-media<?= $c['image'] === '' ? ' card-media-icon' : '' ?>" href="<?= e(url('product.php?slug=' . $c['slug'])) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($c['image'] !== ''): ?>
            <img src="<?= e(url($c['image'])) ?>"<?= img_srcset($c['image']) ?> alt="" loading="lazy" decoding="async">
        <?php else: ?>
            <?= icon($c['icon'], 48) ?>
        <?php endif; ?>
    </a>
    <div class="card-body">
        <span class="card-icon"><?= icon($c['icon'], 20) ?></span>
        <h3><a href="<?= e(url('product.php?slug=' . $c['slug'])) ?>"><?= e($c['title']) ?></a></h3>
        <p><?= e($c['summary']) ?></p>
        <a class="link-arrow" href="<?= e(url('product.php?slug=' . $c['slug'])) ?>">View products <?= icon('arrow', 16) ?></a>
    </div>
</article>
