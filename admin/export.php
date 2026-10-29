<?php
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

$type = ($_GET['type'] ?? 'quotes') === 'messages' ? 'messages' : 'quotes';
$rows = db()->query("SELECT * FROM $type ORDER BY id")->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $type . '-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads accents correctly
if ($rows) {
    fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $r) {
        // Neutralise spreadsheet formula injection.
        foreach ($r as $k => $v) {
            if (is_string($v) && $v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) {
                $r[$k] = "'" . $v;
            }
        }
        fputcsv($out, $r);
    }
}
fclose($out);
