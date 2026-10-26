<template>
    <div class="ks-account-panel">
        <header class="ks-account-panel-head">
            <h1>{{ t.orders.title }}</h1>
            <p>{{ t.orders.subtitle }}</p>
        </header>

        <div v-if="loading" class="ks-account-muted">{{ t.profile.saving }}</div>

        <div v-else-if="!orders.length" class="ks-account-empty">
            <p>{{ t.orders.empty }}</p>
            <router-link :to="`/${locale}/`" class="ks-account-btn">{{ t.dashboard.openShop }}</router-link>
        </div>

        <div v-else class="ks-account-table-wrap">
            <table class="ks-account-table">
                <thead>
                    <tr>
                        <th>{{ t.orders.orderNo }}</th>
                        <th>{{ t.orders.date }}</th>
                        <th>{{ pageLang === "ar" ? "المنتجات" : "Products" }}</th>
                        <th>{{ t.orders.amount }}</th>
                        <th>{{ pageLang === "ar" ? "حالة الطلب" : "Order status" }}</th>
                        <th>{{ pageLang === "ar" ? "حالة الدفع" : "Payment" }}</th>
                        <th>{{ t.orders.invoice }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders" :key="order.id">
                        <td lang="en" dir="ltr">{{ orderLabel(order) }}</td>
                        <td>{{ order.created_at || "—" }}</td>
                        <td>{{ order.order_title || order?.ad?.title || "—" }}</td>
                        <td lang="en" dir="ltr">{{ formatMoney(order.amount, order.currency) }}</td>
                        <td>{{ order.order_status_label_ar || (pageLang === "ar" ? "طلب جديد" : "New order") }}</td>
                        <td>
                            <span class="ks-account-status" :class="paymentStatusClass(order)">
                                {{ paymentStatusLabel(order) }}
                            </span>
                        </td>
                        <td>
                            <router-link
                                v-if="order.track_id"
                                :to="`/${locale}/invoice/${order.track_id}`"
                                class="ks-account-table-link"
                                target="_blank"
                            >
                                {{ t.orders.view }}
                            </router-link>
                            <span v-else>—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import { authApi } from "@/axiosUser";
import { accountCopy } from "@/constants/accountI18n.js";

export default {
    name: "AccountOrders",
    data() {
        return {
            orders: [],
            loading: true,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        t() {
            return accountCopy(this.pageLang);
        },
    },
    mounted() {
        this.fetchOrders();
    },
    methods: {
        async fetchOrders() {
            this.loading = true;
            try {
                const { data } = await authApi.get("order/invoices");
                this.orders = Array.isArray(data?.items) ? data.items : [];
            } catch (_) {
                this.orders = [];
            } finally {
                this.loading = false;
            }
        },
        orderLabel(order) {
            return order.order_number || order.track_id || `#${order.id}`;
        },
        formatMoney(amount, currency) {
            const value = Number(amount || 0).toFixed(2);
            const cur = currency || "SAR";
            return `${value} ${cur}`;
        },
        statusLabel(status) {
            const s = String(status || "").toLowerCase();
            if (s.includes("success") || s.includes("paid") || s === "completed") {
                return this.t.orders.status_paid;
            }
            if (s.includes("fail") || s.includes("cancel")) {
                return this.t.orders.status_failed;
            }
            return this.t.orders.status_pending;
        },
        paymentStatusLabel(order) {
            if (order?.is_paid) return this.pageLang === "ar" ? "مدفوع" : "Paid";
            const s = String(order?.status || "").toLowerCase();
            if (s.includes("paid") || s.includes("success")) {
                return this.pageLang === "ar" ? "مدفوع" : "Paid";
            }
            return this.pageLang === "ar" ? "غير مدفوعة" : "Unpaid";
        },
        paymentStatusClass(order) {
            const paid = this.paymentStatusLabel(order) === (this.pageLang === "ar" ? "مدفوع" : "Paid");
            return paid ? "is-paid" : "is-failed";
        },
        statusClass(status) {
            const s = String(status || "").toLowerCase();
            if (s.includes("success") || s.includes("paid") || s === "completed") {
                return "is-paid";
            }
            if (s.includes("fail") || s.includes("cancel")) {
                return "is-failed";
            }
            return "is-pending";
        },
    },
};
</script>

<style scoped>
.ks-account-panel {
    background: #fff;
    border: 1px solid #e6e6e6;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.ks-account-panel-head h1 {
    margin: 0 0 6px;
    font-size: clamp(22px, 2.5vw, 30px);
    font-weight: 800;
}

.ks-account-panel-head p {
    margin: 0 0 20px;
    color: #555;
    font-size: 14px;
}

.ks-account-muted {
    color: #666;
    padding: 24px 0;
    text-align: center;
}

.ks-account-empty {
    text-align: center;
    padding: 32px 16px;
    color: #555;
}

.ks-account-empty p {
    margin: 0 0 16px;
}

.ks-account-table-wrap {
    overflow-x: auto;
}

.ks-account-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.ks-account-table th,
.ks-account-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #ececec;
    text-align: start;
}

.ks-account-table th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #666;
    font-weight: 800;
}

.ks-account-table-link {
    color: #8a7200;
    font-weight: 700;
    text-decoration: none;
}

.ks-account-table-link:hover {
    text-decoration: underline;
}

.ks-account-status {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}

.ks-account-status.is-paid {
    background: #e8f8ef;
    color: #067647;
}

.ks-account-status.is-pending {
    background: #fff6db;
    color: #8a7200;
}

.ks-account-status.is-failed {
    background: #fdecec;
    color: #b42318;
}

.ks-account-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 18px;
    border-radius: 4px;
    background: #f5c400;
    color: #111;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
}
</style>
