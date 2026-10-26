<template>
    <section id="story" class="features section light-background">
        <SectionTitle :title="data.title" :lead="data.lead" />
        <div class="container">
            <div class="row justify-content-around gy-4">
                <div class="features-image col-lg-6" data-aos="fade-up" data-aos-delay="100">
                    <img :src="data.image" alt="" />
                </div>
                <div class="col-lg-5 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
                    <h3>{{ $t(data.heading) }}</h3>
                    <p>{{ $t(data.text) }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import SectionTitle from "./SectionTitle.vue";

export default {
    name: "StorySection",
    components: { SectionTitle },
    props: {
        data: { type: Object, required: true },
    },
};
</script>
