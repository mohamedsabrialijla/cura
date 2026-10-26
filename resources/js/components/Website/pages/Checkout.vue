<template>
    <section class="ks-checkout-page" :lang="pageLang">
        <div class="ks-checkout-wrap">
            <router-link :to="`/${locale}/cart`" class="ks-checkout-back">
                <i :class="pageLang === 'ar' ? 'fas fa-angle-right' : 'fas fa-angle-left'" aria-hidden="true"></i>
                <span>{{ pageLang === "ar" ? "العودة للسلة" : "Back to cart" }}</span>
            </router-link>

            <h1>{{ pageLang === "ar" ? "إتمام الطلب" : "Checkout" }}</h1>

            <p v-if="!items.length" class="ks-checkout-empty">
                {{ pageLang === "ar" ? "السلة فارغة." : "Your cart is empty." }}
                <router-link :to="`/${locale}/`">{{ pageLang === "ar" ? "تسوق الآن" : "Shop now" }}</router-link>
            </p>

            <div v-else class="ks-checkout-grid">
                <div class="ks-checkout-main">
                    <!-- Contact -->
                    <section class="ks-box">
                        <div class="ks-box-head">
                            <h2>{{ pageLang === "ar" ? "التواصل" : "Contact" }}</h2>
                            <router-link v-if="!isLoggedIn" :to="`/${locale}/login?redirect=/${locale}/checkout`">
                                {{ pageLang === "ar" ? "تسجيل الدخول" : "Log in" }}
                            </router-link>
                        </div>

                        <div v-if="isLoggedIn && user" class="ks-logged-as">
                            {{ pageLang === "ar" ? "مسجّل كـ" : "Signed in as" }}
                            <strong>{{ user.name || user.email }}</strong>
                        </div>

                        <div class="ks-fields" autocomplete="off">
                            <label>
                                <span>{{ pageLang === "ar" ? "الاسم الكامل" : "Full name" }}</span>
                                <input
                                    v-model.trim="contact.name"
                                    type="text"
                                    name="checkout_full_name"
                                    autocomplete="off"
                                    :readonly="contactFieldsLocked"
                                    @focus="unlockContactFields"
                                    required
                                />
                            </label>
                            <label>
                                <span>{{ pageLang === "ar" ? "البريد الإلكتروني" : "Email" }}</span>
                                <input
                                    v-model.trim="contact.email"
                                    type="email"
                                    name="checkout_email"
                                    autocomplete="off"
                                    :readonly="contactFieldsLocked"
                                    @focus="unlockContactFields"
                                    required
                                />
                            </label>
                            <label class="ks-mobile-field">
                                <span>{{ pageLang === "ar" ? "الجوال" : "Mobile" }}</span>
                                <MobileFlag
                                    v-model="contact.mobile"
                                    initial-country="sa"
                                    :placeholder="pageLang === 'ar' ? '5xxxxxxxx' : '5xxxxxxxx'"
                                />
                            </label>
                        </div>
                    </section>

                    <!-- Delivery -->
                    <section class="ks-box">
                        <h2>{{ pageLang === "ar" ? "عنوان التوصيل" : "Delivery" }}</h2>

                        <div v-if="savedAddresses.length" class="ks-saved-addresses">
                            <label
                                v-for="addr in savedAddresses"
                                :key="addr.id"
                                class="ks-addr-card"
                                :class="{ active: selectedAddressId === addr.id }"
                            >
                                <input
                                    type="radio"
                                    name="saved_address"
                                    :value="addr.id"
                                    v-model="selectedAddressId"
                                    @change="applySavedAddress(addr)"
                                />
                                <div>
                                    <strong>{{ addr.full_name }}</strong>
                                    <p>{{ addr.street }}{{ addr.district ? `, ${addr.district}` : "" }}, {{ addr.city }}</p>
                                    <p lang="en" dir="ltr">{{ addr.country_code }} · {{ addr.mobile }}</p>
                                </div>
                            </label>
                            <button type="button" class="ks-link-btn" @click="useNewAddress">
                                {{ pageLang === "ar" ? "+ عنوان جديد" : "+ New address" }}
                            </button>
                        </div>

                        <div class="ks-fields" v-if="!selectedAddressId || showAddressForm">
                            <label>
                                <span>{{ pageLang === "ar" ? "الدولة / المنطقة" : "Country / Region" }}</span>
                                <select v-model="address.country_code" @change="onCountryChange">
                                    <option v-for="c in countries" :key="c.code" :value="c.code">
                                        {{ pageLang === "ar" ? c.name_ar : c.name_en }}
                                    </option>
                                </select>
                            </label>
                            <label>
                                <span>{{ pageLang === "ar" ? "الاسم على العنوان" : "Full name" }}</span>
                                <input v-model.trim="address.full_name" type="text" />
                            </label>
                            <div class="ks-fields-row">
                                <label>
                                    <span>{{ pageLang === "ar" ? "المدينة" : "City" }}</span>
                                    <input v-model.trim="address.city" type="text" />
                                </label>
                                <label>
                                    <span>{{ pageLang === "ar" ? "الحي" : "District" }}</span>
                                    <input v-model.trim="address.district" type="text" />
                                </label>
                            </div>
                            <label>
                                <span>{{ pageLang === "ar" ? "الشارع / العنوان" : "Address" }}</span>
                                <input v-model.trim="address.street" type="text" />
                            </label>
                            <div class="ks-fields-row">
                                <label>
                                    <span>{{ pageLang === "ar" ? "المبنى" : "Building" }}</span>
                                    <input v-model.trim="address.building" type="text" />
                                </label>
                                <label>
                                    <span>{{ pageLang === "ar" ? "الشقة (اختياري)" : "Apartment (optional)" }}</span>
                                    <input v-model.trim="address.apartment" type="text" />
                                </label>
                            </div>
                            <label>
                                <span>{{ pageLang === "ar" ? "الرقم الوطني" : "National ID" }}</span>
                                <input
                                    v-model.trim="address.national_id"
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    lang="en"
                                    dir="ltr"
                                />
                            </label>
                            <label>
                                <span>{{ pageLang === "ar" ? "ملاحظات" : "Notes" }}</span>
                                <textarea v-model.trim="address.notes" rows="2"></textarea>
                            </label>
                            <label class="ks-check">
                                <input v-model="saveAddress" type="checkbox" />
                                <span>{{ pageLang === "ar" ? "حفظ العنوان في حسابي" : "Save this address to my account" }}</span>
                            </label>
                        </div>
                    </section>

                    <!-- Shipping -->
                    <section class="ks-box">
                        <h2>{{ pageLang === "ar" ? "طريقة الشحن" : "Shipping method" }}</h2>
                        <p v-if="!shippingMethods.length" class="ks-muted">
                            {{ pageLang === "ar" ? "لا توجد طرق شحن مفعّلة حالياً." : "No active shipping methods." }}
                        </p>
                        <div v-else class="ks-ship-list">
                            <label
                                v-for="method in shippingMethods"
                                :key="method.id"
                                class="ks-ship-card"
                                :class="{ active: shippingMethodId === method.id }"
                            >
                                <input type="radio" name="shipping" :value="method.id" v-model.number="shippingMethodId" />
                                <div class="ks-ship-body">
                                    <strong>{{ pageLang === "ar" ? method.title_ar : method.title_en }}</strong>
                                </div>
                                <strong lang="en" dir="ltr">{{ formatMoney(method.price) }}</strong>
                            </label>
                        </div>
                    </section>

                    <!-- Payment -->
                    <section class="ks-box">
                        <h2>{{ pageLang === "ar" ? "الدفع" : "Payment" }}</h2>
                        <p class="ks-muted">
                            {{ pageLang === "ar"
                                ? "جميع المعاملات آمنة ومشفّرة."
                                : "All transactions are secure and encrypted." }}
                        </p>
                        <p v-if="!paymentMethods.length" class="ks-muted">
                            {{ pageLang === "ar" ? "لا توجد طرق دفع مفعّلة حالياً." : "No active payment methods." }}
                        </p>
                        <label
                            v-for="method in paymentMethods"
                            :key="method.id"
                            class="ks-pay-card"
                            :class="{ active: paymentMethodId === method.id }"
                        >
                            <input type="radio" name="payment_method" :value="method.id" v-model.number="paymentMethodId" />
                            <div>
                                <strong>{{ pageLang === "ar" ? method.title_ar : method.title_en }}</strong>
                                <span class="ks-muted" v-if="pageLang === 'ar' ? method.description_ar : method.description_en">
                                    {{ pageLang === "ar" ? method.description_ar : method.description_en }}
                                </span>
                            </div>
                        </label>
                    </section>
                </div>

                <!-- Summary -->
                <aside class="ks-checkout-summary">
                    <h2>{{ pageLang === "ar" ? "ملخص الطلب" : "Order summary" }}</h2>

                    <div v-if="totalSavings > 0" class="ks-savings-banner">
                        <span class="ks-savings-label">
                            {{ pageLang === "ar" ? "إجمالي التوفير:" : "Total savings:" }}
                        </span>
                        <strong class="ks-savings-amount" v-html="formatPriceHtml(totalSavings)"></strong>
                    </div>

                    <div class="ks-sum-items">
                        <div v-for="item in items" :key="item.id" class="ks-sum-item">
                            <img
                                v-if="item.image"
                                :src="normalizeCatalogImageUrl(item.image)"
                                :alt="item.title"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                            />
                            <div class="ks-sum-item-body">
                                <strong>{{ item.title }}</strong>
                                <span lang="en" dir="ltr">×{{ item.qty }}</span>
                                <span v-if="itemSavings(item) > 0" class="ks-sum-item-save">
                                    {{ pageLang === "ar" ? "وفّرت" : "You save" }}
                                    <span v-html="formatPriceHtml(itemSavings(item))"></span>
                                </span>
                            </div>
                            <div class="ks-sum-item-prices">
                                <span
                                    v-if="hasOldPrice(item)"
                                    class="ks-sum-price-old ks-price-strike"
                                    v-html="formatPriceHtml(item.price_old * item.qty)"
                                ></span>
                                <strong v-html="formatPriceHtml(item.price * item.qty)"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="ks-coupon">
                        <input
                            v-model.trim="couponCode"
                            type="text"
                            :placeholder="pageLang === 'ar' ? 'كود الخصم' : 'Discount code'"
                            @keyup.enter="applyCoupon"
                        />
                        <button type="button" @click="applyCoupon" :disabled="quoteLoading">
                            {{ pageLang === "ar" ? "تطبيق" : "Apply" }}
                        </button>
                    </div>
                    <p v-if="couponMessage" class="ks-coupon-msg" :class="{ ok: couponOk, err: !couponOk }">
                        {{ couponMessage }}
                    </p>

                    <div class="ks-s-row">
                        <span>{{ pageLang === "ar" ? "المجموع الفرعي" : "Subtotal" }}</span>
                        <strong
                            lang="en"
                            dir="ltr"
                            v-html="formatPriceHtml(quote.is_saudi ? quote.subtotal_incl_vat : quote.subtotal_excl_vat)"
                        ></strong>
                    </div>
                    <div v-if="quote.discount_amount > 0" class="ks-s-row">
                        <span>{{ pageLang === "ar" ? "الخصم" : "Discount" }}</span>
                        <strong lang="en" dir="ltr">- {{ formatMoney(quote.discount_amount) }}</strong>
                    </div>
                    <div class="ks-s-row">
                        <span>{{ pageLang === "ar" ? "الشحن" : "Shipping" }}</span>
                        <strong lang="en" dir="ltr" v-html="formatPriceHtml(quote.shipping_amount || 0)"></strong>
                    </div>
                    <div v-if="quote.is_saudi && quote.tax_amount > 0" class="ks-s-row">
                        <span>{{ pageLang === "ar" ? `الضريبة (${quote.vat_percent}%)` : `VAT (${quote.vat_percent}%)` }}</span>
                        <strong lang="en" dir="ltr" v-html="formatPriceHtml(quote.tax_amount)"></strong>
                    </div>
                    <div v-else class="ks-s-row">
                        <span>{{ pageLang === "ar" ? "الضريبة" : "Tax" }}</span>
                        <strong>{{ pageLang === "ar" ? "غير مطبّقة خارج السعودية" : "Not applied outside SA" }}</strong>
                    </div>
                    <hr />
                    <div class="ks-s-row ks-s-row-total">
                        <span>{{ pageLang === "ar" ? "الإجمالي" : "Total" }}</span>
                        <strong lang="en" dir="ltr" v-html="formatPriceHtml(quote.total || 0)"></strong>
                    </div>

                    <p v-if="errorMessage" class="ks-error">{{ errorMessage }}</p>

                    <button
                        type="button"
                        class="ks-pay-btn"
                        :disabled="placing || quoteLoading || !canPlace"
                        @click="placeOrder"
                    >
                        {{
                            placing
                                ? (pageLang === "ar" ? "جاري التحويل للدفع..." : "Redirecting to payment...")
                                : (pageLang === "ar" ? "ادفع الآن" : "Pay now")
                        }}
                    </button>
                </aside>
            </div>
        </div>
    </section>
