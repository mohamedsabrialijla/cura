<template>
    <section class="ks-auth-page" :lang="pageLang">
        <div class="ks-auth-wrap">
            <h1 class="ks-auth-title">{{ t.accountTitle }}</h1>

            <div class="ks-auth-grid">
                <article class="ks-auth-card">
                    <h2>{{ t.loginTitle }}</h2>
                    <h4 class="ks-auth-kicker">{{ t.loginUser }}</h4>
                    <p class="ks-auth-help">{{ t.loginHelp }}</p>

                    <form class="ks-auth-form" @submit.prevent="login">
                        <label>
                            <span>{{ t.email }}</span>
                            <input
                                v-model.trim="form.email"
                                type="email"
                                autocomplete="email"
                                :placeholder="t.email"
                                required
                            />
                        </label>

                        <label>
                            <span>{{ t.password }}</span>
                            <div class="ks-pass-wrap">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    :placeholder="t.password"
                                    required
                                />
                                <button
                                    type="button"
                                    class="ks-pass-toggle"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? t.hide : t.show"
                                >
                                    {{ showPassword ? t.hide : t.show }}
                                </button>
                            </div>
                        </label>

                        <button class="ks-auth-btn" type="submit" :disabled="isLoading">
                            {{ isLoading ? t.pleaseWait : t.loginCta }}
                        </button>

                        <router-link :to="`/${locale}/forget-password`" class="ks-auth-link">
                            {{ t.forgot }}
                        </router-link>

                        <p v-if="errorMessage" class="ks-auth-error">{{ errorMessage }}</p>
                    </form>
                </article>

                <article class="ks-auth-card ks-auth-card--alt">
                    <h2>{{ t.registerTitle }}</h2>
                    <h3>{{ t.notRegistered }}</h3>
                    <p class="ks-auth-help">{{ t.registerHelp }}</p>
                    <router-link :to="`/${locale}/signUp`" class="ks-auth-btn ks-auth-btn--mini">
                        {{ t.registerCta }}
                    </router-link>
                </article>
            </div>

        </div>

        <div class="ks-auth-newsletter">
            <div class="ks-auth-newsletter-inner">
                <span class="ks-auth-newsletter-label">Newsletter</span>
                <input
                    class="ks-auth-newsletter-input"
                    type="email"
                    :placeholder="t.newsletterPlaceholder"
                />
                <button type="button" class="ks-auth-newsletter-btn">
                    {{ t.loginCta }}
                </button>
            </div>
        </div>
    </section>
</template>

<script>
import { authApi } from "@/axiosUser";

const COPY = {
    en: {
        accountTitle: "IL TUO ACCOUNT KEEPERSPORT",
        loginTitle: "LOGIN",
        loginUser: "LOGIN UTENTE",
        loginHelp: "Inserisci qui il tuo nome utente e la password per accedere al sito web",
        email: "Email",
        password: "Password",
        forgot: "Hai dimenticato la password?",
        loginCta: "LOGIN",
        pleaseWait: "Please wait...",
        registerTitle: "ISCRIVITI",
        notRegistered: "NON SEI ANCORA REGISTRATO?",
        registerHelp: "È possibile creare qui un nuovo account KEEPERsport.",
        registerCta: "REGISTRATI CON KEEPERSPORT",
        newsletterPlaceholder: "Tua E-Mail",
        show: "Show",
        hide: "Hide",
    },
    ar: {
        accountTitle: "حساب KEEPERsport الخاص بك",
        loginTitle: "تسجيل الدخول",
        loginUser: "دخول المستخدم",
        loginHelp: "أدخل البريد الإلكتروني وكلمة المرور للوصول إلى الموقع",
        email: "البريد الإلكتروني",
        password: "كلمة المرور",
        forgot: "هل نسيت كلمة المرور؟",
        loginCta: "دخول",
        pleaseWait: "يرجى الانتظار...",
        registerTitle: "التسجيل",
        notRegistered: "لست مسجلًا بعد؟",
        registerHelp: "يمكنك إنشاء حساب جديد من هنا.",
        registerCta: "سجل مع KEEPERSPORT",
        newsletterPlaceholder: "بريدك الإلكتروني",
        show: "إظهار",
        hide: "إخفاء",
    },
};

