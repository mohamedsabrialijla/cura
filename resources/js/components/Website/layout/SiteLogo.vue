<template>
    <img
        v-if="logoUrl"
        :src="logoUrl"
        :alt="alt"
        v-bind="$attrs"
    />
</template>

<script>
import { resolveSiteLogoFromStore } from "@/utils/siteLogo.js";

export default {
    name: "SiteLogo",
    inheritAttrs: false,
    props: {
        alt: {
            type: String,
            default: "KEEPERsport",
        },
    },
    computed: {
        logoUrl() {
            return resolveSiteLogoFromStore(this.$store);
        },
    },
};
</script>
