<template>
    <div class="ks-inv-page" :lang="pageLang" :dir="pageLang === 'ar' ? 'rtl' : 'ltr'">
        <div v-if="isLoading" class="ks-inv-loading">{{ pageLang === "ar" ? "جاري التحميل..." : "Loading..." }}</div>

        <div v-else-if="!invoice" class="ks-inv-loading">
            {{ pageLang === "ar" ? "الفاتورة غير موجودة." : "Invoice not found." }}
        </div>

        <template v-else>
            <div class="ks-inv-sheet" ref="cvContent">
                <header class="ks-inv-head">
                    <div class="ks-inv-brand">
                        <img v-if="brandLogo" :src="brandLogo" alt="KEEPERsport" />
                        <div>
                            <strong>KEEPERsport</strong>
                            <span>{{ pageLang === "ar" ? "فاتورة ضريبية مبسطة" : "Simplified Tax Invoice" }}</span>
                        </div>
                    </div>
                    <div class="ks-inv-meta">
                        <div><span>{{ pageLang === "ar" ? "رقم الفاتورة" : "Invoice No." }}</span><strong lang="en" dir="ltr">{{ invoice.order_number }}</strong></div>
                        <div><span>{{ pageLang === "ar" ? "المرجع" : "Reference" }}</span><strong lang="en" dir="ltr">{{ invoice.track_id }}</strong></div>
                        <div><span>{{ pageLang === "ar" ? "التاريخ" : "Date" }}</span><strong>{{ invoice.created_at }}</strong></div>
                        <div>
                            <span>{{ pageLang === "ar" ? "حالة الدفع" : "Payment" }}</span>
                            <strong :class="isPaid ? 'is-paid' : 'is-unpaid'">{{ paymentStatusLabel }}</strong>
                        </div>
                        <div>
                            <span>{{ pageLang === "ar" ? "حالة الطلب" : "Order status" }}</span>
                            <strong>{{ orderStatusLabel }}</strong>
                        </div>
                    </div>
                </header>

                <section class="ks-inv-parties">
                    <div>
                        <h3>{{ pageLang === "ar" ? "العميل" : "Customer" }}</h3>
                        <p><strong>{{ customerName }}</strong></p>
                        <p v-if="customerEmail">{{ customerEmail }}</p>
                        <p v-if="customerMobile" lang="en" dir="ltr">{{ customerMobile }}</p>
                    </div>
                    <div v-if="hasAddress">
                        <h3>{{ pageLang === "ar" ? "عنوان التوصيل" : "Delivery address" }}</h3>
                        <p>{{ address.full_name }}</p>
                        <p>{{ addressLine }}</p>
                        <p v-if="address.country_code || nationalId">
                            <span v-if="address.country_code" lang="en" dir="ltr">{{ address.country_code }}</span>
                            <span v-if="nationalId" lang="en" dir="ltr"> — {{ pageLang === "ar" ? "الرقم الوطني" : "National ID" }}: {{ nationalId }}</span>
                        </p>
                    </div>
                    <div>
                        <h3>{{ pageLang === "ar" ? "البائع" : "Seller" }}</h3>
                        <p><strong>KEEPERsport Saudi Arabia</strong></p>
                        <p>{{ pageLang === "ar" ? "متجر إلكتروني لمستلزمات حراسة المرمى" : "Goalkeeper equipment e-commerce" }}</p>
                    </div>
                </section>

                <section class="ks-inv-table-wrap">
                    <table class="ks-inv-table" dir="rtl">
                        <colgroup>
                            <col class="ks-inv-col-desc" />
                            <col class="ks-inv-col-num" />
                            <col class="ks-inv-col-num" />
                            <col class="ks-inv-col-num" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="ks-inv-col-desc">{{ pageLang === "ar" ? "الوصف" : "Description" }}</th>
                                <th class="ks-inv-col-num">{{ pageLang === "ar" ? "الكمية" : "Qty" }}</th>
                                <th class="ks-inv-col-num">{{ pageLang === "ar" ? "السعر" : "Price" }}</th>
                                <th class="ks-inv-col-num">{{ pageLang === "ar" ? "المجموع" : "Amount" }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, idx) in lineItems" :key="idx">
                                <td class="ks-inv-col-desc">
                                    <strong>{{ row.title || row.sku || "Item" }}</strong>
                                    <div v-if="row.sku" class="ks-inv-sku">{{ row.sku }}</div>
                                </td>
                                <td class="ks-inv-col-num">{{ row.qty || 1 }}</td>
                                <td class="ks-inv-col-num">{{ money(row.price) }}</td>
                                <td class="ks-inv-col-num">{{ money(lineTotal(row)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="ks-inv-totals">
                    <div class="ks-inv-total-rows">
                        <div><span>{{ pageLang === "ar" ? "المجموع الفرعي" : "Subtotal" }}</span><strong lang="en" dir="ltr">{{ money(subtotal) }}</strong></div>
                        <div v-if="discount > 0"><span>{{ pageLang === "ar" ? "الخصم" : "Discount" }}</span><strong lang="en" dir="ltr">- {{ money(discount) }}</strong></div>
                        <div><span>{{ pageLang === "ar" ? "الشحن" : "Shipping" }}</span><strong lang="en" dir="ltr">{{ money(shipping) }}</strong></div>
                        <div v-if="tax > 0"><span>{{ pageLang === "ar" ? `الضريبة (${vatPercent}%)` : `VAT (${vatPercent}%)` }}</span><strong lang="en" dir="ltr">{{ money(tax) }}</strong></div>
                        <div v-else><span>{{ pageLang === "ar" ? "الضريبة" : "Tax" }}</span><strong>{{ pageLang === "ar" ? "غير مطبّقة" : "Not applied" }}</strong></div>
                        <div class="ks-inv-grand"><span>{{ pageLang === "ar" ? "الإجمالي" : "Total" }}</span><strong lang="en" dir="ltr">{{ money(total) }} SAR</strong></div>
                    </div>
                    <div class="ks-inv-payinfo">
                        <p>{{ pageLang === "ar" ? "طريقة الدفع" : "Payment method" }}: <strong>{{ paymentMethodLabel }}</strong></p>
                        <p v-if="shippingLabel">{{ pageLang === "ar" ? "الشحن" : "Shipping" }}: <strong>{{ shippingLabel }}</strong></p>
                        <p v-if="couponCode">{{ pageLang === "ar" ? "كوبون" : "Coupon" }}: <strong lang="en" dir="ltr">{{ couponCode }}</strong></p>
                        <div class="ks-inv-qr" v-if="invoice.barCode" v-html="invoice.barCode"></div>
                    </div>
                </section>

                <footer class="ks-inv-foot">
                    <p>KEEPERsport — {{ pageLang === "ar" ? "شكراً لتسوقك معنا" : "Thank you for shopping with us" }}</p>
                </footer>
            </div>

            <div class="ks-inv-actions">
                <button type="button" class="ks-inv-btn" :disabled="isLoading" @click="exportToPDF">
                    {{ pageLang === "ar" ? "تحميل PDF" : "Download PDF" }}
                </button>
                <router-link v-if="backLink" :to="backLink" class="ks-inv-link">
                    {{ backLinkLabel }}
                </router-link>
            </div>
        </template>
    </div>
</template>

<script>
import axios from "axios";
import html2pdf from "html2pdf.js";

export default {
    name: "InvoicePdf",
    data() {
        return {
            invoice: null,
            isLoading: false,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        raw() {
            return this.invoice?.raw_response && typeof this.invoice.raw_response === "object"
                ? this.invoice.raw_response
                : {};
        },
        quote() {
            return this.raw.quote && typeof this.raw.quote === "object" ? this.raw.quote : {};
        },
        contact() {
            return this.raw.contact && typeof this.raw.contact === "object" ? this.raw.contact : {};
        },
        address() {
            return this.raw.address && typeof this.raw.address === "object" ? this.raw.address : {};
        },
        hasAddress() {
            return !!(this.address.city || this.address.street || this.address.full_name);
        },
        addressLine() {
            return [this.address.street, this.address.district, this.address.city, this.address.building]
                .filter(Boolean)
                .join(", ");
        },
        nationalId() {
            return this.address.national_id || this.address.postal_code || "";
        },
        customerName() {
            return this.contact.name || this.address.full_name || this.invoice?.user?.name || "—";
        },
        customerEmail() {
            return this.contact.email || this.invoice?.user?.email || "";
        },
        customerMobile() {
            return this.contact.mobile || this.address.mobile || this.invoice?.user?.mobile || "";
        },
        lineItems() {
            if (Array.isArray(this.invoice?.cart_items) && this.invoice.cart_items.length) {
                return this.invoice.cart_items;
            }
            if (Array.isArray(this.raw.items) && this.raw.items.length) {
                return this.raw.items;
            }
            if (this.invoice?.ad?.title) {
                return [{ title: this.invoice.ad.title, qty: 1, price: this.invoice.amount }];
            }
            return [{ title: this.invoice?.order_title || "Order", qty: 1, price: this.invoice?.amount || 0 }];
        },
        subtotal() {
            if (this.quote.subtotal_incl_vat != null && this.quote.is_saudi) {
                return Number(this.quote.subtotal_incl_vat);
            }
            if (this.quote.subtotal_excl_vat != null) {
                return Number(this.quote.subtotal_excl_vat);
            }
            return this.lineItems.reduce((sum, row) => sum + Number(this.lineTotal(row)), 0);
        },
        discount() {
            return Number(this.quote.discount_amount || 0);
        },
        shipping() {
            return Number(this.quote.shipping_amount || 0);
        },
        tax() {
            return Number(this.quote.tax_amount || 0);
        },
        vatPercent() {
            return Number(this.quote.vat_percent || 0);
        },
        total() {
            return Number(this.quote.total != null ? this.quote.total : this.invoice?.amount || 0);
        },
        isPaid() {
            if (this.invoice?.is_paid != null) return !!this.invoice.is_paid;
            const s = String(this.invoice?.status || "").toLowerCase();
            return s.includes("paid") || s.includes("success");
        },
        paymentStatusLabel() {
            if (this.isPaid) return this.pageLang === "ar" ? "مدفوع" : "Paid";
            return this.pageLang === "ar" ? "غير مدفوعة" : "Unpaid";
        },
        orderStatusLabel() {
            if (this.pageLang === "ar") {
                return this.invoice?.order_status_label_ar || "طلب جديد";
            }
            const key = this.invoice?.order_status || "new";
            const map = {
                new: "New order",
                preparing: "Preparing",
                shipped: "Shipped",
                delivered: "Delivered",
                cancelled: "Cancelled",
            };
            return map[key] || key;
        },
        brandLogo() {
            const logo = this.$store?.state?.setting?.logo;
            if (typeof logo === "string" && logo.trim() !== "") return logo.trim();
            return "/assets/media/svg/brand-logos/logo.png";
        },
        isAdminContext() {
            const from = String(this.$route.query.from || "").toLowerCase();
            if (from === "dashboard" || from === "admin") return true;
            return this.$route.path.includes("/dashboard/");
        },
        backLink() {
            if (this.isAdminContext) {
                return `/${this.locale}/dashboard/orders/order`;
            }
            const token = localStorage.getItem("authToken") || localStorage.getItem("token");
            if (token) return `/${this.locale}/account/orders`;
            return null;
        },
        backLinkLabel() {
            if (this.isAdminContext) {
                return this.pageLang === "ar" ? "العودة لإدارة الطلبات" : "Back to orders";
            }
            return this.pageLang === "ar" ? "طلباتي" : "My orders";
        },
        paymentMethodLabel() {
            const method = this.raw.payment_method || this.invoice?.payment_type || "neoleap";
            return String(method).toUpperCase();
        },
        shippingLabel() {
            const m = this.quote.shipping_method;
            if (!m) return "";
            return this.pageLang === "ar" ? (m.title_ar || m.title_en) : (m.title_en || m.title_ar);
        },
        couponCode() {
            return this.quote?.coupon?.code || "";
        },
    },
    mounted() {
        if (!this.$store?.state?.setting?.logo) {
            this.$store?.dispatch?.("fetchSetting");
        }
        this.fetchItems();
    },
    methods: {
        money(value) {
            return Number(value || 0).toFixed(2);
        },
        lineTotal(row) {
            return (Number(row?.price || 0) * Number(row?.qty || 1)).toFixed(2);
        },
        async fetchItems() {
            this.isLoading = true;
            const trackId = this.$route.params.track_id;
            try {
                // نفس الفاتورة للأدمن والمستخدم بعد الدفع (عبر track_id)
                const response = await axios.get("Payment/getAllItems", {
                    params: { trackId },
                });
                this.invoice = response?.data?.items || null;
            } catch (error) {
                console.error(error);
                this.invoice = null;
            } finally {
                this.isLoading = false;
            }
        },
        async exportToPDF() {
            if (!this.$refs.cvContent) return;
            this.isLoading = true;
            try {
                const opt = {
                    margin: 0.4,
                    filename: `keepersport-invoice-${this.invoice?.track_id || "order"}.pdf`,
                    image: { type: "jpeg", quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: "in", format: "a4", orientation: "portrait" },
                };
                await html2pdf().set(opt).from(this.$refs.cvContent).save();
            } finally {
                this.isLoading = false;
            }
        },
    },
};
</script>

<style scoped>
.ks-inv-page {
    min-height: 70vh;
    background: #f4f4f4;
    padding: 28px 16px 48px;
}
.ks-inv-loading {
    text-align: center;
    color: #666;
    padding: 48px 12px;
}
.ks-inv-sheet {
    max-width: 860px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 8px;
    overflow: hidden;
    color: #111;
}
.ks-inv-head {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 22px;
    background: linear-gradient(180deg, #fff9e6 0%, #fff 100%);
    border-bottom: 3px solid #ffcc00;
    flex-wrap: wrap;
}
.ks-inv-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}
.ks-inv-brand img {
    width: 56px;
    height: 56px;
    object-fit: contain;
}
.ks-inv-brand strong {
    display: block;
    font-size: 20px;
    font-weight: 900;
}
.ks-inv-brand span {
    color: #666;
    font-size: 13px;
}
.ks-inv-meta {
    display: grid;
    gap: 6px;
    min-width: 220px;
    font-size: 13px;
}
.ks-inv-meta div {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}
.ks-inv-meta span { color: #666; }
.is-paid { color: #067647; }
.is-unpaid { color: #b42318; }
.ks-inv-parties {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    padding: 18px 22px;
    border-bottom: 1px solid #ececec;
}
.ks-inv-parties h3 {
    margin: 0 0 8px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #8a7200;
}
.ks-inv-parties p {
    margin: 0 0 4px;
    font-size: 13px;
    color: #333;
}
.ks-inv-table-wrap { padding: 8px 22px 18px; overflow-x: auto; }
.ks-inv-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 13px;
    direction: rtl;
}
.ks-inv-table th {
    background: #111;
    color: #ffcc00;
    padding: 10px 8px;
    font-weight: 800;
}
.ks-inv-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #efefef;
    vertical-align: top;
}
.ks-inv-col-desc {
    width: 52%;
    text-align: right !important;
}
.ks-inv-col-num {
    width: 16%;
    text-align: center !important;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}
.ks-inv-sku {
    color: #777;
    font-size: 12px;
    margin-top: 2px;
    direction: ltr;
    unicode-bidi: isolate;
    text-align: right;
}
[dir="ltr"] .ks-inv-table,
.ks-inv-page[lang="en"] .ks-inv-table {
    direction: ltr;
}
[dir="ltr"] .ks-inv-col-desc,
.ks-inv-page[lang="en"] .ks-inv-col-desc {
    text-align: left !important;
}
[dir="ltr"] .ks-inv-sku,
.ks-inv-page[lang="en"] .ks-inv-sku {
    text-align: left;
}
.ks-inv-totals {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 16px;
    padding: 8px 22px 22px;
}
.ks-inv-total-rows {
    border: 1px solid #ececec;
    border-radius: 6px;
    padding: 12px;
}
.ks-inv-total-rows > div {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin: 8px 0;
    font-size: 14px;
}
.ks-inv-grand {
    border-top: 1px solid #ececec;
    padding-top: 10px;
    font-size: 16px !important;
    font-weight: 800;
}
.ks-inv-payinfo {
    font-size: 13px;
    color: #444;
}
.ks-inv-payinfo p { margin: 0 0 8px; }
.ks-inv-qr :deep(svg) { max-width: 140px; height: auto; }
.ks-inv-foot {
    padding: 14px 22px 18px;
    border-top: 1px solid #ececec;
    text-align: center;
    color: #666;
    font-size: 13px;
}
.ks-inv-actions {
    max-width: 860px;
    margin: 16px auto 0;
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
.ks-inv-btn {
    min-height: 42px;
    border: 0;
    background: #ffcc00;
    color: #111;
    font-weight: 800;
    padding: 0 18px;
    border-radius: 4px;
    cursor: pointer;
}
.ks-inv-link {
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    padding: 0 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #111;
    text-decoration: none;
    font-weight: 700;
}
@media (max-width: 800px) {
    .ks-inv-parties,
    .ks-inv-totals { grid-template-columns: 1fr; }
}
</style>