export default {
    name: "LoginPage",
    data() {
        return {
            isLoading: false,
            showPassword: false,
            errorMessage: "",
            form: {
                email: "",
                password: "",
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
        async login() {
            this.errorMessage = "";
            this.isLoading = true;
            try {
                const response = await authApi({
                    method: "POST",
                    url: "/auth/login",
                    data: {
                        flag: "email",
                        email_or_username: this.form.email,
                        password: this.form.password,
                    },
                });

                if (response?.data?.code === 200 && response?.data?.items?.token) {
                    localStorage.setItem("authToken", response.data.items.token);
                    localStorage.setItem("token", response.data.items.token);
                    try {
                        await this.$store.dispatch("fetchUser");
                    } catch (_) {
                        // ignore
                    }
                    this.$router.push(`/${this.locale}/account`);
                } else {
                    this.errorMessage = response?.data?.message || "Login failed.";
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
    background: #fff;
    color: #111;
    padding: 22px 12px 0;
    display: flex;
    flex-direction: column;
}

.ks-auth-wrap {
    max-width: 1140px;
    margin: 0 auto;
    width: 100%;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.ks-auth-title {
    margin: 0 0 8px;
    font-size: clamp(28px, 3vw, 48px);
    font-weight: 800;
    letter-spacing: 0.01em;
}

.ks-auth-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 14px;
    margin-bottom: 10px;
}

.ks-auth-card {
    background: transparent;
    border: 0;
    padding: 0;
}

.ks-auth-card h2 {
    margin: 0;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: 0.01em;
}

.ks-auth-card h3 {
    margin: 0 0 8px;
    font-size: 30px;
    font-weight: 700;
}

.ks-auth-kicker {
    margin: 2px 0 6px;
    font-size: 18px;
    font-weight: 800;
}

.ks-auth-help {
    margin: 0 0 12px;
    color: #333;
    line-height: 1.4;
    font-size: 15px;
}

.ks-auth-form label {
    display: block;
    margin-bottom: 8px;
}

.ks-auth-form label > span {
    display: none;
}

.ks-auth-form input {
    width: 100%;
    border: 1px solid #bfbfbf;
    min-height: 48px;
    padding: 9px 12px;
    background: #ebebeb;
    font-size: 15px;
    border-radius: 4px;
}

.ks-pass-wrap {
    position: relative;
}

.ks-pass-wrap input {
    padding-right: 62px;
}

.ks-pass-toggle {
    position: absolute;
    inset-inline-end: 8px;
    top: 50%;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    color: #555;
    font-weight: 700;
    font-size: 12px;
}

.ks-auth-link {
    display: inline-block;
    margin: 10px 0 0;
    color: #4b4b4b;
    text-decoration: none;
    font-size: 14px;
}

.ks-auth-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    min-height: 38px;
    padding: 0 16px;
    border: 0;
    background: var(--ks-brand, #f5c400);
    color: #111;
    font-weight: 800;
    text-decoration: none;
    text-transform: uppercase;
    font-size: 13px;
    margin-top: 10px;
    box-shadow: 0 2px 2px rgba(0, 0, 0, 0.2);
    border-radius: 4px;
}

.ks-auth-btn--mini {
    min-height: 36px;
    padding: 0 14px;
    margin-top: 4px;
}

.ks-auth-btn:disabled {
    opacity: 0.7;
}

.ks-auth-error {
    margin: 12px 0 0;
    color: #b42318;
    font-size: 13px;
    line-height: 1.4;
}

.ks-auth-newsletter {
    width: 100vw;
    margin-inline: calc(50% - 50vw);
    margin-top: auto;
    background: var(--ks-brand, #f5c400);
    border-top: 1px solid #9f9f9f;
    border-bottom: 1px solid #9f9f9f;
    padding: 4px 0;
}

.ks-auth-newsletter-inner {
    display: grid;
    grid-template-columns: 110px minmax(260px, 420px) 170px;
    gap: 8px;
    align-items: center;
    max-width: 1140px;
    margin: 0 auto;
    width: 100%;
    padding: 0 12px;
}

.ks-auth-newsletter-label {
    color: #1b1b1b;
    font-size: 12px;
    font-weight: 600;
}

.ks-auth-newsletter-input {
    width: 100%;
    min-height: 34px;
    border: 1px solid #111;
    background: #1a1a1a;
    color: #fff;
    padding: 6px 10px;
    font-size: 13px;
    border-radius: 4px;
}

.ks-auth-newsletter-btn {
    min-height: 34px;
    border: 1px solid #b8b8b8;
    background: #d9d9d9;
    color: #111;
    font-weight: 700;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
}

@media (max-width: 900px) {
    .ks-auth-grid {
        grid-template-columns: 1fr;
    }

    .ks-auth-newsletter-inner {
        grid-template-columns: 1fr;
        padding: 6px 12px;
    }

    .ks-auth-newsletter-btn {
        width: 100%;
    }
}
</style>

