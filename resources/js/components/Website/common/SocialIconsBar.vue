<template>
    <div v-if="links.length" class="ks-social-bar" :class="`ks-social-bar--${variant}`">
        <p v-if="showTitle" class="ks-social-bar__title">{{ titleText }}</p>
        <div class="ks-social-bar__icons" role="list">
            <a
                v-for="item in links"
                :key="item.id"
                :href="item.href"
                class="ks-social-btn"
                :class="'ks-social-btn--' + item.id"
                target="_blank"
                rel="noopener noreferrer"
                :aria-label="item.label"
                role="listitem"
            >
                <span class="ks-social-btn__icon" aria-hidden="true" v-html="socialIconSvg(item.id)"></span>
            </a>
        </div>
    </div>
</template>

<script>
import { resolveSocialLinksFromStore, socialIconSvg } from "@/utils/siteSocial.js";

export default {
    name: "SocialIconsBar",
    props: {
        variant: {
            type: String,
            default: "footer",
        },
        showTitle: {
            type: Boolean,
            default: true,
        },
        title: {
            type: String,
            default: "",
        },
    },
    computed: {
        locale() {
            return this.$route?.params?.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        links() {
            return resolveSocialLinksFromStore(this.$store, this.pageLang);
        },
        titleText() {
            if (this.title) {
                return this.title;
            }
            return this.pageLang === "ar" ? "تابعنا" : "Follow us";
        },
    },
    methods: {
        socialIconSvg,
    },
};
</script>

<style scoped>
.ks-social-bar--footer {
    margin-top: 8px;
    padding-top: 28px;
    border-top: 1px solid #1f1f1f;
    text-align: center;
}

.ks-social-bar--contact {
    margin-top: 32px;
    padding: 28px 24px;
    border: 1px solid #2a2a2a;
    border-radius: 12px;
    background: #0d0d0d;
    text-align: center;
}

.ks-social-bar--inline {
    margin-top: 0;
    padding-top: 0;
    border-top: none;
    text-align: start;
}

.ks-social-bar__title {
    margin: 0 0 14px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #f5c400;
}

.ks-social-bar--contact .ks-social-bar__title {
    font-size: 13px;
    letter-spacing: 0.08em;
}

.ks-social-bar__icons {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.ks-social-bar--inline .ks-social-bar__icons {
    justify-content: flex-start;
}

.ks-social-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid #333;
    background: #111;
    color: #f2f2f2;
    text-decoration: none;
    transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
}

.ks-social-btn:hover {
    transform: translateY(-2px);
    border-color: #f5c400;
    background: #1a1a1a;
    color: #f5c400;
    box-shadow: 0 8px 20px rgba(245, 196, 0, 0.15);
}

.ks-social-btn__icon {
    display: inline-flex;
    width: 22px;
    height: 22px;
}

.ks-social-btn__icon :deep(svg) {
    width: 22px;
    height: 22px;
    display: block;
}

.ks-social-btn--instagram:hover {
    color: #f77737;
    border-color: #f77737;
}

.ks-social-btn--facebook:hover {
    color: #4c8dff;
    border-color: #4c8dff;
}

.ks-social-btn--tiktok:hover {
    color: #ff3b5c;
    border-color: #ff3b5c;
}

.ks-social-btn--snapchat:hover {
    color: #111;
    background: #fffc00;
    border-color: #fffc00;
}
</style>
