<template>
    <section id="contact" class="contact section">
        <SectionTitle :title="data.title" :lead="data.lead" />
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row gy-4">
                <div class="col-lg-5">
                    <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="200">
                        <i class="bi bi-geo-alt flex-shrink-0"></i>
                        <div>
                            <h3>{{ $t(data.address) }}</h3>
                            <p class="contact-person"><strong>{{ $t(data.person) }}</strong></p>
                        </div>
                    </div>
                    <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
                        <i class="bi bi-telephone flex-shrink-0"></i>
                        <div>
                            <h3>Phone</h3>
                            <p><a :href="contact.phoneHref">{{ contact.phone }}</a></p>
                        </div>
                    </div>
                    <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
                        <i class="bi bi-envelope flex-shrink-0"></i>
                        <div>
                            <h3>Email</h3>
                            <p><a :href="`mailto:${contact.email}`">{{ contact.email }}</a></p>
                        </div>
                    </div>
                    <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="500">
                        <i class="bi bi-instagram flex-shrink-0"></i>
                        <div>
                            <h3>Instagram / Telegram</h3>
                            <p>
                                <a :href="contact.instagram.url" target="_blank" rel="noopener">{{ contact.instagram.label }}</a>
                                ·
                                <a :href="contact.telegram.url" target="_blank" rel="noopener">{{ contact.telegram.label }}</a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <form class="php-email-form" data-aos="fade-up" data-aos-delay="200" @submit.prevent="send">
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <input v-model="form.name" type="text" class="form-control" required :placeholder="$t('contact.form.name')" />
                            </div>
                            <div class="col-md-6">
                                <input v-model="form.email" type="email" class="form-control" required :placeholder="$t('contact.form.email')" />
                            </div>
                            <div class="col-md-12">
                                <input v-model="form.subject" type="text" class="form-control" required :placeholder="$t('contact.form.subject')" />
                            </div>
                            <div class="col-md-12">
                                <textarea v-model="form.message" class="form-control" rows="6" required :placeholder="$t('contact.form.message')"></textarea>
                            </div>
                            <div class="col-md-12 text-center">
                                <button type="submit">{{ $t("contact.form.send") }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import site from "../content/site.js";
import SectionTitle from "./SectionTitle.vue";

export default {
    name: "ContactSection",
    components: { SectionTitle },
    props: {
        data: { type: Object, required: true },
    },

    data() {
        return {
            contact: site.contact,
            form: { name: "", email: "", subject: "", message: "" },
        };
    },

    methods: {
        send() {
            const { name, email, subject, message } = this.form;
            const body = `${name} <${email}>\n\n${message}`;
            window.location.href =
                `mailto:${this.contact.email}?subject=${encodeURIComponent(subject || site.name)}` +
                `&body=${encodeURIComponent(body)}`;
        },
    },
};
</script>
