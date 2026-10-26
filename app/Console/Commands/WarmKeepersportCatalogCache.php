<?php

namespace App\Console\Commands;

use App\Services\KeeperSportCatalogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WarmKeepersportCatalogCache extends Command
{
    protected $signature = 'keepersport:warm-catalog-cache
                            {--locale= : Accept-Language واحد (مثلاً en أو ar)}
                            {--skip-nav-counts : تخطّي أعداد القائمة الجانبية (أسرع)}
                            {--skip-products : تخطّي تخزين صفحات المنتجات}
                            {--products-limit= : أقصى عدد صفحات منتج للتخزين (افتراضي من الإعدادات)}
                            {--all-products : تسخين كل المنتجات المتاحة (حتى 5000)}
                            {--warm-recommended : تخزين المنتجات الموصى بها أيضاً (أبطأ)}
                            {--memory= : حد ذاكرة PHP بالميغابايت (افتراضي من الإعدادات)}';

    protected $description = 'سحب كتالوج Keepersport وتخزين التصنيفات وصفحات المنتجات في الكاش';

    public function handle(KeeperSportCatalogService $catalog): int
    {
        $memoryMb = (int) ($this->option('memory') ?: config('services.keepersport_graphql.warm_catalog_memory_mb', 512));
        $memoryMb = max(128, min(2048, $memoryMb));
        ini_set('memory_limit', "{$memoryMb}M");

        $logDir = storage_path('logs');
        if (! is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $locales = $this->resolveLocales();
        $skipNav = (bool) $this->option('skip-nav-counts');
        $skipProducts = (bool) $this->option('skip-products');
        $warmRecommended = (bool) $this->option('warm-recommended')
            || (bool) config('services.keepersport_graphql.warm_product_recommended', false);
        $productsLimit = $this->option('products-limit');
        $productsLimit = $productsLimit !== null && $productsLimit !== ''
            ? max(0, (int) $productsLimit)
            : null;
        if ((bool) $this->option('all-products')) {
            $productsLimit = 5000;
        }
        $started = microtime(true);

        $this->info('Keepersport catalog cache warm — locales: '.implode(', ', $locales));
        if (! $skipProducts) {
            $defaultLimit = (int) config('services.keepersport_graphql.warm_products_limit', 800);
            $limitLabel = $productsLimit ?? $defaultLimit;
            $this->line("  Newest product pages: up to {$limitLabel} slug(s) per locale (+ stock cache)");
            if ($warmRecommended) {
                $this->line('  Recommended products: enabled (slower)');
            }
        }
        $this->safeLog('info', 'keepersport:warm-catalog-cache started', [
            'locales' => $locales,
            'memory_mb' => $memoryMb,
            'skip_nav_counts' => $skipNav,
            'skip_products' => $skipProducts,
            'products_limit' => $productsLimit,
            'warm_recommended' => $warmRecommended,
        ]);

        foreach ($locales as $locale) {
            $localeStarted = microtime(true);
            $this->line("→ Warming locale: {$locale}");

            try {
                $warmResult = $catalog->warmPublicCatalogCache(
                    $locale,
                    $skipNav,
                    ! $skipProducts,
                    $productsLimit,
                    $warmRecommended
                );
                $secs = round(microtime(true) - $localeStarted, 1);
                $this->info("  ✓ {$locale} listings done ({$secs}s)");

                $productWarm = $warmResult['product_warm'] ?? null;
                if (is_array($productWarm)) {
                    $this->info(sprintf(
                        '  ✓ %s newest products: %d selected, %d newly cached, %d already present (scanned %d nodes)',
                        $locale,
                        (int) ($productWarm['selected'] ?? 0),
                        (int) ($productWarm['warmed'] ?? 0),
                        (int) ($productWarm['skipped'] ?? 0),
                        (int) ($productWarm['scanned'] ?? 0)
                    ));
                }
            } catch (\Throwable $e) {
                $this->error("  ✗ {$locale} failed: {$e->getMessage()}");
                $this->safeLog('error', 'keepersport:warm-catalog-cache locale failed', [
                    'locale' => $locale,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $totalSecs = round(microtime(true) - $started, 1);
        $this->info("Finished in {$totalSecs}s");
        $this->safeLog('info', 'keepersport:warm-catalog-cache finished', ['seconds' => $totalSecs]);

        return self::SUCCESS;
    }

    /**
     * لا تُفشّل التسخين إذا كانت صلاحيات storage/logs خاطئة.
     *
     * @param  array<string, mixed>  $context
     */
    private function safeLog(string $level, string $message, array $context = []): void
    {
        try {
            Log::{$level}($message, $context);
        } catch (\Throwable $e) {
            $this->warn('Log unavailable: '.$e->getMessage());
        }
    }

    /**
     * @return list<string>
     */
    private function resolveLocales(): array
    {
        $explicit = trim((string) $this->option('locale'));
        if ($explicit !== '') {
            return [$explicit];
        }

        $raw = (string) config('services.keepersport_graphql.warm_catalog_locales', '');
        $locales = array_values(array_filter(array_map('trim', explode(',', $raw))));
        if ($locales !== []) {
            return $locales;
        }

        return [(string) config('services.keepersport_graphql.catalog_accept_language', 'en')];
    }
}
