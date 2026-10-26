<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\KeeperSportCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CatalogController extends Controller
{
    public function __construct(private readonly KeeperSportCatalogService $catalog)
    {
    }

    public function categories(Request $request): JsonResponse
    {
        @set_time_limit(0);
        $this->resolveLocale($request);
        $catalogLocale = (string) $request->input(
            'catalog_locale',
            config('services.keepersport_graphql.catalog_accept_language', 'it')
        );

        try {
            return $this->catalogJsonCacheOk(
                [
                    'status' => true,
                    'meta' => [
                        'catalog_locale' => $catalogLocale,
                    ],
                    'categories' => $this->catalog->categories($catalogLocale),
                ]
            );
        } catch (Throwable $e) {
            Log::error('catalog.categories failed', ['exception' => $e]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'meta' => [
                    'catalog_locale' => $catalogLocale,
                ],
                'categories' => [],
            ], 503);
        }
    }

    /**
     * تصنيفات + منتجات في طلب HTTP واحد: جلب الكتالوج من upstream مرة واحدة فقط عند غياب الكاش.
     */
    public function shopListing(Request $request): JsonResponse
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        $this->resolveLocale($request);
        $catalogLocale = (string) $request->input(
            'catalog_locale',
            config('services.keepersport_graphql.catalog_accept_language', 'it')
        );

        $filters = $request->only([
            'q',
            'category',
            'brand',
            'size',
            'color',
            'sort',
            'page',
            'per_page',
            'teamsport_sub',
        ]);

        $sizes = $request->input('sizes');
        if (is_string($sizes) && $sizes !== '') {
            $sizes = array_values(array_filter(array_map('trim', explode(',', $sizes))));
        }
        if (is_array($sizes) && $sizes !== []) {
            $filters['sizes'] = $sizes;
        }

        $attrs = $request->input('attrs');
        if (is_string($attrs) && $attrs !== '') {
            $attrs = array_values(array_filter(array_map('trim', explode(',', $attrs))));
        }
        if (is_array($attrs) && $attrs !== []) {
            $filters['attrs'] = $attrs;
        }

        $brands = $this->parseListFilterInput($request->input('brands'));
        if ($brands !== []) {
            $filters['brands'] = $brands;
        }

        $colors = $this->parseListFilterInput($request->input('colors'));
        if ($colors !== []) {
            $filters['colors'] = $colors;
        }

        try {
            $payload = $this->catalog->shopListing($filters, $catalogLocale);
            $innerMeta = $payload['meta'] ?? [];
            unset($payload['meta']);

            return $this->catalogJsonCacheOk([
                'status' => true,
                'meta' => array_merge($innerMeta, [
                    'catalog_locale' => $catalogLocale,
                ]),
                ...$payload,
            ]);
        } catch (Throwable $e) {
            Log::error('catalog.shop_listing failed', ['exception' => $e]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'meta' => [
                    'catalog_locale' => $catalogLocale,
                    'upstream_error' => true,
                ],
                'categories' => [],
                'items' => [],
                'facets' => [
                    'brands' => [],
                    'sizes' => [],
                    'colors' => [],
                    'attributes' => [],
                ],
                'pagination' => [
                    'page' => 1,
                    'per_page' => 48,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ], 503);
        }
    }

    public function products(Request $request): JsonResponse
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        $this->resolveLocale($request);
        $catalogLocale = (string) $request->input(
            'catalog_locale',
            config('services.keepersport_graphql.catalog_accept_language', 'it')
        );

        $filters = $request->only([
            'q',
            'category',
            'brand',
            'size',
            'color',
            'sort',
            'page',
            'per_page',
            'teamsport_sub',
        ]);

        $sizes = $request->input('sizes');
        if (is_string($sizes) && $sizes !== '') {
            $sizes = array_values(array_filter(array_map('trim', explode(',', $sizes))));
        }
        if (is_array($sizes) && $sizes !== []) {
            $filters['sizes'] = $sizes;
        }

        $attrs = $request->input('attrs');
        if (is_string($attrs) && $attrs !== '') {
            $attrs = array_values(array_filter(array_map('trim', explode(',', $attrs))));
        }
        if (is_array($attrs) && $attrs !== []) {
            $filters['attrs'] = $attrs;
        }

        $brands = $this->parseListFilterInput($request->input('brands'));
        if ($brands !== []) {
            $filters['brands'] = $brands;
        }

        $colors = $this->parseListFilterInput($request->input('colors'));
        if ($colors !== []) {
            $filters['colors'] = $colors;
        }

        try {
            $payload = $this->catalog->products($filters, $catalogLocale);
            $innerMeta = $payload['meta'] ?? [];
            unset($payload['meta']);

            return $this->catalogJsonCacheOk([
                'status' => true,
                'meta' => array_merge($innerMeta, [
                    'catalog_locale' => $catalogLocale,
                ]),
                ...$payload,
            ]);
        } catch (Throwable $e) {
            Log::error('catalog.products failed', ['exception' => $e]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'meta' => [
                    'catalog_locale' => $catalogLocale,
                    'upstream_error' => true,
                ],
                'items' => [],
                'facets' => [
                    'brands' => [],
                    'sizes' => [],
                    'colors' => [],
                    'attributes' => [],
                ],
                'pagination' => [
                    'page' => 1,
                    'per_page' => 48,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ], 503);
        }
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        @set_time_limit(0);
        $this->resolveLocale($request);
        $catalogLocale = (string) $request->input(
            'catalog_locale',
            config('services.keepersport_graphql.catalog_accept_language', 'it')
        );

        try {
            $product = $this->catalog->productBySlug($slug, $catalogLocale);
        } catch (Throwable $e) {
            Log::error('catalog.show failed', ['slug' => $slug, 'exception' => $e]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 503);
        }

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return $this->catalogJsonCacheOk([
            'status' => true,
            'product' => $product,
        ], 200, $this->resolveProductHttpMaxAge());
    }

    public function recommended(Request $request, string $slug): JsonResponse
    {
        @set_time_limit(0);
        $this->resolveLocale($request);
        $catalogLocale = (string) $request->input(
            'catalog_locale',
            config('services.keepersport_graphql.catalog_accept_language', 'it')
        );

        try {
            $items = $this->catalog->recommendedProductsBySlug($slug, $catalogLocale);
        } catch (Throwable $e) {
            Log::error('catalog.recommended failed', ['slug' => $slug, 'exception' => $e]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'items' => [],
            ], 503);
        }

        $ttl = max(60, (int) config('services.keepersport_graphql.recommended_cache_ttl', 3600));

        return $this->catalogJsonCacheOk([
            'status' => true,
            'items' => $items,
        ], 200, min(900, $ttl));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function catalogJsonCacheOk(array $data, int $status = 200, ?int $maxAgeSeconds = null): JsonResponse
    {
        $cap = max(60, (int) config('services.keepersport_graphql.http_catalog_max_age_cap', 86400));

        if ($maxAgeSeconds !== null) {
            $maxAge = max(15, min($cap, $maxAgeSeconds));
        } else {
            $configured = (int) config('services.keepersport_graphql.http_catalog_max_age', 0);
            $fromServer = max(60, (int) config('services.keepersport_graphql.products_cache_ttl', 1800));
            $maxAge = $configured > 0 ? $configured : $fromServer;
            $maxAge = max(15, min($cap, $maxAge));
        }

        $swr = (int) config('services.keepersport_graphql.http_stale_while_revalidate', 0);
        $cc = 'public, max-age='.$maxAge;
        if ($swr > 0) {
            $cc .= ', stale-while-revalidate='.$swr;
        }

        return response()
            ->json($data, $status)
            ->header('Cache-Control', $cc);
    }

    private function resolveProductHttpMaxAge(): int
    {
        $cap = max(60, (int) config('services.keepersport_graphql.http_catalog_max_age_cap', 86400));
        $configured = (int) config('services.keepersport_graphql.http_product_max_age', 0);
        $slugTtl = max(120, (int) config('services.keepersport_graphql.product_slug_cache_ttl', 7200));
        $maxAge = $configured > 0 ? $configured : min(900, $slugTtl);

        return max(15, min($cap, $maxAge));
    }

    /**
     * @return list<string>
     */
    private function parseListFilterInput(mixed $input): array
    {
        if (is_string($input) && $input !== '') {
            $input = array_values(array_filter(array_map('trim', explode(',', $input))));
        }
        if (! is_array($input) || $input === []) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($value) => trim((string) $value),
            $input
        )));
    }

    private function resolveLocale(Request $request): string
    {
        $locale = (string) ($request->route('locale') ?? $request->header('Accept-Language', 'en'));
        return $locale === 'ar' ? 'ar' : 'en';
    }
}

