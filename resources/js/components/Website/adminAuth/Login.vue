<template>
    <div>
        <div class="d-flex flex-column flex-root">
            <!--begin::Authentication - Sign-in -->
            <div class="d-flex flex-column flex-lg-row flex-column-fluid ks-admin-auth">
                <!--begin::Body-->
                <div
                    class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1 ks-admin-auth-panel"
                >
                    <!--begin::Form-->
                    <div
                        class="d-flex flex-center flex-column flex-lg-row-fluid"
                    >
                        <!--begin::Wrapper-->
                        <div class="w-lg-500px p-10">
                            <!--begin::Form-->
                            <form
                                class="form w-100"
                                novalidate="novalidate"
                                id="kt_sign_in_form"
                                @submit.prevent="login"
                            >
                                <!--begin::Heading-->
                                <div class="text-center mb-11">
                                    <!--begin::Title-->
                                    <router-link
                                        :to="`/${locale}`"
                                        class="mb-4 d-inline-flex flex-column align-items-center text-decoration-none"
                                    >
                                        <img
                                            alt="CURA"
                                            :src="brandLogo"
                                            class="ks-admin-logo"
                                        />
                                        <span class="ks-admin-brand">CURA</span>
                                    </router-link>

                                    <h1 class="text-gray-900 fw-bolder mb-2">
                                        {{ locale === "ar" ? "دخول الإدارة" : "Admin Sign In" }}
                                    </h1>
                                    <p class="text-muted fs-6 mb-0">
                                        {{ locale === "ar" ? "لوحة تحكم كيورا الطبي" : "CURA Medical Team dashboard" }}
                                    </p>

                                    <!--end::Title-->
                                    <!--begin::Subtitle-->
                                    <!--end::Subtitle=-->
                                </div>
                                <!--begin::Heading-->

                                <!--begin::Separator-->

                                <!--end::Separator-->
                                <!--begin::Input group=-->
                                <div class="fv-row mb-8">
                                    <!--begin::Email-->
                                    <input
                                        type="text"
                                        placeholder="Email"
                                        v-model="formData.email"
                                        name="email"
                                        autocomplete="off"
                                        class="form-control bg-transparent"
                                    />
                                    <!--end::Email-->
                                </div>
                                <!--end::Input group=-->
                                <div class="fv-row mb-3">
                                    <!--begin::Password-->
                                    <input
                                        type="password"
                                        placeholder="Password"
                                        v-model="formData.password"
                                        name="password"
                                        autocomplete="off"
                                        class="form-control bg-transparent"
                                    />
                                    <!--end::Password-->
                                </div>
                                <!--end::Input group=-->
                                <!--begin::Wrapper-->
                                <div
                                    class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8"
                                >
                                    <div></div>
                                    <!--begin::Link-->
                                    <!--end::Link-->
                                </div>
                                <!--end::Wrapper-->
                                <!--begin::Submit button-->
                                <div class="d-grid mb-10">
                                    <button
                                        type="submit"
                                        id="kt_sign_in_submit"
                                        class="btn btn-primary"
                                    >
                                        <!--begin::Indicator label-->
                                        <span class="indicator-label"
                                            >Sign In</span
                                        >
                                        <!--end::Indicator label-->
                                        <!--begin::Indicator progress-->
                                        <span class="indicator-progress"
                                            >Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"
                                            ></span
                                        ></span>
                                        <!--end::Indicator progress-->
                                    </button>
                                </div>
                                <!--end::Submit button-->
                                <!--begin::Sign up-->

                                <!--end::Sign up-->
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Form-->
                    <!--begin::Footer-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Authentication - Sign-in-->
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { mapActions, mapState } from "vuex";

export default {
    data() {
        return {
            errors: [],
            formData: {
                email: null,
                password: null,
            },
        };
    },

    mounted() {
        this.$store.dispatch("fetchSetting");
    },

    computed: {
        locale() {
            return this.$route.params.locale;
        },
        brandLogo() {
            return "/site/assets/img/misbar/logo-transparent.png";
        },
    },

    methods: {
        async login() {
            try {
                let url = window.location.origin;
                const response = await axios.post("admin/login", this.formData);

                if (response.data.code == 200) {
                    const token = response.data.items.token;
                    localStorage.setItem("token", token);
                    localStorage.setItem("authToken", token);
                    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
                    const locale = this.locale || "ar";

                    this.$store.dispatch("fetchUser").then(() => {
                        window.location.assign(`${url}/${locale}/dashboard`);
                    }).catch(() => {
                        window.location.assign(`${url}/${locale}/dashboard`);
                    });
                } else {
                    this.swalFunction("error", response.data.message);
                }

                // this.$router.push('/'+ this.$route.params.locale +'/dashboard').then(() => {
                //     window.location.reload(); // إعادة تحميل الصفحة
                // });
                // this.$router.push('/'+ this.$route.params.locale +'/dashboard');  // تأكد من المسار الصحيح
            } catch (error) {
                this.swalFunction("error", error);
            }
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
    },
};
</script>

<style scoped>
.ks-admin-auth {
    min-height: 100vh;
    background:
        linear-gradient(rgba(255, 255, 255, 0.94), rgba(255, 255, 255, 0.94)),
        url("https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJEQU1fRm9sZGVyXC9XZWJzaG9wXC9CYWNrZ3JvdW5kcy9rZWVwZXJzcG9ydF9iZ19ibGFjay5qcGciLCJlZGl0cyI6W119");
    background-size: cover;
    background-position: center;
}

.ks-admin-auth-panel {
    background: transparent;
}

.ks-admin-logo {
    width: 72px;
    height: 72px;
    object-fit: contain;
}

.ks-admin-brand {
    margin-top: 10px;
    color: #111;
    font-size: 22px;
    font-weight: 900;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

:deep(.btn.btn-primary) {
    background: #efc527;
    border-color: #efc527;
    color: #111;
    font-weight: 800;
}

:deep(.btn.btn-primary:hover),
:deep(.btn.btn-primary:focus) {
    background: #e1b91f;
    border-color: #e1b91f;
    color: #111;
}

:deep(.form-control.bg-transparent) {
    background: #fff !important;
    border-color: #d9d9d9;
}
</style>
