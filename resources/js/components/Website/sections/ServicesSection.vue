<template>
    <section id="services" class="services section">
        <SectionTitle :title="data.title" :lead="data.lead" />
        <div class="container">
            <div class="row gy-4">
                <div
                    v-for="(item, i) in data.items"
                    :key="item.title"
                    class="col-lg-6"
                    data-aos="fade-up"
                    :data-aos-delay="(i + 1) * 100"
                >
                    <div class="service-item position-relative">
                        <div class="icon"><i :class="item.icon"></i></div>
                        <h3>{{ $t(item.title) }}</h3>
                        <p>{{ $t(item.text) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import SectionTitle from "./SectionTitle.vue";

export default {
    name: "ServicesSection",
    components: { SectionTitle },
    props: {
        data: { type: Object, required: true },
    },
};
</script>
