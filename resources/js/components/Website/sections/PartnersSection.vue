<template>
    <section id="partners" class="services section">
        <SectionTitle :title="data.title" :lead="data.lead" />
        <div class="container">
            <p class="mb-4" data-aos="fade-up">{{ $t(data.text) }}</p>
            <div class="row gy-4 partner-options">
                <div
                    v-for="(option, i) in data.options"
                    :key="option.title"
                    class="col-md-4"
                    data-aos="fade-up"
                    :data-aos-delay="(i + 1) * 100"
                >
                    <div class="icon-box">
                        <i class="mb-3" :class="option.icon" style="color: var(--accent-color); font-size: 28px"></i>
                        <h4>{{ $t(option.title) }}</h4>
                        <p>{{ $t(option.text) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import SectionTitle from "./SectionTitle.vue";

export default {
    name: "PartnersSection",
    components: { SectionTitle },
    props: {
        data: { type: Object, required: true },
    },
};
</script>
