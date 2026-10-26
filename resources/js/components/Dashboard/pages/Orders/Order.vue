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
              @input="searchItems"
              type="text"
              class="form-control form-control-solid w-250px ps-13"
              :placeholder="dt.common.search_item"
            />
          </div>
        </div>
        <div class="card-toolbar">
          <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base" v-if="selectedItems.length === 0">
            <button type="button" class="btn btn-light-primary me-3" :disabled="isExporting" @click="exportOrders">
              <i class="ki-duotone ki-exit-up fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
              </i>{{ isExporting ? "جاري التصدير..." : dt.common.export }}
            </button>
          </div>
          <div class="d-flex justify-content-end align-items-center justing" v-if="selectedItems.length != 0">
            <div class="fw-bold me-5">
              <span class="me-2">{{ selectedItems.length }}</span>{{ dt.common.selected }}
            </div>
            <button type="button" class="btn btn-light-danger margin-5" @click="actionSelected('delete')">{{ dt.common.delete_selected }}</button>
          </div>
        </div>
      </div>

      <div class="card-body py-4">
        <div v-if="isLoading">{{ dt.common.please_wait }}</div>
        <table v-else class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
          <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
              <th class="min-w-70px">الصورة</th>
              <th class="min-w-125px">الرقم</th>
              <th class="min-w-150px">العميل</th>
              <th class="min-w-120px">الحالة</th>
              <th class="min-w-90px">العملة</th>
              <th class="min-w-110px">الإجمالي</th>
              <th class="min-w-110px">تكلفة الشحن</th>
              <th class="min-w-120px">تاريخ الطلب</th>
              <th class="text-center min-w-100px">{{ dt.common.actions }}</th>
            </tr>
          </thead>
          <tbody class="text-gray-600 fw-semibold">
            <tr
              v-for="item in items"
              :key="item.id"
              :class="{ 'highlight-red': item.id == selectedOrderId }"
            >
              <td>
                <img
                  v-if="orderProductImage(item)"
                  :src="orderProductImage(item)"
                  alt=""
                  class="ks-order-thumb"
                />
                <div v-else class="ks-order-thumb ks-order-thumb--empty">—</div>
              </td>
              <td>
                <span class="text-gray-800 fw-bold" lang="en" dir="ltr">{{ item.order_number }}</span>
              </td>
              <td>
                <div class="d-flex flex-column">
                  <span class="text-gray-800 fw-semibold">{{ customerNameOf(item) }}</span>
                  <span class="text-muted fs-7" lang="en" dir="ltr">{{ customerMobileOf(item) }}</span>
                </div>
              </td>
              <td>
                <span :class="orderStatusClass(item.order_status)">
                  <i :class="orderStatusIcon(item.order_status)" class="me-1"></i>
                  {{ orderStatusLabel(item.order_status) }}
                </span>
              </td>
              <td>
                <span lang="en" dir="ltr">{{ currencyLabel(item) }}</span>
              </td>
              <td>
                <span lang="en" dir="ltr">{{ money(totalOf(item)) }}</span>
              </td>
              <td>
                <span lang="en" dir="ltr">{{ money(shippingOf(item)) }}</span>
              </td>
              <td>{{ orderDateLabel(item) }}</td>
              <td class="text-end">
                <div class="d-flex flex-center rounded p-4 h-80px mb-1 overlay">
                  <div class="overlay-wrapper text-gray-600">
                    <i
                      @click="getModalEdit(item)"
                      class="ki-duotone ki-exit-right-corner fs-2"
                      title="تفاصيل الطلب"
                      style="cursor: pointer"
                    >
                      <span class="path1"></span>
                      <span class="path2"></span>
                    </i>
                    <i
                      @click="openInvoice(item)"
                      class="fas fa-file-invoice"
                      title="فاتورة كيبرسبورت"
                      style="padding: 5px; cursor: pointer"
                    ></i>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <pagination :totalItems="totalItems" :currentPage="currentPage" @page-changed="fetchItems"></pagination>
      </div>
    </div>
  </div>

  <div class="modal fade" id="kt_modal_add_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-950px">
      <div class="modal-content">
        <div class="modal-header" id="kt_modal_add_user_header">
          <h2 class="fw-bold">تفاصيل طلب كيبرسبورت</h2>
          <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-users-modal-action="close" @click="closeModal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>

        <div class="modal-body px-5 my-7" v-if="itemCurrent">
          <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_user_scroll">
            <div class="row g-4 mb-4">
              <div class="col-md-6">
                <div class="border border-dashed rounded p-4 h-100">
                  <label class="form-label fw-bold">حالة الطلب</label>
                  <select v-model="statusForm.order_status" class="form-select form-select-solid">
                    <option v-for="opt in orderStatusOptions" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border border-dashed rounded p-4 h-100">
                  <label class="form-label fw-bold">حالة الدفع</label>
                  <select v-model="statusForm.payment_status" class="form-select form-select-solid">
                    <option value="paid">مدفوع</option>
                    <option value="unpaid">غير مدفوعة</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-4">
              <div class="col-md-4">
                <div class="border border-dashed rounded p-4 h-100">
                  <div class="text-muted fs-7 mb-2">العميل</div>
                  <div class="fw-bold fs-5 mb-1">{{ customerNameOf(itemCurrent) }}</div>
                  <div class="text-gray-700" v-if="customerEmailOf(itemCurrent)">{{ customerEmailOf(itemCurrent) }}</div>
                  <div class="text-gray-700" lang="en" dir="ltr">{{ customerMobileOf(itemCurrent) }}</div>
                  <div class="text-muted mt-2" v-if="!itemCurrent.user">ضيف / Guest checkout</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="border border-dashed rounded p-4 h-100">
                  <div class="text-muted fs-7 mb-2">عنوان التوصيل</div>
                  <template v-if="hasAddress(itemCurrent)">
                    <div class="fw-bold">{{ addressOf(itemCurrent).full_name }}</div>
                    <div>{{ addressLine(itemCurrent) }}</div>
                    <div lang="en" dir="ltr">
                      {{ addressOf(itemCurrent).country_code || "" }}
                    </div>
                    <div v-if="nationalIdOf(itemCurrent)" lang="en" dir="ltr">
                      الرقم الوطني: {{ nationalIdOf(itemCurrent) }}
                    </div>
                    <div class="text-muted mt-1" v-if="addressOf(itemCurrent).notes">
                      ملاحظات: {{ addressOf(itemCurrent).notes }}
                    </div>
                  </template>
                  <div v-else class="text-muted">—</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="border border-dashed rounded p-4 h-100">
                  <div class="text-muted fs-7 mb-2">الدفع والشحن</div>
                  <div>المرجع: <strong lang="en" dir="ltr">{{ itemCurrent.track_id }}</strong></div>
                  <div>رقم الفاتورة: <strong lang="en" dir="ltr">{{ itemCurrent.order_number }}</strong></div>
                  <div>حالة الطلب: <span :class="orderStatusClass(itemCurrent.order_status)">{{ orderStatusLabel(itemCurrent.order_status) }}</span></div>
                  <div>حالة الدفع: <span :class="paymentStatusClass(itemCurrent)">{{ paymentStatusLabel(itemCurrent) }}</span></div>
                  <div>النوع: <strong>{{ typeLabel(itemCurrent) }}</strong></div>
                  <div v-if="shippingLabel(itemCurrent)">الشحن: <strong>{{ shippingLabel(itemCurrent) }}</strong></div>
                  <div v-if="couponCode(itemCurrent)">كوبون: <strong lang="en" dir="ltr">{{ couponCode(itemCurrent) }}</strong></div>
                  <div>التاريخ: {{ itemCurrent.created_at }}</div>
                </div>
              </div>
            </div>

            <div class="card mb-4" v-if="cartItemsOf(itemCurrent).length">
              <div class="card-header min-h-40px">
                <h3 class="card-title fs-6">منتجات الطلب</h3>
              </div>
              <div class="card-body py-3">
                <div
                  v-for="(row, idx) in cartItemsOf(itemCurrent)"
                  :key="idx"
                  class="d-flex justify-content-between align-items-start border-bottom py-3 gap-3"
                >
                  <div class="d-flex align-items-start gap-3">
                    <img
                      v-if="cartRowImage(row)"
                      :src="cartRowImage(row)"
                      alt=""
                      class="ks-order-thumb"
                    />
                    <div>
                      <div class="fw-bold">{{ row.title || row.sku }}</div>
                      <div class="text-muted fs-7" v-if="row.sku" lang="en" dir="ltr">SKU: {{ row.sku }}</div>
                    </div>
                  </div>
                  <div class="text-end" lang="en" dir="ltr">
                    <div>×{{ row.qty || 1 }}</div>
                    <div class="fw-bold">{{ Number(row.price || 0).toFixed(2) }} SAR</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-4" v-else-if="itemCurrent.ad">
              <div class="card-body d-flex align-items-center gap-4">
                <img
                  v-if="itemCurrent?.ad?.image_basic?.url"
                  :src="itemCurrent.ad.image_basic.url"
                  alt=""
                  style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px"
                />
                <div>
                  <div class="fw-bold">{{ itemCurrent.ad.title }}</div>
                  <div class="text-muted" v-if="itemCurrent.ad.category">
                    {{ itemCurrent.ad.category?.name }} — {{ itemCurrent.ad.brand?.name }}
                  </div>
                </div>
              </div>
            </div>

            <div class="border rounded p-4 bg-light">
              <div class="d-flex justify-content-between mb-2">
                <span>المجموع الفرعي</span>
                <strong lang="en" dir="ltr">{{ money(subtotalOf(itemCurrent)) }} SAR</strong>
              </div>
              <div class="d-flex justify-content-between mb-2" v-if="discountOf(itemCurrent) > 0">
                <span>الخصم</span>
                <strong lang="en" dir="ltr">- {{ money(discountOf(itemCurrent)) }} SAR</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>الشحن</span>
                <strong lang="en" dir="ltr">{{ money(shippingOf(itemCurrent)) }} SAR</strong>
              </div>
              <div class="d-flex justify-content-between mb-2" v-if="taxOf(itemCurrent) > 0">
                <span>الضريبة ({{ vatPercentOf(itemCurrent) }}%)</span>
                <strong lang="en" dir="ltr">{{ money(taxOf(itemCurrent)) }} SAR</strong>
              </div>
              <div class="d-flex justify-content-between mb-2" v-else>
                <span>الضريبة</span>
                <strong>غير مطبّقة</strong>
              </div>
              <div class="d-flex justify-content-between border-top pt-3 fs-5">
                <span class="fw-bold">الإجمالي</span>
                <strong lang="en" dir="ltr">{{ money(totalOf(itemCurrent)) }} SAR</strong>
              </div>
            </div>
          </div>

          <div class="text-center pt-10 d-flex justify-content-center gap-3 flex-wrap">
            <button type="button" class="btn btn-primary" :disabled="isSavingStatus" @click="saveOrderStatus">
              {{ isSavingStatus ? "جاري الحفظ..." : "حفظ الحالات" }}
            </button>
            <button type="button" class="btn btn-warning" @click="openInvoice(itemCurrent)">
              عرض فاتورة كيبرسبورت
            </button>
            <button type="button" class="btn btn-secondary" @click.prevent="closeModal">إغلاق</button>
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
  components: {
    Pagination,
  },
  data() {
    return {
      languages: {},
      totalItems: 0,
      currentPage: 1,
      selectedItems: [],
      selectAll: false,
      searchQuery: "",
      isLoading: false,
      isExporting: false,
      isSavingStatus: false,
      items: [],
      ItemID: null,
      itemCurrent: null,
      selectedOrderId: null,
      statusForm: {
        order_status: "new",
        payment_status: "unpaid",
      },
      orderStatusOptions: [
        { value: "new", label: "طلب جديد" },
        { value: "preparing", label: "جاري التجهيز" },
        { value: "shipped", label: "تم الشحن" },
        { value: "delivered", label: "تم التسليم" },
        { value: "cancelled", label: "ملغي" },
      ],
    };
  },
  mounted() {
    this.fetchItems(this.currentPage);
    this.$nextTick(() => {
      if (this.$route.query.openModal && this.$route.query.orderId) {
        this.selectedOrderId = Number(this.$route.query.orderId);
      }
    });
  },
  methods: {
    statusClass(status) {
      switch ((status || "").toLowerCase()) {
        case "paid":
        case "success":
        case "successful":
          return "badge badge-light-success fw-bold";
        case "unpaid":
        case "failed":
        case "fail":
          return "badge badge-light-danger fw-bold";
        case "pending":
          return "badge badge-light-warning fw-bold";
        default:
          return "badge badge-light-info fw-bold";
      }
    },
    orderStatusClass(status) {
      switch (status) {
        case "preparing":
          return "badge badge-light-warning fw-bold";
        case "shipped":
          return "badge badge-light-success fw-bold";
        case "delivered":
          return "badge badge-light-success fw-bold";
        case "cancelled":
          return "badge badge-light-danger fw-bold";
        default:
          return "badge badge-light-primary fw-bold";
      }
    },
    orderStatusIcon(status) {
      switch (status) {
        case "preparing":
          return "fas fa-sync-alt";
        case "shipped":
          return "fas fa-truck";
        case "delivered":
          return "fas fa-check";
        case "cancelled":
          return "fas fa-times";
        default:
          return "fas fa-plus";
      }
    },
    orderStatusLabel(status) {
      const found = this.orderStatusOptions.find((opt) => opt.value === (status || "new"));
      return found ? found.label : "طلب جديد";
    },
    currencyLabel() {
      return "SAR";
    },
    orderDateLabel(item) {
      return item?.created_at || "—";
    },
    paymentStatusLabel(item) {
      if (item?.is_paid) return "مدفوع";
      const s = String(item?.status || "").toLowerCase();
      if (s.includes("paid") || s.includes("success")) return "مدفوع";
      return "غير مدفوعة";
    },
    paymentStatusClass(item) {
      return this.paymentStatusLabel(item) === "مدفوع"
        ? "badge badge-light-success fw-bold"
        : "badge badge-light-danger fw-bold";
    },
    paymentStatusValue(item) {
      return this.paymentStatusLabel(item) === "مدفوع" ? "paid" : "unpaid";
    },
    typeLabel(item) {
      if (!item) return "—";
      if (item.payment_type === "cart" || item.is_cart_order) return "سلة / Cart";
      return item.payment_type || "—";
    },
    rawOf(item) {
      return item?.raw_response && typeof item.raw_response === "object" ? item.raw_response : {};
    },
    quoteOf(item) {
      const q = this.rawOf(item).quote;
      return q && typeof q === "object" ? q : {};
    },
    contactOf(item) {
      const c = this.rawOf(item).contact;
      return c && typeof c === "object" ? c : {};
    },
    addressOf(item) {
      const a = this.rawOf(item).address;
      return a && typeof a === "object" ? a : {};
    },
    hasAddress(item) {
      const a = this.addressOf(item);
      return !!(a.city || a.street || a.full_name);
    },
    addressLine(item) {
      const a = this.addressOf(item);
      return [a.street, a.district, a.city, a.building].filter(Boolean).join("، ");
    },
    nationalIdOf(item) {
      const a = this.addressOf(item);
      return a.national_id || a.postal_code || "";
    },
    customerNameOf(item) {
      return (
        this.contactOf(item).name ||
        this.addressOf(item).full_name ||
        item?.user?.name ||
        "ضيف"
      );
    },
    customerEmailOf(item) {
      return this.contactOf(item).email || item?.user?.email || "";
    },
    customerMobileOf(item) {
      return this.contactOf(item).mobile || this.addressOf(item).mobile || item?.user?.mobile || "";
    },
    cartItemsOf(item) {
      if (!item) return [];
      if (Array.isArray(item.cart_items) && item.cart_items.length) return item.cart_items;
      const rawItems = this.rawOf(item).items;
      return Array.isArray(rawItems) ? rawItems : [];
    },
    cartItemCount(item) {
      return this.cartItemsOf(item).length;
    },
    orderProductLabel(item) {
      if (!item) return "—";
      if (item.order_title) return item.order_title;
      if (item.ad?.title) return item.ad.title;
      const rows = this.cartItemsOf(item);
      if (rows.length) return rows[0].title || rows[0].sku || "طلب سلة";
      return item.payment_type === "cart" ? "طلب سلة" : "—";
    },
    cartRowImage(row) {
      if (!row || typeof row !== "object") return "";
      const raw =
        row.image ||
        row.image_url ||
        row.thumbnail ||
        row.img ||
        row.picture ||
        "";
      return typeof raw === "string" ? raw.trim() : "";
    },
    orderProductImage(item) {
      if (!item) return "";
      const adImg = item?.ad?.image_basic?.url || item?.ad?.image || "";
      if (adImg) return adImg;
      const rows = this.cartItemsOf(item);
      for (const row of rows) {
        const img = this.cartRowImage(row);
        if (img) return img;
      }
      return "";
    },
    shippingLabel(item) {
      const m = this.quoteOf(item).shipping_method;
      if (!m) return "";
      return m.title_ar || m.title_en || "";
    },
    couponCode(item) {
      return this.quoteOf(item)?.coupon?.code || "";
    },
    money(v) {
      return Number(v || 0).toFixed(2);
    },
    subtotalOf(item) {
      const q = this.quoteOf(item);
      if (q.is_saudi && q.subtotal_incl_vat != null) return Number(q.subtotal_incl_vat);
      if (q.subtotal_excl_vat != null) return Number(q.subtotal_excl_vat);
      return Number(item?.amount || 0);
    },
    discountOf(item) {
      return Number(this.quoteOf(item).discount_amount || 0);
    },
    shippingOf(item) {
      return Number(this.quoteOf(item).shipping_amount || 0);
    },
    taxOf(item) {
      return Number(this.quoteOf(item).tax_amount || 0);
    },
    vatPercentOf(item) {
      return Number(this.quoteOf(item).vat_percent || 0);
    },
    totalOf(item) {
      const q = this.quoteOf(item);
      return Number(q.total != null ? q.total : item?.amount || 0);
    },
    getModalEdit(item) {
      this.ItemID = item.id;
      this.itemCurrent = item;
      this.statusForm = {
        order_status: item.order_status || "new",
        payment_status: this.paymentStatusValue(item),
      };
      $("#kt_modal_add_item").modal("show");
    },
    closeModal() {
      $("#kt_modal_add_item").modal("hide");
      this.ItemID = null;
      this.itemCurrent = null;
      this.statusForm = { order_status: "new", payment_status: "unpaid" };
    },
    async saveOrderStatus() {
      if (!this.itemCurrent?.id) return;
      this.isSavingStatus = true;
      try {
        const response = await axios.post("Orders/updateStatus", {
          id: this.itemCurrent.id,
          order_status: this.statusForm.order_status,
          payment_status: this.statusForm.payment_status,
        });
        const updated = response?.data?.items;
        if (updated) {
          this.itemCurrent = updated;
          const idx = this.items.findIndex((row) => row.id === updated.id);
          if (idx !== -1) this.items.splice(idx, 1, updated);
        }
        Swal.fire({
          text: "تم حفظ الحالات بنجاح",
          icon: "success",
          buttonsStyling: false,
          confirmButtonText: "حسناً",
          customClass: { confirmButton: "btn btn-primary" },
        });
      } catch (error) {
        Swal.fire({
          text: "تعذر حفظ الحالات",
          icon: "error",
          buttonsStyling: false,
          confirmButtonText: "حسناً",
          customClass: { confirmButton: "btn btn-primary" },
        });
      } finally {
        this.isSavingStatus = false;
      }
    },
    async exportOrders() {
      this.isExporting = true;
      try {
        const response = await axios.get("Orders/export", {
          params: { search: this.searchQuery },
          responseType: "blob",
        });
        const blob = new Blob([response.data], { type: "text/csv;charset=utf-8;" });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = `keepersport-orders-${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      } catch (error) {
        Swal.fire({
          text: "تعذر تصدير الطلبات",
          icon: "error",
          buttonsStyling: false,
          confirmButtonText: "حسناً",
          customClass: { confirmButton: "btn btn-primary" },
        });
      } finally {
        this.isExporting = false;
      }
    },
    searchItems() {
      this.fetchItems(1);
    },
    async fetchItems(page) {
      this.currentPage = page;
      this.isLoading = true;
      try {
        const response = await axios.get("Orders/getAllItems", {
          params: {
            search: this.searchQuery,
            pagination: 1,
            page,
          },
        });
        this.items = response.data.items.data;
        this.totalItems = response.data.items.total;
        this.languages = this.$store.state.languages;
      } catch (error) {
        Swal.fire({
          text: String(error),
          icon: "error",
          buttonsStyling: false,
          confirmButtonText: "حسناً",
          customClass: { confirmButton: "btn btn-primary" },
        });
      } finally {
        this.isLoading = false;
      }
    },
    openInvoice(item) {
      if (!item?.track_id) return;
      const url = `/${this.$route.params.locale}/invoice/${item.track_id}?from=dashboard`;
      window.open(url, "_blank");
    },
  },
};
</script>

<style type="text/css" scoped>
.justing {
  gap: 7px;
}
.highlight-red {
  background-color: #e4a92a9c;
}
.ks-order-thumb {
  width: 52px;
  height: 52px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e5e5e5;
  background: #f5f5f5;
  display: block;
}
.ks-order-thumb--empty {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #999;
  font-size: 12px;
}
</style>
