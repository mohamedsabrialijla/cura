<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],


    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ],



    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => storage_path('app/firebase_credentials.json'),
        'server_key' => env('FIREBASE_SERVER_KEY'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Nafath (نفاذ) via Semati/ELM API
    | الرابط لا يُفتح في المتصفح — يُستدعى من السيرفر فقط (POST + API Key وربما App ID).
    | - authorize_url: الرابط الكامل (مثال: https://rabet-nafath.api.elm.sa/nafath/api/v1/client/authorize/)
    | - app_id: إن طلبته وثيقة ELM/نفاذ أضفه هنا
    */
    'nafath' => [
        'authorize_url' => env('NAFATH_AUTHORIZE_URL', ''),
        'url_base' => env('NAFATH_URL_BASE', 'test'),
        'app_id' => env('NAFATH_APP_ID', ''),
        'api_key' => env('NAFATH_API_KEY', ''),
        'callback_url' => env('NAFATH_CALLBACK_URL', ''),
    ],

    'keepersport' => [
        'vat_number' => env('KEEPERSPORT_VAT_NUMBER', '300000000000003'),
    ],

    'keepersport_graphql' => [
        'endpoint' => env('KEEPERSPORT_GRAPHQL_ENDPOINT', 'https://backend.keepersport.at/sa/graphql'),
        'username' => env('KEEPERSPORT_GRAPHQL_USERNAME', ''),
        'password' => env('KEEPERSPORT_GRAPHQL_PASSWORD', ''),
        'token' => env('KEEPERSPORT_GRAPHQL_TOKEN', ''),
        /** لغة كتالوج GraphQL (en / de / ar …) */
        'catalog_accept_language' => env('KEEPERSPORT_GRAPHQL_ACCEPT_LANGUAGE', 'en'),
        /** ثواني: اتصال DNS/TCP ثم الطلب الكامل (تقليل cURL 28 على شبكات بطيئة) */
        'connect_timeout' => (int) env('KEEPERSPORT_GRAPHQL_CONNECT_TIMEOUT', 30),
        'request_timeout' => (int) env('KEEPERSPORT_GRAPHQL_REQUEST_TIMEOUT', 120),
        /** إن كان DNS يفشل: ضع IP السيرفر هنا (مثال: 1.2.3.4) — يُستخدم CURLOPT_RESOLVE */
        'resolve_ip' => env('KEEPERSPORT_GRAPHQL_RESOLVE_IP', ''),
        /** كاش نتائج المنتجات بعد الفلاتر (ثوانٍ) — 24س افتراضي؛ لا تخفّضه لأقل من ساعات وإلا الصفحات تبطئ يومياً */
        'products_cache_ttl' => (int) env('KEEPERSPORT_PRODUCTS_CACHE_TTL', 86400),
        /** عدد المنتجات المجلوبة من GraphQL قبل الفلترة محليًا (أعلى = أدق وأبطأ) */
        'catalog_scan_limit' => (int) env('KEEPERSPORT_CATALOG_SCAN_LIMIT', 1200),
        /** كاش قائمة المنتجات الخام من upstream (ثوانٍ) */
        'catalog_cache_ttl' => (int) env('KEEPERSPORT_CATALOG_CACHE_TTL', 86400),
        /** نسخة احتياطية من كتالوج GraphQL عند انتهاء TTL أو فشل الجلب (ثوانٍ) — تُخدم فوراً ريثما يُحدَّث الكاش */
        'catalog_stale_ttl_seconds' => (int) env('KEEPERSPORT_CATALOG_STALE_TTL', 259200),
        /** نسخة احتياطية لقوائم المنتجات المحسوبة (ثوانٍ) */
        'products_list_stale_ttl_seconds' => (int) env('KEEPERSPORT_PRODUCTS_LIST_STALE_TTL', 172800),
        /** حد صفحات الـ pagination لـ GraphQL (كل صفحة حتى 100 منتج) */
        'catalog_max_pages' => (int) env('KEEPERSPORT_CATALOG_MAX_PAGES', 12),
        /** انتظار قفل الكاش عند جلب الكتالوج بالتوازي (ثوانٍ) */
        'catalog_lock_wait_seconds' => (int) env('KEEPERSPORT_CATALOG_LOCK_WAIT', 240),
        /** مدة احتفاظ قفل الكاش بعد انتهاء الانتظار (ثوانٍ) = انتظار + هذه القيمة */
        'catalog_lock_ttl_extra_seconds' => (int) env('KEEPERSPORT_CATALOG_LOCK_TTL_EXTRA', 300),
        /** اسم متجر كاش Laravel (مثلاً redis) — فارغ = الافتراضي من CACHE_STORE */
        'cache_store' => env('KEEPERSPORT_CACHE_STORE', ''),
        /** كاش صفحة منتج واحد (حسب slug) — يفضّل ≥ warm_cache_ttl حتى لا تبرد PDP خلال اليوم */
        'product_slug_cache_ttl' => (int) env('KEEPERSPORT_PRODUCT_SLUG_CACHE_TTL', 86400),
        /** عند غياب المنتج: مدة كاش أقصر حتى لا يبقى 404 طويلاً */
        'product_slug_miss_cache_ttl' => (int) env('KEEPERSPORT_PRODUCT_SLUG_MISS_CACHE_TTL', 300),
        /** كاش REST للمخزون لكل SKU (ثوانٍ) */
        'rest_stock_cache_ttl' => (int) env('KEEPERSPORT_REST_STOCK_CACHE_TTL', 900),
        /**
         * max-age لاستجابات JSON للكتالوج في المتصفح/CDN (ثوانٍ).
         * 0 = يُشتق من products_cache_ttl (مع http_catalog_max_age_cap).
         */
        'http_catalog_max_age' => (int) env('KEEPERSPORT_HTTP_CATALOG_MAX_AGE', 0),
        'http_catalog_max_age_cap' => (int) env('KEEPERSPORT_HTTP_CATALOG_MAX_AGE_CAP', 86400),
        /** max-age لصفحة منتج واحد (ثوانٍ)، 0 = min(900, product_slug_cache_ttl) */
        'http_product_max_age' => (int) env('KEEPERSPORT_HTTP_PRODUCT_MAX_AGE', 0),
        /** stale-while-revalidate لرؤوس Cache-Control (ثوانٍ)، 0 = تعطيل */
        'http_stale_while_revalidate' => (int) env('KEEPERSPORT_HTTP_STALE_WHILE_REVALIDATE', 3600),
        /** لغات Accept-Language لتسخين الكاش (مفصولة بفاصلة)، فارغ = catalog_accept_language فقط */
        'warm_catalog_locales' => env('KEEPERSPORT_WARM_CATALOG_LOCALES', ''),
        /**
         * جدولة التسخين التلقائي (routes/console.php):
         * - فارغ أو off = لا جدولة داخل Laravel (استخدم cron مباشر على السيرفر)
         * - HH:MM مثل 03:00 = يومياً
         * - تعبير cron كامل مثل 0 3,15 * * * = مرتين يومياً (موصى به)
         */
        'warm_catalog_cron' => env('KEEPERSPORT_WARM_CATALOG_CRON', '0 3,15 * * *'),
        /** حد ذاكرة PHP لأمر التسخين (ميغابايت) */
        'warm_catalog_memory_mb' => (int) env('KEEPERSPORT_WARM_CATALOG_MEMORY_MB', 1024),
        /** مدة الكاش الذي يكتبه أمر التسخين (ثوانٍ) — 48س أفضل إن تأخر cron يوماً */
        'warm_cache_ttl' => (int) env('KEEPERSPORT_WARM_CACHE_TTL', 172800),
        /** بعد تسخين التصنيفات/القوائم: أقصى عدد صفحات منتج (slug) تُخزَّن في الكاش — 0 = تعطيل */
        'warm_products_limit' => (int) env('KEEPERSPORT_WARM_PRODUCTS_LIMIT', 2000),
        /** أثناء warm: جلب وتخزين المنتجات الموصى بها (أبطأ — HTTP upstream) */
        'warm_product_recommended' => filter_var(env('KEEPERSPORT_WARM_PRODUCT_RECOMMENDED', false), FILTER_VALIDATE_BOOLEAN),
        // Windows / PHP: إن ظهر cURL error 60، ضع مسار cacert.pem أو عطّل التحقق محليًا فقط.
        'ca_bundle' => env('KEEPERSPORT_GRAPHQL_CA_BUNDLE', ''),
        'verify_ssl' => env('KEEPERSPORT_GRAPHQL_VERIFY_SSL', true),
        /** Base URL for Keepersport REST API (auto-derived from GraphQL endpoint if empty) */
        'rest_base_url' => env('KEEPERSPORT_REST_BASE_URL', ''),
        /** صفحة المنتج العامة لاستخراج SKU الموصى بها من HTML (data-query-id) */
        'public_product_page_base' => env('KEEPERSPORT_PUBLIC_PRODUCT_PAGE_BASE', 'https://www.keepersport.net'),
        'public_graphql_endpoint' => env('KEEPERSPORT_PUBLIC_GRAPHQL_ENDPOINT', 'https://www.keepersport.net/graphql'),
        /** كاش قائمة المنتجات الموصى بها لكل SKU (ثوانٍ) */
        'recommended_cache_ttl' => (int) env('KEEPERSPORT_RECOMMENDED_CACHE_TTL', 3600),
        /** مهلة جلب HTML صفحة المنتج upstream (ثوانٍ) */
        'recommended_page_timeout' => (int) env('KEEPERSPORT_RECOMMENDED_PAGE_TIMEOUT', 20),
        /**
         * عند miss: هل نبحث fulltext على GraphQL؟ false = أسرع (كاش محلي فقط)
         */
        'pdp_fulltext_lookup' => filter_var(env('KEEPERSPORT_PDP_FULLTEXT_LOOKUP', true), FILTER_VALIDATE_BOOLEAN),
        /**
         * عند miss: جلب GraphQL مباشر بـ fulltext من slug (بدون انتظار قفل كتالogg كامل) — أسرع لفتح PDP بارداً
         */
        'pdp_direct_graphql_lookup' => filter_var(env('KEEPERSPORT_PDP_DIRECT_GRAPHQL_LOOKUP', true), FILTER_VALIDATE_BOOLEAN),
        /**
         * عند hit على كاش PDP الجاهز: هل نُعيد جلب maximumBuyableQuantity من GraphQL؟ false = فتح فوري
         */
        'pdp_refresh_quantities_on_cache_hit' => filter_var(env('KEEPERSPORT_PDP_REFRESH_QUANTITIES_ON_CACHE_HIT', false), FILTER_VALIDATE_BOOLEAN),
        /**
         * عند فتح المنتج: إن لم يكن مخزون REST في الكاش، هل ننتظر جلب upstream؟
         * false = أسرع (يعتمد in_stock من GraphQL؛ الكمية تُحدَّث عند وجودها في الكاش)
         */
        'rest_stock_fetch_on_miss' => filter_var(env('KEEPERSPORT_REST_STOCK_FETCH_ON_MISS', false), FILTER_VALIDATE_BOOLEAN),
        /**
         * عند فتح المنتج: إن لم تكن الموصى بها في الكاش، هل ننتظر جلب upstream؟
         * false = أسرع (الموصى بها تُجلب لاحقاً عبر /products/{slug}/recommended)
         */
        'recommended_fetch_on_miss' => filter_var(env('KEEPERSPORT_RECOMMENDED_FETCH_ON_MISS', false), FILTER_VALIDATE_BOOLEAN),
    ],

];
