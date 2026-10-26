<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SiteShareMetaService
{
    private const SITE_EN = 'KEEPERsport Saudi Arabia';

    private const SITE_AR = 'KEEPERsport السعودية';

    public function __construct(
        private readonly SettingCoverService $coverService,
        private readonly KeeperSportCatalogService $catalogService,
    ) {}

    /**
     * @return array<string, string|null>
     */
    public function forRequest(Request $request): array
    {
        $path = trim($request->path(), '/');
        $segments = $path === '' ? [] : explode('/', $path);
        $locale = ($segments[0] ?? 'en') === 'ar' ? 'ar' : 'en';
        $rest = array_slice($segments, 1);
        $canonical = $this->absoluteUrl($request->getRequestUri());
        $defaultImage = $this->defaultShareImage();

        $meta = $this->homeMeta($locale, $canonical, $defaultImage);

        if ($rest === []) {
            return $meta;
        }

        if (($rest[0] ?? '') === 'product' && isset($rest[1]) && $rest[1] !== '') {
            return $this->productMeta($locale, $canonical, $defaultImage, (string) $rest[1]) ?: $meta;
        }

        if (($rest[0] ?? '') === 'shop' && isset($rest[1]) && $rest[1] !== '') {
            return $this->catalogMeta($locale, $canonical, $defaultImage, (string) $rest[1], true);
        }

        if (count($rest) === 1) {
            return $this->catalogMeta($locale, $canonical, $defaultImage, (string) $rest[0], false);
        }

        if (($rest[0] ?? '') === 'search') {
            return $this->pageMeta(
                $locale,
                $canonical,
                $defaultImage,
                $locale === 'ar' ? 'نتائج البحث' : 'Search results',
                $locale === 'ar'
                    ? 'ابحث عن قفازات حراس المرمى، الأحذية، الملابس والمعدات في KEEPERsport السعودية.'
                    : 'Search goalkeeper gloves, boots, apparel and equipment at KEEPERsport Saudi Arabia.'
            );
        }

        return $this->pageMeta(
            $locale,
            $canonical,
            $defaultImage,
            $this->pageTitleFromSlug((string) ($rest[0] ?? ''), $locale),
            $this->siteTagline($locale)
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function homeMeta(string $locale, string $canonical, string $defaultImage): array
    {
        if ($locale === 'ar') {
            return $this->pack(
                self::SITE_AR,
                'متجر KEEPERsport الرسمي في السعودية — قفازات حراس المرمى، أحذية كرة القدم، ملابس ومعدات احترافية. شحن سريع، ماركات عالمية، وأسعار بالريال السعودي.',
                $canonical,
                $defaultImage,
                'ar_SA'
            );
        }

        return $this->pack(
            self::SITE_EN,
            'Official KEEPERsport store in Saudi Arabia — goalkeeper gloves, football boots, apparel and pro equipment. Fast delivery, top brands, prices in SAR.',
            $canonical,
            $defaultImage,
            'en_US'
        );
    }

    /**
     * @return array<string, string|null>|null
     */
    private function productMeta(string $locale, string $canonical, string $defaultImage, string $slug): ?array
    {
        try {
            // لغة واجهة الموقع (ar/en) ≠ لغة كتالوج GraphQL
            $catalogLocale = (string) config('services.keepersport_graphql.catalog_accept_language', 'en');
            // كاش فقط — لا GraphQL عند تحميل HTML حتى لا تبطئ الصفحة
            $product = $this->catalogService->productBySlugFromCacheOnly($slug, $catalogLocale);
        } catch (\Throwable) {
            $product = null;
        }

        if (! is_array($product) || empty($product['name'])) {
            return null;
        }

        $name = trim(strip_tags((string) $product['name']));
        $image = $this->firstProductImage($product) ?: $defaultImage;
        $site = $locale === 'ar' ? self::SITE_AR : self::SITE_EN;

        if ($locale === 'ar') {
            $description = "اشتري {$name} من {$site}. قفازات حراس المرمى، أحذية، ملابس ومعدات — توصيل داخل السعودية.";
        } else {
            $description = "Shop {$name} at {$site}. Goalkeeper gloves, boots, apparel & equipment with delivery across Saudi Arabia.";
        }

        return $this->pack("{$name} | {$site}", $description, $canonical, $image, $locale === 'ar' ? 'ar_SA' : 'en_US');
    }

    /**
     * @return array<string, string|null>
     */
    private function catalogMeta(
        string $locale,
        string $canonical,
        string $defaultImage,
        string $slug,
        bool $isShopPath
    ): array {
        $label = $this->catalogLabel($slug, $locale);
        $site = $locale === 'ar' ? self::SITE_AR : self::SITE_EN;

        if ($locale === 'ar') {
            $description = $isShopPath
                ? "تسوق {$label} في {$site}. تشكيلة واسعة من منتجات حراس المرمى والملابس الرياضية مع توصيل داخل المملكة."
                : "اكتشف {$label} في {$site}. ماركات عالمية، أسعار بالريال، وشحن سريع داخل السعودية.";
        } else {
            $description = $isShopPath
                ? "Shop {$label} at {$site}. Wide range of goalkeeper and teamsport products with delivery across Saudi Arabia."
                : "Explore {$label} at {$site}. Top brands, SAR pricing, and fast delivery in Saudi Arabia.";
        }

        return $this->pack("{$label} | {$site}", $description, $canonical, $defaultImage, $locale === 'ar' ? 'ar_SA' : 'en_US');
    }

    /**
     * @return array<string, string|null>
     */
    private function pageMeta(
        string $locale,
        string $canonical,
        string $defaultImage,
        string $title,
        string $description
    ): array {
        $site = $locale === 'ar' ? self::SITE_AR : self::SITE_EN;

        return $this->pack("{$title} | {$site}", $description, $canonical, $defaultImage, $locale === 'ar' ? 'ar_SA' : 'en_US');
    }

    /**
     * @return array<string, string|null>
     */
    private function pack(
        string $title,
        string $description,
        string $canonical,
        string $image,
        string $ogLocale
    ): array {
        $description = $this->trimDescription($description);

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'og_locale' => $ogLocale,
            'og_type' => 'website',
            'og_title' => $title,
            'og_description' => $description,
            'og_url' => $canonical,
            'og_site_name' => str_contains($ogLocale, 'ar') ? self::SITE_AR : self::SITE_EN,
            'og_image' => $image,
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $title,
            'twitter_description' => $description,
            'twitter_image' => $image,
        ];
    }

    private function siteTagline(string $locale): string
    {
        return $locale === 'ar'
            ? 'متجر KEEPERsport الرسمي في السعودية — قفازات حراس المرمى، أحذية، ملابس ومعدات احترافية.'
            : 'Official KEEPERsport store in Saudi Arabia — goalkeeper gloves, boots, apparel and pro equipment.';
    }

    private function catalogLabel(string $slug, string $locale): string
    {
        $mapEn = [
            'goalkeeper-gloves' => 'Goalkeeper Gloves',
            'football-boots' => 'Football Boots',
            'goalkeeper-apparel' => 'Goalkeeper Apparel',
            'goalkeeper-equipment' => 'Goalkeeper Equipment',
            'teamsport' => 'Teamsport',
            'sale' => 'Sale',
            'cart' => 'Shopping Cart',
            'contact-us' => 'Contact Us',
            'about-us' => 'About Us',
            'faq' => 'FAQ',
            'terms' => 'Terms & Conditions',
            'privacy' => 'Privacy Policy',
            'usage-return' => 'Usage & Return Policy',
            'glove-care' => 'Glove Care',
        ];

        $mapAr = [
            'goalkeeper-gloves' => 'قفازات حارس المرمى',
            'football-boots' => 'أحذية كرة القدم',
            'goalkeeper-apparel' => 'ملابس حارس المرمى',
            'goalkeeper-equipment' => 'معدات حارس المرمى',
            'teamsport' => 'ملابس الفريق',
            'sale' => 'التخفيضات',
            'cart' => 'سلة التسوق',
            'contact-us' => 'اتصل بنا',
            'about-us' => 'من نحن',
            'faq' => 'الأسئلة الشائعة',
            'terms' => 'الشروط والأحكام',
            'privacy' => 'سياسة الخصوصية',
            'usage-return' => 'سياسة الاستخدام والاسترجاع',
            'glove-care' => 'العناية بالقفازات',
        ];

        $slug = Str::lower(trim($slug));
        $map = $locale === 'ar' ? $mapAr : $mapEn;
        if (isset($map[$slug])) {
            return $map[$slug];
        }

        if (str_contains($slug, 'goalkeeper-gloves')) {
            $brand = str_replace('-goalkeeper-gloves', '', $slug);

            return $locale === 'ar'
                ? 'قفازات '.ucwords(str_replace('-', ' ', $brand))
                : ucwords(str_replace('-', ' ', $brand)).' Goalkeeper Gloves';
        }

        if (str_starts_with($slug, 'football-boots')) {
            return $locale === 'ar' ? 'أحذية كرة القدم' : 'Football Boots';
        }

        return ucwords(str_replace('-', ' ', $slug));
    }

    private function pageTitleFromSlug(string $slug, string $locale): string
    {
        return $this->catalogLabel($slug, $locale);
    }

  /**
     * @param  array<string, mixed>  $product
     */
    private function firstProductImage(array $product): ?string
    {
        $candidates = [
            $product['image'] ?? null,
            $product['main_image'] ?? null,
            $product['thumbnail'] ?? null,
        ];

        if (isset($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as $img) {
                if (is_string($img) && $img !== '') {
                    $candidates[] = $img;
                } elseif (is_array($img) && ! empty($img['url'])) {
                    $candidates[] = $img['url'];
                }
            }
        }

        foreach ($candidates as $candidate) {
            $url = $this->absoluteUrl(is_string($candidate) ? $candidate : null);
            if ($url !== '') {
                return $url;
            }
        }

        return null;
    }

    private function defaultShareImage(): string
    {
        $cover = $this->coverService->get();
        foreach ([$cover['cover_image'] ?? null, $cover['cover_image_mobile'] ?? null] as $candidate) {
            $url = $this->absoluteUrl(is_string($candidate) ? $candidate : null);
            if ($url !== '') {
                return $url;
            }
        }

        $setting = Setting::query()->find(1);
        $logo = $setting?->logo;
        if (is_string($logo) && trim($logo) !== '') {
            return $this->absoluteUrl($logo);
        }

        return $this->absoluteUrl('assets_website/brand/d.webp');
    }

    private function absoluteUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    private function trimDescription(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', trim(strip_tags($text))) ?? '';

        return Str::limit($text, 300, '…');
    }
}
