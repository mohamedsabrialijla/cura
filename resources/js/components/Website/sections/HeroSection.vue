<template>
    <section id="hero" class="hero section">
        <div id="hero-carousel" ref="carousel" class="carousel slide carousel-fade">
            <div v-for="(slide, i) in data.slides" :key="i" class="carousel-item" :class="i === 0 ? 'active' : null">
                <img :src="slide.image" alt="" />
                <div class="container">
                    <div v-if="slide.chip" class="hero-palestine-chip" data-aos="fade-up">
                        <PalestineFlag />
                        <span>{{ $t(slide.chip) }}</span>
                    </div>
                    <h2>{{ $t(slide.title) }}</h2>
                    <p>{{ $t(slide.text) }}</p>
                    <SectionLink v-if="slide.button" class="btn-get-started" :section="slide.button.section">
                        {{ $t(slide.button.label) }}
                    </SectionLink>
                </div>
            </div>

            <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-target="#hero-carousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
            </a>
            <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-target="#hero-carousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
            </a>
            <ol class="carousel-indicators">
                <li
                    v-for="(slide, i) in data.slides"
                    :key="i"
                    data-bs-target="#hero-carousel"
                    :data-bs-slide-to="i"
                    :class="i === 0 ? 'active' : null"
                ></li>
            </ol>
        </div>
    </section>
</template>

<script>
import PalestineFlag from "../common/PalestineFlag.vue";
import SectionLink from "../common/SectionLink.vue";

export default {
    name: "HeroSection",
    components: { PalestineFlag, SectionLink },
    props: {
        data: { type: Object, required: true },
    },

    mounted() {
        this.carousel = window.bootstrap?.Carousel.getOrCreateInstance(this.$refs.carousel, {
            interval: this.data.interval,
            ride: "carousel",
        });
    },

    beforeUnmount() {
        this.carousel?.dispose();
    },
};
</script>
