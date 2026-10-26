<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages') || ! Schema::hasColumn('pages', 'page_key')) {
            return;
        }

        $pages = [
            'payment' => [
                'en' => [
                    'name' => 'Payment',
                    'slug' => 'payment',
                    'key_words' => 'payment, billing',
                    'description' => '<p>We accept major credit/debit cards and supported digital wallets at checkout. Once payment succeeds, your order items are reserved and you receive an order confirmation by email.</p><p>Edit this page from the dashboard to add your full payment policy.</p>',
                ],
                'ar' => [
                    'name' => 'الدفع',
                    'slug' => 'الدفع',
                    'key_words' => 'دفع, فوترة',
                    'description' => '<p>نقبل بطاقات الائتمان/الخصم والمحافظ الرقمية المدعومة عند إتمام الطلب. بعد نجاح الدفع يتم حجز المنتجات ويصلك تأكيد الطلب على البريد.</p><p>عدّل هذه الصفحة من لوحة التحكم لإضافة سياسة الدفع الكاملة.</p>',
                ],
            ],
            'shipping' => [
                'en' => [
                    'name' => 'Shipping',
                    'slug' => 'shipping',
                    'key_words' => 'shipping, delivery',
                    'description' => '<p>Shipping costs are calculated at checkout after you enter your address. Carriers may vary by destination and product availability.</p><p>Edit this page from the dashboard to add your full shipping policy.</p>',
                ],
                'ar' => [
                    'name' => 'الشحن',
                    'slug' => 'الشحن',
                    'key_words' => 'شحن, توصيل',
                    'description' => '<p>تُحسب تكلفة الشحن تلقائيًا في صفحة الدفع بعد إدخال العنوان. قد تختلف شركة الشحن حسب الوجهة وتوفر المنتجات.</p><p>عدّل هذه الصفحة من لوحة التحكم لإضافة سياسة الشحن الكاملة.</p>',
                ],
            ],
            'delivery' => [
                'en' => [
                    'name' => 'Delivery times',
                    'slug' => 'delivery-times',
                    'key_words' => 'delivery, times',
                    'description' => '<p>In-stock items are usually prepared within 1–2 working days. Delivery often takes around 3–10 business days depending on destination.</p><p>Edit this page from the dashboard to publish exact delivery timelines.</p>',
                ],
                'ar' => [
                    'name' => 'مدة التوصيل',
                    'slug' => 'مدة-التوصيل',
                    'key_words' => 'مدة, توصيل',
                    'description' => '<p>المنتجات المتوفرة تُجهَّز عادة خلال 1–2 يوم عمل، والتوصيل غالبًا خلال 3–10 أيام عمل حسب الوجهة.</p><p>عدّل هذه الصفحة من لوحة التحكم لنشر مدد التوصيل الدقيقة.</p>',
                ],
            ],
            'returns' => [
                'en' => [
                    'name' => 'Returns | Withdrawal',
                    'slug' => 'returns',
                    'key_words' => 'returns, withdrawal',
                    'description' => '<p>You can return eligible items according to our return policy after delivery. Contact support if you need help starting a return.</p><p>Edit this page from the dashboard to publish the full returns policy.</p>',
                ],
                'ar' => [
                    'name' => 'الإرجاع',
                    'slug' => 'الإرجاع',
                    'key_words' => 'إرجاع, استبدال',
                    'description' => '<p>يمكنك إرجاع المنتجات المؤهلة وفق سياسة الإرجاع بعد الاستلام. تواصل مع الدعم إذا احتجت مساعدة لبدء طلب الإرجاع.</p><p>عدّل هذه الصفحة من لوحة التحكم لنشر سياسة الإرجاع الكاملة.</p>',
                ],
            ],
        ];

        $now = now();

        foreach ($pages as $key => $locales) {
            $pageId = DB::table('pages')->where('page_key', $key)->value('id');
            if ($pageId === null) {
                $pageId = DB::table('pages')->insertGetId([
                    'page_key' => $key,
                    'image' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
            }

            foreach ($locales as $locale => $row) {
                $exists = DB::table('page_translations')
                    ->where('page_id', $pageId)
                    ->where('locale', $locale)
                    ->exists();

                if ($exists) {
                    DB::table('page_translations')
                        ->where('page_id', $pageId)
                        ->where('locale', $locale)
                        ->update([
                            'name' => $row['name'],
                            'slug' => $row['slug'],
                            'key_words' => $row['key_words'],
                            'description' => $row['description'],
                        ]);
                } else {
                    DB::table('page_translations')->insert([
                        'page_id' => $pageId,
                        'locale' => $locale,
                        'name' => $row['name'],
                        'slug' => $row['slug'],
                        'key_words' => $row['key_words'],
                        'description' => $row['description'],
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages') || ! Schema::hasColumn('pages', 'page_key')) {
            return;
        }

        $ids = DB::table('pages')
            ->whereIn('page_key', ['payment', 'shipping', 'delivery', 'returns'])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('page_translations')->whereIn('page_id', $ids)->delete();
        DB::table('pages')->whereIn('id', $ids)->delete();
    }
};
