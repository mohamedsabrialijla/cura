<template>
    <div class="contact-us-container">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="hero-content">
                            <h1 class="hero-title">
                                {{
                                    locale === "ar"
                                        ? "تواصل معنا"
                                        : "Contact Us"
                                }}
                            </h1>
                            <p class="hero-description">
                                {{
                                    locale === "ar"
                                        ? "نحن هنا لمساعدتك في أي استفسار أو طلب. لا تتردد في التواصل معنا وسنكون سعداء بالرد عليك في أقرب وقت ممكن."
                                        : "We're here to help you with any inquiry or request. Don't hesitate to contact us and we'll be happy to get back to you as soon as possible."
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-stats">
                            <div class="stat-item">
                                <div class="stat-number">24/7</div>
                                <div class="stat-label">
                                    {{
                                        locale === "ar"
                                            ? "دعم متواصل"
                                            : "Support"
                                    }}
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">5min</div>
                                <div class="stat-label">
                                    {{
                                        locale === "ar"
                                            ? "وقت الاستجابة"
                                            : "Response Time"
                                    }}
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">98%</div>
                                <div class="stat-label">
                                    {{
                                        locale === "ar"
                                            ? "رضا العملاء"
                                            : "Satisfaction"
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Methods Section -->
        <section class="contact-methods-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2>
                        {{
                            locale === "ar" ? "طرق التواصل" : "Contact Methods"
                        }}
                    </h2>
                    <p>
                        {{
                            locale === "ar"
                                ? "اختر الطريقة التي تناسبك للتواصل معنا"
                                : "Choose the method that suits you to contact us"
                        }}
                    </p>
                </div>
                <div class="row g-4">
                    <div
                        class="col-lg-4 col-md-6"
                        v-for="method in contactMethods"
                        :key="method.id"
                    >
                        <div class="contact-method-card">
                            <div class="method-icon">
                                <i :class="method.icon"></i>
                            </div>
                            <h4>{{ method.title }}</h4>
                            <p>{{ method.description }}</p>
                            <div class="method-details">
                                <div
                                    v-for="(detail, dIdx) in method.details"
                                    :key="dIdx"
                                >
                                    <a
                                        v-if="detail.href"
                                        :href="detail.href"
                                        :target="detail.external ? '_blank' : undefined"
                                        :rel="detail.external ? 'noopener' : undefined"
                                    >{{ detail.text }}</a>
                                    <span v-else>{{ detail.text }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <SocialIconsBar variant="contact" />
            </div>
        </section>

        <!-- Contact Form Section -->
        <section class="contact-form-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="contact-form-card">
                            <div class="form-header text-center">
                                <h3>
                                    {{
                                        locale === "ar"
                                            ? "أرسل لنا رسالة"
                                            : "Send Us a Message"
                                    }}
                                </h3>
                                <p>
                                    {{
                                        locale === "ar"
                                            ? "املأ النموذج أدناه وسنرد عليك في أقرب وقت ممكن"
                                            : "Fill out the form below and we'll get back to you as soon as possible"
                                    }}
                                </p>
                            </div>
                            <form class="contact-form">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">
                                                {{
                                                    locale === "ar"
                                                        ? "الاسم الكامل"
                                                        : "Full Name"
                                                }}
                                            </label>
                                            <input
                                                type="text"
                                                id="name"
                                                v-model="formData.name"
                                                class="form-control"
                                                :placeholder="
                                                    locale === 'ar'
                                                        ? 'أدخل اسمك الكامل'
                                                        : 'Enter your full name'
                                                "
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">
                                                {{
                                                    locale === "ar"
                                                        ? "البريد الإلكتروني"
                                                        : "Email"
                                                }}
                                            </label>
                                            <input
                                                type="email"
                                                id="email"
                                                v-model="formData.email"
                                                class="form-control"
                                                :placeholder="
                                                    locale === 'ar'
                                                        ? 'أدخل بريدك الإلكتروني'
                                                        : 'Enter your email'
                                                "
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mobile">
                                                {{
                                                    locale === "ar"
                                                        ? "رقم الهاتف"
                                                        : "Phone Number"
                                                }}
                                            </label>
                                            <input
                                                type="tel"
                                                id="mobile"
                                                v-model="formData.mobile"
                                                class="form-control"
                                                :placeholder="
                                                    locale === 'ar'
                                                        ? 'أدخل رقم هاتفك'
                                                        : 'Enter your phone number'
                                                "
                                            />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="organization">
                                                {{
                                                    locale === "ar"
                                                        ? "الموضوع"
                                                        : "Subject"
                                                }}
                                            </label>
                                            <input
                                                type="text"
                                                id="organization"
                                                v-model="formData.subject"
                                                class="form-control"
                                                :placeholder="
                                                    locale === 'ar'
                                                        ? 'أدخل عنوان الموضوع'
                                                        : 'Enter subject Title'
                                                "
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="message">
                                                {{
                                                    locale === "ar"
                                                        ? "الرسالة"
                                                        : "Message"
                                                }}
                                            </label>
                                            <textarea
                                                id="message"
                                                v-model="formData.message"
                                                class="form-control"
                                                rows="5"
                                                :placeholder="
                                                    locale === 'ar'
                                                        ? 'اكتب رسالتك هنا...'
                                                        : 'Write your message here...'
                                                "
                                                required
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button
                                            @click="sendMessage"
                                            type="submit"
                                            class="btn btn-primary btn-lg"
                                            :disabled="isLoading"
                                        >
                                            <span v-if="isLoading">
                                                <i
                                                    class="fas fa-spinner fa-spin"
                                                ></i>
                                                {{
                                                    locale === "ar"
                                                        ? "جاري الإرسال..."
                                                        : "Sending..."
                                                }}
                                            </span>
                                            <span v-else>
                                                {{
                                                    locale === "ar"
                                                        ? "إرسال الرسالة"
                                                        : "Send Message"
                                                }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2>
                        {{
                            locale === "ar"
                                ? "الأسئلة الشائعة"
                                : "Frequently Asked Questions"
                        }}
                    </h2>
                    <p>
                        {{
                            locale === "ar"
                                ? "إجابات على الأسئلة الأكثر شيوعاً"
                                : "Answers to the most common questions"
                        }}
                    </p>
                </div>
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="faq-list">
                            <div
                                class="faq-item"
                                v-for="(faq, index) in faqs"
                                :key="index"
                            >
                                <div
                                    class="faq-question"
                                    @click="toggleFaq(index)"
                                >
                                    <h4>{{ faq.question }}</h4>
                                    <i
                                        :class="
                                            activeFaq === index
                                                ? 'fas fa-chevron-up'
                                                : 'fas fa-chevron-down'
                                        "
                                    ></i>
                                </div>
                                <div
                                    class="faq-answer"
                                    :class="{ active: activeFaq === index }"
                                >
                                    <p>{{ faq.answer }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Office Locations Section -->
        <!--  <section class="locations-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2>
                        {{ locale === "ar" ? "مكاتبنا" : "Our Offices" }}
                    </h2>
                    <p>
                        {{
                            locale === "ar"
                                ? "يمكنك زيارة مكاتبنا في المواقع التالية"
                                : "You can visit our offices at the following locations"
                        }}
                    </p>
                </div>
                <div class="row g-4">
                    <div
                        class="col-lg-4 col-md-6"
                        v-for="office in offices"
                        :key="office.id"
                    >
                        <div class="office-card">
                            <div class="office-image">
                                <img :src="office.image" :alt="office.city" />
                            </div>
                            <div class="office-info">
                                <h4>{{ office.city }}</h4>
                                <p class="office-address">
                                    {{ office.address }}
                                </p>
                                <div class="office-details">
                                    <div class="detail-item">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ office.phone }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-envelope"></i>
                                        <span>{{ office.email }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ office.hours }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->
    </div>
</template>

<script>
import axios from "axios";
import { mapActions, mapState } from "vuex";
import { api } from "../../../axiosUser";
import {
    resolveSiteContactFromStore,
    telHref,
    mailtoHref,
    normalizeWhatsappHref,
} from "@/utils/siteContact.js";
import SocialIconsBar from "@/components/Website/common/SocialIconsBar.vue";

export default {
    name: "Contact",
    components: {
        SocialIconsBar,
    },
    data() {
        return {
            isLoading: false,
            activeFaq: null,
            urlMessage: "Contacts/createItem2",
            formData: {
                message: "",
                organization: "",
                email: "",
                mobile: "",
                name: "",
                subject: "",
            },
        };
    },

    mounted() {
        this.$store.dispatch("fetchSetting");
    },

    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },

        setting() {
            return this.$store.getters.getSetting;
        },

        contactInfo() {
            return resolveSiteContactFromStore(this.$store);
        },

        contactMethods() {
            const ar = this.locale === "ar";
            const contact = this.contactInfo;
            const methods = [];

            if (contact.mobiles.length) {
                methods.push({
                    id: "phone",
                    icon: "fas fa-phone",
                    title: ar ? "اتصل بنا" : "Call Us",
                    description: ar
                        ? "اتصل بنا مباشرة على أرقام الهاتف التالية"
                        : "Call us directly on the following phone numbers",
                    details: contact.mobiles.map((phone) => ({
                        text: phone,
                        href: telHref(phone),
                    })),
                });
            }

            if (contact.emails.length) {
                methods.push({
                    id: "email",
                    icon: "fas fa-envelope",
                    title: ar ? "راسلنا" : "Email Us",
                    description: ar
                        ? "أرسل لنا بريد إلكتروني وسنرد عليك في أقرب وقت"
                        : "Send us an email and we'll get back to you as soon as possible",
                    details: contact.emails.map((email) => ({
                        text: email,
                        href: mailtoHref(email),
                    })),
                });
            }

            if (contact.whatsapp) {
                methods.push({
                    id: "whatsapp",
                    icon: "fab fa-whatsapp",
                    title: ar ? "واتساب" : "WhatsApp",
                    description: ar
                        ? "تواصل معنا عبر واتساب للحصول على مساعدة سريعة"
                        : "Reach us on WhatsApp for quick assistance",
                    details: [
                        {
                            text: ar ? "فتح واتساب" : "Open WhatsApp",
                            href: normalizeWhatsappHref(contact.whatsapp),
                            external: true,
                        },
                    ],
                });
            }

            return methods;
        },

        faqs() {
            const ar = this.locale === "ar";
            const contact = this.contactInfo;
            const contactHint = contact.mobile || contact.email
                ? (ar
                    ? `يمكنك التواصل معنا عبر ${contact.mobile ? "الهاتف" : ""}${contact.mobile && contact.email ? " أو " : ""}${contact.email ? "البريد الإلكتروني" : ""}${contact.whatsapp ? " أو واتساب" : ""}.`
                    : `You can contact us via ${contact.mobile ? "phone" : ""}${contact.mobile && contact.email ? ", " : ""}${contact.email ? "email" : ""}${contact.whatsapp ? " or WhatsApp" : ""}.`)
                : (ar
                    ? "يمكنك التواصل معنا عبر صفحة التواصل أو الدردشة المباشرة."
                    : "You can contact us through our contact page or live chat.");

            return [
                {
                    question: ar ? "كيف يمكنني إنشاء حساب جديد؟" : "How can I create a new account?",
                    answer: ar
                        ? "يمكنك إنشاء حساب جديد من خلال النقر على زر 'تسجيل' في أعلى الصفحة وملء النموذج بالمعلومات المطلوبة."
                        : "You can create a new account by clicking the 'Sign Up' button at the top of the page and filling out the form with the required information.",
                },
                {
                    question: ar ? "ما هي طرق الدفع المتاحة؟" : "What payment methods are available?",
                    answer: ar
                        ? "نقبل البطاقات الائتمانية الرئيسية، مدى، فيزا، ماستركارد، والتحويلات البنكية."
                        : "We accept major credit cards, Mada, Visa, Mastercard, and bank transfers.",
                },
                {
                    question: ar ? "كيف يمكنني التواصل مع خدمة العملاء؟" : "How can I contact customer service?",
                    answer: contactHint,
                },
            ];
        },

        t() {
            return this.$store.getters.getTranslations;
        },
    },

    methods: {
        toggleFaq(index) {
            this.activeFaq = this.activeFaq === index ? null : index;
        },

        resetData() {
            this.formData.name = "";
            this.formData.email = "";
            this.formData.mobile = "";
            this.formData.message = "";
            this.formData.subject = "";
        },

        swalFunction(type, text) {
            Swal.fire({
                text: text,
                icon: type,
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            });
        },

        sendMessage() {
            this.isLoading = true;

            axios
                .post(this.urlMessage, this.formData)
                .then((response) => {
                    this.isLoading = false;
                    if (response.data.code == 200) {
                        this.isLoading = false;
                        this.resetData();
                        let message = "Thank You, Message Sent Successfully.";
                        if (this.locale == "ar") {
                            message = "شكرا لتواصلكم , تم الارسال بنجاح .";
                        }
                        this.swalFunction("success", message);
                    } else {
                        let message = "Please Confirm Fill Data Success";

                        if (this.locale == "ar") {
                            message = "يرجى التاكد من البيانات ";
                        }

                        this.isLoading = false;
                        this.swalFunction("info", message);
                    }
                })
                .catch((error) => {
                    let message = "Please Confirm Fill Data Success";

                    if (this.locale == "ar") {
                        message = "يرجى التاكد من البيانات ";
                    }

                    this.isLoading = false;
                    this.swalFunction("info", message);
                });
        },
    },
};
</script>

<style scoped>
.contact-us-container {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #f5c400 0%, #e6b800 100%);
    padding: 80px 0;
    color: #1a2733;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
    color: #1a2733;
}

.hero-description {
    font-size: 1.2rem;
    line-height: 1.8;
    margin-bottom: 2rem;
    color: #2c3e50;
}

.hero-stats {
    display: flex;
    gap: 2rem;
    margin-top: 2rem;
    justify-content: end;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #1a2733;
}

.stat-label {
    font-size: 0.9rem;
    color: #2c3e50;
    margin-top: 0.5rem;
}

.hero-image img {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Contact Methods Section */
.contact-methods-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.section-header {
    margin-bottom: 4rem;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: bold;
    color: #1a2733;
    margin-bottom: 1rem;
}

.section-header p {
    font-size: 1.2rem;
    color: #2c3e50;
}

.contact-method-card {
    background: #fff;
    padding: 2.5rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
    height: 100%;
    transition: transform 0.3s ease;
}

.contact-method-card:hover {
    transform: translateY(-5px);
}

.method-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #f5c400, #e6b800);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.method-icon i {
    font-size: 2rem;
    color: #fff;
}

.contact-method-card h4 {
    font-size: 1.4rem;
    font-weight: bold;
    margin-bottom: 1rem;
    color: #1a2733;
}

.contact-method-card p {
    font-size: 1rem;
    line-height: 1.6;
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.method-details {
    text-align: left;
}

.method-details div {
    padding: 0.5rem 0;
    color: #1a2733;
    font-weight: 500;
}

/* Contact Form Section */
.contact-form-section {
    padding: 80px 0;
    background: #fff;
}

.contact-form-card {
    background: #fff;
    padding: 3rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.form-header {
    margin-bottom: 2rem;
}

.form-header h3 {
    font-size: 2rem;
    font-weight: bold;
    color: #1a2733;
    margin-bottom: 1rem;
}

.form-header p {
    font-size: 1.1rem;
    color: #2c3e50;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #1a2733;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #f5c400;
    box-shadow: 0 0 0 3px rgba(229, 170, 42, 0.1);
}

.btn-primary {
    background: #f5c400;
    border-color: #f5c400;
    color: #1a2733;
    font-weight: 600;
    padding: 12px 30px;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #d49a1a;
    border-color: #d49a1a;
    transform: translateY(-2px);
}

.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* FAQ Section */
.faq-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.faq-list {
    max-width: 800px;
    margin: 0 auto;
}

.faq-item {
    background: #fff;
    border-radius: 15px;
    margin-bottom: 1rem;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.faq-question {
    padding: 1.5rem 2rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.3s ease;
}

.faq-question:hover {
    background-color: #f8f9fa;
}

.faq-question h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a2733;
    margin: 0;
}

.faq-question i {
    color: #f5c400;
    transition: transform 0.3s ease;
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.faq-answer.active {
    max-height: 200px;
}

.faq-answer p {
    padding: 0 2rem 1.5rem;
    margin: 0;
    color: #2c3e50;
    line-height: 1.6;
}

/* Office Locations Section */
.locations-section {
    padding: 80px 0;
    background: #fff;
}

.office-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.office-card:hover {
    transform: translateY(-5px);
}

.office-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.office-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.office-info {
    padding: 2rem;
}

.office-info h4 {
    font-size: 1.4rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #1a2733;
}

.office-address {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.office-details {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    color: #2c3e50;
}

.detail-item i {
    color: #f5c400;
    width: 16px;
}

/* Enhanced Responsive Design */
@media (max-width: 1200px) {
    .container {
        padding: 0 20px;
    }

    .hero-title {
        font-size: 3rem;
    }

    .hero-stats {
        gap: 1.5rem;
    }

    .contact-form-card {
        padding: 2.5rem;
    }
}

@media (max-width: 992px) {
    .container {
        padding: 0 16px;
    }

    .hero-title {
        font-size: 2.8rem;
    }

    .hero-description {
        font-size: 1.1rem;
    }

    .hero-stats {
        gap: 1.2rem;
    }

    .stat-number {
        font-size: 2.5rem;
    }

    .contact-form-card {
        padding: 2.2rem;
    }
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }

    .hero-stats {
        flex-direction: column;
        gap: 1rem;
    }

    .contact-form-card {
        padding: 2rem;
    }

    .faq-question {
        padding: 1rem 1.5rem;
    }

    .faq-answer p {
        padding: 0 1.5rem 1rem;
    }

    .container {
        padding: 0 12px;
    }

    .hero-description {
        font-size: 1rem;
    }

    .stat-number {
        font-size: 2.2rem;
    }
}

@media (max-width: 576px) {
    .container {
        padding: 0 8px;
    }

    .hero-title {
        font-size: 2rem;
    }

    .hero-description {
        font-size: 0.95rem;
    }

    .stat-number {
        font-size: 2rem;
    }

    .stat-label {
        font-size: 0.9rem;
    }

    .contact-form-card {
        padding: 1.5rem;
    }

    .faq-question {
        padding: 0.8rem 1rem;
        font-size: 0.95rem;
    }

    .faq-answer p {
        padding: 0 1rem 0.8rem;
        font-size: 0.9rem;
    }
}

/* RTL Support */
[dir="rtl"] .hero-stats {
    flex-direction: row-reverse;
}

[dir="rtl"] .method-details {
    text-align: right;
}

[dir="rtl"] .detail-item {
    flex-direction: row-reverse;
}
</style>


