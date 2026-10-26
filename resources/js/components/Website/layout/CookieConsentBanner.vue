<template>
    <div
        v-if="visible"
        class="ks-cookie-banner"
        role="dialog"
        aria-live="polite"
        :aria-label="copy.title"
    >
        <div class="ks-cookie-banner-inner">
            <button
                type="button"
                class="ks-cookie-close"
                :aria-label="copy.reject"
                @click="reject"
            >
                ×
            </button>
            <div class="ks-cookie-banner-text">
                <strong>{{ copy.title }}</strong>
                <p>{{ copy.message }}</p>
            </div>
            <div class="ks-cookie-banner-actions">
                <button type="button" class="ks-cookie-btn ks-cookie-btn--primary" @click="accept">
                    {{ copy.accept }}
                </button>
                <button type="button" class="ks-cookie-btn ks-cookie-btn--ghost" @click="reject">
                    {{ copy.reject }}
                </button>
            </div>
            <router-link :to="privacyPath" class="ks-cookie-policy">
                {{ copy.privacy }}
            </router-link>
        </div>
    </div>
</template>

<script>
const STORAGE_KEY = "ks_cookie_consent";
const COOKIE_NAME = "ks_cookie_consent";
const MAX_AGE_SECONDS = 60 * 60 * 24 * 365; // 1 year

const COPY = {
    ar: {
        title: "ملفات تعريف الارتباط",
        message: "نستخدم الكوكيز لتحسين تجربتك وحفظ تفضيلاتك.",
        accept: "قبول",
        reject: "رفض",
        privacy: "سياسة الخصوصية",
    },
    en: {
        title: "Cookies",
        message: "We use cookies to improve your experience and save your preferences.",
        accept: "Accept",
        reject: "Reject",
        privacy: "Privacy policy",
    },
};

function writeConsentCookie(value) {
    const secure = window.location.protocol === "https:" ? ";Secure" : "";
    document.cookie = `${COOKIE_NAME}=${encodeURIComponent(value)};path=/;max-age=${MAX_AGE_SECONDS};SameSite=Lax${secure}`;
}

function readConsent() {
    try {
        const fromStorage = localStorage.getItem(STORAGE_KEY);
        if (fromStorage === "accepted" || fromStorage === "rejected") {
            return fromStorage;
        }
    } catch (_) {
        /* ignore */
    }

    const match = document.cookie.match(/(?:^|;\s*)ks_cookie_consent=([^;]+)/);
    if (!match) return null;
    const value = decodeURIComponent(match[1] || "");
    return value === "accepted" || value === "rejected" ? value : null;
}

function saveConsent(value) {
    try {
        localStorage.setItem(STORAGE_KEY, value);
    } catch (_) {
        /* ignore */
    }
    writeConsentCookie(value);
}

export default {
    name: "CookieConsentBanner",
    data() {
        return {
            visible: false,
        };
    },
    computed: {
        locale() {
            return this.$route?.params?.locale || localStorage.getItem("locale") || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        copy() {
            return COPY[this.pageLang] || COPY.en;
        },
        privacyPath() {
            return `/${this.locale}/privacy`;
        },
    },
    mounted() {
        this.visible = !readConsent();
    },
    methods: {
        accept() {
            saveConsent("accepted");
            this.visible = false;
            window.dispatchEvent(new CustomEvent("ks-cookie-consent", { detail: { value: "accepted" } }));
        },
        reject() {
            saveConsent("rejected");
            this.visible = false;
            window.dispatchEvent(new CustomEvent("ks-cookie-consent", { detail: { value: "rejected" } }));
        },
    },
};
</script>

<style scoped>
.ks-cookie-banner {
    position: fixed;
    inset-inline-start: 16px;
    bottom: 16px;
    z-index: 80;
    width: min(320px, calc(100vw - 32px));
    pointer-events: none;
}

.ks-cookie-banner-inner {
    pointer-events: auto;
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 16px 18px 14px;
    background: #111;
    color: #f2f2f2;
    border: 1px solid #2a2a2a;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
}

.ks-cookie-close {
    position: absolute;
    top: 6px;
    inset-inline-end: 8px;
    appearance: none;
    border: 0;
    background: transparent;
    color: #999;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    padding: 4px 6px;
}

.ks-cookie-close:hover {
    color: #fff;
}

.ks-cookie-banner-text {
    padding-inline-end: 18px;
}

.ks-cookie-banner-text strong {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    color: #f5c400;
}

.ks-cookie-banner-text p {
    margin: 0;
    font-size: 12px;
    line-height: 1.5;
    color: #d0d0d0;
}

.ks-cookie-banner-actions {
    display: flex;
    gap: 8px;
}

.ks-cookie-btn {
    appearance: none;
    border: 0;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    line-height: 1.2;
    flex: 1 1 auto;
    text-align: center;
}

.ks-cookie-btn--primary {
    background: #f5c400;
    color: #111;
}

.ks-cookie-btn--primary:hover {
    background: #ffd21f;
}

.ks-cookie-btn--ghost {
    background: transparent;
    color: #eee;
    border: 1px solid #444;
}

.ks-cookie-btn--ghost:hover {
    border-color: #777;
}

.ks-cookie-policy {
    font-size: 11px;
    color: #aaa;
    text-decoration: underline;
}

.ks-cookie-policy:hover {
    color: #f5c400;
}

@media (max-width: 640px) {
    .ks-cookie-banner {
        inset-inline-start: 12px;
        bottom: 12px;
        width: min(280px, calc(100vw - 24px));
    }
}
</style>
