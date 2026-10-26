<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LandingSliderProductsService
{
    protected function storageFileName(): string
    {
        return 'landing_slider_products.json';
    }

    private function filePath(): string
    {
        return storage_path('app/'.$this->storageFileName());
    }

    /**
     * @return array{cta_link: string, products: array<int, array<string, mixed>>}
     */
    protected function readStorage(): array
    {
        $path = $this->filePath();
        if (! File::exists($path)) {
            return ['cta_link' => '', 'products' => []];
        }

        $decoded = json_decode((string) File::get($path), true);
        if (! is_array($decoded)) {
            return ['cta_link' => '', 'products' => []];
        }

        if (array_is_list($decoded)) {
            return [
                'cta_link' => '',
                'products' => $this->normalizeProductList($decoded),
            ];
        }

        return [
            'cta_link' => trim((string) ($decoded['cta_link'] ?? '')),
            'products' => $this->normalizeProductList(
                is_array($decoded['products'] ?? null) ? $decoded['products'] : []
            ),
        ];
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeProductList(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $normalized = $this->normalizeItem($row);
            if ($normalized !== null) {
                $items[] = $normalized;
            }
        }

        return $items;
    }

    public function getCtaLink(): string
    {
        return $this->readStorage()['cta_link'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        return $this->hydrateProductsForDisplay($this->readStorage()['products']);
    }

    /**
     * @return array{cta_link: string, products: array<int, array<string, mixed>>}
     */
    public function getPayload(): array
    {
        $data = $this->readStorage();

        return [
            'cta_link' => $data['cta_link'],
            'products' => $this->hydrateProductsForDisplay($data['products']),
        ];
    }

    /**
     * يستبدل أسعار التخزين (قد تكون قديمة/EUR) بسعر الكatalog الجاهز ليطابق صفحة المنتج.
     *
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, array<string, mixed>>
     */
    private function hydrateProductsForDisplay(array $products): array
    {
        $pricing = app(WebsitePricingService::class);
        $catalog = app(KeeperSportCatalogService::class);

        return array_map(function (array $row) use ($pricing, $catalog) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug !== '') {
                $live = $catalog->displayPriceForSlug($slug);
                if (is_array($live) && (float) ($live['current'] ?? 0) > 0) {
                    $row['price_current'] = (float) $live['current'];
                    $row['price_old'] = isset($live['old']) && $live['old'] !== null
                        ? (float) $live['old']
                        : null;
                    $row['currency'] = (string) ($live['currency'] ?? $pricing->displayCurrency());
                }
            }

            return $pricing->transformSliderProductRow($row);
        }, $products);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function saveAll(array $items, ?string $ctaLink = null): array
    {
        $current = $this->readStorage();
        $normalized = $this->normalizeProductList($items);
        $next = [
            'cta_link' => $ctaLink !== null
                ? trim($ctaLink)
                : $current['cta_link'],
            'products' => $normalized,
        ];

        File::ensureDirectoryExists(dirname($this->filePath()));
        File::put(
            $this->filePath(),
            json_encode($next, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>|null
     */
    private function normalizeItem(array $row): ?array
    {
        $sku = trim((string) ($row['sku'] ?? ''));
        $slug = trim((string) ($row['slug'] ?? ''));
        if (str_ends_with(Str::lower($slug), '.html')) {
            $slug = substr($slug, 0, -5);
        }
        $name = trim((string) ($row['name'] ?? ''));
        $image = trim((string) ($row['image'] ?? ''));

        if ($sku === '' || $slug === '' || $name === '' || $image === '') {
            return null;
        }

        $priceCurrent = (float) ($row['price_current'] ?? $row['priceNow'] ?? 0);
        $priceOldRaw = $row['price_old'] ?? $row['priceWas'] ?? null;
        $priceOld = $priceOldRaw !== null ? (float) $priceOldRaw : null;
        if ($priceOld !== null && $priceOld <= 0) {
            $priceOld = null;
        }

        $currency = strtoupper(trim((string) ($row['currency'] ?? 'EUR')));
        if ($currency === '') {
            $currency = 'EUR';
        }

        $brandRaw = $row['brand'] ?? null;
        if (is_array($brandRaw)) {
            $brand = trim((string) ($brandRaw['name'] ?? $brandRaw['id'] ?? ''));
        } else {
            $brand = trim((string) ($brandRaw ?? ''));
        }
        if ($brand === '') {
            $brand = 'KEEPERsport';
        }

        return [
            'sku' => $sku,
            'slug' => Str::lower($slug),
            'name' => $name,
            'image' => $image,
            'brand' => $brand,
            'price_current' => $priceCurrent,
            'price_old' => $priceOld,
            'currency' => $currency,
        ];
    }
}
