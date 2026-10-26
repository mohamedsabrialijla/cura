<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = $argv[1] ?? 'goalkeeper-gloves-keepersport-champ-elite-nc';
$svc = app(App\Services\KeeperSportCatalogService::class);
$p = $svc->productBySlug($slug, 'en');
if (! $p) {
    echo "product not found\n";
    exit(1);
}
$rec = $p['recommended_products'] ?? [];
echo 'sku: ' . ($p['sku'] ?? '') . PHP_EOL;
echo 'recommended count: ' . count($rec) . PHP_EOL;
foreach (array_slice($rec, 0, 5) as $item) {
    echo ' - ' . ($item['sku'] ?? '') . ' | ' . ($item['name'] ?? '') . PHP_EOL;
}
