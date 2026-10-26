<template>
    <div class="content flex-column-fluid" id="kt_content">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
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
                    {{ dt.payment_methods.empty }}
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
                            <th>{{ dt.payment_methods.code }}</th>
                            <th>{{ dt.settings.title_ar }}</th>
                            <th>{{ dt.settings.title_en }}</th>
                            <th>{{ dt.payment_methods.sort_order }}</th>
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
                            <td lang="en" dir="ltr"><code>{{ item.code }}</code></td>
                            <td>{{ item.title_ar }}</td>
                            <td>{{ item.title_en }}</td>
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

        <div class="modal fade" id="kt_modal_payment_method" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold">
                            {{ editingId ? dt.payment_methods.edit_title : dt.payment_methods.add_title }}
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
                            <label class="required fw-semibold fs-6 mb-2">{{ dt.payment_methods.code }}</label>
                            <input
                                v-model="form.code"
                                type="text"
                                class="form-control form-control-solid"
                                lang="en"
                                dir="ltr"
                                required
                                :disabled="!!editingId"
                            />
                            <div class="form-text">{{ dt.payment_methods.code_hint }}</div>
                        </div>

                        <div class="mb-5">
                            <label class="required fw-semibold fs-6 mb-2">{{ dt.settings.title_ar }}</label>
                            <input v-model="form.title_ar" type="text" class="form-control form-control-solid" required />
                        </div>

                        <div class="mb-5">
                            <label class="required fw-semibold fs-6 mb-2">{{ dt.settings.title_en }}</label>
                            <input v-model="form.title_en" type="text" class="form-control form-control-solid" required />
                        </div>

                        <div class="mb-5">
                            <label class="fw-semibold fs-6 mb-2">{{ dt.payment_methods.description_ar }}</label>
                            <input v-model="form.description_ar" type="text" class="form-control form-control-solid" />
                        </div>

                        <div class="mb-5">
                            <label class="fw-semibold fs-6 mb-2">{{ dt.payment_methods.description_en }}</label>
                            <input v-model="form.description_en" type="text" class="form-control form-control-solid" />
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="fw-semibold fs-6 mb-2">{{ dt.payment_methods.sort_order }}</label>
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
    code: "",
    title_ar: "",
    title_en: "",
    description_ar: "",
    description_en: "",
    sort_order: 0,
    status: "active",
});

export default {
    name: "PaymentMethodsAdmin",
    components: { Pagination },
    data() {
        return {
            items: [],
            loading: false,
            saving: false,
            searchQuery: "",
            searchTimer: null,
            currentPage: 1,
            totalItems: 0,
            selectedItems: [],
            selectAll: false,
            editingId: null,
            form: emptyForm(),
            submitUrl: "PaymentMethods/createItem",
        };
    },
    mounted() {
        this.fetchItems(1);
    },
    methods: {
        async fetchItems(page = 1) {
            this.loading = true;
            this.currentPage = page;
            try {
                const { data } = await axios.get("PaymentMethods/getAllItems", {
                    params: {
                        search: this.searchQuery || undefined,
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
            this.submitUrl = "PaymentMethods/createItem";
            this.form = emptyForm();
            $("#kt_modal_payment_method").modal("show");
        },
        openEdit(item) {
            this.editingId = item.id;
            this.submitUrl = "PaymentMethods/editItem";
            this.form = {
                code: item.code || "",
                title_ar: item.title_ar || "",
                title_en: item.title_en || "",
                description_ar: item.description_ar || "",
                description_en: item.description_en || "",
                sort_order: item.sort_order != null ? Number(item.sort_order) : 0,
                status: item.status || "active",
            };
            $("#kt_modal_payment_method").modal("show");
        },
        closeModal() {
            $("#kt_modal_payment_method").modal("hide");
            this.editingId = null;
            this.form = emptyForm();
            this.submitUrl = "PaymentMethods/createItem";
        },
        buildPayload() {
            const payload = {
                code: String(this.form.code || "").trim().toLowerCase(),
                title_ar: String(this.form.title_ar || "").trim(),
                title_en: String(this.form.title_en || "").trim(),
                description_ar: String(this.form.description_ar || "").trim(),
                description_en: String(this.form.description_en || "").trim(),
                sort_order: this.form.sort_order ?? 0,
                status: this.form.status,
            };
            if (this.editingId) {
                payload.Item_id = this.editingId;
            }
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
                    text: this.dt.payment_methods.saved_ok,
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
                text: this.dt.payment_methods.delete_confirm,
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
                if (result.isConfirmed) {
                    this.deleteItem(item.id);
                }
            });
        },
        async deleteItem(id) {
            await axios.delete(`PaymentMethods/deleteItem/${id}`);
            this.selectedItems = this.selectedItems.filter((itemId) => itemId !== id);
            await this.fetchItems(this.currentPage);
        },
        deleteSelected() {
            if (!this.selectedItems.length) return;
            Swal.fire({
                text: this.dt.payment_methods.delete_selected_confirm,
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
                await axios.delete("PaymentMethods/deleteAllItem", {
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
