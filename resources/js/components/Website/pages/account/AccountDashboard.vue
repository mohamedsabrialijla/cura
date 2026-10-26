<template>
    <div class="ks-account-panel">
        <header class="ks-account-panel-head">
            <h1>{{ t.dashboard.title }}</h1>
            <p>{{ t.dashboard.subtitle }}</p>
        </header>

        <div class="ks-account-welcome">
            <div>
                <p class="ks-account-welcome-kicker">{{ t.dashboard.welcome }}</p>
                <h2>{{ userName }}</h2>
            </div>
            <router-link :to="`/${locale}/account/profile`" class="ks-account-btn ks-account-btn--ghost">
                {{ t.dashboard.editProfile }}
            </router-link>
        </div>

        <div class="ks-account-cards">
            <article class="ks-account-card">
                <span class="ks-account-card-label">{{ t.dashboard.cartCard }}</span>
                <strong class="ks-account-card-value" lang="en" dir="ltr">{{ cartCount }}</strong>
                <router-link :to="`/${locale}/cart`" class="ks-account-card-link">
                    {{ t.dashboard.viewCart }} →
                </router-link>
            </article>

            <article class="ks-account-card">
                <span class="ks-account-card-label">{{ t.dashboard.ordersCard }}</span>
                <strong class="ks-account-card-value" lang="en" dir="ltr">{{ ordersCount }}</strong>
                <router-link :to="`/${locale}/account/orders`" class="ks-account-card-link">
                    {{ t.dashboard.viewOrders }} →
                </router-link>
            </article>

            <article class="ks-account-card">
                <span class="ks-account-card-label">{{ t.dashboard.profileCard }}</span>
                <strong class="ks-account-card-value ks-account-card-value--text">{{ userEmail }}</strong>
                <router-link :to="`/${locale}/account/profile`" class="ks-account-card-link">
                    {{ t.dashboard.editProfile }} →
                </router-link>
            </article>
        </div>

        <div class="ks-account-panel-foot">
            <router-link :to="`/${locale}/`" class="ks-account-btn">
                {{ t.dashboard.openShop }}
            </router-link>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import { authApi } from "@/axiosUser";
import { getCartCount } from "@/utils/cartStorage.js";
import { accountCopy } from "@/constants/accountI18n.js";

export default {
    name: "AccountDashboard",
    data() {
        return {
            ordersCount: 0,
            cartCount: 0,
        };
    },
    computed: {
        ...mapGetters(["getUser"]),
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        t() {
            return accountCopy(this.pageLang);
        },
        userName() {
            return this.getUser?.name || "—";
        },
        userEmail() {
            const email = this.getUser?.email || "";
            if (email.length <= 28) return email || "—";
            return `${email.slice(0, 26)}…`;
        },
    },
    mounted() {
        this.cartCount = getCartCount();
        this.fetchOrdersCount();
    },
    methods: {
        async fetchOrdersCount() {
            try {
                const { data } = await authApi.get("order/invoices");
                const rows = Array.isArray(data?.items) ? data.items : [];
                this.ordersCount = rows.length;
            } catch (_) {
                this.ordersCount = 0;
            }
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
    color: #111;
}

.ks-account-panel-head p {
    margin: 0 0 20px;
    color: #555;
    font-size: 14px;
    line-height: 1.5;
}

.ks-account-welcome {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 18px;
    margin-bottom: 20px;
    border-radius: 8px;
    background: linear-gradient(135deg, #fff9df 0%, #fff 55%);
    border: 1px solid #f0e3a8;
}

.ks-account-welcome-kicker {
    margin: 0 0 4px;
    font-size: 12px;
    font-weight: 700;
    color: #8a7200;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.ks-account-welcome h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    color: #111;
}

.ks-account-cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.ks-account-card {
    border: 1px solid #ececec;
    border-radius: 8px;
    padding: 16px;
    background: #fafafa;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: 130px;
}

.ks-account-card-label {
    font-size: 12px;
    font-weight: 700;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.ks-account-card-value {
    font-size: 28px;
    font-weight: 800;
    color: #111;
    line-height: 1.1;
}

.ks-account-card-value--text {
    font-size: 14px;
    font-weight: 700;
    word-break: break-all;
}

.ks-account-card-link {
    margin-top: auto;
    font-size: 13px;
    font-weight: 700;
    color: #8a7200;
    text-decoration: none;
}

.ks-account-card-link:hover {
    text-decoration: underline;
}

.ks-account-panel-foot {
    margin-top: 20px;
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
    border: 0;
    cursor: pointer;
}

.ks-account-btn--ghost {
    background: #fff;
    border: 1px solid #ddd;
}

.ks-account-btn:hover {
    filter: brightness(0.97);
}

@media (max-width: 768px) {
    .ks-account-cards {
        grid-template-columns: 1fr;
    }

    .ks-account-welcome {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
