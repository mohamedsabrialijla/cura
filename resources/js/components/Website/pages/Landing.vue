<template>
    <HeroSection :data="home.hero" />
    <PillarsSection :items="home.pillars" />
    <CtaSection :data="home.cta" />
    <AboutSection :data="home.about" />
    <ServicesSection :data="home.services" />
    <StorySection :data="home.story" />
    <VisionSection :data="home.vision" />
    <PlanSection :data="home.plan" />
    <PartnersSection :data="home.partners" />
    <GallerySection :data="home.gallery" />
    <ContactSection :data="home.contact" />
</template>

<script>
import home from "../content/home.js";
import { scrollToSection } from "../common/scroll.js";
import HeroSection from "../sections/HeroSection.vue";
import PillarsSection from "../sections/PillarsSection.vue";
import CtaSection from "../sections/CtaSection.vue";
import AboutSection from "../sections/AboutSection.vue";
import ServicesSection from "../sections/ServicesSection.vue";
import StorySection from "../sections/StorySection.vue";
import VisionSection from "../sections/VisionSection.vue";
import PlanSection from "../sections/PlanSection.vue";
import PartnersSection from "../sections/PartnersSection.vue";
import GallerySection from "../sections/GallerySection.vue";
import ContactSection from "../sections/ContactSection.vue";

export default {
    name: "LandingPage",
    components: {
        HeroSection,
        PillarsSection,
        CtaSection,
        AboutSection,
        ServicesSection,
        StorySection,
        VisionSection,
        PlanSection,
        PartnersSection,
        GallerySection,
        ContactSection,
    },

    data() {
        return { home };
    },

    mounted() {
        this.lightbox = window.GLightbox?.({ selector: ".glightbox" });

        const hash = this.$route.hash.replace("#", "");
        if (hash) setTimeout(() => scrollToSection(hash), 100);
    },

    beforeUnmount() {
        this.lightbox?.destroy();
    },
};
</script>
