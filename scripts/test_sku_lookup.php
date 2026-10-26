<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$skus = [
    'KE80008-630', 'SET-KS134', 'KE80028-999', 'KS10050-555', 'KS10050-804',
    'KE80024-613', 'KS10052-452', 'KS10050-903', 'KS10050-108', 'KS40016K-374',
];

$client = app(App\Services\KeepersportGraphqlCatalogClient::class);
$ref = new ReflectionClass($client);
$req = $ref->getMethod('fetchProductEdges');
$req->setAccessible(true);

echo "=== batch skus[] ===\n";
try {
    $edges = $req->invoke($client, 'en', count($skus), [], null, false, 2, $skus);
    echo 'edges: ' . count($edges) . PHP_EOL;
    foreach ($edges as $edge) {
        $n = $edge['node'] ?? [];
        echo '  - ' . ($n['sku'] ?? '?') . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'ERR: ' . $e->getMessage() . PHP_EOL;
}

echo "\n=== per sku skus[] ===\n";
foreach ($skus as $sku) {
    try {
        $edges = $req->invoke($client, 'en', 5, [], null, false, 1, [$sku]);
        $found = null;
        foreach ($edges as $edge) {
            $n = $edge['node'] ?? [];
            if (strcasecmp((string) ($n['sku'] ?? ''), $sku) === 0) {
                $found = $n['sku'];
            }
        }
        echo "{$sku}: " . ($found ? "OK {$found}" : 'MISS (' . count($edges) . ' edges)') . PHP_EOL;
    } catch (Throwable $e) {
        echo "{$sku}: ERR " . $e->getMessage() . PHP_EOL;
    }
}

echo "\n=== client fetchProductNodeBySku ===\n";
foreach ($skus as $sku) {
    $n = $client->fetchProductNodeBySku('en', $sku);
    echo "{$sku}: " . ($n ? 'OK' : 'FAIL') . PHP_EOL;
}
