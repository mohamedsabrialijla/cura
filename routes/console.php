<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
 * تسخين كاش كتالوج Keepersport في الخلفية (سحب من المخزن + تخزين محلي).
 *
 * الطريقة ١ — cron مباشر (أنت تحدد الموعد — مرتين يومياً أفضل):
 *   0 3,15 * * * /bin/bash /home/keepersport/public_html/project/scripts/warm-catalog-cron.sh
 *
 * الطريقة ٢ — جدولة Laravel (KEEPERSPORT_WARM_CATALOG_CRON في .env) + cron كل دقيقة:
 *   * * * * * cd /home/keepersport/public_html/project && php artisan schedule:run >> /dev/null 2>&1
 */
$cron = trim((string) config('services.keepersport_graphql.warm_catalog_cron', ''));
if ($cron !== '' && ! in_array(strtolower($cron), ['off', 'false', '0', 'disabled'], true)) {
    $event = Schedule::command('keepersport:warm-catalog-cache')
        ->timezone(config('app.timezone'))
        ->withoutOverlapping(120)
        ->runInBackground()
        ->appendOutputTo(storage_path('logs/catalog-warm.log'));

    if (preg_match('/^\d{1,2}:\d{2}$/', $cron)) {
        $event->dailyAt($cron);
    } else {
        $event->cron($cron);
    }
}
