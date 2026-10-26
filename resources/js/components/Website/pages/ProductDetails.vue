<template>
    <section class="ks-product-page" :lang="locale === 'ar' ? 'ar' : 'en'">
        <div class="ks-product-wrap ks-product-wrap--bleed">
            <p v-if="loading" class="ks-state">{{ t.loading }}</p>
            <p v-else-if="!product" class="ks-state">{{ t.not_found }}</p>

            <div v-else class="ks-pv-wide">
                <div class="ks-product-overview">
                    <div class="ks-product-split">
                    <div class="ks-images images">
                        <div class="ks-gallery ks-gallery--pdp">
                            <!-- en: مصغّرات يسار الصورة | ar: مصغّرات يمين الصورة -->
                            <div
                                class="ks-gallery__inner"
                                :class="locale === 'ar' ? 'ks-gallery__inner--ar' : 'ks-gallery__inner--en'"
                                dir="ltr"
                            >
                                <div
                                    ref="zoomHost"
                                    class="ks-gallery__main ks-gallery__main--zoom"
                                    :class="{
                                        'ks-gallery__main--panning': mainZoomPanning,
                                        'ks-gallery__main--zoomed': mainZoom > 1,
                                    }"
                                    @wheel="onMainImageWheel"
                                    @mousedown="onMainZoomPanStart"
                                    @pointerdown="onGallerySwipePointerDown"
                                    @pointerup="onGallerySwipePointerUp"
                                    @pointercancel="onGallerySwipePointerUp"
                                >
                                    <div v-if="product" class="ks-zoom-toolbar" @mousedown.stop @click.stop>
                                        <button type="button" class="ks-zoom-btn" :title="t.zoom_out" @click="mainZoomStep(-1)">−</button>
                                        <button type="button" class="ks-zoom-btn ks-zoom-btn--text" :title="t.zoom_reset" @click="resetMainZoom">
                                            {{ mainZoomPercent }}
                                        </button>
                                        <button type="button" class="ks-zoom-btn" :title="t.zoom_in" @click="mainZoomStep(1)">+</button>
                                    </div>
                                    <div
                                        class="ks-gallery__zoom-inner"
                                        :style="mainZoomTransformStyle"
                                    >
                                        <img
                                            :src="mainImage"
                                            :alt="product.name"
                                            class="ks-gallery__main-img"
                                            draggable="false"
                                            referrerpolicy="no-referrer"
                                            decoding="async"
                                            fetchpriority="high"
                                        />
                                    </div>
                                </div>
                                <div class="ks-pdp-heading">
                                    <h1
                                        class="ks-product-title product-title"
                                        :class="{ 'ks-product-title--upper': locale !== 'ar' }"
                                        :dir="locale === 'ar' ? 'rtl' : 'ltr'"
                                    >
                                        {{ product.name }}
                                    </h1>
                                    <p
                                        v-if="taglineHtml"
                                        class="ks-product-tagline"
                                        :dir="locale === 'ar' ? 'rtl' : 'ltr'"
                                        v-html="taglineHtml"
                                    ></p>
                                </div>
                                <div v-if="galleryImages.length" class="ks-gallery__aside">
                                    <button
                                        v-if="galleryImages.length > thumbViewportCount"
                                        type="button"
                                        class="ks-gallery__nav ks-gallery__nav--prev"
                                        :aria-label="t.scroll_thumbs_prev"
                                        @click="scrollGalleryThumbs(-1)"
                                    >
                                        ‹
                                    </button>
                                    <div
                                        ref="galleryThumbStrip"
                                        class="ks-gallery__track"
                                        role="tablist"
                                        :aria-label="t.product_images"
                                    >
                                        <button
                                            v-for="(image, idx) in galleryImages"
                                            :key="idx"
                                            type="button"
                                            role="tab"
                                            :aria-selected="image === mainImage"
                                            class="ks-gallery__thumb"
                                            :class="{ 'ks-gallery__thumb--active': image === mainImage }"
                                            @click="selectGalleryImage(image)"
                                        >
                                            <img
                                                :src="image"
                                                :alt="`${product.name} ${idx + 1}`"
                                                referrerpolicy="no-referrer"
                                                decoding="async"
                                                loading="lazy"
                                            />
                                        </button>
                                    </div>
                                    <button
                                        v-if="galleryImages.length > thumbViewportCount"
                                        type="button"
                                        class="ks-gallery__nav ks-gallery__nav--next"
                                        :aria-label="t.scroll_thumbs_next"
                                        @click="scrollGalleryThumbs(1)"
                                    >
                                        ›
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ks-product-detail-info product-detail-info">
                        <div class="ks-product-detail-wrapper product-detail-wrapper">
                            <div class="ks-pdp-info-stack">
                                <h1
                                    class="ks-product-title product-title ks-pdp-info-stack__title"
                                    :class="{ 'ks-product-title--upper': locale !== 'ar' }"
                                    :dir="locale === 'ar' ? 'rtl' : 'ltr'"
                                >
                                    {{ product.name }}
                                </h1>
                                <p
                                    v-if="taglineHtml"
                                    class="ks-product-tagline ks-pdp-info-stack__tagline"
                                    :dir="locale === 'ar' ? 'rtl' : 'ltr'"
                                    v-html="taglineHtml"
                                ></p>
                                <div class="ks-pdp-info-stack__pricing">
                                    <template v-if="showSalePromoBox">
                                        <p class="ks-rrp ks-rrp--stack listPrice">
                                            <span class="ks-rrp-label">{{ t.rrp }}</span>
                                            <span
                                                class="ks-pdp-price-with-currency ks-price-strike"
                                                v-html="mobileRrpPriceHtml"
                                            ></span>
                                        </p>
                                        <div class="ks-pdp-price-row">
                                            <span class="ks-pdp-price-now ks-pdp-price-with-currency" v-html="mobileSalePriceHtml"></span>
                                            <span
                                                v-if="discountPercent > 0"
                                                class="ks-pdp-discount-badge"
                                                lang="en"
                                                dir="ltr"
                                            >{{ discountPercent }}%</span>
                                        </div>
                                        <p
                                            v-if="promoEndsAtTs"
                                            class="ks-pdp-mobile-timer"
                                            dir="ltr"
                                            v-html="promoTimerHtml"
                                        ></p>
                                        <p v-if="promoEndsAtTs || showSalePromoBox" class="ks-pdp-mobile-limited">
                                            {{ t.limited_time }}
                                        </p>
                                    </template>
                                    <p v-else class="ks-pdp-price-now ks-pdp-price-now--solo ks-pdp-price-with-currency" v-html="mobileSoloPriceHtml"></p>
                                    <p class="ks-sku-line ks-sku-line--stack itemnumber">
                                        <span class="ks-sku-line__label">{{ t.sku }}</span>
                                        <span class="ks-sku-line__code">{{ selectedVariant?.sku || product.sku }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="ks-price-box ks-price-box--desktop">
                                <div class="ks-detail-brand-row ks-detail-brand-row--above-price">
                                    <div v-if="product.brand?.logo" class="ks-product-brand product-brand">
                                        <img
                                            :src="normalizeCatalogImageUrl(product.brand.logo)"
                                            :alt="product.brand.name || ''"
                                            width="75"
                                            height="75"
                                            referrerpolicy="no-referrer"
                                            decoding="async"
                                        />
                                    </div>
                                    <p v-else-if="product.brand?.name" class="ks-brand ks-brand--text">{{ product.brand.name }}</p>
                                </div>
                                <template v-if="showSalePromoBox">
                                    <p class="ks-rrp listPrice">
                                        <span class="ks-rrp-label">{{ t.rrp }}</span>
                                        <span
                                            class="ks-pdp-price-with-currency ks-price-strike"
                                            v-html="desktopRrpPriceHtml"
                                        ></span>
                                    </p>
                                    <div class="ks-sale-box">
                                        <div v-if="discountPercent > 0" class="ks-sale-corner-ribbon" aria-hidden="true">
                                            <span class="ks-sale-corner-ribbon-tri"></span>
                                            <span class="ks-sale-corner-ribbon-txt" lang="en" dir="ltr">{{ discountPercent }}%</span>
                                        </div>
                                        <div v-if="promoEndsAtTs" class="ks-sale-box-head">
                                            <p class="ks-sale-box-timer" dir="ltr" v-html="promoTimerHtml"></p>
                                        </div>
                                        <div v-if="promoEndsAtTs" class="ks-sale-head-rule" aria-hidden="true"></div>
                                        <p class="ks-sale-price" v-html="salePricePromoHtml"></p>
                                        <p class="ks-sale-box-note">{{ t.limited_time }}</p>
                                    </div>
                                </template>
                                <template v-else>
                                    <strong class="ks-now itemnumber ks-pdp-price-with-currency" v-html="desktopSoloPriceHtml"></strong>
                                </template>
                            </div>

                            <div v-if="sizeOptions.length" class="ks-variants">
                                <label>{{ t.size }}</label>
                                <div class="ks-size-grid">
                                    <button
                                        v-for="variant in sizeOptions"
                                        :key="variant.sku"
                                        type="button"
                                        class="ks-size-box"
                                        :class="{
                                            active: selectedVariantSku === variant.sku,
                                            'ks-size-box--soldout': !variant.in_stock,
                                        }"
                                        :disabled="!variant.in_stock"
                                        @click="selectedVariantSku = variant.sku"
                                    >
                                        {{ variant.size || variant.sku }}
                                    </button>
                                </div>
                            </div>

                            <div class="ks-qty-row">
                                <span class="ks-qty-label">{{ t.quantity }}</span>
                                <select v-model.number="quantity" class="ks-qty-select" lang="en" dir="ltr" aria-label="Quantity">
                                    <option v-for="n in qtyOptions" :key="n" :value="n">{{ n }}</option>
                                </select>
                            </div>

                            <button
                                class="ks-add-cart add-to-cart"
                                type="button"
                                :disabled="!canAddToCart"
                                @click="addToCart"
                            >
                                <span>{{ t.add_to_basket }}</span>
                                <svg class="ks-cart-ico" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        fill="currentColor"
                                        d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1.003 1.003 0 0 0 20 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"
                                    />
                                </svg>
                            </button>
                            <p class="ks-sku-line ks-sku-line--detail itemnumber">
                                <span class="ks-sku-line__label">{{ t.sku }}</span>
                                <span class="ks-sku-line__code">{{ selectedVariant?.sku || product.sku }}</span>
                            </p>
                            <p class="ks-cart-note">{{ t.shipping_hint }}</p>
                        </div>
                    </div>
                    </div>
                </div>

            <div v-if="product" class="ks-tabs ks-pv-tabs">
                <div class="ks-tab-head" role="tablist">
                    <button
                        v-for="tab in productTabs"
                        :key="tab.key"
                        type="button"
                        role="tab"
                        class="ks-tab-btn"
                        :class="{ active: activeTab === tab.key }"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>
                <div
                    v-if="activeTab === 'description'"
                    class="ks-tab-body ks-tab-body--html"
                    v-html="descriptionHtml"
                ></div>
                <div
                    v-else-if="activeTab === 'details'"
                    class="ks-tab-body"
                    :class="{ 'ks-tab-body--html': detailsHtml }"
                >
                    <div v-if="detailsHtml" class="ks-tab-body--html" v-html="detailsHtml"></div>
                    <ul v-if="productDetailsRows.length" class="ks-details-list">
                        <li v-for="(row, idx) in productDetailsRows" :key="idx">
                            <strong>{{ row.label }}:</strong> {{ row.value }}
                        </li>
                    </ul>
                    <p v-if="!detailsHtml && !productDetailsRows.length" class="ks-details-empty">
                        {{ t.details_empty }}
                    </p>
                </div>
                <div
                    v-else-if="activeTab === 'material'"
                    class="ks-tab-body ks-tab-body--html"
                    v-html="materialHtml"
                ></div>
            </div>
            </div>

            <section
                v-if="product && recommendedProducts.length"
                class="ks-pdp-recommended"
                aria-label="Recommended products"
            >
                <div class="ks-pdp-recommended-band" aria-hidden="true"></div>
                <div class="ks-pdp-recommended-inner">
                <header class="ks-pdp-recommended-head">
                    <h2 class="ks-pdp-recommended-title">{{ t.recommended_products }}</h2>
                </header>
                <div class="ks-pdp-recommended-slider">
                    <div ref="recommendedScroll" class="ks-pdp-recommended-scroll">
                        <a
                            v-for="p in recommendedProducts"
                            :key="p.sku || p.slug"
                            class="ks-pdp-recommended-card"
                            :href="recProductPath(p)"
                            draggable="false"
                            @click="onRecommendedCardClick(p, $event)"
                        >
                            <div class="ks-plp-card-img-wrap">
                                <span
                                    v-if="recShowSaleFlag(p)"
                                    class="ks-plp-card-sale-flag"
                                    aria-hidden="true"
                                >
                                    <span lang="en" dir="ltr">{{ recDiscountPercent(p) }}%</span>
                                </span>
                                <img
                                    :src="normalizeCatalogImageUrl(p.image)"
                                    :alt="p.name"
                                    class="ks-plp-card-img"
                                    loading="lazy"
                                    decoding="async"
                                    referrerpolicy="no-referrer"
                                    draggable="false"
                                />
                            </div>
                            <div class="ks-pdp-recommended-body">
                                <p class="ks-pdp-recommended-brand">{{ (p.brand?.name || "").toUpperCase() }}</p>
                                <p class="ks-pdp-recommended-name">{{ recDisplayName(p) }}</p>
                                <div class="ks-pdp-recommended-price">
                                    <span
                                        v-if="recHasDiscount(p)"
                                        class="ks-pdp-recommended-price-was ks-price-strike"
                                        v-html="formatPriceHtml(p.price.old, p.price.currency)"
                                    ></span>
                                    <span
                                        class="ks-pdp-recommended-price-now"
                                        v-html="formatPromoPriceHtml(p.price.current, p.price.currency)"
                                    ></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <button
                        v-show="recCanScrollPrev"
                        type="button"
                        class="ks-pdp-recommended-arrow ks-pdp-recommended-arrow--prev"
                        :aria-label="t.recommended_scroll_prev"
                        @click="scrollRecommended(-1)"
                    >
                        ‹
                    </button>
                    <button
                        v-show="recCanScrollNext"
                        type="button"
                        class="ks-pdp-recommended-arrow ks-pdp-recommended-arrow--next"
                        :aria-label="t.recommended_scroll_next"
                        @click="scrollRecommended(1)"
                    >
                        ›
                    </button>
                </div>
                </div>
            </section>
        </div>

        <transition name="ks-cart-toast">
            <div
                v-if="cartToastVisible"
                class="ks-cart-toast"
                role="status"
                aria-live="polite"
            >
                <span class="ks-cart-toast__icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.4"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4.5 12.5l5 5 10-11"
                        />
                    </svg>
                </span>
                <span class="ks-cart-toast__text">{{ t.added_to_cart }}</span>
            </div>
        </transition>
    </section>
