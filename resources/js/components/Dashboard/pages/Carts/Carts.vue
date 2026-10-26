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
                    <span class="badge badge-light-primary fs-7">
                        {{ dt.carts.pending_hint }}
                    </span>
                </div>
            </div>

            <div class="card-body py-4">
                <div v-if="loading" class="text-muted py-10 text-center">
                    {{ dt.common.please_wait }}
                </div>

                <div v-else-if="!items.length" class="text-muted py-10 text-center">
                    {{ dt.carts.empty }}
                </div>

                <table v-else class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>#</th>
                            <th>{{ dt.carts.customer }}</th>
                            <th>{{ dt.carts.items_count }}</th>
                            <th>{{ dt.carts.subtotal }}</th>
                            <th>{{ dt.carts.last_activity }}</th>
                            <th class="text-end">{{ dt.common.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        <tr v-for="(cart, index) in items" :key="cart.id">
                            <td>{{ rowNumber(index) }}</td>
                            <td>
                                <div v-if="cart.user" class="d-flex flex-column">
                                    <span class="text-gray-800 fw-bold">{{ cart.user.name }}</span>
                                    <span class="text-muted fs-7">{{ cart.user.email }}</span>
                                </div>
                                <div v-else class="d-flex flex-column">
                                    <span class="badge badge-light-warning w-fit">{{ dt.carts.guest }}</span>
                                    <span class="text-muted fs-8 mt-1">{{ shortToken(cart.cart_token) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-light-info">{{ cart.items_count }}</span>
                            </td>
                            <td lang="en" dir="ltr">{{ formatMoney(cart.subtotal, cart.currency) }}</td>
                            <td>{{ formatDate(cart.last_activity_at || cart.updated_at) }}</td>
                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light-primary me-2"
                                    @click="openDetails(cart)"
                                >
                                    {{ dt.common.view }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light-danger"
                                    @click="confirmDelete(cart)"
                                >
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

        <div class="modal fade" id="kt_modal_cart_details" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold">{{ dt.carts.details_title }}</h2>
                        <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" @click="closeModal">
                            <i class="ki-duotone ki-cross fs-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </button>
                    </div>
                    <div v-if="selectedCart" class="modal-body px-5 pb-7">
                        <div class="row g-4 mb-6">
                            <div class="col-md-6">
                                <div class="text-muted fs-7">{{ dt.carts.customer }}</div>
                                <div v-if="selectedCart.user">
                                    <strong>{{ selectedCart.user.name }}</strong>
                                    <div class="text-muted">{{ selectedCart.user.email }}</div>
                                </div>
                                <div v-else>
                                    <span class="badge badge-light-warning">{{ dt.carts.guest }}</span>
                                    <div class="text-muted fs-8 mt-1">{{ selectedCart.cart_token }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted fs-7">{{ dt.carts.items_count }}</div>
                                <strong>{{ selectedCart.items_count }}</strong>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted fs-7">{{ dt.carts.subtotal }}</div>
                                <strong lang="en" dir="ltr">
                                    {{ formatMoney(selectedCart.subtotal, selectedCart.currency) }}
                                </strong>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle">
                                <thead>
                                    <tr class="text-muted fw-bold fs-7">
                                        <th>{{ dt.settings.card_image }}</th>
                                        <th>{{ dt.carts.product }}</th>
                                        <th>{{ dt.carts.qty }}</th>
                                        <th>{{ dt.carts.price }}</th>
                                        <th>{{ dt.carts.line_total }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="line in selectedCart.items || []" :key="line.id">
                                        <td>
                                            <img
                                                :src="line.image || '/assets/media/svg/files/blank-image.svg'"
                                                alt=""
                                                class="ks-cart-line-thumb"
                                            />
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ line.title }}</div>
                                            <div v-if="line.subtitle" class="text-muted fs-7">{{ line.subtitle }}</div>
                                            <div v-if="line.sku" class="text-muted fs-8">SKU: {{ line.sku }}</div>
                                        </td>
                                        <td lang="en" dir="ltr">{{ line.qty }}</td>
                                        <td lang="en" dir="ltr">{{ formatMoney(line.price, selectedCart.currency) }}</td>
                                        <td lang="en" dir="ltr">
                                            {{ formatMoney(Number(line.price) * Number(line.qty), selectedCart.currency) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Pagination from "../../layout/pagination.vue";

export default {
    name: "ShoppingCartsAdmin",
    components: { Pagination },
    data() {
        return {
            items: [],
            loading: false,
            searchQuery: "",
            currentPage: 1,
            totalItems: 0,
            selectedCart: null,
            searchTimer: null,
        };
    },
    mounted() {
        this.fetchItems(1);
    },
    methods: {
        rowNumber(index) {
            return (this.currentPage - 1) * 15 + index + 1;
        },
        shortToken(token) {
            const value = String(token || "");
            if (value.length <= 12) return value;
            return `${value.slice(0, 8)}…`;
        },
        formatMoney(value, currency = "EUR") {
            const amount = Number(value || 0);
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: currency || "EUR",
                maximumFractionDigits: 2,
            }).format(amount);
        },
        formatDate(value) {
            if (!value) return "—";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString(this.dashboardLocale === "ar" ? "ar" : "en");
        },
        onSearch() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => {
                this.fetchItems(1);
            }, 300);
        },
        async fetchItems(page = 1) {
            this.loading = true;
            this.currentPage = page;
            try {
                const { data } = await axios.get("Carts/getAllItems", {
                    params: {
                        pagination: 1,
                        page,
                        search: this.searchQuery || undefined,
                    },
                });
                const payload = data?.items;
                this.items = Array.isArray(payload?.data) ? payload.data : [];
                this.totalItems = Number(payload?.total || this.items.length || 0);
            } finally {
                this.loading = false;
            }
        },
        openDetails(cart) {
            this.selectedCart = cart;
            $("#kt_modal_cart_details").modal("show");
        },
        closeModal() {
            $("#kt_modal_cart_details").modal("hide");
            this.selectedCart = null;
        },
        confirmDelete(cart) {
            Swal.fire({
                text: this.dt.carts.delete_confirm,
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
                if (!result.isConfirmed) return;
                await axios.post("Carts/deleteItem", { id: cart.id });
                Swal.fire({
                    text: this.dt.carts.deleted_ok,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: this.dt.common.ok,
                    customClass: { confirmButton: "btn btn-primary" },
                });
                await this.fetchItems(this.currentPage);
            });
        },
    },
};
</script>

<style scoped>
.ks-cart-line-thumb {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #eee;
}
.w-fit {
    width: fit-content;
}
</style>
