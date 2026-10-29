<?php
require __DIR__ . '/includes/bootstrap.php';

$errors = [];
$old = ['name' => '', 'organisation' => '', 'email' => '', 'phone' => '', 'location' => '', 'needed_by' => '', 'items' => '', 'details' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) {
        $old[$k] = trim((string) ($_POST[$k] ?? ''));
    }

    if (!csrf_ok()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (!empty($_POST['website'])) {
        // Honeypot filled in: silently pretend success.
        flash('Thank you! Your quote request has been received.');
        redirect('quote.php');
    } else {
        if ($old['name'] === '' || mb_strlen($old['name']) > 120) {
            $errors[] = 'Please enter your name.';
        }
        if (mb_strlen($old['organisation']) > 160) {
            $errors[] = 'The organisation name is too long.';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 190) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($old['phone'] !== '' && !preg_match('~^[0-9+()\-\s]{6,40}$~', $old['phone'])) {
            $errors[] = 'Please enter a valid phone number, or leave it blank.';
        }
        if (mb_strlen($old['location']) > 160 || mb_strlen($old['needed_by']) > 80) {
            $errors[] = 'One of your answers is too long.';
        }
        if (mb_strlen($old['items']) > 6000 || mb_strlen($old['details']) > 4000) {
            $errors[] = 'Your list or details are too long.';
        }
        if (mb_strlen($old['items']) + mb_strlen($old['details']) < 5) {
            $errors[] = 'Please tell us what you need, using the item list or the details box.';
        }
        if (!$errors && !rate_ok('quote', 5, 3600)) {
            $errors[] = 'Too many requests from your connection. Please try again later, or call us.';
        }
    }

    if (!$errors) {
        $pdo = db();
        $pdo->prepare('INSERT INTO quotes (name, organisation, email, phone, location, needed_by, items, details) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$old['name'], $old['organisation'], $old['email'], $old['phone'], $old['location'], $old['needed_by'], $old['items'], $old['details']]);
        $id = (int) $pdo->lastInsertId();
        $ref = 'PE-' . date('ymd') . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
        $pdo->prepare('UPDATE quotes SET ref = ? WHERE id = ?')->execute([$ref, $id]);
        notify("New quote request $ref", "Ref: $ref\nName: {$old['name']}\nOrganisation: {$old['organisation']}\nEmail: {$old['email']}\nPhone: {$old['phone']}\nLocation: {$old['location']}\nNeeded by: {$old['needed_by']}\n\nItems:\n{$old['items']}\n\nDetails:\n{$old['details']}");
        $_SESSION['quote_done'] = $ref;
        redirect('quote.php?sent=1');
    }
}

$done = isset($_GET['sent']) && !empty($_SESSION['quote_done']) ? $_SESSION['quote_done'] : null;

$pageTitle = 'Request a quote';
$metaDesc  = 'Request a quote from ' . setting('site_name') . ' for bulk supplies, printing and branding.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow">Wholesale &amp; bulk orders</p>
        <h1>Request a quote</h1>
        <p class="lead">Tell us what you need. We will reply with a competitive quote.</p>
    </div>
</section>

<section class="section">
    <div class="container contact-grid">
<?php if ($done): ?>
        <div class="panel success-panel" id="quote-done">
            <span class="big-check"><?= icon('check', 34) ?></span>
            <h2>Thank you, your request is in</h2>
            <p>Your reference number is <strong class="ref"><?= e($done) ?></strong>. Please keep it for follow-up.</p>
            <p>We will get back to you on the email or phone number you gave. For anything urgent, call <a href="tel:+<?= e(phone_digits(setting('phone'))) ?>"><?= e(setting('phone')) ?></a>.</p>
            <p><a class="btn btn-primary" href="<?= e(url('products.php')) ?>">Keep browsing</a></p>
        </div>
<?php else: ?>
        <form class="form" method="post" id="quote-form" novalidate>
            <?= csrf_field() ?>
            <?php if ($errors): ?>
                <div class="alert alert-error" role="alert"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

            <fieldset class="panel">
                <legend>Your items</legend>
                <div id="basket-empty" class="muted small">Add products from a <a href="<?= e(url('products.php')) ?>">category page</a>, or type your list below.</div>
                <ul class="basket-list" id="basket-list"></ul>
                <div class="field"><label for="items">Items and quantities <span class="opt">(one per line)</span></label>
                    <textarea id="items" name="items" rows="6" maxlength="6000" placeholder="e.g. 10 x A4 paper reams&#10;5 x HP 85A toner&#10;50 x branded T-shirts"><?= e($old['items']) ?></textarea></div>
                <button type="button" class="btn btn-sm btn-ghost-dark" id="basket-clear" hidden>Clear list</button>
            </fieldset>

            <fieldset class="panel">
                <legend>About you</legend>
                <div class="row">
                    <div class="field"><label for="name">Your name</label>
                        <input id="name" name="name" required maxlength="120" value="<?= e($old['name']) ?>" autocomplete="name"></div>
                    <div class="field"><label for="organisation">Organisation <span class="opt">(optional)</span></label>
                        <input id="organisation" name="organisation" maxlength="160" value="<?= e($old['organisation']) ?>" autocomplete="organization"></div>
                </div>
                <div class="row">
                    <div class="field"><label for="email">Email address</label>
                        <input id="email" name="email" type="email" required maxlength="190" value="<?= e($old['email']) ?>" autocomplete="email"></div>
                    <div class="field"><label for="phone">Phone / WhatsApp <span class="opt">(optional)</span></label>
                        <input id="phone" name="phone" type="tel" maxlength="40" value="<?= e($old['phone']) ?>" autocomplete="tel"></div>
                </div>
                <div class="row">
                    <div class="field"><label for="location">Delivery location <span class="opt">(optional)</span></label>
                        <input id="location" name="location" maxlength="160" placeholder="Town or area" value="<?= e($old['location']) ?>"></div>
                    <div class="field"><label for="needed_by">Needed by <span class="opt">(optional)</span></label>
                        <input id="needed_by" name="needed_by" maxlength="80" placeholder="e.g. end of the month" value="<?= e($old['needed_by']) ?>"></div>
                </div>
                <div class="field"><label for="details">Anything else? <span class="opt">(optional)</span></label>
                    <textarea id="details" name="details" rows="4" maxlength="4000" placeholder="Branding requirements, brands you prefer, budget..."><?= e($old['details']) ?></textarea></div>
            </fieldset>
            <button class="btn btn-primary" type="submit">Send quote request</button>
        </form>
<?php endif; ?>

        <aside class="panel info-panel">
            <h3>Prefer to talk?</h3>
            <ul class="footer-contact dark">
                <li><?= icon('phone', 20) ?> <span><a href="tel:+<?= e(phone_digits(setting('phone'))) ?>"><?= e(setting('phone')) ?></a>
                    <?php if (setting('phone2') !== ''): ?><br><a href="tel:+<?= e(phone_digits(setting('phone2'))) ?>"><?= e(setting('phone2')) ?></a><?php endif; ?></span></li>
                <li><?= icon('mail', 20) ?> <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
            </ul>
            <h3>What happens next?</h3>
            <ol class="steps">
                <li>You send your list.</li>
                <li>We check stock and pricing.</li>
                <li>We send you a quote.</li>
                <li>You confirm and we deliver.</li>
            </ol>
        </aside>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
