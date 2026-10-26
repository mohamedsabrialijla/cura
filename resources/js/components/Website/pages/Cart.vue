<template>
    <section class="ks-cart-page" :lang="pageLang">
        <div class="ks-cart-wrap">
            <router-link :to="`/${locale}/account`" class="ks-cart-back">
                <i
                    :class="pageLang === 'ar' ? 'fas fa-angle-right' : 'fas fa-angle-left'"
                    aria-hidden="true"
                ></i>
                <span>{{ backLabel }}</span>
            </router-link>

            <h1>{{ pageLang === "ar" ? "سلة التسوق" : "Shopping Cart" }}</h1>

            <div v-if="items.length && totalSavings > 0" class="ks-savings-banner">
                <span class="ks-savings-label">
                    {{ pageLang === "ar" ? "إجمالي التوفير:" : "Total savings:" }}
                </span>
                <strong class="ks-savings-amount" v-html="formatPriceHtml(totalSavings)"></strong>
            </div>

            <p v-if="!items.length" class="ks-cart-empty">
                {{ pageLang === "ar" ? "لا يوجد منتجات في السلة." : "There are no items in your cart." }}
            </p>

            <div v-else class="ks-cart-grid">
                <div class="ks-cart-list">
                    <article v-for="item in items" :key="item.id" class="ks-cart-row">
                        <img
                            v-if="item.image"
                            :src="normalizeCatalogImageUrl(item.image)"
                            :alt="item.title"
                            referrerpolicy="no-referrer"
                            decoding="async"
                            loading="lazy"
                        />
                        <div class="ks-cart-row-body">
                            <h3>{{ item.title }}</h3>
                            <p v-if="item.subtitle">{{ item.subtitle }}</p>
                            <div class="ks-cart-row-meta">
                                <div class="ks-cart-prices">
                                    <span
                                        v-if="hasOldPrice(item)"
                                        class="ks-cart-price-old ks-price-strike"
                                        v-html="formatPriceHtml(item.price_old)"
                                    ></span>
                                    <strong
                                        class="ks-cart-price-now"
                                        :class="{ 'is-sale': hasOldPrice(item) }"
                                        v-html="formatPriceHtml(item.price)"
                                    ></strong>
                                </div>
                                <div class="ks-qty">
                                    <button
                                        type="button"
                                        :disabled="item.qty <= 1"
                                        @click="changeQty(item.id, -1)"
                                    >-</button>
                                    <span lang="en" dir="ltr">{{ item.qty }}</span>
                                    <button
                                        type="button"
                                        :disabled="!canIncreaseQty(item)"
                                        @click="changeQty(item.id, 1)"
                                    >+</button>
                                </div>
                            </div>
                            <p v-if="itemSavings(item) > 0" class="ks-cart-item-save">
                                {{ pageLang === "ar" ? "وفّرت" : "You save" }}
                                <span v-html="formatPriceHtml(itemSavings(item))"></span>
                            </p>
                            <button type="button" class="ks-remove" @click="removeItem(item.id)">
                                {{ pageLang === "ar" ? "حذف المنتج" : "Remove item" }}
                            </button>
                        </div>
                    </article>
                </div>

                <aside class="ks-cart-summary">
                    <h4>{{ pageLang === "ar" ? "ملخص الطلب" : "Order Summary" }}</h4>
                    <div v-if="totalSavings > 0" class="ks-savings-banner ks-savings-banner--summary">
                        <span class="ks-savings-label">
                            {{ pageLang === "ar" ? "إجمالي التوفير:" : "Total savings:" }}
                        </span>
                        <strong class="ks-savings-amount" v-html="formatPriceHtml(totalSavings)"></strong>
                    </div>
                    <div class="ks-s-row">
                        <span>{{ pageLang === "ar" ? "عدد المنتجات" : "Items" }}</span>
                        <strong lang="en" dir="ltr">{{ cartCount }}</strong>
                    </div>
                    <div class="ks-s-row">
                        <span>{{ pageLang === "ar" ? "المبلغ قبل الضريبة" : "Amount before VAT" }}</span>
                        <strong v-html="formatPriceHtml(subtotalExclVat)"></strong>
                    </div>
                    <div v-if="vatPercent > 0" class="ks-s-row">
                        <span>{{ vatLabel }}</span>
                        <strong v-html="formatPriceHtml(taxAmount)"></strong>
                    </div>
                    <div class="ks-s-row">
                        <span>{{ pageLang === "ar" ? "الشحن" : "Shipping" }}</span>
                        <strong>{{ pageLang === "ar" ? "يُحسب لاحقًا" : "Calculated later" }}</strong>
                    </div>
                    <hr />
                    <div class="ks-s-row ks-s-row-total">
                        <span>{{ pageLang === "ar" ? "الإجمالي" : "Total" }}</span>
                        <strong v-html="formatPriceHtml(total)"></strong>
                    </div>
                    <button
                        type="button"
                        class="ks-checkout"
                        :disabled="!items.length"
                        @click="goCheckout"
                    >
                        {{ pageLang === "ar" ? "إتمام الشراء" : "Proceed to checkout" }}
                    </button>
                </aside>
            </div>
        </div>
    </section>
