<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sku = $argv[1] ?? 'SET-KS134';
$endpoint = $argv[2] ?? 'https://www.keepersport.net/graphql';

$query = <<<'GQL'
query ($input: CatalogInput) {
  catalog(input: $input) {
    products {
      edges {
        node {
          sku
          name
          url
          images { url }
        }
      }
    }
  }
}
GQL;

$inputs = [
    ['first' => 5, 'skus' => [$sku], 'stockedOnly' => false],
    ['first' => 40, 'fulltextQuery' => $sku, 'stockedOnly' => false],
];

foreach ($inputs as $i => $input) {
    echo "endpoint={$endpoint} input=" . json_encode($input) . PHP_EOL;
    $headers = ['Accept-Language' => 'en'];
    $token = (string) config('services.keepersport_graphql.token', '');
    $user = (string) config('services.keepersport_graphql.username', '');
    $pass = (string) config('services.keepersport_graphql.password', '');
    if ($token !== '') {
        $headers['Authorization'] = 'Bearer ' . $token;
        $headers['Token'] = $token;
    }
    $req = Illuminate\Support\Facades\Http::withHeaders($headers);
    if ($user !== '' && $pass !== '') {
        $req = $req->withBasicAuth($user, $pass);
    }
    $resp = $req->withOptions(['verify' => false, 'timeout' => 60])
        ->post($endpoint, ['query' => $query, 'variables' => ['input' => $input]]);
    $json = $resp->json();
    if (! empty($json['errors'])) {
        echo '  ERR: ' . ($json['errors'][0]['message'] ?? '') . PHP_EOL;
    } else {
        $edges = $json['data']['catalog']['products']['edges'] ?? [];
        echo '  edges: ' . count($edges) . PHP_EOL;
        foreach ($edges as $edge) {
            $n = $edge['node'] ?? [];
            echo '    ' . ($n['sku'] ?? '?') . ' | ' . ($n['name'] ?? '') . PHP_EOL;
        }
    }
    echo PHP_EOL;
}
