<template>
    <section id="about" class="about section">
        <SectionTitle :title="data.title" :lead="data.lead" />
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-6 position-relative align-self-start" data-aos="fade-up" data-aos-delay="100">
                    <img :src="data.video.poster" class="img-fluid rounded about-video-poster" alt="CURA Medical Team" />
                    <a
                        :href="data.video.src"
                        class="glightbox pulsating-play-btn"
                        data-gallery="about-video"
                        data-type="video"
                        aria-label="Play video"
                    ></a>
                </div>
                <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="200">
                    <h3>{{ $t(data.heading) }}</h3>
                    <p v-for="p in data.paragraphs" :key="p">{{ $t(p) }}</p>
                    <ul>
                        <li v-for="point in data.points" :key="point">
                            <i class="bi bi-check2-all"></i> <span>{{ $t(point) }}</span>
                        </li>
                    </ul>
                    <p>{{ $t(data.closing) }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import SectionTitle from "./SectionTitle.vue";

export default {
    name: "AboutSection",
    components: { SectionTitle },
    props: {
        data: { type: Object, required: true },
    },
};
</script>
