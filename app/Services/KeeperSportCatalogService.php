<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Contracts\Cache\LockTimeoutException;

class KeeperSportCatalogService
{
    private const PER_PAGE = 48;

    /**
     * مسارات تصنيف مثل keepersport.it → مطابقة slug/id من الـ API (غالبًا إنجليزي).
     *
     * @var array<string, list<string>>
     */
    private const CATEGORY_SLUG_ALIASES = [
        'goalkeeper-gloves' => [
            'goalkeeper-gloves',
            'goalkeeper-glove',
            'guanti-da-portiere',
            'torwarthandschuhe',
            'gloves',
        ],
        'guanti-da-portiere' => [
            'goalkeeper-gloves',
            'goalkeeper-glove',
            'guanti-da-portiere',
            'torwarthandschuhe',
            'gloves',
        ],
        'football-boots' => [
            'football-boots',
            'scarpe-da-calcio',
            'fussballschuhe',
        ],
        'scarpe-da-calcio' => [
            'football-boots',
            'scarpe-da-calcio',
            'fussballschuhe',
        ],
        'goalkeeper-apparel' => [
            'goalkeeper-apparel',
            'goalkeeper-clothing',
            'abbigliamento-da-portiere',
            'torwartbekleidung',
        ],
        'abbigliamento-da-portiere' => [
            'goalkeeper-apparel',
            'goalkeeper-clothing',
            'abbigliamento-da-portiere',
            'torwartbekleidung',
        ],
        'goalkeeper-equipment' => [
            'goalkeeper-equipment',
            'equipaggiamento-per-portiere',
            'torwartausruestung',
        ],
        'equipaggiamento-per-portiere' => [
            'goalkeeper-equipment',
            'equipaggiamento-per-portiere',
            'torwartausruestung',
        ],
        'abbigliamento' => [
            'apparel',
            'abbigliamento',
            'goalkeeper-apparel',
            'goalkeeper-clothing',
            'abbigliamento-da-portiere',
            'torwartbekleidung',
        ],
        'teamsport' => [
            'teamsport',
            'teamwear',
            'jerseys',
            'shorts',
            'long-pants',
            'tracksuits',
            'sweatshirts',
            'jackets',
            'polo-shirts',
            'tank-tops',
            'training-tops',
            'short-sleeve',
            'sport-bras',
            'boxer-shorts',
            'underpants',
            'knee-high-socks',
            'indoor-clothing',
            'indoor-shoes',
            't-shirt',
            't-shirts',
            'team-bag',
        ],
        'sale' => [
            'sale',
            'outlet',
            'sconti',
        ],
        'saldi' => [
            'sale',
            'saldi',
            'outlet',
            'sconti',
        ],
    ];

    /**
     * أوراق أحذية الكرة في المتجر — تُحلّ إلى categoryIds من GraphQL (كما في GraphQLService عبر شجرة التصنيفات الحقيقية).
     *
     * @var list<string>
     */
    private const FOOTBALL_BOOT_SHOP_LEAF_SLUGS = [
        'hard-court',
        'firm-ground',
        'soft-ground',
        'artificial-grass',
        'turf',
        'multi-ground',
        'street',
        'cleats',
        'accessories',
        'kids-football-boots',
        'leisure-kids',
        'indoor',
        'sandals',
    ];

    /** مسارات /shop/{brand}-goalkeeper-gloves — نفس mobileNavTree.js */
    private const GOALKEEPER_GLOVES_BRAND_SHOP_SLUGS = [
        'uhlsport-goalkeeper-gloves',
        'adidas-goalkeeper-gloves',
        'reusch-goalkeeper-gloves',
        'puma-goalkeeper-gloves',
        'keepersport-goalkeeper-gloves',
        'elite-sport-goalkeeper-gloves',
        'rehab-goalkeeper-gloves',
        'erima-goalkeeper-gloves',
        'packs-sets-goalkeeper-gloves',
        'nike-goalkeeper-gloves',
        'gloveglu-goalkeeper-gloves',
        'ho-soccer-goalkeeper-gloves',
        'jako-goalkeeper-gloves',
    ];

    /**
     * تصنيفات فرعية لملابس الحارس (نفس تجميع keepersport.net) — تُمرَّر كـ categoryIds إلى GraphQL.
     *
     * @var array<string, list<string>>
     */
    private const GOALKEEPER_APPAREL_LEAF_API_IDS = [
        'goalkeeper-underwear' => [
            '20718792',
            '20718793',
        ],
        'goalkeeper-pants' => [
            '18',
        ],
        'goalkeeper-jerseys' => [
            '21',
        ],
        'goalkeeper-set' => [
            '20716001',
        ],
        'goalkeeper-socks' => [
            '20716003',
        ],
        'goalkeeper-training-tops' => [
            '20719075',
        ],
        'goalkeeper-undershirts' => [
            '20718792',
        ],
        'goalkeeper-undershorts' => [
            '20718793',
        ],
    ];

    /**
     * أوراق معدات الحارس (نفس keepersport.net) — تُمرَّر كـ categoryIds إلى GraphQL.
     * بدون هذا: /shop/shin-pads وغيرها ترجع 0 منتجات لأن slug غير مربوط بـ API.
     *
     * @var array<string, list<string>>
     */
    /**
     * أوراق معدات الحارس — IDs من keepersport.net (data-query-id) بتاريخ المراجعة.
     * GK-Bag = 20716010 (ليس Team-Bag 20716011).
     *
     * @var array<string, list<string>>
     */
    private const GOALKEEPER_EQUIPMENT_LEAF_API_IDS = [
        // Goalkeeper glove care
        'goalkeeper-glove-care' => ['20716030'],
        'glove-care' => ['20716030'],
        'goalkeeper-gloves-spray' => ['20716030'],
        'equipment-goalkeeper-glove-care' => ['20716030'],
        'glove-wash' => ['6885'],
        'equipment-goalkeeper-glove-care-glove-wash' => ['6885'],
        'care' => ['20716031'],
        'equipment-goalkeeper-glove-care-care' => ['20716031'],
        'grip-enhancer' => ['20716398'],
        'equipment-goalkeeper-glove-care-grip-enhancer' => ['20716398'],
        'towels' => ['20716016'],
        'equipment-miscellaneous-towels' => ['20716016'],
        // Bags
        'gk-bag' => ['20716010'],
        'equipment-bags-gk-bag' => ['20716010'],
        'team-bag' => ['20716011'],
        'equipment-bags-team-bag' => ['20716011'],
        'equipment-bags' => ['20716010', '20716011'],
        // Protection
        'protection' => ['20716022'],
        'equipment-protection' => ['20716022'],
        'elbow-pads' => ['88'],
        'equipment-protection-elbow-pads' => ['88'],
        'shin-pads' => ['89'],
        'equipment-protection-shin-pads' => ['89'],
        'knee-pads' => ['90'],
        'goalkeeper-knee-pads' => ['90'],
        'helmets' => ['10004'],
        'goalkeeper-helmet' => ['10004'],
        'mouth' => ['20716024'],
        'equipment-protection-mouth' => ['20716024'],
        'genital' => ['20716026'],
        'equipment-protection-genital' => ['20716026'],
        // Training equipment
        'training-equipment' => ['20716014'],
        'equipment-training-equipment' => ['20716014'],
        'training-aids' => ['20716015'],
        'goalkeeper-training-accessories' => ['20716015'],
        'water-bottles' => ['8711'],
        'equipment-training-equipment-water-bottles' => ['8711'],
        'studs' => ['10008'],
        'equipment-training-equipment-studs' => ['10008'],
        // Medicine / Tapes
        'medicine-tapes' => ['20716029'],
        'equipment-medicine-tapes' => ['20716029'],
        'bandages' => ['96'],
        'equipment-medicine-tapes-bandages' => ['96'],
        'cooling' => ['97'],
        'equipment-medicine-tapes-cooling' => ['97'],
        'tapes-pavings' => ['98'],
        'tapes' => ['98'],
        'equipment-medicine-tapes-tapes-pavings' => ['98'],
        // Misc / under gloves / multimedia
        'key-rings' => ['10003'],
        'equipment-miscellaneous-key-rings' => ['10003'],
        'under-gloves' => ['20716020'],
        'equipment-gloves-under-gloves' => ['20716020'],
        'multimedia' => ['20716017'],
        'equipment-miscellaneous-multimedia' => ['20716017'],
        'online-goalkeeper-training' => ['5267'],
        'dvd-s' => ['5267'],
        'equipment-miscellaneous-multimedia-dvd-s' => ['5267'],
        'books' => ['20716018'],
        'equipment-miscellaneous-multimedia-books' => ['20716018'],
        'recovery' => ['20719231'],
        'equipment-recovery' => ['20719231'],
        'equipment-footballs' => ['5269'],
        'neckwarmer' => ['20716007'],
        'headband' => ['20716008'],
        'sleeves' => ['20716005'],
    ];

    /** أوراق معدات — تُحل عبر GOALKEEPER_EQUIPMENT_LEAF_API_IDS أو فهرس slug من الجذر */
    private const GOALKEEPER_EQUIPMENT_SHOP_LEAF_SLUGS = [
        'goalkeeper-glove-care',
        'glove-care',
        'goalkeeper-gloves-spray',
        'equipment-goalkeeper-glove-care',
        'glove-wash',
        'equipment-goalkeeper-glove-care-glove-wash',
        'care',
        'equipment-goalkeeper-glove-care-care',
        'grip-enhancer',
        'equipment-goalkeeper-glove-care-grip-enhancer',
        'towels',
        'equipment-miscellaneous-towels',
        'gk-bag',
        'equipment-bags-gk-bag',
        'team-bag',
        'equipment-bags-team-bag',
        'equipment-bags',
        'protection',
        'equipment-protection',
        'elbow-pads',
        'equipment-protection-elbow-pads',
        'shin-pads',
        'equipment-protection-shin-pads',
        'knee-pads',
        'goalkeeper-knee-pads',
        'helmets',
        'goalkeeper-helmet',
        'mouth',
        'equipment-protection-mouth',
        'genital',
        'equipment-protection-genital',
        'training-equipment',
        'equipment-training-equipment',
        'training-aids',
        'goalkeeper-training-accessories',
        'water-bottles',
        'equipment-training-equipment-water-bottles',
        'studs',
        'equipment-training-equipment-studs',
        'medicine-tapes',
        'equipment-medicine-tapes',
        'tapes-pavings',
        'tapes',
        'equipment-medicine-tapes-tapes-pavings',
        'bandages',
        'equipment-medicine-tapes-bandages',
        'cooling',
        'equipment-medicine-tapes-cooling',
        'key-rings',
        'equipment-miscellaneous-key-rings',
        'under-gloves',
        'equipment-gloves-under-gloves',
        'multimedia',
        'equipment-miscellaneous-multimedia',
        'online-goalkeeper-training',
        'dvd-s',
        'equipment-miscellaneous-multimedia-dvd-s',
        'books',
        'equipment-miscellaneous-multimedia-books',
        'recovery',
        'equipment-recovery',
        'equipment-footballs',
        'neckwarmer',
        'headband',
        'sleeves',
    ];

    /**
     * قواعد تضييق إضافية لكل تصنيف رئيسي حتى لا تختلط النتائج بين الأقسام.
     *
     * @var array<string, list<string>>
     */
    private const MAIN_CATEGORY_KEYWORDS = [
        'goalkeeper-gloves' => ['glove', 'guanti', 'handschuh', 'handschuhe'],
        'guanti-da-portiere' => ['glove', 'guanti', 'handschuh', 'handschuhe'],
        'football-boots' => ['boot', 'boots', 'scarpe', 'fussballschuhe'],
        'scarpe-da-calcio' => ['boot', 'boots', 'scarpe', 'fussballschuhe'],
        'goalkeeper-apparel' => [
            'goalkeeper jersey', 'goalkeeper pant', 'goalkeeper sock', 'goalkeeper under',
            'goalkeeper training', 'goalkeeper shirt', 'goalkeeper bib', 'goalkeeper set',
            'torwartbekleidung', 'abbigliamento da portiere', 'torwart shirt', 'torwart hose',
        ],
        'abbigliamento-da-portiere' => [
            'goalkeeper jersey', 'goalkeeper pant', 'goalkeeper sock', 'goalkeeper under',
            'goalkeeper training', 'goalkeeper shirt', 'goalkeeper bib', 'goalkeeper set',
            'torwartbekleidung', 'abbigliamento da portiere', 'torwart shirt', 'torwart hose',
        ],
        'goalkeeper-equipment' => ['equipment', 'equipaggiamento', 'accessories', 'bag', 'ball'],
        'equipaggiamento-per-portiere' => ['equipment', 'equipaggiamento', 'accessories', 'bag', 'ball'],
        'teamsport' => ['teamsport', 'teamwear', 'jersey', 'tracksuits', 'polo shirt', 'tank top', 'training top', 'sweatshirt', 'indoor'],
        'sale' => ['sale', 'outlet', 'discount', 'sconti', 'angebot'],
        'saldi' => ['sale', 'outlet', 'discount', 'sconti', 'angebot'],
    ];

    /**
     * خريطة التصنيفات الرئيسية → معرّفات التصنيفات في API الـ GraphQL.
     * تُستخدم لتمرير categoryIds مباشرة إلى GraphQL بدلاً من جلب كل الكتالوج.
     */
    private const CATEGORY_API_IDS = [
        'football-boots' => [
            '54', // Football Boots
        ],
        'scarpe-da-calcio' => [
            '54', // Football Boots
        ],
        /** جذر قفازات الحارس في GraphQL */
        'goalkeeper-gloves' => [
            '17',
        ],
        'guanti-da-portiere' => [
            '17',
        ],
        /** ملابس حارس المرمى — نفس مجموعات keepersport (جيرسي، بنطلون، مجموعات، جوارب، تدريب، داخلية) */
        'goalkeeper-apparel' => [
            '21',       // Goalkeeper jerseys
            '18',       // Goalkeeper pants
            '20716001', // Goalkeeper set
            '20716003', // Goalkeeper socks
            '20719075', // Goalkeeper training tops
            '20718792', // Goalkeeper undershirts
            '20718793', // Goalkeeper undershorts
        ],
        'abbigliamento-da-portiere' => [
            '21',
            '18',
            '20716001',
            '20716003',
            '20719075',
            '20718792',
            '20718793',
        ],
        /** معدات الحارس — جذر keepersport.net + فروع رئيسية (انظر GOALKEEPER_EQUIPMENT_LEAF_API_IDS) */
        'goalkeeper-equipment' => [
            '20719246', // Goalkeeper equipment (root)
            '20716030', // Goalkeeper glove care
            '20716010', // GK-Bag
            '20716011', // Team-Bag
            '20716022', // Protection
            '20716014', // Training equipment
            '20716015', // Training aids
            '20716029', // Medicine / Tapes
            '10003',    // Key Rings
            '20716020', // Under gloves
            '20716017', // Multimedia
            '20719231', // Recovery
            '5269',     // Footballs
            '88', '89', '90', '10004', '20716024', '20716026',
            '8711', '10008',
            '96', '97', '98',
            '6885', '20716031', '20716398', '20716016',
            '5267', '20716018',
            '20716007', '20716008', '20716005',
        ],
        'equipaggiamento-per-portiere' => [
            '20719246',
            '20716030',
            '20716010',
            '20716011',
            '20716022',
            '20716014',
            '20716015',
            '20716029',
            '10003',
            '20716020',
            '20716017',
            '20719231',
            '5269',
            '88', '89', '90', '10004', '20716024', '20716026',
            '8711', '10008',
            '96', '97', '98',
            '6885', '20716031', '20716398', '20716016',
            '5267', '20716018',
            '20716007', '20716008', '20716005',
        ],
        'teamsport' => [
            '20716402',  // Shorts
            '20716425',  // Underpants
            '20716427',  // Training tops
            '20716418',  // Knee-high socks
            '20716405',  // Jerseys
            '20716011',  // Team-Bag
            '20716406',  // Sweatshirts
            '20716401',  // Long pants
            '20716460',  // Long pants
            '20717764',  // Short pants
            '20716419',  // Short sleeve
            '20716426',  // T-Shirt
            '5272',      // T-Shirt
            '20716456',  // T-Shirts
            '20716442',  // T-Shirts
            '5269',      // Footballs
            '20716015',  // Training aids
            '20716004',  // Socks
            '20716403',  // Polo shirts
            '20716412',  // Sport Bras
            '5270',      // Functional Socks
            '20716455',  // Sweatshirts
            '20716399',  // Tracksuits
            '20716404',  // Tank tops
            '20716413',  // Boxer shorts
        ],
    ];

    /**
     * تصنيفات Teamsport الفرعية كما تظهر على keepersport.net مع IDs الـ API.
     * بنية 3 مستويات: parent → children (كل child له ids خاصة).
     */
    private const TEAMSPORT_SUBCATEGORIES = [
        'tops' => [
            'label' => 'Tops',
            'ids'   => ['20716405', '20716427', '20716403', '20716404', '20716419', '20716426', '5272', '20716456', '20716442', '20716455', '20716406'],
            'children' => [
                ['slug' => 'jerseys', 'label' => 'Jerseys', 'ids' => ['20716405']],
                ['slug' => 'training-tops', 'label' => 'Training tops', 'ids' => ['20716427']],
                ['slug' => 't-shirt', 'label' => 'T-Shirt', 'ids' => ['20716426', '5272', '20716456', '20716442']],
                ['slug' => 'tank-tops', 'label' => 'Tank tops', 'ids' => ['20716404']],
                ['slug' => 'jackets', 'label' => 'Jackets', 'ids' => ['20716455']],
                ['slug' => 'sweatshirts', 'label' => 'Sweatshirts', 'ids' => ['20716406']],
                ['slug' => 'polo-shirts', 'label' => 'Polo shirts', 'ids' => ['20716403']],
            ],
        ],
        'pants' => [
            'label' => 'Pants',
            'ids'   => ['20716402', '20716401', '20716460', '20717764'],
            'children' => [
                ['slug' => 'shorts', 'label' => 'Shorts', 'ids' => ['20716402']],
                ['slug' => 'long-pants', 'label' => 'Long pants', 'ids' => ['20716401', '20716460']],
                ['slug' => 'short-pants', 'label' => 'Short pants', 'ids' => ['20717764']],
            ],
        ],
        'underwear' => [
            'label' => 'Underwear',
            'ids'   => ['20716425', '20716413', '20716412'],
            'children' => [
                ['slug' => 'underpants', 'label' => 'Underpants', 'ids' => ['20716425']],
                ['slug' => 'boxer-shorts', 'label' => 'Boxer shorts', 'ids' => ['20716413']],
                ['slug' => 'sport-bras', 'label' => 'Sport Bras', 'ids' => ['20716412']],
            ],
        ],
        'tracksuits' => [
            'label' => 'Tracksuits',
            'ids'   => ['20716399'],
            'children' => [],
        ],
        'socks' => [
            'label' => 'Socks',
            'ids'   => ['20716418', '20716004', '5270'],
            'children' => [
                ['slug' => 'knee-high-socks', 'label' => 'Knee-high socks', 'ids' => ['20716418']],
                ['slug' => 'functional-socks', 'label' => 'Functional Socks', 'ids' => ['5270', '20716004']],
            ],
        ],
        'footballs' => [
            'label' => 'Footballs',
            'ids'   => ['5269'],
            'children' => [],
        ],
    ];

    private string $endpoint;
    private string $username;
    private string $password;
    private string $token;

    private ?KeepersportGraphqlCatalogClient $graphqlCatalogClient = null;

    public function __construct(?KeepersportGraphqlCatalogClient $graphqlCatalogClient = null)
    {
        $this->endpoint = (string) config('services.keepersport_graphql.endpoint', 'https://backend.keepersport.at/sa/graphql');
        $this->username = (string) config('services.keepersport_graphql.username', '');
        $this->password = (string) config('services.keepersport_graphql.password', '');
        $this->token = (string) config('services.keepersport_graphql.token', '');
        $this->graphqlCatalogClient = $graphqlCatalogClient;
    }

    private function graphqlCatalog(): KeepersportGraphqlCatalogClient
    {
        return $this->graphqlCatalogClient ??= app(KeepersportGraphqlCatalogClient::class);
    }

    /**
     * متجر كاش اختياري (مثلاً redis) لتخفيف ضغط جدول cache في قاعدة البيانات.
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    private function ksCache()
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

    private function catalogGraphStaleExpiresAt(): \Illuminate\Support\Carbon
    {
        $sec = max(3600, (int) config('services.keepersport_graphql.catalog_stale_ttl_seconds', 172800));

        return now()->addSeconds($sec);
    }

    private function productsListStaleExpiresAt(): \Illuminate\Support\Carbon
    {
        $sec = max(3600, (int) config('services.keepersport_graphql.products_list_stale_ttl_seconds', 172800));

        return now()->addSeconds($sec);
    }

    /** أثناء warm/إعادة البناء: لا تعتمد على stale — اجلب من upstream. */
    private bool $forceFreshCatalogFetch = false;

    /**
     * تسخين الكاش (تصنيفات + قوائم التصنيفات الرئيسية + الصفحة الرئيسية للكتالوج) — مفيد مع cron.
     *
     * @return array{product_warm?: array{warmed: int, skipped: int, scanned: int}}
     */
    public function warmPublicCatalogCache(
        ?string $graphLocale = null,
        bool $skipNavCounts = false,
        bool $warmProducts = true,
        ?int $productWarmLimit = null,
        bool $warmRecommended = false,
    ): array {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'en');
        $result = [];
        $warmTtl = max(3600, (int) config('services.keepersport_graphql.warm_cache_ttl', 172800));

        // مفاتيح الكاش التي ينشئها cron يجب ألا تنتهي بعد دقائق حسب إعدادات الطلبات العادية.
        config()->set(
            'services.keepersport_graphql.catalog_cache_ttl',
            max($warmTtl, (int) config('services.keepersport_graphql.catalog_cache_ttl', 86400))
        );
        config()->set(
            'services.keepersport_graphql.products_cache_ttl',
            max($warmTtl, (int) config('services.keepersport_graphql.products_cache_ttl', 86400))
        );
        config()->set(
            'services.keepersport_graphql.product_slug_cache_ttl',
            max($warmTtl, (int) config('services.keepersport_graphql.product_slug_cache_ttl', 86400))
        );

