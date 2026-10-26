<template>
    <a
        href="#"
        id="scroll-top"
        class="scroll-top d-flex align-items-center justify-content-center"
        :class="{ active: visible }"
        @click.prevent="toTop"
    >
        <i class="bi bi-arrow-up-short"></i>
    </a>
</template>

<script>
export default {
    name: "ScrollTop",

    data() {
        return { visible: false };
    },

    mounted() {
        window.addEventListener("scroll", this.onScroll, { passive: true });
        this.onScroll();
    },

    beforeUnmount() {
        window.removeEventListener("scroll", this.onScroll);
    },

    methods: {
        onScroll() {
            this.visible = window.scrollY > 100;
        },
        toTop() {
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    },
};
</script>
