<template>
    <div class="ks-legal-page" :lang="pageLang">
        <section class="ks-legal-hero">
            <div class="ks-legal-wrap">
                <h1>{{ pageTitle }}</h1>
                <p>{{ pageSubtitle }}</p>
            </div>
        </section>

        <section class="ks-legal-body">
            <div class="ks-legal-wrap">
                <div v-if="isLoading" class="ks-legal-card ks-legal-muted">
                    {{ pageLang === "ar" ? "جاري التحميل…" : "Loading…" }}
                </div>
                <div
                    v-else-if="currentDescription"
                    class="ks-legal-card ks-legal-content"
                    v-html="currentDescription"
                ></div>
                <p v-else class="ks-legal-card ks-legal-muted">
                    {{ pageLang === "ar" ? "لا يوجد محتوى لهذه الصفحة بعد." : "No content for this page yet." }}
                </p>
            </div>
        </section>

        <section class="ks-legal-cta">
            <div class="ks-legal-wrap ks-legal-cta-inner">
                <h2>{{ pageLang === "ar" ? "هل لديك سؤال؟" : "Have a question?" }}</h2>
                <p>
                    {{
                        pageLang === "ar"
                            ? "فريقنا جاهز لمساعدتك في أي استفسار."
                            : "Our team is ready to help with any inquiry."
                    }}
                </p>
                <router-link :to="`/${locale}/contact-us`" class="ks-legal-btn">
                    {{ pageLang === "ar" ? "تواصل معنا" : "Contact Us" }}
                </router-link>
            </div>
        </section>
    </div>
</template>

<script>
import { api } from "../../../axiosUser";

const PAGE_ID = 5;

export default {
    name: "UsageReturnPolicy",
    data() {
        return {
            isLoading: false,
            items: null,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        pageTitle() {
            const translations = this.items?.translations || [];
            const current = translations.find((row) => row.locale === this.locale);
            if (current?.name) {
                return current.name;
            }
            return this.pageLang === "ar"
                ? "سياسة الاستخدام والاسترجاع"
                : "Usage & Return Policy";
        },
        pageSubtitle() {
            return this.pageLang === "ar"
                ? "تعرف على شروط الاستخدام وسياسة الإرجاع والاستبدال."
                : "Learn about usage terms and our return and exchange policy.";
        },
        currentDescription() {
            if (!this.items || Array.isArray(this.items)) {
                return "";
            }

            const translations = this.items.translations || [];
            if (translations.length) {
                const current = translations.find((row) => row.locale === this.locale);
                if (current?.description) {
                    return current.description;
                }
            }

            return this.items.description || "";
        },
    },
    watch: {
        locale() {
            this.fetchPage();
        },
    },
    mounted() {
        this.fetchPage();
    },
    methods: {
        async fetchPage() {
            this.isLoading = true;
            try {
                const response = await api.get("Pages/getById", {
                    params: { ID: PAGE_ID },
                });
                this.items = response.data?.items ?? null;
            } catch (error) {
                console.error("Usage/return page load failed:", error);
                this.items = null;
            } finally {
                this.isLoading = false;
            }
        },
    },
};
</script>

<style scoped>
.ks-legal-page {
    font-family: Inter, "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    color: #1a1a1a;
    background: #f5f5f5;
}

.ks-legal-wrap {
    width: min(920px, 100%);
    margin: 0 auto;
    padding: 0 20px;
}

.ks-legal-hero {
    background: var(--ks-brand-gradient, linear-gradient(135deg, #f5c400 0%, #e6b800 100%));
    padding: 56px 0 48px;
}

.ks-legal-hero h1 {
    margin: 0 0 12px;
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 800;
    color: #111;
}

.ks-legal-hero p {
    margin: 0;
    max-width: 62ch;
    color: #222;
    line-height: 1.6;
    font-size: 16px;
}

.ks-legal-body {
    padding: 40px 0 48px;
}

.ks-legal-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    padding: 28px 24px;
}

.ks-legal-content :deep(h2),
.ks-legal-content :deep(h3),
.ks-legal-content :deep(h4) {
    color: #111;
    margin-top: 1.4em;
}

.ks-legal-content :deep(p),
.ks-legal-content :deep(li) {
    line-height: 1.7;
    color: #333;
}

.ks-legal-content :deep(a) {
    color: var(--ks-brand-dark, #c9a000);
}

.ks-legal-muted {
    margin: 0;
    color: #666;
    text-align: center;
}

.ks-legal-cta {
    padding: 0 0 56px;
}

.ks-legal-cta-inner {
    text-align: center;
    background: #111;
    color: #f2f2f2;
    border-radius: 8px;
    padding: 32px 24px;
}

.ks-legal-cta-inner h2 {
    margin: 0 0 8px;
    color: var(--ks-brand, #f5c400);
    font-size: 22px;
}

.ks-legal-cta-inner p {
    margin: 0 0 18px;
    color: #ccc;
}

.ks-legal-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 22px;
    background: var(--ks-brand, #f5c400);
    color: #111;
    font-weight: 800;
    text-decoration: none;
    border-radius: 4px;
}

.ks-legal-btn:hover {
    background: var(--ks-brand-dark, #c9a000);
    color: #111;
}
</style>
