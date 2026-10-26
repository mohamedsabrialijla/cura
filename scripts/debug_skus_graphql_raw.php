<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$endpoint = config('services.keepersport_graphql.endpoint');
$token = config('services.keepersport_graphql.token');
$username = config('services.keepersport_graphql.username');
$password = config('services.keepersport_graphql.password');

$query = <<<'GQL'
query ($input: CatalogInput) {
  catalog(input: $input) {
    products {
      edges {
        node { sku name }
      }
    }
  }
}
GQL;

$headers = ['Accept-Language' => 'en'];
if ($token) {
    $headers['Authorization'] = 'Bearer ' . $token;
    $headers['Token'] = $token;
}

$verifySsl = config('services.keepersport_graphql.verify_ssl', true);
$ca = config('services.keepersport_graphql.ca_bundle', '');
$opts = ['connect_timeout' => 30, 'timeout' => 60];
if ($ca && is_file($ca)) {
    $opts['verify'] = $ca;
} elseif ($verifySsl === false || $verifySsl === 'false') {
    $opts['verify'] = false;
}

$client = Illuminate\Support\Facades\Http::withBasicAuth($username, $password)
    ->withHeaders($headers)
    ->withOptions($opts);

$inputs = [
    ['first' => 3, 'skus' => ['KS10050-108'], 'stockedOnly' => false],
    ['first' => 3, 'skus' => ['KS10052-452', 'KE80008-630'], 'stockedOnly' => false],
    ['first' => 3, 'fulltextQuery' => 'KS10052-452', 'stockedOnly' => false],
];

foreach ($inputs as $i => $input) {
    echo "Input #" . ($i + 1) . ': ' . json_encode($input) . PHP_EOL;
    $resp = $client->post($endpoint, ['query' => $query, 'variables' => ['input' => $input]]);
    echo 'status: ' . $resp->status() . PHP_EOL;
    $json = $resp->json();
    if (! empty($json['errors'])) {
        echo 'errors: ' . json_encode($json['errors'], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
    $edges = $json['data']['catalog']['products']['edges'] ?? [];
    echo 'edges: ' . count($edges) . PHP_EOL;
    foreach ($edges as $e) {
        echo '  ' . ($e['node']['sku'] ?? '') . PHP_EOL;
    }
    echo PHP_EOL;
}
