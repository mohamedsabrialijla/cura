<template>
    <section id="vision" class="about section">
        <SectionTitle :title="data.title" :lead="data.lead" />
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-6 content" data-aos="fade-up">
                    <h3>{{ $t(data.heading) }}</h3>
                    <p>{{ $t(data.text) }}</p>
                </div>
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                    <img :src="data.image" class="img-fluid rounded" alt="" />
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import SectionTitle from "./SectionTitle.vue";

export default {
    name: "VisionSection",
    components: { SectionTitle },
    props: {
        data: { type: Object, required: true },
    },
};
</script>
