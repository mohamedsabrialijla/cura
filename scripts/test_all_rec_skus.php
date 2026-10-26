<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = 'goalkeeper-gloves-keepersport-champ-elite-nc';
$svc = app(App\Services\KeeperSportCatalogService::class);
$p = $svc->productBySlug($slug, 'en');
$ref = new ReflectionClass($svc);
$fetchSkus = $ref->getMethod('fetchRecommendedSkusFromUpstreamPage');
$fetchSkus->setAccessible(true);
$skus = $fetchSkus->invoke($svc, $p);

$client = app(App\Services\KeepersportGraphqlCatalogClient::class);

echo 'All SKUs (' . count($skus) . "):\n";
foreach ($skus as $sku) {
    $node = $client->fetchProductNodeBySku('en', $sku);
    echo $sku . ': ' . ($node ? ($node['sku'] ?? 'OK') : 'FAIL') . PHP_EOL;
}
