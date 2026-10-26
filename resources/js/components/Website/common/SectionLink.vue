<template>
    <a :href="`#${section}`" @click.prevent="go">
        <slot />
    </a>
</template>

<script>
import { goToSection } from "./scroll.js";

export default {
    name: "SectionLink",
    props: {
        section: { type: String, required: true },
    },
    emits: ["navigate"],
    methods: {
        go() {
            goToSection(this.$router, this.$route, this.section);
            this.$emit("navigate", this.section);
        },
    },
};
</script>
