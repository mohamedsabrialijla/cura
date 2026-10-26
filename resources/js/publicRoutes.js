import Landing from "@/components/Website/pages/Landing.vue";
import Login from "@/components/Website/auth/Login.vue";
import Forget from "@/components/Website/auth/Forget.vue";
import Verfied from "@/components/Website/auth/Verfied.vue";
import Register from "@/components/Website/auth/Register.vue";
import AuthLayout from "@/components/Website/auth/AuthLayout.vue";
import LoginAdmin from "@/components/Website/adminAuth/Login.vue";
import AboutUs from "@/components/Website/pages/AboutKeepersport.vue";
import FAQ from "@/components/Website/pages/FAQ.vue";
import Contact from "@/components/Website/pages/Contact.vue";
import Terms from "@/components/Website/pages/Terms.vue";
import UsageReturnPolicy from "@/components/Website/pages/UsageReturnPolicy.vue";
import CmsPage from "@/components/Website/pages/CmsPage.vue";
import GloveCare from "@/components/Website/pages/GloveCare.vue";
import CartPage from "@/components/Website/pages/Cart.vue";
import CheckoutPage from "@/components/Website/pages/Checkout.vue";
import GuantiDaPortierePage from "@/components/Website/pages/GuantiDaPortierePage.vue";
import ProductDetails from "@/components/Website/pages/ProductDetails.vue";
import MyPage from "@/components/Website/pages/MyPage.vue";
import SellerPage from "@/components/Website/pages/SellerPage.vue";
import PayCommission from "@/components/Website/pages/PayCommission.vue";
import PaymentSuccess from "@/components/Website/pages/PaymentSuccess.vue";
import PaymentFailed from "@/components/Website/pages/PaymentFailed.vue";
import AddAd from "@/components/Website/pages/AddAd.vue";
import AdDetails from "@/components/Website/pages/AdDetails.vue";
import OtpVerify from "@/components/Website/auth/OtpVerify.vue";
import AccountLayout from "@/components/Website/pages/account/AccountLayout.vue";
import AccountDashboard from "@/components/Website/pages/account/AccountDashboard.vue";
import AccountProfile from "@/components/Website/pages/account/AccountProfile.vue";
import AccountOrders from "@/components/Website/pages/account/AccountOrders.vue";

import userDashboard from "@/components/Website/dashboard/Dashboard.vue";
import userCV from "@/components/Website/dashboard/userCV.vue";
import Create from "@/components/Website/dashboard/Create.vue";

import InvoicePdf from "@/components/Website/pages/InvoicePdf.vue";



