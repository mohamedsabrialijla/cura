<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = app(App\Services\KeeperSportCatalogService::class);
$slug = 'goalkeeper-gloves-puma-king-ultimate-hybrid-the-arrival-pack';

foreach (['run1', 'run2', 'run3'] as $label) {
    $t = microtime(true);
    $p = $s->productBySlug($slug, 'en');
    $ms = round((microtime(true) - $t) * 1000);
    fwrite(STDOUT, "$label productBySlug={$ms}ms slug=" . ($p['slug'] ?? 'null') . PHP_EOL);
}
