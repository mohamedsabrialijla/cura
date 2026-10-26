<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$urls = [
    'https://backend.keepersport.at/sa/goalkeeper-gloves-keepersport-champ-elite-nc.html',
    'https://www.keepersport.net/goalkeeper-gloves-keepersport-champ-elite-nc.html',
];

$svc = app(App\Services\KeeperSportCatalogService::class);
$ref = new ReflectionClass($svc);

$httpGet = $ref->getMethod('httpGetUpstreamProductPage');
$httpGet->setAccessible(true);
$parse = $ref->getMethod('parseRecommendedSkusFromHtml');
$parse->setAccessible(true);

foreach ($urls as $url) {
    echo "URL: {$url}\n";
    $html = $httpGet->invoke($svc, $url);
    if ($html === null) {
        echo "  fetch: FAILED\n";
        continue;
    }
    echo '  fetch: OK, len=' . strlen($html) . "\n";
    echo '  has product-list: ' . (str_contains($html, 'product-list/product-list') ? 'yes' : 'no') . "\n";
    echo '  has Recommended: ' . (stripos($html, 'Recommended products') !== false ? 'yes' : 'no') . "\n";
    $skus = $parse->invoke($svc, $html);
    echo '  skus: ' . count($skus) . "\n";
    if ($skus) {
        echo '  first: ' . implode(', ', array_slice($skus, 0, 5)) . "\n";
    }
    echo "\n";
}