export default [
    {
        path: "/:locale/",
        name: "home",
        component: Landing,
        meta: { isHomePage: true },
    },
    {
        path: "/:locale/guanti-da-portiere",
        name: "guanti-da-portiere",
        redirect: (to) => `/${to.params.locale}/goalkeeper-gloves`,
        meta: { isMenu: false },
    },
    /** نفس مسار keepersport.net: /en/football-boots-hard-court (قبل /shop العام) */
    {
        path: "/:locale/:bootCategory(football-boots-.+)",
        name: "shop-football-boots-leaf",
        component: GuantiDaPortierePage,
        meta: { isMenu: false },
    },
    /** تحويل الروابط القديمة /shop/hard-court → /football-boots-hard-court */
    {
        path: "/:locale/shop/:bootRedirect(hard-court|firm-ground|soft-ground|artificial-grass|turf|multi-ground|street|cleats|indoor|sandals|astro-turf|fg|sg|ag|tf|mg|ic|in|fxg|accessories|kids-football-boots|leisure-kids)",
        redirect: (to) =>
            `/${to.params.locale}/football-boots-${String(to.params.bootRedirect).replace(/_/g, "-")}`,
    },
    {
        path: "/:locale/search",
        name: "catalog-search",
        component: GuantiDaPortierePage,
        meta: { isMenu: false },
    },
    {
        path: "/:locale/shop/:catalogSlug",
        name: "shop-catalog",
        component: GuantiDaPortierePage,
        meta: { isMenu: false },
    },
    {
        path: "/:locale/:catalogSlug(goalkeeper-gloves|football-boots|goalkeeper-apparel|goalkeeper-equipment|teamsport|sale)",
        name: "shop-catalog-main",
        component: GuantiDaPortierePage,
        meta: { isMenu: false },
    },
    {
        path: "/:locale/login",
        component: AuthLayout,
        children: [
            { path: "", component: Login },
        ],
    },

    {
        path: "/:locale/forget-password",
        component: AuthLayout,
        children: [
            { path: "", component: Forget },
        ],
    },

    {
        path: "/:locale/verfied-message",
        component: AuthLayout,
        children: [
            { path: "", component: Verfied },
        ],
    },

    {
        path: "/:locale/signUp",
        component: AuthLayout,
        children: [
            { path: "", component: Register },
        ],
    },

    {
        path: "/:locale/otp-verify",
        component: AuthLayout,
        children: [
            {
                path: "",
                component: OtpVerify,
            },
        ],
    },

    {
        path: "/:locale/admin/login",
        component: AuthLayout,
        name: "AdminLogin",
        children: [
            {
                path: "",
                component: LoginAdmin,
                meta: { hideHeaderFooter: true },
            },
        ],
    },

    { path: "/:locale/about-us", component: AboutUs, meta: { isMenu: false } },
    { path: "/:locale/faq", component: FAQ, meta: { isMenu: false } },
    {
        path: "/:locale/contact-us",
        component: Contact,
        meta: { isMenu: false },
    },
    { path: "/:locale/terms", component: Terms, meta: { isMenu: false } },
    {
        path: "/:locale/privacy",
        component: CmsPage,
        props: { fixedPageKey: "privacy" },
        meta: { isMenu: false },
    },
    { path: "/:locale/usage-return", component: UsageReturnPolicy, meta: { isMenu: false } },
    {
        path: "/:locale/page/goalkeeper-day",
        redirect: (to) => ({
            path: `/${to.params.locale}/about-us`,
            query: { tab: "day" },
        }),
    },
    { path: "/:locale/page/:pageKey", component: CmsPage, meta: { isMenu: false } },
    { path: "/:locale/glove-care", component: GloveCare, meta: { isMenu: false } },
    { path: "/:locale/cart", name: "shopping-cart", component: CartPage, meta: { isMenu: false } },
    { path: "/:locale/checkout", name: "checkout", component: CheckoutPage, meta: { isMenu: false } },
    {
        path: "/:locale/account",
        component: AccountLayout,
        meta: { requiresAuth: true, isMenu: false },
        children: [
            { path: "", component: AccountDashboard, meta: { requiresAuth: true } },
            { path: "profile", component: AccountProfile, meta: { requiresAuth: true } },
            { path: "orders", component: AccountOrders, meta: { requiresAuth: true } },
        ],
    },
    {
        path: "/:locale/account-overview",
        redirect: (to) => `/${to.params.locale}/account`,
    },

    {
        path: "/:locale/app/info",
        component: userDashboard,
        meta: { requiresAuth: true, isMenu: true },
    },

    {
        path: "/:locale/app/templates",
        component: userCV,
        meta: { requiresAuth: true, isMenu: true },
    },

    {
        path: "/:locale/app/create-template",
        component: Create,
        name: "CreateTemplate",
        meta: { requiresAuth: true, isMenu: true },
    },

    {
        path: "/:locale/category/:id",
        name: "category-products",
        component: GuantiDaPortierePage,
        meta: { isMenu: false },
    },
    {
        path: "/:locale/product/:slug",
        name: "product-details",
        component: ProductDetails,
    },

    {
        path: "/:locale/my-page",
        component: MyPage,
        meta: { isMenu: false },
    },


    {
        path: "/:locale/seller-page",
        component: SellerPage,
        meta: { isMenu: false },
    },

    {
        path: "/:locale/pay-commission",
        component: PayCommission,
        meta: { isMenu: false },
    },

    {
        path: "/:locale/add-ad",
        component: AddAd,
        meta: { isMenu: false },
    },

    {
        path: "/:locale/ad-details/:id",
        component: AdDetails,
        meta: { isMenu: false },
    },


    {
      path: '/:locale/payment/success',
      component: PaymentSuccess
    },
    {
      path: '/:locale/payment/failed',
      component: PaymentFailed
    },
 
    {
        path: "/:locale/invoice/:track_id",
        component: InvoicePdf,
        meta: { isMenu: false, hideHeaderFooter: true},
    },




];
