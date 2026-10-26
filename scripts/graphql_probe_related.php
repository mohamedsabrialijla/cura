<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sku = $argv[1] ?? 'KS10050-108';
$locale = $argv[2] ?? 'en';

$fields = [
    'relatedProducts { sku name url }',
    'related { sku name url }',
    'crossSellProducts { sku name url }',
    'crossSells { sku name url }',
    'accessories { sku name url }',
    'recommendations { sku name url }',
    'recommendedProducts { sku name url }',
    'upsellProducts { sku name url }',
];

$endpoint = config('services.keepersport_graphql.endpoint');
$token = config('services.keepersport_graphql.token');

foreach ($fields as $field) {
    $query = <<<GQL
    query (\$input: CatalogInput) {
      catalog(input: \$input) {
        products {
          edges {
            node {
              sku
              {$field}
            }
          }
        }
      }
    }
    GQL;

    $headers = ['Accept-Language' => $locale];
    if ($token) {
        $headers['Authorization'] = 'Bearer ' . $token;
        $headers['Token'] = $token;
    }

    $resp = Illuminate\Support\Facades\Http::withHeaders($headers)
        ->timeout(60)
        ->post($endpoint, [
            'query' => $query,
            'variables' => ['input' => ['first' => 1, 'skus' => [$sku], 'stockedOnly' => false]],
        ]);

    $json = $resp->json();
    $errs = $json['errors'] ?? [];
    if ($errs) {
        $msg = $errs[0]['message'] ?? 'error';
        echo "FAIL {$field}: {$msg}\n";
        continue;
    }
    $node = $json['data']['catalog']['products']['edges'][0]['node'] ?? null;
    echo "OK {$field}: " . json_encode($node, JSON_UNESCAPED_UNICODE) . "\n";
}
