<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      translate="no"
      class="notranslate"
      style="direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};">
    <head>
        <title>{{ $shareMeta['title'] ?? 'KEEPERsport Saudi Arabia' }}</title>
        <meta charset="utf-8" />
        <meta name="google" content="notranslate" />
        <meta name="description" content="{{ $shareMeta['description'] ?? 'KEEPERsport Saudi Arabia — goalkeeper gloves, boots, apparel and equipment.' }}" />
        <meta name="keywords" content="keeper sport, goalkeeper gloves, Saudi Arabia, حراس المرمى, KEEPERsport السعودية" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover" />
        <meta name="robots" content="index, follow" />
        <link rel="canonical" href="{{ $shareMeta['canonical'] ?? url()->current() }}" />

        <meta property="og:locale" content="{{ $shareMeta['og_locale'] ?? (app()->getLocale() === 'ar' ? 'ar_SA' : 'en_US') }}" />
        <meta property="og:type" content="{{ $shareMeta['og_type'] ?? 'website' }}" />
        <meta property="og:title" content="{{ $shareMeta['og_title'] ?? ($shareMeta['title'] ?? 'KEEPERsport Saudi Arabia') }}" />
        <meta property="og:description" content="{{ $shareMeta['og_description'] ?? ($shareMeta['description'] ?? 'KEEPERsport Saudi Arabia — goalkeeper gloves, boots, apparel and equipment.') }}" />
        <meta property="og:url" content="{{ $shareMeta['og_url'] ?? url()->current() }}" />
        <meta property="og:site_name" content="{{ $shareMeta['og_site_name'] ?? 'KEEPERsport Saudi Arabia' }}" />
        @if(!empty($shareMeta['og_image']))
        <meta property="og:image" content="{{ $shareMeta['og_image'] }}" />
        <meta property="og:image:secure_url" content="{{ $shareMeta['og_image'] }}" />
        @endif

        <meta name="twitter:card" content="{{ $shareMeta['twitter_card'] ?? 'summary_large_image' }}" />
        <meta name="twitter:title" content="{{ $shareMeta['twitter_title'] ?? ($shareMeta['title'] ?? 'KEEPERsport Saudi Arabia') }}" />
        <meta name="twitter:description" content="{{ $shareMeta['twitter_description'] ?? ($shareMeta['description'] ?? 'KEEPERsport Saudi Arabia — goalkeeper gloves, boots, apparel and equipment.') }}" />
        @if(!empty($shareMeta['twitter_image']))
        <meta name="twitter:image" content="{{ $shareMeta['twitter_image'] }}" />
        @endif

        @php
            $siteFavicon = optional(\App\Models\Setting::query()->find(1))->logo;
        @endphp
        @if($siteFavicon)
        <link rel="shortcut icon" href="{{ $siteFavicon }}" />
        @endif

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
        <link href="{{url('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{url('assets/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{url('assets/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />

        @if(app()->getLocale() === 'ar')
            <link id="main-style" href="{{ url('assets/css/style.bundle.rtl.css') }}" rel="stylesheet" type="text/css" />
        @else
            <link id="main-style" href="{{url('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
        @endif

        <link href="{{ url('assets/css/styleCustom.css') }}" rel="stylesheet" type="text/css" />

        <script>
            window.defaultLocale = @json(app()->getLocale());
            window.__SITE_SHARE_META__ = @json($shareMeta ?? []);
            window.__SITE_SETTINGS__ = @json($siteBootstrap ?? []);
        </script>

        @if(!empty($siteBootstrap['cover_image_mobile']))
        <link rel="preload" as="image" href="{{ $siteBootstrap['cover_image_mobile'] }}" media="(max-width: 1024px)" fetchpriority="high" />
        @endif
        @if(!empty($siteBootstrap['cover_image']))
        <link rel="preload" as="image" href="{{ $siteBootstrap['cover_image'] }}" media="(min-width: 1025px)" fetchpriority="high" />
        @endif

        <style>
            .ks-hero-boot-shimmer {
                width: 100%;
                min-height: clamp(180px, 42vw, 420px);
                background: linear-gradient(90deg, #1a1a1a 0%, #2a2a2a 40%, #1a1a1a 80%);
                background-size: 200% 100%;
                animation: ks-hero-shimmer 1.1s ease-in-out infinite;
            }
            @keyframes ks-hero-shimmer {
                0% { background-position: 100% 0; }
                100% { background-position: -100% 0; }
            }
            @media (max-width: 1024px) {
                .ks-hero-boot-shimmer { min-height: clamp(220px, 70vw, 520px); }
            }
        </style>

        <script>if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>

        @vite(['resources/js/components/Website/app.js'])
    </head>
    <body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed notranslate" translate="no">
        <script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>

        <div id="app"></div>

        <script>var hostUrl = "assets/";</script>

        <script src="{{url('assets/plugins/global/plugins.bundle.js')}}"></script>
        <script src="{{url('assets/js/scripts.bundle.js')}}"></script>
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
        <script src="{{url('assets/plugins/custom/datatables/datatables.bundle.js')}}"></script>
        <script src="{{url('assets/js/widgets.bundle.js')}}"></script>
        <script src="{{url('assets/js/custom/widgets.js')}}"></script>
        <script src="{{url('assets/js/custom/apps/chat/chat.js')}}"></script>
        <script src="{{url('assets/js/custom/utilities/modals/create-app.js')}}"></script>
        <script src="{{url('assets/js/custom/utilities/modals/create-campaign.js')}}"></script>
        <script src="{{url('assets/js/custom/utilities/modals/users-search.js')}}"></script>
    </body>
</html>