</template>

<script>
import MobileFlag from "@/components/Website/auth/MobileFlag.vue";
import {
    getCartItems,
    getOrCreateCartToken,
    getCartProductSavings,
    syncCartToServer,
    CART_CHANGE_EVENT,
} from "@/utils/cartStorage.js";
import { formatWebsiteCurrency } from "@/utils/westernNumerals";
import { formatWebsitePriceHtml } from "@/utils/websiteCurrencyDisplay";
import { normalizeCatalogImageUrl } from "@/utils/catalogImageUrl";
import { authApi } from "@/axiosUser.js";

const COUNTRIES = [
    { code: "SA", name_en: "Saudi Arabia", name_ar: "المملكة العربية السعودية" },
    { code: "AE", name_en: "United Arab Emirates", name_ar: "الإمارات" },
    { code: "KW", name_en: "Kuwait", name_ar: "الكويت" },
    { code: "BH", name_en: "Bahrain", name_ar: "البحرين" },
    { code: "QA", name_en: "Qatar", name_ar: "قطر" },
    { code: "OM", name_en: "Oman", name_ar: "عُمان" },
    { code: "EG", name_en: "Egypt", name_ar: "مصر" },
    { code: "JO", name_en: "Jordan", name_ar: "الأردن" },
    { code: "US", name_en: "United States", name_ar: "الولايات المتحدة" },
    { code: "GB", name_en: "United Kingdom", name_ar: "المملكة المتحدة" },
    { code: "DE", name_en: "Germany", name_ar: "ألمانيا" },
];

