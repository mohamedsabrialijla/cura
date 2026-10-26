<template>
   
        <div id="kt_aside" class="aside card" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_toggle">
            <!--begin::Aside menu-->
            <div class="aside-menu flex-column-fluid px-4">
                <!--begin::Aside Menu-->
                <div class="hover-scroll-overlay-y mh-100 my-5" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="{default: '#kt_aside_footer', lg: '#kt_header, #kt_aside_footer'}" data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu" data-kt-scroll-offset="{default: '5px', lg: '75px'}">
                    <!--begin::Menu-->
                    <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_aside_menu" data-kt-menu="true">
                        <div class="menu-item pt-5">
                            <div v-if="canMenu('menu-admins')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/user_management/admins`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-element-8">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                    </i>
                                {{ dt.menu.admins }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-roles')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/user_management/roles`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-element-8">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                    </i>
                                {{ dt.menu.roles }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-payment')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/orders/order`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-abstract-28">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                    </i>
                                {{ dt.menu.orders || dt.menu.payment }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-users')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/user_management/users`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-profile-user">
                                         <span class="path1"></span>
                                         <span class="path2"></span>
                                         <span class="path3"></span>
                                         <span class="path4"></span>
                                        </i>
                                {{ dt.menu.users }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-contact')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/setting/contact`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-user-tick">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                    </i>
                                {{ dt.menu.contact }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-landing-slider')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/setting/landing-slider`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-user-tick">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                    </i>
                                {{ dt.menu.landing_slider }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-technical-slider')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/setting/technical-slider`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-user-tick">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                    </i>
                                {{ dt.menu.technical_slider }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-category-strip')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/setting/category-strip`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-user-tick">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                    </i>
                                {{ dt.menu.category_strip }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-brand-slider')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/setting/brand-slider`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-user-tick">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                    </i>
                                {{ dt.menu.brand_slider }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-shipping')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/shipping/methods`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-delivery">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                     <span class="path4"></span>
                                     <span class="path5"></span>
                                    </i>
                                {{ dt.menu.shipping }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-payment-methods') || canMenu('menu-payment')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/payment-methods`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-credit-cart">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                    </i>
                                {{ dt.menu.payment_methods }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-coupons')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/coupons/coupon`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-discount">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                    </i>
                                {{ dt.menu.coupons }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-carts')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/carts/cart`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-basket">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                     <span class="path4"></span>
                                    </i>
                                {{ dt.menu.carts }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-pages')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/setting/pages`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-user-tick">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                    </i>
                                {{ dt.menu.pages }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-faq') || canMenu('menu-pages')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/faqs`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-questionnaire-tablet">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                    </i>
                                {{ dt.menu.faq }}</router-link>
                            </div>

                            <div v-if="canMenu('menu-settings')" class="menu-content">
                                <router-link :to="`/${locale}/dashboard/setting/edit`" class="menu-heading fw-bold fs-6 " style="text-align: center;" >
                                    <i class="ki-duotone ki-user-tick">
                                     <span class="path1"></span>
                                     <span class="path2"></span>
                                     <span class="path3"></span>
                                    </i>
                                {{ dt.menu.settings }}</router-link>
                            </div>
                        </div>

                    </div>
                </div>
               
            </div>

        </div>    


</template>

<script>
    export default {
       name : 'Menu',

       computed: {
    locale() {
        return this.$route.params.locale;
    }
}
    }
</script>