</template>

<script>
import {
    getCartItems,
    saveCartItems,
    getCartCount,
    getCartSubtotal,
    getCartProductSavings,
    syncCartToServer,
    maxQtyForCartItem,
    CART_CHANGE_EVENT,
} from "@/utils/cartStorage.js";
import { formatWebsiteCurrency } from "@/utils/westernNumerals";
import { formatWebsitePriceHtml } from "@/utils/websiteCurrencyDisplay";
import { normalizeCatalogImageUrl } from "@/utils/catalogImageUrl";

export default {
    name: "CartPage",
    data() {
        return {
            items: [],
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        cartCount() {
            return getCartCount(this.items);
        },
        vatPercent() {
            const raw = parseFloat(this.$store.getters.getSetting?.vat);
            return Number.isFinite(raw) && raw > 0 ? raw : 0;
        },
        subtotalInclVat() {
            return getCartSubtotal(this.items);
        },
        subtotalExclVat() {
            if (!this.vatPercent) {
                return this.subtotalInclVat;
            }
            return this.roundMoney(this.subtotalInclVat / (1 + this.vatPercent / 100));
        },
        taxAmount() {
            return this.roundMoney(this.subtotalInclVat - this.subtotalExclVat);
        },
        total() {
            return this.subtotalInclVat;
        },
        totalSavings() {
            return this.roundMoney(getCartProductSavings(this.items));
        },
        vatLabel() {
            const pct = this.vatPercent;
            if (this.pageLang === "ar") {
                return `ضريبة القيمة المضافة (${pct}%)`;
            }
            return `VAT (${pct}%)`;
        },
        backLabel() {
            return this.pageLang === "ar" ? "العودة إلى حسابي" : "Back to my account";
        },
    },
    mounted() {
        this.reloadFromStorage();
        window.addEventListener(CART_CHANGE_EVENT, this.reloadFromStorage);
        if (!this.$store.getters.getSetting) {
            this.$store.dispatch("fetchSetting");
        }
    },
    beforeUnmount() {
        window.removeEventListener(CART_CHANGE_EVENT, this.reloadFromStorage);
    },
    watch: {
        "$route.fullPath"(path) {
            if (String(path || "").includes("/cart")) {
                this.reloadFromStorage();
            }
        },
    },
    methods: {
        normalizeCatalogImageUrl,
        reloadFromStorage() {
            this.items = getCartItems();
            if (this.items.length) {
                syncCartToServer(this.items);
            }
        },
        roundMoney(value) {
            return Math.round((Number(value) || 0) * 100) / 100;
        },
        persist() {
            saveCartItems(this.items);
        },
        formatPrice(value) {
            return formatWebsiteCurrency(value, "SAR", { maximumFractionDigits: 2 });
        },
        formatPriceHtml(value) {
            return formatWebsitePriceHtml(value, "SAR", this.pageLang, { maximumFractionDigits: 2 });
        },
        hasOldPrice(item) {
            const oldPrice = Number(item?.price_old || 0);
            const nowPrice = Number(item?.price || 0);
            return oldPrice > 0 && nowPrice > 0 && oldPrice > nowPrice;
        },
        itemSavings(item) {
            if (!this.hasOldPrice(item)) return 0;
            const qty = Number(item.qty || 0);
            return this.roundMoney((Number(item.price_old) - Number(item.price)) * qty);
        },
        changeQty(itemId, delta) {
            this.items = this.items.map((item) => {
                if (item.id !== itemId) return item;
                return { ...item, qty: maxQtyForCartItem(item, delta) };
            });
            this.persist();
        },
        canIncreaseQty(item) {
            if (item.maxStock == null) {
                return false;
            }
            return Number(item.qty || 0) < Number(item.maxStock);
        },
        removeItem(itemId) {
            this.items = this.items.filter((item) => item.id !== itemId);
            this.persist();
        },
        goCheckout() {
            if (!this.items.length) {
                return;
            }
            this.$router.push(`/${this.locale}/checkout`);
        },
    },
};
</script>

<style scoped>
.ks-cart-page { background: #fff; color: #111; padding: 32px 16px 54px; min-height: 70vh; }
.ks-cart-wrap { max-width: 1120px; margin: 0 auto; }
.ks-cart-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 14px;
    color: #111;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
}
.ks-cart-back:hover {
    color: #b88600;
}
.ks-cart-back i {
    font-size: 18px;
    line-height: 1;
}
h1 { margin: 0 0 16px; font-size: clamp(28px, 3vw, 42px); font-weight: 900; }
.ks-savings-banner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px 12px;
    margin: 0 0 14px;
    padding: 12px 14px;
    background: linear-gradient(90deg, #fff8df 0%, #ffefb3 100%);
    border: 1px solid #efc527;
    border-radius: 6px;
    color: #5a4500;
}
.ks-savings-banner--summary {
    margin: 0 0 12px;
    padding: 10px 12px;
}
.ks-savings-label {
    font-size: 14px;
    font-weight: 700;
}
.ks-savings-amount {
    font-size: 15px;
    font-weight: 900;
    color: #3f3000;
}
.ks-cart-item-save {
    margin: 6px 0 0;
    color: #8a6d00;
    font-size: 12px;
    font-weight: 700;
}
.ks-cart-empty { color: #5f5f5f; }
.ks-cart-grid { display: grid; grid-template-columns: 1fr 320px; gap: 16px; align-items: start; }
.ks-cart-list { border: 1px solid #ececec; border-radius: 6px; background: #fff; }
.ks-cart-row { display: grid; grid-template-columns: 110px 1fr; gap: 12px; padding: 12px; border-bottom: 1px solid #efefef; }
.ks-cart-row:last-child { border-bottom: 0; }
.ks-cart-row img { width: 110px; height: 110px; object-fit: cover; border: 1px solid #ececec; }
.ks-cart-row-body h3 { margin: 0 0 6px; font-size: 16px; }
.ks-cart-row-body p { margin: 0 0 8px; color: #666; font-size: 13px; }
.ks-cart-row-meta { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
.ks-cart-prices {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 8px 10px;
    min-width: 0;
}
.ks-cart-price-old {
    color: #979ea0;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.2;
}
.ks-cart-price-now {
    color: #111;
    font-size: 16px;
    font-weight: 800;
    line-height: 1.2;
}
.ks-cart-price-now.is-sale {
    color: #111;
}
.ks-qty { display: inline-flex; border: 1px solid #ddd; border-radius: 3px; flex-shrink: 0; }
.ks-qty button:disabled { opacity: 0.45; cursor: not-allowed; }
.ks-qty button { border: 0; background: #fafafa; width: 28px; height: 28px; }
.ks-qty span { width: 30px; text-align: center; line-height: 28px; font-size: 13px; }
.ks-remove { border: 0; background: transparent; color: #b42318; padding: 6px 0 0; cursor: pointer; font-size: 12px; }
.ks-cart-summary { border: 1px solid #ececec; border-radius: 6px; padding: 12px; background: #fff; position: sticky; top: 16px; }
.ks-cart-summary h4 { margin: 0 0 8px; font-size: 18px; font-weight: 800; }
.ks-s-row { display: flex; justify-content: space-between; margin: 8px 0; font-size: 14px; }
.ks-s-row-total { font-size: 16px; font-weight: 800; }
.ks-checkout { width: 100%; min-height: 42px; border: 0; background: #ffcc00; color: #111; font-weight: 800; margin-top: 10px; cursor: pointer; }
.ks-checkout:disabled { opacity: 0.65; cursor: not-allowed; }
@media (max-width: 900px) { .ks-cart-grid { grid-template-columns: 1fr; } .ks-cart-summary { position: static; } }
</style>
