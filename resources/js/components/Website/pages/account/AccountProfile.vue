<template>
    <div class="ks-account-panel">
        <header class="ks-account-panel-head">
            <h1>{{ t.profile.title }}</h1>
            <p>{{ t.profile.subtitle }}</p>
        </header>

        <form class="ks-account-profile-form" @submit.prevent="saveProfile">
            <div class="ks-account-profile-photo">
                <div class="ks-account-profile-photo-preview">
                    <img :src="photoPreview" :alt="t.profile.photo" />
                </div>
                <div>
                    <p class="ks-account-profile-photo-label">{{ t.profile.photo }}</p>
                    <label class="ks-account-btn ks-account-btn--ghost ks-account-profile-upload">
                        {{ t.profile.choosePhoto }}
                        <input type="file" accept="image/*" class="d-none" @change="onPhotoChange" />
                    </label>
                </div>
            </div>

            <div class="ks-account-form-grid">
                <label class="ks-account-field">
                    <span>{{ t.profile.name }}</span>
                    <input v-model.trim="form.name" type="text" class="ks-account-input" required />
                </label>
                <label class="ks-account-field">
                    <span>{{ t.profile.email }}</span>
                    <input v-model.trim="form.email" type="email" class="ks-account-input" required />
                </label>
                <label class="ks-account-field ks-account-mobile-field">
                    <span>{{ t.profile.mobile }}</span>
                    <MobileFlag
                        v-if="mobileFieldReady"
                        :key="mobileFieldKey"
                        v-model="form.mobile"
                        :items="countries"
                        :initial-country="mobileInitialCountry"
                        :placeholder="t.profile.mobilePh"
                    />
                    <input
                        v-else
                        type="tel"
                        class="ks-account-input"
                        :value="form.mobile"
                        disabled
                        :placeholder="t.profile.mobilePh"
                    />
                </label>
            </div>

            <p v-if="errorMessage" class="ks-account-error">{{ errorMessage }}</p>
            <p v-if="successMessage" class="ks-account-success">{{ successMessage }}</p>

            <div class="ks-account-form-actions">
                <button type="submit" class="ks-account-btn" :disabled="saving">
                    {{ saving ? t.profile.saving : t.profile.save }}
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import { authApi } from "@/axiosUser";
import { accountCopy } from "@/constants/accountI18n.js";
import MobileFlag from "@/components/Website/auth/MobileFlag.vue";

const DEFAULT_AVATAR = "/assets/media/svg/avatars/blank.svg";

