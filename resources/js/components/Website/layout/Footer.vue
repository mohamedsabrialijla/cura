<template>
    <footer id="footer" class="footer light-background">
        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-5 col-md-6 footer-about">
                    <router-link :to="homePath" class="logo d-flex align-items-center">
                        <img :src="site.logo" :alt="site.name" class="site-logo" />
                    </router-link>
                    <div class="footer-contact pt-3">
                        <p>{{ $t("footer.about") }}</p>
                        <p class="mt-3"><strong>Phone:</strong> <span>{{ site.contact.phone }}</span></p>
                        <p><strong>Email:</strong> <span>{{ site.contact.email }}</span></p>
                    </div>
                    <div class="social-links d-flex mt-4">
                        <a
                            v-for="social in site.socials"
                            :key="social.label"
                            :href="social.url"
                            :aria-label="social.label"
                            v-bind="social.external ? { target: '_blank', rel: 'noopener' } : {}"
                        >
                            <i class="bi" :class="social.icon"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 footer-links">
                    <h4>{{ $t("footer.links") }}</h4>
                    <ul>
                        <li v-for="link in site.footerLinks" :key="link.section">
                            <SectionLink :section="link.section">{{ $t(link.label) }}</SectionLink>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-3 footer-links">
                    <h4>LEARN • RESEARCH • GROW</h4>
                    <p>{{ $t("footer.designed") }}</p>
                    <div class="palestine-badge mt-3">
                        <PalestineFlag large />
                        <span>{{ $t("footer.palestine") }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="palestine-stripe" aria-hidden="true"></div>
        <div class="container copyright text-center mt-4">
            <p>
                © <span>Copyright</span> <strong class="px-1 sitename">{{ site.name }}</strong>
                <span>{{ $t("footer.copy") }}</span>
            </p>
        </div>
    </footer>
</template>

<script>
import site from "../content/site.js";
import { localePath } from "../i18n/index.js";
import PalestineFlag from "../common/PalestineFlag.vue";
import SectionLink from "../common/SectionLink.vue";

export default {
    name: "SiteFooter",
    components: { PalestineFlag, SectionLink },

    data() {
        return { site };
    },

    computed: {
        homePath() {
            return localePath(this.$route);
        },
    },
};
</script>
