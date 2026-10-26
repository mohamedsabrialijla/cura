<template>
    <section class="ks-newsletter" :lang="pageLang">
        <div class="ks-container ks-newsletter-inner">
            <span class="ks-newsletter-tag">{{ copy.newsletter_title }}</span>
            <form
                class="ks-newsletter-form"
                :action="`${newsletterBase}/servizio-e-aiuto/newsletter-1.html`"
                method="get"
                target="_blank"
                rel="noopener"
            >
                <input
                    type="email"
                    name="email"
                    :placeholder="copy.newsletter_placeholder"
                    autocomplete="email"
                />
                <button type="submit">{{ copy.newsletter_submit }}</button>
            </form>
        </div>
    </section>
</template>

<script>
import { LANDING_COPY } from "@/constants/landingI18n.js";

export default {
    name: "NewsletterBar",
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        copy() {
            const lang = this.locale === "ar" ? "ar" : "en";
            return LANDING_COPY[lang] || LANDING_COPY.en;
        },
        newsletterBase() {
            return typeof window !== "undefined" ? window.location.origin : "";
        },
    },
};
</script>

<style scoped>
.ks-newsletter {
    background: #f5c400;
    padding: 8px 0;
    border-top: 0;
    border-bottom: 0;
}

.ks-newsletter-inner {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 10px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 16px;
    box-sizing: border-box;
}

.ks-newsletter-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 18px;
    padding: 0 6px;
    color: #000;
    font-size: 11px;
    line-height: 1;
    border-radius: 2px;
    white-space: nowrap;
    font-weight: 800;
}

.ks-newsletter-form {
    display: flex;
    flex-wrap: nowrap;
    gap: 6px;
    width: min(100%, 640px);
    justify-content: center;
    margin: 0 auto;
}

.ks-newsletter-form input {
    flex: 1 1 auto;
    min-width: 0;
    height: 30px;
    padding: 0 12px;
    border: 1px solid #1f1f1f;
    background: #111;
    color: #eee;
    font-size: 13px;
    border-radius: 0;
}

.ks-newsletter-form input::placeholder {
    color: #8f8f8f;
}

.ks-newsletter-form input:focus {
    outline: none;
    border-color: #f5c400;
    box-shadow: 0 0 0 1px #f5c400;
}

.ks-newsletter-form button {
    height: 30px;
    min-width: 200px;
    padding: 0 22px;
    background: #d9d9d9;
    color: #111;
    border: 1px solid #bdbdbd;
    font-weight: 800;
    font-size: 12px;
    letter-spacing: 0.04em;
    cursor: pointer;
    border-radius: 0;
    text-transform: uppercase;
}

.ks-newsletter-form button:hover {
    filter: brightness(1.03);
}

@media (max-width: 900px) {
    .ks-newsletter-inner {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    .ks-newsletter-tag {
        align-self: flex-start;
    }

    .ks-newsletter-form {
        width: 100%;
    }

    .ks-newsletter-form button {
        min-width: 140px;
    }
}
</style>
