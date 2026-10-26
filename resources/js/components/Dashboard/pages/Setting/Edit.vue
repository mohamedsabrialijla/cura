<template>
    <div class="content flex-column-fluid" id="kt_content">
        <div class="card mb-5">
            <div class="card-header border-0 pt-6 pb-4">
                <div class="card-title flex-column align-items-start">
                    <h3 class="fw-bold mb-1">{{ dt.settings.title }}</h3>
                    <span class="text-muted fs-7">
                        {{ dt.settings.subtitle }}
                    </span>
                </div>
            </div>
        </div>

        <div v-if="isLoading && !formLoaded" class="card">
            <div class="card-body py-10 text-center text-muted">
                <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                {{ dt.settings.loading }}
            </div>
        </div>

        <form v-else id="ks-settings-form" class="form" @submit.prevent="addEditItem">
            <!-- General -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ dt.settings.general }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light-primary mb-6">
                        {{ dt.settings.general_hint }}
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.logo }}</label>
                        <div class="col-lg-9">
                            <div class="image-input image-input-outline image-input-placeholder">
                                <div
                                    class="image-input-wrapper w-125px h-125px"
                                    :style="{
                                        backgroundImage: formData.logo
                                            ? `url(${formData.logo})`
                                            : 'url(/assets/avatar.svg)',
                                    }"
                                ></div>
                                <label
                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                    title="Change logo"
                                >
                                    <i class="ki-duotone ki-pencil fs-7"></i>
                                    <input
                                        type="file"
                                        accept=".png,.jpg,.jpeg,.webp"
                                        @change="onFileChange"
                                    />
                                </label>
                            </div>
                            <div class="form-text">{{ dt.settings.logo_hint }}</div>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="required col-lg-3 col-form-label fw-semibold">{{ dt.settings.vat }}</label>
                        <div class="col-lg-9 col-xl-4">
                            <input
                                v-model="formData.vat"
                                type="text"
                                maxlength="12"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.vat_ph"
                                required
                            />
                        </div>
                    </div>


                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.eur_to_sar_rate }}</label>
                        <div class="col-lg-9 col-xl-4">
                            <input
                                v-model="formData.eur_to_sar_rate"
                                type="number"
                                min="0"
                                step="0.0001"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.eur_to_sar_ph"
                            />
                            <div class="form-text">{{ dt.settings.eur_to_sar_hint }}</div>
                        </div>
                    </div>

                    <div class="alert alert-light-warning mb-6">
                        {{ dt.settings.pricing_formula_hint }}
                    </div>

                    <div
                        v-for="lang in languages"
                        :key="'name_' + lang.locale"
                        class="row mb-5"
                    >
                        <label class="required col-lg-3 col-form-label fw-semibold">
                            {{ dt.settings.site_name }} ({{ lang.name }})
                        </label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData['name_' + langCode(lang)]"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="`${lang.name} — ${dt.settings.site_name_ph}`"
                                required
                            />
                        </div>
                    </div>

                    <div
                        v-for="lang in languages"
                        :key="'key_words_' + lang.locale"
                        class="row mb-2"
                    >
                        <label class="required col-lg-3 col-form-label fw-semibold">
                            {{ dt.settings.seo_keywords }} ({{ lang.name }})
                        </label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData['key_words_' + langCode(lang)]"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.seo_keywords_ph"
                                required
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header ticker -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ dt.settings.header_ticker_title }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light-warning mb-6">
                        {{ dt.settings.header_ticker_hint }}
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.header_ticker_enabled }}</label>
                        <div class="col-lg-9 d-flex align-items-center">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input
                                    id="ticker_enabled"
                                    v-model="formData.ticker_enabled"
                                    class="form-check-input"
                                    type="checkbox"
                                />
                                <label class="form-check-label" for="ticker_enabled">
                                    {{ dt.settings.header_ticker_enabled_label }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-8">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.header_ticker_bg }}</label>
                        <div class="col-lg-9">
                            <div class="d-flex align-items-center gap-3">
                                <input
                                    v-model="formData.ticker_bg_color"
                                    type="color"
                                    class="form-control form-control-color p-1"
                                    style="width: 52px; height: 40px; min-width: 52px;"
                                    :title="dt.settings.header_ticker_bg"
                                />
                                <input
                                    v-model="formData.ticker_bg_color"
                                    type="text"
                                    class="form-control form-control-solid w-150px"
                                    maxlength="7"
                                    placeholder="#000000"
                                    dir="ltr"
                                />
                            </div>
                            <div class="form-text">{{ dt.settings.header_ticker_bg_hint }}</div>
                        </div>
                    </div>

                    <h4 class="fw-semibold mb-4">{{ dt.settings.technic_text_ar }}</h4>
                    <div class="row mb-5">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.header_ticker_prefix }}</label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData.ticker_prefix_ar"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.header_ticker_prefix_ph"
                            />
                        </div>
                    </div>
                    <div class="row mb-5">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.header_ticker_cta }}</label>
                        <div class="col-lg-9 col-xl-5">
                            <input
                                v-model="formData.ticker_cta_ar"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.header_ticker_cta_ph"
                            />
                        </div>
                    </div>
                    <div class="row mb-8">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.header_ticker_link }}</label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData.ticker_link_ar"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.header_ticker_link_ph"
                            />
                        </div>
                    </div>

                    <h4 class="fw-semibold mb-4">{{ dt.settings.technic_text_en }}</h4>
                    <div class="row mb-5">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.header_ticker_prefix }}</label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData.ticker_prefix_en"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.header_ticker_prefix_ph"
                            />
                        </div>
                    </div>
                    <div class="row mb-5">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.header_ticker_cta }}</label>
                        <div class="col-lg-9 col-xl-5">
                            <input
                                v-model="formData.ticker_cta_en"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.header_ticker_cta_ph"
                            />
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.header_ticker_link }}</label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData.ticker_link_en"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.header_ticker_link_ph"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ dt.settings.contact }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light-info mb-6">
                        {{ dt.settings.contact_hint }}
                    </div>

                    <div class="row mb-5">
                        <label class="required col-lg-3 col-form-label fw-semibold">{{ dt.settings.mobile }}</label>
                        <div class="col-lg-9 col-xl-5">
                            <input
                                v-model="formData.mobile"
                                type="text"
                                maxlength="20"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.mobile_ph"
                                required
                            />
                        </div>
                    </div>

                    <div class="row mb-5">
                        <label class="required col-lg-3 col-form-label fw-semibold">{{ dt.common.email }}</label>
                        <div class="col-lg-9 col-xl-5">
                            <input
                                v-model="formData.email"
                                type="email"
                                maxlength="255"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.email_ph"
                                required
                            />
                        </div>
                    </div>

                    <div class="row mb-5">
                        <label class="required col-lg-3 col-form-label fw-semibold">{{ dt.settings.whatsapp }}</label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData.whatsapp"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="dt.settings.whatsapp_ph"
                                required
                            />
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-lg-3">
                            <span class="col-form-label fw-semibold d-block">{{ dt.settings.social_links }}</span>
                            <span class="text-muted fs-8">{{ dt.settings.social_links_hint }}</span>
                        </div>
                        <div class="col-lg-9">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ dt.settings.instagram }}</label>
                                    <input
                                        v-model="formData.instagram"
                                        type="text"
                                        class="form-control form-control-solid"
                                        :placeholder="dt.settings.instagram_ph"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ dt.settings.facebook }}</label>
                                    <input
                                        v-model="formData.facebook"
                                        type="text"
                                        class="form-control form-control-solid"
                                        :placeholder="dt.settings.facebook_ph"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ dt.settings.tiktok }}</label>
                                    <input
                                        v-model="formData.tiktok"
                                        type="text"
                                        class="form-control form-control-solid"
                                        :placeholder="dt.settings.tiktok_ph"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ dt.settings.snapchat }}</label>
                                    <input
                                        v-model="formData.snapchat"
                                        type="text"
                                        class="form-control form-control-solid"
                                        :placeholder="dt.settings.snapchat_ph"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ dt.settings.terms }}</h3>
                </div>
                <div class="card-body">
                    <div
                        v-for="lang in languages"
                        :key="'condition1_' + lang.locale"
                        class="row mb-5"
                    >
                        <label class="required col-lg-3 col-form-label fw-semibold">
                            {{ lang.name }}
                        </label>
                        <div class="col-lg-9">
                            <textarea
                                v-model="formData['condition1_' + langCode(lang)]"
                                rows="4"
                                maxlength="250"
                                class="form-control form-control-solid"
                                :placeholder="`${lang.name} — ${dt.settings.terms_ph}`"
                                required
                            ></textarea>
                            <div class="form-text">{{ dt.settings.terms_max }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Homepage banners -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ dt.settings.banners }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light-warning mb-8">
                        {{ dt.settings.banners_hint }}
                    </div>

                    <div
                        v-for="section in bannerSections"
                        :key="section.key"
                        class="border rounded p-5 mb-6"
                    >
                        <h4 class="fw-bold mb-1">{{ section.title }}</h4>
                        <p class="text-muted fs-7 mb-5">{{ section.hint }}</p>

                        <div class="row mb-5">
                            <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.link_url }}</label>
                            <div class="col-lg-9">
                                <input
                                    v-model="formData[section.linkField]"
                                    type="url"
                                    maxlength="500"
                                    class="form-control form-control-solid"
                                    :placeholder="dt.settings.link_url_ph"
                                />
                                <div class="form-text">
                                    {{ dt.settings.link_hint }}
                                </div>
                            </div>
                        </div>

                        <div class="ks-media-upload-grid">
                            <div class="ks-media-upload">
                                <div class="ks-media-upload__head">
                                    <span class="fw-semibold">{{ dt.settings.desktop_image }}</span>
                                    <span class="text-muted fs-8">{{ dt.settings.desktop_size }}</span>
                                </div>
                                <div class="ks-media-upload__frame ks-media-upload__frame--wide">
                                    <img
                                        :src="mediaPreviewUrl(formData[section.desktopField])"
                                        :alt="`${section.title} desktop`"
                                    />
                                </div>
                                <label class="btn btn-sm btn-light-primary mt-3 mb-0">
                                    {{ dt.settings.choose_desktop }}
                                    <input
                                        type="file"
                                        class="d-none"
                                        accept=".png,.jpg,.jpeg,.webp"
                                        @change="section.onDesktopChange"
                                    />
                                </label>
                            </div>

                            <div class="ks-media-upload ks-media-upload--mobile">
                                <div class="ks-media-upload__head">
                                    <span class="fw-semibold">{{ dt.settings.mobile_image }}</span>
                                    <span class="text-muted fs-8">{{ dt.settings.mobile_size }}</span>
                                </div>
                                <div class="ks-media-upload__frame ks-media-upload__frame--mobile">
                                    <img
                                        :src="mediaPreviewUrl(formData[section.mobileField])"
                                        :alt="`${section.title} mobile`"
                                    />
                                </div>
                                <label class="btn btn-sm btn-light-primary mt-3 mb-0">
                                    {{ dt.settings.choose_mobile }}
                                    <input
                                        type="file"
                                        class="d-none"
                                        accept=".png,.jpg,.jpeg,.webp"
                                        @change="section.onMobileChange"
                                    />
                                </label>
                            </div>
                        </div>
                        <div class="form-text mt-3">{{ dt.settings.image_max }}</div>
                    </div>
                </div>
            </div>

            <!-- Technical apparel section text -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ dt.settings.technic_text_title }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light-info mb-6">
                        {{ dt.settings.technic_text_hint }}
                    </div>

                    <h4 class="fw-bold mb-4">{{ dt.settings.technic_text_ar }}</h4>
                    <div class="row mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.technic_title_white }}</label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData.technic_title_white_ar"
                                type="text"
                                maxlength="255"
                                class="form-control form-control-solid"
                            />
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.technic_title_gold }}</label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData.technic_title_gold_ar"
                                type="text"
                                maxlength="255"
                                class="form-control form-control-solid"
                            />
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.technic_description }}</label>
                        <div class="col-lg-9">
                            <textarea
                                v-model="formData.technic_description_ar"
                                rows="3"
                                maxlength="2000"
                                class="form-control form-control-solid"
                            ></textarea>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-4">{{ dt.settings.technic_text_en }}</h4>
                    <div class="row mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.technic_title_white }}</label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData.technic_title_white_en"
                                type="text"
                                maxlength="255"
                                class="form-control form-control-solid"
                            />
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.technic_title_gold }}</label>
                        <div class="col-lg-9">
                            <input
                                v-model="formData.technic_title_gold_en"
                                type="text"
                                maxlength="255"
                                class="form-control form-control-solid"
                            />
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.technic_description }}</label>
                        <div class="col-lg-9">
                            <textarea
                                v-model="formData.technic_description_en"
                                rows="3"
                                maxlength="2000"
                                class="form-control form-control-solid"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Landing cards -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ dt.settings.landing_cards }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light-info mb-6">
                        {{ dt.settings.landing_cards_hint }}
                    </div>

                    <div
                        v-for="(card, idx) in landingCards"
                        :key="`landing-card-${idx}`"
                        class="border rounded p-5 mb-5"
                    >
                        <h4 class="fw-bold mb-4">{{ landingCardTitle(card, idx) }}</h4>

                        <div class="row mb-4">
                            <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.title_ar }}</label>
                            <div class="col-lg-9">
                                <input
                                    v-model="card.title_ar"
                                    type="text"
                                    class="form-control form-control-solid"
                                />
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.title_en }}</label>
                            <div class="col-lg-9">
                                <input
                                    v-model="card.title_en"
                                    type="text"
                                    class="form-control form-control-solid"
                                />
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.link_url }}</label>
                            <div class="col-lg-9">
                                <input
                                    v-model="card.link"
                                    type="url"
                                    class="form-control form-control-solid"
                                    :placeholder="dt.settings.link_url_ph"
                                />
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-lg-3 col-form-label fw-semibold">{{ dt.settings.card_image }}</label>
                            <div class="col-lg-9">
                                <div class="ks-media-upload ks-media-upload--card-only">
                                    <div class="ks-media-upload__head">
                                        <span class="text-muted fs-8">{{ dt.settings.card_size }}</span>
                                    </div>
                                    <div class="ks-media-upload__frame ks-media-upload__frame--card">
                                        <img
                                            :src="mediaPreviewUrl(card.image)"
                                            :alt="landingCardTitle(card, idx)"
                                        />
                                    </div>
                                    <label class="btn btn-sm btn-light-primary mt-3 mb-0">
                                        {{ dt.settings.choose_image }}
                                        <input
                                            type="file"
                                            class="d-none"
                                            accept=".png,.jpg,.jpeg,.webp"
                                            @change="onLandingCardImageChange($event, idx)"
                                        />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save -->
            <div class="card">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="text-muted fs-7">
                        {{ dt.settings.save_hint }}
                    </span>
                    <button type="submit" class="btn btn-primary" :disabled="isLoading">
                        <span v-if="!isLoading">{{ dt.settings.save_settings }}</span>
                        <span v-else>
                            {{ dt.common.saving }}
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            languages: [],
            isLoading: false,
            formLoaded: false,
            logo: null,
            coverImage: null,
            coverImageMobile: null,
            technicImage: null,
            technicImageMobile: null,
            whiteoutImage: null,
            whiteoutImageMobile: null,
            trainingImage: null,
            trainingImageMobile: null,
            landingCardImages: [null, null, null, null],
            landingCards: [
                { id: "deal_1", title_ar: "", title_en: "", link: "", image: "" },
                { id: "deal_2", title_ar: "", title_en: "", link: "", image: "" },
                { id: "news_1", title_ar: "", title_en: "", link: "", image: "" },
                { id: "news_2", title_ar: "", title_en: "", link: "", image: "" },
            ],
            formData: {
                mobile: "",
                email: "",
                whatsapp: "",
                instagram: "",
                facebook: "",
                tiktok: "",
                snapchat: "",
                vat: "",
                eur_to_sar_rate: "",
                cover_link: "",
                cover_image: "",
                cover_image_mobile: "",
                whiteout_link: "",
                whiteout_image: "",
                whiteout_image_mobile: "",
                training_link: "",
                training_image: "",
                training_image_mobile: "",
                technic_link: "",
                technic_image: "",
                technic_image_mobile: "",
                technic_title_white_ar: "",
                technic_title_gold_ar: "",
                technic_description_ar: "",
                technic_title_white_en: "",
                technic_title_gold_en: "",
                technic_description_en: "",
                ticker_enabled: true,
                ticker_prefix_en: "",
                ticker_prefix_ar: "",
                ticker_cta_en: "",
                ticker_cta_ar: "",
                ticker_link_en: "",
                ticker_link_ar: "",
                ticker_bg_color: "#000000",
                logo: "",
            },
            ItemID: null,
            URL: "Settings/editItem",
        };
    },
    computed: {
        bannerSections() {
            const s = this.dt.settings;
            return [
                {
                    key: "cover",
                    title: s.banner_cover,
                    hint: s.banner_cover_hint,
                    linkField: "cover_link",
                    desktopField: "cover_image",
                    mobileField: "cover_image_mobile",
                    onDesktopChange: this.onCoverFileChange,
                    onMobileChange: this.onCoverMobileFileChange,
                },
                {
                    key: "whiteout",
                    title: s.banner_whiteout,
                    hint: s.banner_whiteout_hint,
                    linkField: "whiteout_link",
                    desktopField: "whiteout_image",
                    mobileField: "whiteout_image_mobile",
                    onDesktopChange: this.onWhiteoutFileChange,
                    onMobileChange: this.onWhiteoutMobileFileChange,
                },
                {
                    key: "training",
                    title: s.banner_training,
                    hint: s.banner_training_hint,
                    linkField: "training_link",
                    desktopField: "training_image",
                    mobileField: "training_image_mobile",
                    onDesktopChange: this.onTrainingFileChange,
                    onMobileChange: this.onTrainingMobileFileChange,
                },
                {
                    key: "technic",
                    title: s.banner_technic,
                    hint: s.banner_technic_hint,
                    linkField: "technic_link",
                    desktopField: "technic_image",
                    mobileField: "technic_image_mobile",
                    onDesktopChange: this.onTechnicFileChange,
                    onMobileChange: this.onTechnicMobileFileChange,
                },
            ];
        },
    },
    mounted() {
        void this.bootstrapSettingsForm();
    },
    methods: {
        defaultLanguages() {
            return [
                { lang: "en", locale: "en", name: this.dt.lang_names.en },
                { lang: "ar", locale: "ar", name: this.dt.lang_names.ar },
            ];
        },
        langCode(lang) {
            return String(lang?.lang || lang?.locale || "").trim();
        },
        async ensureLanguages() {
            const fromStore = Array.isArray(this.$store.state.languages)
                ? this.$store.state.languages
                : [];
            if (fromStore.length) {
                this.languages = fromStore;
                return;
            }

            try {
                const { data } = await axios.get("Languages/getAllItems");
                const items = Array.isArray(data?.items) ? data.items : [];
                this.languages = items.length ? items : this.defaultLanguages();
                this.$store.commit("setLanguages", this.languages);
            } catch (_) {
                this.languages = this.defaultLanguages();
            }
        },
        applyTranslationsFromPayload(data) {
            if (!data || typeof data !== "object") {
                return;
            }

            const rows = Array.isArray(data.translations) ? data.translations : [];
            if (rows.length) {
                rows.forEach((row) => {
                    const code = String(row?.locale || row?.lang || "").trim();
                    if (!code) {
                        return;
                    }
                    this.formData[`name_${code}`] = row.name ?? "";
                    this.formData[`key_words_${code}`] = row.key_words ?? "";
                    this.formData[`condition1_${code}`] = row.condition1 ?? "";
                });
                return;
            }

            const fallbackLocale = localStorage.getItem("locale") || "en";
            if (data.name != null && data.name !== "") {
                this.formData[`name_${fallbackLocale}`] = data.name;
            }
            if (data.key_words != null && data.key_words !== "") {
                this.formData[`key_words_${fallbackLocale}`] = data.key_words;
            }
            if (data.condition1 != null && data.condition1 !== "") {
                this.formData[`condition1_${fallbackLocale}`] = data.condition1;
            }
        },
        async bootstrapSettingsForm() {
            await this.ensureLanguages();
            await this.getItemById();
        },
        mediaPreviewUrl(value) {
            const src = String(value || "").trim();
            return src || "/assets/media/svg/files/blank-image.svg";
        },
        imageStorageFilename(value) {
            const src = String(value || "").trim();
            if (!src || src.startsWith("blob:")) {
                return "";
            }
            const match = src.match(/\/uploads\/settings\/([^/?#]+)/i);
            if (match) {
                return match[1];
            }
            if (!src.includes("/") && !src.startsWith("http")) {
                return src;
            }
            return "";
        },
        landingCardsForSave() {
            return this.landingCards.map((card) => ({
                id: card.id,
                title_ar: card.title_ar || "",
                title_en: card.title_en || "",
                link: card.link || "",
                image: this.imageStorageFilename(card.image),
            }));
        },
        landingCardTitle(card, idx) {
            const s = this.dt.settings;
            const labels = {
                deal_1: s.card_deal_1,
                deal_2: s.card_deal_2,
                news_1: s.card_news_1,
                news_2: s.card_news_2,
            };
            return labels[card.id] || `${s.card_n} ${idx + 1}`;
        },
        onFileChange(e) {
            this.logo = e.target.files[0];
            const file = e.target.files[0];
            if (file) {
                this.formData.logo = URL.createObjectURL(file);
            }
        },
        onCoverFileChange(e) {
            this.coverImage = e.target.files[0];
            const file = e.target.files[0];
            if (file) {
                this.formData.cover_image = URL.createObjectURL(file);
            }
        },
        onCoverMobileFileChange(e) {
            this.coverImageMobile = e.target.files[0];
            const file = e.target.files[0];
            if (file) {
                this.formData.cover_image_mobile = URL.createObjectURL(file);
            }
        },
        onLandingCardImageChange(e, idx) {
            const file = e.target.files[0];
            this.landingCardImages[idx] = file || null;
            if (file) {
                this.landingCards[idx].image = URL.createObjectURL(file);
            }
        },
        onWhiteoutFileChange(e) {
            this.whiteoutImage = e.target.files[0];
            const file = e.target.files[0];
            if (file) {
                this.formData.whiteout_image = URL.createObjectURL(file);
            }
        },
        onWhiteoutMobileFileChange(e) {
            this.whiteoutImageMobile = e.target.files[0];
            const file = e.target.files[0];
            if (file) {
                this.formData.whiteout_image_mobile = URL.createObjectURL(file);
            }
        },
        onTrainingFileChange(e) {
            this.trainingImage = e.target.files[0];
            const file = e.target.files[0];
            if (file) {
                this.formData.training_image = URL.createObjectURL(file);
            }
        },
        onTrainingMobileFileChange(e) {
            this.trainingImageMobile = e.target.files[0];
            const file = e.target.files[0];
            if (file) {
                this.formData.training_image_mobile = URL.createObjectURL(file);
            }
        },
        onTechnicFileChange(e) {
            this.technicImage = e.target.files[0];
            const file = e.target.files[0];
            if (file) {
                this.formData.technic_image = URL.createObjectURL(file);
            }
        },
        onTechnicMobileFileChange(e) {
            this.technicImageMobile = e.target.files[0];
            const file = e.target.files[0];
            if (file) {
                this.formData.technic_image_mobile = URL.createObjectURL(file);
            }
        },
        swalFunction(type, text) {
            Swal.fire({
                text,
                icon: type,
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            });
        },
        addEditItem() {
            this.isLoading = true;

            const config = {
                headers: {
                    "content-type": "multipart/form-data",
                },
            };

            const form = new FormData();
            form.append("mobile", this.formData.mobile);
            form.append("email", this.formData.email);
            form.append("whatsapp", this.formData.whatsapp);
            form.append("instagram", this.formData.instagram || "");
            form.append("facebook", this.formData.facebook || "");
            form.append("tiktok", this.formData.tiktok || "");
            form.append("snapchat", this.formData.snapchat || "");
            form.append("vat", this.formData.vat);
            form.append("eur_to_sar_rate", this.formData.eur_to_sar_rate || 0);
            form.append("cover_link", this.formData.cover_link || "");
            form.append("whiteout_link", this.formData.whiteout_link || "");
            form.append("training_link", this.formData.training_link || "");
            form.append("technic_link", this.formData.technic_link || "");
            form.append("technic_title_white_ar", this.formData.technic_title_white_ar || "");
            form.append("technic_title_gold_ar", this.formData.technic_title_gold_ar || "");
            form.append("technic_description_ar", this.formData.technic_description_ar || "");
            form.append("technic_title_white_en", this.formData.technic_title_white_en || "");
            form.append("technic_title_gold_en", this.formData.technic_title_gold_en || "");
            form.append("technic_description_en", this.formData.technic_description_en || "");
            form.append("ticker_enabled", this.formData.ticker_enabled ? "1" : "0");
            form.append("ticker_prefix_en", this.formData.ticker_prefix_en || "");
            form.append("ticker_prefix_ar", this.formData.ticker_prefix_ar || "");
            form.append("ticker_cta_en", this.formData.ticker_cta_en || "");
            form.append("ticker_cta_ar", this.formData.ticker_cta_ar || "");
            form.append("ticker_link_en", this.formData.ticker_link_en || "");
            form.append("ticker_link_ar", this.formData.ticker_link_ar || "");
            form.append("ticker_bg_color", this.formData.ticker_bg_color || "#000000");
            form.append("landing_cards", JSON.stringify(this.landingCardsForSave()));

            this.languages.forEach((element) => {
                const code = this.langCode(element);
                if (!code) {
                    return;
                }
                form.append("name_" + code, this.formData["name_" + code] ?? "");
                form.append("key_words_" + code, this.formData["key_words_" + code] ?? "");
                form.append("condition1_" + code, this.formData["condition1_" + code] ?? "");
            });

            if (this.logo) {
                form.append("logo", this.logo);
            }
            if (this.coverImage) {
                form.append("cover_image", this.coverImage);
            }
            if (this.coverImageMobile) {
                form.append("cover_image_mobile", this.coverImageMobile);
            }
            if (this.whiteoutImage) {
                form.append("whiteout_image", this.whiteoutImage);
            }
            if (this.whiteoutImageMobile) {
                form.append("whiteout_image_mobile", this.whiteoutImageMobile);
            }
            if (this.trainingImage) {
                form.append("training_image", this.trainingImage);
            }
            if (this.trainingImageMobile) {
                form.append("training_image_mobile", this.trainingImageMobile);
            }
            if (this.technicImage) {
                form.append("technic_image", this.technicImage);
            }
            if (this.technicImageMobile) {
                form.append("technic_image_mobile", this.technicImageMobile);
            }
            this.landingCardImages.forEach((file, idx) => {
                if (file) {
                    form.append(`landing_card_image_${idx}`, file);
                }
            });

            if (this.ItemID != "") {
                form.append("Item_id", this.ItemID);
            }

            axios
                .post(this.URL, form, config)
                .then((response) => {
                    this.isLoading = false;
                    if (response.data.items) {
                        this.swalFunction("success", response.data.message);
                        this.getItemById();
                    } else {
                        Swal.fire({
                            text: response.data.message,
                            icon: "error",
                            timer: false,
                            button: true,
                        });
                    }
                })
                .catch((error) => {
                    this.isLoading = false;
                    this.swalFunction("error", error);
                });
        },
        async getItemById() {
            this.isLoading = true;
            return axios
                .get("Settings/getById", { params: { ID: this.ItemID } })
                .then(async (response) => {
                    if (response.data) {
                        const data = response.data.items || {};
                        this.formData = { ...this.formData, ...data };
                        if (!this.languages.length) {
                            await this.ensureLanguages();
                        }

                        if (
                            Array.isArray(data.landing_section_cards) &&
                            data.landing_section_cards.length === 4
                        ) {
                            this.landingCards = data.landing_section_cards.map((row, idx) => ({
                                id: row.id || this.landingCards[idx].id,
                                title_ar: row.title_ar || "",
                                title_en: row.title_en || "",
                                link: row.link || "",
                                image: row.image || "",
                            }));
                        }

                        this.applyTranslationsFromPayload(data);

                        if (data.header_ticker && typeof data.header_ticker === "object") {
                            const t = data.header_ticker;
                            this.formData.ticker_enabled = t.is_enabled !== false;
                            this.formData.ticker_prefix_en = t.prefix_en || "";
                            this.formData.ticker_prefix_ar = t.prefix_ar || "";
                            this.formData.ticker_cta_en = t.cta_en || "";
                            this.formData.ticker_cta_ar = t.cta_ar || "";
                            this.formData.ticker_link_en = t.link_en || "";
                            this.formData.ticker_link_ar = t.link_ar || "";
                            this.formData.ticker_bg_color = t.bg_color || "#000000";
                        } else {
                            this.formData.ticker_enabled = data.ticker_enabled !== false;
                        }

                        this.coverImage = null;
                        this.coverImageMobile = null;
                        this.whiteoutImage = null;
                        this.whiteoutImageMobile = null;
                        this.trainingImage = null;
                        this.trainingImageMobile = null;
                        this.technicImage = null;
                        this.technicImageMobile = null;
                        this.landingCardImages = [null, null, null, null];
                        this.logo = null;

                        this.formLoaded = true;
                    } else {
                        Swal.fire({
                            text: this.dt.settings.load_error,
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                        });
                    }
                })
                .catch((error) => {
                    this.swalFunction("error", error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
    },
};
</script>

<style scoped>
.image-input-placeholder {
    background-image: url("assets/media/svg/files/blank-image.svg");
}

.ks-media-upload-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 280px;
    gap: 24px;
    align-items: start;
}

.ks-media-upload__head {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 10px;
}

.ks-media-upload__frame {
    border: 1px solid #e4e6ef;
    border-radius: 8px;
    overflow: hidden;
    background: #f5f8fa;
}

.ks-media-upload__frame img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ks-media-upload__frame--wide {
    aspect-ratio: 3 / 1;
    max-height: 220px;
}

.ks-media-upload__frame--mobile {
    aspect-ratio: 3 / 4;
    width: 100%;
    max-width: 280px;
}

.ks-media-upload__frame--card {
    aspect-ratio: 23 / 15;
    max-width: 460px;
}

.ks-media-upload--card-only {
    max-width: 480px;
}

@media (max-width: 991px) {
    .ks-media-upload-grid {
        grid-template-columns: 1fr;
    }

    .ks-media-upload--mobile .ks-media-upload__frame--mobile {
        max-width: 220px;
    }
}
</style>
