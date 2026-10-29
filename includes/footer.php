</main>
<?php
$wa = phone_digits(setting('whatsapp'));
$showBank = setting('show_bank') === '1';
?>
<?php if ($wa !== ''): ?>
    <a class="whatsapp-fab" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"><?= icon('chat', 26) ?><span>WhatsApp</span></a>
<?php endif; ?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <img class="footer-logo" src="<?= e(url('assets/img/logo-white.png')) ?>" alt="<?= e(setting('site_name')) ?>" width="190" height="55" loading="lazy">
            <p class="muted-light"><?= e(setting('tagline')) ?></p>
            <?php if (setting('facebook') !== ''): ?>
                <p class="social"><a href="<?= e(setting('facebook')) ?>" rel="noopener" target="_blank">Facebook: <?= e(setting('facebook_name')) ?></a></p>
            <?php elseif (setting('facebook_name') !== ''): ?>
                <p class="social muted-light">Find us on Facebook: <?= e(setting('facebook_name')) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <h3>Explore</h3>
            <ul class="footer-links">
                <li><a href="<?= e(url('products.php')) ?>">Products</a></li>
                <li><a href="<?= e(url('services.php')) ?>">Services</a></li>
                <li><a href="<?= e(url('about.php')) ?>">About us</a></li>
                <li><a href="<?= e(url('quote.php')) ?>">Request a quote</a></li>
                <li><a href="<?= e(url('contact.php')) ?>">Contact</a></li>
            </ul>
        </div>
        <div>
            <h3>Visit or call</h3>
            <ul class="footer-contact">
                <li><?= icon('phone', 18) ?> <span><a href="tel:+<?= e(phone_digits(setting('phone'))) ?>"><?= e(setting('phone')) ?></a>
                    <?php if (setting('phone2') !== ''): ?><br><a href="tel:+<?= e(phone_digits(setting('phone2'))) ?>"><?= e(setting('phone2')) ?></a><?php endif; ?></span></li>
                <li><?= icon('mail', 18) ?> <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
                <li><?= icon('pin', 18) ?> <span><?= nl2br(e(setting('address'))) ?></span></li>
                <li><?= icon('clock', 18) ?> <span><?= e(setting('hours')) ?></span></li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>&copy; <?= date('Y') ?> <?= e(setting('site_name')) ?>. All rights reserved.</span>
        <span><?= e(setting('footer_note')) ?></span>
    </div>
</footer>
</body>
</html>