export default {
    name: "AccountProfile",
    components: { MobileFlag },
    data() {
        return {
            form: {
                name: "",
                email: "",
                mobile: "",
            },
            countries: [],
            userHydrated: false,
            mobileFieldKey: 0,
            photoFile: null,
            photoPreview: DEFAULT_AVATAR,
            saving: false,
            errorMessage: "",
            successMessage: "",
        };
    },
    computed: {
        ...mapGetters(["getUser"]),
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        t() {
            return accountCopy(this.pageLang);
        },
        mobileInitialCountry() {
            const countryId = this.getUser?.country_id;
            if (countryId && this.countries.length) {
                const match = this.countries.find((country) => Number(country.id) === Number(countryId));
                if (match?.slug) {
                    return String(match.slug).toLowerCase();
                }
            }
            return "sa";
        },
        mobileFieldReady() {
            return this.userHydrated;
        },
    },
    watch: {
        getUser: {
            immediate: true,
            handler(user) {
                if (!user) return;
                this.applyUser(user);
            },
        },
    },
    async mounted() {
        this.hydrateUserFromCache();
        await this.loadCountries();
        if (!this.getUser) {
            await this.$store.dispatch("fetchUser");
        }
        if (this.getUser) {
            this.applyUser(this.getUser);
        }
    },
    methods: {
        hydrateUserFromCache() {
            if (this.getUser) return;
            try {
                const cached = localStorage.getItem("user");
                if (!cached) return;
                const user = JSON.parse(cached);
                if (user && typeof user === "object") {
                    this.$store.commit("setUser", user);
                }
            } catch (_) {
                /* ignore invalid cache */
            }
        },
        async loadCountries() {
            try {
                const { data } = await authApi.get("basic/getCountries");
                this.countries = Array.isArray(data?.items) ? data.items : [];
            } catch (error) {
                console.error("Failed to load countries:", error);
                this.countries = [];
            }
        },
        applyUser(user) {
            this.form.name = user.name || "";
            this.form.email = user.email || "";
            this.form.mobile = String(user.mobile || "").replace(/\D/g, "");
            this.photoPreview = user.logo || DEFAULT_AVATAR;
            this.userHydrated = true;
            this.mobileFieldKey += 1;
        },
        isMobileValid() {
            const digits = String(this.form.mobile || "").replace(/\D/g, "");
            return digits.length >= 8 && digits.length <= 15;
        },
        onPhotoChange(event) {
            const file = event.target.files?.[0];
            this.photoFile = file || null;
            if (file) {
                this.photoPreview = URL.createObjectURL(file);
            }
        },
        async saveProfile() {
            this.errorMessage = "";
            this.successMessage = "";

            if (!String(this.form.mobile || "").trim()) {
                this.errorMessage = this.t.profile.mobileRequired;
                return;
            }
            if (!this.isMobileValid()) {
                this.errorMessage = this.t.profile.mobileInvalid;
                return;
            }

            this.saving = true;
            try {
                const formData = new FormData();
                formData.append("name", this.form.name);
                formData.append("email", this.form.email);
                formData.append("mobile2", this.form.mobile);
                if (this.photoFile) {
                    formData.append("logo", this.photoFile);
                }

                const { data } = await authApi({
                    method: "post",
                    url: "auth/editInfo",
                    data: formData,
                });

                if (data?.status === false || Number(data?.code) !== 200) {
                    const msg =
                        (typeof data?.message === "string" && data.message.replace(/,\s*$/, "")) ||
                        this.t.profile.error;
                    this.errorMessage = msg;
                    return;
                }

                const updated = data.items || data.item || null;
                if (updated) {
                    const userPayload = { ...updated, userType: "user" };
                    this.$store.commit("setUser", userPayload);
                    localStorage.setItem("user", JSON.stringify(userPayload));
                    this.applyUser(userPayload);
                }
                this.photoFile = null;
                this.successMessage = data.message || this.t.profile.saved;
            } catch (error) {
                console.error("Profile update failed:", error);
                const apiMessage = error?.response?.data?.message;
                this.errorMessage =
                    (typeof apiMessage === "string" && apiMessage.replace(/,\s*$/, "")) ||
                    error?.message ||
                    this.t.profile.error;
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<style scoped>
.ks-account-panel {
    background: #fff;
    border: 1px solid #e6e6e6;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.ks-account-panel-head h1 {
    margin: 0 0 6px;
    font-size: clamp(22px, 2.5vw, 30px);
    font-weight: 800;
}

.ks-account-panel-head p {
    margin: 0 0 20px;
    color: #555;
    font-size: 14px;
}

.ks-account-profile-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ks-account-profile-photo {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border: 1px solid #efefef;
    border-radius: 8px;
    background: #fafafa;
}

.ks-account-profile-photo-preview {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #f5c400;
    flex-shrink: 0;
    background: #eee;
}

.ks-account-profile-photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ks-account-profile-photo-label {
    margin: 0 0 8px;
    font-size: 13px;
    font-weight: 700;
    color: #333;
}

.ks-account-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.ks-account-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.ks-account-field span {
    font-size: 13px;
    font-weight: 700;
    color: #333;
}

.ks-account-mobile-field {
    overflow: visible;
}

.ks-account-mobile-field :deep(.ks-mobile-flag-input) {
    min-height: 42px;
    border: 1px solid #d8d8d8;
    border-radius: 4px;
    padding: 10px 12px;
    font-size: 14px;
}

.ks-account-mobile-field :deep(.ks-mobile-flag .iti--separate-dial-code .iti__selected-country) {
    background: #f7f7f7;
    border-right: 1px solid #d8d8d8;
    border-radius: 4px 0 0 4px;
}

.ks-account-mobile-field :deep(.ks-mobile-flag-input:focus) {
    outline: none;
    border-color: #f5c400;
    box-shadow: 0 0 0 2px rgba(245, 196, 0, 0.25);
}

.ks-account-input {
    min-height: 42px;
    border: 1px solid #d8d8d8;
    border-radius: 4px;
    padding: 0 12px;
    font-size: 14px;
    background: #fff;
}

.ks-account-input:focus {
    outline: none;
    border-color: #f5c400;
    box-shadow: 0 0 0 2px rgba(245, 196, 0, 0.25);
}

.ks-account-form-actions {
    display: flex;
    justify-content: flex-start;
}

.ks-account-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 18px;
    border-radius: 4px;
    background: #f5c400;
    color: #111;
    font-size: 13px;
    font-weight: 800;
    border: 0;
    cursor: pointer;
}

.ks-account-btn--ghost {
    background: #fff;
    border: 1px solid #ddd;
}

.ks-account-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.ks-account-profile-upload {
    cursor: pointer;
}

.ks-account-error {
    margin: 0;
    color: #b42318;
    font-size: 13px;
    font-weight: 600;
}

.ks-account-success {
    margin: 0;
    color: #067647;
    font-size: 13px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .ks-account-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