</template>

<script>
import { fetchCatalogProductBySlug, fetchCatalogProductRecommended } from "@/utils/catalogApi";
import { addToCartStorage, getCartItems } from "@/utils/cartStorage";
import { embedWesternDigits, formatWebsiteCurrency, formatWebsiteCurrencyParts } from "@/utils/westernNumerals";
import { normalizeCatalogImageUrl } from "@/utils/catalogImageUrl";
import { applyProductShareMeta } from "@/utils/siteShareMeta.js";
import { readHorizontalSwipeDelta, touchPoint } from "@/utils/imageSwipe.js";
import { sortVariantsBySize } from "@/utils/sortSizes.js";
import {
    currencySuffixHtml,
    escapeHtml,
    formatWebsitePriceHtml,
    priceAmountPartsHtml,
    pricePromoHtml as buildPricePromoHtml,
    wrapArabicPriceHtml,
    wrapEnglishPriceHtml,
} from "@/utils/websiteCurrencyDisplay";

export default {
    name: "ProductDetails",
    data() {
        return {
            loading: false,
            product: null,
            mainImage: "",
            selectedVariantSku: "",
            activeTab: "description",
            quantity: 1,
            nowTs: Date.now(),
            timerHandle: null,
            mainZoom: 1,
            mainZoomPanX: 0,
            mainZoomPanY: 0,
            mainZoomPanning: false,
            _mainZoomPanLast: null,
            _mainZoomPanCleanup: null,
            /** تكبير الصورة بالعجلة/السحب: فقط مع ماوس/لوحة دقيقة — ليس باللمس */
            galleryPointerFine: true,
            _gallerySwipe: null,
            _gallerySwipePointerId: null,
            recCanScrollPrev: false,
            recCanScrollNext: true,
            _recScrollCleanup: null,
            _touchPinch: null,
            _touchPinchActive: false,
            _mainPageScrollLastY: null,
            _mainTouchScrollAxis: null,
            _lastGalleryTap: 0,
            _mainTouchCleanup: null,
            cartToastVisible: false,
            _cartToastTimer: null,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        slug() {
            return this.$route.params.slug;
        },
        t() {
            if (this.locale === "ar") {
                return {
                    loading: "جاري تحميل المنتج...",
                    not_found: "المنتج غير موجود.",
                    after_discount: "السعر بعد الخصم",
                    rrp: "السعر الأصلي",
                    limited_time: "لفترة محدودة",
                    size: "المقاس",
                    add_to_cart: "إضافة إلى السلة",
                    add_to_basket: "أضف إلى السلة",
                    quantity: "الكمية",
                    shipping_hint: "شحن سريع وخيارات دفع آمنة",
                    description: "وصف المنتج",
                    details: "تفاصيل المنتج",
                    material: "الخامة",
                    details_empty: "لا توجد تفاصيل إضافية.",
                    no_description: "لا يوجد وصف متاح حاليًا.",
                    brand: "الماركة",
                    sku: "رمز المنتج",
                    category: "التصنيف",
                    color: "اللون",
                    available_sizes: "المقاسات المتوفرة",
                    product_images: "صور المنتج",
                    scroll_thumbs_prev: "تمرير المصغّرات للخلف",
                    scroll_thumbs_next: "تمرير المصغّرات للأمام",
                    zoom_in: "تكبير",
                    zoom_out: "تصغير",
                    zoom_reset: "إعادة التعيين",
                    recommended_products: "منتجات موصى بها:",
                    recommended_scroll_prev: "السابق",
                    recommended_scroll_next: "التالي",
                    added_to_cart: "تمت الإضافة إلى السلة",
                };
            }
            return {
                loading: "Loading product...",
                not_found: "Product not found.",
                after_discount: "after discount",
                rrp: "RRP",
                limited_time: "limited-time only",
                size: "Size",
                add_to_cart: "Add to cart",
                add_to_basket: "ADD TO BASKET",
                quantity: "Quantity",
                shipping_hint: "Fast shipping and secure payment options",
                description: "Product description",
                details: "Product details",
                material: "Material",
                details_empty: "No further details available.",
                no_description: "No description available.",
                brand: "Brand",
                sku: "SKU",
                category: "Category",
                color: "Color",
                available_sizes: "Available sizes",
                product_images: "Product images",
                scroll_thumbs_prev: "Scroll thumbnails",
                scroll_thumbs_next: "Scroll thumbnails",
                zoom_in: "Zoom in",
                zoom_out: "Zoom out",
                zoom_reset: "Reset zoom",
                recommended_products: "Recommended products:",
                recommended_scroll_prev: "Previous",
                recommended_scroll_next: "Next",
                added_to_cart: "Added to basket",
            };
        },
        recommendedProducts() {
            return Array.isArray(this.product?.recommended_products)
                ? this.product.recommended_products.filter((p) => p?.slug)
                : [];
        },
        sizeOptions() {
            if (!this.product?.variants?.length) {
                return [];
            }
            return sortVariantsBySize(this.product.variants);
        },
        firstInStockVariantSku() {
            return this.sizeOptions.find((variant) => variant.in_stock)?.sku || "";
        },
        galleryImages() {
            const list = Array.isArray(this.product?.images) ? this.product.images.filter(Boolean) : [];
            const raw = list.length ? list : this.product?.image ? [this.product.image] : [];
            return raw.map((u) => normalizeCatalogImageUrl(u)).filter(Boolean);
        },
        hasDiscount() {
            const oldPrice = Number(this.activeDisplayPrice.old || 0);
            const nowPrice = Number(this.activeDisplayPrice.current || 0);
            if (!oldPrice || !nowPrice || oldPrice <= nowPrice) {
                return false;
            }
            const abs = oldPrice - nowPrice;
            const ratio = abs / oldPrice;
            return abs >= 1 || ratio >= 0.02;
        },
        showSalePromoBox() {
            // المربع الأحمر / شارة الخصم فقط عند خصم حقيقي (ليس فرق تسعير نفسي)
            return this.hasDiscount === true;
        },
        discountPercent() {
            const oldPrice = Number(this.activeDisplayPrice.old || 0);
            const nowPrice = Number(this.activeDisplayPrice.current || 0);
            if (!oldPrice || !nowPrice || oldPrice <= nowPrice) return 0;
            return Math.round(((oldPrice - nowPrice) / oldPrice) * 100);
        },
        promoEndsAtTs() {
            const raw = this.product?.promo_ends_at;
            if (!raw) return null;
            const ts = new Date(raw).getTime();
            return Number.isFinite(ts) ? ts : null;
        },
        countdownTargetTs() {
            return this.promoEndsAtTs;
        },
        countdownText() {
            if (!this.countdownTargetTs) return "";
            const diffMs = this.countdownTargetTs - this.nowTs;
            if (diffMs <= 0) return "";
            const totalSec = Math.floor(diffMs / 1000);
            const days = Math.floor(totalSec / 86400);
            const hours = Math.floor((totalSec % 86400) / 3600);
            const mins = Math.floor((totalSec % 3600) / 60);
            const secs = totalSec % 60;
            if (this.locale === "ar") {
                return `ينتهي خلال ${embedWesternDigits(days)}ي ${embedWesternDigits(hours)}س ${embedWesternDigits(mins)}د ${embedWesternDigits(secs)}ث`;
            }
            return `ends in ${days}d ${hours}h ${mins}m ${secs}s`;
        },
        /** تنسيق العدّاد كما على keepersport.net (مثال: 4 hr 0 min 29 sec) */
        countdownShortText() {
            if (!this.countdownTargetTs) return this.locale === "ar" ? "ينتهي قريبًا" : "ending soon";
            const diffMs = this.countdownTargetTs - this.nowTs;
            if (diffMs <= 0) return this.locale === "ar" ? "انتهى" : "ended";
            const totalSec = Math.floor(diffMs / 1000);
            const days = Math.floor(totalSec / 86400);
            const hours = Math.floor((totalSec % 86400) / 3600);
            const mins = Math.floor((totalSec % 3600) / 60);
            const secs = totalSec % 60;
            if (this.locale === "ar") {
                if (days > 0) {
                    return `${embedWesternDigits(days)} يوم ${embedWesternDigits(hours)} س ${embedWesternDigits(mins)} د ${embedWesternDigits(secs)} ث`;
                }
                return `${embedWesternDigits(hours)} س ${embedWesternDigits(mins)} د ${embedWesternDigits(secs)} ث`;
            }
            if (days > 0) return `${days} d ${hours} hr ${mins} min ${secs} sec`;
            return `${hours} hr ${mins} min ${secs} sec`;
        },
        qtyOptions() {
            const max = this.maxPurchasableQty;
            if (max <= 0) {
                return [1];
            }
            return Array.from({ length: max }, (_, i) => i + 1);
        },
        maxPurchasableQty() {
            const variant = this.selectedVariant;
            if (!variant || !variant.in_stock) {
                return 0;
            }
            // null/undefined: Number(null)===0 في JS — لا تعتبرها نفاد مخزون
            if (variant.quantity == null || variant.quantity === "") {
                return 1;
            }
            const stock = Number(variant.quantity);
            if (!Number.isFinite(stock) || stock <= 0) {
                return 0;
            }
            return Math.min(99, Math.floor(stock));
        },
        cartQtyForSelectedSku() {
            const sku = this.selectedVariant?.sku || this.product?.sku;
            if (!sku) {
                return 0;
            }
            const row = getCartItems().find((item) => item.sku === sku || item.id === sku);
            return Number(row?.qty || 0);
        },
        remainingStockForSelected() {
            return Math.max(0, this.maxPurchasableQty - this.cartQtyForSelectedSku);
        },
        canAddToCart() {
            return this.remainingStockForSelected > 0 && this.maxPurchasableQty > 0;
        },
        mainZoomTransformStyle() {
            return {
                transform: `translate(${this.mainZoomPanX}px, ${this.mainZoomPanY}px) scale(${this.mainZoom})`,
            };
        },
        mainZoomPercent() {
            return `${embedWesternDigits(Math.round(this.mainZoom * 100))}%`;
        },
        mobileRrpPriceHtml() {
            if (!this.activeDisplayPrice.old) {
                return "";
            }
            return this.priceWithCurrencyHtml(this.activeDisplayPrice.old, this.activeDisplayPrice.currency);
        },
        mobileSoloPriceHtml() {
            return this.priceWithCurrencyHtml(this.activeDisplayPrice.current, this.activeDisplayPrice.currency);
        },
        desktopRrpPriceHtml() {
            if (!this.activeDisplayPrice.old) {
                return "";
            }
            return this.priceWithCurrencyHtml(this.activeDisplayPrice.old, this.activeDisplayPrice.currency);
        },
        desktopSoloPriceHtml() {
            return this.priceWithCurrencyHtml(this.activeDisplayPrice.current, this.activeDisplayPrice.currency);
        },
        mobileSalePriceHtml() {
            const parts = formatWebsiteCurrencyParts(
                this.activeDisplayPrice.current,
                this.activeDisplayPrice.currency || "SAR",
                { maximumFractionDigits: 2 }
            );
            if (!parts.length) {
                const fallback = escapeHtml(
                    this.formatPrice(this.activeDisplayPrice.current, this.activeDisplayPrice.currency)
                );
                return this.locale === "ar"
                    ? wrapArabicPriceHtml(fallback, this.locale)
                    : wrapEnglishPriceHtml(fallback);
            }
            return this.priceWithCurrencyHtml(this.activeDisplayPrice.current, this.activeDisplayPrice.currency);
        },
        /** عدد المصغّرات الظاهرة دفعة واحدة (الباقي بالتمرير) */
        thumbViewportCount() {
            return 5;
        },
        primaryCategoryName() {
            return this.product?.categories?.[0]?.name || "";
        },
        hasProductDescription() {
            return String(this.product?.description || "").trim() !== "";
        },
        detailsHtml() {
            const raw = String(this.product?.description_details || "").trim();
            if (!raw) return "";
            const html = /<[a-z][\s\S]*>/i.test(raw)
                ? raw
                : `<p>${raw.replace(/\n/g, "<br>")}</p>`;
            return this.stripRedundantPdpTabHeadings(html);
        },
        hasMaterialContent() {
            return String(this.product?.description_material || "").trim() !== "";
        },
        materialHtml() {
            const raw = String(this.product?.description_material || "").trim();
            if (!raw) return `<p>${this.t.no_description}</p>`;
            const html = /<[a-z][\s\S]*>/i.test(raw)
                ? raw
                : `<p>${raw.replace(/\n/g, "<br>")}</p>`;
            return this.stripRedundantPdpTabHeadings(html);
        },
        productTabs() {
            const tabs = [];
            if (this.hasProductDescription) {
                tabs.push({ key: "description", label: this.t.description });
            }
            if (this.productDetailsRows.length || this.detailsHtml) {
                tabs.push({ key: "details", label: this.t.details });
            }
            if (this.hasMaterialContent) {
                tabs.push({ key: "material", label: this.t.material });
            }
            if (!tabs.length) {
                tabs.push({ key: "description", label: this.t.description });
            }
            return tabs;
        },
        descriptionHtml() {
            const raw = String(this.product?.description || "").trim();
            if (!raw) return `<p>${this.t.no_description}</p>`;
            const html = /<[a-z][\s\S]*>/i.test(raw)
                ? raw
                : `<p>${raw.replace(/\n/g, "<br>")}</p>`;
            return this.stripRedundantPdpTabHeadings(html);
        },
        taglineHtml() {
            const raw = String(this.product?.tagline || "").trim();
            if (!raw) return "";
            if (/<[a-z][\s\S]*>/i.test(raw)) return raw;
            return `<span>${raw.replace(/\n/g, "<br>")}</span>`;
        },
        /** صفوف تبويب «التفاصيل»: خصائص GraphQL + ماركة/رمز/تصنيف/مقاسات (بدون تكرار مع الـ facets) */
        productDetailsRows() {
            const p = this.product;
            if (!p) return [];
            const rows = [];
            const usedLabels = new Set();

            const norm = (s) => String(s || "").trim().toLowerCase();

            const pushRow = (label, value) => {
                const v = String(value ?? "").trim();
                const l = String(label ?? "").trim();
                if (!l || !v || v === "-") return;
                const key = norm(l);
                if (usedLabels.has(key)) return;
                usedLabels.add(key);
                rows.push({ label: l, value: v });
            };

            const facets = Array.isArray(p.attribute_facets) ? p.attribute_facets : [];
            for (const af of facets) {
                const label = (af.attr_label || af.attr_key || "").trim();
                const val = (af.value_label || af.value_key || "").trim();
                pushRow(label, val);
            }

            const colorNames = (Array.isArray(p.colors) ? p.colors : [])
                .map((c) => (c && c.name ? String(c.name).trim() : ""))
                .filter(Boolean);
            if (colorNames.length) {
                pushRow(this.t.color, colorNames.join(", "));
            }

            pushRow(this.t.brand, p.brand?.name || "");
            pushRow(this.t.sku, p.sku || "");
            pushRow(this.t.category, this.primaryCategoryName || "");
            if (this.sizeOptions.length) {
                pushRow(this.t.available_sizes, this.sizeOptions.map((v) => v.size).filter(Boolean).join(", "));
            }

            return rows;
        },
        selectedVariant() {
            if (!this.selectedVariantSku || !this.product?.variants?.length) {
                return null;
            }
            return this.product.variants.find((variant) => variant.sku === this.selectedVariantSku) || null;
        },
        /** السعر الظاهر: سعر المنتج (مع الخصم) أولاً — أسعار المقاسات غالباً listPrice فقط بدون عرض */
        activeDisplayPrice() {
            const currency = this.product?.price?.currency || "SAR";
            const productOld = this.product?.price?.old ?? null;
            const productCurrent = Number(this.product?.price?.current);
            if (Number.isFinite(productCurrent) && productCurrent > 0) {
                return { current: productCurrent, old: productOld, currency };
            }
            const variantPrice = Number(this.selectedVariant?.price);
            if (Number.isFinite(variantPrice) && variantPrice > 0) {
                return { current: variantPrice, old: productOld, currency };
            }
            const fallback = (this.product?.variants || []).find((v) => Number(v?.price) > 0);
            return {
                current: Number(fallback?.price || 0),
                old: productOld,
                currency: fallback?.currency || currency,
            };
        },
        /** تايمر العرض: أرقام أوضح، وحدات عادية — كما في keepersport */
        promoTimerHtml() {
            if (!this.countdownTargetTs) {
                return this.locale === "ar"
                    ? '<span class="ks-timer-fallback">ينتهي قريبًا</span>'
                    : '<span class="ks-timer-fallback">ending soon</span>';
            }
            const diffMs = this.countdownTargetTs - this.nowTs;
            if (diffMs <= 0) {
                return this.locale === "ar" ? '<span class="ks-timer-fallback">انتهى</span>' : '<span class="ks-timer-fallback">ended</span>';
            }
            const n = (x) => `<span class="ks-timer-num" lang="en" dir="ltr">${x}</span>`;
            const u = (s) => `<span class="ks-timer-unit">${s}</span>`;
            const totalSec = Math.floor(diffMs / 1000);
            const days = Math.floor(totalSec / 86400);
            const hours = Math.floor((totalSec % 86400) / 3600);
            const mins = Math.floor((totalSec % 3600) / 60);
            const secs = totalSec % 60;
            if (this.locale === "ar") {
                if (days > 0) return `${n(days)}${u(" يوم ")}${n(hours)}${u(" س ")}${n(mins)}${u(" د ")}${n(secs)}${u(" ث")}`;
                return `${n(hours)}${u(" س ")}${n(mins)}${u(" د ")}${n(secs)}${u(" ث")}`;
            }
            if (days > 0) {
                return `${n(days)}${u(" d ")}${n(hours)}${u(" hr ")}${n(mins)}${u(" min ")}${n(secs)}${u(" sec")}`;
            }
            return `${n(hours)}${u(" hr ")}${n(mins)}${u(" min ")}${n(secs)}${u(" sec")}`;
        },
        /** سعر العرض: عملة + رقم كبير + كسور علوية صغيرة */
        salePricePromoHtml() {
            if (!this.product) return "";
            return buildPricePromoHtml(
                this.activeDisplayPrice.current,
                this.activeDisplayPrice.currency || "SAR",
                this.locale
            );
        },
    },
    watch: {
        slug() {
            this.fetchProduct();
        },
        selectedVariantSku() {
            this.applyVariantMainImage();
            this.clampQuantityToStock();
        },
        mainImage() {
            this.resetMainZoom();
        },
        mainZoom(value, prev) {
            if (value <= 1 && prev > 1) {
                this.mainZoomPanX = 0;
                this.mainZoomPanY = 0;
            }
        },
        recommendedProducts() {
            this.$nextTick(() => this.bindRecommendedCarousel());
        },
    },
    mounted() {
        this.syncGalleryPointerFine();
        if (typeof window !== "undefined") {
            window.addEventListener("resize", this.syncGalleryPointerFine, { passive: true });
        }
        this.fetchProduct();
        this.timerHandle = setInterval(() => {
            this.nowTs = Date.now();
        }, 1000);
    },
    beforeUnmount() {
        if (typeof window !== "undefined") {
            window.removeEventListener("resize", this.syncGalleryPointerFine);
        }
        this.detachMainZoomPanListeners();
        this.unbindMainTouchListeners();
        this.unbindRecommendedCarousel();
        if (this.timerHandle) {
            clearInterval(this.timerHandle);
            this.timerHandle = null;
        }
        clearTimeout(this._cartToastTimer);
    },
    methods: {
        normalizeCatalogImageUrl,
        /** يزيل عناوين مكررة من HTML الوصف (مثل «Product description» فوق التبويبات). */
        stripRedundantPdpTabHeadings(html) {
            const labels = new Set(
                [
                    this.t.description,
                    this.t.details,
                    this.product?.name,
                    "Product description",
                    "Product details",
                    "Material",
                    "Produktbeschreibung",
                    "Produktdetails",
                    "Descrizione prodotto",
                    "Dettagli prodotto",
                    "وصف المنتج",
                    "تفاصيل المنتج",
                ]
                    .map((s) => String(s || "").trim())
                    .filter(Boolean)
            );
            let out = String(html || "");
            for (const label of labels) {
                const esc = label.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
                const patterns = [
                    new RegExp(`^\\s*<h[1-3][^>]*>\\s*${esc}\\s*</h[1-3]>\\s*`, "i"),
                    new RegExp(`^\\s*<p[^>]*>\\s*<strong>\\s*${esc}\\s*</strong>\\s*</p>\\s*`, "i"),
                    new RegExp(`^\\s*<p[^>]*>\\s*${esc}\\s*</p>\\s*`, "i"),
                ];
                for (const re of patterns) {
                    out = out.replace(re, "");
                }
            }
            return out.trim();
        },
        async fetchProduct() {
            this.loading = true;
            try {
                this.product = await fetchCatalogProductBySlug(this.slug);
                applyProductShareMeta(this.product, this.locale);
                this.selectedVariantSku = this.firstInStockVariantSku || this.sizeOptions?.[0]?.sku || "";
                this.applyVariantMainImage();
                this.activeTab = this.productTabs[0]?.key || "description";
                this.$nextTick(() => {
                    this.scrollActiveThumbIntoView();
                    this.bindRecommendedCarousel();
                    this.bindMainTouchListeners();
                });
                this.fetchRecommendedProducts();
            } finally {
                this.loading = false;
            }
        },
        async fetchRecommendedProducts() {
            const slug = this.slug;
            if (!slug) {
                return;
            }
            try {
                const items = await fetchCatalogProductRecommended(slug);
                if (this.slug !== slug || !this.product) {
                    return;
                }
                if (!items.length) {
                    return;
                }
                this.product = {
                    ...this.product,
                    recommended_products: items,
                };
                this.$nextTick(() => this.bindRecommendedCarousel());
            } catch {
                /* الموصى بها ثانوية — لا نُفشل صفحة المنتج */
            }
        },
        recHasDiscount(product) {
            const oldPrice = Number(product?.price?.old || 0);
            const nowPrice = Number(product?.price?.current || 0);
            if (!oldPrice || !nowPrice || oldPrice <= nowPrice) {
                return false;
            }
            const abs = oldPrice - nowPrice;
            const ratio = abs / oldPrice;
            return abs >= 1 || ratio >= 0.02;
        },
        recDiscountPercent(product) {
            const oldPrice = Number(product?.price?.old || 0);
            const nowPrice = Number(product?.price?.current || 0);
            if (!oldPrice || !nowPrice || oldPrice <= nowPrice) return 0;
            return Math.round(((oldPrice - nowPrice) / oldPrice) * 100);
        },
        recShowSaleFlag(product) {
            return this.recHasDiscount(product) && this.recDiscountPercent(product) > 0;
        },
        recDisplayName(product) {
            const name = String(product?.name || "").trim();
            const brand = String(product?.brand?.name || "").trim();
            if (!brand) return name;
            const prefix = new RegExp(`^${brand}\\s+`, "i");
            return name.replace(prefix, "").trim() || name;
        },
        recProductPath(product) {
            const slug = String(product?.slug || "").trim().replace(/\.html$/i, "");
            return slug ? `/${this.locale}/product/${slug}` : `/${this.locale}`;
        },
        onRecommendedCardClick(product, e) {
            const target = this.recProductPath(product);
            if (!target || target === `/${this.locale}`) {
                return;
            }
            if (e?.metaKey || e?.ctrlKey || e?.shiftKey || e?.altKey || e?.button === 1) {
                return;
            }
            if (e) {
                e.preventDefault();
            }
            if (this.$route.fullPath === target || this.$route.path === target) {
                return;
            }
            this.$router.push(target).catch(() => {
                window.location.assign(target);
            });
        },
        unbindRecommendedCarousel() {
            if (typeof this._recScrollCleanup === "function") {
                this._recScrollCleanup();
                this._recScrollCleanup = null;
            }
        },
        recommendedCardStride(el) {
            if (!el) {
                return 0;
            }
            const card = el.querySelector(".ks-pdp-recommended-card");
            if (!card) {
                return Math.max(180, Math.floor(el.clientWidth / 3));
            }
            return card.offsetWidth;
        },
        snapRecommendedScroll() {
            const el = this.$refs.recommendedScroll;
            const stride = this.recommendedCardStride(el);
            if (!el || !stride) {
                return;
            }
            const index = Math.round(el.scrollLeft / stride);
            el.scrollTo({ left: index * stride, behavior: "smooth" });
            window.setTimeout(() => this.updateRecScrollArrows(), 320);
        },
        updateRecScrollArrows() {
            const el = this.$refs.recommendedScroll;
            if (!el) {
                this.recCanScrollPrev = false;
                this.recCanScrollNext = false;
                return;
            }
            const maxScroll = el.scrollWidth - el.clientWidth;
            this.recCanScrollPrev = el.scrollLeft > 4;
            this.recCanScrollNext = maxScroll > 4 && el.scrollLeft < maxScroll - 4;
        },
        bindRecommendedCarousel() {
            this.unbindRecommendedCarousel();
            const el = this.$refs.recommendedScroll;
            if (!el) {
                return;
            }

            const onScroll = () => this.updateRecScrollArrows();
            el.addEventListener("scroll", onScroll, { passive: true });
            if (typeof window !== "undefined") {
                window.addEventListener("resize", onScroll, { passive: true });
            }

            const onWheel = (e) => {
                const absX = Math.abs(e.deltaX);
                const absY = Math.abs(e.deltaY);
                if (absX <= absY && !e.shiftKey) {
                    return;
                }
                const delta = absX > absY ? e.deltaX : e.deltaY;
                if (Math.abs(delta) < 2) {
                    return;
                }
                el.scrollLeft += delta;
                if (e.cancelable) {
                    e.preventDefault();
                }
                this.updateRecScrollArrows();
            };

            el.addEventListener("wheel", onWheel, { passive: false });
            this.updateRecScrollArrows();

            this._recScrollCleanup = () => {
                el.removeEventListener("scroll", onScroll);
                el.removeEventListener("wheel", onWheel);
                el.classList.remove("ks-pdp-recommended-scroll--dragging");
                if (typeof window !== "undefined") {
                    window.removeEventListener("resize", onScroll);
                }
            };
        },
        scrollRecommended(direction) {
            const el = this.$refs.recommendedScroll;
            if (!el) {
                return;
            }
            const stride = this.recommendedCardStride(el);
            el.scrollBy({ left: direction * stride, behavior: "smooth" });
            window.setTimeout(() => this.snapRecommendedScroll(), 280);
        },
        applyVariantMainImage() {
            if (!this.product) {
                this.mainImage = "";
                return;
            }
            const v = this.selectedVariant;
            if (v?.image) {
                this.mainImage = normalizeCatalogImageUrl(v.image);
                return;
            }
            const imgs = this.galleryImages;
            this.mainImage = normalizeCatalogImageUrl(imgs[0] || this.product.image || "");
        },
        selectGalleryImage(image) {
            this.mainImage = image;
            this.$nextTick(() => this.scrollActiveThumbIntoView());
        },
        canGallerySwipe() {
            return this.galleryImages.length > 1 && this.mainZoom <= 1;
        },
        touchPinchDistance(touches) {
            if (!touches || touches.length < 2) {
                return 0;
            }
            const dx = touches[0].clientX - touches[1].clientX;
            const dy = touches[0].clientY - touches[1].clientY;
            return Math.hypot(dx, dy);
        },
        onMainTouchStart(e) {
            if (e.touches.length === 2) {
                this._gallerySwipe = null;
                this._mainTouchScrollAxis = null;
                this._mainPageScrollLastY = null;
                this._touchPinchActive = true;
                this._touchPinch = {
                    startDist: this.touchPinchDistance(e.touches),
                    startZoom: this.mainZoom,
                };
                if (e.cancelable) {
                    e.preventDefault();
                }
                return;
            }
            if (e.touches.length !== 1) {
                return;
            }
            const t = e.touches[0];
            if (this.mainZoom > 1) {
                this._gallerySwipe = null;
                this._mainTouchScrollAxis = null;
                this._mainPageScrollLastY = null;
                this.mainZoomPanning = true;
                this._mainZoomPanLast = { x: t.clientX, y: t.clientY };
                if (e.cancelable) {
                    e.preventDefault();
                }
                return;
            }
            this._mainTouchScrollAxis = null;
            this._mainPageScrollLastY = t.clientY;
            this._gallerySwipe = {
                startX: t.clientX,
                startY: t.clientY,
                startTime: Date.now(),
            };
        },
        onMainTouchMove(e) {
            if (this._touchPinch && e.touches.length >= 2) {
                const dist = this.touchPinchDistance(e.touches);
                if (this._touchPinch.startDist > 8) {
                    const next = Math.max(
                        1,
                        Math.min(3, this._touchPinch.startZoom * (dist / this._touchPinch.startDist))
                    );
                    this.mainZoom = next;
                    if (next <= 1) {
                        this.mainZoomPanX = 0;
                        this.mainZoomPanY = 0;
                    }
                }
                if (e.cancelable) {
                    e.preventDefault();
                }
                return;
            }
            if (this.mainZoomPanning && e.touches.length === 1 && this._mainZoomPanLast) {
                const t = e.touches[0];
                this.mainZoomPanX += t.clientX - this._mainZoomPanLast.x;
                this.mainZoomPanY += t.clientY - this._mainZoomPanLast.y;
                this._mainZoomPanLast = { x: t.clientX, y: t.clientY };
                if (e.cancelable) {
                    e.preventDefault();
                }
                return;
            }
            if (this.mainZoom <= 1 && e.touches.length === 1) {
                const t = e.touches[0];
                const swipe = this._gallerySwipe;
                if (!swipe) {
                    return;
                }
                const dx = t.clientX - swipe.startX;
                const dy = t.clientY - swipe.startY;
                if (!this._mainTouchScrollAxis && (Math.abs(dx) > 8 || Math.abs(dy) > 8)) {
                    this._mainTouchScrollAxis = Math.abs(dy) > Math.abs(dx) ? "y" : "x";
                }
                if (this._mainTouchScrollAxis === "y") {
                    // touch-action:none يمنع تمرير الصفحة — نحرّك عنصر التمرير يدوياً
                    const lastY = this._mainPageScrollLastY ?? t.clientY;
                    const delta = lastY - t.clientY;
                    this._mainPageScrollLastY = t.clientY;
                    if (typeof document !== "undefined" && Math.abs(delta) > 0) {
                        const root = document.scrollingElement || document.documentElement;
                        if (root) {
                            root.scrollTop += delta;
                        } else if (typeof window !== "undefined") {
                            window.scrollBy(0, delta);
                        }
                    }
                    if (e.cancelable) {
                        e.preventDefault();
                    }
                    return;
                }
                if (this._mainTouchScrollAxis === "x" && e.cancelable) {
                    e.preventDefault();
                }
            }
        },
        onMainTouchEnd(e) {
            const wasPinch = this._touchPinchActive;
            const axis = this._mainTouchScrollAxis;
            const swipe = this._gallerySwipe;
            this._touchPinch = null;
            this._touchPinchActive = false;
            this.mainZoomPanning = false;
            this._mainZoomPanLast = null;
            this._mainTouchScrollAxis = null;
            this._mainPageScrollLastY = null;

            if (wasPinch || this.mainZoom > 1) {
                this._gallerySwipe = null;
                return;
            }

            const t = e.changedTouches?.[0];
            if (t && swipe && axis !== "y" && axis !== "x") {
                const dt = Date.now() - (swipe.startTime || 0);
                const dist = Math.hypot(t.clientX - swipe.startX, t.clientY - swipe.startY);
                if (dt < 280 && dist < 12) {
                    const now = Date.now();
                    if (now - this._lastGalleryTap < 320) {
                        this._lastGalleryTap = 0;
                        this.mainZoom = 2;
                        this._gallerySwipe = null;
                        return;
                    }
                    this._lastGalleryTap = now;
                }
            }

            if (axis === "x" && swipe && this.mainZoom <= 1.02) {
                this.onGallerySwipeEnd(e);
            } else {
                this._gallerySwipe = null;
            }
        },
        bindMainTouchListeners() {
            this.unbindMainTouchListeners();
            const host = this.$refs.zoomHost;
            if (!host) return;

            const onStart = (e) => this.onMainTouchStart(e);
            const onMove = (e) => this.onMainTouchMove(e);
            const onEnd = (e) => this.onMainTouchEnd(e);

            // دائماً غير-passive: القرص يحتاج preventDefault، والتمرير العمودي ننفّذه يدوياً
            host.addEventListener("touchstart", onStart, { passive: false });
            host.addEventListener("touchmove", onMove, { passive: false });
            host.addEventListener("touchend", onEnd, { passive: true });
            host.addEventListener("touchcancel", onEnd, { passive: true });
            this._mainTouchRebind = null;

            let io = null;
            if (typeof IntersectionObserver !== "undefined") {
                io = new IntersectionObserver(
                    (entries) => {
                        if (!entries.some((entry) => entry.isIntersecting)) {
                            return;
                        }
                        this._touchPinch = null;
                        this._touchPinchActive = false;
                        this.mainZoomPanning = false;
                        this._mainZoomPanLast = null;
                        this._mainTouchScrollAxis = null;
                        this._mainPageScrollLastY = null;
                        this._gallerySwipe = null;
                    },
                    { threshold: 0.2 }
                );
                io.observe(host);
            }

            this._mainTouchCleanup = () => {
                if (io) {
                    io.disconnect();
                    io = null;
                }
                this._mainTouchRebind = null;
                host.removeEventListener("touchstart", onStart);
                host.removeEventListener("touchmove", onMove);
                host.removeEventListener("touchend", onEnd);
                host.removeEventListener("touchcancel", onEnd);
            };
        },
        unbindMainTouchListeners() {
            if (typeof this._mainTouchCleanup === "function") {
                this._mainTouchCleanup();
                this._mainTouchCleanup = null;
            }
        },
        stepGalleryImage(dir) {
            const imgs = this.galleryImages;
            if (imgs.length <= 1) {
                return;
            }
            let idx = imgs.indexOf(this.mainImage);
            if (idx < 0) {
                idx = 0;
            }
            const next = (idx + dir + imgs.length) % imgs.length;
            this.selectGalleryImage(imgs[next]);
            this.resetMainZoom();
        },
        onGallerySwipeStart(e) {
            if (!this.canGallerySwipe()) {
                return;
            }
            const t = touchPoint(e);
            if (!t) {
                return;
            }
            this._gallerySwipe = { startX: t.clientX, startY: t.clientY };
        },
        onGallerySwipeEnd(e) {
            if (!this._gallerySwipe) {
                return;
            }
            const t = e.changedTouches?.[0];
            if (!t) {
                this._gallerySwipe = null;
                return;
            }
            const dx = readHorizontalSwipeDelta(this._gallerySwipe, t.clientX, t.clientY);
            this._gallerySwipe = null;
            if (dx === null) {
                return;
            }
            this.stepGalleryImage(dx < 0 ? 1 : -1);
        },
        onGallerySwipePointerDown(e) {
            if (e.pointerType === "touch" || !this.canGallerySwipe()) {
                return;
            }
            this._gallerySwipePointerId = e.pointerId;
            this._gallerySwipe = { startX: e.clientX, startY: e.clientY };
        },
        onGallerySwipePointerUp(e) {
            if (
                e.pointerType === "touch" ||
                this._gallerySwipePointerId !== e.pointerId ||
                !this._gallerySwipe
            ) {
                return;
            }
            this._gallerySwipePointerId = null;
            const dx = readHorizontalSwipeDelta(this._gallerySwipe, e.clientX, e.clientY);
            this._gallerySwipe = null;
            if (dx === null) {
                return;
            }
            this.stepGalleryImage(dx < 0 ? 1 : -1);
        },
        scrollGalleryThumbs(direction) {
            const el = this.$refs.galleryThumbStrip;
            if (!el) return;
            /* يجب أن يطابق .ks-gallery--pdp: --ks-thumb-size و --ks-thumb-gap */
            const thumb = 72;
            const gap = 8;
            const stride = thumb + gap;
            const mobile = typeof window !== "undefined" && window.innerWidth <= 715;
            if (mobile) {
                el.scrollBy({ left: direction * stride, behavior: "smooth" });
            } else {
                el.scrollBy({ top: direction * stride, behavior: "smooth" });
            }
        },
        scrollActiveThumbIntoView() {
            const strip = this.$refs.galleryThumbStrip;
            if (!strip) return;
            const active = strip.querySelector(".ks-gallery__thumb--active");
            if (active && typeof active.scrollIntoView === "function") {
                active.scrollIntoView({ block: "nearest", inline: "nearest", behavior: "smooth" });
            }
        },
        formatPrice(amount, currency) {
            return formatWebsiteCurrency(amount, currency || "SAR", { maximumFractionDigits: 2 });
        },
        formatPriceHtml(amount, currency) {
            return formatWebsitePriceHtml(amount, currency || "SAR", this.locale, {
                maximumFractionDigits: 2,
            });
        },
        formatPromoPriceHtml(amount, currency) {
            return buildPricePromoHtml(amount, currency || "SAR", this.locale, {
                maximumFractionDigits: 2,
            });
        },
        priceWithCurrencyHtml(amount, currency) {
            const amountHtml = priceAmountPartsHtml(
                amount,
                currency || "SAR",
                { maximumFractionDigits: 2 },
                { majorClass: "ks-pdp-price-major", minorClass: "ks-pdp-price-minor" }
            );
            if (!amountHtml) {
                const fallback = escapeHtml(this.formatPrice(amount, currency));
                return this.locale === "ar"
                    ? wrapArabicPriceHtml(fallback, this.locale)
                    : wrapEnglishPriceHtml(fallback);
            }
            return this.locale === "ar"
                ? wrapArabicPriceHtml(amountHtml, this.locale)
                : wrapEnglishPriceHtml(amountHtml);
        },
        resetMainZoom() {
            this.mainZoom = 1;
            this.mainZoomPanX = 0;
            this.mainZoomPanY = 0;
            this.mainZoomPanning = false;
            this._mainZoomPanLast = null;
            this.detachMainZoomPanListeners();
        },
        mainZoomStep(direction) {
            const next = Math.max(1, Math.min(3, this.mainZoom + direction * 0.25));
            if (next <= 1) this.resetMainZoom();
            else this.mainZoom = next;
        },
        syncGalleryPointerFine() {
            if (typeof window === "undefined") return;
            const fine = window.matchMedia("(pointer: fine)").matches;
            if (this.galleryPointerFine && !fine) {
                this.resetMainZoom();
            }
            this.galleryPointerFine = fine;
        },
        onMainImageWheel(e) {
            if (!this.product || !this.galleryPointerFine) return;
            // تمرير الصفحة فوق الصورة — الزوم فقط إذا مكبّرة مسبقاً أو مع Ctrl/Cmd
            const wantZoom = this.mainZoom > 1 || e.ctrlKey || e.metaKey;
            if (!wantZoom) {
                return;
            }
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.12 : 0.12;
            const next = Math.max(1, Math.min(3, this.mainZoom + delta));
            if (next <= 1) this.resetMainZoom();
            else this.mainZoom = next;
        },
        onMainZoomPanStart(e) {
            if (!this.galleryPointerFine) return;
            if (this.mainZoom <= 1) return;
            if (e.button !== 0) return;
            this.mainZoomPanning = true;
            this._mainZoomPanLast = { x: e.clientX, y: e.clientY };
            this.attachMainZoomPanListeners();
        },
        attachMainZoomPanListeners() {
            if (typeof this._mainZoomPanCleanup === "function") return;
            const onMove = (ev) => {
                if (!this.mainZoomPanning || !this._mainZoomPanLast) return;
                this.mainZoomPanX += ev.clientX - this._mainZoomPanLast.x;
                this.mainZoomPanY += ev.clientY - this._mainZoomPanLast.y;
                this._mainZoomPanLast = { x: ev.clientX, y: ev.clientY };
            };
            const onUp = () => {
                this.mainZoomPanning = false;
                this.detachMainZoomPanListeners();
            };
            window.addEventListener("mousemove", onMove);
            window.addEventListener("mouseup", onUp, { passive: true });
            this._mainZoomPanCleanup = () => {
                window.removeEventListener("mousemove", onMove);
                window.removeEventListener("mouseup", onUp);
                this._mainZoomPanCleanup = null;
            };
        },
        detachMainZoomPanListeners() {
            if (typeof this._mainZoomPanCleanup === "function") {
                this._mainZoomPanCleanup();
            }
        },
        clampQuantityToStock() {
            const remaining = this.remainingStockForSelected;
            if (remaining <= 0) {
                this.quantity = 1;
                return;
            }
            if (this.quantity > remaining) {
                this.quantity = remaining;
            } else if (this.quantity < 1) {
                this.quantity = 1;
            }
        },
        addToCart() {
            if (!this.product) return;
            const variant = this.selectedVariant;
            if (variant && !variant.in_stock) return;

            const maxStock = this.maxPurchasableQty;
            if (maxStock <= 0) return;

            const remaining = this.remainingStockForSelected;
            if (remaining <= 0) return;

            const qty = Math.min(this.quantity, remaining, maxStock);
            if (qty <= 0) return;

            const result = addToCartStorage({
                id: variant?.sku || this.product.sku,
                sku: variant?.sku || this.product.sku,
                title: this.product.name,
                subtitle: variant?.size ? `Size: ${variant.size}` : this.product.brand?.name || "",
                image: normalizeCatalogImageUrl(variant?.image || this.mainImage),
                price: Number(this.activeDisplayPrice.current || 0),
                price_old: Number(this.activeDisplayPrice.old || 0) || null,
                qty,
                maxStock,
                inStock: variant?.in_stock !== false,
            });
            if (!result?.ok || !result.items?.length) {
                return;
            }
            this.clampQuantityToStock();
            this.showCartToast();
        },
        showCartToast() {
            clearTimeout(this._cartToastTimer);
            this.cartToastVisible = true;
            this._cartToastTimer = setTimeout(() => {
                this.cartToastVisible = false;
            }, 2600);
        },
    },
};
</script>

<style scoped>
.ks-product-page {
    --indicator-color: #b89500;
    --ks-page-pad: clamp(14px, 2.8vw, 36px);
    --ks-sale-red: #d32f2f;
    /* خلفية معرض المنتج/السلايدر تكون أبيض ناصع */
    --ks-pdp-image-surface: #ffffff;
    background: #ffffff;
    color: #393939;
    padding: 0;
    overflow-x: clip;
    box-sizing: border-box;
}
.ks-product-wrap--bleed {
    max-width: none;
    width: 100%;
    margin: 0 auto;
    padding-left: 0;
    padding-right: 0;
    box-sizing: border-box;
    background: #fff;
}
.ks-pv-wide {
    width: 100%;
    margin-bottom: 0;
}
.ks-product-overview {
    --spacing: 8px;
    --detail-width: calc(var(--spacing) * 48);
    --gap: calc(var(--spacing) * 3);
    --indicator-color: #b89500;
    --fs-md: clamp(1.15rem, 3.5vw, 1.35rem);
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0;
    background-color: #373e3e;
    color: #393939;
    border-radius: 0;
    box-sizing: border-box;
    box-shadow: none;
    padding: 0;
}
@media (max-width: 1200px) {
    .ks-product-overview {
        --detail-width: calc(var(--spacing) * 32);
    }
}
.ks-product-split {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: stretch;
    gap: 0;
    width: 100%;
    min-height: 0;
    padding: 15px;
    box-sizing: border-box;
}
.ks-product-title {
    align-items: center;
    padding: 0;
    line-height: 1;
    display: flex;
    margin: 0 0 6px;
    font-size: clamp(28px, 4vw, 50px);
    font-weight: 700;
    color: #000;
    width: 100%;
    box-sizing: border-box;
}
.ks-product-page:lang(ar) .ks-product-title {
    direction: rtl;
    text-align: right;
    justify-content: flex-start;
}
.ks-product-page:lang(en) .ks-product-title {
    direction: ltr;
    text-align: left;
    justify-content: flex-start;
}
.ks-product-title--upper {
    text-transform: uppercase;
}
.ks-product-tagline {
    margin: 0 0 10px;
    padding: 0;
    font-size: clamp(15px, 2.2vw, 18px);
    font-weight: 500;
    line-height: 1.45;
    color: #444;
    width: 100%;
    box-sizing: border-box;
}
.ks-product-tagline :deep(p) {
    margin: 0;
}
.ks-images,
.ks-images.images {
    min-width: 0;
    display: flex;
    flex-direction: column;
    min-height: 0;
    padding: var(--gap) 0 var(--gap) var(--ks-page-pad);
    background: var(--ks-pdp-image-surface, #f5f5f5);
    /* يمنع انعكاس ترتيب المعرض عندما تكون الصفحة ar/rtl */
    direction: ltr;
    unicode-bidi: isolate;
}
.ks-images.images > *,
.ks-images > * {
    min-width: 0;
}
.ks-product-detail-info,
.ks-product-detail-info.product-detail-info {
    width: var(--detail-width);
    flex-shrink: 0;
    align-self: stretch;
    display: flex;
    flex-direction: column;
    color: #979ea0;
    padding: var(--gap) var(--ks-page-pad) var(--gap) var(--gap);
    background-color: #ebebeb;
    border-inline-start: 1px solid #0002;
    font-size: 0.875em;
    position: relative;
    box-sizing: border-box;
}
.ks-product-detail-wrapper,
.ks-product-detail-wrapper.product-detail-wrapper {
    gap: calc(var(--spacing) * 2);
    flex-direction: column;
    display: flex;
}
.ks-detail-brand-row {
    display: flex;
    justify-content: flex-end;
    width: 100%;
}
.ks-detail-brand-row--above-price {
    justify-content: center;
    margin: 0 0 12px;
    padding: 0;
}
.ks-detail-brand-row--above-price .ks-product-brand,
.ks-detail-brand-row--above-price .ks-product-brand.product-brand {
    margin-bottom: 0;
}
.ks-product-brand,
.ks-product-brand.product-brand {
    margin-bottom: 10px;
}
.ks-product-brand img,
.ks-product-brand.product-brand img {
    filter: invert(1);
    max-width: 100%;
    height: auto;
    margin: 0;
    display: block;
    width: 75px;
}
@media screen and (max-width: 500px) {
    .ks-product-brand.product-brand {
        display: none;
    }
}
.ks-state {
    color: #555;
    background: #fff;
    border: 1px solid #d9d9d9;
    padding: 14px;
    border-radius: 2px;
    margin: 20px var(--ks-page-pad);
}

/* معرض PDP: ترتيب المصغّرات حسب اللغة (en يسار الصورة، ar يمينها) */
.ks-gallery--pdp {
    --ks-thumb-size: 72px;
    --ks-thumb-gap: 8px;
    --ks-thumb-visible: 5;
    background: var(--ks-pdp-image-surface, #f5f5f5);
    direction: ltr;
    border: 0;
    border-radius: 0;
    padding: 0;
    box-shadow: none;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
}
.ks-gallery__inner {
    display: grid;
    direction: ltr;
    unicode-bidi: isolate;
    align-items: stretch;
    gap: 14px;
    flex: 1;
    min-height: clamp(280px, 58vh, 820px);
    min-width: 0;
    background: var(--ks-pdp-image-surface, #f5f5f5);
}
/* إنجليزي: عنوان فوق الصورة + سلايدر يسار الصورة الكبيرة */
.ks-gallery__inner--en {
    grid-template-columns: auto minmax(0, 1fr);
    grid-template-areas:
        "heading heading"
        "thumbs main";
}
/* عربي: عنوان فوق الصورة + سلايدر يمين الصورة الكبيرة */
.ks-gallery__inner--ar {
    grid-template-columns: minmax(0, 1fr) auto;
    grid-template-areas:
        "heading heading"
        "main thumbs";
}
.ks-pdp-heading {
    grid-area: heading;
    min-width: 0;
}
.ks-gallery__main,
.ks-gallery__main--zoom {
    grid-area: main;
    min-width: 0;
    min-height: clamp(320px, 58vh, 720px);
    height: clamp(320px, 58vh, 720px);
    background: var(--ks-pdp-image-surface, #f5f5f5);
    border: 0;
    border-radius: 0;
    position: relative;
    overflow: hidden;
    cursor: zoom-in;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ks-gallery__main--zoom.ks-gallery__main--zoomed {
    cursor: grab;
}
.ks-gallery__main--zoom.ks-gallery__main--panning {
    cursor: grabbing;
}
.ks-gallery__main--zoom {
    touch-action: none;
}
.ks-gallery__main--zoom.ks-gallery__main--zoomed {
    touch-action: none;
}
.ks-gallery__main--zoom:not(.ks-gallery__main--zoomed) {
    touch-action: none;
}
.ks-gallery__zoom-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    min-height: 100%;
    background: var(--ks-pdp-image-surface, #f5f5f5);
    will-change: transform;
    transform-origin: center center;
    touch-action: inherit;
}
.ks-zoom-toolbar {
    position: absolute;
    top: 8px;
    inset-inline-end: 8px;
    z-index: 3;
    display: inline-flex;
    flex-direction: column;
    align-items: stretch;
    gap: 2px;
    padding: 3px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #e0e0e0;
    border-radius: 2px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.ks-zoom-btn {
    min-width: 24px;
    width: 24px;
    height: 24px;
    padding: 0;
    border: 1px solid #d2d2d2;
    background: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1;
    cursor: pointer;
    color: #111;
}
.ks-zoom-btn--text {
    font-size: 8px;
    font-weight: 800;
    min-width: 24px;
    width: 24px;
    height: 22px;
    letter-spacing: -0.02em;
}
.ks-zoom-btn:hover {
    border-color: #b89500;
    color: #b89500;
}
.ks-gallery__main-img {
    display: block;
    max-width: 100%;
    max-height: 100%;
    touch-action: inherit;
    width: auto;
    height: auto;
    object-fit: contain;
    background-color: var(--ks-pdp-image-surface, #f5f5f5);
    user-select: none;
    -webkit-user-drag: none;
}
.ks-gallery__aside {
    grid-area: thumbs;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    gap: 6px;
    flex-shrink: 0;
    width: calc(var(--ks-thumb-size) + 10px);
    align-self: stretch;
    min-height: 0;
    background: var(--ks-pdp-image-surface, #f5f5f5);
}
.ks-gallery__track {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--ks-thumb-gap);
    width: 100%;
    flex: 0 0 auto;
    max-height: calc(
        var(--ks-thumb-visible) * var(--ks-thumb-size) + (var(--ks-thumb-visible) - 1) * var(--ks-thumb-gap)
    );
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 2px 0;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    box-sizing: border-box;
}
.ks-gallery__track::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}
.ks-gallery__thumb {
    flex: 0 0 var(--ks-thumb-size);
    width: var(--ks-thumb-size);
    height: var(--ks-thumb-size);
    min-width: var(--ks-thumb-size);
    min-height: var(--ks-thumb-size);
    max-width: var(--ks-thumb-size);
    max-height: var(--ks-thumb-size);
    box-sizing: border-box;
    padding: 0;
    margin: 0;
    border: 1px solid #d9d9d9;
    border-radius: 2px;
    background: #fff;
    cursor: pointer;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.ks-gallery__thumb:hover {
    border-color: #b89500;
}
.ks-gallery__thumb--active {
    border-color: #efc527;
    box-shadow: 0 0 0 1px rgba(239, 197, 39, 0.55);
}
.ks-gallery__thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    vertical-align: middle;
}
.ks-gallery__nav {
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    padding: 0;
    border: 1px solid #bbbcbc;
    border-radius: 50%;
    background: #fff;
    color: #1d1d1d;
    font-size: 18px;
    font-weight: 700;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    transition: border-color 0.2s, color 0.2s, background 0.2s;
}
.ks-gallery__nav:hover {
    border-color: #efc527;
    color: #b89500;
    background: #fffef5;
}

.ks-brand--text {
    margin: 0;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: inherit;
}
.ks-product-detail-info .ks-now,
.ks-product-detail-info .ks-now.itemnumber {
    color: #000;
    line-height: 2.1rem;
    font-size: 1.75rem;
    font-weight: 800;
}
.ks-product-detail-info .ks-variants label,
.ks-product-detail-info .ks-qty-label {
    color: #000;
    font-weight: 700;
}
.ks-product-detail-info .ks-cart-note {
    color: #979ea0;
    font-size: 12px;
}
/* ترتيب keepersport على الجوال: 1 عنوان → 2 RRP → 3 سعر+خصم → 4 SKU */
.ks-pdp-info-stack {
    display: none;
}
.ks-pdp-info-stack__pricing {
    display: flex;
    flex-direction: column;
    gap: 0;
    margin: 0 0 4px;
}
.ks-pdp-price-with-currency {
    display: inline-flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 0.2em;
}
.ks-pdp-price-now :deep(.ks-pdp-currency-label),
.ks-pdp-price-with-currency :deep(.ks-pdp-currency-label) {
    font-size: 0.58em;
    font-weight: 600;
    color: #5c5c5c;
    line-height: 1.1;
    letter-spacing: 0.01em;
    vertical-align: baseline;
    white-space: nowrap;
}
.ks-pdp-price-now :deep(.ks-currency-icon),
.ks-pdp-price-with-currency :deep(.ks-currency-icon),
.ks-pdp-price-now :deep(.ks-pdp-currency-icon),
.ks-pdp-price-with-currency :deep(.ks-pdp-currency-icon),
.ks-sale-price :deep(.ks-currency-icon),
.ks-sale-price :deep(.ks-pdp-currency-icon),
.ks-now :deep(.ks-currency-icon),
.ks-now :deep(.ks-pdp-currency-icon) {
    display: inline-block;
    height: 0.72em;
    width: auto;
    max-height: 18px;
    margin-inline-start: 0.18em;
    vertical-align: -0.08em;
    background: transparent;
    border: 0;
    object-fit: contain;
}
.ks-rrp--stack :deep(.ks-currency-icon),
.ks-rrp--stack :deep(.ks-pdp-currency-icon) {
    height: 0.82em;
    max-height: 20px;
    vertical-align: -0.06em;
}
.ks-pdp-price-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    margin: 0;
}
.ks-pdp-mobile-timer {
    margin: 8px 0 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #d32f2f;
    letter-spacing: 0.01em;
}
.ks-pdp-mobile-limited {
    margin: 4px 0 0;
    font-size: 0.82rem;
    font-weight: 600;
    color: #d32f2f;
    text-transform: lowercase;
}
.ks-pdp-price-now {
    margin: 0;
    font-size: clamp(1.75rem, 6.2vw, 2.125rem);
    font-weight: 800;
    color: #111;
    line-height: 1.1;
}
.ks-pdp-price-now :deep(.ks-pdp-price-major) {
    font-size: 1em;
    font-weight: 800;
}
.ks-pdp-price-now :deep(.ks-pdp-price-minor) {
    font-size: 0.92em;
    font-weight: 800;
    vertical-align: super;
}
.ks-pdp-price-now :deep(.ks-price-with-currency--en),
.ks-pdp-price-now--solo :deep(.ks-price-with-currency--en),
.ks-pdp-price-with-currency :deep(.ks-price-with-currency--en) {
    display: inline-flex;
    align-items: baseline;
    gap: 0.32em;
}
.ks-pdp-price-now :deep(sup),
.ks-sale-price :deep(sup) {
    top: 0.4em;
}
.ks-pdp-price-now--solo {
    margin: 0;
    font-size: clamp(1.75rem, 6.2vw, 2.125rem);
    font-weight: 800;
    color: #111;
}
.ks-pdp-discount-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 5px 10px;
    background: #ffcc00;
    color: #111;
    font-size: 14px;
    font-weight: 800;
    line-height: 1;
    border-radius: 3px;
    letter-spacing: -0.02em;
}
.ks-rrp--stack {
    margin: 0 0 6px;
    font-size: 1.5em;
    color: #979ea0;
}
.ks-sku-line--stack {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.35em;
    margin: 8px 0 14px;
    font-size: 13px;
    color: #6b7280;
    line-height: 1.35;
}
.ks-sku-line__label {
    font-weight: 500;
    color: #979ea0;
}
.ks-sku-line__code {
    font-weight: 700;
    color: #4b5563;
    letter-spacing: 0.04em;
}
.ks-price-box {
    margin: 0 0 8px;
    width: 100%;
}
.ks-rrp,
.ks-rrp.listPrice {
    color: #979ea0;
    font-size: 1.5em;
    margin: 0 0 8px;
    line-height: 1.4;
    text-decoration: none;
}
.ks-rrp-label {
    margin-inline-end: 0.35em;
    text-decoration: none;
}
.ks-rrp .ks-price-strike {
    color: #979ea0;
}
.ks-sale-box {
    --ks-sale-pad-x: 14px;
    margin: 0;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
    gap: 0;
    background: var(--ks-sale-red, #d32f2f);
    color: #fff;
    padding: 8px var(--ks-sale-pad-x) 14px;
    width: 100%;
    max-width: none;
    min-height: 128px;
    border-radius: 0;
    box-sizing: border-box;
    /* يقصّ الشريط الأبيض المائل حتى لا يخرج عن حدود الصندوق */
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.12);
}
/* شارة خصم: شريط أبيض مائل — القصّ يتم من .ks-sale-box overflow */
.ks-sale-corner-ribbon {
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 6px;
    left: -15px;
    width: 66px;
    height: 22px;
    background-color: #fff;
    transform: rotate(-45deg);
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.22);
    text-align: center;
    color: #111;
    font-size: 11px;
    font-weight: 700;
    line-height: 22px;
    letter-spacing: 0.02em;
    pointer-events: none;
    z-index: 10;
    box-sizing: border-box;
}
.ks-sale-corner-ribbon-tri {
    display: none;
}
.ks-sale-corner-ribbon-txt {
    display: block;
    position: relative;
    top: 0;
    left: 0;
    width: 100%;
    transform: none;
    text-align: center;
    color: inherit;
    font-size: inherit;
    font-weight: inherit;
    line-height: inherit;
    letter-spacing: inherit;
    white-space: nowrap;
}
/* صف التايمر — بالنص، مع مساحة يسار للشارة */
.ks-sale-box-head {
    position: relative;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    min-height: 40px;
    padding: 4px 12px 0 clamp(44px, 12vw, 56px);
    box-sizing: border-box;
    z-index: 1;
}
.ks-sale-box-timer {
    margin: 0 auto;
    padding: 0 0 7px;
    width: auto;
    max-width: 100%;
    box-sizing: border-box;
    font-size: 12px;
    line-height: 1.3;
    color: #fff;
    letter-spacing: 0.01em;
    text-align: center;
    border: none;
}
.ks-sale-box-timer :deep(.ks-timer-num) {
    font-weight: 600;
}
.ks-sale-box-timer :deep(.ks-timer-unit) {
    font-weight: 400;
}
.ks-sale-box-timer :deep(.ks-timer-fallback) {
    font-weight: 400;
}
.ks-sale-head-rule {
    width: 100%;
    height: 0;
    margin: 0 0 8px;
    border: 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.35);
}
.ks-sale-price {
    margin: 0;
    padding: 0 4px 4px;
    text-align: center;
    width: 100%;
    line-height: 1.05;
    color: #fff;
    font-size: clamp(2.2rem, 4.2vw, 2.85rem);
}
.ks-sale-price :deep(.ks-price-with-currency),
.ks-sale-price :deep(.ks-price-with-currency--en),
.ks-sale-price :deep(.ks-price-with-currency--ar),
.ks-sale-price :deep(.ks-currency-label),
.ks-sale-price :deep(.ks-sale-cur),
.ks-sale-price :deep(.ks-sale-major),
.ks-sale-price :deep(.ks-sale-minor),
.ks-sale-price :deep(sup) {
    color: #fff !important;
}
.ks-sale-price :deep(.ks-currency-icon),
.ks-sale-price :deep(.ks-pdp-currency-icon) {
    filter: brightness(0) invert(1);
    opacity: 1;
}
.ks-sale-price :deep(.ks-sale-cur),
.ks-sale-price :deep(.ks-currency-label) {
    font-size: 0.58em;
    font-weight: 600;
    vertical-align: baseline;
    margin-inline-end: 0.05em;
}
.ks-sale-price :deep(.ks-sale-major) {
    font-size: 1em;
    font-weight: 600;
    letter-spacing: -0.025em;
}
.ks-sale-price :deep(.ks-sale-minor) {
    font-size: 0.38em;
    font-weight: 400;
    vertical-align: super;
    margin-inline-start: 0.02em;
}
.ks-sale-box-note {
    margin: 0;
    font-size: 11px;
    font-weight: 500;
    color: #fff;
    line-height: 1.25;
    letter-spacing: 0.02em;
    text-transform: lowercase;
    text-align: center;
    width: 100%;
}
.ks-variants {
    margin: 12px 0 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
    max-width: none;
}
.ks-variants label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: #979ea0;
}
.ks-size-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.ks-size-box {
    min-width: 46px;
    height: 38px;
    border: 1px solid #d5d5d5;
    background: #fff;
    font-size: 12px;
    font-weight: 700;
    color: #555;
    cursor: pointer;
    padding: 0 10px;
}
.ks-size-box:disabled {
    cursor: not-allowed;
}
.ks-size-box.active {
    border-color: #efc527;
    box-shadow: inset 0 0 0 1px #efc527;
    color: #111;
}
.ks-size-box--soldout {
    position: relative;
    overflow: hidden;
    color: #969696;
    border-color: #d8d8d8;
    background: #f1f1f1;
    text-decoration: line-through;
    text-decoration-color: #a9a9a9;
    text-decoration-thickness: 1px;
}
.ks-size-box--soldout::before,
.ks-size-box--soldout::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 6px;
    right: 6px;
    border-top: 1px solid #ababab;
    pointer-events: none;
}
.ks-size-box--soldout::before {
    transform: rotate(28deg);
}
.ks-size-box--soldout::after {
    transform: rotate(-28deg);
}
.ks-qty-row {
    margin: 12px 0 10px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.ks-qty-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}
.ks-qty-select {
    min-width: 56px;
    height: 36px;
    padding: 0 10px;
    border: 1px solid #d2d2d2;
    background: #fff;
    color: #111;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}
.ks-add-cart,
.ks-add-cart.add-to-cart {
    width: 100%;
    border: 0;
    background: #ffcc00;
    color: #111;
    min-height: 48px;
    padding: 0 16px;
    font-weight: 800;
    cursor: pointer;
    font-size: 13px;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 4px;
    box-sizing: border-box;
}
.ks-add-cart:hover {
    filter: brightness(0.97);
}
.ks-cart-ico {
    flex-shrink: 0;
}
.ks-sku-line {
    margin: 10px 0 0;
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.35em;
    font-size: 13px;
    color: #979ea0;
    line-height: 1.4;
}
.ks-cart-note {
    margin: 8px 0 4px;
    font-size: 12px;
    color: #979ea0;
    padding-bottom: 4px;
}
.ks-pv-tabs.ks-tabs {
    background: #fff;
    border: 1px solid #d9d9d9;
    border-radius: 2px;
    overflow: hidden;
    margin: 20px 0 0;
    width: 100%;
    max-width: none;
    box-sizing: border-box;
    border-bottom: none;
}
.ks-product-wrap:has(.ks-pdp-recommended) .ks-pv-tabs.ks-tabs {
    margin-bottom: 0;
    border-bottom: none;
}
.ks-tab-head {
    display: flex;
    flex-wrap: nowrap;
    align-items: stretch;
    padding-left: 20px;
    background: #fff;
    text-transform: uppercase;
    font-size: 14px;
    font-weight: 700;
    color: #000;
}
.ks-tab-btn {
    box-sizing: border-box;
    cursor: pointer;
    opacity: 0.7;
    text-align: center;
    min-width: 160px;
    max-width: 264px;
    height: 48px;
    color: inherit;
    border: 0;
    border-bottom: 2px solid transparent;
    background: #fff;
    flex: none;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px 12px 0;
    font: inherit;
    font-weight: 700;
    text-transform: uppercase;
}
.ks-tab-btn.active {
    border-bottom-color: var(--indicator-color, #b89500);
    opacity: 1;
}
@media screen and (max-width: 1000px) {
    .ks-tab-btn {
        flex: 1 0 auto;
        min-width: 72px;
        max-width: none;
    }
}
.ks-tab-body { padding: 14px var(--ks-page-pad); font-size: 14px; color: #2e2e2e; white-space: pre-line; min-height: 120px; width: 100%; max-width: none; box-sizing: border-box; }
.ks-tab-body--html :deep(p) { margin: 0 0 10px; line-height: 1.6; }
.ks-tab-body--html :deep(h2),
.ks-tab-body--html :deep(h3) { margin: 10px 0 8px; font-size: 18px; line-height: 1.25; }
.ks-tab-body--html :deep(ul) { margin: 0; padding-left: 18px; display: grid; gap: 6px; }
.ks-tab-body--html :deep(li) { margin: 0; }
.ks-details-list { margin: 0; padding: 0 0 0 16px; display: grid; gap: 8px; }
.ks-details-empty { margin: 0; color: #666; font-size: 14px; }
@media (min-width: 716px) {
    .ks-pdp-info-stack {
        display: none !important;
    }
}

@media (max-width: 715px) {
    .ks-pdp-info-stack {
        display: block;
        padding: 12px calc(var(--spacing) * 2) 0;
        box-sizing: border-box;
    }
    .ks-pdp-info-stack__title {
        display: block;
        width: 100%;
        margin: 0 0 10px;
        font-size: clamp(1.375rem, 5.4vw, 2.5rem);
        font-weight: 800;
        line-height: 1.15;
        color: #111;
        text-transform: inherit;
        letter-spacing: -0.015em;
    }
    .ks-pdp-info-stack__title[dir="rtl"] {
        direction: rtl;
        text-align: right;
    }
    .ks-pdp-info-stack__title[dir="ltr"] {
        direction: ltr;
        text-align: left;
    }
    .ks-pdp-info-stack__tagline {
        margin: 0 0 8px;
        font-size: 13px;
        color: #555;
    }
    .ks-pdp-heading {
        display: none !important;
    }
    .ks-price-box--desktop {
        display: none !important;
    }
    .ks-sku-line--detail {
        display: none !important;
    }
    .ks-product-page {
        background: #fff;
        --ks-pdp-image-surface: #fff;
    }
    .ks-product-overview {
        --gap: calc(var(--spacing) * 2);
        background-color: #fff;
    }
    .ks-product-split {
        grid-template-columns: 1fr;
        gap: 0;
        padding: 0;
    }
    .ks-pdp-heading {
        padding: 12px 12px 0;
        background: #fff;
    }
    .ks-product-overview .ks-product-title--upper {
        text-transform: inherit;
    }
    .ks-pdp-heading .ks-product-tagline {
        padding: 0;
        margin: 0 0 4px;
        background: transparent;
    }
    .ks-images,
    .ks-images.images {
        padding: 0;
        background: #fff;
        width: 100%;
    }
    .ks-product-detail-info,
    .ks-product-detail-info.product-detail-info {
        width: 100%;
        max-width: none;
        background: #fff;
        border: none;
        border-inline-start: none;
        padding: 0;
    }
    .ks-product-detail-wrapper {
        padding: 0 calc(var(--spacing) * 2) calc(var(--spacing) * 2);
        box-sizing: border-box;
    }
    .ks-gallery__inner {
        min-height: auto;
        background: #fff;
    }
    .ks-gallery--pdp {
        --ks-thumb-size: 52px;
        --ks-thumb-gap: 6px;
        padding: 0;
        position: relative;
        background: #fff;
    }
    .ks-gallery__inner,
    .ks-gallery__inner--en,
    .ks-gallery__inner--ar {
        display: flex;
        flex-direction: column;
        gap: 10px;
        background: #fff;
    }
    .ks-gallery__main,
    .ks-gallery__main--zoom,
    .ks-gallery__zoom-inner {
        background: #fff;
    }
    .ks-gallery__main,
    .ks-gallery__main--zoom {
        width: 100%;
        min-height: min(75vw, 400px);
        height: min(75vw, 400px);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }
    .ks-gallery__zoom-inner {
        width: 100%;
        height: 100%;
        min-height: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ks-gallery__main-img {
        width: auto;
        max-width: 100%;
        max-height: 100%;
        height: auto;
        object-fit: contain;
        background-color: #fff;
    }
    .ks-gallery__aside {
        flex-direction: row;
        width: 100%;
        align-items: center;
        justify-content: flex-start;
        gap: 0;
        padding: 0 10px 10px;
        box-sizing: border-box;
        background: #fff;
    }
    .ks-gallery__nav {
        display: none;
    }
    .ks-gallery__track {
        flex: 1 1 auto;
        flex-direction: row;
        align-items: stretch;
        max-height: none;
        max-width: 100%;
        width: 100%;
        min-width: 0;
        overflow-y: hidden;
        overflow-x: auto;
        gap: var(--ks-thumb-gap);
        padding: 0;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .ks-gallery__track::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }
    .ks-gallery__thumb {
        flex: 0 0 var(--ks-thumb-size);
        width: var(--ks-thumb-size);
        height: var(--ks-thumb-size);
        min-width: var(--ks-thumb-size);
        min-height: var(--ks-thumb-size);
        max-width: var(--ks-thumb-size);
        max-height: var(--ks-thumb-size);
        border: 2px solid #e8e8e8;
        border-radius: 0;
        background: #fff;
        scroll-snap-align: start;
    }
    .ks-gallery__thumb--active {
        border-color: #efc527;
        box-shadow: none;
    }
    .ks-gallery__thumb img {
        object-fit: contain;
        background: #fff;
    }
}

/* Recommended products — full-bleed keepersport carousel */
.ks-pdp-recommended {
    --ks-rec-gold: #b89500;
    --ks-rec-sep: #373e3e;
    --ks-rec-pad-x: max(20px, env(safe-area-inset-left, 0px));
    --ks-rec-img-ratio: 1.12;
    --ks-rec-card-w: clamp(220px, calc((100vw - var(--ks-rec-pad-x) - 48px) / 3.2), 320px);
    --ks-rec-img-h: calc(var(--ks-rec-card-w) * var(--ks-rec-img-ratio));
    width: 100vw;
    max-width: 100vw;
    margin: 0;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);
    box-sizing: border-box;
    background: #fff;
    color: #111;
    padding: 0;
    overflow: hidden;
}

/* شريط فاصل داكن فوق القسم — مثل keepersport (بدل خط رفيع) */
.ks-pdp-recommended-band {
    display: block;
    width: 100%;
    height: 16px;
    margin: 0;
    padding: 0;
    background: var(--ks-rec-sep);
    border: none;
    flex-shrink: 0;
}

.ks-pdp-recommended-inner {
    width: 100%;
    box-sizing: border-box;
    background: #fff;
    padding: 32px 0 44px;
}

.ks-pdp-recommended-head {
    margin: 0 0 22px;
    padding: 0 var(--ks-rec-pad-x);
    box-sizing: border-box;
}

.ks-pdp-recommended-title {
    margin: 0;
    padding: 0 0 6px;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--ks-rec-gold);
    display: inline-block;
    border-bottom: 2px solid var(--ks-rec-gold);
    line-height: 1.25;
}

.ks-pdp-recommended-slider {
    position: relative;
    width: 100%;
    overflow: hidden;
    min-height: calc(var(--ks-rec-card-w) * 0.9 + 132px);
}

.ks-pdp-recommended-scroll {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: stretch;
    gap: 0;
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    scroll-behavior: smooth;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
    direction: ltr;
    /* يسمح: تمرير الصفحة ↑↓ + سحب أفقي + قرص التكبير — pan-x وحده كان يقتل العمودي والقرص */
    touch-action: pan-x pan-y pinch-zoom;
    overscroll-behavior-x: contain;
    overscroll-behavior-y: auto;
    cursor: default;
    padding: 10px max(52px, env(safe-area-inset-right, 0px)) 8px var(--ks-rec-pad-x);
    box-sizing: border-box;
}

.ks-pdp-recommended-scroll--dragging {
    cursor: grabbing;
    scroll-behavior: auto;
    scroll-snap-type: none;
    user-select: none;
}

.ks-pdp-recommended-scroll::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}

.ks-pdp-recommended-card {
    flex: 0 0 var(--ks-rec-card-w);
    width: var(--ks-rec-card-w);
    min-width: var(--ks-rec-card-w);
    max-width: var(--ks-rec-card-w);
    padding: 0;
    box-sizing: border-box;
    text-decoration: none;
    color: inherit;
    text-align: center;
    scroll-snap-align: center;
    touch-action: pan-x pan-y pinch-zoom;
    scroll-snap-stop: always;
    overflow: hidden;
    cursor: pointer;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    background: #f5f5f5;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0.08);
}

.ks-pdp-recommended-body {
    padding: 8px 10px 12px;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.ks-pdp-recommended-brand {
    margin: 0 0 4px;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.01em;
    color: #111;
    line-height: 1.2;
}

.ks-pdp-recommended-name {
    margin: 0 0 8px;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.35;
    color: #333;
    min-height: 2.6em;
    max-width: 100%;
}

.ks-pdp-recommended-price {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.ks-pdp-recommended-price-was {
    font-size: 11px;
    color: #888;
}

.ks-pdp-recommended-price-now {
    font-size: 15px;
    font-weight: 800;
    color: #111;
}

.ks-pdp-recommended-arrow {
    position: absolute;
    top: 42%;
    transform: translateY(-50%);
    z-index: 4;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid #dcdcdc;
    background: #fff;
    color: #444;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
    padding: 0;
}

.ks-pdp-recommended-arrow:hover {
    border-color: #bbb;
    color: #000;
}

.ks-pdp-recommended-arrow--prev {
    left: max(8px, env(safe-area-inset-left, 0px));
}

.ks-pdp-recommended-arrow--next {
    right: max(8px, env(safe-area-inset-right, 0px));
}

@media (max-width: 900px) {
    .ks-pdp-recommended {
        --ks-rec-card-w: clamp(168px, calc((100vw - var(--ks-rec-pad-x) - 32px) / 2.35), 260px);
        padding: 28px 0 36px;
    }
}

@media (max-width: 600px) {
    .ks-pdp-recommended {
        --ks-rec-card-w: clamp(158px, calc((100vw - var(--ks-rec-pad-x) - 20px) / 1.75), 220px);
    }
    .ks-pdp-recommended-arrow {
        width: 42px;
        height: 42px;
        font-size: 22px;
        opacity: 0.95;
    }
    .ks-pdp-recommended-scroll {
        padding-inline: max(14px, env(safe-area-inset-left, 0px));
        scroll-snap-type: x proximity;
        touch-action: pan-x pan-y pinch-zoom;
        -webkit-overflow-scrolling: touch;
        overflow-y: visible;
    }
    .ks-pdp-recommended-card {
        touch-action: pan-x pan-y pinch-zoom;
        scroll-snap-align: start;
    }
    .ks-pdp-recommended-card .ks-plp-card-img-wrap,
    .ks-pdp-recommended-card .ks-plp-card-img {
        touch-action: pan-x pan-y pinch-zoom;
        -webkit-user-drag: none;
        user-select: none;
    }
}

/* توست «تمت الإضافة إلى السلة» */
.ks-cart-toast {
    position: fixed;
    left: 50%;
    bottom: clamp(22px, 6vh, 48px);
    transform: translateX(-50%);
    z-index: 4000;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 22px;
    background: #1c2321;
    color: #fff;
    border-radius: 999px;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.28);
    pointer-events: none;
    white-space: nowrap;
}
.ks-cart-toast__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #2e7d32;
    color: #fff;
    flex: 0 0 auto;
}
.ks-cart-toast-enter-active {
    transition: opacity 0.22s ease, transform 0.22s ease;
}
.ks-cart-toast-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.ks-cart-toast-enter-from,
.ks-cart-toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(12px);
}
</style>

