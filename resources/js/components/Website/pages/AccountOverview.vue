<template>
    <section class="ks-account-overview">
        <div class="ks-container">
            <div
                class="ks-account-overview-inner"
                :class="pageLang === 'ar' ? 'ks-account-overview-inner--ar' : 'ks-account-overview-inner--en'"
            >
                <h1>{{ t.title }}</h1>
                <ol class="ks-account-list">
                    <li v-for="(item, idx) in itemsResolved" :key="idx">
                        <span class="ks-account-item-index">{{ idx + 1 }}</span>
                        <div class="ks-account-item-body">
                            <h3>
                                <a
                                    v-if="item.target && item.target.type !== 'action'"
                                    class="ks-account-title-link"
                                    :href="item.target.type === 'url' ? item.target.to : undefined"
                                    @click.prevent="handleItem(item)"
                                    >{{ item.title }}</a
                                >
                                <button
                                    v-else
                                    type="button"
                                    class="ks-account-title-link ks-account-title-link--btn"
                                    @click="handleItem(item)"
                                >
                                    {{ item.title }}
                                </button>
                            </h3>
                            <p>{{ item.desc }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </section>
</template>

<script>
const ACCOUNT_I18N = {
    en: {
        title: "Account Overview",
        openCta: "Open",
        logoutCta: "Logout",
        items: [
            {
                key: "profile",
                title: "My profile",
                desc: "Check or edit your details and your shipping/billing addresses.",
            },
            {
                key: "orders",
                title: "My orders",
                desc: "View all your orders in one place.",
            },
            {
                key: "support",
                title: "Support",
                desc: "Find answers to common questions and contact us.",
            },
            {
                key: "returns",
                title: "Returns",
                desc: "Useful information for returning products.",
            },
            {
                key: "glove_guide",
                title: "Glove guide",
                desc: "Everything you need to know about goalkeeper gloves.",
            },
            {
                key: "glove_care",
                title: "Glove care",
                desc: "Our suggestions to maintain your gloves correctly.",
            },
            {
                key: "about",
                title: "About KEEPERsport",
                desc: "Discover more about who we are.",
            },
            {
                key: "logout",
                title: "Logout",
                desc: "See you again soon.",
            },
        ],
    },
    ar: {
        title: "نظرة عامة على الحساب",
        openCta: "فتح",
        logoutCta: "تسجيل الخروج",
        items: [
            {
                key: "profile",
                title: "ملفي الشخصي",
                desc: "راجع أو عدّل بياناتك وعناوين الشحن والفوترة.",
            },
            {
                key: "orders",
                title: "طلباتي",
                desc: "عرض جميع طلباتك في مكان واحد.",
            },
            {
                key: "support",
                title: "الدعم",
                desc: "اعثر على إجابات للأسئلة الشائعة وتواصل معنا.",
            },
            {
                key: "returns",
                title: "الإرجاع",
                desc: "كل المعلومات المفيدة لتنفيذ عملية إرجاع.",
            },
            {
                key: "glove_guide",
                title: "دليل القفازات",
                desc: "كل ما تحتاج معرفته عن قفازات حراس المرمى.",
            },
            {
                key: "glove_care",
                title: "العناية بالقفازات",
                desc: "نصائحنا للحفاظ على القفازات بالشكل الصحيح.",
            },
            {
                key: "about",
                title: "عن KEEPERsport",
                desc: "اكتشف المزيد عنّا.",
            },
            {
                key: "logout",
                title: "تسجيل الخروج",
                desc: "إلى اللقاء.",
            },
        ],
    },
};

export default {
    name: "AccountOverview",
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        t() {
            return ACCOUNT_I18N[this.pageLang] || ACCOUNT_I18N.en;
        },
        itemTargets() {
            return {
                profile: { type: "route", to: `/${this.locale}/login` },
                orders: { type: "route", to: `/${this.locale}/login` },
                support: { type: "route", to: `/${this.locale}/faq` },
                returns: { type: "route", to: `/${this.locale}/faq?tab=returns` },
                glove_guide: { type: "route", to: `/${this.locale}/faq?tab=glove-guide` },
                glove_care: { type: "route", to: `/${this.locale}/glove-care` },
                about: { type: "route", to: `/${this.locale}/about-us` },
                logout: { type: "action", to: "logout" },
            };
        },
        itemsResolved() {
            return this.t.items.map((item) => ({
                ...item,
                target: this.itemTargets[item.key] || null,
            }));
        },
    },
    methods: {
        handleItem(item) {
            if (!item?.target) return;
            const target = item.target;
            if (target.type === "route") {
                this.$router.push(target.to);
                return;
            }
            if (target.type === "url") {
                window.open(target.to, "_blank", "noopener");
                return;
            }
            if (target.type === "action" && target.to === "logout") {
                localStorage.removeItem("authToken");
                localStorage.removeItem("user");
                this.$router.push(`/${this.locale}/login`);
            }
        },
    },
};
</script>

<style scoped>
.ks-account-overview {
    background: #fff;
    color: #111;
    padding: 48px 0 64px;
}

.ks-account-overview-inner {
    width: min(980px, 100%);
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    padding: 28px;
    box-shadow: 0 10px 26px rgba(0, 0, 0, 0.08);
}

.ks-account-overview-inner--en {
    margin-inline: auto;
    text-align: left;
}

.ks-account-overview-inner--ar {
    margin-inline: auto;
    text-align: right;
}

.ks-account-overview h1 {
    margin: 0 0 24px;
    color: #111;
    font-size: clamp(24px, 2.3vw, 31px);
    font-weight: 800;
}

.ks-account-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 12px;
}

.ks-account-list li {
    border: 1px solid #ececec;
    background: #fafafa;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    border-radius: 4px;
}

.ks-account-item-index {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #f5c400;
    color: #111;
    font-size: 13px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
}

.ks-account-item-body {
    flex: 1;
}

.ks-account-list h3 {
    margin: 0 0 6px;
    font-size: 17px;
    color: #111;
}

.ks-account-list p {
    margin: 0;
    color: #4a4a4a;
    line-height: 1.55;
    font-size: 14px;
}

.ks-account-title-link {
    color: #111;
    text-decoration: none;
    font: inherit;
    font-weight: 700;
    cursor: pointer;
}

.ks-account-title-link:hover {
    color: #b78f00;
    text-decoration: underline;
}

.ks-account-title-link--btn {
    border: 0;
    background: transparent;
    padding: 0;
}

@media (max-width: 768px) {
    .ks-account-overview-inner {
        padding: 20px;
    }
}
</style>
