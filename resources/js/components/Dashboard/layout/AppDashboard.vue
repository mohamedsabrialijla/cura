<template>
        <router-view v-if="isAuthScreen" :key="$route.fullPath"></router-view>

        <div v-else class="d-flex flex-column flex-root" :dir="dashboardIsRtl ? 'rtl' : 'ltr'">
            <!--begin::Page-->
            <div class="page d-flex flex-row flex-column-fluid">
                <!--begin::Wrapper-->
                <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                    
                    <!--begin::Header-->
                            
                            <HeaderDashboard></HeaderDashboard>

                    <!--end::Header-->


                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column-fluid">
                        <!--begin::Aside-->
                        <Menu></Menu>
                        <!--end::Aside-->
                        <!--begin::Container-->
                        <div class="d-flex flex-column flex-column-fluid container-fluid">
                            <!--begin::Toolbar-->
                              <Breadcrumb></Breadcrumb>
                            <!--end::Toolbar-->                            

                                <router-view :key="$route.fullPath"></router-view>


                            <!--begin::Footer-->
                            
                            <FooterDashboard></FooterDashboard>

                            <!--end::Footer-->



                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Content wrapper-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Page-->
        </div>
        <!--end::Root-->

    
</template>

<script>
import HeaderDashboard from './HeaderDashboard.vue';
import FooterDashboard from './FooterDashboard.vue';
import Breadcrumb from './Breadcrumb.vue';
import Menu from './Menu.vue';
import store from './../../../store';

import { mapActions } from 'vuex';

export default {
    components: {
        HeaderDashboard,
        FooterDashboard,
        Menu,
        Breadcrumb,
        store,
    },




     
 
    computed: {
        isAuthScreen() {
            const path = this.$route?.path || "";
            return path.includes("/admin/login") || this.$route?.meta?.hideHeaderFooter === true;
        },
    },

    watch: {
        dashboardLocale: {
            immediate: true,
            handler(locale) {
                const isAr = locale === "ar";
                document.documentElement.lang = locale;
                document.documentElement.dir = isAr ? "rtl" : "ltr";
            },
        },
    },

    mounted() {

        let url = window.location.origin ;
        this.loadScript(url+'/assets/js/custom/apps/user-management/users/list/table.js');
        // this.loadScript(url+'/assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js');
        
        // this.loadScript(url+'/assets/js/custom/apps/user-management/users/list/export-users.js');
        // this.loadScript(url+'/assets/js/custom/apps/user-management/users/list/add.js');
        


        this.fetchLanguages();
        if (!this.isAuthScreen) {
            this.fetchUser();
        }





    },

   methods: {

    ...mapActions(['fetchLanguages']), // ربط دالة fetchLanguages هنا
    ...mapActions(['fetchUser']), // ربط دالة fetchLanguages هنا

    
    loadScript(src) {
      let script = document.createElement('script');
      script.src = src;
      script.async = true; // جعل التحميل غير متزامن لتحسين الأداء
      document.body.appendChild(script);
    }
  }
};
</script>

<style>
    
    ol, p, ul {
        line-height: 1.0;
    }

    .margin-auto{
      margin: auto;
    }


    .form-control.form-control-solid {
      background-color: inherit;
    }


    .modal-header {
      border-bottom: var(--bs-modal-header-border-width) solid rgb(214,162,48);
    }


    .multiselect__option--highlight {
      background-color: transparent !important; /* Remove background highlight */
      color: inherit !important; /* Keep text color default */
  }

  .multiselect__option::after {
      content: "" !important;
  }



            
.multiselect__option--highlight {
    background: rgb(214,162,48) !important;
    outline: none;
    color: #fff;}

    .multiselect__tag {
    background: rgb(214,162,48);}

    .multiselect__tag-icon:after {
    color: #fff;
    }


    .multiselect--above{
      background: rgb(214,162,48);
    }



     .image-input-placeholder { background-image: url('assets/media/svg/files/blank-image.svg'); } [data-bs-theme="dark"] .image-input-placeholder { background-image: url('assets/media/svg/files/blank-image-dark.svg'); }


    .btn-primary {
        --bs-btn-bg: rgb(214,162,48) !important;
    }

    .btn.btn-primary {
        color: var(--bs-primary-inverse);
        border-color: rgb(214,162,48) !important;
        background-color: rgb(214,162,48) !important;
    }


    .btn-check:checked + .btn.btn-primary, .btn-check:active + .btn.btn-primary, .btn.btn-primary:focus:not(.btn-active), .btn.btn-primary:hover:not(.btn-active), .btn.btn-primary:active:not(.btn-active), .btn.btn-primary.active, .btn.btn-primary.show, .show > .btn.btn-primary {
    background-color: rgb(235 170 27 / 65%) !important;
    }

    .btn-info {
        --bs-btn-bg: #202F3A !important;
    }

    [data-bs-theme="light"] {
        --bs-primary: #202F3A !important;
        --bs-info: #202F3A !important;
    }

    .swal2-confirm{
        background: rgb(214, 162, 48) !important;
    }


</style>