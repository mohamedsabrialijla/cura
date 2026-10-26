<template>
    <div class="add-ad-bg">
        <div class="add-ad-card">
            <button class="add-ad-back" @click="handleBack">
                <i
                    :class="
                        locale == 'ar'
                            ? 'fas fa-angle-right'
                            : 'fas fa-angle-left'
                    "
                ></i>
                {{ translations?.Back || "Back" }}
            </button>
            <template v-if="!showForm && setting">
                <div class="add-ad-title-row">
                    <img
                        src="/assets/media/svg/brand-logos/logo.png"
                        class="add-ad-logo"
                    />
                    <div class="add-ad-title">
                        {{
                            translations?.Conditions_1 ||
                            "Conditions for Adding a Product"
                        }}
                    </div>
                </div>

                <hr class="add-ad-divider" />

                <!-- شرط 1 -->
                <div class="add-ad-condition-block">
                    <div class="add-ad-condition-text">
                        {{ setting.condition1 }}
                    </div>
                    <div class="add-ad-actions">
                        <button
                            :class="['add-ad-btn', agreed[0] ? 'active' : '']"
                            @click="setAgree(0, true)"
                        >
                            {{ translations?.Agree || "Agree" }}
                        </button>
                        <button
                            :class="[
                                'add-ad-btn',
                                agreed[0] === false ? 'disagree' : '',
                            ]"
                            @click="setAgree(0, false)"
                        >
                            {{ translations?.Disagree || "Disagree" }}
                        </button>
                    </div>
                </div>

                <!-- شرط 2 -->
                <div class="add-ad-condition-block">
                    <div class="add-ad-condition-text">
                        {{ setting.condition2 }}
                    </div>
                    <div class="add-ad-actions">
                        <button
                            :class="['add-ad-btn', agreed[1] ? 'active' : '']"
                            @click="setAgree(1, true)"
                        >
                            {{ translations?.Agree || "Agree" }}
                        </button>
                        <button
                            :class="[
                                'add-ad-btn',
                                agreed[1] === false ? 'disagree' : '',
                            ]"
                            @click="setAgree(1, false)"
                        >
                            {{ translations?.Disagree || "Disagree" }}
                        </button>
                    </div>
                </div>

                <!-- زر المتابعة -->
                <button
                    class="add-ad-continue"
                    :disabled="!canContinue"
                    @click="showForm = true"
                >
                    {{ translations?.Continue || "Continue" }}
                </button>
            </template>

            <template v-else>
                <div class="add-ad-title-row">
                    <img
                        src="/assets/media/svg/brand-logos/logo.png"
                        class="add-ad-logo"
                    />
                    <div class="add-ad-title">
                        {{ translations?.Add_ad || "Add a Product" }}
                    </div>
                </div>
                <hr class="add-ad-divider" />
                <form class="ad-form">
                    <div class="ad-form-upload">
                        <label class="ad-form-upload-box">
                            <input
                                type="file"
                                multiple
                                accept="image/*"
                                @change="onImageChange"
                                style="display: none"
                            />
                            <div class="ad-form-upload-content">
                                <i
                                    class="fas fa-camera ad-form-upload-icon"
                                ></i>
                                <span class="ad-form-upload-plus">+</span>
                            </div>
                        </label>
                    </div>
                    <div class="ad-form-upload-thumbs" v-if="images.length">
                        <div
                            v-for="(img, i) in images"
                            :key="i"
                            class="ad-form-thumb-wrap"
                        >
                            <img :src="img.url" class="ad-form-thumb" />
                            <span
                                class="ad-form-thumb-remove"
                                @click.stop="removeImage(img.id, i)"
                                >&times;</span
                            >
                        </div>
                    </div>

                    <div class="ad-form-row">
                        <select
                            class="ad-form-input"
                            v-model="formAd.category_id"
                            @click="fetchItemsCategories()"
                            @change="onCategoryChange"
                        >
                            <option disabled value="">
                                {{ translations?.category || "category" }}
                            </option>
                            <option
                                :value="category.id"
                                v-for="category in categories"
                                :key="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                        <select class="ad-form-input" v-model="formAd.brand_id">
                            <option disabled value="">
                                {{ (isGoldCategory ? (translations?.brand_optional || 'Brand (optional)') : (translations?.brand || 'Brand')) }}
                            </option>
                            <option
                                :value="brand.id"
                                v-for="brand in brands"
                                :key="brand.id"
                            >
                                {{ brand.name }}
                            </option>
                        </select>
                    </div>

                    <!-- حقول الذهب: كل صف حقلين جنب بعض -->
                    <template v-if="isGoldCategory">
                        <div class="ad-form-row ad-form-gold-row">
                            <select class="ad-form-input ad-form-gold-input" v-model="formAd.piece_id" @change="formAd.caliber_id = ''">
                                <option value="">
                                    {{ translations?.piece_type || 'نوع القطعة' }}
                                </option>
                                <option v-for="p in pieces" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <select class="ad-form-input ad-form-gold-input" v-model="formAd.metal_id" @change="onMetalChange">
                                <option value="">
                                    {{ translations?.metal || 'المعدن' }}
                                </option>
                                <option v-for="m in metals" :key="m.id" :value="m.id">{{ m.name }}</option>
                            </select>
                        </div>
                        <div class="ad-form-row ad-form-gold-row">
                            <select class="ad-form-input ad-form-gold-input" v-model="formAd.caliber_id">
                                <option value="">
                                    {{ translations?.caliber || 'العيار' }}
                                </option>
                                <option v-for="c in calibers" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <div class="ad-form-weight-wrap ad-form-gold-input">
                                <input
                                    type="text"
                                    class="ad-form-input"
                                    v-model="formAd.weight"
                                    :placeholder="weightPlaceholder"
                                    inputmode="decimal"
                                />
                                <span class="ad-form-weight-label">{{ weightLabel }}</span>
                            </div>
                        </div>
                    </template>
                   
                    <div class="ad-form-row ad-form-invoice-row">
                        
                        <div class="modal-account-field col-md-6">
                            <MobileFlag
                                v-if="countries.length"
                                v-model="formAd.mobile"
                                :items="countries"
                                initialCountry="sa"
                                :placeholder="`+966 (${
                                    translations?.optional || 'optional'
                                })`"
                                class="col-md-12"
                            />
                            <input
                                v-else
                                type="text"
                                v-model="formAd.mobile"
                                :placeholder="translations.mobile || 'Mobile'"
                            />


                        </div>

                    <input
                        class="ad-form-input"
                        placeholder="SAR (Optional)"
                        v-model="formAd.price"
                    />

                       
                    </div>



                    <div class="ad-form-row ad-form-invoice-row">

                            <div class="modal-account-field col-md-6">
                                <i class="fas fa-city"></i>
                                <select
                                    @click="fetchItemsCityAd"
                                    v-model="formAd.city_id"
                                    class="form-control bg-transparent border-0"
                                >
                                    <option value="" disabled>
                                        {{ translations?.select || "Select" }}
                                        {{ translations?.state || "City " }}
                                    </option>
                                    <option
                                        :value="city.id"
                                        v-for="city in cities"
                                        :key="city.id"
                                    >
                                        {{ city.name }}
                                    </option>
                                </select>
                            </div>


                            <div class="ad-form-invoice-box">
                                <label class="ad-form-invoice-label">
                                    <span class="ad-form-invoice-title">
                                        {{
                                            translations?.invoice || "Invoice"
                                        }}
                                        <span class="ad-form-invoice-optional"
                                            >({{
                                                translations?.optional ||
                                                "optional"
                                            }})</span
                                        ></span>

                                    <input
                                        type="file"
                                        accept="*"
                                        @change="onInvoiceChange"
                                        style="display: none"
                                        ref="invoiceInput"
                                    />

                                    <!-- إذا لا يوجد ملف، أظهر زر الرفع -->
                                    <span
                                        v-if="!formAd.invoice"
                                        class="ad-form-invoice-attach"
                                        @click="$refs.invoiceInput.click()"
                                    >
                                        <i class="fas fa-paperclip"></i>
                                    </span>

                                    <!-- إذا الملف موجود -->
                                    <span v-else class="ad-form-invoice-file">
                                        <!-- اسم الملف + رابط فتح -->
                                        <a
                                            :href="
                                                getInvoiceUrl(formAd.invoice)
                                            "
                                            target="_blank"
                                            rel="noopener"
                                            class="ad-form-invoice-filename"
                                            style="margin-right: 8px"
                                        >
                                            <i class="fas fa-file-alt"></i>
                                        </a>

                                        <!-- زر الحذف -->
                                    </span>
                                </label>

                                <span
                                    v-if="formAd.invoice"
                                    class="ad-form-invoice-remove"
                                    @click.stop="removeInvoice"
                                >
                                    &times;
                                </span>
                            </div>
                            <div
                                class="ad-form-invoice-check"
                                v-if="invoiceFile"
                            >
                                <svg
                                    width="40"
                                    height="40"
                                    viewBox="0 0 40 40"
                                    fill="none"
                                >
                                    <circle
                                        cx="20"
                                        cy="20"
                                        r="20"
                                        fill="#fff"
                                    />
                                    <circle
                                        @click="getInvoiceUrl(formAd.invoice)"
                                        cx="20"
                                        cy="20"
                                        r="16"
                                        stroke="#e5aa2a"
                                        stroke-width="3"
                                        fill="#fff"
                                    />
                                    <path
                                        d="M13 21L18 26L27 15"
                                        stroke="#e5aa2a"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                        </div>
                    



                    <label class="ad-form-label">{{
                        translations?.product_type || "Product Type"
                    }}</label>
                    <div class="ad-form-row ad-form-product-type-row">
                        <div class="ad-form-product-type">
                            <div
                                class="ad-form-type-option"
                                :class="{
                                    active: formAd.product_type === 'new',
                                }"
                                @click="formAd.product_type = 'new'"
                            >
                                <span class="ad-form-type-label">{{
                                    translations?.new || "New"
                                }}</span>
                                <span class="ad-form-type-radio">
                                    <span
                                        class="ad-form-type-radio-inner"
                                        v-if="formAd.product_type === 'new'"
                                    ></span>
                                </span>
                            </div>
                            <div
                                class="ad-form-type-option"
                                :class="{
                                    active: formAd.product_type === 'used',
                                }"
                                @click="formAd.product_type = 'used'"
                            >
                                <span class="ad-form-type-label">{{
                                    translations?.Used || "Used"
                                }}</span>
                                <span class="ad-form-type-radio">
                                    <span
                                        class="ad-form-type-radio-inner"
                                        v-if="formAd.product_type === 'used'"
                                    ></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <input
                        class="ad-form-input"
                        :placeholder="translations.title"
                        v-model="formAd.title"
                    />
                    <textarea
                        class="ad-form-textarea"
                        :placeholder="translations.Other_details"
                        v-model="formAd.description"
                    ></textarea>
                    <div class="ad-form-footer-row">
                        <span class="ad-form-max">{{
                            translations?.Maximum_of_500_words ||
                            "Maximum of 500 words"
                        }}</span>
                    </div>
                    <button class="ad-form-submit" @click="submitAd">
                        {{ translations?.Add_Advertise || "Add Advertise" }}
                    </button>
                </form>
            </template>
        </div>
    </div>



    <div v-if="showTrusteModal" class="modal-update-backdrop">
            <div class="modal-update-content" style="max-width: 90%; width: 600px;">

                <div class="modal-update-icon-wrap">
                    <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                        <circle cx="32" cy="32" r="32" fill="#fff" />
                        <path
                            d="M20 34L29 43L44 25"
                            stroke="#e5aa2a"
                            stroke-width="4"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <circle
                            cx="32"
                            cy="32"
                            r="28"
                            stroke="#e5aa2a"
                            stroke-width="4"
                        />
                    </svg>
                </div>
                <div class="modal-update-text">
                    {{setting.alert_gold}}<br />

                    <template v-if="nafathStep === 'idle'">
                        <input
                            style="margin-top: 19px;"
                            class="ad-form-input"
                            :placeholder="translations?.ID_NO || 'رقم الهوية'"
                            v-model="formTrust.id_no"
                            maxlength="10"
                        />
                    </template>
                    <template v-else-if="nafathStep === 'waiting'">
                        <p style="margin-top: 19px; font-weight: 600; color: #333;">
                            {{ translations?.Open_Nafath_App || 'افتح تطبيق نفاذ وأدخل الرقم:' }}
                        </p>
                        <p style="font-size: 1.75rem; letter-spacing: 4px; color: #0d6efd;">{{ nafathRandom }}</p>
                        <p style="margin-top: 8px; color: #666; font-size: 0.9rem;">{{ translations?.Waiting_verification || 'بانتظار الموافقة من التطبيق...' }}</p>
                    </template>
                </div>

                    <div class="add-ad-actions">
                        <template v-if="nafathStep === 'idle'">
                            <button
                                class="add-ad-btn active"
                                @click="AgreeTrust()"
                                :disabled="isLoadingNafath"
                            >
                                <span v-if="isLoadingNafath">{{ translations?.Loading || "Loading..." }}</span>
                                <span v-else>{{ translations?.Agree || "Agree" }}</span>
                            </button>
                            <button class="add-ad-btn" @click="DisAgreeTrust()">
                                {{ translations?.Disagree || "Disagree" }}
                            </button>
                        </template>
                        <template v-else-if="nafathStep === 'waiting'">
                            <button class="add-ad-btn" @click="stopNafathPoll(); closeNafathWebView();">
                                {{ translations?.Close || "Close" }}
                            </button>
                        </template>
                    </div>
            </div>
        </div>
</template>
 
<script>
import imgAdd from "@/assets/img-add.png";
import MobileFlag from "@/components/Website/auth/MobileFlag.vue";
import { authApi } from "@/axiosUser";

export default {
    name: "AddAd",
    components: {
        MobileFlag,
    },
    data() {
        return {
            showForm: false,
            productType: "used",
            images: [],
            selectedCategory: "",
            selectedBrand: "",
            selectedCountry: "",
            selectedCity: "",
            agreed: [null, null],

            countries: [],
            cities: [],
            categories: [],
            brands: [],
            pieces: [],
            metals: [],
            calibers: [],
            images: [],
            formAd: {
                invoiceFile: "",
                country_id: "",
                city_id: "",
                category_id: "",
                brand_id: "",
                product_type: "",
                piece_id: "",
                metal_id: "",
                caliber_id: "",
                weight: "",
            },


            formTrust: {
                mobile_trust:'',
                id_no:'',
                birth_date:'',
            },

            showTrusteModal: false,
            showNafathWebView: false,
            nafathUrl: null,
            nafathPopup: null,
            isLoadingNafath: false,
            nafathStep: 'idle',
            nafathRandom: '',
            nafathTransId: null,
            nafathPollTimer: null,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        canContinue() {
            if (this.agreed[0] && this.agreed[1]) {
                return true;
            }
            // return this.agreed.every(val => val === true);
        },

        isGoldCategory() {
            return this.formAd.category_id === 6 || this.formAd.category_id === '6';
        },

        weightLabel() {
            if (!this.isGoldCategory) return '';
            const cat = Number(this.formAd.category_id);
            return cat === 7 ? (this.translations?.carat || 'قيراط') : (this.translations?.gram || 'جرام');
        },

        weightPlaceholder() {
            return this.isGoldCategory ? (this.translations?.weight || 'الوزن') : '';
        },

        setting() {
            return this.$store.getters.getSetting;
        },

        translations() {
            return this.$store.getters.getTranslations || {};
        },
    },
    watch: {
        'formAd.metal_id'(newVal) {
            if (this.isGoldCategory && newVal) {
                this.formAd.caliber_id = '';
                this.fetchCalibers();
            } else {
                this.calibers = [];
            }
        },
    },
    mounted() {
        this.$store.dispatch("fetchSetting");
        this.fetchItemsCountryAd();
        this.fetchItemsCategories();
        this.checkNafathReturn();
    },
    methods: {
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

        DisAgreeTrust(){
            this.stopNafathPoll();
            this.nafathStep = 'idle';
            this.nafathRandom = '';
            this.nafathTransId = null;
            this.formAd.category_id = '';
            this.showTrusteModal = false;
            this.showNafathWebView = false;
            this.nafathUrl = null;
        },

      
 
        /** نفاذ عبر Semati: POST طلب تحقق → عرض الرقم → polling حتى اكتمال/رفض */
        async AgreeTrust() {
            const idNo = (this.formTrust.id_no || '').trim().replace(/\s/g, '');
            if (!idNo || idNo.length !== 10) {
                this.swalFunction("error", this.translations?.Enter_valid_id || "أدخل رقم هوية صالح (10 أرقام)");
                return;
            }
            this.isLoadingNafath = true;
            try {
                const response = await authApi({
                    method: "post",
                    url: "auth/nafath/request",
                    data: { id_number: idNo },
                });
                const data = response.data || {};
                const code = data.code ?? data.status;
                const items = data.items || {};
                const ok = (code === 200 || code === '200' || data.status === true) && (items.trans_id != null || items.transId != null);
                if (ok) {
                    this.nafathTransId = items.trans_id ?? items.transId;
                    this.nafathRandom = items.random ?? this.nafathTransId;
                    this.nafathStep = 'waiting';
                    this.startNafathPoll();
                } else {
                    const msg = data.message || items.error || "فشل في إرسال طلب نفاذ";
                    this.swalFunction("error", typeof msg === 'string' ? msg : JSON.stringify(msg));
                }
            } catch (error) {
                console.error("Nafath error:", error);
                const res = error.response;
                let msg = "حدث خطأ أثناء الاتصال بخدمة نفاذ";
                if (res) {
                    if (res.status === 401) msg = "يجب تسجيل الدخول أولاً";
                    else if (res.data && typeof res.data === 'object') msg = res.data.message || res.data.error || msg;
                }
                this.swalFunction("error", msg);
            } finally {
                this.isLoadingNafath = false;
            }
        },

        startNafathPoll() {
            this.stopNafathPoll();
            const check = async () => {
                if (!this.nafathTransId) return;
                try {
                    const res = await authApi({ method: "get", url: `auth/nafath/check/${this.nafathTransId}` });
                    const status = res.data?.items?.status;
                    if (status === 'completed') {
                        this.stopNafathPoll();
                        this.nafathStep = 'idle';
                        this.showTrusteModal = false;
                        this.swalFunction("success", this.translations?.Verification_success || "تم التحقق من الهوية بنجاح");
                        this.$store.dispatch("fetchUser").catch(() => {});
                    } else if (status === 'rejected') {
                        this.stopNafathPoll();
                        this.nafathStep = 'idle';
                        this.swalFunction("error", this.translations?.Verification_rejected || "تم رفض التحقق");
                    }
                } catch (e) { /* ignore */ }
            };
            check();
            this.nafathPollTimer = setInterval(check, 2500);
        },

        stopNafathPoll() {
            if (this.nafathPollTimer) {
                clearInterval(this.nafathPollTimer);
                this.nafathPollTimer = null;
            }
        },

        closeNafathWebView() {
            this.stopNafathPoll();
            this.nafathStep = 'idle';
            this.nafathRandom = '';
            this.nafathTransId = null;
            this.showTrusteModal = false;
            this.showNafathWebView = false;
            this.nafathUrl = null;
        },

        /** عند العودة من صفحة نفاذ (نفس التبويب) */
        async checkNafathReturn() {
            const q = this.$route.query;
            if (q.nafath !== 'success' && q.nafath !== 'fail') return;
            const nationalId = q.national_id || q.nationalId || '';
            if (q.nafath === 'success' && nationalId) {
                await this.saveNafathResult({ national_id: nationalId, verified: true });
                this.swalFunction("success", "تم التحقق من الهوية بنجاح");
            } else if (q.nafath === 'fail') {
                this.swalFunction("error", "فشل التحقق من الهوية");
            }
            sessionStorage.removeItem('nafath_return');
            sessionStorage.removeItem('nafath_category_id');
            this.showTrusteModal = false;
            // إزالة query من الرابط
            this.$router.replace({ path: this.$route.path, query: {} }).catch(() => {});
        },

        /** حفظ نتيجة نفاذ في الباك اند (POST auth/nafath/save) ثم التأكد من الحالة (GET auth/nafath/status) */
        async saveNafathResult(data) {
            try {
                const res = await authApi({
                    method: "post",
                    url: "auth/nafath/save",
                    data: { national_id: data.national_id, verified: true }
                });
                if (res.data?.code === 200 || res.data?.status === true) {
                    const statusRes = await authApi({ method: "get", url: "auth/nafath/status" });
                    if (statusRes.data?.items) this.$store.dispatch("fetchUser").catch(() => {});
                }
            } catch (e) {
                console.error("Save nafath result:", e);
            }
        },
        // async AgreeTrust() {

          
        //     this.isLoading = true;
        //     try {
        //         const response = await authApi({
        //             method: "get",
        //             url: "Payment/generateUrwayPaymentLinkTrust",
        //             params: {
        //                 amount: this.setting.trust_fees,
        //                 ad_id: '000111',
        //                 id_no: this.formTrust.id_no,
        //                 birth_date: this.formTrust.birth_date,
        //                 mobile_trust: this.formTrust.mobile_trust,
        //             },
        //         });

        //         if (response.data.status && response.data.payment_url) {
        //             window.location.href = response.data.payment_url;
        //         } else {
        //             this.swalFunction(
        //                 "error",
        //                 response.data.message ||
        //                     "حدث خطأ أثناء إنشاء رابط الدفع"
        //             );
        //         }
        //     } catch (error) {
        //         console.error(error);
        //         this.swalFunction(
        //             "error",
        //             error.message || "تعذر الاتصال بالسيرفر"
        //         );
        //     } finally {
        //         this.isLoading = false;
        //     }
        // },

        setAgree(index, value) {
            this.agreed[index] = value;
        },

        handleBack() {
            if (this.showForm) {
                this.showForm = false;
            } else {
                this.$router.back();
            }
        },
        onImageChange(e) {
            const files = Array.from(e.target.files);
            files.forEach((file) => {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.images.push({ file, url: ev.target.result });
                };
                reader.readAsDataURL(file);
            });
        },
        removeImage(idx) {
            this.images.splice(idx, 1);
        },
        onInvoiceChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.invoiceFile = file;
            }
        },
        removeInvoice() {
            this.invoiceFile = null;
            this.$refs.invoiceInput.value = null;
        },

        async submitAd(e) {
            e.preventDefault();
            try {
                const formData = new FormData();

                formData.append("title", this.formAd.title);
                formData.append("description", this.formAd.description);
                formData.append("category_id", this.formAd.category_id);
                formData.append("brand_id", this.formAd.brand_id || "");
                formData.append("country_id", this.formAd.country_id);
                if (this.isGoldCategory) {
                    formData.append("piece_id", this.formAd.piece_id || "");
                    formData.append("metal_id", this.formAd.metal_id || "");
                    formData.append("caliber_id", this.formAd.caliber_id || "");
                    formData.append("weight", this.formAd.weight || "");
                }
                formData.append("city_id", this.formAd.city_id);
                formData.append("product_type", this.formAd.product_type);
                formData.append("mobile", this.formAd.mobile ?? "");
                formData.append("price", this.formAd.price ?? "");

                if (this.invoiceFile) {
                    formData.append("invoice", this.invoiceFile);
                }

                if (this.images && this.images.length > 0) {
                    this.images.forEach((img, index) => {
                        if (img.file) {
                            formData.append(`images[${index}]`, img.file);
                        }
                    });
                }

                const response = await axios.post("/ads/create", formData, {
                    headers: {
                        "Content-Type":
                            formData instanceof FormData
                                ? "multipart/form-data"
                                : "application/json",
                    },
                });

                if (response.data.code === 200) {
                    this.formAd = response.data.items;
                    this.$router.push({ path: `/${this.locale}` });

                    this.isEditing = false;
                } else {
                    this.swalFunction("error", response.data.message);
                }
            } catch (error) {
                this.swalFunction(
                    "error",
                    error.response?.data?.message || "Error updating profile"
                );
            } finally {
                this.isUpdating = false;
            }
        },

        async fetchItemsCountryAd() {
            this.isLoading = true;
            await axios
                .get("basic/getCountries")
                .then((response) => {
                    this.countries = response.data.items;

                    this.fetchItemsCityAd();

                    this.isLoading = false;
                    this.languages = this.$store.state.languages;
                })
                .catch((error) => {
                    this.swalFunction("error", error);
                    this.isLoading = false;
                });
        },

        async fetchItemsCityAd() {

             let intro = $(".iti__selected-dial-code").html();
            let code = intro.replace("+", "");
            let matchedCountry = null;
            for (const country of this.countries) {
                if (country.code == code) {
                    matchedCountry = country;
                    break;
                }
            }

            if (matchedCountry) {
                this.formAd.country_id = matchedCountry.id ;
                // const code = matchedCountry.code.toString();
                // const raw = this.formAd.mobile
                //     .replace("+" + code, "")
                //     .replace(code, "");
                // const cleaned = raw.replace(/^0+/, ""); // يشيل صفر البداية إذا موجود
                // document.querySelector('input[type="tel"]').value = cleaned;
            }

            this.isLoading = true;
            await axios
                .get("basic/getCountries", {
                    params: {
                        country_id: matchedCountry.id,
                    },
                })
                .then((response) => {
                    if (matchedCountry.id) {
                        this.cities = response.data.items;
                    } else {
                        this.countries = response.data.items;
                    }

                    this.isLoading = false;
                    this.languages = this.$store.state.languages;
                })
                .catch((error) => {
                    this.swalFunction("error", error);
                    this.isLoading = false;
                });
        },

        async fetchItemsCategories() {
            this.isLoading = true;
            await axios
                .get("basic/getCategories")
                .then((response) => {
                    this.categories = response.data.items;
                    this.fetchItemsBrands();
                    if (this.formAd.category_id != 6 && this.formAd.category_id != '6') {
                        this.formAd.piece_id = '';
                        this.formAd.metal_id = '';
                        this.formAd.caliber_id = '';
                        this.formAd.weight = '';
                        this.calibers = [];
                    } else {
                        this.fetchPieces();
                        this.fetchMetals();
                    }
                    this.isLoading = false;
                    if(this.formAd.category_id == 6){
                        this.showTrusteModal= true
                    }
                })
                .catch((error) => {
                    this.swalFunction("error", error);
                    this.isLoading = false;
                });
        },

        async fetchPieces() {
            try {
                const res = await axios.get("basic/getPieces");
                this.pieces = res.data?.items || [];
            } catch (e) {
                this.pieces = [];
            }
        },

        async fetchMetals() {
            try {
                const res = await axios.get("basic/getMetals");
                this.metals = res.data?.items || [];
            } catch (e) {
                this.metals = [];
            }
        },

        async fetchCalibers() {
            if (!this.formAd.metal_id) {
                this.calibers = [];
                return;
            }
            try {
                const res = await axios.get("basic/getCalibers", { params: { metal_id: this.formAd.metal_id } });
                this.calibers = res.data?.items || [];
            } catch (e) {
                this.calibers = [];
            }
        },

        onMetalChange() {
            this.formAd.caliber_id = '';
            this.fetchCalibers();
        },

        onCategoryChange() {
            if (this.formAd.category_id == 6 || this.formAd.category_id === '6') {
                this.fetchPieces();
                this.fetchMetals();
                this.formAd.caliber_id = '';
                this.calibers = [];
            } else {
                this.formAd.piece_id = '';
                this.formAd.metal_id = '';
                this.formAd.caliber_id = '';
                this.formAd.weight = '';
                this.calibers = [];
            }
        },

        async fetchItemsBrands() {
            this.isLoading = true;
            await axios
                .get("basic/getCategories", {
                    params: {
                        category_id: this.formAd.category_id,
                    },
                })
                .then((response) => {
                    if (this.formAd.category_id) {
                        this.brands = response.data.items;
                    } else {
                        this.categories = response.data.items;
                    }

                    this.isLoading = false;
                })
                .catch((error) => {
                    this.swalFunction("error", error);
                    this.isLoading = false;
                });
        },
    },
};
</script>

<style scoped>
.add-ad-bg {
    min-height: 100vh;
    background: #fafbfc;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 48px 0 64px 0;
}
.add-ad-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.07);
    padding: 38px 38px 32px 38px;
    min-width: 420px;
    max-width: 710px;
    width: 100%;
    margin-top: 18px;
    position: relative;
}
.add-ad-back {
    background: none;
    border: none;
    color: #222;
    font-size: 1.08rem;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: color 0.18s;
    margin-top: -80px;
    margin-bottom: 50px;
}
.add-ad-title-row {
    display: flex;
    align-items: center;
    gap: 18px;
    justify-content: center;
    margin-bottom: 12px;
    margin-top: 0;
}
.add-ad-logo {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: contain;
}
.add-ad-title {
    flex: 1;
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
    color: #222;
}
.add-ad-divider {
    margin: 18px 0 18px 0;
    border: none;
    border-top: 2px solid #e5e5e5;
}
.add-ad-condition-block {
    background: #f7f7f9;
    border-radius: 12px;
    padding: 18px 18px 10px 18px;
    margin-bottom: 18px;
}
.add-ad-condition-text {
    color: #222;
    font-size: 1.08rem;
    margin-bottom: 12px;
    font-weight: bold;
    line-height: 1.7;
}
.add-ad-actions {
    display: flex;
    gap: 0;
    margin-bottom: 0;
    justify-content: center;
    margin-top: 8px;
}
.add-ad-btn {
    font-weight: 700;
    font-size: 1.08rem;
    border: none;
    outline: none;
    padding: 10px 36px;
    border-radius: 8px 8px;
    background: #cbcbd4;
    color: #1a2733;
    transition: background 0.18s, color 0.18s;
    cursor: pointer;
    box-shadow: none;
    margin: 4px;
}
.add-ad-btn.active {
    background: #e5aa2a;
    color: #fff;
    z-index: 1;
}
.add-ad-btn.disagree {
    background: #e2e2e2;
    color: #1a2733;
    border-radius: 8px 8px;
}
.add-ad-continue {
    width: 100%;
    background: #e5aa2a;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 14px 0;
    font-size: 1.15rem;
    font-weight: 500;
    cursor: pointer;
    margin-top: 24px;
    transition: background 0.18s;
}
.add-ad-continue:disabled {
    background: #e5aa2a77;
    color: #fff;
    cursor: not-allowed;
}
/* --- Add Form Styles --- */
.ad-form-upload {
    margin-bottom: 18px;
}
.ad-form-upload-box {
    width: 100%;
    min-height: 85px;
    background: #f7f7f9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    cursor: pointer;
    overflow-x: auto;
    padding: 12px 0 20px;
}
.ad-form-upload-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    pointer-events: none;
}
.ad-form-upload-icon {
    font-size: 3.2rem;
    color: #7c7c7c1f;
    margin-bottom: 0px;
}
.ad-form-upload-plus {
    font-size: 1.3rem;
    color: #88888863;
    font-weight: bold;
    margin-top: -33px;
}
.ad-form-upload-thumbs {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 18px;
}
.ad-form-thumb-wrap {
    position: relative;
    display: inline-block;
}
.ad-form-thumb {
    width: 54px;
    height: 54px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #e5aa2a;
    background: #fff;
}
.ad-form-thumb-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #fff;
    color: #e53935;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    border: 1px solid #eee;
}
.ad-form-row {
    display: flex;
    gap: 18px;
    margin-bottom: 18px;
}
.ad-form-input {
    flex: 1;
    background: #f7f7f9;
    border: none;
    border-radius: 8px;
    padding: 14px 16px;
    font-size: 1.08rem;
    color: #222;
    outline: none;
    width: 100%;
}
.ad-form-invoice-row {
    align-items: center;
    gap: 12px;
}
.ad-form-gold-row {
    display: flex;
    gap: 18px;
    margin-bottom: 18px;
}
.ad-form-gold-row .ad-form-gold-input {
    flex: 1;
    min-width: 0;
}
.ad-form-gold-row .ad-form-weight-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}
.ad-form-gold-row .ad-form-weight-wrap .ad-form-input {
    max-width: 140px;
    flex: 0 0 auto;
}
.ad-form-weight-label {
    font-size: 1rem;
    color: #555;
    font-weight: 500;
}
.ad-form-invoice-box {
    flex: 2;
    background: #f7f7f9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    padding: 0 18px;
    height: 54px;
    position: relative;
}
.ad-form-invoice-label {
    display: flex;
    align-items: center;
    width: 100%;
    cursor: pointer;
}
.ad-form-invoice-title {
    color: #222;
    font-weight: 600;
    font-size: 1.08rem;
    margin-right: 8px;
}
.ad-form-invoice-optional {
    color: #bbb;
    font-size: 1.05rem;
    font-weight: 400;
    margin-left: 4px;
}
.ad-form-invoice-attach {
    color: #222;
    font-size: 1.25rem;
    margin-left: 12px;
    cursor: pointer;
    transition: color 0.18s;
}
.ad-form-invoice-attach:hover {
    color: #e5aa2a;
}
.ad-form-invoice-file {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 12px;
}
.ad-form-invoice-filename {
    color: #888;
    font-size: 1.05rem;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ad-form-invoice-remove {
    color: #e53935;
    font-size: 1.2rem;
    cursor: pointer;
    margin-left: 4px;
    font-weight: bold;
}
.ad-form-invoice-check {
    background: #fff;
    border-radius: 8px;
    width: 54px;
    height: 54px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(229, 170, 42, 0.1);
}
.modal-account-field {
    display: flex;
    align-items: center;
    background: #f5f5f5;
    border-radius: 6px;
    padding: 0 18px;
    height: 48px;
    font-size: 1.1rem;
    color: #222;
    position: relative;
}
.modal-account-field i {
    margin-right: 12px;
    color: #bdbdbd;
    font-size: 1.15em;
}
.modal-account-field input {
    border: none;
    background: transparent;
    outline: none;
    width: 100%;
    font-size: 1.08em;
    color: #222;
    padding: 0;
}
.modal-account-field input[readonly] {
    cursor: default;
}
.ad-form-label {
    font-weight: bold;
    color: #222;
    margin-bottom: 8px;
    font-size: 1.08rem;
    flex: 1;
}
.ad-form-product-type-row {
    align-items: flex-start;
    margin-bottom: 18px;
}
.ad-form-product-type {
    display: flex;
    gap: 24px;
    flex: 2;
}
.ad-form-type-option {
    background: #f7f7f9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8.7px 16px;
    min-width: 180px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border 0.18s, background 0.18s;
    font-weight: 500;
    color: #222;
    position: relative;
    width: 100%;
}
.ad-form-type-option.active {
    border: 1px solid #e5aa2a;
    background: #fffbe9;
    color: #e5aa2a;
}
.ad-form-type-label {
    font-size: 1.08rem;
    font-weight: 600;
    color: inherit;
}
.ad-form-type-radio {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid #cbcbd4;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 18px;
    transition: border 0.18s;
}
.ad-form-type-option.active .ad-form-type-radio {
    border: 2.5px solid #e5aa2a;
}


.modal-update-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.13);
    z-index: 2600;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-update-content {
    background: #fff;
    border-radius: 1.3rem;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
    padding: 38px 38px 32px 38px;
    min-width: 340px;
    max-width: 585px;
    text-align: center;
    position: relative;
}
.modal-update-icon-wrap {
    width: 80px;
    height: 80px;
    background: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 5px auto 5px auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
    position: absolute;
    left: 50%;
    transform: translateX(-50%) translateY(-50%);
    top: 0;
}
.modal-update-text {
    color: #222;
    font-size: 1.25rem;
    margin-bottom: 22px;
    margin-top: 22px;
    font-weight: 500;
}
.modal-update-ok {
    background: #1a2733;
    color: #fff;
    border: none;
    border-radius: 16px;
    padding: 14px 0;
    width: 90%;
    font-size: 1.2rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.18s;
    margin: 0 auto;
    display: block;
}
.modal-update-ok:hover {
    background: #222f3a;
}


.ad-form-type-radio-inner {
    width: 16px;
    height: 16px;
    background: #e5aa2a;
    border-radius: 50%;
    display: block;
}
.ad-form-textarea {
    width: 100%;
    background: #f7f7f9;
    border: none;
    border-radius: 8px;
    padding: 14px 16px;
    font-size: 1.08rem;
    color: #222;
    outline: none;
    min-height: 70px;
    margin-bottom: 8px;
    margin-top: 18px;
    resize: vertical;
}
.ad-form-footer-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 18px;
}
.ad-form-max {
    color: #bbb;
    font-size: 0.98rem;
}
.ad-form-submit {
    width: 100%;
    background: #e5aa2a;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 14px 0;
    font-size: 1.15rem;
    font-weight: 500;
    cursor: pointer;
    margin-top: 8px;
    transition: background 0.18s;
}
.ad-form-submit:hover {
    background: #d49a1a;
}
/* Responsive Design */
@media (max-width: 1200px) {
    .add-ad-card {
        max-width: 95%;
        padding: 24px;
    }
}

@media (max-width: 992px) {
    .add-ad-card {
        max-width: 98%;
        padding: 20px;
    }

    .add-ad-title {
        font-size: 1.8rem;
    }

    .add-ad-condition-text {
        font-size: 1rem;
    }
}

@media (max-width: 768px) {
    .add-ad-card {
        max-width: 100%;
        padding: 16px;
        margin: 8px;
    }

    .add-ad-title {
        font-size: 1.6rem;
    }

    .add-ad-condition-text {
        font-size: 0.95rem;
    }

    .add-ad-actions {
        flex-direction: column;
        gap: 8px;
    }

    .add-ad-btn {
        width: 100%;
        padding: 12px;
    }
}

@media (max-width: 576px) {
    .add-ad-card {
        margin: 4px;
        padding: 12px;
    }

    .add-ad-title {
        font-size: 1.4rem;
    }

    .add-ad-condition-text {
        font-size: 0.9rem;
    }

    .add-ad-form-row {
        flex-direction: column;
        gap: 12px;
    }

    .add-ad-form-group {
        width: 100%;
    }

    .add-ad-form-input,
    .add-ad-form-textarea,
    .add-ad-form-select {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}
</style>

