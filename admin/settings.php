<?php
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

// [key => [label, type]]  types: text, textarea, email, url, bool
$groups = [
    'Identity' => [
        'site_name'   => ['Business name', 'text'],
        'tagline'     => ['Tagline', 'text'],
        'footer_note' => ['Footer note', 'text'],
    ],
    'Home page' => [
        'hero_kicker' => ['Small heading above the main title', 'text'],
        'hero_title'  => ['Main headline', 'text'],
        'hero_text'   => ['Intro paragraph', 'textarea'],
        'motto'       => ['Motto / quote', 'text'],
    ],
    'About us' => [
        'about_who'   => ['Who we are', 'textarea'],
        'about_what'  => ['What we do', 'textarea'],
        'mission'     => ['Mission', 'textarea'],
        'why_us'      => ['Why choose us (one per line)', 'textarea'],
        'proposition_network'    => ['Unique proposition: network leverage', 'textarea'],
        'proposition_dedication' => ['Unique proposition: our dedication', 'textarea'],
        'institutions_note'      => ['Note for academic institutions (blank to hide)', 'text'],
        'credentials' => ['Credentials to show (one per line; only list what is currently valid)', 'textarea'],
        'clients'     => ['Organisations you serve (one per line; only list those who agreed to be named)', 'textarea'],
    ],
    'Contact details' => [
        'phone'     => ['Main phone', 'text'],
        'phone2'    => ['Second phone (optional)', 'text'],
        'whatsapp'  => ['WhatsApp number, digits only, with country code (blank to hide the button)', 'text'],
        'email'     => ['Public email', 'email'],
        'address'   => ['Address', 'textarea'],
        'hours'     => ['Opening hours', 'text'],
        'facebook_name' => ['Facebook page name', 'text'],
        'facebook'  => ['Facebook page link (https://...)', 'url'],
    ],
    'Banking details' => [
        'show_bank' => ['Show banking details on the Contact page', 'bool'],
        'bank_name' => ['Bank', 'text'],
        'bank_account_name' => ['Account name', 'text'],
        'bank_account_no'   => ['Account number', 'text'],
        'bank_branch'    => ['Branch name', 'text'],
        'bank_branch_no' => ['Branch number', 'text'],
        'bank_swift'     => ['Swift code', 'text'],
    ],
];
$maxLen = ['textarea' => 4000, 'text' => 300, 'email' => 190, 'url' => 300, 'bool' => 1];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = (string) ($_POST['form'] ?? '');

    if (!csrf_ok()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif ($form === 'password') {
        $cur  = (string) ($_POST['current'] ?? '');
        $new  = (string) ($_POST['new'] ?? '');
        $new2 = (string) ($_POST['new2'] ?? '');
        $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        if (!$admin || !password_verify($cur, $admin['password_hash'])) {
            $errors[] = 'Your current password is incorrect.';
        } elseif (strlen($new) < 10) {
            $errors[] = 'New password must be at least 10 characters.';
        } elseif ($new !== $new2) {
            $errors[] = 'The new passwords do not match.';
        } else {
            db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
            session_regenerate_id(true);
            flash('Password changed.');
            redirect('admin/settings.php');
        }
    } else {
        $save = db()->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON CONFLICT(k) DO UPDATE SET v = excluded.v');
        foreach ($groups as $fields) {
            foreach ($fields as $key => [$label, $type]) {
                if ($type === 'bool') {
                    $save->execute([$key, !empty($_POST[$key]) ? '1' : '0']);
                    continue;
                }
                $val = trim((string) ($_POST[$key] ?? ''));
                if (mb_strlen($val) > $maxLen[$type]) {
                    $errors[] = "\"$label\" is too long.";
                    continue;
                }
                if ($val !== '' && $type === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "\"$label\" is not a valid email address.";
                    continue;
                }
                if ($val !== '' && $type === 'url' && !preg_match('~^https?://[^\s]+$~i', $val)) {
                    $errors[] = "\"$label\" must start with http:// or https://";
                    continue;
                }
                if ($key === 'whatsapp' && $val !== '' && !preg_match('~^[0-9]{8,15}$~', $val)) {
                    $errors[] = 'WhatsApp number must be digits only, e.g. 260962342018.';
                    continue;
                }
                if ($key === 'site_name' && $val === '') {
                    $errors[] = 'Business name cannot be empty.';
                    continue;
                }
                $save->execute([$key, $val]);
            }
        }
        if (!$errors) {
            flash('Settings saved.');
            redirect('admin/settings.php');
        }
    }
}

$posted = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'settings';
$adminTitle = 'Settings';
require __DIR__ . '/_header.php';
?>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<form class="form" method="post">
    <?= csrf_field() ?><input type="hidden" name="form" value="settings">
    <?php foreach ($groups as $title => $fields): ?>
        <fieldset class="panel">
            <legend><?= e($title) ?></legend>
            <?php foreach ($fields as $key => [$label, $type]):
                $val = $posted ? (string) ($_POST[$key] ?? '') : setting($key); ?>
                <div class="field">
                    <?php if ($type === 'bool'): ?>
                        <label class="check"><input type="checkbox" name="<?= e($key) ?>" value="1"<?= ($posted ? !empty($_POST[$key]) : $val === '1') ? ' checked' : '' ?>> <?= e($label) ?></label>
                    <?php else: ?>
                        <label for="s_<?= e($key) ?>"><?= e($label) ?></label>
                        <?php if ($type === 'textarea'): ?>
                            <textarea id="s_<?= e($key) ?>" name="<?= e($key) ?>" rows="4"><?= e($val) ?></textarea>
                        <?php else: ?>
                            <input id="s_<?= e($key) ?>" name="<?= e($key) ?>" type="<?= e($type) ?>" value="<?= e($val) ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </fieldset>
    <?php endforeach; ?>
    <button class="btn btn-primary" type="submit">Save settings</button>
</form>

<form class="form panel" method="post" autocomplete="off" style="margin-top:2rem">
    <?= csrf_field() ?><input type="hidden" name="form" value="password">
    <h2>Change password</h2>
    <div class="field"><label for="current">Current password</label><input id="current" name="current" type="password" required></div>
    <div class="row">
        <div class="field"><label for="new">New password (10+ characters)</label><input id="new" name="new" type="password" required minlength="10"></div>
        <div class="field"><label for="new2">Repeat new password</label><input id="new2" name="new2" type="password" required minlength="10"></div>
    </div>
    <button class="btn btn-primary" type="submit">Change password</button>
</form>
<?php require __DIR__ . '/_footer.php'; ?>
