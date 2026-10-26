<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$skus = ['KE80008-630', 'KS10052-452', 'KS10050-555'];
$client = app(App\Services\KeepersportGraphqlCatalogClient::class);
$nodes = $client->fetchProductNodesBySkus('en', $skus);
echo 'nodes: ' . count($nodes) . PHP_EOL;
foreach ($nodes as $n) {
    echo ' - ' . ($n['sku'] ?? '') . ' ' . ($n['name'] ?? '') . PHP_EOL;
}
