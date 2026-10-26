<template>
    <section id="call-to-action" class="call-to-action section accent-background">
        <div class="container">
            <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
                <div class="col-xl-10">
                    <div class="text-center">
                        <h3>{{ $t(data.title) }}</h3>
                        <p>{{ $t(data.text) }}</p>
                        <SectionLink class="cta-btn" :section="data.button.section">{{ $t(data.button.label) }}</SectionLink>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import SectionLink from "../common/SectionLink.vue";

export default {
    name: "CtaSection",
    components: { SectionLink },
    props: {
        data: { type: Object, required: true },
    },
};
</script>
