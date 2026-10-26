<template>
    <div class="pay-commission-container" :lang="pageLang">
        <div class="pay-commission-card">
            <button class="pay-commission-back" @click="handleBack">
                <i
                    :class="pageLang === 'ar' ? 'fas fa-angle-right' : 'fas fa-angle-left'"
                ></i>
                {{ pageLang === "ar" ? "العودة للرئيسية" : "Back to home" }}
            </button>
            <div class="pay-commission-header">
                <img
                    src="/assets/media/svg/brand-logos/logo.png"
                    class="pay-commission-logo"
                    alt=""
                />
                <div class="pay-commission-title">
                    {{ pageLang === "ar" ? "تفاصيل الدفع" : "Payment Status Details" }}
                </div>
            </div>
            <hr class="pay-commission-divider" />
            <div>
                <div class="pay-commission-label">
                    {{
                        pageLang === "ar"
                            ? "شكراً لتسوقك مع KEEPERsport — تم استلام طلبك بنجاح."
                            : "Thank you for shopping with KEEPERsport — your order was received."
                    }}
                </div>
            </div>
            <div class="pay-commission-bank-card" v-if="payment">
                <div class="pay-commission-bank-title">
                    {{ payment.order_title || payment?.ad?.title || (pageLang === "ar" ? "طلب شراء" : "Order") }}
                </div>
                <div class="pay-commission-bank-info">
                    {{ pageLang === "ar" ? "المرجع" : "Reference" }}:
                    <span lang="en" dir="ltr">{{ payment.track_id }}</span>
                </div>
                <div class="pay-commission-bank-info">
                    {{ pageLang === "ar" ? "رقم الطلب" : "Order no." }}:
                    <span lang="en" dir="ltr">{{ payment.order_number }}</span>
                </div>
                <div class="pay-commission-bank-info">
                    {{ pageLang === "ar" ? "المبلغ" : "Amount" }}:
                    <span lang="en" dir="ltr">{{ payment.amount }} {{ payment.currency || "SAR" }}</span>
                </div>
                <div class="pay-commission-bank-info">
                    {{ pageLang === "ar" ? "الحالة" : "Status" }}: {{ payment.status }}
                </div>

                <div v-if="cartItems.length" class="ks-paid-items">
                    <h4>{{ pageLang === "ar" ? "المنتجات" : "Items" }}</h4>
                    <div v-for="(row, idx) in cartItems" :key="idx" class="ks-paid-item">
                        <div>
                            <strong>{{ row.title || row.sku }}</strong>
                            <p v-if="row.sku" lang="en" dir="ltr">SKU: {{ row.sku }}</p>
                        </div>
                        <div class="ks-paid-item-meta" lang="en" dir="ltr">
                            ×{{ row.qty || 1 }} · {{ Number(row.price || 0).toFixed(2) }} SAR
                        </div>
                    </div>
                </div>

                <div class="ks-paid-actions">
                    <router-link
                        v-if="payment.track_id"
                        :to="`/${locale}/invoice/${payment.track_id}`"
                        class="ks-paid-link"
                        target="_blank"
                    >
                        {{ pageLang === "ar" ? "عرض الفاتورة" : "View invoice" }}
                    </router-link>
                    <router-link :to="`/${locale}/account/orders`" class="ks-paid-link">
                        {{ pageLang === "ar" ? "طلباتي" : "My orders" }}
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { clearCartStorage } from "@/utils/cartStorage.js";

export default {
    name: "PaymentSuccess",
    data() {
        return {
            payment: null,
            isLoading: false,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        cartItems() {
            if (Array.isArray(this.payment?.cart_items) && this.payment.cart_items.length) {
                return this.payment.cart_items;
            }
            const rawItems = this.payment?.raw_response?.items;
            return Array.isArray(rawItems) ? rawItems : [];
        },
    },
    mounted() {
        this.fetchItems();
    },
    methods: {
        handleBack() {
            this.$router.push({ path: `/${this.locale}` });
        },
        async fetchItems() {
            this.isLoading = true;
            const trackId = this.$route.query.track_id;
            try {
                const response = await axios.get("Payment/getAllItems", {
                    params: { trackId: trackId },
                });
                this.payment = response.data.items;
                if (
                    this.payment
                    && (this.payment.is_cart_order || this.payment.payment_type === "cart")
                    && String(this.payment.status || "").toLowerCase().includes("paid")
                ) {
                    clearCartStorage();
                }
            } catch (error) {
                console.error(error);
            } finally {
                this.isLoading = false;
            }
        },
    },
};
</script>

<style scoped>
.pay-commission-container {
    min-height: 100vh;
    background: #fafbfc;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 48px 0 64px 0;
}
.pay-commission-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.07);
    padding: 38px 38px 32px 38px;
    min-width: 420px;
    max-width: 710px;
    width: 100%;
    margin-top: 18px;
}
.pay-commission-header {
    display: flex;
    align-items: center;
    gap: 18px;
    justify-content: flex-start;
}
.pay-commission-logo {
    width: 54px;
    height: 54px;
    border-radius: 8px;
    object-fit: contain;
}
.pay-commission-title {
    flex: 1;
    text-align: center;
    font-size: 1.6rem;
    font-weight: bold;
    color: #212f3a;
}
.pay-commission-divider {
    margin: 18px 0;
    border: none;
    border-top: 2px solid #e5e5e5;
}
.pay-commission-label {
    text-align: center;
    color: #222;
    font-size: 1.08rem;
    margin-bottom: 18px;
}
.pay-commission-bank-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
    padding: 24px 18px 18px;
    margin-top: 24px;
    text-align: center;
}
.pay-commission-bank-title {
    font-weight: bold;
    color: #222;
    font-size: 1.15rem;
    margin-bottom: 6px;
}
.pay-commission-bank-info {
    color: #555;
    font-size: 1.05rem;
    margin-bottom: 4px;
}
.pay-commission-back {
    background: none;
    border: none;
    color: #222;
    font-size: 1.08rem;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    margin-bottom: 50px;
    font-weight: 500;
}
.ks-paid-items {
    margin-top: 18px;
    text-align: start;
    border-top: 1px solid #eee;
    padding-top: 12px;
}
.ks-paid-items h4 {
    margin: 0 0 10px;
    font-size: 15px;
}
.ks-paid-item {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f2f2f2;
    font-size: 13px;
}
.ks-paid-item p {
    margin: 2px 0 0;
    color: #777;
    font-size: 12px;
}
.ks-paid-item-meta {
    white-space: nowrap;
    color: #444;
    font-weight: 700;
}
.ks-paid-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 18px;
}
.ks-paid-link {
    display: inline-flex;
    align-items: center;
    min-height: 40px;
    padding: 0 16px;
    border-radius: 6px;
    background: #ffcc00;
    color: #111;
    font-weight: 800;
    text-decoration: none;
}
@media (max-width: 700px) {
    .pay-commission-card {
        min-width: 0;
        padding: 18px 4vw;
    }
}
</style>
