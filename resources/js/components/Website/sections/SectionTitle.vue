<template>
    <div class="container section-title" data-aos="fade-up">
        <h2>{{ $t(title) }}</h2>
        <p v-if="lead">{{ $t(lead) }}</p>
    </div>
</template>

<script>
export default {
    name: "SectionTitle",
    props: {
        title: { type: String, required: true },
        lead: { type: String, default: "" },
    },
};
</script>
