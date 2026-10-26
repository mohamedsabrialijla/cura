<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = $argv[1] ?? 'goalkeeper-gloves-keepersport-champ-elite-nc';
$svc = app(App\Services\KeeperSportCatalogService::class);
$p = $svc->productBySlug($slug, 'en');
if (! $p) {
    echo "not found\n";
    exit(1);
}
echo json_encode($p, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