        $previousForceFresh = $this->forceFreshCatalogFetch;
        $this->forceFreshCatalogFetch = true;

        try {
            try {
                $this->categories($graphLocale);
            } catch (\Throwable $e) {
                Log::warning('Keepersport warm: categories failed', [
                    'locale' => $graphLocale,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                $this->categorySlugIdIndex($graphLocale);
            } catch (\Throwable $e) {
                Log::warning('Keepersport warm: boot slug index failed', [
                    'locale' => $graphLocale,
                    'error' => $e->getMessage(),
                ]);
            }

            $slugs = array_unique(array_merge(
                array_keys(self::CATEGORY_API_IDS),
                array_keys(self::GOALKEEPER_APPAREL_LEAF_API_IDS),
                array_keys(self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS),
                self::GOALKEEPER_EQUIPMENT_SHOP_LEAF_SLUGS,
                self::FOOTBALL_BOOT_SHOP_LEAF_SLUGS,
                self::GOALKEEPER_GLOVES_BRAND_SHOP_SLUGS,
                // التخفيضات ليست في CATEGORY_API_IDS — تُجلب عبر on_sale queryId
                ['sale'],
            ));
            // صفحتان لكل تصنيف: الصفحة 2 كانت باردة بعد انتهاء كاش الصفحة الأولى
            $warmPages = [1, 2];
            foreach ($slugs as $slug) {
                foreach ($warmPages as $page) {
                    try {
                        $this->shopListing([
                            'category' => $slug,
                            'page' => $page,
                            'per_page' => self::PER_PAGE,
                            'sort' => 'newest',
                        ], $graphLocale);
                    } catch (\Throwable $e) {
                        Log::warning('Keepersport warm: shopListing failed', [
                            'locale' => $graphLocale,
                            'category' => $slug,
                            'page' => $page,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            try {
                $this->shopListing([
                    'page' => 1,
                    'per_page' => self::PER_PAGE,
                    'sort' => 'newest',
                ], $graphLocale);
            } catch (\Throwable $e) {
                Log::warning('Keepersport warm: shopListing (home) failed', [
                    'locale' => $graphLocale,
                    'error' => $e->getMessage(),
                ]);
            }

            if (! $skipNavCounts) {
                $this->warmNavCategoryCounts($graphLocale);
            }

            if ($warmProducts) {
                try {
                    $result['product_warm'] = $this->warmProductDetailsCache(
                        $graphLocale,
                        $productWarmLimit,
                        $warmRecommended
                    );
                } catch (\Throwable $e) {
                    Log::warning('Keepersport warm: product details failed', [
                        'locale' => $graphLocale,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } finally {
            $this->forceFreshCatalogFetch = $previousForceFresh;
        }

        return $result;
    }

    /**
     * تخزين أحدث صفحات المنتج (حسب slug) من الكتالogg — الأحدث أولاً حتى الحد.
     *
     * @return array{warmed: int, skipped: int, scanned: int, selected: int}
     */
    public function warmProductDetailsCache(
        ?string $graphLocale = null,
        ?int $limit = null,
        bool $warmRecommended = false,
    ): array {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'en');
        $limit = $limit ?? (int) config('services.keepersport_graphql.warm_products_limit', 800);
        $limit = max(0, min(5000, $limit));

        if ($limit === 0) {
            return ['warmed' => 0, 'skipped' => 0, 'scanned' => 0, 'selected' => 0];
        }

        // اجلب كتالوجًا يكفي لتغطية أحدث N منتج (لا نكتفي بترتيب GraphQL الخام)
        $scanLimit = max($this->defaultScanCap(), $limit);
        // products-limit كان يتوقف فعلياً عند catalog_max_pages (مثلاً 12×100=1200).
        // أثناء warm اسمح بعدد صفحات يكفي الحد المطلوب حتى تدخل المنتجات الأقدم في كاش PDP.
        $warmMaxPages = max($this->catalogMaxScanPages(), (int) ceil($scanLimit / 100));
        $nodes = $this->fetchCatalogEdges(
            $graphLocale,
            $scanLimit,
            [],
            null,
            $warmMaxPages
        );
        $hitTtl = max(120, (int) config('services.keepersport_graphql.product_slug_cache_ttl', 3600));
        $pricingSuffix = app(WebsitePricingService::class)->pricingCacheSuffix();

        $seen = [];
        $candidates = [];

        foreach ($nodes as $node) {
            if (! is_array($node) || ! $this->shouldExposeCatalogNode($node)) {
                continue;
            }

            $product = $this->mapProductDetails($node);
            if (($product['product_type'] ?? '') === 'BundleProduct') {
                continue;
            }

            $normalizedSlug = $this->normalizeProductSlug((string) ($product['slug'] ?? ''));
            if ($normalizedSlug === '' || isset($seen[$normalizedSlug])) {
                continue;
            }
            $seen[$normalizedSlug] = true;
            $product['slug'] = $normalizedSlug;
            $candidates[] = $product;
        }

        $candidates = $this->sortProducts(collect($candidates), 'newest')->values()->all();
        // خذ الأحدث أولاً، لكن ارفع الحد حتى يغطي أغلب الكتالوج المخزّن (أفتح PDP أسرع بعد التسخين)
        $selected = array_slice($candidates, 0, $limit);

        $warmed = 0;
        $skipped = 0;
        $hitTtlWarm = max(
            $hitTtl,
            (int) config('services.keepersport_graphql.warm_cache_ttl', 86400)
        );

        foreach ($selected as $product) {
            $normalizedSlug = (string) ($product['slug'] ?? '');
            $cacheKey = sprintf(
                'keepersport.product_by_slug.%s.%s%s',
                $graphLocale,
                md5($normalizedSlug),
                $pricingSuffix
            );

            $alreadyCached = false;
            $wrapped = $this->ksCache()->get($cacheKey);
            if (is_array($wrapped) && ($wrapped['hit'] ?? false) === true && is_array($wrapped['data'] ?? null)) {
                $alreadyCached = true;
                $skipped++;
            }

            // حدّث كاش الـ slug الخام + النسخة الجاهزة للعرض (بدون HTTP عند الفتح)
            $this->ksCache()->put($cacheKey, [
                'hit' => true,
                'data' => $product,
            ], $hitTtlWarm);

            $readyProduct = $this->finalizeProductDetailsResponse($product, $graphLocale, false);
            $this->ksCache()->put(
                $this->productReadyCacheKey($graphLocale, $normalizedSlug, $pricingSuffix),
                ['hit' => true, 'data' => $readyProduct],
                $hitTtlWarm
            );

            if (! $alreadyCached) {
                $warmed++;
            }

            if ($warmRecommended) {
                try {
                    $this->recommendedProductsForProduct($product, $graphLocale, true);
                } catch (\Throwable $e) {
                    Log::debug('Keepersport warm: recommended products skipped', [
                        'sku' => $product['sku'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return [
            'warmed' => $warmed,
            'skipped' => $skipped,
            'scanned' => count($nodes),
            'selected' => count($selected),
        ];
    }

    private function normalizeProductSlug(string $slug): string
    {
        $normalizedSlug = Str::lower(trim($slug));
        if (str_ends_with($normalizedSlug, '.html')) {
            $normalizedSlug = substr($normalizedSlug, 0, -5);
        }

        return $normalizedSlug;
    }

    private function productReadyCacheKey(string $graphLocale, string $normalizedSlug, string $pricingSuffix): string
    {
        return sprintf(
            'keepersport.product_ready.%s.%s%s',
            $graphLocale,
            md5($normalizedSlug),
            $pricingSuffix
        );
    }

    private function productSlugNodeIndexCacheKey(string $graphLocale): string
    {
        return sprintf('keepersport.product_slug_node_index.%s', $graphLocale);
    }

    /**
     * فهرس slug → عقدة GraphQL الخام — بحث O(1) بعد warm الكتالوج.
     *
     * @param  list<array<string, mixed>>  $nodes
     */
    private function refreshProductSlugNodeIndex(string $graphLocale, array $nodes): void
    {
        if ($nodes === []) {
            return;
        }

        $key = $this->productSlugNodeIndexCacheKey($graphLocale);
        $index = $this->ksCache()->get($key);
        if (! is_array($index)) {
            $index = [];
        }

        $changed = false;
        foreach ($nodes as $node) {
            if (! is_array($node) || ! $this->shouldExposeCatalogNode($node)) {
                continue;
            }

            $url = trim((string) ($node['url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $path = (string) parse_url($url, PHP_URL_PATH);
            $slug = $this->normalizeProductSlug(trim((string) basename($path), '/'));
            if ($slug === '') {
                continue;
            }

            $existing = $index[$slug] ?? null;
            if (! is_array($existing) || ($existing['sku'] ?? null) !== ($node['sku'] ?? null)) {
                $index[$slug] = $node;
                $changed = true;
            }
        }

        if ($changed) {
            $ttl = max(
                $this->catalogCacheTtlSeconds(),
                max(120, (int) config('services.keepersport_graphql.product_slug_cache_ttl', 3600))
            );
            $this->ksCache()->put($key, $index, $ttl);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findProductNodeInSlugIndex(string $graphLocale, string $normalizedSlug): ?array
    {
        $index = $this->ksCache()->get($this->productSlugNodeIndexCacheKey($graphLocale));
        if (! is_array($index)) {
            return null;
        }

        $node = $index[$normalizedSlug] ?? null;

        return is_array($node) ? $node : null;
    }

    private function defaultScanCap(): int
    {
        $configured = (int) config('services.keepersport_graphql.catalog_scan_limit', 1200);
        $isLocal = app()->environment('local');
        $min = $isLocal ? 80 : 120;
        $max = $isLocal ? 800 : 2000;

        return max($min, min($max, $configured));
    }

    private function catalogCacheTtlSeconds(): int
    {
        return max(60, (int) config('services.keepersport_graphql.catalog_cache_ttl', 3600));
    }

    private function catalogMaxScanPages(): int
    {
        $configured = (int) config('services.keepersport_graphql.catalog_max_pages', 24);
        $isLocal = app()->environment('local');
        $min = $isLocal ? 2 : 8;
        $max = $isLocal ? 8 : 24;

        return max($min, min($max, $configured));
    }

    /**
     * شجرة تصنيفات من الـ API (جذور + subCategories).
     *
     * @param  string|null  $graphLocale  قيمة Accept-Language لطلب GraphQL (مثلاً it)
     */
    public function categories(?string $graphLocale = null): array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');

        return $this->buildCategoryTreeFromNodes(
            $this->fetchCatalogEdges($graphLocale, $this->defaultScanCap())
        );
    }

    /**
     * أقسام المتجر الرئيسية لاختيار المنتجات في لوحة السلايدر (تطابق slugs الفلترة في products()).
     *
     * @return list<array{slug: string, name: string}>
     */
    public function adminPickerCategories(): array
    {
        $items = [
            ['slug' => 'goalkeeper-gloves', 'name' => 'Goalkeeper Gloves'],
            ['slug' => 'football-boots', 'name' => 'Football Boots'],
            ['slug' => 'goalkeeper-apparel', 'name' => 'Goalkeeper Apparel'],
            ['slug' => 'goalkeeper-equipment', 'name' => 'Goalkeeper Equipment'],
            ['slug' => 'teamsport', 'name' => 'Teamsport'],
        ];

        $apparelLeaves = [
            'goalkeeper-underwear' => 'Goalkeeper underwear',
            'goalkeeper-pants' => 'Goalkeeper pants',
            'goalkeeper-jerseys' => 'Goalkeeper jerseys',
            'goalkeeper-set' => 'Goalkeeper set',
            'goalkeeper-socks' => 'Goalkeeper socks',
            'goalkeeper-training-tops' => 'Goalkeeper training tops',
            'goalkeeper-undershirts' => 'Goalkeeper undershirts',
            'goalkeeper-undershorts' => 'Goalkeeper undershorts',
        ];

        foreach ($apparelLeaves as $slug => $name) {
            $items[] = ['slug' => $slug, 'name' => $name];
        }

        foreach (self::FOOTBALL_BOOT_SHOP_LEAF_SLUGS as $slug) {
            $items[] = [
                'slug' => $slug,
                'name' => ucwords(str_replace('-', ' ', $slug)),
            ];
        }

        return $items;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return list<array<string, mixed>>
     */
    private function buildCategoryTreeFromNodes(array $nodes): array
    {
        $roots = [];

        foreach ($nodes as $node) {
            foreach ($node['categories'] ?? [] as $c) {
                $cid = (string) ($c['id'] ?? '');
                if ($cid === '' || empty($c['name'])) {
                    continue;
                }
                if (! isset($roots[$cid])) {
                    $roots[$cid] = [
                        'id' => $cid,
                        'name' => (string) $c['name'],
                        'slug' => Str::slug((string) $c['name']),
                        'children' => [],
                    ];
                }
                $childIds = [];
                foreach ($roots[$cid]['children'] as $ch) {
                    $childIds[$ch['id']] = true;
                }
                foreach ($c['subCategories'] ?? [] as $sub) {
                    $sid = (string) ($sub['id'] ?? '');
                    if ($sid === '' || empty($sub['name']) || isset($childIds[$sid])) {
                        continue;
                    }
                    $roots[$cid]['children'][] = [
                        'id' => $sid,
                        'name' => (string) $sub['name'],
                        'slug' => Str::slug((string) $sub['name']),
                    ];
                    $childIds[$sid] = true;
                }
            }
        }

        return collect($roots)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * تصنيفات + منتجات في مسار واحد: جلب الكتالوج من upstream مرة واحدة عند غياب كاش المنتجات.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function shopListing(array $filters, ?string $graphLocale = null): array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $filters = $this->normalizeCatalogRequestFilters($filters);

        $perPage = max(1, min(120, (int) ($filters['per_page'] ?? self::PER_PAGE)));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $sort = (string) ($filters['sort'] ?? 'newest');
        $fetchLimits = $this->resolveCatalogFetchLimits($filters, $graphLocale);
        $scanLimit = $fetchLimits['scan'];
        $maxPagesOverride = $fetchLimits['max_pages'];
        $productsCacheTtl = max(60, (int) config('services.keepersport_graphql.products_cache_ttl', 1800));
        $listNorm = $this->normalizeProductsFiltersForCache($filters, $perPage, $page, $sort, $scanLimit, true);
        $listHash = sha1(json_encode($listNorm, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $pricingSuffix = app(WebsitePricingService::class)->pricingCacheSuffix();
        $listCacheKey = sprintf('keepersport.products_list.%s.%s%s', $graphLocale, $listHash, $pricingSuffix);
        $listStaleCacheKey = sprintf('keepersport.products_list.stale.%s.%s%s', $graphLocale, $listHash, $pricingSuffix);
        $isSaleListing = $this->isSaleCatalogSlug((string) ($filters['category'] ?? ''));
        if ($isSaleListing) {
            // كسر كاش القوائم الفارغة القديمة التي كانت تعامل sale كتصنيف.
            $listCacheKey .= '.onsale_v2';
            $listStaleCacheKey .= '.onsale_v2';
        }

        $categoryApiIds = $this->resolveApiCategoryIds($filters, $graphLocale);

        $cachedFull = $this->ksCache()->get($listCacheKey);
        if (is_array($cachedFull) && isset($cachedFull['all_items']) && is_array($cachedFull['all_items'])) {
            $categories = [];
            if (isset($cachedFull['categories']) && is_array($cachedFull['categories']) && $cachedFull['categories'] !== []) {
                $categories = $cachedFull['categories'];
            } else {
                $categories = $this->categories($graphLocale);
            }

            $pagePayload = $this->mergeGkApparelNavCountsIntoMeta(
                $this->sliceProductListForPage($cachedFull, $perPage, $page),
                $graphLocale
            );
            if ($categoryApiIds !== []) {
                $pagePayload['teamsport_subcategories'] = $cachedFull['teamsport_subcategories'] ?? $this->getTeamsportSubcategories();
            }

            return array_merge(['categories' => $categories], $pagePayload);
        }

        $staleFull = $this->ksCache()->get($listStaleCacheKey);
        if (
            ! $this->forceFreshCatalogFetch
            && is_array($staleFull)
            && isset($staleFull['all_items'])
            && is_array($staleFull['all_items'])
        ) {
            $this->scheduleCatalogListRefresh(
                $filters,
                $graphLocale,
                $scanLimit,
                $maxPagesOverride,
                $categoryApiIds,
                $listCacheKey,
                $listStaleCacheKey,
                $productsCacheTtl,
                true
            );

            $categories = [];
            if (isset($staleFull['categories']) && is_array($staleFull['categories']) && $staleFull['categories'] !== []) {
                $categories = $staleFull['categories'];
            } else {
                $categories = $this->categories($graphLocale);
            }

            $pagePayload = $this->mergeGkApparelNavCountsIntoMeta(
                $this->sliceProductListForPage($staleFull, $perPage, $page),
                $graphLocale
            );
            if ($categoryApiIds !== []) {
                $pagePayload['teamsport_subcategories'] = $staleFull['teamsport_subcategories'] ?? $this->getTeamsportSubcategories();
            }

            return array_merge(['categories' => $categories], $pagePayload);
        }

        $nodes = $isSaleListing
            ? $this->fetchOnSaleCatalogEdges($graphLocale, $scanLimit, $maxPagesOverride)
            : $this->fetchCatalogEdges($graphLocale, $scanLimit, $categoryApiIds, null, $maxPagesOverride);
        $categories = $this->resolveCategoriesForListing($graphLocale, $nodes, $isSaleListing);

        $filtersForCompute = $isSaleListing
            ? $this->filtersForSaleProductCompute($filters, $graphLocale)
            : $this->filtersForProductCompute($filters, $categoryApiIds);
        if (! $isSaleListing) {
            $filtersForCompute['_catalog_locale'] = $graphLocale;
        }

        $fullPayload = $this->computeProductsFullListFromNodes($nodes, $filtersForCompute, $sort);
        $fullPayload['categories'] = $categories;
        if ($categoryApiIds !== []) {
            $fullPayload['teamsport_subcategories'] = $this->getTeamsportSubcategories();
        }

        $this->rememberProductListCache($listCacheKey, $listStaleCacheKey, $fullPayload, $productsCacheTtl, $isSaleListing);

        $pagePayload = $this->mergeGkApparelNavCountsIntoMeta(
            $this->sliceProductListForPage($fullPayload, $perPage, $page),
            $graphLocale
        );
        if ($categoryApiIds !== []) {
            $pagePayload['teamsport_subcategories'] = $fullPayload['teamsport_subcategories'] ?? [];
        }

        return array_merge(['categories' => $categories], $pagePayload);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function products(array $filters, ?string $graphLocale = null): array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $filters = $this->normalizeCatalogRequestFilters($filters);

        $perPage = max(1, min(120, (int) ($filters['per_page'] ?? self::PER_PAGE)));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $sort = (string) ($filters['sort'] ?? 'newest');
        $fetchLimits = $this->resolveCatalogFetchLimits($filters, $graphLocale);
        $scanLimit = $fetchLimits['scan'];
        $maxPagesOverride = $fetchLimits['max_pages'];
        $productsCacheTtl = max(60, (int) config('services.keepersport_graphql.products_cache_ttl', 1800));
        $listNorm = $this->normalizeProductsFiltersForCache($filters, $perPage, $page, $sort, $scanLimit, true);
        $listHash = sha1(json_encode($listNorm, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $pricingSuffix = app(WebsitePricingService::class)->pricingCacheSuffix();
        $listCacheKey = sprintf('keepersport.products_list.%s.%s%s', $graphLocale, $listHash, $pricingSuffix);
        $listStaleCacheKey = sprintf('keepersport.products_list.stale.%s.%s%s', $graphLocale, $listHash, $pricingSuffix);
        $isSaleListing = $this->isSaleCatalogSlug((string) ($filters['category'] ?? ''));
        if ($isSaleListing) {
            $listCacheKey .= '.onsale_v2';
            $listStaleCacheKey .= '.onsale_v2';
        }
        $categoryApiIds = $this->resolveApiCategoryIds($filters, $graphLocale);

        $cachedFull = $this->ksCache()->get($listCacheKey);
        if (is_array($cachedFull) && isset($cachedFull['all_items']) && is_array($cachedFull['all_items'])) {
            return $this->mergeGkApparelNavCountsIntoMeta(
                $this->sliceProductListForPage($cachedFull, $perPage, $page),
                $graphLocale
            );
        }

        $staleFull = $this->ksCache()->get($listStaleCacheKey);
        if (
            ! $this->forceFreshCatalogFetch
            && is_array($staleFull)
            && isset($staleFull['all_items'])
            && is_array($staleFull['all_items'])
        ) {
            $this->scheduleCatalogListRefresh(
                $filters,
                $graphLocale,
                $scanLimit,
                $maxPagesOverride,
                $categoryApiIds,
                $listCacheKey,
                $listStaleCacheKey,
                $productsCacheTtl,
                false
            );

            return $this->mergeGkApparelNavCountsIntoMeta(
                $this->sliceProductListForPage($staleFull, $perPage, $page),
                $graphLocale
            );
        }

        $nodes = $isSaleListing
            ? $this->fetchOnSaleCatalogEdges($graphLocale, $scanLimit, $maxPagesOverride)
            : $this->fetchCatalogEdges($graphLocale, $scanLimit, $categoryApiIds, null, $maxPagesOverride);
        $filtersForCompute = $isSaleListing
            ? $this->filtersForSaleProductCompute($filters, $graphLocale)
            : $this->filtersForProductCompute($filters, $categoryApiIds);
        if (! $isSaleListing) {
            $filtersForCompute['_catalog_locale'] = $graphLocale;
        }
        $fullPayload = $this->computeProductsFullListFromNodes($nodes, $filtersForCompute, $sort);

        $this->rememberProductListCache($listCacheKey, $listStaleCacheKey, $fullPayload, $productsCacheTtl, $isSaleListing);

        return $this->mergeGkApparelNavCountsIntoMeta(
            $this->sliceProductListForPage($fullPayload, $perPage, $page),
            $graphLocale
        );
    }

    /**
     * يحدّد categoryIds من الـ API بناءً على التصنيف المطلوب لتمريرها إلى GraphQL.
     *
     * @return list<string>
     */
    private function resolveApiCategoryIds(array $filters, ?string $graphLocale = null): array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $category = Str::lower(trim((string) ($filters['category'] ?? '')));
        if ($category === '' || $this->isSaleCatalogSlug($category)) {
            return [];
        }
        if (isset(self::CATEGORY_API_IDS[$category])) {
            return self::CATEGORY_API_IDS[$category];
        }
        if (isset(self::GOALKEEPER_APPAREL_LEAF_API_IDS[$category])) {
            return self::GOALKEEPER_APPAREL_LEAF_API_IDS[$category];
        }
        if (isset(self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS[$category])) {
            return self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS[$category];
        }
        if ($this->isGoalkeeperEquipmentShopLeafSlug($category)) {
            $leafIds = $this->resolveGoalkeeperEquipmentLeafApiIds($category, $graphLocale);
            if ($leafIds !== []) {
                return $leafIds;
            }

            return self::CATEGORY_API_IDS['goalkeeper-equipment'] ?? [];
        }
        if ($this->isFootballBootShopLeafSlug($category)) {
            $leafIds = $this->resolveFootballBootLeafApiIds($category, $graphLocale);
            if ($leafIds !== []) {
                return $leafIds;
            }

            return self::CATEGORY_API_IDS['football-boots'] ?? [];
        }
        foreach (self::CATEGORY_SLUG_ALIASES as $routeSlug => $aliases) {
            if ($routeSlug !== $category) {
                continue;
            }
            foreach ($aliases as $a) {
                $key = Str::lower((string) $a);
                if (isset(self::CATEGORY_API_IDS[$key])) {
                    return self::CATEGORY_API_IDS[$key];
                }
                if (isset(self::GOALKEEPER_APPAREL_LEAF_API_IDS[$key])) {
                    return self::GOALKEEPER_APPAREL_LEAF_API_IDS[$key];
                }
                if (isset(self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS[$key])) {
                    return self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS[$key];
                }
            }
        }

        return [];
    }

    private function isGoalkeeperEquipmentShopLeafSlug(string $category): bool
    {
        $category = Str::lower(str_replace('_', '-', trim($category)));

        return in_array($category, self::GOALKEEPER_EQUIPMENT_SHOP_LEAF_SLUGS, true)
            || isset(self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS[$category]);
    }

    /**
     * حلّ IDs لورقة معدات من فهرس التصنيفات داخل جذر equipment (مثل football boots).
     *
     * @return list<string>
     */
    private function resolveGoalkeeperEquipmentLeafApiIds(string $category, string $graphLocale): array
    {
        $category = Str::lower(str_replace('_', '-', trim($category)));
        if (isset(self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS[$category])) {
            return self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS[$category];
        }

        $index = $this->equipmentCategorySlugIdIndex($graphLocale);
        $want = Str::lower(Str::slug($category));
        if ($want !== '' && isset($index[$want]) && $index[$want] !== []) {
            return array_values($index[$want]);
        }

        return [];
    }

    /**
     * @return array<string, list<string>>
     */
    private function equipmentCategorySlugIdIndex(string $graphLocale): array
    {
        $ttl = max(3600, (int) config('services.keepersport_graphql.catalog_cache_ttl', 3600));
        $cacheKey = 'keepersport.equipment_category_slug_ids_v2.'.$graphLocale;

        return $this->ksCache()->remember($cacheKey, $ttl, function () use ($graphLocale) {
            $parentIds = self::CATEGORY_API_IDS['goalkeeper-equipment'] ?? [];
            if ($parentIds === []) {
                return [];
            }
            $limit = min(800, $this->defaultScanCap());
            $nodes = $this->fetchCatalogEdges($graphLocale, $limit, $parentIds);

            return $this->buildCategorySlugIdIndexFromNodes($nodes);
        });
    }

    private function isFootballBootShopLeafSlug(string $category): bool
    {
        return in_array($category, self::FOOTBALL_BOOT_SHOP_LEAF_SLUGS, true);
    }

    /**
     * فهرس slug → معرّفات GraphQL من منتجات القسم (نفس منطق بناء التصنيفات في GraphQLService).
     *
     * @return array<string, list<string>>
     */
    private function categorySlugIdIndex(string $graphLocale): array
    {
        $ttl = max(3600, (int) config('services.keepersport_graphql.catalog_cache_ttl', 3600));
        $cacheKey = 'keepersport.category_slug_ids_v2.'.$graphLocale;

        return $this->ksCache()->remember($cacheKey, $ttl, function () use ($graphLocale) {
            $parentIds = self::CATEGORY_API_IDS['football-boots'] ?? [];
            if ($parentIds === []) {
                return [];
            }
            $limit = min(800, $this->defaultScanCap());
            $nodes = $this->fetchCatalogEdges($graphLocale, $limit, $parentIds);

            return $this->buildCategorySlugIdIndexFromNodes($nodes);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<string, list<string>>
     */
    private function buildCategorySlugIdIndexFromNodes(array $nodes): array
    {
        $index = [];
        foreach ($nodes as $node) {
            foreach ($node['categories'] ?? [] as $cat) {
                $rows = array_merge(
                    [['id' => $cat['id'] ?? '', 'name' => $cat['name'] ?? '']],
                    $cat['subCategories'] ?? []
                );
                foreach ($rows as $row) {
                    $id = trim((string) ($row['id'] ?? ''));
                    $name = trim((string) ($row['name'] ?? ''));
                    if ($id === '' || $name === '') {
                        continue;
                    }
                    $slug = Str::lower(Str::slug($name));
                    if ($slug === '') {
                        continue;
                    }
                    $index[$slug][$id] = true;
                }
            }
        }

        return array_map(
            static fn (array $ids) => array_values(array_keys($ids)),
            $index
        );
    }

    /**
     * @return list<string>
     */
    private function resolveFootballBootLeafApiIds(string $shopSlug, string $graphLocale): array
    {
        $slug = Str::lower(trim($shopSlug));
        $index = $this->categorySlugIdIndex($graphLocale);
        if (isset($index[$slug])) {
            return $index[$slug];
        }

        $aliases = [
            'football-boots-cleats' => 'cleats',
            'football-boots-accessories' => 'accessories',
            'football-boots-hard-court' => 'hard-court',
        ];
        if (isset($aliases[$slug], $index[$aliases[$slug]])) {
            return $index[$aliases[$slug]];
        }

        return [];
    }

    /**
     * @param  list<string>  $categoryApiIds
     * @return array<string, mixed>
     */
    private function filtersForProductCompute(array $filters, array $categoryApiIds): array
    {
        $out = $filters;
        if ($categoryApiIds === []) {
            return $out;
        }
        $cat = Str::lower(trim((string) ($filters['category'] ?? '')));
        if ($this->needsClientCategorySlugFilter($cat, $categoryApiIds)) {
            return $out;
        }
        unset($out['category']);

        return $out;
    }

    private function isSaleCatalogSlug(?string $category): bool
    {
        $category = Str::lower(str_replace('_', '-', trim((string) $category)));

        return in_array($category, ['sale', 'saldi', 'outlet', 'sconti'], true);
    }

    /**
     * شجرة تصنيفات للـ PLP بدون إعادة جلب الكتالوج الكامل (مشكلة sale سابقاً).
     *
     * @param  list<array<string, mixed>>  $nodes
     * @return list<array<string, mixed>>
     */
    private function resolveCategoriesForListing(string $graphLocale, array $nodes, bool $isSaleListing): array
    {
        if (! $isSaleListing) {
            return $this->buildCategoryTreeFromNodes($nodes);
        }

        $treeKey = sprintf('keepersport.category_tree.%s', $graphLocale);
        $cached = $this->ksCache()->get($treeKey);
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        return $this->buildCategoryTreeFromNodes($nodes);
    }

    /**
     * @param  array<string, mixed>  $fullPayload
     */
    private function rememberProductListCache(
        string $listCacheKey,
        string $listStaleCacheKey,
        array $fullPayload,
        int $productsCacheTtl,
        bool $isSaleListing = false,
    ): void {
        $toCache = $fullPayload;
        if ($isSaleListing) {
            // شجرة التصنيفات كبيرة؛ لا تُخزَّن مع قائمة التخفيض على database cache.
            unset($toCache['categories']);
        }

        try {
            $this->ksCache()->put($listCacheKey, $toCache, $productsCacheTtl);
            $this->ksCache()->put($listStaleCacheKey, $toCache, $this->productsListStaleExpiresAt());
        } catch (\Throwable $e) {
            Log::warning('Keepersport product list cache put failed', [
                'key' => $listCacheKey,
                'sale' => $isSaleListing,
                'items' => count($toCache['all_items'] ?? []),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * التخفيضات ليست تصنيفاً — أزل فلتر category حتى لا تُصفَّى النتائج بالاسم.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function filtersForSaleProductCompute(array $filters, string $graphLocale): array
    {
        $out = $filters;
        unset($out['category']);
        $out['_catalog_locale'] = $graphLocale;

        return $out;
    }

    /**
     * جلب منتجات on_sale مثل keepersport.net/sale عبر queryId.
     *
     * @return list<array<string, mixed>>
     */
    private function fetchOnSaleCatalogEdges(string $graphLocale, ?int $limit, ?int $maxPagesOverride = null): array
    {
        $cacheKey = sprintf('keepersport.catalog.onsale.v1.%s.%s', $graphLocale, $limit ?? 'all');
        $staleCacheKey = sprintf('keepersport.catalog.onsale.stale.v1.%s.%s', $graphLocale, $limit ?? 'all');
        $maxPages = $maxPagesOverride ?? max(3, (int) ceil(($limit ?? 240) / 100));
        $ttl = $this->catalogCacheTtlSeconds();

        $cached = $this->ksCache()->get($cacheKey);
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        if (! $this->forceFreshCatalogFetch) {
            $staleHit = $this->ksCache()->get($staleCacheKey);
            if (is_array($staleHit) && $staleHit !== []) {
                return $staleHit;
            }
        }

        $lockKey = sprintf('keepersport.catalog.onsale.lock.%s.%s', $graphLocale, $limit ?? 'all');
        $waitSeconds = app()->environment('local') ? 45 : 180;

        try {
            return $this->ksCache()->lock($lockKey, $waitSeconds + 120)->block($waitSeconds, function () use (
                $graphLocale,
                $limit,
                $cacheKey,
                $staleCacheKey,
                $maxPages,
                $ttl
            ) {
                $cachedInner = $this->ksCache()->get($cacheKey);
                if (is_array($cachedInner) && $cachedInner !== []) {
                    return $cachedInner;
                }

                $allEdges = $this->graphqlCatalog()->fetchOnSaleProductEdges($graphLocale, $limit, $maxPages);
                $nodes = array_values(array_filter(array_map(
                    fn ($edge) => is_array($edge) ? ($edge['node'] ?? []) : [],
                    $allEdges
                )));
                $nodes = $this->deduplicateCatalogNodesBySku($nodes);
                $nodes = array_values(array_filter($nodes, fn (array $node) => $this->shouldExposeCatalogNode($node)));

                if ($nodes !== []) {
                    // تجنّب كاش العقد الخام الضخمة على database cache (نفاد ذاكرة محلي).
                    $cacheDriver = (string) config('cache.default');
                    if (! in_array($cacheDriver, ['database', 'file'], true) || count($nodes) <= 320) {
                        $this->ksCache()->put($cacheKey, $nodes, $ttl);
                        $this->ksCache()->put($staleCacheKey, $nodes, $this->catalogGraphStaleExpiresAt());
                    }
                    $this->refreshProductSlugNodeIndex($graphLocale, $nodes);
                } else {
                    Log::warning('Keepersport on_sale catalog returned 0 products', [
                        'locale' => $graphLocale,
                        'limit' => $limit,
                    ]);
                }

                return $nodes;
            });
        } catch (LockTimeoutException $e) {
            $stale = $this->ksCache()->get($staleCacheKey);
            if (is_array($stale) && $stale !== []) {
                return $stale;
            }

            throw $e;
        }
    }

    /**
     * فلتر slug محلي فقط عندما GraphQL أعاد جذر القسم (مثل 54) وليس ورقة التصنيف الفعلية.
     *
     * @param  list<string>  $categoryApiIds
     */
    private function needsClientCategorySlugFilter(string $cat, array $categoryApiIds): bool
    {
        if ($cat === '') {
            return false;
        }
        if (isset(self::GOALKEEPER_APPAREL_LEAF_API_IDS[$cat])) {
            return false;
        }
        if (isset(self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS[$cat])) {
            return false;
        }
        if ($this->isGoalkeeperEquipmentShopLeafSlug($cat)) {
            $parent = self::CATEGORY_API_IDS['goalkeeper-equipment'] ?? [];
            if ($parent !== [] && $categoryApiIds === $parent) {
                return true;
            }
            if (count($categoryApiIds) === 1 && in_array($categoryApiIds[0] ?? '', $parent, true)) {
                return true;
            }

            return false;
        }
        if ($this->isFootballBootShopLeafSlug($cat)) {
            $parent = self::CATEGORY_API_IDS['football-boots'] ?? [];
            if ($parent !== [] && $categoryApiIds === $parent) {
                return true;
            }
            if (count($categoryApiIds) === 1 && in_array($categoryApiIds[0] ?? '', $parent, true)) {
                return true;
            }

            return false;
        }

        return false;
    }

    private function isShopLeafCategorySlug(string $category): bool
    {
        return $this->isFootballBootShopLeafSlug($category)
            || isset(self::GOALKEEPER_APPAREL_LEAF_API_IDS[$category])
            || $this->isGoalkeeperEquipmentShopLeafSlug($category);
    }

    /**
     * مطابقة صارمة: المنتج مُسنَد لورقة التصنيف بالمعرّف أو باسم subCategory فقط (بدون كلمات في اسم المنتج).
     */
    private function productInCategoryByExactSlug(array $product, string $shopSlug): bool
    {
        $want = Str::lower(Str::slug($shopSlug));
        if ($want === '') {
            return false;
        }
        foreach ($product['categories'] ?? [] as $cat) {
            if (Str::lower(Str::slug((string) ($cat['name'] ?? ''))) === $want) {
                return true;
            }
            foreach ($cat['children'] ?? [] as $ch) {
                if (Str::lower(Str::slug((string) ($ch['name'] ?? ''))) === $want) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * يحلّ slug التصنيف الفرعي إلى قائمة IDs. يدعم "tops" (parent) أو "tops/jerseys" (child).
     *
     * @return list<string>
     */
    private function resolveTeamsportSubIds(string $subSlug): array
    {
        if (str_contains($subSlug, '/')) {
            [$parentSlug, $childSlug] = explode('/', $subSlug, 2);
            $parent = self::TEAMSPORT_SUBCATEGORIES[$parentSlug] ?? null;
            if ($parent === null) {
                return [];
            }
            foreach ($parent['children'] ?? [] as $child) {
                if (($child['slug'] ?? '') === $childSlug) {
                    return $child['ids'] ?? [];
                }
            }
            return [];
        }

        return (self::TEAMSPORT_SUBCATEGORIES[$subSlug] ?? [])['ids'] ?? [];
    }

    /**
     * هل المنتج مرتبط بأي من معرّفات التصنيف (الجذر أو الأبناء في شجرة الـ API)؟
     *
     * @param  list<string>  $ids
     */
    private function productHasCategoryIdInTree(array $product, array $ids): bool
    {
        if ($ids === []) {
            return false;
        }
        foreach ($product['categories'] ?? [] as $cat) {
            if (in_array((string) ($cat['id'] ?? ''), $ids, true)) {
                return true;
            }
            foreach ($cat['children'] ?? [] as $ch) {
                if (in_array((string) ($ch['id'] ?? ''), $ids, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * مطابقة إضافية لبعض أوراق Teamsport حيث الـ API يضع التصنيف كاسم/سلاغ فرعي وليس بنفس id الثابت.
     */
    private function productMatchesTeamsportSubExtra(array $product, string $teamsportSub): bool
    {
        if ($teamsportSub === 'tops/t-shirt') {
            $topsIds = self::TEAMSPORT_SUBCATEGORIES['tops']['ids'] ?? [];
            if (! $this->productHasCategoryIdInTree($product, $topsIds)) {
                return false;
            }
            $needles = ['t-shirt', 'tshirt', 'tee shirt'];
            $name = Str::lower((string) ($product['name'] ?? ''));
            foreach ($needles as $n) {
                if (Str::contains($name, $n)) {
                    return true;
                }
            }
            foreach ($product['categories'] ?? [] as $cat) {
                foreach (array_merge(
                    [['name' => $cat['name'] ?? '', 'slug' => $cat['slug'] ?? '']],
                    $cat['children'] ?? []
                ) as $row) {
                    $n = Str::lower((string) ($row['name'] ?? ''));
                    $s = Str::lower((string) ($row['slug'] ?? ''));
                    foreach ($needles as $needle) {
                        if (Str::contains($n, $needle) || Str::contains($s, str_replace(' ', '-', $needle))) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * يرجع التصنيفات الفرعية لـ Teamsport كما تظهر على keepersport.net (3 مستويات).
     */
    private function getTeamsportSubcategories(): array
    {
        $result = [];
        foreach (self::TEAMSPORT_SUBCATEGORIES as $slug => $sub) {
            $result[] = [
                'slug' => $slug,
                'label' => $sub['label'],
                'ids' => $sub['ids'],
                'children' => $sub['children'] ?? [],
            ];
        }
        return $result;
    }

    /**
     * عدد المنتجات لكل ورقة من تصنيفات ملابس الحارس (لإخفاء الفروع الفارغة في القائمة).
     *
     * @return array<string, int>
     */
    public function goalkeeperApparelLeafProductCounts(?string $graphLocale = null): array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $ttl = max(300, (int) config('services.keepersport_graphql.products_cache_ttl', 1800));
        $cacheKey = 'keepersport.gk_apparel_nav_counts_v1.'.$graphLocale;

        return $this->ksCache()->remember($cacheKey, $ttl, function () use ($graphLocale) {
            $apiIds = self::CATEGORY_API_IDS['goalkeeper-apparel'] ?? [];
            if ($apiIds === []) {
                return [];
            }

            $scanLimit = min(800, $this->defaultScanCap());
            $nodes = $this->fetchCatalogEdges($graphLocale, $scanLimit, $apiIds);
            $mapped = collect($nodes)
                ->map(fn (array $edge) => $this->mapProductCard($edge))
                ->filter(fn (array $product) => ! empty($product['sku']))
                ->values();

            $out = [];
            foreach (array_keys(self::GOALKEEPER_APPAREL_LEAF_API_IDS) as $leafSlug) {
                $ids = self::GOALKEEPER_APPAREL_LEAF_API_IDS[$leafSlug];
                $out[$leafSlug] = $mapped->filter(fn (array $p) => $this->productMapsToAnyCategoryId($p, $ids))->count();
            }

            return $out;
        });
    }

    /**
     * @param  list<string>  $targetIds
     */
    private function productMapsToAnyCategoryId(array $product, array $targetIds): bool
    {
        if ($targetIds === []) {
            return false;
        }
        $want = array_flip(array_map('strval', $targetIds));
        foreach ($product['categories'] ?? [] as $cat) {
            $id = (string) ($cat['id'] ?? '');
            if ($id !== '' && isset($want[$id])) {
                return true;
            }
            foreach ($cat['children'] ?? [] as $child) {
                $cid = (string) ($child['id'] ?? '');
                if ($cid !== '' && isset($want[$cid])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * عدد المنتجات لكل فرع في القائمة الجانبية (نفس منطق صفحة المنتجات) — لإخفاء التصنيفات الفارغة.
     *
     * @return array<string, array<string, int>>
     */
    public function shopNavCategoryProductCounts(?string $graphLocale = null): array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $ttl = max(300, (int) config('services.keepersport_graphql.products_cache_ttl', 1800));

        return $this->ksCache()->remember('keepersport.shop_nav_leaf_counts_v6.'.$graphLocale, $ttl, function () use ($graphLocale) {
            $sort = 'newest';
            $out = [];
            $sections = ['goalkeeper-gloves', 'football-boots', 'goalkeeper-equipment'];

            foreach ($sections as $sectionKey) {
                $ids = self::CATEGORY_API_IDS[$sectionKey] ?? [];
                if ($ids === []) {
                    continue;
                }
                $limit = min(800, $this->defaultScanCap());
                $nodes = $this->fetchCatalogEdges($graphLocale, $limit, $ids);
                $sectionCounts = [];

                if ($nodes === []) {
                    $out[$sectionKey] = [];

                    continue;
                }

                if ($sectionKey === 'football-boots') {
                    foreach (self::FOOTBALL_BOOT_SHOP_LEAF_SLUGS as $bootSlug) {
                        $leafIds = $this->resolveFootballBootLeafApiIds($bootSlug, $graphLocale);
                        if ($leafIds === []) {
                            $sectionCounts[$bootSlug] = 0;

                            continue;
                        }
                        $sectionCounts[$bootSlug] = collect($nodes)
                            ->map(fn (array $edge) => $this->mapProductCard($edge))
                            ->filter(fn (array $p) => ! empty($p['sku']) && $this->productHasCategoryIdInTree($p, $leafIds))
                            ->count();
                    }
                }

                $freq = [];
                $subIdToSlug = [];
                $rootIdToSlug = [];
                foreach ($nodes as $node) {
                    foreach ($node['categories'] ?? [] as $c) {
                        $cid = trim((string) ($c['id'] ?? ''));
                        $name = (string) ($c['name'] ?? '');
                        if ($name !== '') {
                            $slug = Str::lower(Str::slug($name));
                            if ($slug !== '') {
                                $freq[$slug] = ($freq[$slug] ?? 0) + 1;
                                if ($cid !== '') {
                                    $rootIdToSlug[$cid] = $slug;
                                }
                            }
                        }
                        foreach ($c['subCategories'] ?? [] as $sub) {
                            $sname = (string) ($sub['name'] ?? '');
                            if ($sname === '') {
                                continue;
                            }
                            $slug = Str::lower(Str::slug($sname));
                            if ($slug === '') {
                                continue;
                            }
                            $freq[$slug] = ($freq[$slug] ?? 0) + 1;
                            $sid = trim((string) ($sub['id'] ?? ''));
                            if ($sid !== '') {
                                $subIdToSlug[$sid] = $slug;
                            }
                        }
                    }
                }
                arsort($freq);
                $candidates = array_slice(array_keys($freq), 0, 48);
                foreach ($candidates as $slug) {
                    if (array_key_exists($slug, $sectionCounts)) {
                        continue;
                    }
                    $full = $this->computeProductsFullListFromNodes($nodes, ['category' => $slug], $sort);
                    $sectionCounts[$slug] = count($full['all_items'] ?? []);
                }
                foreach ($subIdToSlug as $sid => $slug) {
                    $sectionCounts['@id:'.$sid] = (int) ($sectionCounts[$slug] ?? 0);
                }
                foreach ($rootIdToSlug as $rid => $slug) {
                    if (! array_key_exists('@id:'.$rid, $sectionCounts)) {
                        $sectionCounts['@id:'.$rid] = (int) ($sectionCounts[$slug] ?? 0);
                    }
                }
                $out[$sectionKey] = $sectionCounts;
            }

            $teamsportIds = self::CATEGORY_API_IDS['teamsport'] ?? [];
            if ($teamsportIds !== []) {
                $limit = min(800, $this->defaultScanCap());
                $nodes = $this->fetchCatalogEdges($graphLocale, $limit, $teamsportIds);
                $tsCounts = [];
                if ($nodes !== []) {
                    foreach (self::TEAMSPORT_SUBCATEGORIES as $parentSlug => $sub) {
                        $full = $this->computeProductsFullListFromNodes($nodes, [
                            'category' => 'teamsport',
                            'teamsport_sub' => $parentSlug,
                        ], $sort);
                        $tsCounts[$parentSlug] = count($full['all_items'] ?? []);
                        foreach ($sub['children'] ?? [] as $gc) {
                            $gcSlug = (string) ($gc['slug'] ?? '');
                            if ($gcSlug === '') {
                                continue;
                            }
                            $key = $parentSlug.'/'.$gcSlug;
                            $full = $this->computeProductsFullListFromNodes($nodes, [
                                'category' => 'teamsport',
                                'teamsport_sub' => $key,
                            ], $sort);
                            $tsCounts[$key] = count($full['all_items'] ?? []);
                        }
                    }
                }
                $out['teamsport'] = $tsCounts;
            }

            return $out;
        });
    }

    /**
     * @param  array<string, mixed>  $pagePayload
     * @return array<string, mixed>
     */
    private function mergeGkApparelNavCountsIntoMeta(array $pagePayload, string $graphLocale): array
    {
        $meta = $pagePayload['meta'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        // لا نبني الأعداد أثناء طلب المتجر — كان يعلق PLP لدقائق. تُملأ عبر warm-catalog-cache أو أول زيارة سابقة.
        $gkKey = 'keepersport.gk_apparel_nav_counts_v1.'.$graphLocale;
        $navKey = 'keepersport.shop_nav_leaf_counts_v6.'.$graphLocale;
        $gk = $this->ksCache()->get($gkKey);
        if (is_array($gk) && $gk !== []) {
            $meta['gk_apparel_leaf_counts'] = $gk;
        }
        $nav = $this->ksCache()->get($navKey);
        if (is_array($nav) && $nav !== []) {
            $meta['shop_nav_leaf_counts'] = $nav;
        }

        $pagePayload['meta'] = $meta;

        return $pagePayload;
    }

    /**
     * تسخين أعداد القائمة الجانبية (ثقيل — للـ cron فقط).
     */
    public function warmNavCategoryCounts(?string $graphLocale = null): void
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'en');
        try {
            $this->goalkeeperApparelLeafProductCounts($graphLocale);
            $this->shopNavCategoryProductCounts($graphLocale);
        } catch (\Throwable $e) {
            Log::warning('Keepersport warm: nav counts failed', [
                'locale' => $graphLocale,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * حدود جلب GraphQL: فروع ضيقة (accessories) = عدد منتجات قليل + صفحات أقل؛ جذر القسم = مسح أوسع.
     *
     * @param  array<string, mixed>  $filters
     * @return array{scan: int, max_pages: int|null}
     */
    private function resolveCatalogFetchLimits(array $filters, ?string $graphLocale = null): array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $cap = $this->defaultScanCap();
        $explicit = isset($filters['scan_limit']) ? (int) $filters['scan_limit'] : null;
        if ($explicit !== null && $explicit > 0) {
            return ['scan' => max(120, min($cap, $explicit)), 'max_pages' => null];
        }

        $category = Str::lower(trim((string) ($filters['category'] ?? '')));
        if ($this->isSaleCatalogSlug($category)) {
            $isLocal = app()->environment('local');
            // on_sale يعيد ~1300 منتجاً؛ نحدّ الجلب لتفادي نفاد الذاكرة ومهلة الواجهة.
            $scan = $isLocal ? min($cap, 120) : min($cap, 600);

            return [
                'scan' => max(80, $scan),
                'max_pages' => max(2, (int) ceil($scan / 100)),
            ];
        }
        $categoryApiIds = $this->resolveApiCategoryIds($filters, $graphLocale);
        $parentBootIds = self::CATEGORY_API_IDS['football-boots'] ?? [];

        if ($categoryApiIds !== []) {
            if ($category !== '' && isset(self::GOALKEEPER_APPAREL_LEAF_API_IDS[$category])) {
                return ['scan' => max(80, min(250, $cap)), 'max_pages' => 6];
            }

            if ($category !== '' && isset(self::GOALKEEPER_EQUIPMENT_LEAF_API_IDS[$category])) {
                return ['scan' => max(80, min(300, $cap)), 'max_pages' => 6];
            }

            if ($category !== '' && $this->isGoalkeeperEquipmentShopLeafSlug($category)) {
                $leafIds = $this->resolveGoalkeeperEquipmentLeafApiIds($category, $graphLocale ?? (string) config('services.keepersport_graphql.catalog_accept_language', 'en'));
                if ($leafIds !== [] && $categoryApiIds === $leafIds) {
                    return ['scan' => max(80, min(250, $cap)), 'max_pages' => 6];
                }

                return ['scan' => max(120, min(500, $cap)), 'max_pages' => 8];
            }

            if ($category !== '' && $this->isFootballBootShopLeafSlug($category)) {
                $leafIds = $this->resolveFootballBootLeafApiIds($category, $graphLocale ?? (string) config('services.keepersport_graphql.catalog_accept_language', 'en'));
                if ($leafIds !== [] && $categoryApiIds === $leafIds) {
                    return ['scan' => max(80, min(160, $cap)), 'max_pages' => 4];
                }
            }

            if ($category === 'football-boots' || $categoryApiIds === $parentBootIds) {
                return ['scan' => min(800, $cap), 'max_pages' => null];
            }

            if (count($categoryApiIds) <= 2) {
                return ['scan' => max(120, min(400, $cap)), 'max_pages' => 8];
            }

            return ['scan' => min(800, $cap), 'max_pages' => null];
        }

        $hasSearch = trim((string) ($filters['q'] ?? '')) !== '';
        if ($hasSearch) {
            return ['scan' => max(120, min($cap, 600)), 'max_pages' => 10];
        }

        $hasCategory = $category !== '';
        $hasBrand = $this->normalizeBrandFilterList($filters) !== [];
        $hasColor = $this->normalizeColorFilterList($filters) !== [];
        $hasSize = trim((string) ($filters['size'] ?? '')) !== '' || (is_array($filters['sizes'] ?? null) && ($filters['sizes'] ?? []) !== []);
        $hasAttrs = is_array($filters['attrs'] ?? null) && ($filters['attrs'] ?? []) !== [];

        if ($hasCategory && ! $hasBrand && ! $hasColor && ! $hasSize && ! $hasAttrs) {
            return ['scan' => max(120, min($cap, 600)), 'max_pages' => 10];
        }

        return ['scan' => max(120, min($cap, 600)), 'max_pages' => 10];
    }

    /**
     * قائمة كاملة بعد الفلاتر والفرز — تُخزَّن في الكاش مرة لكل (تصنيف/فلاتر/فرز) دون رقم الصفحة.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @param  array<string, mixed>  $filters
     * @return array{all_items: list<array<string, mixed>>, facets: array<string, mixed>, meta: array<string, int>}
     */
    private function computeProductsFullListFromNodes(array $nodes, array $filters, string $sort): array
    {
        $allMapped = collect($nodes)
            ->filter(fn (array $node) => $this->shouldExposeCatalogNode($node))
            ->map(fn (array $edge) => $this->mapProductCard($edge))
            ->filter(fn (array $product) => ! empty($product['sku']))
            ->unique(fn (array $product) => (string) ($product['sku'] ?? ''))
            ->values();

        $sourceCount = $allMapped->count();

        $afterCategory = $allMapped;
        $catKey = Str::lower(trim((string) ($filters['category'] ?? '')));
        $graphLocale = (string) ($filters['_catalog_locale'] ?? config('services.keepersport_graphql.catalog_accept_language', 'it'));

        if ($catKey !== '' && $this->isFootballBootShopLeafSlug($catKey)) {
            $leafIds = $this->resolveFootballBootLeafApiIds($catKey, $graphLocale);
            if ($leafIds !== []) {
                $afterCategory = $allMapped->filter(
                    fn (array $product) => $this->productHasCategoryIdInTree($product, $leafIds)
                )->values();
            } else {
                $afterCategory = $allMapped->filter(
                    fn (array $product) => $this->productInCategoryByExactSlug($product, $catKey)
                )->values();
            }
        } elseif ($catKey !== '' && $this->isGoalkeeperEquipmentShopLeafSlug($catKey)) {
            $leafIds = $this->resolveGoalkeeperEquipmentLeafApiIds($catKey, $graphLocale);
            if ($leafIds !== []) {
                $afterCategory = $allMapped->filter(
                    fn (array $product) => $this->productHasCategoryIdInTree($product, $leafIds)
                )->values();
            }
            if ($afterCategory->isEmpty()) {
                $afterCategory = $allMapped->filter(
                    fn (array $product) => $this->productInCategoryByExactSlug($product, $catKey)
                )->values();
            }
            if ($afterCategory->isEmpty()) {
                $needles = $this->categoryFilterNeedles($catKey);
                $afterCategory = $allMapped->filter(function (array $product) use ($needles) {
                    return collect($product['categories'])->contains(function (array $cat) use ($needles) {
                        if ($this->categoryMatchesNeedles($cat, $needles)) {
                            return true;
                        }
                        foreach ($cat['children'] ?? [] as $child) {
                            if ($this->categoryMatchesNeedles($child, $needles)) {
                                return true;
                            }
                        }

                        return false;
                    });
                })->values();
            }
        } elseif ($catKey !== '') {
            $needles = $this->categoryFilterNeedles($catKey);
            $afterCategory = $allMapped->filter(function (array $product) use ($needles) {
                return collect($product['categories'])->contains(function (array $cat) use ($needles) {
                    if ($this->categoryMatchesNeedles($cat, $needles)) {
                        return true;
                    }
                    foreach ($cat['children'] ?? [] as $child) {
                        if ($this->categoryMatchesNeedles($child, $needles)) {
                            return true;
                        }
                    }

                    return false;
                });
            })->values();
        }

        if ($afterCategory->isEmpty() && ! empty($filters['category'])) {
            $needleKey = Str::lower(trim((string) $filters['category']));
            $isGloveCategory = in_array($needleKey, ['goalkeeper-gloves', 'guanti-da-portiere'], true)
                || Str::contains($needleKey, 'guanti')
                || Str::contains($needleKey, 'glove');
            if ($isGloveCategory) {
                $afterCategory = $this->looseGloveCategoryFallback($allMapped);
            }
        }

        if ($afterCategory->isEmpty() && ! empty($filters['category'])) {
            $needleKey = Str::lower(trim((string) $filters['category']));
            if (isset(self::MAIN_CATEGORY_KEYWORDS[$needleKey])) {
                $afterCategory = $this->looseMainCategoryFallback($allMapped, self::MAIN_CATEGORY_KEYWORDS[$needleKey]);
            }
        }

        $afterCategoryCount = $afterCategory->count();

        $requestedCategory = Str::lower((string) ($filters['category'] ?? ''));
        if (
            $requestedCategory !== ''
            && ! $this->isFootballBootShopLeafSlug($requestedCategory)
            && ! $this->isGoalkeeperEquipmentShopLeafSlug($requestedCategory)
            && ! isset(self::GOALKEEPER_APPAREL_LEAF_API_IDS[$requestedCategory])
        ) {
            $afterCategory = $this->refineMainCategoryResults($afterCategory, $requestedCategory);
        }

        $teamsportSub = Str::lower(trim((string) ($filters['teamsport_sub'] ?? '')));
        if ($teamsportSub !== '') {
            $subCatIds = $this->resolveTeamsportSubIds($teamsportSub);
            $afterCategory = $afterCategory->filter(function (array $product) use ($subCatIds, $teamsportSub) {
                if ($subCatIds !== [] && $this->productHasCategoryIdInTree($product, $subCatIds)) {
                    return true;
                }

                return $this->productMatchesTeamsportSubExtra($product, $teamsportSub);
            })->values();
        }

        $facets = $this->buildFacetsExtended($afterCategory);

        $filtered = $afterCategory;

        if (! empty($filters['brand'])) {
            $filters['brands'] = array_values(array_unique(array_filter(array_merge(
                (array) ($filters['brands'] ?? []),
                [(string) $filters['brand']]
            ))));
            unset($filters['brand']);
        }

        $brandList = $this->normalizeBrandFilterList($filters);
        if ($brandList !== []) {
            $filtered = $filtered->filter(function (array $product) use ($brandList) {
                $brandId = Str::lower((string) ($product['brand']['id'] ?? ''));
                $brandSlug = Str::slug((string) ($product['brand']['name'] ?? ''));

                return in_array($brandId, $brandList, true)
                    || in_array($brandSlug, $brandList, true);
            })->values();
        }

        $sizeList = $this->normalizeSizeFilterList($filters);
        if ($sizeList !== []) {
            $filtered = $filtered->filter(function (array $product) use ($sizeList) {
                $sizesLower = collect($product['sizes'])->map(fn (string $size) => Str::lower($size));

                return $sizesLower->contains(fn (string $size) => in_array($size, $sizeList, true));
            })->values();
        }

        if (! empty($filters['color'])) {
            $filters['colors'] = array_values(array_unique(array_filter(array_merge(
                (array) ($filters['colors'] ?? []),
                [(string) $filters['color']]
            ))));
            unset($filters['color']);
        }

        $colorList = $this->normalizeColorFilterList($filters);
        if ($colorList !== []) {
            $filtered = $filtered->filter(function (array $product) use ($colorList) {
                return collect($product['colors'])->contains(function (array $color) use ($colorList) {
                    $name = Str::lower((string) ($color['name'] ?? ''));
                    $hex = Str::lower((string) ($color['hex'] ?? ''));

                    return in_array($name, $colorList, true) || in_array($hex, $colorList, true);
                });
            })->values();
        }

        if (! empty($filters['attrs']) && is_array($filters['attrs'])) {
            $grouped = [];
            foreach ($filters['attrs'] as $pair) {
                $pair = (string) $pair;
                if (! str_contains($pair, '|')) {
                    continue;
                }
                [$ak, $vk] = array_map('trim', explode('|', $pair, 2));
                $ak = Str::lower($ak);
                $vk = Str::lower($vk);
                if ($ak === '' || $vk === '') {
                    continue;
                }
                $grouped[$ak] ??= [];
                $grouped[$ak][] = $vk;
            }

            foreach ($grouped as $ak => $valueKeys) {
                $valueKeys = array_values(array_unique($valueKeys));
                $filtered = $filtered->filter(function (array $product) use ($ak, $valueKeys) {
                    return collect($product['attribute_facets'] ?? [])->contains(
                        fn (array $f) => Str::lower((string) ($f['attr_key'] ?? '')) === $ak
                            && in_array(Str::lower((string) ($f['value_key'] ?? '')), $valueKeys, true)
                    );
                })->values();
            }
        }

        if (! empty($filters['q'])) {
            $q = Str::lower((string) $filters['q']);
            $filtered = $filtered->filter(function (array $product) use ($q) {
                $attrText = collect($product['attribute_facets'] ?? [])
                    ->map(fn (array $f) => ($f['attr_label'] ?? '').' '.($f['value_label'] ?? '').' '.($f['value_key'] ?? ''))
                    ->implode(' ');
                $haystack = Str::lower(
                    implode(' ', [
                        $product['name'] ?? '',
                        Arr::get($product, 'brand.name', ''),
                        implode(' ', collect($product['categories'])->pluck('name')->all()),
                        $attrText,
                    ])
                );

                return Str::contains($haystack, $q);
            })->values();
        }

        $filtered = $this->sortProducts($filtered, $sort);
        $total = $filtered->count();

        return [
            'all_items' => $filtered->values()->all(),
            'facets' => $facets,
            'meta' => [
                'source_product_count' => $sourceCount,
                'after_category_count' => $afterCategoryCount,
                'filtered_count' => $total,
            ],
        ];
    }

    /**
     * @param  array{all_items?: list<array<string, mixed>>, facets?: array<string, mixed>, meta?: array<string, int>}  $fullPayload
     * @return array{items: list<array<string, mixed>>, pagination: array<string, mixed>, facets: array<string, mixed>, meta: array<string, int>}
     */
    private function sliceProductListForPage(array $fullPayload, int $perPage, int $page): array
    {
        $all = $fullPayload['all_items'] ?? [];
        if (! is_array($all)) {
            $all = [];
        }
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $total = count($all);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($all, $offset, $perPage);
        $items = app(WebsitePricingService::class)->applyToProductCards($items);

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
            'facets' => is_array($fullPayload['facets'] ?? null) ? $fullPayload['facets'] : [
                'brands' => [],
                'sizes' => [],
                'colors' => [],
                'attributes' => [],
            ],
            'meta' => is_array($fullPayload['meta'] ?? null) ? $fullPayload['meta'] : [
                'source_product_count' => 0,
                'after_category_count' => 0,
                'filtered_count' => $total,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @param  array<string, mixed>  $filters
     * @return array{items: list<array<string, mixed>>, pagination: array<string, mixed>, facets: array<string, mixed>, meta: array<string, int>}
     */
    private function computeProductsResultFromNodes(array $nodes, array $filters, int $perPage, int $page, string $sort): array
    {
        $full = $this->computeProductsFullListFromNodes($nodes, $filters, $sort);

        return $this->sliceProductListForPage($full, $perPage, $page);
    }

    public function productBySlug(string $slug, ?string $graphLocale = null): ?array
    {
        $started = microtime(true);
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $normalizedSlug = $this->normalizeProductSlug($slug);

        $hitTtl = max(120, (int) config('services.keepersport_graphql.product_slug_cache_ttl', 3600));
        // miss قصير — لا نحبس المنتج على 404 بعد الـ warm أو إصلاح البحث
        $missTtl = max(15, min(120, (int) config('services.keepersport_graphql.product_slug_miss_cache_ttl', 60)));
        $pricingSuffix = app(WebsitePricingService::class)->pricingCacheSuffix();
        $readyKey = $this->productReadyCacheKey($graphLocale, $normalizedSlug, $pricingSuffix);
        $cacheKey = sprintf('keepersport.product_by_slug.%s.%s%s', $graphLocale, md5($normalizedSlug), $pricingSuffix);

        $source = 'miss';

        // 1) hit إيجابي فقط — تجاهل hit:false حتى لا يبقى 404 عالق
        $readyWrapped = $this->ksCache()->get($readyKey);
        if (is_array($readyWrapped)
            && ($readyWrapped['hit'] ?? false) === true
            && is_array($readyWrapped['data'] ?? null)
        ) {
            $ready = $readyWrapped['data'];
            if (($ready['product_type'] ?? '') !== 'BundleProduct') {
                $needsOfferRefresh = empty($ready['public_offer_checked'])
                    && (empty($ready['promo_ends_at']) || empty($ready['price']['old']));

                if ($needsOfferRefresh) {
                    $slugWrapped = $this->ksCache()->get($cacheKey);
                    if (is_array($slugWrapped)
                        && ($slugWrapped['hit'] ?? false) === true
                        && is_array($slugWrapped['data'] ?? null)
                    ) {
                        $ready = $this->finalizeProductDetailsResponse($slugWrapped['data'], $graphLocale, true);
                        $this->ksCache()->put($readyKey, [
                            'hit' => true,
                            'data' => $ready,
                        ], $hitTtl);
                        $this->logSlowProductLookup($normalizedSlug, $started, 'ready_offer_refresh');

                        return $ready;
                    }
                }

                if ((bool) config('services.keepersport_graphql.pdp_refresh_quantities_on_cache_hit', false)) {
                    $refreshed = $this->ensureProductVariantBuyableQuantities($ready, $graphLocale);
                    if ($refreshed !== $ready) {
                        $this->ksCache()->put($readyKey, [
                            'hit' => true,
                            'data' => $refreshed,
                        ], $hitTtl);
                        $ready = $refreshed;
                    }
                }
                $this->logSlowProductLookup($normalizedSlug, $started, 'ready');

                return $ready;
            }
        }

        // 2) كاش slug إيجابي فقط
        $product = null;
        $wrapped = $this->ksCache()->get($cacheKey);
        if (is_array($wrapped)
            && ($wrapped['hit'] ?? false) === true
            && is_array($wrapped['data'] ?? null)
        ) {
            $product = $wrapped['data'];
            if (($product['product_type'] ?? '') === 'BundleProduct') {
                return null;
            }
            $source = 'slug';
        }

        // 3) بحث محلي / fulltext محدود
        if (! is_array($product)) {
            $product = $this->findProductBySlugUncached($graphLocale, $normalizedSlug);
            $source = 'lookup';

            if ($product !== null) {
                $this->ksCache()->put($cacheKey, [
                    'hit' => true,
                    'data' => $product,
                ], $hitTtl);
            } else {
                // لا تكتب miss على ready إلا لفترة قصيرة جداً
                $this->ksCache()->put($cacheKey, [
                    'hit' => false,
                    'data' => null,
                ], $missTtl);
                $this->logSlowProductLookup($normalizedSlug, $started, 'not_found');

                return null;
            }
        }

        if (($product['product_type'] ?? '') === 'BundleProduct') {
            return null;
        }

        $final = $this->finalizeProductDetailsResponse($product, $graphLocale);
        $this->ksCache()->put($readyKey, [
            'hit' => true,
            'data' => $final,
        ], $hitTtl);
        $this->logSlowProductLookup($normalizedSlug, $started, $source);

        return $final;
    }

    /**
     * قراءة منتج من الكاش فقط (بدون GraphQL) — لـ OG/meta عند تحميل HTML.
     *
     * @return array<string, mixed>|null
     */
    /**
     * سعر العرض (SAR) لسلايدر الرئيسية — يطابق صفحة المنتج قدر الإمكان بدون طلبات HTTP.
     *
     * @return array{current: float, old: float|null, currency: string}|null
     */
    public function displayPriceForSlug(string $slug, ?string $graphLocale = null): ?array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'en');
        $normalizedSlug = $this->normalizeProductSlug($slug);
        if ($normalizedSlug === '') {
            return null;
        }

        $pricingSuffix = app(WebsitePricingService::class)->pricingCacheSuffix();
        $readyKey = $this->productReadyCacheKey($graphLocale, $normalizedSlug, $pricingSuffix);
        $readyWrapped = $this->ksCache()->get($readyKey);
        if (is_array($readyWrapped)
            && ($readyWrapped['hit'] ?? false) === true
            && is_array($readyWrapped['data'] ?? null)
        ) {
            return $this->extractDisplayPriceArray($readyWrapped['data']);
        }

        $cacheKey = sprintf('keepersport.product_by_slug.%s.%s%s', $graphLocale, md5($normalizedSlug), $pricingSuffix);
        $wrapped = $this->ksCache()->get($cacheKey);
        if (is_array($wrapped)
            && ($wrapped['hit'] ?? false) === true
            && is_array($wrapped['data'] ?? null)
        ) {
            $product = $this->applyCachedPublicOfferToProduct($wrapped['data'], $graphLocale);
            $final = $this->finalizeProductDetailsResponse($product, $graphLocale, false);
            $this->ksCache()->put($readyKey, ['hit' => true, 'data' => $final], max(120, (int) config('services.keepersport_graphql.product_slug_cache_ttl', 3600)));

            return $this->extractDisplayPriceArray($final);
        }

        $node = $this->findProductNodeInSlugIndex($graphLocale, $normalizedSlug);
        if (! is_array($node)) {
            foreach ($this->getCatalogEdgesFromCacheOnly($graphLocale) as $candidate) {
                if (! is_array($candidate)) {
                    continue;
                }
                if ($this->catalogNodeQuickMatchesSlug($candidate, $normalizedSlug) === 2) {
                    $node = $candidate;
                    break;
                }
            }
        }
        if (! is_array($node)) {
            return null;
        }

        $card = $this->mapProductCard($node);
        $card = $this->applyCachedPublicOfferToProduct($card, $graphLocale);
        $card = app(WebsitePricingService::class)->applyToProductCard($card);

        return $this->extractDisplayPriceArray($card);
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array{current: float, old: float|null, currency: string}|null
     */
    private function extractDisplayPriceArray(array $product): ?array
    {
        if (! isset($product['price']) || ! is_array($product['price'])) {
            return null;
        }
        $current = (float) ($product['price']['current'] ?? 0);
        if ($current <= 0) {
            return null;
        }

        return [
            'current' => $current,
            'old' => isset($product['price']['old']) && $product['price']['old'] !== null
                ? (float) $product['price']['old']
                : null,
            'currency' => (string) ($product['price']['currency'] ?? 'SAR'),
        ];
    }

    /**
     * يطبّق عرض keepersport.net من الكاش فقط (بدون HTTP) ليطابق PDP بعد أول إثراء.
     *
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function applyCachedPublicOfferToProduct(array $product, string $graphLocale): array
    {
        $sku = trim((string) ($product['sku'] ?? ''));
        if ($sku === '') {
            return $product;
        }

        $cacheKey = sprintf('keepersport.public_offer.%s.%s', $graphLocale, md5($sku));
        $cached = $this->ksCache()->get($cacheKey);
        if (! is_array($cached) || ($cached['hit'] ?? false) !== true || ! is_array($cached['data'] ?? null)) {
            return $product;
        }

        return $this->mergePublicOfferNodeIntoProduct($product, $cached['data']);
    }

    public function productBySlugFromCacheOnly(string $slug, ?string $graphLocale = null): ?array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $normalizedSlug = $this->normalizeProductSlug($slug);
        if ($normalizedSlug === '') {
            return null;
        }

        $hitTtl = max(120, (int) config('services.keepersport_graphql.product_slug_cache_ttl', 3600));
        $pricingSuffix = app(WebsitePricingService::class)->pricingCacheSuffix();
        $readyKey = $this->productReadyCacheKey($graphLocale, $normalizedSlug, $pricingSuffix);

        $readyWrapped = $this->ksCache()->get($readyKey);
        if (is_array($readyWrapped) && ($readyWrapped['hit'] ?? false) === true && is_array($readyWrapped['data'] ?? null)) {
            return $readyWrapped['data'];
        }

        $cacheKey = sprintf('keepersport.product_by_slug.%s.%s%s', $graphLocale, md5($normalizedSlug), $pricingSuffix);
        $wrapped = $this->ksCache()->get($cacheKey);
        if (! is_array($wrapped) || ($wrapped['hit'] ?? false) !== true || ! is_array($wrapped['data'] ?? null)) {
            return null;
        }

        $product = $wrapped['data'];
        if (($product['product_type'] ?? '') === 'BundleProduct') {
            return null;
        }

        $final = $this->finalizeProductDetailsResponse($product, $graphLocale);
        $this->ksCache()->put($readyKey, ['hit' => true, 'data' => $final], $hitTtl);

        return $final;
    }

    private function logSlowProductLookup(string $slug, float $started, string $source): void
    {
        $ms = (microtime(true) - $started) * 1000;
        if ($ms < 400) {
            return;
        }
        Log::warning('Keepersport PDP slow lookup', [
            'slug' => $slug,
            'source' => $source,
            'ms' => (int) round($ms),
        ]);
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function finalizeProductDetailsResponse(array $product, string $graphLocale, bool $enrichPublicOffer = true): array
    {
        // المسار الحرج: لا REST ولا upstream recommended — جاهز فوري بعد warm/slug
        // الكمية الشرائية من GraphQL maximumBuyableQuantity (Stock.quantity أزيل)
        $product = $this->ensureProductVariantBuyableQuantities($product, $graphLocale);
        if ($enrichPublicOffer) {
            $product = $this->enrichProductOfferFromPublicCatalog($product, $graphLocale);
        }
        $fetchStockOnMiss = (bool) config('services.keepersport_graphql.rest_stock_fetch_on_miss', false);
        $product['variants'] = $this->enrichVariantsWithRestStock($product['variants'] ?? [], $fetchStockOnMiss);
        $product['recommended_products'] = [];
        $product = $this->alignVariantPricesWithProductSale($product);
        if ($enrichPublicOffer) {
            $product['public_offer_checked'] = true;
        }

        return app(WebsitePricingService::class)->applyToProductDetails($product);
    }

    /**
     * API الـ SA غالباً بدون offer/countDownTo — نكمّل السعر والمؤقت من keepersport.net العام.
     *
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function enrichProductOfferFromPublicCatalog(array $product, string $graphLocale): array
    {
        $needsPromo = empty($product['promo_ends_at']);
        $needsSale = empty($product['price']['old'])
            || (float) ($product['price']['current'] ?? 0) <= 0;

        if (! $needsPromo && ! $needsSale) {
            return $product;
        }

        $sku = trim((string) ($product['sku'] ?? ''));
        if ($sku === '') {
            return $product;
        }

        $cacheKey = sprintf('keepersport.public_offer.%s.%s', $graphLocale, md5($sku));
        $ttl = max(300, (int) config('services.keepersport_graphql.product_slug_cache_ttl', 7200));
        $missTtl = 180;
        $cached = $this->ksCache()->get($cacheKey);
        $publicNode = null;
        if (is_array($cached) && ($cached['hit'] ?? false) === true && is_array($cached['data'] ?? null)) {
            $publicNode = $cached['data'];
        } elseif (! is_array($cached) || ($cached['hit'] ?? null) !== false) {
            try {
                $fetched = $this->graphqlCatalog()->fetchPublicProductNodeBySku($graphLocale, $sku);
                if (is_array($fetched)) {
                    $this->ksCache()->put($cacheKey, ['hit' => true, 'data' => $fetched], $ttl);
                    $publicNode = $fetched;
                } else {
                    $this->ksCache()->put($cacheKey, ['hit' => false, 'data' => null], $missTtl);
                }
            } catch (\Throwable $e) {
                Log::debug('Keepersport public offer enrich failed', [
                    'sku' => $sku,
                    'error' => $e->getMessage(),
                ]);
                $this->ksCache()->put($cacheKey, ['hit' => false, 'data' => null], $missTtl);
            }
        }

        if (! is_array($publicNode)) {
            return $product;
        }

        return $this->mergePublicOfferNodeIntoProduct($product, $publicNode);
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  array<string, mixed>  $publicNode
     * @return array<string, mixed>
     */
    private function mergePublicOfferNodeIntoProduct(array $product, array $publicNode): array
    {
        $needsPromo = empty($product['promo_ends_at']);
        $publicPrice = $this->extractPrice($publicNode);
        $publicPromo = $this->extractPromoEndsAt($publicNode);

        $localCurrent = (float) ($product['price']['current'] ?? 0);
        $localOld = isset($product['price']['old']) ? (float) $product['price']['old'] : null;
        $publicCurrent = (float) ($publicPrice['current'] ?? 0);
        $publicOld = isset($publicPrice['old']) ? (float) $publicPrice['old'] : null;

        if ($publicCurrent > 0 && $publicOld !== null && $publicOld > $publicCurrent) {
            if ($localOld === null || $localOld <= $localCurrent || $publicCurrent < $localCurrent) {
                $product['price']['current'] = $publicCurrent;
                $product['price']['old'] = $publicOld;
                if (! empty($publicPrice['currency'])) {
                    $product['price']['currency'] = $publicPrice['currency'];
                }
            }
        } elseif ($localCurrent <= 0 && $publicCurrent > 0) {
            $product['price']['current'] = $publicCurrent;
            $product['price']['old'] = $publicOld;
            if (! empty($publicPrice['currency'])) {
                $product['price']['currency'] = $publicPrice['currency'];
            }
        }

        if ($needsPromo && is_string($publicPromo) && $publicPromo !== '') {
            $product['promo_ends_at'] = $publicPromo;
        }

        return $product;
    }

    /**
     * بعد تحديد سعر الخصم على المنتج: لا تُبقِ المقاسات على listPrice الأعلى.
     *
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function alignVariantPricesWithProductSale(array $product): array
    {
        $sale = (float) ($product['price']['current'] ?? 0);
        $old = isset($product['price']['old']) ? (float) $product['price']['old'] : null;
        if ($sale <= 0 || $old === null || $old <= $sale) {
            return $product;
        }

        $variants = $product['variants'] ?? [];
        if (! is_array($variants) || $variants === []) {
            return $product;
        }

        foreach ($variants as $i => $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $variantPrice = (float) ($variant['price'] ?? 0);
            // المقاس يحمل غالباً السعر قبل الخصم فقط
            if ($variantPrice <= 0 || abs($variantPrice - $old) < 0.05 || $variantPrice > $sale) {
                $variants[$i]['price'] = $sale;
                if (! empty($product['price']['currency'])) {
                    $variants[$i]['currency'] = $product['price']['currency'];
                }
            }
        }

        $product['variants'] = $variants;

        return $product;
    }

    /**
     * إذا كانت كميات المقاسات ناقصة (كاش قديم)، جلب maximumBuyableQuantity من GraphQL ودمجها.
     *
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function ensureProductVariantBuyableQuantities(array $product, string $graphLocale): array
    {
        $variants = $product['variants'] ?? [];
        if (! is_array($variants) || $variants === []) {
            return $product;
        }

        $needsRefresh = false;
        foreach ($variants as $variant) {
            if (! is_array($variant)) {
                continue;
            }
            if (($variant['in_stock'] ?? false) && ! is_numeric($variant['quantity'] ?? null)) {
                $needsRefresh = true;
                break;
            }
        }

        if (! $needsRefresh) {
            return $product;
        }

        // fulltext على SKU الأساس — article_key رقم ترتيب فقط وليس للبحث
        $sku = (string) ($product['sku'] ?? '');
        $query = $sku !== '' ? explode('/', $sku)[0] : '';
        if ($query === '') {
            foreach ($variants as $variant) {
                $sku = (string) ($variant['sku'] ?? '');
                if ($sku !== '') {
                    $query = explode('/', $sku)[0];
                    break;
                }
            }
        }
        if ($query === '') {
            return $product;
        }

        try {
            $edges = $this->graphqlCatalog()->fetchProductEdges($graphLocale, 8, [], $query, false, 1);
        } catch (\Throwable $e) {
            Log::warning('Keepersport buyable qty refresh failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return $product;
        }

        $qtyBySku = [];
        $inStockBySku = [];
        foreach ($edges as $edge) {
            $node = is_array($edge['node'] ?? null) ? $edge['node'] : (is_array($edge) ? $edge : null);
            if (! is_array($node)) {
                continue;
            }
            foreach ($node['variants'] ?? [] as $variantNode) {
                $vp = is_array($variantNode['product'] ?? null) ? $variantNode['product'] : [];
                $variantSku = (string) ($vp['sku'] ?? '');
                if ($variantSku === '') {
                    continue;
                }
                $rawQty = Arr::get($vp, 'stock.maximumBuyableQuantity', Arr::get($vp, 'stock.quantity'));
                if (! is_numeric($rawQty)) {
                    continue;
                }
                $qty = max(0, (int) $rawQty);
                $qtyBySku[$variantSku] = $qty;
                $inStockBySku[$variantSku] = (bool) Arr::get($vp, 'stock.isInStock', false) && $qty > 0;
            }
        }

        if ($qtyBySku === []) {
            return $product;
        }

        foreach ($variants as $index => $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $variantSku = (string) ($variant['sku'] ?? '');
            if ($variantSku === '' || ! array_key_exists($variantSku, $qtyBySku)) {
                continue;
            }
            $variants[$index]['quantity'] = $qtyBySku[$variantSku];
            if (array_key_exists($variantSku, $inStockBySku)) {
                $variants[$index]['in_stock'] = $inStockBySku[$variantSku];
            } elseif (($variants[$index]['in_stock'] ?? false) && $qtyBySku[$variantSku] <= 0) {
                $variants[$index]['in_stock'] = false;
            }
        }

        $product['variants'] = array_values($variants);

        return $product;
    }

    /**
     * منتجات موصى بها لصفحة منتج حسب slug (طلب منفصل حتى لا يبطئ فتح PDP).
     *
     * @return list<array<string, mixed>>
     */
    public function recommendedProductsBySlug(string $slug, ?string $graphLocale = null): array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $normalizedSlug = $this->normalizeProductSlug($slug);
        if ($normalizedSlug === '') {
            return [];
        }

        $pricingSuffix = app(WebsitePricingService::class)->pricingCacheSuffix();
        $cacheKey = sprintf(
            'keepersport.product_by_slug.%s.%s%s',
            $graphLocale,
            md5($normalizedSlug),
            $pricingSuffix
        );

        $product = null;
        $wrapped = $this->ksCache()->get($cacheKey);
        if (is_array($wrapped) && ($wrapped['hit'] ?? false) === true && is_array($wrapped['data'] ?? null)) {
            $product = $wrapped['data'];
        }
        if (! is_array($product)) {
            $product = $this->findProductBySlugUncached($graphLocale, $normalizedSlug);
            if (is_array($product)) {
                $hitTtl = max(120, (int) config('services.keepersport_graphql.product_slug_cache_ttl', 3600));
                $this->ksCache()->put($cacheKey, [
                    'hit' => true,
                    'data' => $product,
                ], $hitTtl);
            }
        }

        if (! is_array($product) || ($product['product_type'] ?? '') === 'BundleProduct') {
            return [];
        }

        // جرّب الكاش أولاً، ثم اقتراح محلي سريع من الكتالوج، ثم upstream إن لزم
        $cached = $this->recommendedProductsForProduct($product, $graphLocale, false);
        if ($cached !== []) {
            return $this->applyWebsitePricingToProductCards($cached);
        }

        $local = $this->localRelatedProductCards($product, $graphLocale, 12);
        if ($local !== []) {
            return $this->applyWebsitePricingToProductCards($local);
        }

        return $this->applyWebsitePricingToProductCards(
            $this->recommendedProductsForProduct($product, $graphLocale, true)
        );
    }

    /**
     * توحيد أسعار البطاقات مع صفحة المنتج (EUR→SAR + ضريبة).
     *
     * @param  list<array<string, mixed>>  $cards
     * @return list<array<string, mixed>>
     */
    private function applyWebsitePricingToProductCards(array $cards): array
    {
        if ($cards === []) {
            return [];
        }

        return app(WebsitePricingService::class)->applyToProductCards($cards);
    }

    /**
     * منتجات موصى بها من صفحة upstream (نفس قائمة SKU في data-query-id على keepersport.net).
     *
     * @param  array<string, mixed>  $product
     * @return list<array<string, mixed>>
     */
    public function recommendedProductsForProduct(array $product, string $graphLocale, ?bool $fetchOnMiss = null): array
    {
        $sku = trim((string) ($product['sku'] ?? ''));
        if ($sku === '') {
            return [];
        }

        $ttl = max(300, (int) config('services.keepersport_graphql.recommended_cache_ttl', 3600));
        $emptyTtl = min(120, $ttl);
        $pricingSuffix = app(WebsitePricingService::class)->pricingCacheSuffix();
        $cacheKey = sprintf('keepersport.recommended.%s.%s%s', $graphLocale, md5($sku), $pricingSuffix);

        $fetchOnMiss ??= (bool) config('services.keepersport_graphql.recommended_fetch_on_miss', false);

        $cached = $this->ksCache()->get($cacheKey);
        // [] الفارغ لا يُعتبر نجاحاً عند طلب الجلب — وإلا تختفي الموصى بها حتى انتهاء TTL
        if (is_array($cached) && ($cached !== [] || ! $fetchOnMiss)) {
            return $cached;
        }

        if (! $fetchOnMiss) {
            return [];
        }

        $skus = $this->fetchRecommendedSkusFromUpstreamPage($product);
        if ($skus === []) {
            $this->ksCache()->put($cacheKey, [], $emptyTtl);

            return [];
        }

        $skus = array_values(array_filter(
            $skus,
            fn (string $candidate) => strcasecmp($this->normalizeRecommendationSku($candidate), $sku) !== 0
        ));

        $cards = $this->productCardsBySkusOrdered($skus, $graphLocale);
        $cards = $this->applyWebsitePricingToProductCards($cards);
        $this->ksCache()->put($cacheKey, $cards, $cards === [] ? $emptyTtl : $ttl);

        return $cards;
    }

    /**
     * @param  array<string, mixed>  $product
     * @return list<string>
     */
    private function fetchRecommendedSkusFromUpstreamPage(array $product): array
    {
        $html = $this->fetchUpstreamProductPageHtml($product);
        if ($html === null || $html === '') {
            return [];
        }

        return $this->parseRecommendedSkusFromHtml($html);
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function fetchUpstreamProductPageHtml(array $product): ?string
    {
        $candidates = [];
        $slug = trim((string) ($product['slug'] ?? ''));
        if ($slug !== '') {
            $base = rtrim((string) config('services.keepersport_graphql.public_product_page_base', 'https://www.keepersport.net'), '/');
            $candidates[] = $base.'/'.$slug.'.html';
        }
        $url = trim((string) ($product['url'] ?? ''));
        if ($url !== '') {
            $candidates[] = $url;
        }

        foreach (array_values(array_unique($candidates)) as $candidate) {
            $html = $this->httpGetUpstreamProductPage($candidate);
            if ($html !== null && str_contains($html, 'product-list/product-list')) {
                return $html;
            }
        }

        return null;
    }

    private function httpGetUpstreamProductPage(string $url): ?string
    {
        $timeout = max(5, (int) config('services.keepersport_graphql.recommended_page_timeout', 20));
        $connectTimeout = min($timeout, max(5, (int) config('services.keepersport_graphql.connect_timeout', 30)));

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'KeeperCatalog/1.0',
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => (string) config('services.keepersport_graphql.catalog_accept_language', 'en'),
            ])
                ->withOptions(array_merge(
                    [
                        'connect_timeout' => $connectTimeout,
                        'timeout' => $timeout,
                    ],
                    $this->sslClientOptions()
                ))
                ->get($url);

            if ($response->failed()) {
                Log::debug('Keepersport upstream PDP HTML fetch failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $body = (string) $response->body();

            return $body !== '' ? $body : null;
        } catch (\Throwable $e) {
            Log::debug('Keepersport upstream PDP HTML exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return list<string>
     */
    private function parseRecommendedSkusFromHtml(string $html): array
    {
        $encoded = null;
        if (preg_match(
            '/data-init="module\/content-element\/product-list\/product-list"[^>]*\sdata-query-id="([^"]+)"/i',
            $html,
            $matches
        )) {
            $encoded = $matches[1];
        } elseif (preg_match(
            '/data-query-id="([^"]+)"[^>]*\sdata-init="module\/content-element\/product-list\/product-list"/i',
            $html,
            $matches
        )) {
            $encoded = $matches[1];
        }

        if ($encoded === null || $encoded === '') {
            return [];
        }

        $json = base64_decode($encoded, true);
        if ($json === false || $json === '') {
            return [];
        }

        $data = json_decode($json, true);
        if (! is_array($data)) {
            return [];
        }

        $skusRaw = trim((string) ($data['skus'] ?? ''));
        if ($skusRaw === '') {
            return [];
        }

        $out = [];
        foreach (explode(',', $skusRaw) as $part) {
            $normalized = $this->normalizeRecommendationSku($part);
            if ($normalized !== '') {
                $out[] = $normalized;
            }
        }

        return array_values(array_unique($out));
    }

    private function normalizeRecommendationSku(string $sku): string
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

    /**
     * اقتراحات سريعة من كاش الكتالوج (نفس الماركة/التصنيف) بدون upstream.
     *
     * @param  array<string, mixed>  $product
     * @return list<array<string, mixed>>
     */
    private function localRelatedProductCards(array $product, string $graphLocale, int $limit = 12): array
    {
        $sku = $this->normalizeRecommendationSku((string) ($product['sku'] ?? ''));
        $brandId = trim((string) ($product['brand']['id'] ?? ''));
        $brandName = Str::lower(trim((string) ($product['brand']['name'] ?? '')));
        $catIds = [];
        foreach ($product['categories'] ?? [] as $cat) {
            $id = trim((string) ($cat['id'] ?? ''));
            if ($id !== '') {
                $catIds[$id] = true;
            }
            foreach ($cat['children'] ?? [] as $child) {
                $cid = trim((string) ($child['id'] ?? ''));
                if ($cid !== '') {
                    $catIds[$cid] = true;
                }
            }
        }
        $catIdList = array_keys($catIds);

        $nodes = $this->getCatalogEdgesFromCacheOnly($graphLocale);
        if ($nodes === []) {
            return [];
        }

        $scored = [];
        foreach ($nodes as $node) {
            if (! is_array($node) || ! $this->shouldExposeCatalogNode($node)) {
                continue;
            }
            $nodeSku = $this->normalizeRecommendationSku((string) ($node['sku'] ?? ''));
            if ($nodeSku === '' || ($sku !== '' && strcasecmp($nodeSku, $sku) === 0)) {
                continue;
            }
            $score = 0;
            $nodeBrandId = trim((string) Arr::get($node, 'brand.id', ''));
            $nodeBrandName = Str::lower(trim((string) Arr::get($node, 'brand.name', '')));
            if ($brandId !== '' && $nodeBrandId !== '' && $brandId === $nodeBrandId) {
                $score += 5;
            } elseif ($brandName !== '' && $nodeBrandName !== '' && $brandName === $nodeBrandName) {
                $score += 4;
            }
            if ($catIdList !== []) {
                foreach ($node['categories'] ?? [] as $cat) {
                    $id = trim((string) ($cat['id'] ?? ''));
                    if ($id !== '' && isset($catIds[$id])) {
                        $score += 3;
                        break;
                    }
                    foreach ($cat['subCategories'] ?? [] as $sub) {
                        $sid = trim((string) ($sub['id'] ?? ''));
                        if ($sid !== '' && isset($catIds[$sid])) {
                            $score += 3;
                            break 2;
                        }
                    }
                }
            }
            if ($score < 3) {
                continue;
            }
            $scored[] = ['score' => $score, 'node' => $node];
        }

        usort($scored, fn ($a, $b) => ($b['score'] <=> $a['score']));
        $cards = [];
        foreach (array_slice($scored, 0, max(1, $limit)) as $row) {
            $cards[] = $this->mapProductCard($row['node']);
        }

        if ($cards !== []) {
            $cards = $this->applyWebsitePricingToProductCards($cards);
            $ttl = max(300, (int) config('services.keepersport_graphql.recommended_cache_ttl', 3600));
            $pricingSuffix = app(WebsitePricingService::class)->pricingCacheSuffix();
            $cacheKey = sprintf('keepersport.recommended.%s.%s%s', $graphLocale, md5($sku), $pricingSuffix);
            $this->ksCache()->put($cacheKey, $cards, min(900, $ttl));
        }

        return $cards;
    }

    /**
     * @param  list<string>  $skus
     * @return list<array<string, mixed>>
     */
    private function productCardsBySkusOrdered(array $skus, string $graphLocale): array
    {
        $skus = array_values(array_unique(array_filter(array_map(
            fn (string $s) => $this->normalizeRecommendationSku($s),
            $skus
        ))));
        if ($skus === []) {
            return [];
        }

        $nodesBySku = [];
        foreach ($this->graphqlCatalog()->fetchProductNodesBySkus($graphLocale, $skus) as $node) {
            if (! $this->shouldExposeCatalogNode($node)) {
                continue;
            }
            $nodeSku = $this->normalizeRecommendationSku((string) ($node['sku'] ?? ''));
            if ($nodeSku !== '') {
                $nodesBySku[$nodeSku] = $node;
            }
        }

        $cards = [];
        foreach ($skus as $sku) {
            $node = $nodesBySku[$sku] ?? null;
            if (! is_array($node)) {
                continue;
            }
            $cards[] = $this->mapProductCard($node);
        }

        return $cards;
    }

    /**
     * مطابقة slug أو sku بعد تطبيع (بدون .html).
     *
     * @param  array<string, mixed>  $product
     */
    private function productMatchesSlug(array $product, string $normalizedSlug): bool
    {
        $productSlug = Str::lower((string) ($product['slug'] ?? ''));
        if (str_ends_with($productSlug, '.html')) {
            $productSlug = substr($productSlug, 0, -5);
        }

        return $productSlug === $normalizedSlug
            || Str::lower((string) ($product['sku'] ?? '')) === $normalizedSlug;
    }

    /**
     * جلب منتج واحد: من كاش الكتالوج (بدون قفل/upstream) أولاً، ثم fulltext محدود.
     */
    private function findProductBySlugUncached(string $graphLocale, string $normalizedSlug): ?array
    {
        $indexedNode = $this->findProductNodeInSlugIndex($graphLocale, $normalizedSlug);
        if (is_array($indexedNode)) {
            $product = $this->mapProductDetails($indexedNode);
            if ($this->productMatchesSlug($product, $normalizedSlug)) {
                return $product;
            }
        }

        $cachedNodes = $this->getCatalogEdgesFromCacheOnly($graphLocale);
        if ($cachedNodes !== [] && ! is_array($this->ksCache()->get($this->productSlugNodeIndexCacheKey($graphLocale)))) {
            $this->refreshProductSlugNodeIndex($graphLocale, $cachedNodes);
            $indexedNode = $this->findProductNodeInSlugIndex($graphLocale, $normalizedSlug);
            if (is_array($indexedNode)) {
                $product = $this->mapProductDetails($indexedNode);
                if ($this->productMatchesSlug($product, $normalizedSlug)) {
                    return $product;
                }
            }
        }

        // 1) كتالوج ساخن أو stale فقط — لا ننتظر catalog lock (كان يعلّق PDP دقائق)
        try {
            $fallbackNode = null;
            foreach ($cachedNodes as $node) {
                if (! is_array($node) || ! $this->shouldExposeCatalogNode($node)) {
                    continue;
                }
                $match = $this->catalogNodeQuickMatchesSlug($node, $normalizedSlug);
                if ($match === 2) {
                    return $this->mapProductDetails($node);
                }
                if ($match === 1 && $fallbackNode === null) {
                    $fallbackNode = $node;
                }
            }
            if ($fallbackNode !== null) {
                $product = $this->mapProductDetails($fallbackNode);
                if ($this->productMatchesSlug($product, $normalizedSlug)) {
                    return $product;
                }
            }
            foreach ($cachedNodes as $node) {
                if (! is_array($node) || ! $this->shouldExposeCatalogNode($node)) {
                    continue;
                }
                $url = Str::lower((string) ($node['url'] ?? ''));
                if ($url === '' || ! str_contains($url, $normalizedSlug)) {
                    continue;
                }
                $product = $this->mapProductDetails($node);
                if ($this->productMatchesSlug($product, $normalizedSlug)) {
                    return $product;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Keepersport PDP local catalog lookup failed', [
                'locale' => $graphLocale,
                'slug' => $normalizedSlug,
                'error' => $e->getMessage(),
            ]);
        }

        $direct = $this->findProductBySlugViaDirectGraphql($graphLocale, $normalizedSlug);
        if (is_array($direct)) {
            return $direct;
        }

        $allowFulltext = (bool) config('services.keepersport_graphql.pdp_fulltext_lookup', true);
        if (! $allowFulltext) {
            return null;
        }

        // 2) fulltext محدود جداً (صفحة واحدة) — يمنع 404 دون انتظار قفل الكتالوج الكامل
        $maxFtPages = 1;
        $ftLimit = min(100, max(40, (int) ($this->defaultScanCap() * 0.15)));
        $terms = array_slice($this->slugFulltextSearchCandidates($normalizedSlug), 0, 1);

        foreach ($terms as $term) {
            if (strlen($term) < 3) {
                continue;
            }
            try {
                $nodes = $this->fetchCatalogEdges($graphLocale, $ftLimit, [], $term, $maxFtPages, 8);
            } catch (\Throwable $e) {
                Log::warning('Keepersport PDP fulltext catalog fetch failed', [
                    'locale' => $graphLocale,
                    'term' => $term,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }
            foreach ($nodes as $node) {
                if (! is_array($node) || ! $this->shouldExposeCatalogNode($node)) {
                    continue;
                }
                if ($this->catalogNodeQuickMatchesSlug($node, $normalizedSlug) === 0
                    && ! str_contains(Str::lower((string) ($node['url'] ?? '')), $normalizedSlug)
                ) {
                    continue;
                }
                $product = $this->mapProductDetails($node);
                if ($this->productMatchesSlug($product, $normalizedSlug)) {
                    return $product;
                }
            }
        }

        return null;
    }

    /**
     * قراءة عقد الكتالوج من الكاش/stale فقط بدون انتظار قفل أو طلب GraphQL.
     *
     * @return list<array<string, mixed>>
     */
    private function findProductBySlugViaDirectGraphql(string $graphLocale, string $normalizedSlug): ?array
    {
        if (! (bool) config('services.keepersport_graphql.pdp_direct_graphql_lookup', true)) {
            return null;
        }

        $terms = array_slice($this->slugFulltextSearchCandidates($normalizedSlug), 0, 2);
        foreach ($terms as $term) {
            if (strlen($term) < 3) {
                continue;
            }
            try {
                $edges = $this->graphqlCatalog()->fetchProductEdges(
                    $graphLocale,
                    60,
                    [],
                    $term,
                    false,
                    1
                );
            } catch (\Throwable $e) {
                Log::debug('Keepersport PDP direct GraphQL lookup failed', [
                    'locale' => $graphLocale,
                    'term' => $term,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            foreach ($edges as $edge) {
                $node = is_array($edge['node'] ?? null) ? $edge['node'] : null;
                if (! is_array($node) || ! $this->shouldExposeCatalogNode($node)) {
                    continue;
                }
                if ($this->catalogNodeQuickMatchesSlug($node, $normalizedSlug) === 0
                    && ! str_contains(Str::lower((string) ($node['url'] ?? '')), $normalizedSlug)
                ) {
                    continue;
                }
                $product = $this->mapProductDetails($node);
                if ($this->productMatchesSlug($product, $normalizedSlug)) {
                    return $product;
                }
            }
        }

        return null;
    }

    private function getCatalogEdgesFromCacheOnly(string $graphLocale): array
    {
        $limits = array_values(array_unique(array_filter([
            $this->defaultScanCap(),
            max($this->defaultScanCap(), (int) config('services.keepersport_graphql.warm_products_limit', 800)),
            1200,
            800,
            400,
        ])));

        foreach ($limits as $limit) {
            $cacheKey = sprintf('keepersport.catalog.%s.%s', $graphLocale, $limit);
            $cached = $this->ksCache()->get($cacheKey);
            if (is_array($cached) && $cached !== []) {
                return $cached;
            }
        }

        foreach ($limits as $limit) {
            $staleKey = sprintf('keepersport.catalog.stale.%s.%s', $graphLocale, $limit);
            $stale = $this->ksCache()->get($staleKey);
            if (is_array($stale) && $stale !== []) {
                return $stale;
            }
        }

        $allKey = sprintf('keepersport.catalog.%s.all', $graphLocale);
        $all = $this->ksCache()->get($allKey);
        if (is_array($all) && $all !== []) {
            return $all;
        }
        $allStale = $this->ksCache()->get(sprintf('keepersport.catalog.stale.%s.all', $graphLocale));
        if (is_array($allStale) && $allStale !== []) {
            return $allStale;
        }

        return [];
    }

    /**
     * مطابقة slug/sku من عقدة GraphQL الخام.
     * 2 = تطابق تام، 1 = محتمل، 0 = لا.
     */
    private function catalogNodeQuickMatchesSlug(array $node, string $normalizedSlug): int
    {
        $sku = Str::lower(trim((string) ($node['sku'] ?? '')));
        if ($sku !== '' && $sku === $normalizedSlug) {
            return 2;
        }

        $url = trim((string) ($node['url'] ?? ''));
        if ($url === '') {
            return 0;
        }
        $path = (string) parse_url($url, PHP_URL_PATH);
        $pathSlug = $this->normalizeProductSlug(trim((string) basename($path), '/'));
        if ($pathSlug !== '' && $pathSlug === $normalizedSlug) {
            return 2;
        }
        $pathLower = Str::lower($path);
        if ($normalizedSlug !== '' && str_contains($pathLower, $normalizedSlug)) {
            return 1;
        }

        return 0;
    }

    /**
     * @return list<string>
     */
    private function slugFulltextSearchCandidates(string $normalizedSlug): array
    {
        $specific = [];
        $broad = [];
        $spaced = trim(str_replace(['-', '_', '.'], ' ', $normalizedSlug));
        $words = $spaced !== '' ? preg_split('/\s+/u', $spaced, -1, PREG_SPLIT_NO_EMPTY) : [];
        if (is_array($words) && count($words) >= 2) {
            $specific[] = implode(' ', array_slice($words, -2));
        }
        if (is_array($words) && count($words) > 5) {
            $specific[] = implode(' ', array_slice($words, -5));
        }
        if (preg_match('/^[a-z0-9]{5,32}$/i', $normalizedSlug)) {
            $specific[] = $normalizedSlug;
        }
        if (strlen($spaced) >= 3) {
            $broad[] = $spaced;
        }

        return array_values(array_unique(array_filter(
            array_merge($specific, $broad),
            fn (string $s) => strlen($s) >= 3
        )));
    }

    public function brands(?string $graphLocale = null, ?int $scanLimit = null): array
    {
        $graphLocale ??= (string) config('services.keepersport_graphql.catalog_accept_language', 'it');
        $scanLimit = max(120, min($this->defaultScanCap(), (int) ($scanLimit ?? 500)));

        $products = collect($this->fetchCatalogEdges($graphLocale, $scanLimit))
            ->map(fn (array $edge) => $this->mapProductCard($edge))
            ->filter(fn (array $product) => ! empty($product['sku']))
            ->values();

        return $this->buildFacetsExtended($products)['brands'] ?? [];
    }

    private function sortProducts(Collection $products, string $sort): Collection
    {
        return match ($sort) {
            'price_asc' => $products->sortBy(fn ($p) => (float) ($p['price']['current'] ?? 0))->values(),
            'price_desc' => $products->sortByDesc(fn ($p) => (float) ($p['price']['current'] ?? 0))->values(),
            'name_asc' => $products->sortBy(fn ($p) => Str::lower((string) ($p['name'] ?? '')))->values(),
            'name_desc' => $products->sortByDesc(fn ($p) => Str::lower((string) ($p['name'] ?? '')))->values(),
            'newest', 'relevance' => $products
                ->sort(function (array $a, array $b) {
                    $sortA = (int) ($a['sort_order'] ?? PHP_INT_MIN);
                    $sortB = (int) ($b['sort_order'] ?? PHP_INT_MIN);
                    if ($sortA !== $sortB) {
                        return $sortB <=> $sortA;
                    }

                    $dateA = (string) ($a['release_date'] ?? '');
                    $dateB = (string) ($b['release_date'] ?? '');
                    if ($dateA !== $dateB) {
                        return strcmp($dateB, $dateA);
                    }

                    $ka = (int) ($a['article_key'] ?? $this->extractProductArticleKeyFromUrl((string) ($a['url'] ?? '')));
                    $kb = (int) ($b['article_key'] ?? $this->extractProductArticleKeyFromUrl((string) ($b['url'] ?? '')));
                    if ($ka !== $kb) {
                        return $kb <=> $ka;
                    }

                    return strnatcasecmp((string) ($b['sku'] ?? ''), (string) ($a['sku'] ?? ''));
                })
                ->values(),
            'discount_desc' => $products
                ->sortByDesc(fn ($p) => $this->productDiscountRatio($p))
                ->values(),
            default => $products,
        };
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function productDiscountRatio(array $product): float
    {
        $old = $product['price']['old'] ?? null;
        $current = (float) ($product['price']['current'] ?? 0);
        $oldF = $old !== null ? (float) $old : 0.0;
        if ($oldF <= 0.0 || $current <= 0.0 || $oldF <= $current) {
            return 0.0;
        }

        return ($oldF - $current) / $oldF;
    }

    /**
     * فلاتر من مجموعة المنتجات الحالية (قبل تطبيق brand/size/color على القائمة).
     *
     * @param  Collection<int, array<string, mixed>>  $products
     */
    private function buildFacetsExtended(Collection $products): array
    {
        $brands = $products
            ->pluck('brand')
            ->filter(fn ($brand) => ! empty($brand['id']) && ! empty($brand['name']))
            ->groupBy('id')
            ->map(function (Collection $rows) {
                /** @var array<string, mixed> $first */
                $first = $rows->first();
                $first['count'] = $rows->count();

                return $first;
            })
            ->values()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $sizeToCount = [];
        foreach ($products as $p) {
            foreach ($p['sizes'] ?? [] as $raw) {
                $s = trim((string) $raw);
                if ($s === '') {
                    continue;
                }
                $sizeToCount[$s] = ($sizeToCount[$s] ?? 0) + 1;
            }
        }
        ksort($sizeToCount, SORT_NATURAL | SORT_FLAG_CASE);
        $sizes = collect($sizeToCount)
            ->map(fn (int $count, string $s) => ['key' => $s, 'label' => $s, 'count' => $count])
            ->values()
            ->all();

        $colorKey = fn ($c) => Str::lower(trim(($c['name'] ?? '').'|'.($c['hex'] ?? '')));
        $colorBuckets = [];
        foreach ($products as $p) {
            $seen = [];
            foreach ($p['colors'] ?? [] as $c) {
                if (empty($c['name']) && empty($c['hex'])) {
                    continue;
                }
                $k = $colorKey($c);
                if ($k === '|') {
                    continue;
                }
                if (isset($seen[$k])) {
                    continue;
                }
                $seen[$k] = true;
                if (! isset($colorBuckets[$k])) {
                    $colorBuckets[$k] = [
                        'name' => (string) ($c['name'] ?? ''),
                        'hex' => (string) ($c['hex'] ?? ''),
                        'count' => 0,
                    ];
                }
                $colorBuckets[$k]['count']++;
            }
        }
        $colors = collect($colorBuckets)
            ->sortBy(fn ($c) => Str::lower((string) (($c['name'] ?? '') !== '' ? $c['name'] : ($c['hex'] ?? ''))), SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $attrBuckets = [];
        foreach ($products as $p) {
            $seenPair = [];
            foreach ($p['attribute_facets'] ?? [] as $af) {
                $ak = (string) ($af['attr_key'] ?? '');
                if ($ak === '') {
                    continue;
                }
                $akNorm = Str::lower($ak);
                if (! isset($attrBuckets[$akNorm])) {
                    $attrBuckets[$akNorm] = [
                        'key' => $ak,
                        'label' => (string) ($af['attr_label'] ?? $ak),
                        'values' => [],
                    ];
                }
                $vk = (string) ($af['value_key'] ?? '');
                if ($vk === '') {
                    continue;
                }
                $vkNorm = Str::lower($vk);
                $pair = $akNorm.'|'.$vkNorm;
                if (isset($seenPair[$pair])) {
                    continue;
                }
                $seenPair[$pair] = true;
                if (! isset($attrBuckets[$akNorm]['values'][$vkNorm])) {
                    $attrBuckets[$akNorm]['values'][$vkNorm] = [
                        'key' => $vk,
                        'label' => (string) ($af['value_label'] ?? $vk),
                        'count' => 0,
                    ];
                }
                $attrBuckets[$akNorm]['values'][$vkNorm]['count']++;
            }
        }
        foreach ($attrBuckets as &$bucket) {
            $bucket['values'] = collect($bucket['values'])->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
        }
        unset($bucket);

        $attributes = collect($attrBuckets)
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return [
            'brands' => $brands,
            'sizes' => $sizes,
            'colors' => $colors,
            'attributes' => $attributes,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $allMapped
     * @return Collection<int, array<string, mixed>>
     */
    private function looseGloveCategoryFallback(Collection $allMapped): Collection
    {
        // fallback محافظ جدًا للقفازات فقط
        $hints = ['glove', 'guanti', 'handschuh', 'handschuhe', 'keeper-handschuh', 'tw-handschu'];

        return $allMapped->filter(function (array $product) use ($hints) {
            $name = Str::lower((string) ($product['name'] ?? ''));
            foreach ($hints as $h) {
                if (Str::contains($name, $h)) {
                    return true;
                }
            }
            foreach ($product['categories'] ?? [] as $cat) {
                $cn = Str::lower((string) ($cat['name'] ?? ''));
                foreach ($hints as $h) {
                    if (Str::contains($cn, $h)) {
                        return true;
                    }
                }
            }

            return false;
        });
    }

    /**
     * @param Collection<int, array<string,mixed>> $products
     */
    private function refineMainCategoryResults(Collection $products, string $requestedCategory): Collection
    {
        if ($requestedCategory === '' || ! isset(self::MAIN_CATEGORY_KEYWORDS[$requestedCategory])) {
            return $products;
        }

        $keywords = self::MAIN_CATEGORY_KEYWORDS[$requestedCategory];
        $refined = $products->filter(function (array $product) use ($keywords) {
            $name = Str::lower((string) ($product['name'] ?? ''));
            $cats = collect($product['categories'] ?? [])
                ->map(fn (array $c) => Str::lower((string) ($c['name'] ?? '')))
                ->implode(' ');
            $text = $name . ' ' . $cats;

            foreach ($keywords as $kw) {
                if (Str::contains($text, $kw)) {
                    return true;
                }
            }

            return false;
        })->values();

        // لو refinement صار عدواني أكثر من اللازم، نرجع للأصل.
        if ($refined->isEmpty() && $products->isNotEmpty()) {
            return $products;
        }

        return $refined;
    }

    /**
     * fallback عام مبني على كلمات القسم الرئيسي.
     *
     * @param Collection<int, array<string,mixed>> $allMapped
     * @param list<string> $keywords
     * @return Collection<int, array<string,mixed>>
     */
    private function looseMainCategoryFallback(Collection $allMapped, array $keywords): Collection
    {
        return $allMapped->filter(function (array $product) use ($keywords) {
            $name = Str::lower((string) ($product['name'] ?? ''));
            $cats = collect($product['categories'] ?? [])
                ->map(fn (array $c) => Str::lower((string) ($c['name'] ?? '')))
                ->implode(' ');
            $text = $name . ' ' . $cats;

            foreach ($keywords as $kw) {
                if (Str::contains($text, Str::lower($kw))) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    /**
     * مفتاح ترتيب «الأحدث»: رقم المقالة من مسار keepersport (مثل …-f991.html) — كلما زاد غالبًا أحدث.
     */
    private function extractProductArticleKeyFromUrl(string $url): int
    {
        if ($url === '') {
            return 0;
        }
        $path = (string) parse_url($url, PHP_URL_PATH);
        if ($path === '') {
            return 0;
        }
        if (preg_match('/-([a-z])(\d+)\.html?$/i', $path, $m)) {
            return (int) ($m[2] ?? 0);
        }
        if (preg_match('/(\d{4,})\.html?$/i', $path, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    private function normalizeCatalogMediaUrl(mixed $url): string
    {
        if ($url === null || (! is_string($url) && ! is_scalar($url))) {
            return '';
        }
        $s = trim((string) $url);
        if ($s === '') {
            return '';
        }
        if (str_starts_with($s, '//')) {
            return 'https:'.$s;
        }

        return $s;
    }

    /**
     * صور المنتج: الجذر ثم poster ثم أول variant (Sets/Bundles غالباً بلا images على الأب).
     *
     * @return list<string>
     */
    private function collectProductImageUrlsFromNode(array $node): array
    {
        $seen = [];
        $urls = [];

        $push = function (mixed $url) use (&$seen, &$urls): void {
            $normalized = $this->normalizeCatalogMediaUrl($url);
            if ($normalized === '' || isset($seen[$normalized])) {
                return;
            }
            $seen[$normalized] = true;
            $urls[] = $normalized;
        };

        foreach ($node['images'] ?? [] as $img) {
            $push(is_array($img) ? ($img['url'] ?? null) : $img);
        }

        $poster = $node['poster'] ?? null;
        if (is_array($poster)) {
            $push($poster['url'] ?? null);
        }

        foreach ($node['variants'] ?? [] as $variant) {
            $variantProduct = $variant['product'] ?? [];
            if (! is_array($variantProduct)) {
                continue;
            }
            foreach ($variantProduct['images'] ?? [] as $img) {
                $push(is_array($img) ? ($img['url'] ?? null) : $img);
            }
            if ($urls !== []) {
                break;
            }
        }

        return $urls;
    }

    /**
     * منتجات التجميع/الباقات من الـ API الأم — لا تُعرض في المتجر.
     */
    private function shouldExposeCatalogNode(array $node): bool
    {
        $typename = (string) ($node['__typename'] ?? '');
        if ($typename === 'BundleProduct') {
            return false;
        }

        $sku = strtoupper(trim((string) ($node['sku'] ?? '')));
        if ($sku !== '' && str_starts_with($sku, 'SET-') && str_contains($sku, '/SET')) {
            return false;
        }

        return true;
    }

    /**
     * GraphQL قد يعيد نفس المنتج أكثر من مرة عند تمرير عدة categoryIds.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private function deduplicateCatalogNodesBySku(array $nodes): array
    {
        $seen = [];
        $out = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }
            $sku = trim((string) ($node['sku'] ?? ''));
            if ($sku === '' || isset($seen[$sku])) {
                continue;
            }
            $seen[$sku] = true;
            $out[] = $node;
        }

        return $out;
    }

    private function compareProductSizeStrings(string $a, string $b): int
    {
        return $this->productSizeSortKey($a) <=> $this->productSizeSortKey($b);
    }

    /**
     * @return array{0: int, 1: float, 2: string}
     */
    private function productSizeSortKey(string $size): array
    {
        $raw = trim($size);
        if ($raw === '') {
            return [3, PHP_FLOAT_MAX, ''];
        }

        $lower = Str::lower($raw);

        if (preg_match('/^(\d+(?:[.,]\d+)?)/', $lower, $m)) {
            return [0, (float) str_replace(',', '.', $m[1]), $lower];
        }

        static $rank = [
            'xxxs' => 0, 'xxs' => 1, 'xs' => 2, 's' => 3, 'm' => 4, 'l' => 5,
            'xl' => 6, 'xxl' => 7, '2xl' => 8, '3xl' => 9, '4xl' => 10, '5xl' => 11, 'xxxl' => 12,
        ];

        $compact = preg_replace('/\s+/', '', $lower) ?? $lower;
        if (isset($rank[$compact])) {
            return [1, (float) $rank[$compact], $lower];
        }

        if (preg_match('/(\d+(?:[.,]\d+)?)/', $lower, $m) && ! preg_match('/[a-z]{2,}/', str_replace($m[0], '', $lower))) {
            return [0, (float) str_replace(',', '.', $m[1]), $lower];
        }

        if (preg_match('/^(one\s*size|onesize|os|ua)$/i', $lower)) {
            return [2, 9999.0, $lower];
        }

        return [2, 0.0, $lower];
    }

    private function mapProductCard(array $node): array
    {
        $sku = (string) ($node['sku'] ?? '');
        $name = (string) ($node['name'] ?? '');
        $url = (string) ($node['url'] ?? '');
        $slug = $url !== '' ? trim((string) basename((string) parse_url($url, PHP_URL_PATH)), '/') : Str::slug($name . '-' . $sku);
        if ($slug !== '' && str_ends_with(Str::lower($slug), '.html')) {
            $slug = substr($slug, 0, -5);
        }

        $categories = collect($node['categories'] ?? [])
            ->map(fn ($c) => [
                'id' => (string) ($c['id'] ?? ''),
                'name' => (string) ($c['name'] ?? ''),
                'slug' => Str::slug((string) ($c['name'] ?? '')),
                'children' => collect($c['subCategories'] ?? [])
                    ->map(fn ($s) => [
                        'id' => (string) ($s['id'] ?? ''),
                        'name' => (string) ($s['name'] ?? ''),
                        'slug' => Str::slug((string) ($s['name'] ?? '')),
                    ])
                    ->filter(fn ($s) => $s['id'] !== '' && $s['name'] !== '')
                    ->values()
                    ->all(),
            ])
            ->filter(fn ($c) => $c['id'] !== '' && $c['name'] !== '')
            ->values()
            ->all();

        $attributeFacets = collect($node['attributeValues'] ?? [])
            ->map(function ($av) {
                $ak = (string) ($av['attribute']['key'] ?? '');
                $vk = (string) ($av['key'] ?? '');

                return [
                    'attr_key' => $ak,
                    'attr_label' => (string) ($av['attribute']['label'] ?? ''),
                    'value_key' => $vk,
                    'value_label' => (string) ($av['label'] ?? ''),
                ];
            })
            ->filter(fn (array $f) => $f['attr_key'] !== '')
            ->values()
            ->all();

        $prices = $this->extractPrice($node);

        $brand = [
            'id' => (string) Arr::get($node, 'brand.id', ''),
            'name' => (string) Arr::get($node, 'brand.name', ''),
            'logo' => $this->normalizeCatalogMediaUrl(Arr::get($node, 'brand.logo.url')),
        ];

        $colors = collect($node['badges'] ?? [])
            ->map(function ($badge) {
                return [
                    'name' => (string) ($badge['label'] ?? ''),
                    'hex' => (string) Arr::get($badge, 'color.cssValue', ''),
                ];
            })
            ->filter(fn ($c) => $c['name'] !== '' || $c['hex'] !== '')
            ->values()
            ->all();

        $sizes = collect($node['variants'] ?? [])
            ->map(fn ($variant) => Arr::get($variant, 'product.size'))
            ->filter()
            ->map(fn ($size) => (string) $size)
            ->unique()
            ->sort(fn (string $a, string $b) => $this->compareProductSizeStrings($a, $b))
            ->values()
            ->all();

        $imageUrls = $this->collectProductImageUrlsFromNode($node);

        return [
            'sku' => $sku,
            'slug' => $slug,
            'name' => $name,
            'url' => $url,
            'product_type' => (string) ($node['__typename'] ?? ''),
            'article_key' => $this->extractProductArticleKeyFromUrl($url),
            'sort_order' => isset($node['sortOrder']) ? (int) $node['sortOrder'] : null,
            'release_date' => $this->normalizeReleaseDate($node['releaseDate'] ?? null),
            'image' => $imageUrls[0] ?? '',
            'images' => $imageUrls,
            'brand' => $brand,
            'categories' => $categories,
            'attribute_facets' => $attributeFacets,
            'price' => $prices,
            'promo_ends_at' => $this->extractPromoEndsAt($node),
            'sizes' => $sizes,
            'colors' => $colors,
            'in_stock' => (bool) ($node['isOnline'] ?? false),
        ];
    }

    private function mapProductDetails(array $node): array
    {
        $base = $this->mapProductCard($node);

        $descriptionSections = $this->mapDescriptionSections($node);
        $tagline = '';
        $descriptionHtml = '';
        $detailsHtml = '';
        $materialHtml = '';

        foreach ($descriptionSections as $section) {
            $tab = (string) ($section['key'] ?? '');
            $html = (string) ($section['html'] ?? '');
            if ($tab === 'description') {
                $descriptionHtml = $html;
            } elseif ($tab === 'details') {
                $detailsHtml = $html;
            } elseif ($tab === 'material') {
                $materialHtml = $html;
            }
        }

        if ($descriptionHtml === '' && $descriptionSections !== []) {
            $descriptionHtml = (string) ($descriptionSections[0]['html'] ?? '');
        }

        if ($descriptionHtml !== '' && $materialHtml === '') {
            $split = $this->splitMaterialFromDescriptionHtml($descriptionHtml);
            $descriptionHtml = $split['description'];
            $materialHtml = $split['material'];
        }

        if ($descriptionHtml !== '') {
            $plainLen = strlen(trim(strip_tags($descriptionHtml)));
            if ($plainLen > 0 && $plainLen <= 280 && ! Str::contains(Str::lower($descriptionHtml), '<h2')) {
                $tagline = $descriptionHtml;
                $descriptionHtml = '';
            }
        }

        $description = $descriptionHtml;

        $variants = collect($node['variants'] ?? [])
            ->map(function ($variant) {
                $product = $variant['product'] ?? [];
                $sourceCurrency = (string) Arr::get($product, 'listPrice.currency', 'EUR');
                $rawPrice = (float) Arr::get($product, 'listPrice.amount', 0);
                // Stock.quantity أزيل من GraphQL — الحد الشرائي = maximumBuyableQuantity (مثل الموقع الرسمي)
                $rawQty = Arr::get($product, 'stock.maximumBuyableQuantity', Arr::get($product, 'stock.quantity'));
                $quantity = is_numeric($rawQty) ? max(0, (int) $rawQty) : null;
                $inStock = (bool) Arr::get($product, 'stock.isInStock', false);
                if ($quantity !== null) {
                    $inStock = $inStock && $quantity > 0;
                }

                return [
                    'sku' => (string) ($product['sku'] ?? ''),
                    'size' => (string) ($product['size'] ?? ''),
                    'price' => $rawPrice,
                    'currency' => $sourceCurrency,
                    'in_stock' => $inStock,
                    'quantity' => $quantity,
                    'image' => $this->normalizeCatalogMediaUrl(Arr::get($product, 'images.0.url')),
                ];
            })
            ->filter(fn ($v) => $v['sku'] !== '')
            ->sort(fn (array $a, array $b) => $this->compareProductSizeStrings(
                (string) ($a['size'] ?? ''),
                (string) ($b['size'] ?? '')
            ))
            ->values()
            ->all();

        $base['tagline'] = $tagline;
        $base['description'] = $description;
        $base['description_details'] = $detailsHtml;
        $base['description_material'] = $materialHtml;
        $base['description_sections'] = $descriptionSections;
        $base['variants'] = $variants;
        $base = $this->backfillProductPricesFromVariants($base);

        return $base;
    }

    /**
     * بعض المنتجات (مثل Studs) لها listPrice=0 على الأب، والسعر الحقيقي على المقاسات.
     *
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function backfillProductPricesFromVariants(array $product): array
    {
        $variants = $product['variants'] ?? [];
        if (! is_array($variants) || $variants === []) {
            return $product;
        }

        $positivePrices = [];
        foreach ($variants as $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $price = (float) ($variant['price'] ?? 0);
            if ($price > 0) {
                $positivePrices[] = $price;
            }
        }

        $fallback = $positivePrices !== [] ? min($positivePrices) : 0.0;
        $current = (float) ($product['price']['current'] ?? 0);
        if ($current <= 0 && $fallback > 0) {
            $product['price']['current'] = $fallback;
            if (empty($product['price']['currency'])) {
                foreach ($variants as $variant) {
                    $currency = (string) ($variant['currency'] ?? '');
                    if ($currency !== '') {
                        $product['price']['currency'] = $currency;
                        break;
                    }
                }
            }
        }

        $parentPrice = (float) ($product['price']['current'] ?? 0);
        if ($parentPrice > 0) {
            foreach ($variants as $i => $variant) {
                if (! is_array($variant)) {
                    continue;
                }
                if ((float) ($variant['price'] ?? 0) <= 0) {
                    $variants[$i]['price'] = $parentPrice;
                    if (empty($variants[$i]['currency']) && ! empty($product['price']['currency'])) {
                        $variants[$i]['currency'] = $product['price']['currency'];
                    }
                }
            }
            $product['variants'] = $variants;
        }

        return $product;
    }

    /**
     * @return array<int, array{key: string, name: string, html: string}>
     */
    private function mapDescriptionSections(array $node): array
    {
        $grouped = [];
        $order = ['description', 'details', 'material'];
        $fallbackIndex = 0;

        foreach ($node['descriptions'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $html = trim((string) ($row['text'] ?? ''));
            if ($html === '') {
                continue;
            }

            $typeKey = strtolower(trim((string) Arr::get($row, 'type.key', '')));
            $typeName = trim((string) Arr::get($row, 'type.name', ''));
            $tabKey = $this->resolveDescriptionTabKey($typeKey, $typeName);

            if ($tabKey === 'description' && $typeKey === '' && $typeName === '' && $fallbackIndex < count($order)) {
                $tabKey = $order[$fallbackIndex];
                $fallbackIndex++;
            }

            if (! isset($grouped[$tabKey])) {
                $grouped[$tabKey] = [
                    'key' => $tabKey,
                    'name' => $typeName !== '' ? $typeName : $this->defaultDescriptionTabName($tabKey),
                    'html' => '',
                ];
            }

            if ($grouped[$tabKey]['html'] !== '') {
                $grouped[$tabKey]['html'] .= "\n\n";
            }
            $grouped[$tabKey]['html'] .= $html;
        }

        $out = [];
        foreach ($order as $tabKey) {
            if (! empty($grouped[$tabKey]['html'])) {
                $out[] = $grouped[$tabKey];
            }
        }

        return $out;
    }

    private function resolveDescriptionTabKey(string $typeKey, string $typeName): string
    {
        $haystack = strtolower(trim($typeKey . ' ' . $typeName));

        if ($haystack === '') {
            return 'description';
        }

        if (str_contains($haystack, 'material')) {
            return 'material';
        }

        if (str_contains($haystack, 'detail')) {
            return 'details';
        }

        if (
            str_contains($haystack, 'description')
            || str_contains($haystack, 'descri')
            || str_contains($haystack, 'beschreib')
            || in_array($typeKey, ['productdescription', 'longdescription', 'shortdescription'], true)
        ) {
            return 'description';
        }

        if (in_array($typeKey, ['productdetails', 'details', 'detail'], true)) {
            return 'details';
        }

        if (in_array($typeKey, ['material', 'materials', 'productmaterial'], true)) {
            return 'material';
        }

        return 'description';
    }

    /**
     * @return array{description: string, material: string}
     */
    private function splitMaterialFromDescriptionHtml(string $html): array
    {
        $html = trim($html);
        if ($html === '') {
            return ['description' => '', 'material' => ''];
        }

        $patterns = [
            '/<p>\s*<strong>\s*LATEX\s*<\/strong>/i',
            '/<p>\s*<strong>\s*MATERIAL(?:E)?\s*<\/strong>/i',
            '/<h2[^>]*>\s*Material\s*<\/h2>/i',
            '/<h3[^>]*>\s*Material\s*<\/h3>/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $match, PREG_OFFSET_CAPTURE)) {
                $pos = (int) ($match[0][1] ?? -1);
                if ($pos > 0) {
                    return [
                        'description' => trim(substr($html, 0, $pos)),
                        'material' => trim(substr($html, $pos)),
                    ];
                }
            }
        }

        return ['description' => $html, 'material' => ''];
    }

    private function defaultDescriptionTabName(string $tabKey): string
    {
        return match ($tabKey) {
            'details' => 'Product details',
            'material' => 'Material',
            default => 'Product description',
        };
    }

    private function extractPrice(array $node): array
    {
        $list = Arr::get($node, 'listPrice.amount');
        $offer = Arr::get($node, 'offer.price.amount');
        $exact = Arr::get($node, 'priceRange.exact.amount');
        $currency = Arr::get($node, 'listPrice.currency')
            ?? Arr::get($node, 'offer.price.currency')
            ?? Arr::get($node, 'priceRange.exact.currency')
            ?? 'EUR';

        $listF = $list !== null && $list !== '' ? (float) $list : null;
        $offerF = $offer !== null && $offer !== '' ? (float) $offer : null;
        $exactF = $exact !== null && $exact !== '' ? (float) $exact : null;

        /*
         * keepersport.net: سعر مشطوب = list/RRP، السعر الحالي = offer أو priceRange.exact.
         * في API الـ SA غالباً offer=null والخصم الحقيقي = list > exact (مثل 70 → 59.95).
         * فرق التسعير النفسي 80↔79.95 ليس خصماً على الموقع الأصلي — نتجاهله.
         */
        if ($offerF !== null && $this->isMeaningfulCatalogDiscount($listF, $offerF)) {
            return [
                'current' => $offerF,
                'old' => $listF,
                'currency' => (string) $currency,
            ];
        }

        if ($exactF !== null && $this->isMeaningfulCatalogDiscount($listF, $exactF)) {
            return [
                'current' => $exactF,
                'old' => $listF,
                'currency' => (string) $currency,
            ];
        }

        if ($offerF !== null) {
            return [
                'current' => $offerF,
                'old' => null,
                'currency' => (string) $currency,
            ];
        }

        $current = $exactF ?? $listF ?? 0.0;

        if ((float) $current <= 0.0) {
            $variantFallback = $this->minPositiveVariantListPrice($node);
            if ($variantFallback !== null) {
                return [
                    'current' => $variantFallback['amount'],
                    'old' => null,
                    'currency' => $variantFallback['currency'] !== ''
                        ? $variantFallback['currency']
                        : (string) $currency,
                ];
            }
        }

        return [
            'current' => (float) $current,
            'old' => null,
            'currency' => (string) $currency,
        ];
    }

    /**
     * @return array{amount: float, currency: string}|null
     */
    private function minPositiveVariantListPrice(array $node): ?array
    {
        $min = null;
        $currency = '';
        foreach ($node['variants'] ?? [] as $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $product = is_array($variant['product'] ?? null) ? $variant['product'] : [];
            $amount = Arr::get($product, 'listPrice.amount');
            if ($amount === null || $amount === '') {
                $amount = Arr::get($product, 'offer.price.amount');
            }
            $price = $amount !== null && $amount !== '' ? (float) $amount : 0.0;
            if ($price <= 0.0) {
                continue;
            }
            if ($min === null || $price < $min) {
                $min = $price;
                $currency = (string) (
                    Arr::get($product, 'listPrice.currency')
                    ?? Arr::get($product, 'offer.price.currency')
                    ?? ''
                );
            }
        }

        if ($min === null) {
            return null;
        }

        return ['amount' => $min, 'currency' => $currency];
    }

    /**
     * هل الفرق بين list والسعر الحالي خصم حقيقي (وليس 90↔89.95)؟
     */
    private function isMeaningfulCatalogDiscount(?float $listPrice, ?float $salePrice): bool
    {
        if ($listPrice === null || $salePrice === null || $listPrice <= 0.0 || $salePrice <= 0.0) {
            return false;
        }
        if ($listPrice <= $salePrice) {
            return false;
        }

        $absolute = $listPrice - $salePrice;
        $ratio = $absolute / $listPrice;

        // تجاهل فروقات التسعير النفسي الصغيرة (< 1 وحدة عملة و < 2%)
        return $absolute >= 1.0 || $ratio >= 0.02;
    }

    private function extractPromoEndsAt(array $node): ?string
    {
        if (Arr::get($node, 'offer.price.amount') === null) {
            return null;
        }

        $candidates = [
            Arr::get($node, 'offer.countDownTo'),
            Arr::get($node, 'offer.validTo'),
            Arr::get($node, 'offer.endsAt'),
            Arr::get($node, 'offer.endAt'),
            Arr::get($node, 'offer.to'),
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }
            try {
                return \Carbon\Carbon::parse($candidate)->toIso8601String();
            } catch (\Throwable) {
                // Ignore unknown upstream date formats and keep searching.
            }
        }

        return null;
    }

    /**
     * نفّذ تحديث الكاش بعد إرسال الرد للمستخدم حتى لا ينتظر GraphQL.
     */
    private function scheduleAfterResponse(callable $callback): void
    {
        if (app()->runningInConsole()) {
            $callback();

            return;
        }

        try {
            dispatch($callback)->afterResponse();
        } catch (\Throwable $e) {
            Log::debug('Keepersport afterResponse schedule failed; running sync', [
                'error' => $e->getMessage(),
            ]);
            $callback();
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $categoryApiIds
     */
    private function scheduleCatalogListRefresh(
        array $filters,
        string $graphLocale,
        ?int $scanLimit,
        ?int $maxPagesOverride,
        array $categoryApiIds,
        string $listCacheKey,
        string $listStaleCacheKey,
        int $productsCacheTtl,
        bool $includeCategories,
    ): void {
        $guardKey = 'keepersport.list_refresh.'.md5($listCacheKey);
        if (! $this->ksCache()->add($guardKey, 1, 90)) {
            return;
        }

        $this->scheduleAfterResponse(function () use (
            $filters,
            $graphLocale,
            $scanLimit,
            $maxPagesOverride,
            $categoryApiIds,
            $listCacheKey,
            $listStaleCacheKey,
            $productsCacheTtl,
            $includeCategories,
            $guardKey
        ) {
            $previous = $this->forceFreshCatalogFetch;
            $this->forceFreshCatalogFetch = true;
            try {
                if (is_array($this->ksCache()->get($listCacheKey))) {
                    return;
                }

                $sort = (string) ($filters['sort'] ?? 'newest');
                $isSaleListing = $this->isSaleCatalogSlug((string) ($filters['category'] ?? ''));
                $nodes = $isSaleListing
                    ? $this->fetchOnSaleCatalogEdges($graphLocale, $scanLimit, $maxPagesOverride)
                    : $this->fetchCatalogEdges($graphLocale, $scanLimit, $categoryApiIds, null, $maxPagesOverride);
                $filtersForCompute = $isSaleListing
                    ? $this->filtersForSaleProductCompute($filters, $graphLocale)
                    : $this->filtersForProductCompute($filters, $categoryApiIds);
                if (! $isSaleListing) {
                    $filtersForCompute['_catalog_locale'] = $graphLocale;
                }
                $fullPayload = $this->computeProductsFullListFromNodes($nodes, $filtersForCompute, $sort);
                if ($includeCategories) {
                    $fullPayload['categories'] = $this->resolveCategoriesForListing($graphLocale, $nodes, $isSaleListing);
                    if ($categoryApiIds !== []) {
                        $fullPayload['teamsport_subcategories'] = $this->getTeamsportSubcategories();
                    }
                }

                $this->rememberProductListCache($listCacheKey, $listStaleCacheKey, $fullPayload, $productsCacheTtl, $isSaleListing);
            } catch (\Throwable $e) {
                Log::warning('Keepersport product list background refresh failed', [
                    'locale' => $graphLocale,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                $this->forceFreshCatalogFetch = $previous;
                $this->ksCache()->forget($guardKey);
            }
        });
    }

    /**
     * @param  list<string>  $categoryIds
     */
    private function scheduleCatalogEdgesRefresh(
        string $graphLocale,
        ?int $limit,
        string $cacheKey,
        string $staleCacheKey,
        int $maxPages,
        int $ttl,
        string $treeKey,
        array $categoryIds,
        string $trimmedFt,
        bool $updateCategoryTreeCache,
    ): void {
        $guardKey = 'keepersport.catalog_refresh.'.md5($cacheKey);
        if (! $this->ksCache()->add($guardKey, 1, 120)) {
            return;
        }

        $this->scheduleAfterResponse(function () use (
            $graphLocale,
            $limit,
            $cacheKey,
            $staleCacheKey,
            $maxPages,
            $ttl,
            $treeKey,
            $categoryIds,
            $trimmedFt,
            $updateCategoryTreeCache,
            $guardKey
        ) {
            $previous = $this->forceFreshCatalogFetch;
            $this->forceFreshCatalogFetch = true;
            try {
                $existing = $this->ksCache()->get($cacheKey);
                if (is_array($existing) && $existing !== []) {
                    return;
                }

                $catSuffix = $categoryIds !== [] ? '.cats_'.md5(implode(',', $categoryIds)) : '';
                $ftSuffix = $trimmedFt !== '' ? '.ft_'.md5($trimmedFt) : '';
                $lockKey = sprintf('keepersport.catalog.lock.%s.%s%s%s', $graphLocale, $limit ?? 'all', $catSuffix, $ftSuffix);
                $waitSeconds = max(1, min(60, (int) config('services.keepersport_graphql.catalog_lock_wait_seconds', 30)));
                $lockTtlExtra = max(60, (int) config('services.keepersport_graphql.catalog_lock_ttl_extra_seconds', 300));

                $this->ksCache()->lock($lockKey, $waitSeconds + $lockTtlExtra)->block($waitSeconds, function () use (
                    $graphLocale,
                    $limit,
                    $cacheKey,
                    $staleCacheKey,
                    $maxPages,
                    $ttl,
                    $treeKey,
                    $categoryIds,
                    $trimmedFt,
                    $updateCategoryTreeCache
                ) {
                    $cachedInner = $this->ksCache()->get($cacheKey);
                    if (is_array($cachedInner) && $cachedInner !== []) {
                        return $cachedInner;
                    }

                    return $this->executeCatalogGraphqlFetch(
                        $graphLocale,
                        $limit,
                        $cacheKey,
                        $staleCacheKey,
                        $maxPages,
                        $ttl,
                        $treeKey,
                        $categoryIds,
                        $trimmedFt !== '' ? $trimmedFt : null,
                        $updateCategoryTreeCache
                    );
                });
            } catch (\Throwable $e) {
                Log::warning('Keepersport catalog background refresh failed', [
                    'locale' => $graphLocale,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                $this->forceFreshCatalogFetch = $previous;
                $this->ksCache()->forget($guardKey);
            }
        });
    }

    /**
     * @param  list<string>  $categoryIds  معرّفات تصنيفات لتمريرها كفلتر في GraphQL
     * @param  string|null   $fulltextQuery  استعلام fulltext في upstream (مسار PDP أخف)
     * @param  int|null      $maxPagesOverride  حد صفحات مؤقت (مثلاً بحث منتج واحد)
     */
    private function fetchCatalogEdges(string $graphLocale, ?int $limit, array $categoryIds = [], ?string $fulltextQuery = null, ?int $maxPagesOverride = null, ?int $lockWaitOverride = null): array
    {
        $catSuffix = $categoryIds !== [] ? '.cats_' . md5(implode(',', $categoryIds)) : '';
        $trimmedFt = $fulltextQuery !== null ? trim($fulltextQuery) : '';
        $ftSuffix = $trimmedFt !== '' ? '.ft_' . md5($trimmedFt) : '';
        $cacheKey = sprintf('keepersport.catalog.%s.%s%s%s', $graphLocale, $limit ?? 'all', $catSuffix, $ftSuffix);
        $staleCacheKey = sprintf('keepersport.catalog.stale.%s.%s%s%s', $graphLocale, $limit ?? 'all', $catSuffix, $ftSuffix);
        $maxPages = $maxPagesOverride ?? $this->catalogMaxScanPages();
        $updateCategoryTreeCache = $trimmedFt === '';
        $ttl = $this->catalogCacheTtlSeconds();
        $treeKey = sprintf('keepersport.category_tree.%s', $graphLocale);

        $cached = $this->ksCache()->get($cacheKey);
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        // بعد انتهاء TTL: أعِد النسخة القديمة فوراً وحدّث من upstream بعد الرد (أو تحت القفل إن لزم)
        if (! $this->forceFreshCatalogFetch) {
            $staleHit = $this->ksCache()->get($staleCacheKey);
            if (is_array($staleHit) && $staleHit !== []) {
                $this->scheduleCatalogEdgesRefresh(
                    $graphLocale,
                    $limit,
                    $cacheKey,
                    $staleCacheKey,
                    $maxPages,
                    $ttl,
                    $treeKey,
                    $categoryIds,
                    $trimmedFt,
                    $updateCategoryTreeCache
                );

                return $staleHit;
            }
        }

        $lockKey = sprintf('keepersport.catalog.lock.%s.%s%s%s', $graphLocale, $limit ?? 'all', $catSuffix, $ftSuffix);
        $defaultWait = app()->environment('local') ? 20 : 180;
        $waitSeconds = $lockWaitOverride ?? max(5, (int) config('services.keepersport_graphql.catalog_lock_wait_seconds', $defaultWait));
        $waitSeconds = max(1, min(240, $waitSeconds));
        $lockTtlExtra = max(60, (int) config('services.keepersport_graphql.catalog_lock_ttl_extra_seconds', 300));

        $runFetch = function () use ($graphLocale, $limit, $cacheKey, $staleCacheKey, $maxPages, $ttl, $treeKey, $categoryIds, $trimmedFt, $updateCategoryTreeCache) {
            $cachedInner = $this->ksCache()->get($cacheKey);
            if (is_array($cachedInner) && $cachedInner !== []) {
                return $cachedInner;
            }

            $ftArg = $trimmedFt !== '' ? $trimmedFt : null;

            return $this->executeCatalogGraphqlFetch(
                $graphLocale,
                $limit,
                $cacheKey,
                $staleCacheKey,
                $maxPages,
                $ttl,
                $treeKey,
                $categoryIds,
                $ftArg,
                $updateCategoryTreeCache
            );
        };

        try {
            return $this->ksCache()->lock($lockKey, $waitSeconds + $lockTtlExtra)->block($waitSeconds, $runFetch);
        } catch (LockTimeoutException $e) {
            $cachedAgain = $this->ksCache()->get($cacheKey);
            if (is_array($cachedAgain) && $cachedAgain !== []) {
                return $cachedAgain;
            }
            $stale = $this->ksCache()->get($staleCacheKey);
            if (is_array($stale) && $stale !== []) {
                Log::warning('Keepersport catalog lock timeout; using stale snapshot', [
                    'locale' => $graphLocale,
                    'limit' => $limit,
                    'fulltext' => $trimmedFt !== '' ? $trimmedFt : null,
                ]);

                return $stale;
            }

            throw $e;
        }
    }

    /**
     * جلب صفحات الكتالوج من GraphQL وتخزين الكاش (يُستدعى داخل قفل كاش واحد).
     *
     * @return array<int, array<string, mixed>>
     */
    /**
     * @param  list<string>  $categoryIds
     */
    private function executeCatalogGraphqlFetch(
        string $graphLocale,
        ?int $limit,
        string $cacheKey,
        string $staleCacheKey,
        int $maxPages,
        int $ttl,
        string $treeKey,
        array $categoryIds = [],
        ?string $fulltextQuery = null,
        bool $updateCategoryTreeCache = true,
    ): array {
        try {
            $allEdges = $this->graphqlCatalog()->fetchProductEdges(
                $graphLocale,
                $limit,
                $categoryIds,
                $fulltextQuery,
                true,
                $maxPages
            );
        } catch (\Throwable $e) {
            Log::error('Keepersport catalog fetch failed: '.$e->getMessage());

            $stale = $this->ksCache()->get($staleCacheKey);
            if (is_array($stale) && $stale !== []) {
                Log::warning('Using stale Keepersport catalog cache بسبب فشل الاتصال', [
                    'locale' => $graphLocale,
                    'limit' => $limit,
                ]);
                return $stale;
            }

            throw $e;
        }

        $nodes = array_values(array_filter(array_map(fn ($edge) => is_array($edge) ? ($edge['node'] ?? []) : [], $allEdges)));
        $nodes = $this->deduplicateCatalogNodesBySku($nodes);
        $nodes = array_values(array_filter($nodes, fn (array $node) => $this->shouldExposeCatalogNode($node)));

        if ($nodes !== []) {
            $this->ksCache()->put($cacheKey, $nodes, $ttl);
            $this->ksCache()->put($staleCacheKey, $nodes, $this->catalogGraphStaleExpiresAt());
            $this->refreshProductSlugNodeIndex($graphLocale, $nodes);
            if ($updateCategoryTreeCache) {
                $this->ksCache()->put($treeKey, $this->buildCategoryTreeFromNodes($nodes), $ttl);
            }
        } else {
            Log::warning('Keepersport catalog returned 0 products', [
                'endpoint' => $this->endpoint,
                'locale' => $graphLocale,
            ]);
        }

        return $nodes;
    }

    /**
     * Enrich variant rows with stock quantity fetched from the B2B REST endpoint.
     * الطلبات غير المخزّنة تُجلب بالتوازي فقط إذا $fetchMissing = true.
     *
     * @param  list<array<string, mixed>>  $variants
     * @return list<array<string, mixed>>
     */
    private function enrichVariantsWithRestStock(array $variants, bool $fetchMissing = true): array
    {
        if ($variants === [] || $this->token === '') {
            return $variants;
        }

        $restBase = $this->restBaseUrl();
        if ($restBase === '') {
            return $variants;
        }

        $stockTtl = max(60, (int) config('services.keepersport_graphql.rest_stock_cache_ttl', 900));
        $pending = [];

        foreach ($variants as $index => $variant) {
            $sku = (string) ($variant['sku'] ?? '');
            if ($sku === '') {
                continue;
            }

            $b64Sku = base64_encode($sku);
            $cacheKey = sprintf('keepersport.rest_stock.%s', $b64Sku);
            $cached = $this->ksCache()->get($cacheKey);
            if (is_array($cached)) {
                $variants[$index]['quantity'] = $cached['quantity'] ?? null;
                $variants[$index]['in_stock'] = (bool) ($cached['in_stock'] ?? ($variants[$index]['in_stock'] ?? false));

                continue;
            }

            if ($fetchMissing) {
                $pending[$index] = [
                    'sku' => $sku,
                    'b64' => $b64Sku,
                    'cache_key' => $cacheKey,
                ];
            }
        }

        if ($pending === [] || ! $fetchMissing) {
            return array_values($variants);
        }

        try {
            $responses = Http::pool(function ($pool) use ($pending, $restBase) {
                foreach ($pending as $index => $meta) {
                    $url = rtrim($restBase, '/').'/backend/b2b/products/'.$meta['b64'];
                    $pool->as((string) $index)
                        ->withHeaders(['Token' => $this->token])
                        ->withOptions(array_merge(
                            ['connect_timeout' => 8, 'timeout' => 12],
                            $this->sslClientOptions()
                        ))
                        ->get($url);
                }
            });
        } catch (\Throwable $e) {
            Log::warning('REST stock pool failed', ['error' => $e->getMessage()]);

            return array_values($variants);
        }

        foreach ($pending as $index => $meta) {
            $response = $responses[(string) $index] ?? null;
            if (! $response || $response instanceof \Throwable || (method_exists($response, 'failed') && $response->failed())) {
                if ($response && method_exists($response, 'status')) {
                    Log::warning('REST stock endpoint returned error', [
                        'sku' => $meta['sku'],
                        'status' => $response->status(),
                    ]);
                }

                continue;
            }

            $json = $response->json();
            if (! is_array($json)) {
                continue;
            }

            $qty = $json['quantity'] ?? $json['stock'] ?? $json['qty'] ?? null;
            $result = [
                'quantity' => $qty !== null ? (int) $qty : null,
                'in_stock' => (bool) ($json['isInStock'] ?? $json['in_stock'] ?? ($qty !== null && $qty > 0)),
            ];
            $this->ksCache()->put($meta['cache_key'], $result, $stockTtl);
            $variants[$index]['quantity'] = $result['quantity'];
            $variants[$index]['in_stock'] = $result['in_stock'];
        }

        return array_values($variants);
    }

    private function restBaseUrl(): string
    {
        $explicit = (string) config('services.keepersport_graphql.rest_base_url', '');
        if ($explicit !== '') {
            return $explicit;
        }

        $parsed = parse_url($this->endpoint);
        if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
            $port = !empty($parsed['port']) ? ':' . $parsed['port'] : '';
            return $parsed['scheme'] . '://' . $parsed['host'] . $port;
        }

        return '';
    }

    private function request(string $locale): PendingRequest
    {
        $headers = ['Accept-Language' => $locale];
        if ($this->token !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->token;
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
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    private function normalizeProductsFiltersForCache(
        array $filters,
        int $perPage,
        int $page,
        string $sort,
        int $scanLimit,
        bool $excludePaginationForListCacheKey = false
    ): array {
        $normalized = [
            'q' => trim((string) ($filters['q'] ?? '')),
            'category' => trim((string) ($filters['category'] ?? '')),
            'teamsport_sub' => trim((string) ($filters['teamsport_sub'] ?? '')),
            'brand' => trim((string) ($filters['brand'] ?? '')),
            'size' => trim((string) ($filters['size'] ?? '')),
            'color' => trim((string) ($filters['color'] ?? '')),
            'sort' => $sort,
            'scan_limit' => $scanLimit,
            'catalog_engine' => 'graphql_v5',
        ];

        if (! $excludePaginationForListCacheKey) {
            $normalized['page'] = $page;
            $normalized['per_page'] = $perPage;
        }

        $brands = $filters['brands'] ?? [];
        if (is_array($brands)) {
            $brands = array_values(array_filter(array_map(
                fn ($b) => Str::lower(trim((string) $b)),
                $brands
            )));
            sort($brands);
            $normalized['brands'] = $brands;
        }

        $colors = $filters['colors'] ?? [];
        if (is_array($colors)) {
            $colors = array_values(array_filter(array_map(
                fn ($c) => Str::lower(trim((string) $c)),
                $colors
            )));
            sort($colors);
            $normalized['colors'] = $colors;
        }

        $sizes = $filters['sizes'] ?? [];
        if (is_array($sizes)) {
            $sizes = array_values(array_filter(array_map(fn ($s) => trim((string) $s), $sizes)));
            sort($sizes);
            $normalized['sizes'] = $sizes;
        }

        $attrs = $filters['attrs'] ?? [];
        if (is_array($attrs)) {
            $attrs = array_values(array_filter(array_map(fn ($s) => trim((string) $s), $attrs)));
            sort($attrs);
            $normalized['attrs'] = $attrs;
        }

        return $normalized;
    }

    /**
     * يطبّع slug التصنيف من الطلب (مثلاً goalkeeper_apparel → goalkeeper-apparel).
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function normalizeCatalogRequestFilters(array $filters): array
    {
        if (! empty($filters['category'])) {
            $cat = Str::lower(str_replace('_', '-', trim((string) $filters['category'])));
            if (str_starts_with($cat, 'football-boots-')) {
                $cat = Str::lower(substr($cat, strlen('football-boots-')));
            }
            $cat = $this->normalizeGoalkeeperGloveBrandShopSlug($cat, $filters);
            $filters['category'] = $cat;
        }

        return $filters;
    }

    /**
     * /shop/erima-goalkeeper-gloves → category=goalkeeper-gloves + brand=erima
     *
     * @param  array<string, mixed>  $filters
     */
    private function normalizeGoalkeeperGloveBrandShopSlug(string $category, array &$filters): string
    {
        $suffix = '-goalkeeper-gloves';
        if (! str_ends_with($category, $suffix) || $category === 'packs-sets'.$suffix) {
            return $category;
        }

        $brandPart = Str::lower(trim(substr($category, 0, -strlen($suffix))));
        if ($brandPart === '') {
            return $category;
        }

        if (trim((string) ($filters['brand'] ?? '')) === '' && empty($filters['brands'])) {
            $filters['brands'] = [$brandPart];
        } elseif (! in_array($brandPart, (array) ($filters['brands'] ?? []), true)) {
            $filters['brands'][] = $brandPart;
        }

        return 'goalkeeper-gloves';
    }

    /**
     * @return array<string, mixed> Guzzle options (e.g. verify => path|false)
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

        if ($verifySsl === false) {
            return ['verify' => false];
        }

        return [];
    }

    /**
     * @return list<string>
     */
    private function categoryFilterNeedles(string $category): array
    {
        $key = Str::lower(trim($category));
        $aliases = self::CATEGORY_SLUG_ALIASES[$key] ?? [];
        $needles = array_values(array_unique(array_merge([$key], $aliases)));

        return array_map(static fn (string $n) => Str::lower($n), $needles);
    }

    /**
     * @param  list<string>  $needles
     */
    private function categoryMatchesNeedles(array $cat, array $needles): bool
    {
        $id = Str::lower((string) ($cat['id'] ?? ''));
        $slug = Str::lower((string) ($cat['slug'] ?? ''));
        $nameSlug = Str::lower(Str::slug((string) ($cat['name'] ?? '')));
        $nameLower = Str::lower((string) ($cat['name'] ?? ''));

        foreach ($needles as $needle) {
            if ($needle === '') {
                continue;
            }
            if ($id === $needle || $slug === $needle || $nameSlug === $needle) {
                return true;
            }
            if (strlen($needle) >= 4 && (Str::contains($nameLower, $needle) || Str::contains($slug, str_replace(' ', '-', $needle)))) {
                return true;
            }
        }

        $gloveNeedles = ['guanti-da-portiere', 'gloves', 'goalkeeper-gloves', 'goalkeeper-glove', 'torwarthandschuhe'];
        foreach ($needles as $needle) {
            if (in_array($needle, $gloveNeedles, true)) {
                // تجنب كلمات عامة (keeper/portiere) لأنها تسبب دخول فئات غير القفازات.
                foreach (['glove', 'guanti', 'handschuh', 'handschuhe'] as $kw) {
                    if (Str::contains($nameLower, $kw)) {
                        return true;
                    }
                }
                break;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<string>
     */
    private function normalizeSizeFilterList(array $filters): array
    {
        $out = [];

        if (!empty($filters['sizes']) && is_array($filters['sizes'])) {
            foreach ($filters['sizes'] as $s) {
                $s = Str::lower(trim((string) $s));
                if ($s !== '') {
                    $out[] = $s;
                }
            }
        }

        if (!empty($filters['size'])) {
            $single = Str::lower(trim((string) $filters['size']));
            if ($single !== '') {
                $out[] = $single;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<string>
     */
    private function normalizeBrandFilterList(array $filters): array
    {
        $out = [];

        if (! empty($filters['brands']) && is_array($filters['brands'])) {
            foreach ($filters['brands'] as $brand) {
                $brand = Str::lower(trim((string) $brand));
                if ($brand !== '') {
                    $out[] = $brand;
                }
            }
        }

        if (! empty($filters['brand'])) {
            $single = Str::lower(trim((string) $filters['brand']));
            if ($single !== '') {
                $out[] = $single;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<string>
     */
    private function normalizeColorFilterList(array $filters): array
    {
        $out = [];

        if (! empty($filters['colors']) && is_array($filters['colors'])) {
            foreach ($filters['colors'] as $color) {
                $color = Str::lower(trim((string) $color));
                if ($color !== '') {
                    $out[] = $color;
                }
            }
        }

        if (! empty($filters['color'])) {
            $single = Str::lower(trim((string) $filters['color']));
            if ($single !== '') {
                $out[] = $single;
            }
        }

        return array_values(array_unique($out));
    }

    private function normalizeReleaseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        return substr($raw, 0, 10);
    }
}