export default {
    name: "CheckoutPage",
    components: { MobileFlag },
    data() {
        return {
            items: [],
            countries: COUNTRIES,
            shippingMethods: [],
            paymentMethods: [],
            savedAddresses: [],
            user: null,
            contact: { name: "", email: "", mobile: "" },
            address: {
                full_name: "",
                country_code: "SA",
                country_name: "Saudi Arabia",
                city: "",
                district: "",
                street: "",
                building: "",
                apartment: "",
                postal_code: "",
                national_id: "",
                notes: "",
            },
            selectedAddressId: null,
            showAddressForm: true,
            saveAddress: true,
            shippingMethodId: null,
            paymentMethodId: null,
            contactFieldsLocked: true,
            couponCode: "",
            appliedCoupon: "",
            couponMessage: "",
            couponOk: false,
            quote: {
                subtotal_incl_vat: 0,
                subtotal_excl_vat: 0,
                tax_amount: 0,
                discount_amount: 0,
                shipping_amount: 0,
                total: 0,
                vat_percent: 0,
                is_saudi: true,
            },
            quoteLoading: false,
            placing: false,
            errorMessage: "",
            quoteTimer: null,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        isLoggedIn() {
            return !!(localStorage.getItem("authToken") || localStorage.getItem("token"));
        },
        canPlace() {
            return (
                this.items.length > 0
                && this.contact.name
                && this.contact.email
                && this.contact.mobile
                && this.shippingMethodId
                && this.paymentMethodId
                && (this.selectedAddressId || (this.address.city && this.address.street && this.address.full_name))
                && Number(this.quote.total || 0) > 0
            );
        },
        productSavings() {
            return Math.round(getCartProductSavings(this.items) * 100) / 100;
        },
        couponSavings() {
            return Math.round(Number(this.quote?.discount_amount || 0) * 100) / 100;
        },
        totalSavings() {
            return Math.round((this.productSavings + this.couponSavings) * 100) / 100;
        },
    },
    watch: {
        shippingMethodId() {
            this.scheduleQuote();
        },
        "address.country_code"() {
            this.scheduleQuote();
        },
        items: {
            deep: true,
            handler() {
                this.scheduleQuote();
            },
        },
    },
    mounted() {
        this.prefillContactFromLocalUser();
        this.reloadCart();
        window.addEventListener(CART_CHANGE_EVENT, this.reloadCart);
        this.loadOptions();
        if (!this.$store.getters.getSetting) {
            this.$store.dispatch("fetchSetting");
        }
    },
    beforeUnmount() {
        window.removeEventListener(CART_CHANGE_EVENT, this.reloadCart);
        if (this.quoteTimer) {
            clearTimeout(this.quoteTimer);
        }
    },
    methods: {
        normalizeCatalogImageUrl,
        digitsMobile(value) {
            return String(value || "").replace(/\D/g, "");
        },
        clearGuestContact() {
            this.user = null;
            this.contact = { name: "", email: "", mobile: "" };
            this.contactFieldsLocked = true;
        },
        unlockContactFields() {
            this.contactFieldsLocked = false;
        },
        prefillContactFromLocalUser() {
            if (!this.isLoggedIn) {
                this.clearGuestContact();
                return;
            }
            const storeUser = this.$store?.state?.user;
            let localUser = null;
            try {
                localUser = JSON.parse(localStorage.getItem("user") || "null");
            } catch (_) {
                localUser = null;
            }
            const u = storeUser || localUser;
            if (!u) return;
            this.applyUserContact(u);
        },
        applyUserContact(user) {
            if (!this.isLoggedIn || !user) return;
            this.user = user;
            this.contact.name = user.name || "";
            this.contact.email = user.email || "";
            this.contact.mobile = this.digitsMobile(user.mobile) || "";
            this.contactFieldsLocked = false;
        },
        formatMoney(value) {
            return formatWebsiteCurrency(value, "SAR", { maximumFractionDigits: 2 });
        },
        hasOldPrice(item) {
            const oldPrice = Number(item?.price_old || 0);
            const nowPrice = Number(item?.price || 0);
            return oldPrice > 0 && nowPrice > 0 && oldPrice > nowPrice;
        },
        itemSavings(item) {
            if (!this.hasOldPrice(item)) return 0;
            const qty = Number(item.qty || 0);
            return Math.round((Number(item.price_old) - Number(item.price)) * qty * 100) / 100;
        },
        formatPriceHtml(value) {
            return formatWebsitePriceHtml(value, "SAR", this.pageLang, { maximumFractionDigits: 2 });
        },
        reloadCart() {
            this.items = getCartItems();
        },
        filterVisiblePaymentMethods(methods) {
            const hidden = ["tabby", "tamara"];
            return (methods || []).filter((method) => {
                const code = String(method?.code || "").toLowerCase().trim();
                if (hidden.includes(code)) return false;
                const title = `${method?.title_en || ""} ${method?.title_ar || ""}`.toLowerCase();
                if (title.includes("tabby") || title.includes("tamara")) return false;
                if ((method?.title_ar || "").includes("تابي") || (method?.title_ar || "").includes("تمارا")) {
                    return false;
                }
                return true;
            });
        },
        async loadOptions() {
            try {
                const { data } = await authApi.get("checkout/options");
                this.shippingMethods = Array.isArray(data.shipping_methods) ? data.shipping_methods : [];
                this.paymentMethods = this.filterVisiblePaymentMethods(
                    Array.isArray(data.payment_methods) ? data.payment_methods : []
                );
                this.savedAddresses = Array.isArray(data.addresses) ? data.addresses : [];
                if (this.isLoggedIn && data.user) {
                    this.applyUserContact(data.user);
                } else if (!this.isLoggedIn) {
                    this.clearGuestContact();
                }
                // Do not auto-select shipping or payment — user must choose.
                this.shippingMethodId = null;
                this.paymentMethodId = null;
                if (this.isLoggedIn) {
                    const def = this.savedAddresses.find((a) => a.is_default) || this.savedAddresses[0];
                    if (def) {
                        this.applySavedAddress(def);
                    }
                }
                this.scheduleQuote();
            } catch (e) {
                console.error(e);
                this.errorMessage =
                    e?.response?.data?.message
                    || (this.pageLang === "ar"
                        ? "تعذر تحميل خيارات الدفع/الشحن. تأكد من تشغيل migrate على السيرفر."
                        : "Could not load checkout options. Ensure migrations ran on the server.");
            }
        },
        applySavedAddress(addr) {
            this.selectedAddressId = addr.id;
            this.showAddressForm = false;
            this.address = {
                full_name: addr.full_name || "",
                country_code: addr.country_code || "SA",
                country_name: addr.country_name || "",
                city: addr.city || "",
                district: addr.district || "",
                street: addr.street || "",
                building: addr.building || "",
                apartment: addr.apartment || "",
                postal_code: addr.postal_code || "",
                national_id: addr.national_id || addr.postal_code || "",
                notes: addr.notes || "",
            };
            if (!this.contact.name) this.contact.name = addr.full_name || "";
            if (!this.contact.email) this.contact.email = addr.email || "";
            const addrMobile = this.digitsMobile(addr.mobile);
            if (!this.contact.mobile && addrMobile) this.contact.mobile = addrMobile;
            this.scheduleQuote();
        },
        useNewAddress() {
            this.selectedAddressId = null;
            this.showAddressForm = true;
            this.address.full_name = this.contact.name || "";
        },
        onCountryChange() {
            const found = this.countries.find((c) => c.code === this.address.country_code);
            this.address.country_name = found
                ? (this.pageLang === "ar" ? found.name_ar : found.name_en)
                : this.address.country_code;
            this.scheduleQuote();
        },
        scheduleQuote() {
            if (this.quoteTimer) clearTimeout(this.quoteTimer);
            this.quoteTimer = setTimeout(() => this.fetchQuote(), 250);
        },
        async fetchQuote() {
            if (!this.items.length) return;
            this.quoteLoading = true;
            try {
                const { data } = await authApi({
                    method: "post",
                    url: "checkout/quote",
                    data: {
                        items: this.items.map((i) => ({
                            sku: i.sku,
                            title: i.title,
                            price: i.price,
                            qty: i.qty,
                        })),
                        country_code: this.address.country_code || "SA",
                        coupon_code: this.appliedCoupon || null,
                        shipping_method_id: this.shippingMethodId,
                    },
                });
                if (data?.quote) {
                    this.quote = data.quote;
                }
                if (this.appliedCoupon) {
                    this.couponOk = !!data?.status;
                    this.couponMessage = data?.status
                        ? (this.pageLang === "ar" ? "تم تطبيق الكوبون" : "Coupon applied")
                        : (data?.message || (this.pageLang === "ar" ? "كوبون غير صالح" : "Invalid coupon"));
                    if (!data?.status) {
                        this.appliedCoupon = "";
                    }
                }
            } catch (e) {
                const msg = e?.response?.data?.message;
                if (this.appliedCoupon) {
                    this.couponOk = false;
                    this.couponMessage = msg || (this.pageLang === "ar" ? "كوبون غير صالح" : "Invalid coupon");
                    this.appliedCoupon = "";
                    this.scheduleQuote();
                }
            } finally {
                this.quoteLoading = false;
            }
        },
        applyCoupon() {
            this.appliedCoupon = (this.couponCode || "").trim().toUpperCase();
            if (!this.appliedCoupon) {
                this.couponMessage = "";
                this.scheduleQuote();
                return;
            }
            this.fetchQuote();
        },
        async placeOrder() {
            if (!this.canPlace || this.placing) return;
            this.errorMessage = "";
            this.placing = true;
            try {
                await syncCartToServer(this.items);
                const payload = {
                    items: this.items.map((i) => ({
                        id: i.id,
                        sku: i.sku,
                        title: i.title,
                        price: i.price,
                        qty: i.qty,
                        image: i.image,
                    })),
                    country_code: this.address.country_code || "SA",
                    coupon_code: this.appliedCoupon || null,
                    shipping_method_id: this.shippingMethodId,
                    payment_method_id: this.paymentMethodId,
                    cart_token: getOrCreateCartToken(),
                    payment_from: "web",
                    contact: { ...this.contact },
                    address: {
                        ...this.address,
                        full_name: this.address.full_name || this.contact.name,
                    },
                    save_address: this.saveAddress || this.isLoggedIn,
                    address_id: this.selectedAddressId,
                };
                const { data } = await authApi.post("checkout/place-order", payload);
                if (data?.status && data?.payment_url) {
                    window.location.href = data.payment_url;
                    return;
                }
                this.errorMessage = data?.message
                    || (this.pageLang === "ar" ? "تعذر إنشاء الطلب" : "Could not place order");
            } catch (e) {
                this.errorMessage =
                    e?.response?.data?.message
                    || e?.message
                    || (this.pageLang === "ar" ? "تعذر إتمام الطلب" : "Checkout failed");
            } finally {
                this.placing = false;
            }
        },
    },
};
</script>

<style scoped>
.ks-checkout-page { background: #fff; color: #111; padding: 32px 16px 64px; min-height: 70vh; }
.ks-checkout-wrap { max-width: 1120px; margin: 0 auto; }
.ks-checkout-back {
    display: inline-flex; align-items: center; gap: 8px; margin: 0 0 14px;
    color: #111; font-size: 14px; font-weight: 700; text-decoration: none;
}
.ks-checkout-back:hover { color: #b88600; }
h1 { margin: 0 0 18px; font-size: clamp(28px, 3vw, 40px); font-weight: 900; }
.ks-checkout-empty { color: #5f5f5f; }
.ks-checkout-grid { display: grid; grid-template-columns: 1fr 340px; gap: 18px; align-items: start; }
.ks-box {
    border: 1px solid #ececec; border-radius: 6px; padding: 16px; margin-bottom: 14px; background: #fff;
}
.ks-box h2 { margin: 0 0 12px; font-size: 18px; font-weight: 800; }
.ks-box-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px; }
.ks-box-head h2 { margin: 0; }
.ks-box-head a { color: #8a7200; font-weight: 700; text-decoration: none; font-size: 13px; }
.ks-logged-as { margin-bottom: 12px; color: #555; font-size: 13px; }
.ks-fields { display: grid; gap: 10px; }
.ks-fields-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.ks-fields label { display: grid; gap: 6px; font-size: 13px; font-weight: 700; }
.ks-fields input, .ks-fields select, .ks-fields textarea {
    border: 1px solid #ddd; border-radius: 4px; min-height: 42px; padding: 8px 12px;
    font-size: 14px; font-weight: 500; width: 100%; background: #fff;
}
.ks-fields textarea { min-height: 72px; resize: vertical; }
.ks-check { display: flex !important; align-items: center; gap: 8px; font-weight: 600 !important; }
.ks-check input { width: auto; min-height: auto; }
.ks-muted { color: #666; font-size: 13px; }
.ks-saved-addresses { display: grid; gap: 8px; margin-bottom: 12px; }
.ks-addr-card, .ks-ship-card, .ks-pay-card {
    display: flex; gap: 10px; align-items: flex-start; border: 1px solid #ececec;
    border-radius: 6px; padding: 12px; cursor: pointer;
}
.ks-addr-card.active, .ks-ship-card.active, .ks-pay-card.active { border-color: #f5c400; background: #fffbeb; }
.ks-addr-card p { margin: 2px 0 0; color: #666; font-size: 12px; font-weight: 500; }
.ks-ship-list { display: grid; gap: 8px; }
.ks-ship-body { flex: 1; display: grid; gap: 2px; }
.ks-link-btn {
    border: 0; background: transparent; color: #8a7200; font-weight: 700;
    text-align: start; padding: 4px 0; cursor: pointer;
}
.ks-pay-hint { margin: 10px 0 0; }
.ks-checkout-summary {
    border: 1px solid #ececec; border-radius: 6px; padding: 14px; background: #fff;
    position: sticky; top: 16px;
}
.ks-checkout-summary h2 { margin: 0 0 12px; font-size: 18px; font-weight: 800; }
.ks-savings-banner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px 12px;
    margin: 0 0 12px;
    padding: 10px 12px;
    background: linear-gradient(90deg, #fff8df 0%, #ffefb3 100%);
    border: 1px solid #efc527;
    border-radius: 6px;
    color: #5a4500;
}
.ks-savings-label {
    font-size: 13px;
    font-weight: 700;
}
.ks-savings-amount {
    font-size: 14px;
    font-weight: 900;
    color: #3f3000;
}
.ks-sum-item-save {
    color: #8a6d00;
    font-size: 11px;
    font-weight: 700;
}
.ks-sum-items { display: grid; gap: 10px; margin-bottom: 12px; max-height: 260px; overflow: auto; }
.ks-sum-item { display: grid; grid-template-columns: 54px 1fr auto; gap: 8px; align-items: center; }
.ks-sum-item img { width: 54px; height: 54px; object-fit: cover; border: 1px solid #ececec; }
.ks-sum-item-body { display: grid; gap: 2px; font-size: 13px; }
.ks-sum-item-prices {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
    text-align: end;
}
.ks-sum-price-old {
    color: #979ea0;
    font-size: 11px;
    font-weight: 500;
}
.ks-coupon { display: grid; grid-template-columns: 1fr auto; gap: 8px; margin-bottom: 8px; }
.ks-coupon input {
    border: 1px solid #ddd; border-radius: 4px; min-height: 40px; padding: 0 10px; font-size: 13px;
}
.ks-coupon button {
    border: 0; background: #111; color: #fff; font-weight: 700; padding: 0 14px; border-radius: 4px; cursor: pointer;
}
.ks-coupon-msg { font-size: 12px; margin: 0 0 10px; }
.ks-coupon-msg.ok { color: #067647; }
.ks-coupon-msg.err { color: #b42318; }
.ks-s-row { display: flex; justify-content: space-between; gap: 10px; margin: 8px 0; font-size: 14px; }
.ks-s-row-total { font-size: 16px; font-weight: 800; }
.ks-pay-btn {
    width: 100%; min-height: 46px; border: 0; background: #ffcc00; color: #111;
    font-weight: 800; margin-top: 12px; cursor: pointer; border-radius: 4px;
}
.ks-pay-btn:disabled { opacity: 0.6; cursor: wait; }
.ks-error { color: #b42318; font-size: 13px; margin: 10px 0 0; }
@media (max-width: 900px) {
    .ks-checkout-grid { grid-template-columns: 1fr; }
    .ks-checkout-summary { position: static; }
    .ks-fields-row { grid-template-columns: 1fr; }
}
</style>
