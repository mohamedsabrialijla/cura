<template>
    <section id="plan" class="call-to-action section plan-section">
        <div class="container">
            <div class="row justify-content-center" data-aos="fade-up">
                <div class="col-xl-10 text-center">
                    <h3>{{ $t(data.title) }}</h3>
                    <p>{{ $t(data.lead) }}</p>
                    <p>{{ $t(data.text) }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: "PlanSection",
    props: {
        data: { type: Object, required: true },
    },
};
</script>
