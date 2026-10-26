<template>
    <section class="ks-auth-page" :lang="pageLang">
        <div class="ks-auth-wrap">
            <h1 class="ks-auth-title">{{ t.registerTitle }}</h1>
            <p class="ks-auth-sub">{{ t.registerHelp }}</p>

            <div class="ks-auth-card">
                <form class="ks-auth-form" @submit.prevent="register">
                    <label>
                        <span>{{ t.name }}</span>
                        <input v-model.trim="form.name" type="text" required />
                    </label>

                    <label>
                        <span>{{ t.email }}</span>
                        <input v-model.trim="form.email" type="email" required />
                    </label>

                    <label class="ks-auth-mobile-field">
                        <span>{{ t.mobile }}</span>
                        <MobileFlag
                            v-model="form.mobile"
                            initial-country="sa"
                            :placeholder="t.mobilePh"
                        />
                    </label>

                    <label>
                        <span>{{ t.password }}</span>
                        <input v-model="form.password" type="password" required />
                    </label>

                    <label>
                        <span>{{ t.confirmPassword }}</span>
                        <input v-model="form.confirmPassword" type="password" required />
                    </label>

                    <label class="ks-check">
                        <input v-model="form.terms" type="checkbox" />
                        <span>{{ t.acceptTerms }}</span>
                    </label>

                    <label class="ks-check">
                        <input v-model="form.newsletter" type="checkbox" />
                        <span>{{ t.newsletterOptIn }}</span>
                    </label>

                    <button class="ks-auth-btn" type="submit" :disabled="isLoading">
                        {{ isLoading ? t.pleaseWait : t.registerCta }}
                    </button>

                    <p v-if="errorMessage" class="ks-auth-error">{{ errorMessage }}</p>
                </form>

                <p class="ks-auth-bottom">
                    {{ t.haveAccount }}
                    <router-link :to="`/${locale}/login`">{{ t.login }}</router-link>
                </p>
            </div>
        </div>
    </section>
</template>

<script>
import { api } from "@/axiosUser";
import MobileFlag from "./MobileFlag.vue";

const COPY = {
    en: {
        registerTitle: "Create your account",
        registerHelp: "Register with KEEPERsport in just a few steps.",
        name: "Name",
        email: "Email",
        mobile: "Mobile number",
        mobilePh: "5xxxxxxxx",
        password: "Password",
        confirmPassword: "Confirm password",
        acceptTerms: "I accept terms and conditions",
        newsletterOptIn: "Yes, I want to receive the newsletter",
        registerCta: "Register with KEEPERsport",
        haveAccount: "Already have an account?",
        login: "Login",
        pleaseWait: "Please wait...",
        passMismatch: "Passwords do not match.",
        mobileRequired: "Mobile number is required.",
        mobileInvalid: "Please enter a valid mobile number.",
    },
    ar: {
        registerTitle: "إنشاء حساب جديد",
        registerHelp: "سجّل مع KEEPERsport بخطوات بسيطة.",
        name: "الاسم",
        email: "البريد الإلكتروني",
        mobile: "رقم الجوال",
        mobilePh: "5xxxxxxxx",
        password: "كلمة المرور",
        confirmPassword: "تأكيد كلمة المرور",
        acceptTerms: "أوافق على الشروط والأحكام",
        newsletterOptIn: "نعم، أريد استلام النشرة البريدية",
        registerCta: "التسجيل مع KEEPERsport",
        haveAccount: "لديك حساب بالفعل؟",
        login: "تسجيل الدخول",
        pleaseWait: "يرجى الانتظار...",
        passMismatch: "كلمتا المرور غير متطابقتين.",
        mobileRequired: "رقم الجوال مطلوب.",
        mobileInvalid: "يرجى إدخال رقم جوال صحيح.",
    },
};

export default {
    name: "RegisterPage",
    components: { MobileFlag },
    data() {
        return {
            isLoading: false,
            errorMessage: "",
            form: {
                name: "",
                email: "",
                mobile: "",
                password: "",
                confirmPassword: "",
                terms: false,
                newsletter: false,
            },
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        t() {
            return COPY[this.pageLang] || COPY.en;
        },
    },
    methods: {
        isMobileValid() {
            const digits = String(this.form.mobile || "").replace(/\D/g, "");
            return digits.length >= 8 && digits.length <= 15;
        },
        async register() {
            this.errorMessage = "";
            if (this.form.password !== this.form.confirmPassword) {
                this.errorMessage = this.t.passMismatch;
                return;
            }
            if (!String(this.form.mobile || "").trim()) {
                this.errorMessage = this.t.mobileRequired;
                return;
            }
            if (!this.isMobileValid()) {
                this.errorMessage = this.t.mobileInvalid;
                return;
            }
            this.isLoading = true;
            try {
                const response = await api.post("auth/register", this.form);
                if (response?.data?.code === 200 && response?.data?.items?.token) {
                    localStorage.setItem("authToken", response.data.items.token);
                    localStorage.setItem("token", response.data.items.token);
                    try {
                        await this.$store.dispatch("fetchUser");
                    } catch (_) {
                        // ignore
                    }
                    this.$router.push(`/${this.locale}/account`);
                } else if (response?.data?.code === 200) {
                    this.$router.push(`/${this.locale}/login`);
                } else {
                    this.errorMessage =
                        response?.data?.message || "Registration failed.";
                }
            } catch (error) {
                this.errorMessage =
                    error?.response?.data?.message || "Unexpected error.";
            } finally {
                this.isLoading = false;
            }
        },
    },
};
</script>

<style scoped>
.ks-auth-page {
    min-height: 100vh;
    background: #f6f6f6;
    color: #111;
    padding: 56px 18px;
}

.ks-auth-wrap {
    max-width: 760px;
    margin: 0 auto;
}

.ks-auth-title {
    margin: 0 0 8px;
    font-size: clamp(24px, 2.6vw, 34px);
    font-weight: 800;
}

.ks-auth-sub {
    margin: 0 0 18px;
    color: #666;
}

.ks-auth-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    padding: 24px;
}

.ks-auth-form label {
    display: block;
    margin-bottom: 12px;
}

.ks-auth-form label > span {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
}

.ks-auth-mobile-field {
    overflow: visible;
}

.ks-auth-form input[type="text"],
.ks-auth-form input[type="email"],
.ks-auth-form input[type="password"],
.ks-auth-form input[type="tel"] {
    width: 100%;
    border: 1px solid #ccc;
    min-height: 44px;
    padding: 10px 12px;
}

.ks-check {
    display: flex !important;
    gap: 8px;
    align-items: center;
}

.ks-auth-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 44px;
    padding: 10px 16px;
    border: 0;
    background: var(--ks-brand, #f5c400);
    color: #111;
    font-weight: 800;
}

.ks-auth-bottom {
    margin: 18px 0 0;
}

.ks-auth-bottom a {
    color: #111;
    font-weight: 700;
}

.ks-auth-error {
    margin: 12px 0 0;
    color: #b42318;
    font-size: 13px;
    line-height: 1.4;
}
</style>
