<?php

namespace App\Console\Commands;

use App\Services\KeeperSportCatalogService;
use Illuminate\Console\Command;

class KeepersportNavCountsCommand extends Command
{
    protected $signature = 'keepersport:nav-counts {--locale= : Accept-Language للـ GraphQL (مثلاً it أو en)}';

    protected $description = 'طباعة أعداد المنتجات لكل فرع في القائمة (نفس منطق المتجر) لمقارنتها مع keepersport.net';

    public function handle(KeeperSportCatalogService $catalog): int
    {
        $locale = trim((string) $this->option('locale'));
        if ($locale === '') {
            $locale = (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        }

        $this->info("Locale: {$locale}");
        $payload = [
            'shop_nav_leaf_counts' => $catalog->shopNavCategoryProductCounts($locale),
            'gk_apparel_leaf_counts' => $catalog->goalkeeperApparelLeafProductCounts($locale),
        ];
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
