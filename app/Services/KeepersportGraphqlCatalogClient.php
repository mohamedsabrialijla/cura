<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * جلب كتالوج GraphQL (نفس أسلوب GraphQLService.php في جذر المشروع: pagination كامل + stockedOnly).
 * يُستخدم من KeeperSportCatalogService لصفحات المتجر (PLP).
 */
class KeepersportGraphqlCatalogClient
{
    private string $endpoint;

    private string $username;

    private string $password;

    private string $token;

    public function __construct()
    {
        $this->endpoint = (string) config('services.keepersport_graphql.endpoint', 'https://backend.keepersport.at/sa/graphql');
        $this->username = (string) config('services.keepersport_graphql.username', '');
        $this->password = (string) config('services.keepersport_graphql.password', '');
        $this->token = (string) config('services.keepersport_graphql.token', '');
    }

    /**
     * نفس queryId المستخدم في keepersport.net/sale (فلتر on_sale وليس تصنيف).
     */
    public static function onSaleCatalogQueryId(): string
    {
        return base64_encode(json_encode([
            'in_stock' => '✓',
            'on_sale' => '✓',
            'sort' => 'custom',
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * منتجات التخفيضات عبر CatalogInput.queryId (مثل الموقع العام).
     *
     * @return list<array{node: array<string, mixed>}>
     */
    public function fetchOnSaleProductEdges(
        string $locale,
        ?int $limit = null,
        ?int $maxPages = null,
    ): array {
        $queryId = self::onSaleCatalogQueryId();
        $publicEndpoint = trim((string) config('services.keepersport_graphql.public_graphql_endpoint', ''));

        if ($publicEndpoint !== '') {
            try {
                return $this->fetchProductEdgesAt(
                    $publicEndpoint,
                    $locale,
                    $limit,
                    [],
                    null,
                    true,
                    $maxPages,
                    false,
                    $queryId
                );
            } catch (\Throwable $e) {
                Log::warning('KeepersportGraphqlCatalogClient public on_sale fetch failed; trying primary endpoint', [
                    'locale' => $locale,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $this->fetchProductEdges($locale, $limit, [], null, true, $maxPages, $queryId);
    }

    /**
     * @param  list<string>  $categoryIds
     * @return list<array{node: array<string, mixed>}>
     */
    public function fetchProductEdges(
        string $locale,
        ?int $limit = null,
        array $categoryIds = [],
        ?string $fulltextQuery = null,
        bool $stockedOnly = true,
        ?int $maxPages = null,
        ?string $queryId = null,
    ): array {
        $query = <<<'GRAPHQL'
            query BrowseCatalog($input: CatalogInput) {
                catalog(input: $input) {
                    products {
                        edges {
                            node {
                                __typename
                                sku
                                name
                                url
                                sortOrder
                                releaseDate
                                isOnline
                                areShoes
                                images { url }
                                poster { url }
                                brand { id name logo { url } }
                                badges { label color { cssValue } }
                                categories {
                                    id
                                    name
                                    subCategories { id name }
                                }
                                attributeValues {
                                    key
                                    label
                                    attribute { key label }
                                }
                                descriptions { text type { key name } }
                                listPrice(net: true) { amount currency }
                                priceRange(siteId: "___", net: true) {
                                    exact { amount currency }
                                }
                                offer {
                                    price { amount currency }
                                    countDownTo
                                    discountPercentage
                                    isListPriceBased
                                }
                                ... on ConfigurableProduct {
                                    variants {
                                        product {
                                            sku
                                            size
                                            images { url }
                                            listPrice(net: true) { amount currency }
                                            stock { isInStock maximumBuyableQuantity }
                                        }
                                    }
                                }
                            }
                        }
                        pageInfo {
                            hasNextPage
                            endCursor
                        }
                    }
                }
            }
            GRAPHQL;

        $maxPages = $maxPages ?? max(8, (int) config('services.keepersport_graphql.catalog_max_pages', 24));
        $allEdges = [];
        $after = null;
        $remaining = $limit;
        $pageCount = 0;
        $trimmedFt = $fulltextQuery !== null ? trim($fulltextQuery) : '';

        try {
            do {
                $pageCount++;
                $first = $remaining !== null ? min(100, max(1, $remaining)) : 100;
                $input = [
                    'first' => $first,
                    'stockedOnly' => $stockedOnly,
                ];
                if ($categoryIds !== []) {
                    $input['categoryIds'] = $categoryIds;
                }
                if ($trimmedFt !== '') {
                    $input['fulltextQuery'] = $trimmedFt;
                }
                $trimmedQueryId = $queryId !== null ? trim($queryId) : '';
                if ($trimmedQueryId !== '') {
                    $input['queryId'] = $trimmedQueryId;
                }
                if ($after) {
                    $input['after'] = $after;
                }

                $response = $this->request($locale)->post($this->endpoint, [
                    'query' => $query,
                    'variables' => ['input' => $input],
                ]);

                if ($response->failed()) {
                    throw new \RuntimeException('Keepersport GraphQL HTTP '.$response->status());
                }

                $json = $response->json();
                if (! empty($json['errors']) && ! $this->errorsAreStockOnly($json['errors'])) {
                    $nonStock = $this->filterNonStockErrors($json['errors']);
                    if ($nonStock !== []) {
                        throw new \RuntimeException('Keepersport GraphQL: '.($nonStock[0]['message'] ?? 'error'));
                    }
                }

                $edges = Arr::get($json, 'data.catalog.products.edges', []);
                if (! is_array($edges)) {
                    $edges = [];
                }
                $allEdges = array_merge($allEdges, $edges);

                if ($remaining !== null) {
                    $remaining -= count($edges);
                }

                $hasNext = (bool) Arr::get($json, 'data.catalog.products.pageInfo.hasNextPage', false);
                $after = Arr::get($json, 'data.catalog.products.pageInfo.endCursor');

                if ($pageCount >= $maxPages) {
                    Log::warning('KeepersportGraphqlCatalogClient: max pages reached', [
                        'locale' => $locale,
                        'max_pages' => $maxPages,
                        'category_ids' => $categoryIds,
                        'has_query_id' => $trimmedQueryId !== '',
                    ]);
                    break;
                }
            } while ($hasNext && ($remaining === null || $remaining > 0));
        } catch (\Throwable $e) {
            Log::error('KeepersportGraphqlCatalogClient fetch failed', [
                'locale' => $locale,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $allEdges;
    }

    /**
     * @param  list<string>  $skus
     * @return list<array<string, mixed>>
     */
    public function fetchProductNodesBySkus(string $locale, array $skus): array
    {
        $skus = array_values(array_unique(array_filter(array_map(
            static fn ($s) => trim((string) $s),
            $skus
        ))));
        if ($skus === []) {
            return [];
        }

        $nodes = [];
        foreach ($skus as $sku) {
            $node = $this->fetchProductNodeBySku($locale, $sku);
            if ($node !== null) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /**
     * fulltextQuery + مطابقة SKU الأساس (مثل SET-KS134 ← SET-KS134/SET).
     *
     * @return array<string, mixed>|null
     */
    public function fetchProductNodeBySku(string $locale, string $sku): ?array
    {
        $sku = trim($sku);
        if ($sku === '') {
            return null;
        }

        $node = $this->fetchProductNodeBySkuViaFulltext($this->endpoint, $locale, $sku, true);
        if ($node !== null) {
            return $node;
        }

        return $this->fetchPublicProductNodeBySku($locale, $sku);
    }

    /**
     * عرض/مؤقت الخصم من الموقع العام (keepersport.net) — API الـ SA غالباً بدون offer.
     *
     * @return array<string, mixed>|null
     */
    public function fetchPublicProductNodeBySku(string $locale, string $sku): ?array
    {
        $sku = trim($sku);
        if ($sku === '') {
            return null;
        }

        $publicEndpoint = trim((string) config('services.keepersport_graphql.public_graphql_endpoint', ''));
        if ($publicEndpoint === '') {
            return null;
        }

        return $this->fetchProductNodeBySkuViaFulltext($publicEndpoint, $locale, $sku, false);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchProductNodeBySkuViaFulltext(
        string $endpoint,
        string $locale,
        string $sku,
        bool $useAuth,
    ): ?array {
        foreach ($this->fulltextCandidatesForSku($sku) as $term) {
            try {
                $edges = $this->fetchProductEdgesAt(
                    $endpoint,
                    $locale,
                    60,
                    [],
                    $term,
                    false,
                    5,
                    $useAuth
                );
            } catch (\Throwable $e) {
                Log::debug('KeepersportGraphqlCatalogClient fetchProductNodeBySku failed', [
                    'endpoint' => $endpoint,
                    'locale' => $locale,
                    'sku' => $sku,
                    'term' => $term,
                    'message' => $e->getMessage(),
                ]);

                continue;
            }

            foreach ($edges as $edge) {
                $node = $edge['node'] ?? null;
                if (! is_array($node)) {
                    continue;
                }
                if ($this->skuMatchesRequested($sku, (string) ($node['sku'] ?? ''))) {
                    return $node;
                }
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function fulltextCandidatesForSku(string $sku): array
    {
        $sku = trim($sku);
        $base = $this->skuBaseKey($sku);
        $candidates = [$sku];
        if ($base !== '' && strcasecmp($base, $sku) !== 0) {
            $candidates[] = $base;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function skuBaseKey(string $sku): string
    {
        $sku = trim($sku);
        if ($sku === '') {
            return '';
        }
        if (str_contains($sku, '/')) {
            $sku = explode('/', $sku, 2)[0];
        }

        return trim($sku);
    }

    private function skuMatchesRequested(string $requested, string $nodeSku): bool
    {
        $requestedBase = strtoupper($this->skuBaseKey($requested));
        $nodeBase = strtoupper($this->skuBaseKey($nodeSku));
        if ($requestedBase === '' || $nodeBase === '') {
            return false;
        }

        return $requestedBase === $nodeBase;
    }

    /**
     * @param  list<string>  $categoryIds
     * @return list<array{node: array<string, mixed>}>
     */
    private function fetchProductEdgesAt(
        string $endpoint,
        string $locale,
        ?int $limit,
        array $categoryIds,
        ?string $fulltextQuery,
        bool $stockedOnly,
        ?int $maxPages,
        bool $useAuth,
        ?string $queryId = null,
    ): array {
        if ($endpoint === $this->endpoint) {
            return $this->fetchProductEdges($locale, $limit, $categoryIds, $fulltextQuery, $stockedOnly, $maxPages, $queryId);
        }

        $query = <<<'GRAPHQL'
            query BrowseCatalog($input: CatalogInput) {
                catalog(input: $input) {
                    products {
                        edges {
                            node {
                                __typename
                                sku
                                name
                                url
                                sortOrder
                                releaseDate
                                isOnline
                                areShoes
                                images { url }
                                poster { url }
                                brand { id name logo { url } }
                                badges { label color { cssValue } }
                                categories {
                                    id
                                    name
                                    subCategories { id name }
                                }
                                attributeValues {
                                    key
                                    label
                                    attribute { key label }
                                }
                                descriptions { text type { key name } }
                                listPrice(net: true) { amount currency }
                                priceRange(siteId: "___", net: true) {
                                    exact { amount currency }
                                }
                                offer {
                                    price { amount currency }
                                    countDownTo
                                    discountPercentage
                                    isListPriceBased
                                }
                                ... on ConfigurableProduct {
                                    variants {
                                        product {
                                            sku
                                            size
                                            images { url }
                                            listPrice(net: true) { amount currency }
                                            stock { isInStock maximumBuyableQuantity }
                                        }
                                    }
                                }
                            }
                        }
                        pageInfo {
                            hasNextPage
                            endCursor
                        }
                    }
                }
            }
            GRAPHQL;

        $maxPages = $maxPages ?? 5;
        $allEdges = [];
        $after = null;
        $remaining = $limit;
        $pageCount = 0;
        $trimmedFt = $fulltextQuery !== null ? trim($fulltextQuery) : '';

        do {
            $pageCount++;
            $first = $remaining !== null ? min(100, max(1, $remaining)) : 100;
            $input = [
                'first' => $first,
                'stockedOnly' => $stockedOnly,
            ];
            if ($categoryIds !== []) {
                $input['categoryIds'] = $categoryIds;
            }
            if ($trimmedFt !== '') {
                $input['fulltextQuery'] = $trimmedFt;
            }
            $trimmedQueryId = $queryId !== null ? trim($queryId) : '';
            if ($trimmedQueryId !== '') {
                $input['queryId'] = $trimmedQueryId;
            }
            if ($after) {
                $input['after'] = $after;
            }

            $request = $useAuth ? $this->request($locale) : Http::withHeaders(['Accept-Language' => $locale]);
            $response = $request->withOptions(array_merge(
                ['connect_timeout' => 15, 'timeout' => 45],
                $this->sslClientOptions()
            ))->post($endpoint, [
                'query' => $query,
                'variables' => ['input' => $input],
            ]);

            if ($response->failed()) {
                throw new \RuntimeException('Keepersport GraphQL HTTP '.$response->status());
            }

            $json = $response->json();
            if (! empty($json['errors']) && ! $this->errorsAreStockOnly($json['errors'])) {
                $nonStock = $this->filterNonStockErrors($json['errors']);
                if ($nonStock !== []) {
                    throw new \RuntimeException('Keepersport GraphQL: '.($nonStock[0]['message'] ?? 'error'));
                }
            }

            $edges = Arr::get($json, 'data.catalog.products.edges', []);
            if (! is_array($edges)) {
                $edges = [];
            }
            $allEdges = array_merge($allEdges, $edges);

            if ($remaining !== null) {
                $remaining -= count($edges);
            }

            $hasNext = (bool) Arr::get($json, 'data.catalog.products.pageInfo.hasNextPage', false);
            $after = Arr::get($json, 'data.catalog.products.pageInfo.endCursor');

            if ($pageCount >= $maxPages) {
                break;
            }
        } while ($hasNext && ($remaining === null || $remaining > 0));

        return $allEdges;
    }

    private function request(string $locale): PendingRequest
    {
        $headers = ['Accept-Language' => $locale];
        if ($this->token !== '') {
            $headers['Authorization'] = 'Bearer '.$this->token;
            $headers['Token'] = $this->token;
        }

        $connectTimeout = max(5, (int) config('services.keepersport_graphql.connect_timeout', 30));
        $requestTimeout = max($connectTimeout + 5, (int) config('services.keepersport_graphql.request_timeout', 120));

        $guzzleOptions = array_merge(
            [
                'connect_timeout' => $connectTimeout,
                'timeout' => $requestTimeout,
            ],
            $this->sslClientOptions()
        );

        $resolveIp = trim((string) config('services.keepersport_graphql.resolve_ip', ''));
        if ($resolveIp !== '') {
            $host = (string) parse_url($this->endpoint, PHP_URL_HOST);
            $scheme = strtolower((string) parse_url($this->endpoint, PHP_URL_SCHEME));
            $port = (int) (parse_url($this->endpoint, PHP_URL_PORT) ?: ($scheme === 'https' ? 443 : 80));
            if ($host !== '') {
                $guzzleOptions['curl'][CURLOPT_RESOLVE] = [
                    $host.':'.$port.':'.$resolveIp,
                ];
            }
        }

        return Http::withBasicAuth($this->username, $this->password)
            ->withHeaders($headers)
            ->retry(3, 500)
            ->withOptions($guzzleOptions);
    }

    /**
     * @return array<string, mixed>
     */
    private function sslClientOptions(): array
    {
        $ca = (string) config('services.keepersport_graphql.ca_bundle', '');
        if ($ca !== '' && is_file($ca)) {
            return ['verify' => $ca];
        }

        $verifySsl = config('services.keepersport_graphql.verify_ssl', true);
        if (is_string($verifySsl)) {
            $parsed = filter_var($verifySsl, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $verifySsl = $parsed ?? true;
        }

        return $verifySsl === false ? ['verify' => false] : [];
    }

    /**
     * @param  list<array<string, mixed>>  $errors
     */
    private function errorsAreStockOnly(array $errors): bool
    {
        foreach ($errors as $error) {
            $path = implode('.', (array) ($error['path'] ?? []));
            $message = (string) ($error['message'] ?? '');
            $stockish = str_contains($path, 'stock')
                || str_contains($message, 'stock')
                || str_contains($message, 'notAuthenticated');
            if (! $stockish) {
                return false;
            }
        }

        return $errors !== [];
    }

    /**
     * @param  list<array<string, mixed>>  $errors
     * @return list<array<string, mixed>>
     */
    private function filterNonStockErrors(array $errors): array
    {
        return array_values(array_filter($errors, function (array $error) {
            $path = implode('.', (array) ($error['path'] ?? []));
            $message = (string) ($error['message'] ?? '');

            return ! str_contains($path, 'stock')
                && ! str_contains($message, 'stock')
                && ! str_contains($message, 'notAuthenticated');
        }));
    }
}
