<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
$GLOBALS['config'] = require BASE_PATH . '/config.php';

date_default_timezone_set(cfg('timezone', 'UTC'));
if (cfg('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_name('psid');
session_start();

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-ancestors 'self'");

/* ------------------------------------------------------------------ */
/* Basic helpers                                                       */
/* ------------------------------------------------------------------ */

function cfg(string $key, $default = null)
{
    return $GLOBALS['config'][$key] ?? $default;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $manual = cfg('base_url');
    if (is_string($manual)) {
        return $base = rtrim($manual, '/');
    }
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $here    = realpath(BASE_PATH);
    if ($docRoot && $here && str_starts_with($here, $docRoot)) {
        $base = str_replace('\\', '/', substr($here, strlen($docRoot)));
        return $base = rtrim($base, '/');
    }
    return $base = '';
}

function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

/**
 * Responsive image attributes: if smaller copies (name-800.jpg, name-1600.jpg)
 * exist next to the 4K original, let the browser choose the right size.
 */
function img_srcset(string $path, string $sizes = '(max-width: 700px) 100vw, 400px'): string
{
    $info = pathinfo($path);
    if (!isset($info['extension'])) {
        return '';
    }
    $dir   = ($info['dirname'] === '.' ? '' : $info['dirname'] . '/');
    $parts = [];
    foreach ([800, 1600] as $w) {
        $variant = $dir . $info['filename'] . '-' . $w . '.' . $info['extension'];
        if (is_file(__DIR__ . '/../' . $variant)) {
            $parts[] = url($variant) . ' ' . $w . 'w';
        }
    }
    if (!$parts) {
        return '';
    }
    $parts[] = url($path) . ' 3840w';
    return ' srcset="' . e(implode(', ', $parts)) . '" sizes="' . e($sizes) . '"';
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('~[^a-z0-9]+~', '-', $text) ?? '';
    return trim($text, '-') ?: 'item';
}

function paragraphs(string $text): string
{
    $parts = preg_split('~\R{2,}~', trim($text)) ?: [];
    $out = '';
    foreach ($parts as $p) {
        if (trim($p) !== '') {
            $out .= '<p>' . nl2br(e(trim($p))) . '</p>';
        }
    }
    return $out;
}

/** Split a textarea into clean, non-empty lines. */
function lines(string $text): array
{
    return array_values(array_filter(array_map('trim', preg_split('~\R~', $text) ?: []), fn($l) => $l !== ''));
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** Digits-only phone for tel: / wa.me links. */
function phone_digits(string $phone): string
{
    return preg_replace('~[^0-9]~', '', $phone) ?? '';
}

/* ------------------------------------------------------------------ */
/* Database (SQLite via PDO), created and seeded automatically         */
/* ------------------------------------------------------------------ */

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $dir = BASE_PATH . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $pdo = new PDO('sqlite:' . $dir . '/site.sqlite', null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    migrate($pdo);
    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            k TEXT PRIMARY KEY, v TEXT NOT NULL DEFAULT ''
        );
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            title TEXT NOT NULL,
            summary TEXT NOT NULL DEFAULT '',
            body TEXT NOT NULL DEFAULT '',
            items TEXT NOT NULL DEFAULT '',
            tagline TEXT NOT NULL DEFAULT '',
            icon TEXT NOT NULL DEFAULT 'box',
            image TEXT NOT NULL DEFAULT '',
            status TEXT NOT NULL DEFAULT 'published',
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS quotes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ref TEXT NOT NULL DEFAULT '',
            name TEXT NOT NULL,
            organisation TEXT NOT NULL DEFAULT '',
            email TEXT NOT NULL,
            phone TEXT NOT NULL DEFAULT '',
            location TEXT NOT NULL DEFAULT '',
            items TEXT NOT NULL DEFAULT '',
            details TEXT NOT NULL DEFAULT '',
            needed_by TEXT NOT NULL DEFAULT '',
            status TEXT NOT NULL DEFAULT 'new',
            admin_note TEXT NOT NULL DEFAULT '',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL, email TEXT NOT NULL,
            phone TEXT NOT NULL DEFAULT '',
            subject TEXT NOT NULL DEFAULT '', message TEXT NOT NULL,
            is_read INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS rate_limits (
            bucket TEXT NOT NULL, ip TEXT NOT NULL, ts INTEGER NOT NULL
        );
        CREATE INDEX IF NOT EXISTS idx_rate ON rate_limits(bucket, ip, ts);
    ");

    $seeded = $pdo->query("SELECT v FROM settings WHERE k = 'seeded'")->fetchColumn();
    if (!$seeded) {
        seed($pdo);
    }
}

function default_settings(): array
{
    return [
        'site_name'   => 'Printel Enterprise',
        'tagline'     => 'Connecting needs. Delivering solutions.',
        'hero_kicker' => 'Non-specialised wholesale trade',
        'hero_title'  => 'Your one-stop source for quality products and solutions.',
        'hero_text'   => 'From printing and branding to computers, office supplies, workwear and cleaning products, Printel Enterprise supplies businesses, institutions and communities across Zambia in bulk, at competitive prices.',
        'motto'       => 'Creativity is nothing without purpose. We brand anything.',
        'about_who'   => 'Printel Enterprise is a trusted wholesale supplier of diverse products for businesses, industries, institutions and communities.',
        'about_what'  => 'We provide quality products in bulk at competitive prices, ensuring reliability, consistency and value for our customers.',
        'mission'     => 'To deliver a wide range of quality products and solutions that support businesses, industries, institutions and communities across Zambia.',
        'proposition_network'   => 'Partnering with leading suppliers and brands to bring you the best.',
        'proposition_dedication' => 'Committed to quality, reliability and customer satisfaction in every transaction.',
        'why_us'      => "Wide range of products\nCompetitive prices\nQuality guaranteed\nReliable supply & service",
        'institutions_note' => 'We provide affordable procurement services to academic institutions.',
        'credentials' => "Registered business (PACRA)\nZRA tax clearance",
        'clients'     => "Micro-Pro Innovations Limited\nMeanwood General Insurance Company\nVisberg Construction\nFinchgold General Dealers",
        'phone'       => '+260 962 342 018',
        'phone2'      => '+260 973 383 435',
        'whatsapp'    => '260962342018',
        'email'       => 'Printelzm@gmail.com',
        'address'     => "Off Burma Road, Kabwata\nLusaka, Zambia",
        'hours'       => 'Mon to Fri, 08:00 to 17:00',
        'facebook'    => '',
        'facebook_name' => 'Printel Enterprises',
        'show_bank'   => '0',
        'bank_name'   => 'First National Bank (FNB) Zambia',
        'bank_account_name' => 'Printel Enterprises',
        'bank_account_no'   => '63001218783',
        'bank_branch' => 'Commercial Suite',
        'bank_branch_no' => '260001',
        'bank_swift'  => 'FIRNZMLX',
        'footer_note' => 'Registered business in Zambia.',
        'seeded'      => '1',
    ];
}

function seed(PDO $pdo): void
{
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings (k, v) VALUES (?, ?)');
    foreach (default_settings() as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    // [title, tagline, icon, image, summary, items]
    $cats = [
        ['Printing & Branding', 'We brand anything.', 'print', 'assets/img/products/digital-printing.jpg',
            'Bulk photocopying, printing, banners, ID and business cards, and printed stationery.',
            "Bulk photocopying\nPrinting\nID cards\nBusiness cards\nPrinted stationery (receipts, invoices, delivery notes)\nBanners and roll-up banners\nName tags\nScreen printing and DTG (direct to garment)\nEmbroidery\nCoroplast yard signs\nBranded mugs"],
        ['Promotional Products', 'Put your logo everywhere.', 'gift', 'assets/img/products/promotional.jpg',
            'Branded caps, mugs, bottles, bags, notebooks, pens and USB drives for your business or event.',
            "Branded caps\nBranded mugs\nWater bottles\nBags\nNotebooks and diaries\nPens\nKeyrings\nUSB flash drives"],
        ['Printers & Copiers', 'Print. Copy. Scan. Perform.', 'print', 'assets/img/products/printers-copiers.jpg',
            'Multifunction printers, photocopiers, scanners and large-format printers, with parts and maintenance.',
            "Multifunction printers\nPhotocopiers and scanners\nLarge-format printers\nPrinter accessories and parts\nMaintenance and spare parts"],
        ['Ink, Toners & Consumables', 'Quality. Compatibility. Value.', 'drop', 'assets/img/products/ink-toners.jpg',
            'Original and compatible toners, ink cartridges and other bulk printing consumables.',
            "Original and compatible toners\nInk cartridges\nDrums and imaging units\nBulk printing consumables"],
        ['IT Equipment & Computer Accessories', 'Technology for every business.', 'laptop', 'assets/img/products/it-equipment.jpg',
            'Laptops, desktops, monitors, networking devices, storage and computer peripherals.',
            "Computers and laptops\nMonitors and accessories\nPrinters and scanners\nNetworking devices\nStorage and memory\nComputer peripherals"],
        ['Cell Phones, Gadgets & Accessories', 'Connect. Protect. Power.', 'phone', 'assets/img/products/phones.jpg',
            'Cell phones and gadgets with chargers, cables, earphones, power banks, cases and more.',
            "Cell phones and gadgets\nChargers and power adapters\nCables and connectors\nEarphones and headsets\nPower banks\nPhone cases and covers\nScreen protectors\nCar holders and mounts\nMemory cards and USB drives"],
        ['Office & School Supplies', 'Everyday items. Exceptional quality.', 'clip', 'assets/img/products/office-school.jpg',
            'Binders, files, folders, desk accessories and organisers for offices and schools.',
            "Binders, files and folders\nDesk accessories\nOffice furniture and organisers\nStaplers, tape and sticky notes\nCalculators"],
        ['Stationery & Paper', 'Everything you write and print on.', 'book', 'assets/img/products/stationery-paper.jpg',
            'Paper, notebooks, pens, markers and packaging materials, in bulk.',
            "A4 paper and other paper sizes\nNotebooks\nPens and markers\nPackaging materials"],
        ['Workwear & PPE', 'Work safe. Look professional.', 'shield', 'assets/img/products/workwear-ppe.jpg',
            'Overalls, reflective jackets, helmets, gloves and eye protection for teams and sites.',
            "Overalls and workwear\nReflective jackets\nSafety helmets\nGloves\nSafety glasses\nSafety boots"],
        ['Safety Signs & Systems', 'Clear signs. Safer places.', 'sign', 'assets/img/products/safety-signs.jpg',
            'Safety and warning signs, first aid kits, fire extinguishers and detection equipment.',
            "Safety and warning signs\nFirst aid kits\nFire extinguishers\nFire and smoke detection\nEmergency exit signs"],
        ['Cleaning & Janitorial Supplies', 'Clean spaces. Safer places.', 'spark', 'assets/img/products/cleaning.jpg',
            'Detergents, cleaning tools, paper products and hygiene supplies for any premises.',
            "Detergents and chemicals\nCleaning tools and equipment\nPaper products\nWaste management solutions\nHygiene and sanitation products"],
        ['Tools & Equipment', 'The right tool for the job.', 'wrench', 'assets/img/products/tools.jpg',
            'Hand tools, power tools, tool boxes and measuring equipment.',
            "Hand tools\nPower tools\nTool boxes\nMeasuring tools"],
    ];
    $ins = $pdo->prepare('INSERT OR IGNORE INTO categories
        (slug, title, tagline, icon, image, summary, body, items, status, sort_order)
        VALUES (?,?,?,?,?,?,?,?,?,?)');
    foreach ($cats as $i => [$title, $tag, $icon, $img, $sum, $items]) {
        $body = "Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.\n\nEdit or replace this text from the admin panel (Categories).";
        $ins->execute([slugify($title), $title, $tag, $icon, $img, $sum, $body, $items, 'published', $i + 1]);
    }
}

function setting(string $key, string $default = ''): string
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (db()->query('SELECT k, v FROM settings') as $row) {
            $all[$row['k']] = $row['v'];
        }
    }
    return $all[$key] ?? default_settings()[$key] ?? $default;
}

/* ------------------------------------------------------------------ */
/* CSRF, flash messages, rate limiting                                 */
/* ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    $sent     = $_POST['_csrf'] ?? '';
    $expected = $_SESSION['csrf'] ?? '';
    return is_string($sent) && is_string($expected) && $expected !== '' && hash_equals($expected, $sent);
}

function flash(?string $msg = null, string $type = 'success')
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/** Returns true if the action is allowed, and records it. */
function rate_ok(string $bucket, int $max, int $windowSeconds): bool
{
    $pdo = db();
    $now = time();
    $pdo->prepare('DELETE FROM rate_limits WHERE ts < ?')->execute([$now - 86400]);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM rate_limits WHERE bucket = ? AND ip = ? AND ts > ?');
    $stmt->execute([$bucket, client_ip(), $now - $windowSeconds]);
    if ((int) $stmt->fetchColumn() >= $max) {
        return false;
    }
    $pdo->prepare('INSERT INTO rate_limits (bucket, ip, ts) VALUES (?,?,?)')
        ->execute([$bucket, client_ip(), $now]);
    return true;
}

function notify(string $subject, string $body): void
{
    $to = cfg('notify_email');
    if ($to && filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $from = setting('email');
        @mail($to, '[' . setting('site_name') . '] ' . $subject, $body,
            'From: ' . (filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : $to));
    }
}

/* ------------------------------------------------------------------ */
/* Admin authentication                                                */
/* ------------------------------------------------------------------ */

function admin_exists(): bool
{
    return (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
}

function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_exists()) {
        redirect('admin/setup.php');
    }
    if (!is_admin()) {
        redirect('admin/login.php');
    }
}

/* ------------------------------------------------------------------ */
/* Icons (inline SVG)                                                  */
/* ------------------------------------------------------------------ */

function icon_choices(): array
{
    return ['print' => 'Printer', 'gift' => 'Gift / Promotional', 'drop' => 'Ink drop', 'laptop' => 'Laptop / IT',
            'phone' => 'Phone', 'clip' => 'Paper clip / Office', 'book' => 'Book / Stationery', 'shield' => 'Shield / Safety',
            'sign' => 'Sign / Warning', 'spark' => 'Sparkle / Clean', 'wrench' => 'Tools', 'box' => 'Box / Packaging'];
}

function icon(string $name, int $size = 28): string
{
    $paths = [
        'print'  => '<path d="M7 9V3h10v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M7 14h10v7H7z"/>',
        'gift'   => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v9h14v-9M12 8v13"/><path d="M12 8C10 8 8 7 8 5.500S9.500 3 12 8c2.500-5 4-3.500 4-2.500S14 8 12 8z"/>',
        'drop'   => '<path d="M12 3s6 6.200 6 10.500a6 6 0 0 1-12 0C6 9.200 12 3 12 3z"/>',
        'laptop' => '<rect x="4" y="5" width="16" height="11" rx="1.500"/><path d="M2 20h20"/>',
        'phone'  => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
        'clip'   => '<path d="M20 11l-8.500 8.500a5 5 0 0 1-7-7L13 4a3.500 3.500 0 0 1 5 5l-8.500 8.500a2 2 0 0 1-3-3L14 7"/>',
        'book'   => '<path d="M4 5.500A2.500 2.500 0 0 1 6.500 3H20v15H6.500A2.500 2.500 0 0 0 4 20.500v-15z"/><path d="M4 20.500A2.500 2.500 0 0 0 6.500 23H20v-5"/>',
        'shield' => '<path d="M12 3l8 3v6c0 5-3.500 8-8 9-4.500-1-8-4-8-9V6z"/><path d="M8.500 12l2.500 2.500 4.500-5"/>',
        'sign'   => '<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18h.01"/>',
        'spark'  => '<path d="M12 2l2.400 6.600L21 11l-6.600 2.400L12 20l-2.400-6.600L3 11l6.600-2.400z"/>',
        'wrench' => '<path d="M14.500 6.500a4 4 0 0 0-5 5L3 18l3 3 6.500-6.500a4 4 0 0 0 5-5l-2.500 2.500-2.500-.5-.5-2.500z"/>',
        'box'    => '<path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4M12 11v10"/>',
        'people' => '<circle cx="9" cy="8" r="3.500"/><path d="M2.500 20c0-3.600 2.900-6 6.500-6s6.500 2.400 6.500 6"/><circle cx="17" cy="9" r="2.500"/><path d="M17 14c2.700 0 4.500 1.900 4.500 4.500"/>',
        'truck'  => '<path d="M2 6h11v10H2zM13 9h4l3 3v4h-7"/><circle cx="6.500" cy="17.500" r="1.800"/><circle cx="16.500" cy="17.500" r="1.800"/>',
        'award'  => '<circle cx="12" cy="9" r="6"/><path d="M8.500 14l-1.500 7 5-3 5 3-1.500-7"/>',
        'tag'    => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.500" cy="8.500" r="1.200"/>',
        'code'   => '<path d="M8 8l-5 4 5 4M16 8l5 4-5 4M14 5l-4 14"/>',
        'headset'=> '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3" y="14" width="4" height="6" rx="1.500"/><rect x="17" y="14" width="4" height="6" rx="1.500"/>',
        'globe'  => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/>',
        'check'  => '<path d="M5 12l5 5 9-10"/>',
        'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'pin'    => '<path d="M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.500"/>',
        'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'plus'   => '<path d="M12 5v14M5 12h14"/>',
        'chat'   => '<path d="M4 5h16v11H9l-5 4z"/>',
    ];
    $p = $paths[$name] ?? $paths['box'];
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.700" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}
