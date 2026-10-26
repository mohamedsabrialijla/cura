<template>
    <section id="featured-services" class="featured-services section">
        <div class="container">
            <div class="row gy-4">
                <div
                    v-for="(item, i) in items"
                    :key="item.title"
                    class="col-xl-3 col-md-6 d-flex"
                    data-aos="fade-up"
                    :data-aos-delay="(i + 1) * 100"
                >
                    <div class="service-item position-relative">
                        <div class="icon"><i class="icon" :class="item.icon"></i></div>
                        <h4><span>{{ $t(item.title) }}</span></h4>
                        <p>{{ $t(item.text) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: "PillarsSection",
    props: {
        items: { type: Array, required: true },
    },
};
</script>
