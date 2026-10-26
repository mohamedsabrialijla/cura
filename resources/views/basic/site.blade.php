<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>كيورا الطبي | CURA Medical Team</title>
    <meta name="description" content="كيورا الطبي — فريق طلابي تطوعي يخدم طلبة الكليات الطبية في غزة عبر التدريب العملي والمعرفة. LEARN • RESEARCH • GROW">
    <meta name="keywords" content="كيورا الطبي, CURA Medical Team, غزة, فلسطين, قطاع غزة, تدريب طبي, تمريض, طلبة الطب">

    <link href="/site/assets/img/misbar/logo-transparent.png" rel="icon">
    <link href="/site/assets/img/misbar/logo-transparent.png" rel="apple-touch-icon">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;700;900&family=Poppins:wght@300;400;500;600;700;800&family=Raleway:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link id="bootstrap-css" href="/site/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script>
        (function () {
            var fromUrl = location.pathname.split('/')[1];
            var lang = fromUrl === 'ar' || fromUrl === 'en' ? fromUrl : (localStorage.getItem('site_lang') || 'en');
            var isAr = lang === 'ar';
            document.documentElement.lang = lang;
            document.documentElement.dir = isAr ? 'rtl' : 'ltr';
            document.getElementById('bootstrap-css').href = isAr
                ? '/site/assets/vendor/bootstrap/css/bootstrap.rtl.min.css'
                : '/site/assets/vendor/bootstrap/css/bootstrap.min.css';
        })();
    </script>
    <link href="/site/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/site/assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="/site/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="/site/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="/site/assets/css/main.css" rel="stylesheet">
    <link href="/site/assets/css/lang.css" rel="stylesheet">

    @vite(['resources/js/components/Website/app.js'])
</head>
<body class="index-page">
    <div id="app"></div>
    <div id="preloader"></div>

    <script src="/site/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/site/assets/vendor/aos/aos.js"></script>
    <script src="/site/assets/vendor/glightbox/js/glightbox.min.js"></script>
</body>
</html>
