<?php

namespace App\Services;

use App\Models\Setting;

class WebsitePricingService
{
    private ?Setting $setting = null;

    public function vatPercent(): float
    {
        return max(0, (float) ($this->setting()->vat ?? 0));
    }

    public function eurToSarRate(): float
    {
        return max(0, (float) ($this->setting()->eur_to_sar_rate ?? 0));
    }

    public function displayCurrency(): string
    {
        return 'SAR';
    }

    public function transformAmount(?float $amount, string $sourceCurrency = 'EUR'): ?float
    {
        if ($amount === null) {
            return null;
        }

        $value = (float) $amount;
        if (strtoupper($sourceCurrency) === 'EUR') {
            $rate = $this->eurToSarRate();
            if ($rate > 0) {
                $value *= $rate;
            }
        }

        $value *= 1 + ($this->vatPercent() / 100);

        return round($value, 2);
    }

    public function transformPrice(array $price): array
    {
        $sourceCurrency = strtoupper((string) ($price['currency'] ?? 'EUR'));
        $displayCurrency = $this->displayCurrency();

        if ($sourceCurrency === $displayCurrency) {
            return [
                'current' => (float) ($price['current'] ?? 0),
                'old' => isset($price['old']) && $price['old'] !== null
                    ? (float) $price['old']
                    : null,
                'currency' => $displayCurrency,
            ];
        }

        return [
            'current' => $this->transformAmount(
                isset($price['current']) ? (float) $price['current'] : 0.0,
                $sourceCurrency
            ) ?? 0.0,
            'old' => isset($price['old']) && $price['old'] !== null
                ? $this->transformAmount((float) $price['old'], $sourceCurrency)
                : null,
            'currency' => $displayCurrency,
        ];
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public function applyToProductCard(array $product): array
    {
        if (isset($product['price']) && is_array($product['price'])) {
            $product['price'] = $this->transformPrice($product['price']);
        }

        return $product;
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public function applyToProductDetails(array $product): array
    {
        $product = $this->applyToProductCard($product);

        if (isset($product['variants']) && is_array($product['variants'])) {
            $product['variants'] = array_map(function (array $variant) {
                $currency = (string) ($variant['currency'] ?? 'EUR');
                $amount = (float) ($variant['price'] ?? 0);
                $variant['price'] = $this->transformAmount($amount, $currency) ?? 0.0;
                $variant['currency'] = $this->displayCurrency();

                return $variant;
            }, $product['variants']);
        }

        if (isset($product['recommended_products']) && is_array($product['recommended_products'])) {
            $product['recommended_products'] = array_map(
                fn (array $row) => $this->applyToProductCard($row),
                $product['recommended_products']
            );
        }

        return $product;
    }

    /**
     * @param  list<array<string, mixed>>  $products
     * @return list<array<string, mixed>>
     */
    public function applyToProductCards(array $products): array
    {
        return array_map(fn (array $product) => $this->applyToProductCard($product), $products);
    }

    public function bumpPricingCacheRevision(): void
    {
        $key = 'keepersport.pricing_revision';
        $current = (int) $this->cache()->get($key, 0);
        $this->cache()->forever($key, $current + 1);
    }

    public function pricingCacheSuffix(): string
    {
        return '.p'.(int) $this->cache()->get('keepersport.pricing_revision', 0);
    }

    private function cache(): \Illuminate\Contracts\Cache\Repository
    {
        $name = config('services.keepersport_graphql.cache_store');
        $trimmed = is_string($name) ? trim($name) : '';
        if ($trimmed !== '') {
            try {
                return \Illuminate\Support\Facades\Cache::store($trimmed);
            } catch (\Throwable) {
                return \Illuminate\Support\Facades\Cache::store();
            }
        }

        return \Illuminate\Support\Facades\Cache::store();
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function transformSliderProductRow(array $row): array
    {
        $currency = strtoupper(trim((string) ($row['currency'] ?? 'EUR')));
        if ($currency === '') {
            $currency = 'EUR';
        }

        // إذا كانت الأسعار أصلاً بعملة العرض (SAR) لا نُعيد التحويل/الضريبة
        if ($currency === $this->displayCurrency()) {
            return [
                ...$row,
                'price_current' => (float) ($row['price_current'] ?? 0),
                'price_old' => isset($row['price_old']) && $row['price_old'] !== null
                    ? (float) $row['price_old']
                    : null,
                'currency' => $this->displayCurrency(),
            ];
        }

        $row['price_current'] = $this->transformAmount(
            (float) ($row['price_current'] ?? 0),
            $currency
        ) ?? 0.0;
        $row['price_old'] = isset($row['price_old']) && $row['price_old'] !== null
            ? $this->transformAmount((float) $row['price_old'], $currency)
            : null;
        $row['currency'] = $this->displayCurrency();

        return $row;
    }

    private function setting(): Setting
    {
        if ($this->setting === null) {
            $this->setting = Setting::query()->find(1) ?? new Setting();
        }

        return $this->setting;
    }
}
