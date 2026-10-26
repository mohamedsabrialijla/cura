<template>
    <header id="header" class="header sticky-top">
        <div class="topbar d-flex align-items-center">
            <div class="container d-flex justify-content-center justify-content-md-between">
                <div class="d-flex align-items-center palestine-top">
                    <PalestineFlag />
                    <span class="palestine-loc">{{ $t("top.palestine") }}</span>
                    <span class="d-none d-md-inline top-sep" aria-hidden="true">—</span>
                    <i class="bi bi-heart-pulse me-1 d-none d-md-inline"></i>
                    <span class="d-none d-md-inline">{{ $t("top.hours") }}</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-phone me-1"></i> <span>{{ $t("top.phone") }}</span>
                    <div class="lang-switcher" role="group" aria-label="Language">
                        <template v-for="(code, i) in locales" :key="code">
                            <span v-if="i > 0" class="lang-sep">|</span>
                            <button
                                type="button"
                                class="lang-btn"
                                :class="{ active: code === locale }"
                                :aria-pressed="code === locale ? 'true' : 'false'"
                                @click="switchLocale(code)"
                            >
                                {{ code.toUpperCase() }}
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="branding d-flex align-items-center">
            <div class="container position-relative d-flex align-items-center justify-content-end">
                <router-link :to="homePath" class="logo d-flex align-items-center me-auto">
                    <PalestineFlag class="d-md-none" />
                    <img :src="site.logo" :alt="site.name" class="site-logo" />
                </router-link>

                <nav id="navmenu" class="navmenu">
                    <ul>
                        <li v-for="item in site.nav" :key="item.section">
                            <SectionLink
                                :section="item.section"
                                :class="{ active: activeSection === item.section }"
                                @navigate="closeMobileNav"
                            >
                                {{ $t(item.label) }}
                            </SectionLink>
                        </li>
                    </ul>
                    <i
                        class="mobile-nav-toggle d-xl-none bi"
                        :class="mobileOpen ? 'bi-x' : 'bi-list'"
                        @click="toggleMobileNav"
                    ></i>
                </nav>

                <SectionLink class="cta-btn" :section="site.headerCta.section">
                    {{ $t(site.headerCta.label) }}
                </SectionLink>
            </div>
        </div>
        <div class="palestine-stripe" aria-hidden="true"></div>
    </header>
</template>

<script>
import site from "../content/site.js";
import { LOCALES, localePath, resolveLocale } from "../i18n/index.js";
import PalestineFlag from "../common/PalestineFlag.vue";
import SectionLink from "../common/SectionLink.vue";

export default {
    name: "SiteHeader",
    components: { PalestineFlag, SectionLink },

    data() {
        return {
            site,
            locales: LOCALES,
            mobileOpen: false,
            activeSection: "hero",
        };
    },

    computed: {
        locale() {
            return resolveLocale(this.$route);
        },
        homePath() {
            return localePath(this.$route);
        },
    },

    watch: {
        "$route.name"() {
            this.closeMobileNav();
            this.$nextTick(this.onScroll);
        },
    },

    mounted() {
        window.addEventListener("scroll", this.onScroll, { passive: true });
        this.onScroll();
    },

    beforeUnmount() {
        window.removeEventListener("scroll", this.onScroll);
        document.body.classList.remove("mobile-nav-active", "scrolled");
    },

    methods: {
        switchLocale(code) {
            if (code === this.locale) return;
            this.$router.push({
                path: `/${code}`,
                query: this.$route.query,
                hash: this.$route.hash || window.location.hash,
            });
        },

        toggleMobileNav() {
            this.mobileOpen = !this.mobileOpen;
            document.body.classList.toggle("mobile-nav-active", this.mobileOpen);
        },

        closeMobileNav() {
            if (!this.mobileOpen) return;
            this.mobileOpen = false;
            document.body.classList.remove("mobile-nav-active");
        },

        onScroll() {
            document.body.classList.toggle("scrolled", window.scrollY > 100);
            this.updateActiveSection();
        },

        updateActiveSection() {
            if (this.$route.name !== "home") {
                this.activeSection = null;
                return;
            }
            const position = window.scrollY + 200;
            const current = this.site.nav.find(({ section }) => {
                const el = document.getElementById(section);
                return el && position >= el.offsetTop && position <= el.offsetTop + el.offsetHeight;
            });
            if (current) this.activeSection = current.section;
        },
    },
};
</script>
