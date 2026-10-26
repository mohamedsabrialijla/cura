<template>
    <SiteHeader />
    <main class="main">
        <router-view />
    </main>
    <SiteFooter />
    <ScrollTop />
</template>

<script>
import SiteHeader from "./Header.vue";
import SiteFooter from "./Footer.vue";
import ScrollTop from "./ScrollTop.vue";

export default {
    name: "CuraApp",
    components: { SiteHeader, SiteFooter, ScrollTop },

    mounted() {
        document.getElementById("preloader")?.remove();
    },
};
</script>
