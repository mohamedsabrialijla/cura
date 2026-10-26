<template>
    <div class="content flex-column-fluid" id="kt_content">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title d-flex flex-wrap gap-3 align-items-center">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="form-control form-control-solid w-250px ps-13"
                            :placeholder="dt.common.search_item"
                            @input="onSearch"
                        />
                    </div>
                    <select
                        v-model="categoryFilter"
                        class="form-select form-select-solid w-250px"
                        @change="fetchItems(1)"
                    >
                        <option value="">{{ dt.faq.all_categories }}</option>
                        <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
                            {{ locale === "ar" ? cat.title_ar : cat.title_en }}
                        </option>
                    </select>
                </div>
                <div class="card-toolbar">
                    <div v-if="selectedItems.length === 0" class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" @click="openCreate">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            {{ dt.common.add_new }}
                        </button>
                    </div>
                    <div v-else class="d-flex justify-content-end align-items-center">
                        <span class="fw-bold me-5">
                            <span class="me-2">{{ selectedItems.length }}</span>
                            {{ dt.common.selected }}
                        </span>
                        <button type="button" class="btn btn-danger" @click="deleteSelected">
                            {{ dt.common.delete_selected }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body py-4">
                <div v-if="loading" class="text-muted py-10 text-center">
                    {{ dt.common.please_wait }}
                </div>

                <div v-else-if="!items.length" class="text-muted py-10 text-center">
                    {{ dt.faq.empty }}
                </div>

                <table v-else class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input
                                        v-model="selectAll"
                                        class="form-check-input"
                                        type="checkbox"
                                        @change="toggleAll"
                                    />
                                </div>
                            </th>
                            <th>{{ dt.faq.category }}</th>
                            <th>{{ dt.faq.question }}</th>
                            <th>{{ dt.faq.sort_order }}</th>
                            <th>{{ dt.common.status }}</th>
                            <th class="text-end">{{ dt.common.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        <tr v-for="item in items" :key="item.id">
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input
                                        v-model="selectedItems"
                                        :value="item.id"
                                        class="form-check-input"
                                        type="checkbox"
                                    />
                                </div>
                            </td>
                            <td>{{ locale === "ar" ? item.category_label_ar : item.category_label_en }}</td>
                            <td>
                                <div class="fw-bold">{{ locale === "ar" ? item.question_ar : item.question_en }}</div>
                                <div class="text-muted fs-7 text-truncate" style="max-width: 420px">
                                    {{ locale === "ar" ? item.answer_ar : item.answer_en }}
                                </div>
                            </td>
                            <td lang="en" dir="ltr">{{ item.sort_order ?? 0 }}</td>
                            <td>
                                <span
                                    class="badge"
                                    :class="item.status === 'active' ? 'badge-light-success' : 'badge-light-danger'"
                                >
                                    {{ item.status === "active" ? dt.common.active : dt.common.not_active }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-light-primary me-2" @click="openEdit(item)">
                                    {{ dt.common.edit }}
                                </button>
                                <button type="button" class="btn btn-sm btn-light-danger" @click="confirmDelete(item)">
                                    {{ dt.common.delete }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <pagination
                    v-if="totalItems > 0"
                    :totalItems="totalItems"
                    :currentPage="currentPage"
                    @page-changed="fetchItems"
                />
            </div>
        </div>

        <div class="modal fade" id="kt_modal_faq" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-750px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold">
                            {{ editingId ? dt.faq.edit_title : dt.faq.add_title }}
                        </h2>
                        <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" @click="closeModal">
                            <i class="ki-duotone ki-cross fs-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </button>
                    </div>

                    <form class="modal-body px-5 my-7" @submit.prevent="submitForm">
                        <div class="mb-5">
                            <label class="required fw-semibold fs-6 mb-2">{{ dt.faq.category }}</label>
                            <select v-model="form.category" class="form-select form-select-solid" required>
                                <option disabled value="">{{ dt.faq.select_category }}</option>
                                <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
                                    {{ locale === "ar" ? cat.title_ar : cat.title_en }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-5">
                            <label class="required fw-semibold fs-6 mb-2">{{ dt.faq.question_ar }}</label>
                            <input v-model="form.question_ar" type="text" class="form-control form-control-solid" required />
                        </div>

                        <div class="mb-5">
                            <label class="required fw-semibold fs-6 mb-2">{{ dt.faq.question_en }}</label>
                            <input v-model="form.question_en" type="text" class="form-control form-control-solid" required />
                        </div>

                        <div class="mb-5">
                            <label class="required fw-semibold fs-6 mb-2">{{ dt.faq.answer_ar }}</label>
                            <textarea v-model="form.answer_ar" rows="4" class="form-control form-control-solid" required></textarea>
                        </div>

                        <div class="mb-5">
                            <label class="required fw-semibold fs-6 mb-2">{{ dt.faq.answer_en }}</label>
                            <textarea v-model="form.answer_en" rows="4" class="form-control form-control-solid" required></textarea>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="fw-semibold fs-6 mb-2">{{ dt.faq.sort_order }}</label>
                                <input
                                    v-model.number="form.sort_order"
                                    type="number"
                                    min="0"
                                    step="1"
                                    class="form-control form-control-solid"
                                    lang="en"
                                    dir="ltr"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="required fw-semibold fs-6 mb-2">{{ dt.common.status }}</label>
                                <select v-model="form.status" class="form-select form-select-solid" required>
                                    <option value="active">{{ dt.common.active }}</option>
                                    <option value="not_active">{{ dt.common.not_active }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-center pt-5">
                            <button type="button" class="btn btn-light me-3" @click="closeModal">
                                {{ dt.common.cancel }}
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <span v-if="!saving">{{ dt.common.save }}</span>
                                <span v-else>{{ dt.common.saving }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Pagination from "../../layout/pagination.vue";

const emptyForm = () => ({
    category: "",
    question_ar: "",
    question_en: "",
    answer_ar: "",
    answer_en: "",
    sort_order: 0,
    status: "active",
});

export default {
    name: "FaqsAdmin",
    components: { Pagination },
    data() {
        return {
            items: [],
            categories: [],
            loading: false,
            saving: false,
            searchQuery: "",
            categoryFilter: "",
            searchTimer: null,
            currentPage: 1,
            totalItems: 0,
            selectedItems: [],
            selectAll: false,
            editingId: null,
            form: emptyForm(),
            submitUrl: "Faqs/createItem",
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "ar";
        },
    },
    mounted() {
        this.fetchCategories();
        this.fetchItems(1);
    },
    methods: {
        async fetchCategories() {
            try {
                const { data } = await axios.get("Faqs/categories");
                this.categories = Array.isArray(data?.items) ? data.items : [];
            } catch (_) {
                this.categories = [];
            }
        },
        async fetchItems(page = 1) {
            this.loading = true;
            this.currentPage = page;
            try {
                const { data } = await axios.get("Faqs/getAllItems", {
                    params: {
                        search: this.searchQuery || undefined,
                        category: this.categoryFilter || undefined,
                        pagination: 1,
                        page,
                    },
                });
                const payload = data?.items;
                this.items = Array.isArray(payload?.data) ? payload.data : Array.isArray(payload) ? payload : [];
                this.totalItems = payload?.total ?? this.items.length;
            } finally {
                this.loading = false;
            }
        },
        onSearch() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.fetchItems(1), 300);
        },
        toggleAll() {
            this.selectedItems = this.selectAll ? this.items.map((item) => item.id) : [];
        },
        openCreate() {
            this.editingId = null;
            this.submitUrl = "Faqs/createItem";
            this.form = emptyForm();
            if (this.categoryFilter) this.form.category = this.categoryFilter;
            $("#kt_modal_faq").modal("show");
        },
        openEdit(item) {
            this.editingId = item.id;
            this.submitUrl = "Faqs/editItem";
            this.form = {
                category: item.category || "",
                question_ar: item.question_ar || "",
                question_en: item.question_en || "",
                answer_ar: item.answer_ar || "",
                answer_en: item.answer_en || "",
                sort_order: item.sort_order != null ? Number(item.sort_order) : 0,
                status: item.status || "active",
            };
            $("#kt_modal_faq").modal("show");
        },
        closeModal() {
            $("#kt_modal_faq").modal("hide");
            this.editingId = null;
            this.form = emptyForm();
            this.submitUrl = "Faqs/createItem";
        },
        buildPayload() {
            const payload = {
                category: this.form.category,
                question_ar: String(this.form.question_ar || "").trim(),
                question_en: String(this.form.question_en || "").trim(),
                answer_ar: String(this.form.answer_ar || "").trim(),
                answer_en: String(this.form.answer_en || "").trim(),
                sort_order: this.form.sort_order ?? 0,
                status: this.form.status,
            };
            if (this.editingId) payload.Item_id = this.editingId;
            return payload;
        },
        async submitForm() {
            this.saving = true;
            try {
                const { data } = await axios.post(this.submitUrl, this.buildPayload());
                if (!data?.status) {
                    const msg = data?.message?.errors?.[0] || data?.message || this.dt.common.error;
                    Swal.fire({
                        text: typeof msg === "string" ? msg : this.dt.common.error,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: this.dt.common.ok,
                        customClass: { confirmButton: "btn btn-primary" },
                    });
                    return;
                }
                this.closeModal();
                await this.fetchItems(this.currentPage);
                Swal.fire({
                    text: this.dt.faq.saved_ok,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: this.dt.common.ok,
                    customClass: { confirmButton: "btn btn-primary" },
                });
            } finally {
                this.saving = false;
            }
        },
        confirmDelete(item) {
            Swal.fire({
                text: this.dt.faq.delete_confirm,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: this.dt.common.delete,
                cancelButtonText: this.dt.common.cancel,
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-light",
                },
            }).then((result) => {
                if (result.isConfirmed) this.deleteItem(item.id);
            });
        },
        async deleteItem(id) {
            await axios.delete(`Faqs/deleteItem/${id}`);
            this.selectedItems = this.selectedItems.filter((itemId) => itemId !== id);
            await this.fetchItems(this.currentPage);
        },
        deleteSelected() {
            if (!this.selectedItems.length) return;
            Swal.fire({
                text: this.dt.faq.delete_selected_confirm,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: this.dt.common.delete_selected,
                cancelButtonText: this.dt.common.cancel,
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-light",
                },
            }).then(async (result) => {
                if (!result.isConfirmed) return;
                await axios.delete("Faqs/deleteAllItem", {
                    params: { selectedItems: this.selectedItems.join(",") },
                });
                this.selectedItems = [];
                this.selectAll = false;
                await this.fetchItems(1);
            });
        },
    },
};
</script>
