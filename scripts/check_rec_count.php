<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = $argv[1] ?? 'goalkeeper-gloves-reusch-legacy-2000';
$locale = $argv[2] ?? 'en';

$svc = app(App\Services\KeeperSportCatalogService::class);
$p = $svc->productBySlug($slug, $locale);

if (! $p) {
    echo "PRODUCT NOT FOUND: {$slug}\n";
    exit(1);
}

echo 'slug: ' . ($p['slug'] ?? '') . PHP_EOL;
echo 'sku: ' . ($p['sku'] ?? '') . PHP_EOL;
echo 'name: ' . ($p['name'] ?? '') . PHP_EOL;

$rec = $p['recommended_products'] ?? [];
echo 'recommended resolved: ' . count($rec) . PHP_EOL;

$ref = new ReflectionClass($svc);
$m = $ref->getMethod('fetchRecommendedSkusFromUpstreamPage');
$m->setAccessible(true);
$skus = $m->invoke($svc, $p);
echo 'skus from upstream html: ' . count($skus) . PHP_EOL;
echo implode(', ', $skus) . PHP_EOL;

if (count($rec) > 0) {
    echo PHP_EOL . 'resolved products:' . PHP_EOL;
    foreach ($rec as $r) {
        echo '  - ' . ($r['sku'] ?? '?') . ' | ' . ($r['name'] ?? '') . PHP_EOL;
    }
}
