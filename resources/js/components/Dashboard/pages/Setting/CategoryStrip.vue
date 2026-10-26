<template>
    <div class="content flex-column-fluid" id="kt_content">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold mb-0">{{ dt.category_strip.title }}</h3>
                </div>
                <div class="card-toolbar">
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="openCreate"
                    >
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ dt.common.add_new }}
                    </button>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="alert alert-light-info mb-6">
                    {{ dt.category_strip.hint }}
                </div>

                <div v-if="loading" class="text-center text-muted py-10">
                    <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                    {{ dt.common.loading }}
                </div>

                <div v-else-if="items.length === 0" class="text-center text-muted py-10">
                    {{ dt.category_strip.empty }}
                </div>

                <table v-else class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-50px">#</th>
                            <th class="min-w-100px">{{ dt.settings.card_image }}</th>
                            <th class="min-w-150px">{{ dt.settings.title_ar }}</th>
                            <th class="min-w-150px">{{ dt.settings.title_en }}</th>
                            <th class="min-w-200px">{{ dt.settings.link_url }}</th>
                            <th class="text-end min-w-100px">{{ dt.common.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        <tr v-for="(item, index) in items" :key="item.id">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <div class="ks-strip-thumb">
                                    <img
                                        :src="mediaPreviewUrl(item.image)"
                                        :alt="item.title_en || item.title_ar"
                                    />
                                </div>
                            </td>
                            <td>{{ item.title_ar || "—" }}</td>
                            <td>{{ item.title_en || "—" }}</td>
                            <td class="text-break">{{ item.link || "—" }}</td>
                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light-primary me-2"
                                    @click="openEdit(item)"
                                >
                                    {{ dt.common.edit }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light-danger"
                                    @click="confirmDelete(item)"
                                >
                                    {{ dt.common.delete }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="kt_modal_category_strip" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold">
                            {{ editingId ? dt.category_strip.edit_title : dt.category_strip.add_title }}
                        </h2>
                        <button
                            type="button"
                            class="btn btn-icon btn-sm btn-active-icon-primary"
                            @click="closeModal"
                        >
                            <i class="ki-duotone ki-cross fs-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </button>
                    </div>

                    <form class="modal-body px-5 my-7" @submit.prevent="submitForm">
                        <div class="mb-5">
                            <label class="fw-semibold fs-6 mb-2">{{ dt.settings.title_ar }}</label>
                            <input
                                v-model="form.title_ar"
                                type="text"
                                class="form-control form-control-solid"
                            />
                        </div>

                        <div class="mb-5">
                            <label class="fw-semibold fs-6 mb-2">{{ dt.settings.title_en }}</label>
                            <input
                                v-model="form.title_en"
                                type="text"
                                class="form-control form-control-solid"
                            />
                        </div>

                        <div class="mb-5">
                            <label class="fw-semibold fs-6 mb-2">{{ dt.settings.link_url }}</label>
                            <input
                                v-model="form.link"
                                type="text"
                                class="form-control form-control-solid"
                                :placeholder="dt.category_strip.link_ph"
                            />
                            <div class="form-text">{{ dt.category_strip.link_hint }}</div>
                        </div>

                        <div class="mb-5">
                            <label class="fw-semibold fs-6 mb-2">
                                {{ dt.settings.card_image }}
                                <span v-if="!editingId" class="text-danger">*</span>
                            </label>
                            <div class="ks-media-upload__frame ks-media-upload__frame--card mb-3">
                                <img
                                    :src="mediaPreviewUrl(form.image_preview)"
                                    :alt="form.title_en || form.title_ar || 'preview'"
                                />
                            </div>
                            <label class="btn btn-sm btn-light-primary mb-0">
                                {{ dt.settings.choose_image }}
                                <input
                                    type="file"
                                    class="d-none"
                                    accept=".png,.jpg,.jpeg,.webp"
                                    @change="onImageChange"
                                />
                            </label>
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

const emptyForm = () => ({
    title_ar: "",
    title_en: "",
    link: "",
    image_preview: "",
    image_file: null,
});

export default {
    name: "CategoryStripSettings",
    data() {
        return {
            items: [],
            loading: false,
            saving: false,
            editingId: null,
            form: emptyForm(),
        };
    },
    mounted() {
        this.fetchItems();
    },
    methods: {
        mediaPreviewUrl(value) {
            const src = String(value || "").trim();
            return src || "/assets/media/svg/files/blank-image.svg";
        },
        async fetchItems() {
            this.loading = true;
            try {
                const { data } = await axios.get("CategoryStrip/getAll");
                this.items = Array.isArray(data?.items) ? data.items : [];
            } finally {
                this.loading = false;
            }
        },
        openCreate() {
            this.editingId = null;
            this.form = emptyForm();
            $("#kt_modal_category_strip").modal("show");
        },
        openEdit(item) {
            this.editingId = item.id;
            this.form = {
                title_ar: item.title_ar || "",
                title_en: item.title_en || "",
                link: item.link || "",
                image_preview: item.image || "",
                image_file: null,
            };
            $("#kt_modal_category_strip").modal("show");
        },
        closeModal() {
            $("#kt_modal_category_strip").modal("hide");
            this.editingId = null;
            this.form = emptyForm();
        },
        onImageChange(e) {
            const file = e.target.files?.[0];
            this.form.image_file = file || null;
            if (file) {
                this.form.image_preview = URL.createObjectURL(file);
            }
        },
        async submitForm() {
            if (!this.editingId && !this.form.image_file) {
                Swal.fire({
                    text: this.dt.category_strip.image_required,
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: this.dt.common.ok,
                    customClass: { confirmButton: "btn btn-primary" },
                });
                return;
            }

            this.saving = true;
            try {
                const form = new FormData();
                form.append("title_ar", this.form.title_ar || "");
                form.append("title_en", this.form.title_en || "");
                form.append("link", this.form.link || "");
                if (this.form.image_file) {
                    form.append("image", this.form.image_file);
                }

                if (this.editingId) {
                    form.append("id", this.editingId);
                    await axios.post("CategoryStrip/editItem", form, {
                        headers: { "content-type": "multipart/form-data" },
                    });
                } else {
                    await axios.post("CategoryStrip/createItem", form, {
                        headers: { "content-type": "multipart/form-data" },
                    });
                }

                Swal.fire({
                    text: this.dt.category_strip.saved_ok,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: this.dt.common.ok,
                    customClass: { confirmButton: "btn btn-primary" },
                });
                this.closeModal();
                await this.fetchItems();
            } finally {
                this.saving = false;
            }
        },
        confirmDelete(item) {
            Swal.fire({
                text: this.dt.category_strip.delete_confirm,
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: this.dt.common.delete,
                cancelButtonText: this.dt.common.cancel,
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-light",
                },
            }).then(async (result) => {
                if (!result.isConfirmed) {
                    return;
                }
                await axios.post("CategoryStrip/deleteItem", { id: item.id });
                Swal.fire({
                    text: this.dt.category_strip.deleted_ok,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: this.dt.common.ok,
                    customClass: { confirmButton: "btn btn-primary" },
                });
                await this.fetchItems();
            });
        },
    },
};
</script>

<style scoped>
.ks-strip-thumb {
    width: 72px;
    height: 54px;
    border-radius: 6px;
    overflow: hidden;
    background: #f5f5f5;
}
.ks-strip-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.ks-media-upload__frame--card {
    width: 100%;
    max-width: 320px;
    aspect-ratio: 4 / 3;
    background: #f5f5f5;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ks-media-upload__frame--card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
