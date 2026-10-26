<?php
$path = $argv[1] ?? '';
$j = json_decode(file_get_contents($path), true);
$rec = $j['product']['recommended_products'] ?? [];
echo 'production count: ' . count($rec) . PHP_EOL;
foreach ($rec as $r) {
    echo ($r['sku'] ?? '?') . ' | ' . ($r['name'] ?? '') . PHP_EOL;
}
