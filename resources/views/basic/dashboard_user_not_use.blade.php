<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      style="direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};">
    <!--begin::Head-->
    <head>
        <title>Siera</title>
        <meta charset="utf-8" />
        <meta name="description" content="The most advanced Tailwind CSS & Bootstrap 5 Admin Theme with 40 unique prebuilt layouts on Themeforest trusted by 100,000 beginners and professionals. Multi-demo, Dark Mode, RTL support and complete React, Angular, Vue, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js, Node.js, Flask, Symfony & Laravel versions. Grab your copy now and get life-time updates for free." />
        <meta name="keywords" content="tailwind, tailwindcss, metronic, bootstrap, bootstrap 5, angular, VueJs, React, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js, Node.js, Flask, Symfony & Laravel starter kits, admin themes, web design, figma, web development, free templates, free admin themes, bootstrap theme, bootstrap template, bootstrap dashboard, bootstrap dak mode, bootstrap button, bootstrap datepicker, bootstrap timepicker, fullcalendar, datatables, flaticon" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta property="og:locale" content="en_US" />
        <meta property="og:type" content="article" />
        <meta property="og:title" content="Metronic - The World's #1 Selling Tailwind CSS & Bootstrap Admin Template by KeenThemes" />
        <meta property="og:url" content="https://keenthemes.com/metronic" />
        <meta property="og:site_name" content="Metronic by Keenthemes" />
        <link rel="canonical" href="http://preview.keenthemes.comindex.html" />
        <link rel="shortcut icon" href="https://www.keepersport.it/favicon/android-icon-192x192.png" />

        <!--begin::Fonts(mandatory for all pages)-->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
        <!--end::Fonts-->
        <!--begin::Vendor Stylesheets(used for this page only)-->
        <link href="{{url('assets_user/plugins/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{url('assets_user/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
        <!--end::Vendor Stylesheets-->

        <!-- <link href="{{url('assets_user/plugins/custom/prismjs/prismjs.bundle.css')}}" rel="stylesheet" type="text/css" /> -->



        <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
        <link href="{{url('assets_user/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
            <!-- <link href="{{url('assets_user/css/style.bundle.css')}}" rel="stylesheet" type="text/css" /> -->



        @if(app()->getLocale() === 'ar')
            <link href="{{ url('assets_user/css/style.bundle.rtl.css') }}" rel="stylesheet" type="text/css" />
        @else
            <link href="{{url('assets_user/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />

        @endif

        <link href="{{ url('assets/css/styleCustom.css') }}" rel="stylesheet" type="text/css" />


        <!--end::Global Stylesheets Bundle-->
        <script>// Frame-busting to prevent site from being loaded within a frame without permission (click-jacking) if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>

                    @vite(['resources/js/components/User/userDashboard.js'])

    </head>
    <!--end::Head-->
    <!--begin::Body-->

    <script>var hostUrl = "assets/";
            window.translations = @json(__('common'));
        </script>
    <body id="kt_app_body" data-kt-app-header-stacked="true" data-kt-app-header-primary-enabled="true" data-kt-app-header-secondary-enabled="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="false" data-kt-app-toolbar-enabled="true" class="app-default">
        <!--begin::Theme mode setup on page load-->
        <script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>



        <div id="dashboard-user" >

        </div>







        <!--begin::Javascript-->
          <script>var hostUrl = "assets_user/";</script>
        <!--begin::Global Javascript Bundle(mandatory for all pages)-->
        <script src="{{url('assets_user/plugins/global/plugins.bundle.js')}}"></script>
        <script src="{{url('assets_user/js/scripts.bundle.js')}}"></script>
        <script src="{{url('assets_user/plugins/custom/datatables/datatables.bundle.js')}}"></script>

        <!--end::Global Javascript Bundle-->
        <!--begin::Vendors Javascript(used for this page only)-->
        <script src="{{url('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js')}}"></script>
        <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/radar.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/map.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/geodata/worldLow.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/geodata/continentsLow.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/geodata/usaLow.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZonesLow.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZoneAreasLow.js"></script>
        <!--end::Vendors Javascript-->

<!--         <script src="{{url('assets/js/custom/apps/user-management/users/list/table.js')}}"></script>
        <script src="{{url('assets/js/custom/apps/user-management/users/list/export-users.js')}}"></script>
        <script src="{{url('assets/js/custom/apps/user-management/users/list/add.js')}}"></script>
 -->
        <!--begin::Custom Javascript(used for this page only)-->
        <script src="{{url('assets_user/js/widgets.bundle.js')}}"></script>
        <script src="{{url('assets_user/js/custom/widgets.js')}}"></script>
        <script src="{{url('assets_user/js/custom/apps/chat/chat.js')}}"></script>
        <script src="{{url('assets_user/js/custom/utilities/modals/create-app.js')}}"></script>
        <script src="{{url('assets_user/js/custom/utilities/modals/create-campaign.js')}}"></script>
        <script src="{{url('assets_user/js/custom/utilities/modals/users-search.js')}}"></script>
        <script src="{{url('assets_user/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>
        <!-- <script src="{{url('assets_user/plugins/custom/prismjs/prismjs.bundle.js')}}"></script> -->

    </body>
    <!--end::Body-->
</html>
