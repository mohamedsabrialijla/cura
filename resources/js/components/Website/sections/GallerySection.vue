<template>
    <section id="gallery" class="gallery section light-background">
        <SectionTitle :title="data.title" :lead="data.lead" />
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="album-filters" role="tablist" aria-label="Albums">
                <button type="button" class="album-filter" :class="{ active: active === 'all' }" @click="active = 'all'">
                    {{ $t("gallery.filter.all") }}
                </button>
                <button
                    v-for="album in data.albums"
                    :key="album.key"
                    type="button"
                    class="album-filter"
                    :class="{ active: active === album.key }"
                    @click="active = album.key"
                >
                    {{ $t(album.filter) }}
                </button>
            </div>

            <div v-if="activeAlbum" class="album-meta mb-4">
                <div class="album-card d-block">
                    <h3>{{ $t(activeAlbum.title) }}</h3>
                    <p>{{ $t(activeAlbum.lead) }}</p>
                </div>
            </div>

            <div class="row gy-4 gallery-grid">
                <template v-for="album in data.albums" :key="album.key">
                    <div
                        v-for="photo in album.photos"
                        :key="photo"
                        class="col-lg-3 col-md-4 col-6 album-item"
                        :class="{ 'is-hidden': active !== 'all' && active !== album.key }"
                    >
                        <a :href="photo" class="glightbox gallery-item" :data-gallery="`cura-${album.key}`">
                            <img :src="photo" alt="" loading="lazy" />
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>

<script>
import SectionTitle from "./SectionTitle.vue";

export default {
    name: "GallerySection",
    components: { SectionTitle },
    props: {
        data: { type: Object, required: true },
    },

    data() {
        return { active: "all" };
    },

    computed: {
        activeAlbum() {
            return this.data.albums.find((album) => album.key === this.active) || null;
        },
    },
};
</script>
