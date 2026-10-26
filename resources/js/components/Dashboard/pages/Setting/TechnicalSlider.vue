<template>
    <div class="card card-flush">
        <div class="card-header">
            <h3 class="card-title">{{ dt.slider.technical_title }}</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-light-warning mb-6">
                {{ dt.slider.technical_hint }}
            </div>

            <div class="row g-4 mb-6">
                <div class="col-lg-3">
                    <label class="form-label">{{ dt.slider.category }}</label>
                    <select
                        v-model="selectedCategory"
                        class="form-select"
                        @change="onCategoryChange"
                    >
                        <option value="">{{ dt.slider.select_category }}</option>
                        <option
                            v-for="cat in categories"
                            :key="cat.slug"
                            :value="cat.slug"
                        >
                            {{ cat.name }}
                        </option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">{{ dt.slider.brand }}</label>
                    <select v-model="selectedBrand" class="form-select" @change="searchProducts">
                        <option value="">{{ dt.slider.all_brands }}</option>
                        <option
                            v-for="brand in brands"
                            :key="brand.id"
                            :value="brand.id"
                        >
                            {{ brand.name }}
                        </option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <label class="form-label">{{ dt.slider.search_label }}</label>
                    <input
                        v-model.trim="searchQuery"
                        type="text"
                        class="form-control"
                        :placeholder="dt.slider.search_ph"
                        @keyup.enter="searchProducts"
                    />
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" @click="searchProducts">
                        {{ dt.slider.search_btn }}
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <button class="btn btn-light-primary me-2" @click="addAllResults">
                    {{ dt.slider.add_all }}
                </button>
                <button class="btn btn-light-danger" @click="clearSelected">
                    {{ dt.slider.clear_selected }}
                </button>
            </div>
            <div class="row g-4 mb-6">
                <div class="col-12 col-lg-8">
                    <label class="form-label">{{ dt.slider.cta_link }}</label>
                    <input
                        v-model.trim="ctaLink"
                        type="url"
                        class="form-control"
                        :placeholder="dt.slider.cta_ph"
                    />
                    <div class="form-text">
                        {{ dt.slider.technical_cta_hint }}
                    </div>
                </div>
            </div>

            <div class="alert alert-light-info mb-6">
                {{ dt.slider.hint }}
            </div>

            <div class="row g-4 mb-8">
                <div
                    v-for="item in searchResults"
                    :key="item.sku"
                    class="col-12 col-md-6 col-xl-4"
                >
                    <div class="border rounded p-3 d-flex gap-3 align-items-center">
                        <img :src="item.image" :alt="item.name" class="ks-thumb" />
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate">{{ item.name }}</div>
                            <div class="text-muted fs-8">{{ dt.slider.sku }}: {{ item.sku }}</div>
                        </div>
                        <button
                            class="btn btn-sm"
                            :class="isSelected(item.sku) ? 'btn-danger' : 'btn-primary'"
                            @click="toggleSelect(item)"
                        >
                            {{ isSelected(item.sku) ? dt.common.remove : dt.slider.add }}
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="!searchResults.length" class="text-muted mb-8">
                {{ dt.slider.no_products }}
            </div>

            <h4 class="mb-4">{{ dt.slider.selected_count }} ({{ selectedProducts.length }})</h4>
            <div class="row g-4">
                <div
                    v-for="item in selectedProducts"
                    :key="item.sku"
                    class="col-12 col-md-6 col-xl-4"
                >
                    <div class="border rounded p-3 d-flex gap-3 align-items-center">
                        <img :src="item.image" :alt="item.name" class="ks-thumb" />
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate">{{ item.name }}</div>
                            <div class="text-muted fs-8">{{ dt.slider.sku }}: {{ item.sku }}</div>
                            <div class="text-muted fs-8">
                                {{ priceText(item.price_current, item.currency) }}
                            </div>
                        </div>
                        <button class="btn btn-sm btn-danger" @click="removeSelected(item.sku)">
                            {{ dt.common.remove }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <button class="btn btn-success" :disabled="isSaving" @click="saveSelected">
                    {{ isSaving ? dt.common.saving : dt.slider.save }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "TechnicalSliderSettings",
    data() {
        return {
            searchQuery: "",
            selectedCategory: "",
            selectedBrand: "",
            categories: [],
            brands: [],
            searchResults: [],
            selectedProducts: [],
            ctaLink: "",
            isSaving: false,
        };
    },
    mounted() {
        this.fetchCategories();
        this.fetchBrands();
        this.fetchSelected();
    },
    methods: {
        priceText(value, currency = "EUR") {
            const amount = Number(value || 0);
            return `${amount.toFixed(2)} ${currency}`;
        },
        normalizeProduct(row) {
            const brandValue = row?.brand ?? null;
            const brandString =
                typeof brandValue === "string"
                    ? brandValue
                    : brandValue && typeof brandValue === "object"
                    ? brandValue.name || brandValue.id || "KEEPERsport"
                    : "KEEPERsport";

            return {
                sku: row.sku || "",
                slug: row.slug || "",
                name: row.name || "",
                image: row.image || "",
                brand: brandString,
                price_current: Number(row.price?.current ?? row.price_current ?? 0),
                price_old:
                    row.price?.old !== undefined && row.price?.old !== null
                        ? Number(row.price.old)
                        : row.price_old !== undefined && row.price_old !== null
                        ? Number(row.price_old)
                        : null,
                currency: row.price?.currency || row.currency || "EUR",
            };
        },
        async fetchCategories() {
            const { data } = await axios.get("TechnicalSlider/getCategories");
            this.categories = Array.isArray(data?.items) ? data.items : [];
        },
        async fetchBrands() {
            const locale = this.dashboardLocale;
            const { data } = await axios.get("TechnicalSlider/getBrands", {
                params: { locale },
            });
            this.brands = Array.isArray(data?.items) ? data.items : [];
        },
        onCategoryChange() {
            this.searchProducts();
        },
        async fetchSelected() {
            const { data } = await axios.get("TechnicalSlider/getSelected");
            const payload = data?.items || {};
            const rows = Array.isArray(payload)
                ? payload
                : Array.isArray(payload?.products)
                ? payload.products
                : [];
            this.selectedProducts = rows.map(this.normalizeProduct);
            this.ctaLink = Array.isArray(payload) ? "" : payload.cta_link || "";
        },
        async searchProducts() {
            const locale = this.dashboardLocale;
            const { data } = await axios.get("TechnicalSlider/searchProducts", {
                params: {
                    q: this.searchQuery || undefined,
                    brand: this.selectedBrand || undefined,
                    category: this.selectedCategory || undefined,
                    locale,
                    per_page: 48,
                },
            });

            const payload = data?.items || {};
            const rows = Array.isArray(payload?.items) ? payload.items : [];
            this.searchResults = rows.map(this.normalizeProduct);
        },
        isSelected(sku) {
            return this.selectedProducts.some((p) => p.sku === sku);
        },
        toggleSelect(item) {
            if (this.isSelected(item.sku)) {
                this.removeSelected(item.sku);
                return;
            }
            this.selectedProducts.push(item);
        },
        addAllResults() {
            this.searchResults.forEach((item) => {
                if (!this.isSelected(item.sku)) {
                    this.selectedProducts.push(item);
                }
            });
        },
        removeSelected(sku) {
            this.selectedProducts = this.selectedProducts.filter(
                (item) => item.sku !== sku
            );
        },
        clearSelected() {
            this.selectedProducts = [];
        },
        async saveSelected() {
            this.isSaving = true;
            try {
                await axios.post("TechnicalSlider/saveSelected", {
                    products: this.selectedProducts,
                    cta_link: this.ctaLink,
                });
                Swal.fire({
                    text: this.dt.slider.saved_ok,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: this.dt.common.ok,
                    customClass: { confirmButton: "btn btn-primary" },
                });
            } finally {
                this.isSaving = false;
            }
        },
    },
};
</script>

<style scoped>
.ks-thumb {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 6px;
    background: #fafafa;
}

.min-w-0 {
    min-width: 0;
}
</style>
