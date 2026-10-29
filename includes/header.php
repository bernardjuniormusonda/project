<?php
/** @var string $pageTitle */
$siteName = setting('site_name');
$current  = basename($_SERVER['SCRIPT_NAME']);
$nav = [
    'index.php'    => 'Home',
    'about.php'    => 'About',
    'products.php' => 'Products',
    'services.php' => 'Services',
    'contact.php'  => 'Contact',
];
if ($current === 'product.php') {
    $current = 'products.php';
}
$fullTitle = ($pageTitle ?? '') !== '' ? $pageTitle . ' | ' . $siteName : $siteName . ' | ' . setting('tagline');
$flash = flash();
$tel = phone_digits(setting('phone'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc ?? setting('hero_text')) ?>">
    <link rel="icon" type="image/png" href="<?= e(url('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script src="<?= e(url('assets/js/main.js')) ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div class="topbar">
    <div class="container topbar-inner">
        <span><?= icon('pin', 15) ?> <?= e(str_replace("\n", ', ', setting('address'))) ?></span>
        <span class="topbar-right">
            <a href="tel:+<?= e($tel) ?>"><?= icon('phone', 15) ?> <?= e(setting('phone')) ?></a>
            <a href="mailto:<?= e(setting('email')) ?>"><?= icon('mail', 15) ?> <?= e(setting('email')) ?></a>
        </span>
    </div>
</div>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('index.php')) ?>" aria-label="<?= e($siteName) ?> home">
            <img class="brand-logo" src="<?= e(url('assets/img/logo.png')) ?>" alt="<?= e($siteName) ?>" width="170" height="56">
        </a>
        <button class="nav-toggle" aria-expanded="false" aria-controls="site-nav" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <nav id="site-nav" class="site-nav" aria-label="Main">
            <ul>
                <?php foreach ($nav as $file => $label): ?>
                    <li><a href="<?= e(url($file)) ?>"<?= $current === $file ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-accent nav-cta" href="<?= e(url('quote.php')) ?>">Get a quote <span class="basket-count" id="basket-count" hidden>0</span></a>
        </nav>
    </div>
</header>
<?php if ($flash): ?>
    <div class="container"><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div></div>
<?php endif; ?>
<main id="main">
