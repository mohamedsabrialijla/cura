<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = 'goalkeeper-gloves-keepersport-champ-elite-nc';
$svc = app(App\Services\KeeperSportCatalogService::class);
$p = $svc->productBySlug($slug, 'en');
if (! $p) {
    exit(1);
}

$ref = new ReflectionClass($svc);
$fetchSkus = $ref->getMethod('fetchRecommendedSkusFromUpstreamPage');
$fetchSkus->setAccessible(true);
$cards = $ref->getMethod('productCardsBySkusOrdered');
$cards->setAccessible(true);

$skus = $fetchSkus->invoke($svc, $p);
echo 'skus from html: ' . count($skus) . PHP_EOL;
echo implode(', ', array_slice($skus, 0, 8)) . PHP_EOL;

$resolved = $cards->invoke($svc, $skus, 'en');
echo 'cards: ' . count($resolved) . PHP_EOL;

$cache = Illuminate\Support\Facades\Cache::store(
    config('services.keepersport_graphql.cache_store') ?: null
);
$key = sprintf('keepersport.recommended.%s.%s', 'en', md5($p['sku']));
$cache->forget($key);
echo "cache cleared\n";

$rec = $svc->recommendedProductsForProduct($p, 'en');
echo 'after cache clear: ' . count($rec) . PHP_EOL;
